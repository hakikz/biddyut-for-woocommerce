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


defined( 'ABSPATH' ) or die( 'Hey, you can\t access this file, you silly human!' );


/**
 * 
 * # Global Variable to catch token_id
 * 
 */
$token = "";

require_once( dirname( __FILE__ ) . '/includes/bapi-admin-notices.php' );

require_once( dirname( __FILE__ ) . '/includes/bapi-connectivity.php' );

require_once( dirname( __FILE__ ) . '/includes/bapi-extra-order-status.php' );

require_once( dirname( __FILE__ ) . '/includes/bapi-extra-order-fields.php' );

/**
 * 
 * # Adding plugin CSS
 * 
 */
function bapi_styles(){
    # Getting Screen Objects
    $screen = get_current_screen(); 

    # Converting Object to Array
    $array = (array) $screen;

    # CSS will load if it is on shop page
    if('shop_order' == $array['post_type']){
        wp_enqueue_style('bapi_main', plugins_url( 'assets/css/bapi_main.css', __FILE__ ), array(), '1.0', 'all');
    }
    
}
add_action('admin_enqueue_scripts', 'bapi_styles');


/**
 * 
 * # Define the woocommerce_order_status_changed callback
 * 
 */
function bapi_woocommerce_order_status_changed( $order_id) { 
    $order = wc_get_order( $order_id );
    #Getting Order Status
    $order_status = $order->get_status();
    #Getting Order ID
    $order_id = $order->get_id();

    $new_order_id = (string)$order_id;
    $new_package = (int)$order->bapi_package;

    if($order_status == "shipped"){
        // $order->update_status( 'completed' );

        #Getting Items of this order
        $items = $order->get_items();



        $response1 = wp_remote_request( 'http://biddyut.publicdemo.xyz/api/v2/merchant/login?store_user=Pikaroo&store_password=rrrrr&key=7d2ApP4',
            array(
                'method'     => 'POST'
            )
        );

        $body = wp_remote_retrieve_body($response1);
        $array = json_decode($body, true);
        $token = $array['response']['api_token'];


        foreach ( $items as $item ) {

            $product_array = array(
                "product_title" => $item['name'],
                // "url" => "",
                "product_category" => $order->bapi_product_category,
                "unit_price" => (int)$item['total'],
                "quantity" => $item['quantity'],
                // "width" => 0,
                // "height" => 0,
                // "length"  => 0,
                // "weight" => 0.5,
                "pickup_location" => $order->bapi_pickup_location,
                // "picking_date" => "20180220"
            );
            // $products[] = array_merge($product_array, $product_array);
            $products[] = $product_array;
        }

        $last_array = [array(
            "delivery_name" => $order->get_billing_first_name().' '.$order->get_billing_last_name(),
            "delivery_email" => $order->get_billing_email(),
            "delivery_msisdn" => $order->get_billing_phone(),
            "delivery_zone" => $order->bapi_delivery_zone,
            "delivery_address" => $order->get_billing_address_1().','.$order->get_billing_address_2().','.$order->get_billing_city(),
            "merchant_order_id" => $new_order_id,
            "as_package" => $new_package,
            // "picking_date" => "2018-0220",
            "pickup_location" => $order->bapi_pickup_location,
            // "remarks" => "",
            "products" => $products,
            "delivery_pay_by_cus" => $order->bapi_delivery_charge,
            "verifed" => 0
        )];
        $products_array = json_encode( $last_array );


        $response = wp_remote_request( 'http://biddyut.publicdemo.xyz/api/v2/merchant/submit-order?api_token='.$token.'&store_id=Pikaroo&orders='.$products_array,
            array(
                'method'     => 'POST'
            )
        );

        $body = wp_remote_retrieve_body($response);
        $array = json_decode($body, true);
        // add_flash_notice( __("Hakik"), "warning", true );
        if($array['status_code'] != 200){
            $order->update_status( 'processing' );
            $msg = $array['status_code']." Something Went Wrong";
            add_flash_notice( $msg , "warning", true );
        }
        else{
            $msg = "Successfully Shipped To Biddyut";
            add_flash_notice( $msg , "success", true );
        }
    }
}; 
add_action( 'woocommerce_order_status_changed', 'bapi_woocommerce_order_status_changed', 10, 4 ); 



