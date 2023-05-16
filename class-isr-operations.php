<?php /** @noinspection SqlResolve */

	/** @noinspection SqlNoDataSourceInspection */

	class ISROps {

		//Get appointment invite details
		function GetInvites( $start_date, $end_date ): array|WP_Error {

			if ( ! $start_date ) {
				$start_date = date( 'Y-m-d', strtotime( 'January 1, 2020' ) );
			}

			if ( ! $end_date ) {
				$end_date = date( 'Y-m-d', strtotime( 'today' ) );
			}

			// Manually set this for now for TJG purposes @TODO: Re-enable this setting
			$ciFormID = get_option( 'ci_form_id' );

			if ( $ciFormID != 14 ) {
				$ciFormID = 14;
			}

			$searchCriteria = array();

			$searchCriteria['start_date'] = $start_date;
			$searchCriteria['end_date']   = $end_date;

			return GFAPI::get_entries( $ciFormID, $searchCriteria, null, array( 'offset' => 0, 'page_size' => 1000 ) );

		}

		//Get agent specific data on presentations
		function GetPresentations( $start_date, $end_date ): array|WP_Error {
			if ( ! $start_date ) {
				$start_date = date( 'Y-m-d', strtotime( 'January 1, 2020' ) );
			}

			if ( ! $end_date ) {
				$end_date = date( 'Y-m-d', strtotime( 'today' ) );
			}

			$wcnFormID = get_option( 'wcn_form_id' );
			if ( $wcnFormID != 13 ) {
				$wcnFormID = 13;
			}

			$searchCriteria = array();

			$searchCriteria['start_date'] = $start_date;
			$searchCriteria['end_date']   = $end_date;

			return GFAPI::get_entries( $wcnFormID, $searchCriteria, null, array( 'offset' => 0, 'page_size' => 1000 ) );

		}

		//Get agents working under supervision of the currently logged-in user
		function GetWorkForce( $user_id ): array {

			global $wpdb;
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}

			$viewing_as = $user_id;
			//        $viewing_as = get_user_meta( $user_id, 'isr_view_sales_analysis_as', true );

			$user              = get_user_by( 'id', $viewing_as );
			$user_agent_number = get_user_meta( $viewing_as, 'agent_number', true );

			$user_position = strtolower( get_user_meta( $viewing_as, 'agent_position', true ) );

			$agentIDs    = array();
			$SAAgentNums = array();

//        $extraIDs = array();

//        $extraIDsExt = array();


			//Add the logged in agent's details
			$agentIDs[] = array(
				'id'       => $user_agent_number,
				'name'     => $user->first_name . ' ' . $user->last_name,
				'indent'   => '0px',
				'parentID' => '',
				'position' => $user_position,
			);

			//Get Agents
			$query             = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s' AND meta_value = %d";
			$prepare           = $wpdb->prepare( $query, 'saNumber', $viewing_as );
			$sa_agent_user_ids = $wpdb->get_results( $prepare );

//
//        $sa_agent_user_ids = $wpdb->get_results(
//            "SELECT user_id FROM $wpdb->usermeta " .
//            "WHERE meta_key = 'saNumber' AND meta_value = " . $user_agent_number
//        );


			if ( sizeof( $sa_agent_user_ids ) > 0 ) {

				foreach ( $sa_agent_user_ids as $sa_agent_user_id ) {

					$agent           = get_user_by( 'id', $sa_agent_user_id->user_id );
					$agent_number    = get_user_meta( $sa_agent_user_id->user_id, 'agent_number', true );
					$user_position   = strtolower( get_user_meta( $sa_agent_user_id->user_id, 'agent_position', true ) );
					$user_visibility = strtolower( get_user_meta( $sa_agent_user_id->user_id, 'is_dashboard_visible', true ) );

					if ( $user_visibility === 'false' || ! $user_visibility ) {
						continue;
					}

					$agentIDs[] = array(
						'id'       => $agent_number,
						'name'     => $agent->first_name . ' ' . $agent->last_name,
						'indent'   => '20px',
						'parentID' => $user_agent_number,
						'position' => $user_position,
					);

					$SAAgentNums[] = $agent_number;
				}

				$extraIDs = $this->getRegularAgents( $SAAgentNums, '40px' );

				$juniorIDs = array();

				foreach ( $extraIDs['ids'] as $agent ) {
					if ( $agent['position'] == 'junior partner' ) {
						$juniorIDs[] = $agent['id'];
					}
				}

				$extraIDsExt = $this->getRegularAgents( $juniorIDs, '60px' );

				// if( $user_position == 'agency owner' ){

				//     $extraIDsExt = $this->getRegularAgents( $extraIDs[ 'extras' ], '60px' );

				// }

				if ( sizeof( $extraIDs ) > 0 ) {
					$agentIDs = array_merge( $agentIDs, $extraIDs['ids'] );
				}

				if ( sizeof( $extraIDsExt ) > 0 ) {
					$agentIDs = array_merge( $agentIDs, $extraIDsExt['ids'] );
				}

			}

			return $agentIDs;

		}

		function getRegularAgents( $nums, $indent ): array {

			global $wpdb;
			$table = $wpdb->prefix . 'usermeta';

			$extraIDsSub = array();

			$extraAgentNums = array();

			foreach ( $nums as $num ) {

				$query          = "SELECT user_id FROM $table WHERE meta_key = '%s' AND meta_value = %s";
				$prepare        = $wpdb->prepare( $query, 'agent_number', $num );
				$agent_user_ids = $wpdb->get_results( $prepare );

				foreach ( $agent_user_ids as $agent_user_id ) {

					$agent = get_user_by( 'id', $agent_user_id->user_id );
					$agent_number = get_user_meta( $agent_user_id->user_id, 'agent_number', true );
					$user_position = strtolower( get_user_meta( $agent_user_id->user_id, 'agent_position', true ) );
					$user_visibility = strtolower( get_user_meta( $agent_user_id->user_id, 'is_dashboard_visible', true ) );

					if ( $user_visibility === 'false' ) {
						continue;
					}

					$extraIDsSub[] = array(
						'id'       => $agent_number,
						'name'     => $agent->first_name . ' ' . $agent->last_name,
						'indent'   => $indent,
						'parentID' => $num,
						'position' => $user_position,
					);

					$extraAgentNums[] = $agent_number;
				}
			}

			return array( 'ids' => $extraIDsSub, 'extras' => $extraAgentNums );
		}
	}