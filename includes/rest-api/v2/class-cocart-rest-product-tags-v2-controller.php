<?php
/**
 * REST API: CoCart_REST_Product_Tags_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v2
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for returning product tags via the REST API (API v2).
 *
 * This REST API controller handles requests to return product tags
 * via "cocart/v2/products/tags" endpoint.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_REST_Product_Tags_V2_Controller extends CoCart_Product_Tags_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

} // END class
