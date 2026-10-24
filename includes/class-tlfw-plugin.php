<?php
/**
 * Main plugin class.
 *
 * @package ThumbnailLinkForWoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
final class TLFW_Plugin {

	/**
	 * Product meta key for the external URL. Unchanged since 1.0 so existing links keep working.
	 */
	const META_KEY = '_external_url';

	/**
	 * Singleton instance.
	 *
	 * @var TLFW_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return TLFW_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registers the hooks.
	 */
	private function __construct() {
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	/**
	 * Declares compatibility with WooCommerce features (HPOS and Cart/Checkout blocks).
	 * The plugin only stores product meta, so both are safe.
	 */
	public function declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', TLFW_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', TLFW_FILE, true );
		}
	}

	/**
	 * Hooks into WooCommerce once it is loaded.
	 */
	public function init() {
		// Admin: field in "Product data → General".
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_field' ) );

		// Classic shop templates.
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'loop_link_open' ), 10 );

		// Block themes (Product Collection block).
		add_filter( 'render_block_woocommerce/product-image', array( $this, 'filter_block_link' ), 10, 3 );
		add_filter( 'render_block_core/post-title', array( $this, 'filter_block_link' ), 10, 3 );
	}

	/**
	 * Returns the external URL of a product (empty string if none).
	 *
	 * @param WC_Product|int|null $product Product or product ID.
	 * @return string
	 */
	public static function get_url( $product ) {
		$product = wc_get_product( $product );
		if ( ! $product ) {
			return '';
		}
		return (string) $product->get_meta( self::META_KEY, true );
	}

	/**
	 * Returns the extra link attributes for external links.
	 *
	 * @param WC_Product $product Product.
	 * @return array<string, string>
	 */
	private function get_link_attributes( $product ) {
		/**
		 * Filters the attributes of external product links.
		 *
		 * @param array      $attributes Attributes, e.g. target and rel.
		 * @param WC_Product $product    Product.
		 */
		return (array) apply_filters(
			'tlfw_link_attributes',
			array(
				'target' => '_blank',
				'rel'    => 'noopener nofollow',
			),
			$product
		);
	}

	/**
	 * Renders the URL field.
	 */
	public function render_field() {
		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'          => self::META_KEY,
				'label'       => __( 'External link', 'thumbnail-link-for-woo' ),
				'placeholder' => 'https://',
				'type'        => 'url',
				'desc_tip'    => true,
				'description' => __( 'Product image and title in the shop link to this URL and open in a new tab. Leave empty to link to the product page.', 'thumbnail-link-for-woo' ),
			)
		);

		echo '</div>';
	}

	/**
	 * Saves the URL field.
	 *
	 * WooCommerce verifies the "woocommerce_meta_nonce" and the user's capability
	 * before this action runs.
	 *
	 * @param WC_Product $product Product being saved.
	 */
	public function save_field( $product ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified by WooCommerce (WC_Admin_Meta_Boxes::save_meta_boxes).
		if ( ! isset( $_POST[ self::META_KEY ] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- See above.
		$raw = trim( sanitize_text_field( wp_unslash( $_POST[ self::META_KEY ] ) ) );

		if ( '' === $raw ) {
			$product->delete_meta_data( self::META_KEY );
			return;
		}

		$url = esc_url_raw( $raw, array( 'http', 'https' ) );
		if ( '' === $url || ! wp_parse_url( $url, PHP_URL_HOST ) ) {
			WC_Admin_Meta_Boxes::add_error(
				/* translators: %s: entered URL */
				sprintf( __( 'Thumbnail Link: "%s" is not a valid URL. Please enter a complete address starting with https://.', 'thumbnail-link-for-woo' ), $raw )
			);
			return;
		}

		$product->update_meta_data( self::META_KEY, $url );
	}

	/**
	 * Opens the product link in classic shop loops.
	 */
	public function loop_link_open() {
		global $product;

		$url = self::get_url( $product );
		if ( '' === $url ) {
			woocommerce_template_loop_product_link_open();
			return;
		}

		$attributes = '';
		foreach ( $this->get_link_attributes( $product ) as $name => $value ) {
			$attributes .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		printf(
			'<a href="%s" class="woocommerce-LoopProduct-link woocommerce-loop-product__link"%s>',
			esc_url( $url ),
			$attributes // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
		);
	}

	/**
	 * Replaces the product link in the Product Image and Post Title blocks.
	 *
	 * @param string   $content Rendered block.
	 * @param array    $block   Parsed block.
	 * @param WP_Block $instance Block instance.
	 * @return string
	 */
	public function filter_block_link( $content, $block, $instance ) {
		if ( is_admin() || false === strpos( $content, '<a ' ) || empty( $instance->context['postId'] ) ) {
			return $content;
		}

		$post_id = (int) $instance->context['postId'];
		if ( 'product' !== get_post_type( $post_id ) ) {
			return $content;
		}

		$product = wc_get_product( $post_id );
		$url     = self::get_url( $product );
		if ( '' === $url ) {
			return $content;
		}

		$permalink = get_permalink( $post_id );
		$processor = new WP_HTML_Tag_Processor( $content );

		while ( $processor->next_tag( 'a' ) ) {
			if ( $processor->get_attribute( 'href' ) !== $permalink ) {
				continue;
			}
			$processor->set_attribute( 'href', esc_url( $url ) );
			foreach ( $this->get_link_attributes( $product ) as $name => $value ) {
				$processor->set_attribute( $name, $value );
			}
		}

		return $processor->get_updated_html();
	}
}
