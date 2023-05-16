<?php

/* Template Name: Insurance Sales Report Page */

if( !is_user_logged_in() ){

    header( 'Location: ' . home_url() );
    
}

get_header(); 

$user = wp_get_current_user();

?>

<div class="container my-5">
    <div class="row my-3">
        <div class="col-12 px-0">
            <h1 class="my-0"><?php echo $user->user_firstname . ' ' . $user->user_lastname;?></h1>
        </div>
        <div class="col-12 px-0 mb-3">
            <h4 class="my-0"><?php echo get_user_meta( $user->ID, 'agent_position', true ); ?></h4>
        </div>
    </div>

    <!-- <div class="row mt-1 mb-3">
        <h2>Calendar Invites</h2>
    </div> -->
    <div class="row mb-2">
        <div class="col-md-8">
            <div class="row">
                <div style="padding-left: 0;" class="col-md-3 my-auto">
                    <small class="font-weight-bold">Date Range:</small>
                </div>
                <div class="col-md-3 my-auto">
                    <div class="form-group text-center">
                        <small class="isr-date-label">START DATE</small>
                        <input type="date" value="<?php

                                    $lastTuesday = strtotime('tuesday last week');
                        
                                    echo date( 'Y-m-d', $lastTuesday);
                        
                                ;?>" class="form-control" id="invite-start-date"/>
                    </div>
                </div>
                <div class="col-md-3 my-auto">
                    <div class="form-group text-center">
                        <small class="isr-date-label">END DATE</small>
                        <input type="date" value="<?php echo date( 'Y-m-d' );?>" class="form-control" id="invite-end-date"/>
                    </div>
                </div>
            </div>            
        </div>
    </div>
    <?php

    $Ops = new ISROps();
        
    //Get workforce that reports to the current user
    $workforce = $Ops->GetWorkForce( get_current_user_id() );

    $pulled_agents = array();
   
    if( sizeof( $workforce ) > 0 ){
    ?>
    <div class="row mb-3">
        <div class="col-12 my-auto">
            <small class="font-weight-bold">Choose Agents:</small>&nbsp;&nbsp;&nbsp;
        </div>
        <div class="col-12 my-3">
            <div class="form-check form-check-inline">
                <input checked class="form-check-input" type="checkbox" id="chkAllAgents"/>
                <label class="form-check-label" for="chkAllAgents">All</label>
            </div>
        </div>
        <div id="workforce" class="col-12">

            <?php 
            
                //Get distinct positions
                $positions = array();

                foreach( $workforce as $agent ){
                    if( !in_array( $agent[ 'position' ], $positions ) ){
                        array_push( $positions, $agent[ 'position' ] );
                        array_push( $pulled_agents, $agent[ 'ID' ] );
                        array_push( $pulled_agents, $agent[ 'name' ] );
                    }
                }

                // echo '<pre>' . var_dump($pulled_agents) .'</pre>';
            
            ?>

            <div class="row">

                <?php

                $hiddenPositions = array();
                $hiddenPositions = get_option( 'hidden_positions' );
                if( $hiddenPositions == null || sizeof( $hiddenPositions ) == 0 ){
                    $hiddenPositions = array();
                }
                
                foreach( $positions as $position ){
                    $hideColumnClass = '';
                    $hideAgentClass = '';
                    if( in_array( strtolower( $position ), array_map( 'strtolower', $hiddenPositions ) ) ){
                        $hideColumnClass = 'hide-agent-col';
                        $hideAgentClass = 'hide-agent';
                    }
                    ?>
                    <div id="<?php echo 'position-' . str_replace( ' ', '-', $position );?>" class="col-lg-2 agent-position-col <?php echo $hideColumnClass;?>">
                    <h4 class="position-header"><?php echo ucwords( $position );?></h4>
                    <?php 
                    
                        foreach( $workforce as $agent ){
                            // echo 'BEFORE LOGIC <br /><pre>' . var_dump( $agent ) . '</pre>';
                            $userAgentClass = $agent[ 'parentID' ] == '' ? ' is-user-agent':'';
                            if( $agent[ 'position' ] == $position ){
                                // echo 'AFTER LOGIC <br /><pre>' . var_dump( $agent ) . '</pre>';
                                ?>
                                <div data-indent="<?php echo $agent[ 'indent' ];?>">
                                <input checked type="checkbox" data-parent="<?php echo $agent[ 'parentID' ];?>"                         
                                class="form-check-input agent-number<?php echo $userAgentClass . ' ' . $hideAgentClass;?>" 
                                id="<?php echo $agent[ 'id' ];?>" value="<?php echo $agent[ 'id' ];?>"/>
                                <label class="form-check-label" 
                                for="<?php echo $agent[ 'id' ];?>">
                                    <?php 
                                        echo $userAgentClass == '' ? ucwords( $agent[ 'name' ] ):'Me';
                                    ?>
                                </label></div>
                                <?php
                            }
                        }
                    
                    ?>
                    </div>
                    <?php
                }

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

                //             ?>

                //             <div data-indent="<?php //echo $agent[ 'indent' ];?>" style="margin-left: <?php //echo $agent[ 'indent' ];?>;">
                //             <input checked type="checkbox"                          
                //             class="form-check-input agent-number<?php //echo $userAgentClass;?>" 
                //             id="<?php //echo $agent[ 'id' ];?>" value="<?php //echo $agent[ 'id' ];?>"/>
                //             <label class="form-check-label" 
                //             for="<?php //echo $agent[ 'id' ];?>">
                //                 <?php 
                //                     echo $userAgentClass == '' ? ucwords( $agent[ 'name' ] ):'Me';
                //                 ?>
                //             </label></div>

                //             <?php                               

                //         }

                //     }

                // }


                ?> -->
            <!-- </div> -->
        </div>
    </div>
    <div class="row">        
        <div class="col-md-3 px-0 my-3">
            <div class="col-12">
                <button type="button" id="ci-search" 
                class="fusion-button button-default button-small my-auto">Search</button>
                <div class="spinner-border my-auto" role="status">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>
    </div>
    <?php } ;?>

    <div class="row">        
        <div class="col-12">
            <hr/>
        </div>
    </div>

    <div id="period-container" class="row">        
        <div class="col-12">
            Period: <span><strong class="period-label period-label-from"></strong></span> To <span><strong class="period-label period-label-to"></strong></span>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">            
            <table id="main-agent-stats-cumulative-table" class="agent-stats-cumulative-table table table-striped collapse in">
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
                        <td class="sub-heading" scope="col">Agent No.</td>
                        <td class="sub-heading" scope="col">Agent Name</td>
                        <td class="sub-heading" scope="col">AP/Appt.</td>
                        <td class="sub-heading" scope="col">Pres.%</td>
                        <td class="sub-heading" scope="col">Closing %</td>
                        <td class="sub-heading" scope="col">Appt. % Ref.</td>
                        <td class="sub-heading" scope="col">Pres. % Ref.</td>
                        <td class="sub-heading" scope="col">AP % Ref.</td>

                        <td class="sub-heading ab-cell" scope="col">Appts.</td>
                        <td class="sub-heading ab-cell" scope="col">Pres.</td>
                        <td class="sub-heading ab-cell" scope="col">Sales</td>
                        <td class="sub-heading ab-cell" scope="col">Closing %</td>
                        <td class="sub-heading ab-cell" scope="col">Total AP</td>

                        <td class="sub-heading rb-cell" scope="col">Appts.</td>
                        <td class="sub-heading rb-cell" scope="col">Pres.</td>
                        <td class="sub-heading rb-cell" scope="col">Sales</td>
                        <td class="sub-heading rb-cell" scope="col">Closing %</td>
                        <td class="sub-heading rb-cell" scope="col">Total AP</td>
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
</div>


<?php

get_footer();

?>