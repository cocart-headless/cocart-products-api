<?php
/**
 * CoCart Settings: Products API Settings.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Settings
 * @since   4.0.0
 * @license GPL-2.0+
 */

namespace CoCart\Admin;

use CoCart\Admin\Settings;
use CoCart\Admin\SettingsPage as Page;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductsAPISettings extends Page {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->id    = 'products';
		$this->label = esc_html__( 'Products API', 'cart-rest-api-for-woocommerce' );

		parent::__construct();
	} // END __construct()

	/**
	 * Get settings array.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings[] = array(
			'id'    => $this->id,
			'type'  => 'title',
			'title' => __( 'Default Configurations', 'cart-rest-api-for-woocommerce' ),
			'desc'  => __( 'Configurations set below apply to the default behaviour when accessing the products API.', 'cart-rest-api-for-woocommerce' ),
		);

		$settings[] = array(
			'title'    => esc_html__( 'Catalog visibility', 'cart-rest-api-for-woocommerce' ),
			'id'       => 'catalog_visibility',
			'type'     => 'select',
			'default'  => 'visible',
			'options'  => array(
				'visible' => __( 'Shop and search results', 'cart-rest-api-for-woocommerce' ),
				'catalog' => __( 'Shop only', 'cart-rest-api-for-woocommerce' ),
				'search'  => __( 'Search results only', 'cart-rest-api-for-woocommerce' ),
				'hidden'  => __( 'Hidden', 'cart-rest-api-for-woocommerce' ),
			),
			'css'      => 'min-width:10em;',
			'autoload' => true,
		);

		$settings[] = array(
			'title'    => esc_html__( 'Order By', 'cart-rest-api-for-woocommerce' ),
			'id'       => 'orderby',
			'type'     => 'select',
			'default'  => 'date',
			'options'  => array(
				'date'           => __( 'Date Added', 'cart-rest-api-for-woocommerce' ),
				'id'             => __( 'Product ID', 'cart-rest-api-for-woocommerce' ),
				'menu_order'     => __( 'Menu Order', 'cart-rest-api-for-woocommerce' ),
				'include'        => __( 'Posts In', 'cart-rest-api-for-woocommerce' ),
				'title'          => __( 'Product Title', 'cart-rest-api-for-woocommerce' ),
				'slug'           => __( 'Product Slug', 'cart-rest-api-for-woocommerce' ),
				'name'           => __( 'Product Name', 'cart-rest-api-for-woocommerce' ),
				'popularity'     => __( 'Popularity', 'cart-rest-api-for-woocommerce' ),
				'alphabetical'   => __( 'Alphabetical', 'cart-rest-api-for-woocommerce' ),
				'reverse_alpha'  => __( 'Reverse Alphabetical', 'cart-rest-api-for-woocommerce' ),
				'by_stock'       => __( 'Stock', 'cart-rest-api-for-woocommerce' ),
				'review_count'   => __( 'Review Count', 'cart-rest-api-for-woocommerce' ),
				'on_sale_first'  => __( 'On Sale First', 'cart-rest-api-for-woocommerce' ),
				'featured_first' => __( 'Featured First', 'cart-rest-api-for-woocommerce' ),
				'price_asc'      => __( 'Price High', 'cart-rest-api-for-woocommerce' ),
				'price_desc'     => __( 'Price Low', 'cart-rest-api-for-woocommerce' ),
				'sales'          => __( 'Sales', 'cart-rest-api-for-woocommerce' ),
				'rating'         => __( 'Rating', 'cart-rest-api-for-woocommerce' ),
			),
			'css'      => 'min-width:10em;',
			'autoload' => true,
		);

		$settings[] = array(
			'title'    => esc_html__( 'Order', 'cart-rest-api-for-woocommerce' ),
			'id'       => 'order',
			'type'     => 'select',
			'default'  => 'DESC',
			'options'  => array(
				'DESC' => 'DESC',
				'ASC'  => 'ASC',
			),
			'autoload' => true,
		);

		$settings[] = array(
			'title'   => esc_html__( 'Variations as single products?', 'cart-rest-api-for-woocommerce' ),
			'id'      => 'include_variations',
			'type'    => 'checkbox',
			'default' => 'no',
			'desc'    => esc_html__( 'If enabled, variations will return without the parent product.', 'cart-rest-api-for-woocommerce' ),
		);

		$settings[] = array(
			'id'   => $this->id,
			'type' => 'sectionend',
		);

		return $settings;
	} // END get_settings()

	/**
	 * Output the settings.
	 *
	 * @access public
	 */
	public function output() {
		$settings = $this->get_settings();

		Settings::output_fields( $this->id, $settings );
	} // END output()

} // END class

return new ProductsAPISettings();
