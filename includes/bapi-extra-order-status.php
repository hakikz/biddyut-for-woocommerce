<?php

/**
 * 
 * # Add another option in orders table inside selectbox
 * 
 */
function bapi_wc_register_post_statuses() {
    register_post_status( 'wc-shipped', array(
        'label' => _x( 'Shipped', 'WooCommerce Order status', 'text_domain' ),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop( 'Shipped to Biddyut (%s)', 'Shipped to Biddyut (%s)', 'text_domain' )
    ) );
}
add_filter( 'init', 'bapi_wc_register_post_statuses' );

function bapi_wc_add_order_statuses( $order_statuses) {
    // $screen = get_current_screen(); 
    // print_r($screen);
    // $array = (array) $screen;
    // if('shop_order' == $array['post_type'] && 'post' == $array['base']){
    //     $order = wc_get_order( $order_id );
    //     $bapi_delivery_zone = $order->bapi_delivery_zone;
    //     $bapi_pickup_location = $order->bapi_pickup_location;
    //     $bapi_product_category = $order->_bapi_product_category;
    //     if($bapi_delivery_zone != "" && $bapi_pickup_location != "" && $bapi_product_category != ""){
    //     }
        $order_statuses['wc-shipped'] = _x( 'Shipped', 'WooCommerce Order status', 'text_domain' );
    // }
    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'bapi_wc_add_order_statuses' );