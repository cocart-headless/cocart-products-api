<?php
/**
 * REST API: CoCart_REST_Product_Variations_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v2
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 */

use CoCart\Utilities\APIPermission;
use CoCart\DataException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for returning product variations via the REST API (API v2).
 *
 * This REST API controller handles requests to return product
 * details of each variations of a variable product
 * via "cocart/v2/products/<id>/variations" endpoint.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_REST_Product_Variations_V2_Controller extends CoCart_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Register the routes for product variations.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Variable Product Variations - cocart/v2/products/32/variations (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'        => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => array( 'CoCart\Utilities\APIPermission', 'has_api_permission' ),
				),
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get a single variation - cocart/v2/products/32/variations/148 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'product_id' => array(
							'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
						'id'         => array(
							'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
					),
					'permission_callback' => array( $this, 'validate_variation' ),
				),
				'allow_batch' => array( 'v1' => true ),
				'schema'      => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Validate the variation exists and is part of the variable product.
	 *
	 * @throws CoCart\DataException Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Added API Permission check.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return bool|WP_Error
	 */
	public function validate_variation( $request ) {
		try {
			$api_permission = APIPermission::has_api_permission( $request );

			if ( ! $api_permission ) {
				return $api_permission;
			}

			$parent    = wc_get_product( (int) $request['product_id'] );
			$variation = wc_get_product( (int) $request['id'] );

			$variation_ids = $parent->get_children();

			// Validate the variation product exists.
			if ( ! $variation || 0 === $variation->get_id() ) {
				throw new DataException( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Validate the variation requested to see if it is not one of the variations for the variable product.
			if ( ! in_array( $variation->get_id(), $variation_ids ) ) {
				throw new DataException( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), 404 );
			}
		} catch ( DataException $e ) {
			return \CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}

		return true;
	} // END validate_variation()

	/**
	 * Prepare a single variation output for response.
	 *
	 * @uses CoCart_REST_Products_V2_Controller
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		$controller = new CoCart_REST_Products_V2_Controller();

		$data     = $controller->get_variation_product_data( $product );
		$data     = $controller->add_additional_fields_to_object( $data, $request );
		$data     = $controller->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to product type being prepared for the response.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  The product object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object_v2", $response, $product, $request );
	} // END prepare_object_for_response()

	/**
	 * Get a single item.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Added CoCart headers to response.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$product = wc_get_product( (int) $request['id'] );

		$data     = $this->prepare_object_for_response( $product, $request );
		$response = rest_ensure_response( $data );

		// Add timestamp of response.
		$response->header( 'CoCart-Timestamp', time() );

		// Add version of CoCart.
		$response->header( 'CoCart-Version', COCART_VERSION );

		return $response;
	} // END get_item()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $links Links for the given product.
	 */
	protected function prepare_links( $product, $request ) {
		$controller = new CoCart_REST_Products_V2_Controller();

		$links = $controller->prepare_links( $product, $request );

		$rest_base = str_replace( '(?P<product_id>[\d]+)', $product->get_parent_id(), $this->rest_base );

		$links['self']['href']       = rest_url( sprintf( '/%s/%s/%d', $this->namespace, $rest_base, $product->get_id() ) );
		$links['collection']['href'] = rest_url( sprintf( '/%s/%s', $this->namespace, $rest_base ) );

		// Rename link type and add permalink for the parent product.
		$links['up'] = array(
			'permalink' => cocart_get_permalink( get_permalink( $product->get_parent_id() ) ),
			'href'      => $links['parent_product']['href'],
		);

		unset( $links['parent_product'] );

		return $links;
	} // END prepare_links()

} // END class
