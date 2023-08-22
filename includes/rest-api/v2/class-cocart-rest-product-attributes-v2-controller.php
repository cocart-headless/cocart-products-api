<?php
/**
 * REST API: CoCart_REST_Product_Attributes_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Products\v2
 * @since   3.1.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for returning product attributes via the REST API (API v2).
 *
 * This REST API controller handles requests to return product attributes
 * via "cocart/v2/products/attributes" endpoint.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_REST_Product_Attributes_V2_Controller extends CoCart_Product_Attributes_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

}
