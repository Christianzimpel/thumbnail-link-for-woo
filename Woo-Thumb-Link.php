<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- File name kept since 1.0, renaming would deactivate the plugin on update.
/**
 * Plugin Name:          Thumbnail Link for WooCommerce
 * Plugin URI:           https://wordpress.org/plugins/thumbnail-link-for-woo/
 * Description:          Links the product image and title in your shop to an external URL of your choice, e.g. an affiliate link.
 * Version:              1.1.0
 * Requires at least:    6.2
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 8.0
 * WC tested up to:      11.2
 * Author:               Christian Zimpel
 * Author URI:           https://christianzimpel.de
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          thumbnail-link-for-woo
 *
 * @package ThumbnailLinkForWoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TLFW_VERSION', '1.1.0' );
define( 'TLFW_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-tlfw-plugin.php';

TLFW_Plugin::instance();
