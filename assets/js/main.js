jQuery( document ).ready( function () {

    //Search for calendar invites
    jQuery( '#ci-search' ).click( function () {

        jQuery( '.spinner-border' ).css( 'opacity', 1 );

        const agentNumInputs = jQuery( 'input.agent-number' ).not( '.hide-agent' ).get();

        let agentNums = '';

        for (i = 0; i < agentNumInputs.length; i++) {
            if ( jQuery( agentNumInputs[i] ).is( ':checked' ) ) {
                agentNums += jQuery( agentNumInputs[i] ).val() + ',';
            }
        }

        agentNums = agentNums.slice( 0, -1 );

        jQuery.ajax( {

            type: "POST",
            url: isr_urls.ajaxurl,
            data: {
                action: 'fetch_data',
                start: jQuery( 'input#invite-start-date' ).val(),
                end: jQuery( 'input#invite-end-date' ).val(),
                agents: agentNums
            },
            success: function ( _data ) {

                const data = JSON.parse( _data );

                jQuery( '#referrals-collected' ).text( data.all_business.referrals );

                displayData( data.all_business,
                    jQuery( '#all-business-table' ).get()[0],
                    jQuery( '#agent-stats-table' ).get()[0] );

                displayData( data.referral_business, jQuery( '#referrals-business-table' ).get()[0], null );

                displayCumulativeData( data.agents_cumulative, data.skipped_workforce );

            },
            complete: function () {

                jQuery( '.spinner-border' ).css( 'opacity', 0 );

            },
            error: function () {


            }

        } );

    } );

    //Change presentations or sales values
    jQuery( 'body' ).delegate( '.pres-value, .sales-value', 'change', function () {

        let closing = 0;
        let closing_str = '';

        if ( jQuery( this ).hasClass( 'pres-value' ) ) {
            closing =
                parseFloat( jQuery( this ).closest( 'tbody' ).find( 'input.sales-value' ).val() * 100 / jQuery( this )
                    .val() ).toFixed( 2 );
        }
        else {
            closing = parseFloat( jQuery( this ).val() * 100 / jQuery( this )
                .closest( 'tbody' )
                .find( 'input.pres-value' )
                .val() ).toFixed( 2 );
        }

        if ( closing > 100 || !isFinite( closing ) ) {
            closing = parseFloat( 100 ), toFixed( 2 );
        }

        if ( isNaN( closing ) ) {
            closing_str = '0.00%';
        }
        else {
            closing_str = closing.toString() + '%';
        }

        jQuery( this ).closest( 'tbody' ).find( '.closing-value' ).text( closing_str );

    } );

    //Toggle check all agents
    jQuery( '#chkAllAgents' ).click( function () {

        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery( '.agent-number' ).prop( 'checked', true );
            jQuery( '.agent-number' ).next().addClass( 'selected-agent' );
            jQuery( 'div#workforce' ).css( 'display', 'none' );
        }
        else {
            jQuery( '.agent-number' ).prop( 'checked', false );
            jQuery( '.agent-number' ).next().removeClass( 'selected-agent' );
            jQuery( 'div#workforce' ).css( 'display', 'block' );
        }

    } );

    jQuery( '.agent-number' ).click( function (e) {
        // do nothing
        return;

        /*
        var agent_inputs = jQuery( '.agent-number' ).get();

        if ( !jQuery( this ).is( ':checked' ) ) {
            jQuery( this ).next().removeClass( 'selected-agent' );
            jQuery( '#chkAllAgents' ).prop( 'checked', false );
            for (i = 0; i < agent_inputs.length; i++) {
                if ( jQuery( agent_inputs[i] ).attr( 'data-parent' ) == jQuery( this ).val() ) {
                    jQuery( agent_inputs[i] ).prop( 'checked', false );
                    jQuery( agent_inputs[i] ).next().removeClass( 'selected-agent' );
                    var subsLength = jQuery( '.agent-number[data-parent="' + jQuery( agent_inputs[i] ).val() + '"]' )
                        .get().length;
                    if ( subsLength > 0 ) {
                        toggleSubAgents( jQuery( agent_inputs[i] ) );
                    }
                }
            }
        }
        else {
            jQuery( this ).next().addClass( 'selected-agent' );
            for (i = 0; i < agent_inputs.length; i++) {
                if ( jQuery( agent_inputs[i] ).attr( 'data-parent' ) == jQuery( this ).val() ) {
                    jQuery( agent_inputs[i] ).prop( 'checked', true );
                    jQuery( agent_inputs[i] ).next().addClass( 'selected-agent' );
                    var subsLength = jQuery( '.agent-number[data-parent="' + jQuery( agent_inputs[i] ).val() + '"]' )
                        .get().length;
                    if ( subsLength > 0 ) {
                        toggleSubAgents( jQuery( agent_inputs[i] ) );
                    }
                }
            }
            //jQuery(this).closest('div').nextUntil('div[data-indent="' + jQuery(this).closest('div').css('margin-left') +'"]').find('input').prop('checked', true);
        }

        var allAgentChks = jQuery( '.agent-number' ).get().length;
        var allAgentChksChecked = jQuery( '.agent-number:checked' ).get().length;

        if ( allAgentChks == allAgentChksChecked ) {
            jQuery( '#chkAllAgents' ).prop( 'checked', true );
        }
        else {
         */

    } );

    //Edit data
    jQuery( 'body' ).delegate( '.ci-edit', 'click', function () {

        jQuery( this ).closest( 'tr' ).find( '.datum-test' ).toggleClass( 'datum-test-on' );

        jQuery( this ).closest( 'tr' ).find( '.datum-test-val' ).toggleClass( 'datum-test-val-on' );

    } );

    //Change Values
    jQuery( 'body' ).delegate( '.datum-test', 'input propertychange paste', function () {

        if ( !isNaN( jQuery( this ).val() ) && jQuery( this ) != '' ) {

            const presPercentCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(5) > .datum-test-val' ).get();
            const apptRefPercentCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(7) > .datum-test-val' ).get();
            const apApptValueCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(4) > .datum-test-val' ).get();
            const mainClosePercentCell = jQuery( this )
                .closest( 'tr' )
                .find( 'td:nth-child(6) > .datum-test-val' )
                .get();
            const presRefPercentCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(8) > .datum-test-val' ).get();
            const apRefPercentCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(9) > .datum-test-val' ).get();
            const abClosePercentCell = jQuery( this )
                .closest( 'tr' )
                .find( 'td:nth-child(13) > .datum-test-val' )
                .get();
            const rbClosePercentCell = jQuery( this )
                .closest( 'tr' )
                .find( 'td:nth-child(18) > .datum-test-val' )
                .get();

            const apptValueTestCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(10) > .datum-test' ).get();
            const salesValueTestCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(12) > .datum-test' ).get();
            const presValueTestCell = jQuery( this ).closest( 'tr' ).find( 'td:nth-child(11) > .datum-test' ).get();

            const pres = jQuery( this ).closest( 'tr' ).find( '.datum-test-pres' ).val();
            const appts = jQuery( this ).closest( 'tr' ).find( '.datum-test-appts' ).val();
            const sales = jQuery( this ).closest( 'tr' ).find( '.datum-test-sales' ).val();
            const totalap = jQuery( this ).closest( 'tr' ).find( '.datum-test-ap' ).val();
            const refap = jQuery( this ).closest( 'tr' ).find( '.datum-test-refap' ).val();

            const refpres = jQuery( this ).closest( 'tr' ).find( '.datum-test-refpres' ).val();
            const apptsref = jQuery( this ).closest( 'tr' ).find( '.datum-test-apptsref' ).val();
            const refsales = jQuery( this ).closest( 'tr' ).find( '.datum-test-refsales' ).val();

            let newAPApptValue = '$' + (totalap / appts).toFixed( 2 ).toString();
            let newPresPercent = (pres * 100 / appts).toFixed( 2 ).toString() + '%';
            let newApptRefPercent = (apptsref * 100 / appts).toFixed( 2 ).toString() + '%';
            let newMainClosePercent = (sales * 100 / pres).toFixed( 2 ).toString() + '%';
            const newAPRefPercent = (refap * 100 / totalap).toFixed( 2 ).toString() + '%';
            let newRefPresPercent = (refpres * 100 / pres).toFixed( 2 ).toString() + '%';

            if ( jQuery( this ).hasClass( 'datum-test-appts' ) ) {
                jQuery( apApptValueCell ).text( newAPApptValue );
                jQuery( presPercentCell ).text( newPresPercent );
                jQuery( apptRefPercentCell ).text( newApptRefPercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-pres' ) ) {
                jQuery( presPercentCell ).text( newPresPercent );
                jQuery( mainClosePercentCell ).text( newMainClosePercent );
                jQuery( abClosePercentCell ).text( newMainClosePercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-sales' ) ) {
                jQuery( mainClosePercentCell ).text( newMainClosePercent );
                jQuery( abClosePercentCell ).text( newMainClosePercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-ap' ) ) {
                jQuery( apApptValueCell ).text( newAPApptValue );
                jQuery( apRefPercentCell ).text( newAPRefPercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-apptsref' ) ) {
                origTotalAppts = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(10)' ).text() );
                origTotalRefAppts = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(15)' ).text() );
                const newApptsTotal = origTotalAppts - origTotalRefAppts + parseInt( apptsref );

                newAPApptValue = '$' + (totalap / newApptsTotal).toFixed( 2 ).toString();

                newPresPercent = (pres * 100 / newApptsTotal).toFixed( 2 ).toString() + '%';

                newApptRefPercent = (apptsref * 100 / newApptsTotal).toFixed( 2 ).toString() + '%';

                jQuery( apptValueTestCell ).val( newApptsTotal.toString() );
                jQuery( apApptValueCell ).text( newAPApptValue );
                jQuery( presPercentCell ).text( newPresPercent );
                jQuery( apptRefPercentCell ).text( newApptRefPercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-refpres' ) ) {
                origTotalPres = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(11)' ).text() );
                origTotalRefPres = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(16)' ).text() );
                const newPresTotal = origTotalPres - origTotalRefPres + parseInt( refpres );

                newRefPresPercent = (refpres * 100 / newPresTotal).toFixed( 2 ).toString() + '%';

                var newRBClosePercent = (refsales * 100 / refpres).toFixed( 2 ).toString() + '%';

                jQuery( presValueTestCell ).val( newPresTotal.toString() );
                jQuery( presRefPercentCell ).text( newRefPresPercent );
                jQuery( rbClosePercentCell ).text( newRBClosePercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-refsales' ) ) {
                origSales = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(12)' ).text() );
                origRefSales = parseInt( jQuery( this ).closest( 'tr' ).find( 'td:nth-child(17)' ).text() );
                const newSales = origSales - origRefSales + parseInt( refsales );

                newMainClosePercent = (newSales * 100 / pres).toFixed( 2 ).toString() + '%';

                var newRBClosePercent = (refsales * 100 / refpres).toFixed( 2 ).toString() + '%';

                jQuery( salesValueTestCell ).val( newSales.toString() );
                jQuery( mainClosePercentCell ).text( newMainClosePercent );
                jQuery( rbClosePercentCell ).text( newRBClosePercent );
            }

            if ( jQuery( this ).hasClass( 'datum-test-refap' ) ) {
                jQuery( apRefPercentCell ).text( newAPRefPercent );
            }

        }

    } );

} );

function toggleSubAgents( agent ) {

    const agent_inputs = jQuery( '.agent-number[data-parent="' + jQuery( agent ).val() + '"]' ).get();

    if ( !jQuery( agent ).is( ':checked' ) ) {
        jQuery( agent ).next().removeClass( 'selected-agent' );
        jQuery( '#chkAllAgents' ).prop( 'checked', false );
        for (i = 0; i < agent_inputs.length; i++) {
            if ( jQuery( agent_inputs[i] ).attr( 'data-parent' ) == jQuery( agent ).val() ) {
                jQuery( agent_inputs[i] ).prop( 'checked', false );
                jQuery( agent_inputs[i] ).next().removeClass( 'selected-agent' );
                var subsLength = jQuery( '.agent-number[data-parent="' + jQuery( agent_inputs[i] ).val() + '"]' )
                    .get().length;
                if ( subsLength > 0 ) {
                    toggleSubAgents( jQuery( agent_inputs[i] ) );
                }
            }
        }
    }
    else {
        jQuery( agent ).next().addClass( 'selected-agent' );
        for (i = 0; i < agent_inputs.length; i++) {
            if ( jQuery( agent_inputs[i] ).attr( 'data-parent' ) == jQuery( agent ).val() ) {
                jQuery( agent_inputs[i] ).prop( 'checked', true );
                jQuery( agent_inputs[i] ).next().addClass( 'selected-agent' );
                var subsLength = jQuery( '.agent-number[data-parent="' + jQuery( agent_inputs[i] ).val() + '"]' )
                    .get().length;
                if ( subsLength > 0 ) {
                    toggleSubAgents( jQuery( agent_inputs[i] ) );
                }
            }
        }
    }

}

//Populate the report with data
function displayData( dataSet, summaryTable, agentsTable ) {

    jQuery( 'div#period-container' ).css( 'display', 'unset' );

    jQuery( '.period-label-from' ).text( jQuery( '#invite-start-date' ).val() );

    jQuery( '.period-label-to' ).text( jQuery( '#invite-end-date' ).val() );

    jQuery( summaryTable ).find( 'tbody' ).empty();

    jQuery( agentsTable ).find( 'tbody' ).empty();

    summary_html =
        '<tr>'
        + '<td><input min="0" class="pres-value" type="number" value="'
        + dataSet.summary.presentations
        + '"/></td>'
        + '<td><input min="0" class="sales-value" type="number" value="'
        + dataSet.summary.sales
        + '"/></td>'
        + '<td class="closing-value">'
        + dataSet.summary.closing
        + '</td>'
        + '<td>'
        + dataSet.summary.total
        + '</td>'
        + '</tr>';

    agents_html = '';

    if ( agentsTable != null ) {

        for (const [ key, value ] of Object.entries( dataSet.agent_summary )) {

            let checkedStateRef = '';
            let checkedStateSale = '';

            if ( value.wasReferral == 'true' ) {
                checkedStateRef =
                    '<img class="referral-check" alt="yes" src="'
                    + isr_urls.plugins_url
                    + '/gravity-isr/assets/images/green-check.png"/>';
            }
            else {
                checkedStateRef =
                    '<input style="margin: 0;" type="checkbox" disabled class="form-check-input referral-check" data-checked="false">';
            }

            if ( value.wasSale == 'yes' ) {
                checkedStateSale =
                    '<img class="sale-check" alt="yes" src="'
                    + isr_urls.plugins_url
                    + '/gravity-isr/assets/images/green-check.png"/>';
            }
            else {
                checkedStateSale =
                    '<input style="margin: 0;" type="checkbox" disabled class="form-check-input sale-check" data-checked="false">';
            }

            agents_html +=
                '<tr>'
                + '<td>'
                + value.agNum
                + '</td>'
                + '<td>'
                + value.agName
                + '</td>'
                + '<td>'
                + value.date
                + '</td>'
                + '<td>'
                + value.apType
                + '</td>'
                + '<td>'
                + checkedStateRef
                + '</td>'
                + '<td>'
                + value.refsCollected
                + '</td>'
                + '<td>'
                + checkedStateSale
                + '</td>'
                + '<td class="agent-ap">'
                + value.AP
                + '</td>'
                + '</tr>';
        }

        jQuery( agentsTable ).find( 'tbody' ).append( agents_html );

        jQuery( 'input.referral-check[data-checked="false"]' ).prop( 'checked', false );
        jQuery( 'input.referral-check[data-checked="true"]' ).prop( 'checked', true );

        jQuery( 'input.sale-check[data-checked="false"]' ).prop( 'checked', false );
        jQuery( 'input.sale-check[data-checked="true"]' ).prop( 'checked', true );

    }

    jQuery( summaryTable ).find( 'tbody' ).append( summary_html );

}

function displayCumulativeData( dataSet, skippedAgents ) {

    let totalAppts = 0;
    let totalPres = 0;
    let totalSales = 0;
    let totalAP = 0;
    let totalRefAppts = 0;
    let totalRefPres = 0;
    let totalRefSales = 0;
    let totalRefAP = 0;

    let totalAPperApptTest = 0;
    let totalPresPercentTest = 0;
    let totalClosePercentTest = 0;
    let totalAppPercentRefTest = 0;
    let totalPresPercentRefTest = 0;
    let totalAPPercentRefTest = 0;
    let totalAllClosingPercentTest = 0;
    let totalRefClosingPercentTest = 0;

    let totalAPperAppt = 0;
    let totalPresPercent = 0;
    let totalClosePercent = 0;
    let totalAppPercentRef = 0;
    let totalPresPercentRef = 0;
    let totalAPPercentRef = 0;
    let totalAllClosingPercent = 0;
    let totalRefClosingPercent = 0;

    jQuery( '.agent-stats-cumulative-table tr.cummulative-row' ).remove();

    for (i = 0; i < dataSet.length; i++) {

        totalAppts += isNaN( parseInt( dataSet[i].appts ) ) ? 0 : parseInt( dataSet[i].appts );
        totalPres += isNaN( parseInt( dataSet[i].pres ) ) ? 0 : parseInt( dataSet[i].pres );
        totalSales += isNaN( parseInt( dataSet[i].sales ) ) ? 0 : parseInt( dataSet[i].sales );

        const APToAdd = isNaN( parseFloat( dataSet[i].AP ).toFixed( 2 ) ) ?
                        0 :
                        parseFloat( dataSet[i].AP ).toFixed( 2 );
        totalAP = totalAP + parseFloat( APToAdd );

        totalRefAppts += isNaN( parseInt( dataSet[i].apptsRef ) ) ? 0 : parseInt( dataSet[i].apptsref );
        totalRefPres += isNaN( parseInt( dataSet[i].refpres ) ) ? 0 : parseInt( dataSet[i].refpres );
        totalRefSales += isNaN( parseInt( dataSet[i].refsales ) ) ? 0 : parseInt( dataSet[i].refsales );

        totalAPperApptTest = (dataSet[i].AP / dataSet[i].appts);
        totalPresPercentTest = (dataSet[i].pres * 100 / dataSet[i].appts);
        totalClosePercentTest = (dataSet[i].sales * 100 / dataSet[i].pres);
        totalAppPercentRefTest = (dataSet[i].apptsref * 100 / dataSet[i].appts);
        totalPresPercentRefTest = (dataSet[i].refpres * 100 / dataSet[i].pres);
        totalAPPercentRefTest = (dataSet[i].refAP * 100 / dataSet[i].AP);
        totalAllClosingPercentTest = (dataSet[i].sales * 100 / dataSet[i].pres);
        totalRefClosingPercentTest = (dataSet[i].refsales * 100 / dataSet[i].refpres);

        totalAPperAppt +=
            isNaN( totalAPperApptTest ) || !isFinite( totalAPperApptTest ) ? 0 : parseFloat( totalAPperApptTest );
        totalPresPercent +=
            isNaN( totalPresPercentTest ) || !isFinite( totalPresPercentTest ) ? 0 : parseFloat( totalPresPercentTest );
        totalClosePercent +=
            isNaN( totalClosePercentTest ) || !isFinite( totalClosePercentTest ) ?
            0 :
            parseFloat( totalClosePercentTest );
        totalAppPercentRef +=
            isNaN( totalAppPercentRefTest ) || !isFinite( totalAppPercentRefTest ) ?
            0 :
            parseFloat( totalAppPercentRefTest );
        totalPresPercentRef +=
            isNaN( totalPresPercentRefTest ) || !isFinite( totalPresPercentRefTest ) ?
            0 :
            parseFloat( totalPresPercentRefTest );
        totalAPPercentRef +=
            isNaN( totalAPPercentRefTest ) || !isFinite( totalAPPercentRefTest ) ?
            0 :
            parseFloat( totalAPPercentRefTest );
        totalAllClosingPercent +=
            isNaN( totalAllClosingPercentTest ) || !isFinite( totalAllClosingPercentTest ) ?
            0 :
            parseFloat( totalAllClosingPercentTest );
        totalRefClosingPercent +=
            isNaN( totalRefClosingPercentTest ) || !isFinite( totalRefClosingPercentTest ) ?
            0 :
            parseFloat( totalRefClosingPercentTest );

        const RefAPToAdd = isNaN( parseFloat( dataSet[i].refAP ).toFixed( 2 ) ) ?
                           0 :
                           parseFloat( dataSet[i].refAP ).toFixed( 2 );
        totalRefAP = totalRefAP + parseFloat( RefAPToAdd );

        jQuery( '#main-agent-stats-cumulative-table' )
            .append( '<tr class="cummulative-row">'
                     + '<td class="text-center"><img alt="edit" src="'
                     + isr_urls.plugins_url
                     + '/gravity-isr/assets/images/edit-record.png" class="ci-edit"/></td>'
                     + '<td class="cumulative-data">'
                     + dataSet[i].agNum
                     + '</td>'
                     + '<td class="cumulative-data">'
                     + dataSet[i].agName
                     + '</td>'
                     + '<td class="cumulative-data"><span>$'
                     + (dataSet[i].AP / dataSet[i].appts).toFixed( 2 ).toString()
                     + '</span><span class="datum-test-val">$'
                     + (dataSet[i].AP / dataSet[i].appts).toFixed( 2 ).toString()
                     + '</span></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].pres * 100 / dataSet[i].appts).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].pres * 100 / dataSet[i].appts).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].sales * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].sales * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].apptsref * 100 / dataSet[i].appts).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].apptsref * 100 / dataSet[i].appts).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].refpres * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].refpres * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].refAP * 100 / dataSet[i].AP).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].refAP * 100 / dataSet[i].AP).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].appts
                     + '<input class="datum-test datum-test-appts" id="apptstest" type="text" value="'
                     + dataSet[i].appts
                     + '"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].pres
                     + '<input class="datum-test datum-test-pres" id="prestest" type="text" value="'
                     + dataSet[i].pres
                     + '"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].sales
                     + '<input class="datum-test datum-test-sales" id="salestest" type="text" value="'
                     + dataSet[i].sales
                     + '"/></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].sales * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].sales * 100 / dataSet[i].pres).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>$'
                     + dataSet[i].AP.toFixed( 2 ).toString()
                     + '</span><input class="datum-test datum-test-ap" id="aptest" type="text" value="'
                     + dataSet[i].AP.toFixed( 2 )
                     + '"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].apptsref
                     + '<input class="datum-test datum-test-apptsref" id="apptsreftest" type="text" value="'
                     + dataSet[i].apptsref
                     + '"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].refpres
                     + '<input class="datum-test datum-test-refpres" id="refprestest" type="text" value="'
                     + dataSet[i].refpres
                     + '"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">'
                     + dataSet[i].refsales
                     + '<input class="datum-test datum-test-refsales" id="refsales" type="text" value="'
                     + dataSet[i].refsales
                     + '"/></td>'
                     + '<td class="cumulative-data"><span>'
                     + (dataSet[i].refsales * 100 / dataSet[i].refpres).toFixed( 2 ).toString()
                     + '%</span><span class="datum-test-val">'
                     + (dataSet[i].refsales * 100 / dataSet[i].refpres).toFixed( 2 ).toString()
                     + '%</span></td>'
                     + '<td class="cumulative-data"><span>$'
                     + dataSet[i].refAP.toFixed( 2 ).toString()
                     + '</span><input class="datum-test datum-test-refap" id="refaptest" type="text" value="'
                     + dataSet[i].refAP.toFixed( 2 )
                     + '"/></td>'
                     + '</tr>' );

    }

    jQuery( '#agent-stats-cumulative-totals-table' ).closest( 'div.row' ).remove();

    jQuery( '<div class="row">'
            + '<div class="col-12"> '
            + '<table id="agent-stats-cumulative-totals-table" class="agent-stats-cumulative-table table table-striped collapse in">'
            + '<tr class="cummulative-row">'
            + '<td></td>'
            + '<td class="sub-heading heading-transparent" scope="col">Agent No.</td>'
            + '<td class="sub-heading heading-transparent" scope="col">Agent Name</td>'
            + '<td class="sub-heading" scope="col">AP/Appt.</td>'
            + '<td class="sub-heading" scope="col">Pres.%</td>'
            + '<td class="sub-heading" scope="col">Closing %</td>'
            + '<td class="sub-heading" scope="col">Appt. % Ref.</td>'
            + '<td class="sub-heading" scope="col">Pres. % Ref.</td>'
            + '<td class="sub-heading" scope="col">AP % Ref.</td>'
            +

            '<td class="sub-heading ab-cell" scope="col">Appts.</td>'
            + '<td class="sub-heading ab-cell" scope="col">Pres.</td>'
            + '<td class="sub-heading ab-cell" scope="col">Sales</td>'
            + '<td class="sub-heading ab-cell" scope="col">Closing %</td>'
            + '<td class="sub-heading ab-cell" scope="col">Total AP</td>'
            +

            '<td class="sub-heading rb-cell" scope="col">Appts.</td>'
            + '<td class="sub-heading rb-cell" scope="col">Pres.</td>'
            + '<td class="sub-heading rb-cell" scope="col">Sales</td>'
            + '<td class="sub-heading rb-cell" scope="col">Closing %</td>'
            + '<td class="sub-heading rb-cell" scope="col">Total AP</td>'
            + '</tr>'
            + '<tr class="cummulative-row">'
            + '<td></td>'
            + '<td class="cumulative-data"><strong>TOTALS</strong></td>'
            + '<td class="cumulative-data"></td>'
            + '<td class="cumulative-data">$'
            + (totalAPperAppt / dataSet.length).toFixed( 2 )
            + '</td>'
            + '<td class="cumulative-data">'
            + (totalPresPercent / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">'
            + (totalClosePercent / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">'
            + (totalAppPercentRef / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">'
            + (totalPresPercentRef / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">'
            + (totalAPPercentRef / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalAppts.toString()
            + '</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalPres.toString()
            + '</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalSales.toString()
            + '</td>'
            + '<td class="cumulative-data">'
            + (totalAllClosingPercent / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">$'
            + totalAP.toFixed( 2 ).toString()
            + '</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalRefAppts.toString()
            + '</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalRefPres.toString()
            + '</td>'
            + '<td class="cumulative-data cumulative-data-centered">'
            + totalRefSales.toString()
            + '</td>'
            + '<td class="cumulative-data">'
            + (totalRefClosingPercent / dataSet.length).toFixed( 2 )
            + '%</td>'
            + '<td class="cumulative-data">$'
            + totalRefAP.toFixed( 2 ).toString()
            + '</td>'
            + '</tr>'
            + '</table>'
            + '</div>'
            + '</div>' ).insertAfter( '#period-container' );

    //Add skipped agents
    for (i = 0; i < skippedAgents.length; i++) {
        jQuery( '#main-agent-stats-cumulative-table' )
            .append( '<tr class="cummulative-row">'
                     + '<td class="text-center"><img alt="edit" src="'
                     + isr_urls.plugins_url
                     + '/gravity-isr/assets/images/edit-record.png" class="ci-edit-disabled"/></td>'
                     + '<td class="cumulative-data">'
                     + skippedAgents[i].num
                     + '</td>'
                     + '<td class="cumulative-data">'
                     + skippedAgents[i].name
                     + '</td>'
                     + '<td class="cumulative-data"><span>$0.00</span><span class="datum-test-val">$0.00'
                     + '<td class="cumulative-data"><span>0%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-appts" id="apptstest" type="text" value="0"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-pres" id="prestest" type="text" value="0"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-sales" id="salestest" type="text" value="0"/></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>$0.00</span><input class="datum-test datum-test-ap" id="aptest" type="text" value="0.00"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-apptsref" id="apptsreftest" type="text" value="0"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-refpres" id="refprestest" type="text" value="0"/></td>'
                     + '<td class="cumulative-data cumulative-data-centered">0<input class="datum-test datum-test-refsales" id="refsales" type="text" value="0"/></td>'
                     + '<td class="cumulative-data"><span>0.00%</span><span class="datum-test-val">0.00%</span></td>'
                     + '<td class="cumulative-data"><span>$0.00</span><input class="datum-test datum-test-refap" id="refaptest" type="text" value="0.00"/></td>'
                     + '</tr>' );
    }

    const cumulativeCells = jQuery( 'td.cumulative-data' ).not( '.cumulative-data-centered' ).get();

    for (i = 0; i < cumulativeCells.length; i++) {

        if ( jQuery( cumulativeCells[i] ).find( 'span:first-child' ).text().includes( 'NaN' ) ) {
            jQuery( cumulativeCells[i] ).find( 'span:first-child' ).text( 'N/A' );
            jQuery( cumulativeCells[i] ).find( '.datum-test-val' ).text( 'N/A' );
        }

    }

}