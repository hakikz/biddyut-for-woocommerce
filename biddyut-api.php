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
       
        // print_r($array);
        echo '<div class="notice notice-success is-dismissible">
             <p>API Successfully Connected and the API TOKEN is <b>'.$array['response']['api_token'].'</b></p>
         </div>';

    }
}
add_action('admin_notices', 'api_connectivity');

// $response = wp_remote_request( 'http://biddyut.publicdemo.xyz/api/v2/merchant/login?store_user=Pikaroo&store_password=rrrrr&key=7d2ApP4',
//     array(
//         'method'     => 'POST'
//     )
// );

// $body = wp_remote_retrieve_body($response);
// $array = array();
// $array = json_decode($body, true);
// echo "<div class='new'>";
// print_r($array);
// foreach($array as $a){
//     // foreach($a as $key => $k){
//     //     echo $key[$k];
//     // }
//     echo $a['api_token'];
// }
// echo "</div>";

add_filter( 'bulk_actions-edit-shop_order', 'hakik_register_bulk_action' ); // edit-shop_order is the screen ID of the orders page
 
function hakik_register_bulk_action( $bulk_actions ) {
 
	$bulk_actions['mark_awaiting_shipment'] = 'Mark awaiting shipment'; // <option value="mark_awaiting_shipment">Mark awaiting shipment</option>
	return $bulk_actions;
 
}

