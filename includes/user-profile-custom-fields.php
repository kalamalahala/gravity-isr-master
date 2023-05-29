<?php

    //Add "View Sales Analysis As..." to customer profile
    add_action( 'show_user_profile', 'add_sales_analysis_viewing' );
    add_action( 'edit_user_profile', 'add_sales_analysis_viewing' );

    function add_sales_analysis_viewing( $user ) { 

        if( current_user_can( 'manage_options' ) ){

            $sales_users = array();
        
            $sales_users_raw = get_users();

            $positions = [ 'agency owner', 'senior partner', 'junior partner', 'agent' ];

            $viewing_as = get_user_meta( $user->ID, 'isr_view_sales_analysis_as', true );
                        
            foreach( $sales_users_raw as $sales_user_raw ){

                $_position = get_user_meta( $sales_user_raw->ID, 'agent_position', true );
                
                if( in_array( strtolower( $_position ), $positions ) ){

                    array_push( $sales_users, $sales_user_raw );

                }

            }

            ?>
            <h3><?php _e("Sales Analysis Viewing", "blank"); ?></h3>

            <table class="form-table">
            <tr>
                <th><label for="customer-csr"><?php _e("View Sales Analysis As..."); ?></label></th>
                <td>
                    <select id="isr-view-sales-analysis-as" name="isr-view-sales-analysis-as" >
                    <?php
                    
                        foreach( $sales_users as $sales_user ){

                            $selected = '';

                            if( $viewing_as == $sales_user->ID ){

                                $selected = ' selected';

                            }

                            $full_name = $sales_user->first_name . ' ' . $sales_user->last_name;

                            $style = '';

                            if( $user->ID == $sales_user->ID ){

                                $full_name = 'Myself';

                                $style = ' style="font-weight: bold;"';

                            }

                        ?>
                        <option <?php echo $style;?> <?php echo $selected;?>
                        value="<?php echo $sales_user->ID;?>">
                        <?php echo $full_name;?>
                        </option>
                        <?php

                        }

                    ?>
                    </select>
                </td>
            </tr>
            </table>
        <?php 
        }
    }

    add_action( 'personal_options_update', 'save_view_sales_analysis_as' );
    add_action( 'edit_user_profile_update', 'save_view_sales_analysis_as' );

    function save_view_sales_analysis_as( $user_id ) {

        if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
            return;
        }
        
        if ( !current_user_can( 'manage_options', $user_id ) ) { 
            return false; 
        }

        update_user_meta( $user_id, 'isr_view_sales_analysis_as', $_POST[ 'isr-view-sales-analysis-as' ] );

    }

?>