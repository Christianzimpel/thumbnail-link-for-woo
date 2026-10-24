<?php
/**
 * @link              https://christianzimpel.de
 * @since             1.0.0
 * @package           Thumbnail_Link_for_Woo
 *
 * @wordpress-plugin
 * Plugin Name:       Thumbnail-Link-for-Woo
 * Plugin URI:        https://wp-support-blog.com/
 * Description:       Make a Product Thumbnail linked to an extern url of your choice.
 * Version:           1.0.1
 * Author:            Christian Zimpel
 * Author URI:        https://christianzimpel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       Thumbnail_Link_for_Woo
 * Domain Path:       /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function my_init_function_Thumbnail_Link_for_Woocommerce() {

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
add_action( 'woocommerce_before_shop_loop_item', 'modify_woocommerce_template_loop_product_link_Woo_thumb', 10 );

// Display Fields
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields_Woo_thumb' );

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );

function woo_add_custom_general_fields_Woo_thumb() {
    global $woocommerce, $post;

    echo ('<div class="options_group">');

    woocommerce_wp_text_input(
        array(
            'id'          => '_external_url',
            'label'       => esc_html( 'External URL', 'woo' ),
            'placeholder' => 'http://',
            'desc_tip'    => 'true',
            'description' => esc_html( 'Enter the external URL here.', 'woocommerce' )
        )
    );

    echo ('</div>');
}

function woo_add_custom_general_fields_save_Woo_thumb( $post_id ){
    $woocommerce_text_field = sanitize_text_field($_POST['_external_url']);


	    if( !empty( $woocommerce_text_field ) ){

        // Validate url

        if (filter_var($woocommerce_text_field , FILTER_VALIDATE_URL)) {
                 update_post_meta( $post_id, '_external_url', esc_attr( $woocommerce_text_field ) );
        }

		}
    else {
       update_post_meta( $post_id, '_external_url', esc_attr( $woocommerce_text_field ) );
    }

}

function modify_woocommerce_template_loop_product_link_Woo_thumb() {
    $product_url =  get_post_meta( get_the_ID(), '_external_url', true );

    if( empty( $product_url) )
        echo '<a href="' . esc_url( get_the_permalink() ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" target="_blank">';
    else
        echo '<a href="' . esc_url( $product_url ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" target="_blank">';
}
}
add_action( 'init', 'my_init_function_Thumbnail_Link_for_Woocommerce');
