jQuery( document ).ready( function () {
    var preset_days = [];
    var dayindex = -1;

    //Choose a preset day
    jQuery( '.preset-appt-day' ).click( function () {
        if ( jQuery( this ).is( ':checked' ) ) {
            preset_days.push( jQuery( this ).val() );
        }
        else {
            dayindex = preset_days.indexOf( jQuery( this ).val() );
            if ( dayindex > -1 ) {
                preset_days.splice( dayindex, 1 );
            }
        }
        jQuery( '#preset-appt-days' ).val( preset_days.toString() );
    } );
} );