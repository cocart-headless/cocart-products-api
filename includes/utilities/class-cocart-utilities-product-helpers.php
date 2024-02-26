<?php
/**
 * Utilities: Helpers class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.0.0 Introduced.
 */

namespace CoCart\ProductsAPI\Utilities;

use CoCart\Utilities\MonetaryFormatting;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to handle product functions for the API.
 *
 * @since 4.0.0 Introduced.
 */
class Helpers {

	//** Product images **//

	/**
	 * Returns product image sizes.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public static function get_product_image_sizes() {
		return apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );
	} // END get_product_image_sizes()

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array $images Array of image data.
	 */
	public static function get_images( $product ) {
		$images           = array();
		$attachment_ids   = array();
		$attachment_sizes = self::get_product_image_sizes();

		// Add featured image.
		if ( $product->get_image_id() ) {
			$attachment_ids[] = $product->get_image_id();
		}

		// Add gallery images.
		$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

		$attachments = array();

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = wc_placeholder_img_src( $size );
			}

			$featured = $position === 0 ? true : false; // phpcs:ignore WordPress.PHP.YodaConditions.NotYoda

			$images[] = array(
				'id'       => (int) $attachment_id,
				'src'      => $attachments,
				'name'     => get_the_title( $attachment_id ),
				'alt'      => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position' => (int) $position,
				'featured' => $featured,
			);
		}

		// Set a placeholder image if the product has no images set.
		if ( empty( $images ) ) {
			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( get_option( 'woocommerce_placeholder_image', 0 ), $size ) );
			}

			$images[] = array(
				'id'       => 0,
				'src'      => $attachments,
				'name'     => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'alt'      => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'position' => 0,
				'featured' => true,
			);
		}

		return $images;
	} // END get_images()

	//** Product Details **//

	/**
	 * Returns the product quantity minimum requirement.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WC_Product The product object.
	 *
	 * @return int Quantity
	 */
	public static function get_quantity_minimum_requirement( $product ) {
		return (int) apply_filters( 'cocart_quantity_minimum_requirement', $product->get_min_purchase_quantity(), $product );
	} // END get_quantity_minimum_requirement()

	/**
	 * Returns the product maximum quantity allowed.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WC_Product The product object.
	 *
	 * @return int Quantity
	 */
	public static function get_quantity_maximum_allowed( $product ) {
		return apply_filters( 'cocart_quantity_maximum_allowed', $product->get_max_purchase_quantity(), $product );
	} // END get_quantity_maximum_allowed()

	/**
	 * Returns the price range for variable or grouped product.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Added the request object as parameter.
	 *
	 * @param WC_Product|WC_Product_Variable $product          The product object.
	 * @param string                         $tax_display_mode If returned prices are incl or excl of tax.
	 * @param WP_REST_Request                $request          The request object.
	 *
	 * @return array
	 */
	public static function get_price_range( $product, $tax_display_mode, $request ) {
		$price = array();

		if ( $product->is_type( 'variable' ) && $product->has_child() || $product->is_type( 'variable-subscription' ) && $product->has_child() ) {
			$prices = $product->get_variation_prices( true );

			if ( empty( $prices['price'] ) ) {
				/**
				 * Filter the variable products empty prices.
				 *
				 * @since x.x.x Introduced.
				 *
				 * @param array
				 * @param WC_Product The project object.
				 */
				$price = apply_filters( 'cocart_products_variable_empty_price', array(), $product );
			} else {
				$min_price     = current( $prices['price'] );
				$max_price     = end( $prices['price'] );
				$min_reg_price = current( $prices['regular_price'] );
				$max_reg_price = end( $prices['regular_price'] );

				if ( $min_price !== $max_price ) {
					$price = array(
						'from' => MonetaryFormatting::format_money( $min_price, $request ),
						'to'   => MonetaryFormatting::format_money( $max_price, $request ),
					);
				} else {
					$price = array(
						'from' => MonetaryFormatting::format_money( $min_price, $request ),
						'to'   => '',
					);
				}
			}
		}

		if ( $product->is_type( 'grouped' ) ) {
			$children       = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
			$price_function = $this->get_price_from_tax_display_mode( $tax_display_mode );

			foreach ( $children as $child ) {
				if ( '' !== $child->get_price() ) {
					$child_prices[] = $price_function( $child );
				}
			}

			if ( ! empty( $child_prices ) ) {
				$price = array(
					'from' => MonetaryFormatting::format_money( min( $child_prices ), $request ),
					'to'   => MonetaryFormatting::format_money( max( $child_prices ), $request ),
				);
			}
		}

		/**
		 * Filters the products price range.
		 *
		 * @since x.x.x Introduced.
		 *
		 * @param array      $price   The current product price range.
		 * @param WC_Product $product The product object.
		 */
		$price_range = apply_filters( 'cocart_products_get_price_range', $price, $product );

		return $price_range;
	} // END get_price_range()

} // END class
