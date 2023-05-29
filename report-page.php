<?php

/* Template Name: Insurance Sales Report Page */
if ( ! is_user_logged_in() ) {
	header( 'Location: ' . home_url() );
}

get_header();

$user = wp_get_current_user();

$tuesday_last_week = date( 'Y-m-d', strtotime( 'tuesday last week' ) );
$today             = date( 'Y-m-d' );

$user_full_name = $user->user_firstname . ' ' . $user->user_lastname;

?>


    <div class="container my-5"> <!-- Begin plugin container -->
    <div class="row my-3">
        <div class="col-12 px-0">
            <h1 class="my-0"><?php echo $user_full_name; ?></h1>
        </div>

        <div class="col-12 px-0 mb-3">
            <h4 class="my-0"><?php echo get_user_meta( $user->ID, 'agent_position', true ); ?></h4>
        </div>
    </div> <!-- end header row -->

    <div class="row mb-2"> <!-- Begin date range row -->
        <div class="col-md-8">
            <div class="row">
                <div style="padding-left: 0;" class="col-md-3 my-auto">
                    <small class="font-weight-bold">Date Range:</small>
                </div>

                <div class="col-md-3 my-auto">
                    <div class="form-group text-center">
                        <label class="isr-date-label">START DATE
                            <input type="date" value="<?php echo $tuesday_last_week; ?>" class="form-control"
                                   id="invite-start-date"/>
                        </label>
                    </div>
                </div>

                <div class="col-md-3 my-auto">
                    <div class="form-group text-center">
                        <label class="isr-date-label">END DATE
                            <input type="date" value="<?php echo $today; ?>" class="form-control" id="invite-end-date"/>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php

$ops = new ISROps();


//Get workforce that reports to the current user

$workforce = $ops->GetWorkForce( $user->ID );
	echo '<div style="max-height:33vh; overflow-y: scroll;font-size:0.7em;"><pre>';
	print_r( $workforce );
	echo '</pre></div>';
$pulled_agents = array();

if ( sizeof( $workforce ) > 0 ) {
	echo <<<HTML
				<div class="row mb-3">
					<div class="col-12 my-auto">
						<small class="font-weight-bold">Choose Agents:</small>&nbsp;&nbsp;&nbsp;
					</div>
					<div class="col-12 my-3">
						<div class="form-check form-check-inline">
							<label class="form-check-label" for="chkAllAgents">
								<input checked class="form-check-input" type="checkbox" id="chkAllAgents"/>
								All
							</label>
						</div>
					</div>
					<div id="workforce" class="col-12"> <!-- Begin workforce row -->
HTML;

	//Get distinct positions
	$positions = array();
//		echo '<pre>';
//		print_r( $workforce );
//		echo '</pre>';

	foreach ( $workforce as $agent ) {
        if ( isset($agent['viewing_as']) ) {
            continue;
        }
		if ( ! in_array( $agent['position'], $positions ) ) {
			$positions[]     = $agent['position'];
			$pulled_agents[] = $agent['id'];
			$pulled_agents[] = $agent['name'];
		}
	}


	echo '<div class="row">';
	$hiddenPositions = array();
	$hiddenPositions = get_option( 'hidden_positions' );

	if ( $hiddenPositions == null || sizeof( $hiddenPositions ) == 0 ) {
		$hiddenPositions = array();
	}

	foreach ( $positions as $position ) {
		$hideColumnClass = '';
		$hideAgentClass  = '';

		if ( in_array( strtolower( $position ), array_map( 'strtolower', $hiddenPositions ) ) ) {
			$hideColumnClass = 'hide-agent-col';
			$hideAgentClass  = 'hide-agent';
		}
		?>

        <div id="<?php echo 'position-' . str_replace( ' ', '-', $position ); ?>"
             class="col-lg-2 agent-position-col <?php echo $hideColumnClass; ?>">

            <h4 class="position-header"><?php echo ucwords( $position ); ?></h4>

			<?php

			foreach ( $workforce as $agent ) {
				if ( isset($agent['viewing_as']) ) {
					continue;
				}
				$userAgentClass = $agent['parentID'] == '' ? ' is-user-agent' : '';
				if ( $agent['position'] == $position ) {

					?>

                    <div data-indent="<?php echo $agent['indent']; ?>">

                        <label class="form-check-label" for="<?php echo $agent['id']; ?>">
                            <input
                                    checked
                                    type="checkbox"
                                    data-parent="<?php echo $agent['parentID']; ?>"
                                    class="form-check-input agent-number<?php echo $userAgentClass . ' ' . $hideAgentClass; ?>"
                                    id="<?php echo $agent['id']; ?>"
                                    value="<?php echo $agent['id']; ?>"
                            />
							<?php
							// Label text
							echo $userAgentClass == '' ? ucwords( $agent['name'] ) : 'Me';
							?>
                        </label>
                    </div>
					<?php
				} // end position check
			} // end workforce loop
			?>

        </div>

		<?php
	} // end positions loop
	?>

    </div>


    <!-- <div class="row"> -->

    <!-- <?php


	// $agentCount = 0;


	// $parentIDs = array();


	// $parentIDGroups = array();


	// $theHierarchy = array();


	// $theHierarchyFixed = array();


	// foreach( $workforce as $agent ){


	//     if( $agent[ 'parentID' ] != '' ){


	//         array_push( $parentIDs, $agent[ 'parentID' ] );


	//     }


	//     $parentIDs = array_unique( $parentIDs );


	// }


	// foreach( $parentIDs as $parentID ){


	//     $parentIDGroups[ $parentID ] = array();


	//     foreach( $workforce as $agent ){


	//         if( $agent[ 'parentID' ] == $parentID ){


	//             array_push( $parentIDGroups[ $parentID ], $agent );


	//         }


	//     }


	// }


	// //Create hierarchical ordering of agent numbers

	// foreach( $parentIDGroups as $key => $value ){


	//     foreach( $workforce as $agent ){


	//         if( $agent[ 'id' ] == $key ){


	//             $theHierarchy[ $key ] = array();


	//             if( sizeof( $value ) > 0 ){


	//                 $sub = array();


	//                 foreach( $value as $agent ){


	//                     $sub[ $agent[ 'id' ] ] = array();


	//                 }


	//                 array_push( $theHierarchy[ $key ], $sub );


	//             }


	//         }


	//     }


	// }


	// $keyAt = 1;


	// $masterKey = '';


	// //Check Level 2 agents and add their sub-agents (Level 3)

	// foreach( $theHierarchy as $key => $value ){


	//     if( $keyAt == 1 ){

	//         $masterKey = $key;//This is the Level 1 agent (Agency Owner)

	//         $keyAt++;

	//         continue;

	//     }


	//     if( array_key_exists( $key, $theHierarchy[ $masterKey ][0] ) ){

	//         array_push( $theHierarchy[ $masterKey ][0][ $key ], $value );

	//         unset( $theHierarchy[ $key ] );

	//     }


	//     $keyAt++;


	// }


	// $keyAt = 1;


	// //Check Level 3 agents and add their sub-agents (Level 4)

	// foreach( $theHierarchy as $key => $value ){


	//     if( $keyAt == 1 ){

	//         $keyAt++;

	//         continue;

	//     }


	//     foreach( $theHierarchy[ $masterKey ][0] as $_key => $_value ){


	//         foreach( $_value[0][0] as $__key => $__value ){


	//             if( $key == $__key ){

	//                 array_push( $theHierarchy[ $masterKey ][0][ $_key ][0][0][ $__key ], $value[0] );

	//                 unset( $theHierarchy[ $key ] );

	//             }


	//         }


	//     }


	//     $keyAt++;


	// }


	// $keyAt = 1;


	// foreach( $theHierarchy as $key => $value ){


	//     array_push( $theHierarchyFixed, $key );


	//     if( sizeof( $value ) > 0 ){

	//         foreach( $value[0] as $_key => $_value ){


	//             array_push( $theHierarchyFixed, $_key );


	//             if( sizeof( $_value[0] ) > 0 ){

	//                 foreach( $_value[0][0] as $__key => $__value ){


	//                     array_push( $theHierarchyFixed, $__key );


	//                     if( sizeof( $__value ) > 0 ){

	//                         foreach( $__value[0] as $___key => $___value ){


	//                             array_push( $theHierarchyFixed, $___key );


	//                         }

	//                     }


	//                 }

	//             }


	//         }

	//     }


	//     $keyAt++;


	// }


	// foreach( $theHierarchyFixed as $num ) {


	//     foreach( $workforce as $agent ){


	//         if( $agent[ 'id' ] == $num ){


	//             $userAgentClass = $agent[ 'parentID' ] == '' ? ' is-user-agent':'';


	//
	?>



                //             <div data-indent="<?php //echo $agent[ 'indent' ];
	?>" style="margin-left: <?php //echo $agent[ 'indent' ];
	?>;">

                //             <input checked type="checkbox"                          

                //             class="form-check-input agent-number<?php //echo $userAgentClass;
	?>"

                //             id="<?php //echo $agent[ 'id' ];
	?>" value="<?php //echo $agent[ 'id' ];
	?>"/>

                //             <label class="form-check-label" 

                //             for="<?php //echo $agent[ 'id' ];
	?>">

                //                 <?php

	//                     echo $userAgentClass == '' ? ucwords( $agent[ 'name' ] ):'Me';

	//
	?>

                //             </label></div>



                //             <?php


	//         }


	//     }


	// }


	?> -->

    </div>
    </div>

    <div class="row">
        <div class="col-md-3 px-0 my-3">
            <div class="col-12">
                <button type="button" id="ci-search" class="fusion-button button-default button-small my-auto">
                    Get WAR Statistics
                </button>
                <div class="spinner-border my-auto" role="status">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>
    </div>

	<?php
}; // end of "if sizeof( $workforce ) > 0"
?>


    <div class="row">
        <div class="col-12">
            <hr/>
        </div>
    </div>

    <div id="period-container" class="row">
        <div class="col-12">
            Period: <span><strong class="period-label period-label-from"></strong></span> To <span><strong
                        class="period-label period-label-to"></strong></span>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <table id="main-agent-stats-cumulative-table"
                   class="agent-stats-cumulative-table table table-striped collapse in">
                <thead>
                <tr>
                    <th colspan="9" scope="colgroup">General</th>
                    <th class="ab-cell" colspan="5" scope="colgroup">All Business</th>
                    <th class="rb-cell" colspan="5" scope="colgroup">Referral Business</th>
                </tr>
                </thead>
                <tbody>
                <tr>

                    <td></td>
                    <td class="sub-heading">Agent No.</td>
                    <td class="sub-heading">Agent Name</td>
                    <td class="sub-heading">AP/Appt.</td>
                    <td class="sub-heading">Pres.%</td>
                    <td class="sub-heading">Closing %</td>
                    <td class="sub-heading">Appt. % Ref.</td>
                    <td class="sub-heading">Pres. % Ref.</td>
                    <td class="sub-heading">AP % Ref.</td>
                    <td class="sub-heading ab-cell">Appts.</td>
                    <td class="sub-heading ab-cell">Pres.</td>
                    <td class="sub-heading ab-cell">Sales</td>
                    <td class="sub-heading ab-cell">Closing %</td>
                    <td class="sub-heading ab-cell">Total AP</td>
                    <td class="sub-heading rb-cell">Appts.</td>
                    <td class="sub-heading rb-cell">Pres.</td>
                    <td class="sub-heading rb-cell">Sales</td>
                    <td class="sub-heading rb-cell">Closing %</td>
                    <td class="sub-heading rb-cell">Total AP</td>

                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-5">
            <table id="agent-stats-table" class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">Agent No.</th>
                    <th scope="col">Agent Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Appt. Type</th>
                    <th scope="col">Referral?</th>
                    <th scope="col"># Referrals</th>
                    <th scope="col">Sale?</th>
                    <th scope="col">Annual Premium</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

<?php
get_footer();
?>