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
        // print_r($array);
        echo '<div class="notice notice-success is-dismissible">
             <p>Biddyut API Successfully Connected and the API TOKEN is <b>'.$token.'</b></p>
         </div>';

         $orders = wc_get_orders( array('numberposts' => -1) );
        // print_r($orders);
            // Loop through each WC_Order object
            foreach( $orders as $order ){
                echo "<b>Merchant Order ID:</b> ".$order->get_id() . '<br>'; // The order ID
                echo "<b>Order Status:</b> ".$order->get_status() . '<br>'; // The order status
                echo "<b>Customer ID:</b> ".$order->get_customer_id() . '<br>'; // The order status
                echo "<b>Delivery Name:</b> ". $order->get_billing_first_name().' '. $order->get_billing_last_name(). '<br>'; // The order status
                echo "<b>Delivery Email:</b> ". $order->get_billing_email(). '<br>'; // The order status
                echo "<b>Delivery msisdn:</b> ". $order->get_billing_phone(). '<br>'; // The order status
                echo "<b>Delivery Address:</b> ". 
                $order->get_billing_address_1().' '.
                $order->get_billing_address_2().', '.
                $order->get_billing_postcode().', '.
                $order->get_billing_city().
                '<br>'; // The order status
                echo "<b>Delivery Zone:</b> ".$order->bapi_delivery_zone . '<br>'; // The order status
                echo "<b>Pickup Location:</b> ".$order->bapi_pickup_location . '<br>'; // The order status
                echo "<b>Product Category from API Resource:</b> ".$order->bapi_product_category . '<br>'; // The order status
                echo "<b>Pacakge:</b> ".$order->bapi_package . '<br>'; // The order status
                echo "<b>Delivery Charge:</b> ".$order->bapi_delivery_charge . '<br>'; // The order status
                echo "<b>Order Total Amount:</b> ".$order->get_total() . '<br>'; // The order status

                $order = new WC_Order( $order->get_id() );
                $items = $order->get_items();
                foreach ( $items as $item ) {
                    // echo $item_id = $item['order_item_id']. '<br>'; 
                    // echo $product_name = $item['name']. '<br>';
                    // echo $product_id = $item['product_id']. '<br>';
                    // echo $quantity = $item['quantity']. '<br><br><br><br><br>';

                    $last_array = array(
                        "delivery_name" => $order->get_billing_first_name(),
                        "delivery_email" => $order->get_billing_email(),
                        "products" => array([
                            "product_title" => $item['name'],
                            "unit_price" => $item['total']
                        ])
                    );
                    // $last_array = array(
                    //     "product_title" => $item['name'],
                    //     "unit_price" => $order->get_total()
                    // );
                    // print_r($last_array );
                    $array_o[] = array_merge($last_array, $last_array);
                    // $display[] = json_encode($array_o);
                }
                $main = json_encode($array_o);
                echo $main;
            }
            // $order = new WC_Order( $order_id );
    }
}
add_action('admin_notices', 'bapi_api_connectivity');

/**
 * 
 * # Add another option in orders table inside selectbox
 * 
 */
 
function bapi_register_bulk_action( $bulk_actions ) {
	$bulk_actions['mark_awaiting_shipment'] = 'Mark awaiting shipment'; // <option value="mark_awaiting_shipment">Mark awaiting shipment</option>
	return $bulk_actions;
}

add_filter( 'bulk_actions-edit-shop_order', 'bapi_register_bulk_action' ); // edit-shop_order is the screen ID of the orders page



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
    # This is collecting Delivery Zones from API Resources
    foreach($array['response']['delivery_zones'] as $zones){
        foreach($zones as $zone){
            $delivery_zones[] = $zone;
        }
        // echo $a;
    }
    $delivery_zone = array_combine($delivery_zones, $delivery_zones);
    // print_r($delivery_zones);


    # This is collecting Pickup Locations from API Resources
    foreach($array['response']['pickup_locations'] as $plocations){
        foreach($plocations as $plocation){
            $pickup_locations[] = $plocation;
        }
        // echo $a;
    }
    $pickup_location = array_combine($pickup_locations, $pickup_locations);


    # This is collecting Product Category from API Resources
    foreach($array['response']['product_categories'] as $pcategories){
        foreach($pcategories as $pcategory){
            $product_categories[] = $pcategory;
        }
        // echo $a;
    }
    $product_category = array_combine($product_categories, $product_categories);

?>
    <div class="order_data_column bapi_column">
        <h4><?php _e( 'Biddyut Shipping Info', 'woocommerce' ); ?><a href="#" class="edit_address"><?php _e( 'Edit', 'woocommerce' ); ?></a></h4>
        <div class="address">
        <?php
            echo '<p><strong>' . __( 'Delivery Zone' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_delivery_zone', true ) . '</p>'; 
            echo '<p><strong>' . __( 'Pickup Location' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_pickup_location', true ) . '</p>'; 
            echo '<p><strong>' . __( 'Product Category' ) . ':</strong>' . get_post_meta( $order->id, '_bapi_product_category', true ) . '</p>'; 
        ?>
        </div>
        <div class="edit_address">
            <?php 
                # Select Box for Delivery Zone
                woocommerce_wp_select( 
                    array( 
                        'id'      => '_bapi_delivery_zone', 
                        'class' => 'bapi_select wc-enhanced-select select2-hidden-accessible',
                        'label'   => __( 'Delivery Zone', 'woocommerce' ), 
                        'options' => $delivery_zone
                    )
                );
             ?>
            <?php 
                # Select Box for Pickup Location
                woocommerce_wp_select( 
                    array( 
                        'id'      => '_bapi_pickup_location', 
                        'class' => 'bapi_select wc-enhanced-select select2-hidden-accessible',
                        'label'   => __( 'Pickup Location', 'woocommerce' ), 
                        'options' => $pickup_location
                    )
                );
             ?>
            <?php 
                # Select Box for Product Category
                woocommerce_wp_select( 
                    array( 
                        'id'      => '_bapi_product_category', 
                        'class' => 'bapi_select wc-enhanced-select select2-hidden-accessible',
                        'label'   => __( 'Product Category', 'woocommerce' ), 
                        'options' => $product_category
                    )
                );
             ?>
             <?php
                $is_package = get_post_meta( $order->id, '_bapi_package', true );
                
                if($is_package == 1){ $type = "Yes"; }else{ $type = "No"; }

                $format = '<p class="%s"><strong>Is this a wrapping pacakage by Fulchasi?</strong>%s</p>';
                echo sprintf($format,"bapi_d_none",$type);

                # Radio Button for Package
                woocommerce_wp_radio( array(
                    'id' => '_bapi_package',
                    'label' => 'Is this a wrapping pacakage by Fulchasi?',
                    'value' => $is_package,
                    'options' => array(
                        '0' => 'No',
                        '1' => 'Yes'
                    ),
                    'style' => 'width:16px', // required for checkboxes and radio buttons
                    'wrapper_class' => 'form-field-wide' // always add this class
                ) );
             ?>
            
             <?php
                $is_delivery_charge = get_post_meta($order->id,'_bapi_delivery_charge',true);

                if($is_delivery_charge == 1){ $charge="Yes"; }else{ $charge="No"; }
                $format = '<p class="%s"><strong>Is this delivery charge given by customer?</strong>%s</p>';
                echo sprintf($format,"bapi_d_none",$charge);

                # Radio Button for Charge
                woocommerce_wp_radio( array(
                    'id' => '_bapi_delivery_charge',
                    'label' => __('Is this delivery charge given by customer?'),
                    'value' => $is_delivery_charge,
                    'options' => array(
                        '0' => 'No',
                        '1' => 'Yes'
                    ),
                    'style' => 'width:16px', // required for checkboxes and radio buttons
                    'wrapper_class' => 'form-field-wide' // always add this class
                ) );
             ?>
        </div>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'bapi_display_order_data_in_admin', 10, 1 );

function bapi_save_extra_details( $post_id, $post ){
    update_post_meta( $post_id, '_bapi_delivery_zone', wc_clean( $_POST[ '_bapi_delivery_zone' ] ) );
    update_post_meta( $post_id, '_bapi_pickup_location', wc_clean( $_POST[ '_bapi_pickup_location' ] ) );
    update_post_meta( $post_id, '_bapi_product_category', wc_clean( $_POST[ '_bapi_product_category' ] ) );
    update_post_meta( $post_id, '_bapi_package', wc_clean( $_POST[ '_bapi_package' ] ) );
    update_post_meta( $post_id, '_bapi_delivery_charge', wc_clean( $_POST[ '_bapi_delivery_charge' ] ) );
}
add_action( 'woocommerce_process_shop_order_meta', 'bapi_save_extra_details', 45, 2 );
