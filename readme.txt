=== Thumbnail Link for WooCommerce ===
Contributors: christianzimpel
Donate link: https://paypal.me/wirsindveg
Tags: woocommerce, product image, external link, affiliate, thumbnail
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Link product images in your shop to any external URL – perfect for affiliate products.

== Description ==

By default every product image in your WooCommerce shop links to the product page. This plugin adds an **External link** field to each product. Fill it in and the product image and title in the shop link straight to that URL, opened in a new tab.

Typical use cases:

* Affiliate products (Amazon, Awin, Digistore24 …)
* Products that are sold on another platform
* Teasers that should lead to a landing page

= Features =

* One extra field in Product data → General.
* Works with classic shop templates and with block themes (Product Collection block).
* External links get `target="_blank"` and `rel="noopener nofollow"`.
* Products without an external link behave exactly as before.
* Compatible with High-Performance Order Storage (HPOS) and the Cart/Checkout blocks.
* Lightweight: no settings page, no database tables, no tracking.

= For developers =

Change the link attributes with the filter `tlfw_link_attributes`, for example to mark affiliate links as sponsored:

`add_filter( 'tlfw_link_attributes', function ( $attributes, $product ) {
	$attributes['rel'] = 'noopener sponsored';
	return $attributes;
}, 10, 2 );`

== Installation ==

1. Install and activate the plugin (WooCommerce must be active).
2. Edit a product and enter a URL in Product data → General → External link.
3. Save – done.

== Frequently Asked Questions ==

= What is the difference to the WooCommerce product type "External/Affiliate product"? =

The built-in type only changes the "Buy" button on the product page. The image in the shop still leads to your product page. With this plugin the image itself links to the external URL – and it works with every product type.

= Where are the links stored? =

As product meta `_external_url`. Deleting the plugin keeps the data, so nothing is lost if you reinstall it.

== Screenshots ==

1. The External link field in the product data.

== Changelog ==

= 1.1.0 – 2026-10-24 =
* Fixed: saving a product caused a fatal error on PHP 8 because the save function was registered under a wrong name. URLs are saved reliably again.
* Fixed: products without an external link no longer open in a new tab.
* Fixed: invalid URLs are rejected with an error message instead of being stored silently.
* New: support for block themes (Product Collection block: product image and title).
* New: declared compatibility with HPOS and the Cart/Checkout blocks, `Requires Plugins: woocommerce` header.
* New: filter `tlfw_link_attributes`; external links now use `rel="noopener nofollow"`.
* Changed: code rewritten following the WordPress Coding Standards; text domain corrected to `thumbnail-link-for-woo`.
* Tested with WordPress 7.1, WooCommerce 11.2 and PHP 8.3.

= 1.0.1 =
* Tested up to WordPress 6.8.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Important fix: saving products crashed on PHP 8. Existing links are kept.
