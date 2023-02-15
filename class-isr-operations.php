<?php

class ISROps {

    //Get appointment invite details
    function GetInvites( $start_date, $end_date ){

        $finalEntries = array();

        $ciFormID = get_option( 'ci_form_id' );

        $searchCriteria = array();
        
        $searchCriteria['start_date'] = $start_date;
        $searchCriteria['end_date'] = $end_date;

        $allEntries = GFAPI::get_entries( $ciFormID, $searchCriteria, null, array('offset' => 0, 'page_size' => 1000) );

        return $allEntries;//$finalEntries;

    }

    //Get agent specific data on presentations
    function GetPresentations( $start_date, $end_date ){

        $finalPresentations = array();

        $wcnFormID = get_option( 'wcn_form_id' );

        $searchCriteria = array();
        
        $searchCriteria['start_date'] = $start_date;
        $searchCriteria['end_date'] = $end_date;

        $allPresentations = GFAPI::get_entries( $wcnFormID, $searchCriteria, null, array('offset' => 0, 'page_size' => 1000) );

        return $allPresentations;

    }

    //Get agents working under supervision of the crrent logged-in user
    function GetWorkForce( $user_id ){

        global $wpdb;
        
        $viewing_as = get_user_meta( $user_id, 'isr_view_sales_analysis_as', true );

        if( $viewing_as == null || $viewing_as == '' ){
            $viewing_as = $user_id;
        }
        
        $user = get_user_by( 'id', $viewing_as );
        
        $user_agent_number = get_user_meta( $viewing_as, 'agent_number', true );

        $user_position = strtolower( get_user_meta( $viewing_as, 'agent_position', true ) );
        
        $agentIDs = array();

        $extraIDs = array();

        $extraIDsExt = array();

        $SAAgentNums = array();

        $table = get_option( 'agents_table_name' );

        //Add the logged in agent's details
        array_push( $agentIDs, 
            array( 
                    'id' => $user_agent_number, 
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'indent' => '0px',
                    'parentID' => '',
                    'position' => $user_position,
                )
        );        

        //Get Agents
        $sa_agent_user_ids =   $wpdb->get_results(
            "SELECT user_id FROM $wpdb->usermeta " . 
            "WHERE meta_key = 'saNumber' AND meta_value = " . $user_agent_number
        );

        
        if( sizeof( $sa_agent_user_ids ) > 0 ){
            
            foreach( $sa_agent_user_ids as $sa_agent_user_id ){
                
                $agent = get_user_by( 'id', $sa_agent_user_id->user_id );
                
                $agent_number = get_user_meta( $sa_agent_user_id->user_id, 'agent_number', true );
                
                $user_position = strtolower( get_user_meta( $sa_agent_user_id->user_id, 'agent_position', true ) );
                
                $user_visibility = strtolower ( get_user_meta( $sa_agent_user_id->user_id, 'is_dashboard_visible', true ) );
                
                if ($user_visibility === 'false') {
                    continue;
                }
    
                array_push( $agentIDs, 
                array( 
                    'id' => $agent_number, 
                            'name' => $agent->first_name . ' ' . $agent->last_name,
                            'indent' => '20px',
                            'parentID' => $user_agent_number,
                            'position' => $user_position,
                            )
                );
                
                array_push( $SAAgentNums, $agent_number );
                
            }
 
            $extraIDs = $this->getRegularAgents( $SAAgentNums, '40px' );

            $juniorIDs = array();

            foreach ( $extraIDs['ids'] as $agent ) {
                if ($agent['position'] == 'junior partner') {
                    array_push( $juniorIDs, $agent['id'] );  
                }
            }

            $extraIDsExt = $this->getRegularAgents( $juniorIDs, '60px' );

            // if( $user_position == 'agency owner' ){

            //     $extraIDsExt = $this->getRegularAgents( $extraIDs[ 'extras' ], '60px' );

            // }

            if( sizeof( $extraIDs ) > 0 ){
                $agentIDs = array_merge( $agentIDs, $extraIDs[ 'ids' ] );
            }

            if( sizeof( $extraIDsExt ) > 0 ){
                $agentIDs = array_merge( $agentIDs, $extraIDsExt[ 'ids' ] );
            }

        }
        
        return $agentIDs;

    }

    function getRegularAgents( $nums, $indent ) {
        
        global $wpdb;

        $extraIDsSub = array();

        $extraAgentNums = array();

        foreach( $nums as $num ){

            $agent_user_ids =   $wpdb->get_results(
                "SELECT user_id FROM " . $wpdb->prefix . "usermeta " . 
                "WHERE meta_key='saNumber' AND meta_value='" . $num . "'"
            );

            foreach( $agent_user_ids as $agent_user_id ){

                $agent = get_user_by( 'id', $agent_user_id->user_id );

                $agent_number = get_user_meta( $agent_user_id->user_id, 'agent_number', true );

                $user_position = strtolower( get_user_meta( $agent_user_id->user_id, 'agent_position', true ) );

                $user_visibility = strtolower ( get_user_meta( $agent_user_id->user_id, 'is_dashboard_visible', true ) );

                if ($user_visibility === 'false') {
                    continue;
                }

                array_push( $extraIDsSub, 
                    array( 
                            'id' => $agent_number, 
                            'name' => $agent->first_name . ' ' . $agent->last_name,
                            'indent' => $indent,
                            'parentID' => $num,
                            'position' => $user_position,
                        )
                );

                array_push( $extraAgentNums, $agent_number );

            }

        }

        return array( 'ids' => $extraIDsSub, 'extras' => $extraAgentNums );

    }

}

?>