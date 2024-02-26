<?php
/**
 * REST API: Abstract Rest Terms v2 controller.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v2
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 */

use CoCart\Utilities\APIPermission;
use CoCart\DataException;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_REST_Terms_V2_Controller' ) ) {

	/**
	 * CoCart REST API v2 - Terms controller class.
	 *
	 * @package CoCart Products/RESTAPI
	 * @extends CoCart_REST_Terms_Controller
	 */
	abstract class CoCart_REST_Terms_V2_Controller extends CoCart_REST_Terms_Controller {

		/**
		 * Endpoint namespace.
		 *
		 * @var string
		 */
		protected $namespace = 'cocart/v2';

		/**
		 * Register the routes for terms.
		 *
		 * @access public
		 */
		public function register_routes() {
			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
					'allow_batch' => array( 'v1' => true ),
					'schema'      => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<id>[\d]+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'id'      => array(
								'description' => __( 'Unique identifier for the resource.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
							),
							'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						),
					),
					'allow_batch' => array( 'v1' => true ),
					'schema'      => array( $this, 'get_public_item_schema' ),
				)
			);
		} // END register_routes()

		/**
		 * Check permissions.
		 *
		 * @throws DataException Exception if invalid data is detected.
		 *
		 * @access protected
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @param WP_REST_Request $request The request object.
		 *
		 * @return bool|WP_Error
		 *
		 * @ignore Function ignored when parsed into Code Reference.
		 */
		protected function check_permissions( $request ) {
			try {
				$api_permission = APIPermission::has_api_permission( $request );

				if ( ! $api_permission ) {
					return $api_permission;
				}

				// Get taxonomy.
				$taxonomy = $this->get_taxonomy( $request );

				if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
					throw new DataException( 'cocart_taxonomy_invalid', __( 'Taxonomy does not exist.', 'cart-rest-api-for-woocommerce' ), 404 );
				}

				// Check permissions for a single term.
				$id = intval( $request['id'] );

				if ( $id ) {
					$term = get_term( $id, $taxonomy );

					if ( is_wp_error( $term ) || ! $term || $term->taxonomy !== $taxonomy ) {
						throw new DataException( 'cocart_term_invalid', __( 'Term does not exist.', 'cart-rest-api-for-woocommerce' ), 404 );
					}

					return true;
				}
			} catch ( DataException $e ) {
				return \CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
			}

			return true;
		} // END check_permissions()

	} // END class

}
