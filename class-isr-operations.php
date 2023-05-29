<?php /** @noinspection SqlResolve */

	/** @noinspection SqlNoDataSourceInspection */

	class ISROps {

		//Get appointment invite details
		function GetInvites( $start_date, $end_date ): WP_Error|array {
			if ( empty( $start_date ) ) {
				$start_date = date( 'Y-m-d', strtotime( 'January 1, 2020' ) );
			}
			if ( empty( $end_date ) ) {
				$end_date = date( 'Y-m-d', strtotime( 'today' ) );
			}

			$ciFormID = get_option( 'ci_form_id' );
			if ( empty( $ciFormID ) ) {
				$ciFormID = 14;
			}

			$searchCriteria               = array();
			$searchCriteria['start_date'] = $start_date;
			$searchCriteria['end_date']   = $end_date;

			return GFAPI::get_entries( $ciFormID, $searchCriteria, null, array( 'offset' => 0, 'page_size' => 1000 ) );

		}

		//Get agent specific data on presentations
		function GetPresentations( $start_date, $end_date ): WP_Error|array {
			if ( empty( $start_date ) ) {
				$start_date = date( 'Y-m-d', strtotime( 'January 1, 2020' ) );
			}
			if ( empty( $end_date ) ) {
				$end_date = date( 'Y-m-d', strtotime( 'today' ) );
			}

			$wcnFormID = get_option( 'wcn_form_id' );
			if ( empty( $wcnFormID ) ) {
				$wcnFormID = 13;
			}

			$searchCriteria               = array();
			$searchCriteria['start_date'] = $start_date;
			$searchCriteria['end_date']   = $end_date;

			return GFAPI::get_entries( $wcnFormID, $searchCriteria, null, array( 'offset' => 0, 'page_size' => 1000 ) );

		}

		//Get agents working under supervision of the currently logged-in user
		function GetWorkForce( $user_id = null ): array {
			$results = [];

			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			$viewing_as = get_user_meta( $user_id, 'isr_view_sales_analysis_as', true );
			if ( $viewing_as == null || $viewing_as == '' ) {
				$viewing_as = $user_id;
			}

			$results['viewing_as'] = $viewing_as;
			$results['user_id']    = $user_id;

			global $wpdb;
			$user              = get_user_by( 'id', $viewing_as );
			$user_agent_number = get_user_meta( $viewing_as, 'agent_number', true );
			$user_position     = strtolower( get_user_meta( $viewing_as, 'agent_position', true ) );

			$agentIDs    = array();
			$SAAgentNums = array();

			//Add the logged in agent's details
			$agentIDs[] = array(
				'id'       => $user_agent_number,
				'name'     => $user->first_name . ' ' . $user->last_name,
				'indent'   => '0px',
				'parentID' => '',
				'position' => $user_position,
			);

			//Get Agents
			$table             = $wpdb->usermeta;
			$query             = "SELECT user_id FROM $table WHERE meta_key = 'saNumber' AND meta_value = %s";
			$prepare           = $wpdb->prepare( $query, $user_agent_number );
			$sa_agent_user_ids = $wpdb->get_results( $prepare );

			$results['sa_agent_user_ids'] = $sa_agent_user_ids;
			$results['sa_agent_user_ids']['error'] = $wpdb->last_error;
			$results['sa_agent_user_ids']['query'] = $wpdb->last_query;

			if ( sizeof( $sa_agent_user_ids ) > 0 ) {

				foreach ( $sa_agent_user_ids as $sa_agent_user_id ) {

					$agent           = get_user_by( 'id', $sa_agent_user_id->user_id );
					$active_agent    = $this->active_agent( $agent->ID );

					if ( !$active_agent ) {
						continue;
					}

					$agent_number    = get_user_meta( $sa_agent_user_id->user_id, 'agent_number', true );
					$user_position   = strtolower( get_user_meta( $sa_agent_user_id->user_id, 'agent_position', true ) );

					$agentIDs[] = array(
						'id'       => $agent_number,
						'name'     => $agent->first_name . ' ' . $agent->last_name,
						'indent'   => '20px',
						'parentID' => $user_agent_number,
						'position' => $user_position,
					);

					$SAAgentNums[] = $agent_number;
				}

				$results['sa_agent_user_id'] = $agentIDs;

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

			$agentIDs['results'] = $results;

			return $agentIDs;
		}

		function getRegularAgents( $nums, $indent ): array {

			global $wpdb;
			$table = $wpdb->prefix . 'usermeta';
			$extraIDsSub = array();
			$extraAgentNums = array();

			foreach ( $nums as $num ) {
				$query = "SELECT user_id FROM $table WHERE meta_key = 'saNumber' AND meta_value = '%s'";
				$prepare = $wpdb->prepare( $query, $num );
				$agent_user_ids = $wpdb->get_results( $prepare );

				foreach ( $agent_user_ids as $agent_user_id ) {

					$agent = get_user_by( 'id', $agent_user_id->user_id );
					$active_agent = $this->active_agent( $agent->ID );

					if ( !$active_agent ) {
						continue;
					}

					$agent_number = get_user_meta( $agent_user_id->user_id, 'agent_number', true );
					$user_position = strtolower( get_user_meta( $agent_user_id->user_id, 'agent_position', true ) );


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

		public function active_agent( $user_id ): bool {
			$active    = get_user_meta( $user_id, 'is_dashboard_visible', true );
			$new_agent = get_user_meta( $user_id, 'classroom_only', true );

			if ( $active === 'true' && ! $new_agent ) {
				return true;
			} else {
				return false;
			}
		}
	}