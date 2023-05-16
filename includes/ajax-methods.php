<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function fetch_data(): void {

	$Ops = new ISROps();

	//Get workforce that reports to the current user
	$workforce = explode( ',', $_POST['agents'] );

	//Get calendar invite entries
	$ciEntries = $Ops->GetInvites( $_POST['start'], $_POST['end'] );

	//Get presentation submission entries
	$wcnEntries = $Ops->GetPresentations( $_POST['start'], $_POST['end'] );

	//Retrieve stats for all business
	$resultsAB = buildBusinessStats( $ciEntries, $wcnEntries, $workforce, false );

	//Retrieve stats for referral business only
	$resultsRB = buildBusinessStats( $ciEntries, $wcnEntries, $workforce, true );

	$resultsCU = $resultsAB['agentStatsCumulative'];
	$refCU     = $resultsRB['agentStatsCumulative'];

	for ( $i = 0; $i < sizeof( $resultsCU ); $i ++ ) {
		for ( $j = 0; $j < sizeof( $refCU ); $j ++ ) {
			if ( $resultsCU[ $i ]['agNum'] == $refCU[ $j ]['agNum'] ) {
				$resultsCU[ $i ]['refsales']    = $refCU[ $j ]['refsales'];
				$resultsCU[ $i ]['refAP']       = $refCU[ $j ]['refAP'];
				$resultsCU[ $i ]['apptsref']    = $refCU[ $j ]['apptsref'];
				$resultsCU[ $i ]['apptsrefpre'] = $refCU[ $j ]['apptsrefpre'];
				$resultsCU[ $i ]['refpres']     = $refCU[ $j ]['refpres'];

			}
		}
	}

	echo json_encode( array(
			'all_business'      => $resultsAB,
			'referral_business' => $resultsRB,
			'agents_cumulative' => $resultsCU,
			'skipped_workforce' => $resultsAB['skippedWorkForce'],
			'allnums'           => $resultsAB['allnums'],
		)
	);
	wp_die();
}

function buildBusinessStats( $ciEntries, $wcnEntries, $workforce, $isReferral ): array {
	global $wpdb;

	//Initialize ISR operations object
	$Ops                    = new ISROps();
	$tablesData             = array();                          //Data used to populate summary & agent tables
	$appointmentsSet        = 0;                                //Appointment count
	$appointmentsSetPre     = 0;                                //Preset appointment count
	$found                  = '';
	$apptByAg               = array();                          //Appointments set by agent
	$apptPreByAg            = array();                          //Pre-Appointments set by agent
	$apptRefByAg            = array();                          //Referral Appointments set by agent
	$apptRefPreByAg         = array();                          //Referral Pre-Appointments set by agent
	$daysForPreSet          = get_option( 'preset_appt_days' ); //Appointments set on particular days are considered preset
	$presentationsCompleted = 0;                                //Presentations that actually tok place
	$salesCompleted         = 0;                                //Number of presentations that converted into sales
	$closingPercentage      = 0;                                //Percentage of presentations that became sales
	$referralsCollected     = 0;                                //Number of referrals collected by an agent
	$totalAP                = 0;                                //Total of all annual premiums from agents
	$ciUIDs                 = array();                          //Entry IDs of calendar invites to be carried forward to find matching submissions
	$ag                     = array();                          //
	$agentStats             = array();                          //Stores agent-specific stats per WCN submission
	$agentStatsCumulative   = array();                          //Stores cumulative agent-specific stats per agent
	$useEntry               = true;                             //Whether to use a particular entry or not
	$skippedWorkForce       = $workforce;                       //Records agents in sent workforce but without data
	$allnums                = array();                          //

	//Loop through all calendar invites
	foreach ( $ciEntries as $entry ) {
		if ( $isReferral ) {    //If we are retrieving referrals ONLY, check the invite type
			if ( $entry['33'] != 'pos' ) {
				$useEntry = true;
			} else {
				$useEntry = false;
			}
		}

		if ( $useEntry ) {//If the entry qualifies
			$agentNum = $entry['12'];
			array_push( $allnums, $agentNum );

			if ( in_array( $agentNum, $workforce ) && str_replace( ' ', '', $entry['23'] ) != '' ) {//If the agent is in the current user's workforce
				$appointmentsSet ++;
				$daySet = date( 'D', strtotime( $entry['date_created'] ) );

				//It's a preset appointment if it was made on a Tuesday or Thursday
				if ( in_array( strtolower( $daySet ), array_map( 'strtolower', $daysForPreSet ) ) ) {
					$appointmentsSetPre ++;

					//Store all pre-appointments by agent
					if ( sizeof( $apptPreByAg ) > 0 ) {
						foreach ( $apptPreByAg as $key => $value ) {
							if ( $key == $agentNum ) {
								$apptPreByAg[ $key ] = (int) $value + 1;
							}
						}

						if ( $found == false ) {
							$apptPreByAg[ $agentNum ] = 1;
						}
					} else {
						$apptPreByAg[ $agentNum ] = 1;
					}

					if ( $isReferral ) {

						//Store all referral pre-appointments by agent
						if ( sizeof( $apptRefPreByAg ) > 0 ) {
							$found = false;

							foreach ( $apptRefPreByAg as $key => $value ) {

								if ( $key == $agentNum ) {
									$apptRefPreByAg[ $key ] = (int) $value + 1;
									$found                  = true;
								}

							}

							if ( $found == false ) {
								$apptRefPreByAg[ $agentNum ] = 1;
							}
						} else {
							$apptRefPreByAg[ $agentNum ] = 1;
						}
					}

				}

				//Store all appointments by agent
				if ( sizeof( $apptByAg ) > 0 ) {

					$found = false;

					foreach ( $apptByAg as $key => $value ) {

						if ( $key == $agentNum ) {
							$apptByAg[ $key ] = (int) $value + 1;
							$found            = true;
						}

					}

					if ( $found == false ) {

						$apptByAg[ $agentNum ] = 1;

					}

				} else {
					$apptByAg[ $agentNum ] = 1;
				}

				if ( $isReferral ) {

					//Store all referral appointments by agent
					if ( sizeof( $apptRefByAg ) > 0 ) {

						$found = false;

						foreach ( $apptRefByAg as $key => $value ) {

							if ( $key == $agentNum ) {
								$apptRefByAg[ $key ] = (int) $value + 1;
								$found               = true;
							}

						}

						if ( $found == false ) {

							$apptRefByAg[ $agentNum ] = 1;

						}

					} else {

						$apptRefByAg[ $agentNum ] = 1;

					}

				}

				//Carry forward this entry id to extract agent specific data about the presentation
				array_push( $ciUIDs, $entry['23'] );

			}

		}

	}

	//Loop through presentation submissions
	foreach ( $wcnEntries as $entry ) {

		$ciUID = $entry['71'];

		//Get the associated agent number
		$refAgentNum = $entry['10'];

		//Check if it was one accpeted to be carried forward
		if ( in_array( $ciUID, $ciUIDs ) ) {

			array_push( $ag, $agentNum );

			//Remove the agent from those who are to be skipped
			$agentIndex = array_search( $refAgentNum, $skippedWorkForce );

			if ( $agentIndex != '' && $agentIndex != null ) {
				unset( $skippedWorkForce[ $agentIndex ] );
			}

			//$skippedWorkForce = array_values( $skippedWorkForce );

			$presentationsCompleted ++;

			$refpres = 0;
			$refAP   = 0;

			$referralsCollected += (int) $entry['76'];

			$wasSale = strtolower( $entry['60'] ) != 'yes' ? 'no' : strtolower( $entry['60'] );

			$wasReferral = strtolower( $entry['82'] ) != 'pos' ? 'true' : 'false';

			$agentUserID = $wpdb->get_var(
				"SELECT user_id FROM " . $wpdb->prefix . "usermeta " .
				"WHERE meta_key='agent_number' AND meta_value='" . $refAgentNum . "'"
			);

			$agentFullName = get_user_meta( $agentUserID, 'first_name', true ) . ' ' . get_user_meta( $agentUserID, 'last_name', true );

			$apType = '';

			switch ( $entry['82'] ) {

				case 'pos':
					$apType = 'POS Appt.';
					break;

				case 'p750k':
					$apType = 'P750K';
					break;

				case 'cs':
					$apType = 'CSK';
					break;

				case 'adb':
					$apType = '$3,000 ADB';
					break;

				case 'ref':
					$apType = 'Referral';
					break;

			}

			$event_date           = date_create( $entry['date_created'] );
			$event_date_formatted = date_format( $event_date, "m/d/Y h:i" );

			$agentStat = array(
				'agNum'         => $refAgentNum,
				'agName'        => ucwords( $agentFullName ),
				'apType'        => $apType,
				'refsCollected' => (int) $entry['76'],
				'wasSale'       => $wasSale,
				'wasReferral'   => $wasReferral,
				'AP'            => '$' . (string) number_format( (float) $entry['68'], 2, '.', ',' ),
				'date'          => $event_date_formatted,
				'ap_per_appt'   => '$' . (string) number_format( (float) ( $totalAP / $appointmentsSet ), 2, '.', ',' ),
				'pres_percent'  => (string) number_format( (float) ( $presentationsCompleted * 100 / $appointmentsSet ), 2, '.', ',' ) . '%',
				'closing'       => (string) $closingPercentage . '%',
			);

			array_push( $agentStats, $agentStat );

			if ( $wasSale == 'yes' ) {
				$sales = 1;
			} else {
				$sales = 0;
			}

			if ( $wasSale == 'yes' && $wasReferral == 'true' ) {
				$refsales = 1;
			} else {
				$refsales = 0;
			}

			if ( $wasReferral == 'true' ) {
				$refpres = 1;
				$refAP   = (float) $entry['68'];
			}

			if ( sizeof( $agentStatsCumulative ) > 0 ) {

				$found = false;

				for ( $i = 0; $i < sizeof( $agentStatsCumulative ); $i ++ ) {

					if ( $agentStatsCumulative[ $i ]['agNum'] == $refAgentNum ) {

						$agentStatsCumulative[ $i ]['refsCollected'] = $agentStatsCumulative[ $i ]['refsCollected'] + (int) $entry['76'];
						$agentStatsCumulative[ $i ]['sales']         = $agentStatsCumulative[ $i ]['sales'] + $sales;
						$agentStatsCumulative[ $i ]['refsales']      = $agentStatsCumulative[ $i ]['refsales'] + $refsales;
						$agentStatsCumulative[ $i ]['AP']            = $agentStatsCumulative[ $i ]['AP'] + (float) $entry['68'];
						$agentStatsCumulative[ $i ]['refAP']         = $agentStatsCumulative[ $i ]['refAP'] + $refAP;
						$agentStatsCumulative[ $i ]['appts']         = $apptByAg[ $refAgentNum ];
						$agentStatsCumulative[ $i ]['apptspre']      = $apptPreByAg[ $refAgentNum ];
						$agentStatsCumulative[ $i ]['apptsref']      = $apptRefByAg[ $refAgentNum ];
						$agentStatsCumulative[ $i ]['apptsrefpre']   = $apptRefPreByAg[ $refAgentNum ];
						$agentStatsCumulative[ $i ]['pres']          = $agentStatsCumulative[ $i ]['pres'] + 1;
						$agentStatsCumulative[ $i ]['refpres']       = $agentStatsCumulative[ $i ]['refpres'] + $refpres;
						$found                                       = true;

					}

				}

				if ( $found == false ) {

					$agentStatCumulative = array(
						'agNum'         => $refAgentNum,
						'agName'        => ucwords( $agentFullName ),
						'refsCollected' => (int) $entry['76'],
						'sales'         => $sales,
						'refsales'      => $refsales,
						'AP'            => (float) $entry['68'],
						'refAP'         => $refAP,
						'appts'         => $apptByAg[ $refAgentNum ],
						'apptspre'      => $apptPreByAg[ $refAgentNum ],
						'apptsref'      => $apptRefByAg[ $refAgentNum ],
						'apptsrefpre'   => $apptRefPreByAg[ $refAgentNum ],
						'pres'          => 1,
						'refpres'       => $refpres,
					);

					array_push( $agentStatsCumulative, $agentStatCumulative );

				}

			} else {

				$agentStatCumulative = array(
					'agNum'         => $refAgentNum,
					'agName'        => ucwords( $agentFullName ),
					'refsCollected' => (int) $entry['76'],
					'sales'         => $sales,
					'refsales'      => $refsales,
					'AP'            => (float) $entry['68'],
					'refAP'         => $refAP,
					'appts'         => $apptByAg[ $refAgentNum ],
					'apptspre'      => $apptPreByAg[ $refAgentNum ],
					'apptsref'      => $apptRefByAg[ $refAgentNum ],
					'apptsrefpre'   => $apptRefPreByAg[ $refAgentNum ],
					'pres'          => 1,
					'refpres'       => $refpres,
				);

				array_push( $agentStatsCumulative, $agentStatCumulative );

			}


			//Check if the presentation resulted in a sale
			if ( strtolower( $entry['60'] ) == 'yes' ) {

				$salesCompleted ++;

				$totalAP += $entry['68'];

			}

		}

	}

	//Build the array for html creation
	if ( $presentationsCompleted == 0 ) {
		$closingPercentage = 0;
	} else {
		$closingPercentage = number_format( (float) ( ( $salesCompleted / $presentationsCompleted ) * 100 ), 2, '.', '' );
	}

	$tablesData['summary'] = array(
		'presentations' => $presentationsCompleted,
		'sales'         => $salesCompleted,
		'closing'       => (string) $closingPercentage . '%',
		'raw_total'     => $totalAP,
		'total'         => '$' . (string) number_format( (float) $totalAP, 2, '.', ',' ),
	);

	$tablesData['agent_summary'] = $agentStats;

	$tablesData['referrals'] = $referralsCollected;

	$tablesData['appts'] = $appointmentsSet;

	$tablesData['apptsPre'] = $appointmentsSetPre;

	$tablesData['agentStatsCumulative'] = $agentStatsCumulative;

	//Add skipped ahent data
	$skippedAgents = array();
	foreach ( $skippedWorkForce as $agentNum ) {
		$agentUserID   = $wpdb->get_var(
			"SELECT user_id FROM " . $wpdb->prefix . "usermeta " .
			"WHERE meta_key='agent_number' AND meta_value='" . $agentNum . "'"
		);
		$agentFullName = get_user_meta( $agentUserID, 'first_name', true ) . ' ' . get_user_meta( $agentUserID, 'last_name', true );
		array_push( $skippedAgents, array(
			'num'  => $agentNum,
			'name' => ucwords( $agentFullName ),
		) );
	}

	$tablesData['skippedWorkForce'] = $skippedAgents;

	$tablesData['allnums'] = $allnums;

	return $tablesData;

}

?>