<?php
/**
 * 
 * # Global Variable to catch token_id
 * 
 */
$token = "";


/**
 * 
 * # Function for API connectivity
 * 
 */
function bapi_api_connectivity(){
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

        $screen = get_current_screen(); 
        // print_r($screen);
        $array = (array) $screen;
        if('shop_order' == $array['post_type'] && 'post' == $array['base']){
            // global $woocommerce, $post;
            // $order = new WC_Order($post->ID);
            // $order_id = trim(str_replace('#', '', $order->get_order_number()));
            // $order_id = $order->get_id();
            // $order_status = $order->get_status();
            // echo '<div class="notice notice-success is-dismissible">
            //         <p>Biddyut API Successfully Connected and the API TOKEN is <b>'.$token.'</b></p>
            //         <p>Order ID is <b>'.$order_id.'</b></p>
            //         <p>Order Status is <b>'.$order_status.'</b></p>
            //     </div>';
            echo '<div class="notice notice-success is-dismissible">
                    <p>Biddyut API Successfully Connected</p>
                </div>';
            
        }
    }
}
add_action('admin_notices', 'bapi_api_connectivity');