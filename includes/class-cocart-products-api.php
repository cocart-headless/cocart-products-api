<?php
/**
 * Handles support for Products API.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Products API
 * @since   2.8.1
 * @version 4.0.0
 */

namespace CoCart\ProductsAPI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Package {

	/**
	 * Package Version
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $version = '4.0.0';

	/**
	 * Initiate Package.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function init() {
		// Settings
		add_filter( 'cocart_get_settings_pages', array( __CLASS__, 'settings_page' ), 5 );

		// REST API Controllers
		add_action( 'cocart_rest_api_controllers', array( __CLASS__, 'dependencies' ) );
		add_filter( 'cocart_rest_api_get_rest_namespaces', array( __CLASS__, 'add_rest_namespace' ) );
	}

	/**
	 * Return the name of the package.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'CoCart Products API';
	} // END get_name()

	/**
	 * Return the version of the package.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	} // END get_version()

	/**
	 * Return the path to the package.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	} // END get_path()

	/**
	 * Adds settings for Products API.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function settings_page( $settings ) {
		$settings['products'] = include dirname( __FILE__ ) . '/settings/class-cocart-admin-settings-products.php';

		return $settings;
	} // END settings_page()

	/**
	 * Includes dependencies for the API.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function dependencies() {
		include_once dirname( __FILE__ ) . '/class-cocart-datetime.php';
	} // END dependencies()

	/**
	 * Adds the REST API namespaces.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function add_rest_namespace( $namespaces ) {
		$namespaces['cocart/v1'] = array_merge( $namespaces['cocart/v1'], self::get_v1_controllers() );
		$namespaces['cocart/v2'] = array_merge( $namespaces['cocart/v2'], self::get_v2_controllers() );

		return $namespaces;
	} // END add_rest_namespace()

	/**
	 * List of controllers in the cocart/v1 namespace.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @return array
	 */
	protected static function get_v1_controllers() {
		return array(
			'cocart-v1-product-attributes'      => 'CoCart_Product_Attributes_Controller',
			'cocart-v1-product-attribute-terms' => 'CoCart_Product_Attribute_Terms_Controller',
			'cocart-v1-product-categories'      => 'CoCart_Product_Categories_Controller',
			'cocart-v1-product-reviews'         => 'CoCart_Product_Reviews_Controller',
			'cocart-v1-product-tags'            => 'CoCart_Product_Tags_Controller',
			'cocart-v1-products'                => 'CoCart_Products_Controller',
			'cocart-v1-product-variations'      => 'CoCart_Product_Variations_Controller',
		);
	} // END get_v1_controllers()

	/**
	 * List of controllers in the cocart/v2 namespace.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @return array
	 */
	protected static function get_v2_controllers() {
		return array(
			'cocart-v2-product-attributes'      => 'CoCart_REST_Product_Attributes_V2_Controller',
			'cocart-v2-product-attribute-terms' => 'CoCart_REST_Product_Attribute_Terms_V2_Controller',
			'cocart-v2-product-categories'      => 'CoCart_REST_Product_Categories_V2_Controller',
			'cocart-v2-product-reviews'         => 'CoCart_REST_Product_Reviews_V2_Controller',
			'cocart-v2-product-tags'            => 'CoCart_REST_Product_Tags_V2_Controller',
			'cocart-v2-products'                => 'CoCart_REST_Products_V2_Controller',
			'cocart-v2-product-variations'      => 'CoCart_REST_Product_Variations_V2_Controller',
		);
	} // END get_v2_controllers()

} // END class.
