<?php
/*
  Plugin Name: Biddyut API Integration
  Plugin URI: http://www.iciclecorporation.com
  Description: Plugin to connect API with Biddyut third party parcel service 
  Version: 1.0.0
  Author: Hakik
  Author URI: http://www.iciclecorporation.com
  Author Email: hakikzmn@gmail.com
 */


$token = "";
//  New query
         

// Function for API connectivity
function api_connectivity()
{
    if ( is_user_logged_in() )  {
        $response = wp_remote_request( 'http://biddyut.publicdemo.xyz/api/v2/merchant/login?store_user=Pikaroo&store_password=rrrrr&key=7d2ApP4',
            array(
                'method'     => 'POST'
            )
        );

        $body = wp_remote_retrieve_body($response);
        $array = json_decode($body, true);
        global $token;
        $token = $array['response']['api_token'];
        // print_r($array)
        echo '<div class="notice notice-success is-dismissible">
             <p>Biddyut API Successfully Connected and the API TOKEN is <b>'.$token.'</b></p>
         </div>';

         $orders = wc_get_orders( array('numberposts' => -1) );
        // print_r($orders);
            // Loop through each WC_Order object
            foreach( $orders as $order ){
                echo $order->get_id() . '<br>'; // The order ID
                echo $order->get_status() . '<br>'; // The order status
                echo $order->get_customer_id() . '<br>'; // The order status
                echo $order->bapi_delivery_zone . '<br>'; // The order status
            }

    }
}
add_action('admin_notices', 'api_connectivity');

// Woocommerce Integration Start

add_filter( 'bulk_actions-edit-shop_order', 'bapi_register_bulk_action' ); // edit-shop_order is the screen ID of the orders page
 
function bapi_register_bulk_action( $bulk_actions ) {
 
	$bulk_actions['mark_awaiting_shipment'] = 'Mark awaiting shipment'; // <option value="mark_awaiting_shipment">Mark awaiting shipment</option>
	return $bulk_actions;
 
}



#------------------------------------------------
#-----------------Break--------------------------
#------------------------------------------------


function bapi_display_order_data_in_admin( $order ){  

    global $token;   
    $response = wp_remote_request( 'http://biddyut.publicdemo.xyz/api/v2/merchant/resource?api_token='.$token,
        array(
            'method'     => 'POST'
        )
    );

    $body = wp_remote_retrieve_body($response);
    $array = json_decode($body, true);
    // print_r($array);
    foreach($array['response']['delivery_zones'] as $a){
        foreach($a as $k){
            $options[] = $k;
        }
        // echo $a;
    }
    $final_options = array_combine($options, $options);
    // print_r($final_options);

    foreach($array['response']['pickup_locations'] as $a){
        foreach($a as $k){
            $options2[] = $k;
        }
        // echo $a;
    }

?>
    <div class="order_data_column">
        <h4><?php _e( 'Additional Information', 'woocommerce' ); ?><a href="#" class="edit_address"><?php _e( 'Edit', 'woocommerce' ); ?></a></h4>
        <div class="address">
        <?php
            echo '<p><strong>' . __( 'Text Field' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_text_field', true ) . '</p>';
            echo '<p><strong>' . __( 'Delivery Zone' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_delivery_zone', true ) . '</p>'; 
            echo '<p><strong>' . __( 'Pickup Location' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_pickup_location', true ) . '</p>'; 
        ?>
        </div>
        <div class="edit_address">
            <?php woocommerce_wp_text_input( array( 'id' => '_bapi_text_field', 'label' => __( 'Some field' ), 'wrapper_class' => '_billing_company_field' ) ); ?>
            <?php 
                
                woocommerce_wp_select( 
                    array( 
                        'id'      => '_bapi_delivery_zone', 
                        'label'   => __( 'Delivery Zone', 'woocommerce' ), 
                        // 'options' => array(
                        //     'one'   => __( 'Option 1', 'woocommerce' ),
                        //     'two'   => __( 'Option 2', 'woocommerce2' ),
                        //     'three' => __( 'Option 3', 'woocommerce3' )
                        //     )
                        // 'value'   => $options,
                        'options' => $final_options
                        )
                    );
             ?>
            <?php 
                
                woocommerce_wp_select( 
                    array( 
                        'id'      => '_bapi_pickup_location', 
                        'label'   => __( 'Pickup Location', 'woocommerce' ), 
                        // 'options' => array(
                        //     'one'   => __( 'Option 1', 'woocommerce' ),
                        //     'two'   => __( 'Option 2', 'woocommerce2' ),
                        //     'three' => __( 'Option 3', 'woocommerce3' )
                        //     )
                        'options' => $options2
                        )
                    );
             ?>
        </div>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'bapi_display_order_data_in_admin' );

function bapi_save_extra_details( $post_id, $post ){
    update_post_meta( $post_id, '_bapi_text_field', wc_clean( $_POST[ '_bapi_text_field' ] ) );
    update_post_meta( $post_id, '_bapi_delivery_zone', wc_clean( $_POST[ '_bapi_delivery_zone' ] ) );
}
add_action( 'woocommerce_process_shop_order_meta', 'bapi_save_extra_details', 45, 2 );
