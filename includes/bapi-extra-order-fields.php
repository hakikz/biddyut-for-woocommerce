<?php
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
        // echo $token;
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