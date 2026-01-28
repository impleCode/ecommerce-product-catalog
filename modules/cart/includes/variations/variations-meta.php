<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages framework folder
 *
 * Here framework folder files defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog-pro/framework
 * @author 		Norbert Dreszer
 */
class ic_cart_variations_meta {

	function __construct() {
		add_action( 'add_product_metaboxes', array( $this, 'add_metaboxes' ) );
		add_filter( 'product_meta_save', array( $this, 'save' ) );
	}

	/**
	 * Adds product variations metabox
	 *
	 */
	function add_metaboxes() {
		$product_variations_settings = get_product_variations_settings();
		if ( !empty( $product_variations_settings[ 'count' ] ) ) {
			add_meta_box( 'al_cart_variations', __( 'Product Variations', 'ecommerce-product-catalog' ), array( __CLASS__, 'meta_box' ), 'al_product', 'normal', 'default' );
		}
	}

	static function meta_box() {
		global $post;
		$product_variations_settings = get_product_variations_settings();
		echo '<div class="al-box info">' . __( 'Only variations with non-empty values will show up on the product page.', 'ecommerce-product-catalog' ) . '</div>';
		echo '<table class="variations">
	<thead><tr>
	<th></th>
	<th class="title"><b>' . __( 'Name', 'ecommerce-product-catalog' ) . '</b></th>
	<th></th>
	<th class="title"><b>' . __( 'Values', 'ecommerce-product-catalog' ) . '</b></th>';
		$mod_drop					 = false;
		if ( ic_is_variations_price_effect_active() ) {
			$mod_drop = true;
			echo '<th class="title"><b>' . __( 'Price mod', 'ecommerce-product-catalog' ) . '</b></th>';
		}
		if ( ic_is_variations_shipping_effect_active() ) {
			$mod_drop = true;
			echo '<th class="title"><b>' . __( 'Shipping mod', 'ecommerce-product-catalog' ) . '</b></th>';
		}
		if ( $mod_drop ) {

			echo '<th><th>';
		}
		echo '</tr>
	</thead>
	<tbody>';
		for ( $i = 1; $i <= $product_variations_settings[ 'count' ]; $i++ ) {
			$variation_label								 = get_post_meta( $post->ID, $i . '_variation_label', true );
			$variation_values								 = get_post_meta( $post->ID, $i . '_variation_values', true );
			$variation_prices								 = get_post_meta( $post->ID, $i . '_variation_prices', true );
			$variation_shipping								 = get_post_meta( $post->ID, $i . '_variation_shipping', true );
			$variation_mod									 = get_post_meta( $post->ID, $i . '_variation_mod', true );
			$product_variations_settings[ 'labels' ][ $i ]	 = isset( $product_variations_settings[ 'labels' ][ $i ] ) ? $product_variations_settings[ 'labels' ][ $i ] : '';
			$product_variations_settings[ 'values' ][ $i ]	 = isset( $product_variations_settings[ 'values' ][ $i ] ) ? $product_variations_settings[ 'values' ][ $i ] : '';
			if ( ic_is_variations_price_effect_active() ) {
				$product_variations_settings[ 'prices' ][ $i ] = isset( $product_variations_settings[ 'prices' ][ $i ] ) ? $product_variations_settings[ 'prices' ][ $i ] : '';
			}
			if ( ic_is_variations_shipping_effect_active() ) {
				$product_variations_settings[ 'shipping' ][ $i ] = isset( $product_variations_settings[ 'shipping' ][ $i ] ) ? $product_variations_settings[ 'shipping' ][ $i ] : '';
			}
			if ( $mod_drop ) {
				$product_variations_settings[ 'mod' ][ $i ] = isset( $product_variations_settings[ 'mod' ][ $i ] ) ? $product_variations_settings[ 'mod' ][ $i ] : '+';
			}
			if ( is_ic_new_product_screen() ) {
				$variation_label	 = !empty( $variation_label ) ? $variation_label : $product_variations_settings[ 'labels' ][ $i ];
				$variation_values	 = !empty( $variation_values ) ? $variation_values : $product_variations_settings[ 'values' ][ $i ];
				if ( ic_is_variations_price_effect_active() ) {
					$variation_prices = !empty( $variation_prices ) ? $variation_prices : $product_variations_settings[ 'prices' ][ $i ];
				}
				if ( ic_is_variations_shipping_effect_active() ) {
					$variation_shipping = !empty( $variation_shipping ) ? $variation_shipping : $product_variations_settings[ 'shipping' ][ $i ];
				}
				if ( $mod_drop ) {
					$variation_mod = !empty( $variation_mod ) ? $variation_mod : $product_variations_settings[ 'mod' ][ $i ];
				}
			}
			$variation_values	 = is_array( $variation_values ) ? $variation_values : explode( "\r\n", $variation_values );
			$values_fields		 = '';
			$prices_fields		 = '';
			$shipping_fields	 = '';
			$edit_buttons		 = '';
			if ( empty( $variation_values ) ) {
				$variation_values[] = '';
			}
			if ( ic_is_variations_price_effect_active() ) {
				$variation_prices = is_array( $variation_prices ) ? $variation_prices : explode( "\r\n", $variation_prices );
			}
			if ( ic_is_variations_shipping_effect_active() ) {
				$variation_shipping = is_array( $variation_shipping ) ? $variation_shipping : explode( "\r\n", $variation_shipping );
			}

			foreach ( $variation_values as $key => $variation_value ) {
				if ( !empty( $key ) && (empty( $variation_value ) && !is_numeric( $variation_value )) ) {
					continue;
				}
				$values_fields .= '<input type="text" data-var_num="' . $i . '" data-var_lp="' . $key . '" name="' . $i . '_variation_values[]" value="' . $variation_value . '">';
				if ( ic_is_variations_price_effect_active() ) {
					$variation_prices[ $key ]	 = isset( $variation_prices[ $key ] ) ? $variation_prices[ $key ] : '';
					$prices_fields				 .= '<input class="admin-number-field" type="number" step="0.01" name="' . $i . '_variation_prices[]" value="' . $variation_prices[ $key ] . '">';
				}
				if ( ic_is_variations_shipping_effect_active() ) {
					$variation_shipping[ $key ]	 = isset( $variation_shipping[ $key ] ) ? $variation_shipping[ $key ] : '';
					$shipping_fields			 .= '<input class="admin-number-field" type="number" step="0.01" name="' . $i . '_variation_shipping[]" value="' . $variation_shipping[ $key ] . '">';
				}
			}
			?>
			<tr class="<?php echo apply_filters( 'ic_cat_variation_tr_class', '', $post->ID, $i ) ?>">
				<td class="lp-column">
					<?php echo $i ?>.
				</td>
				<td class="product-variation-label-column">
					<input class="product-variation-label" type="text" data-var_num="<?php echo $i ?>" name="<?php echo $i ?>_variation_label" value="<?php echo $variation_label ?>"/>
				</td>
				<td class="lp-column">:</td>
				<td class="variation_values">
					<?php echo $values_fields; ?>
				</td>
				<?php
				if ( ic_is_variations_price_effect_active() ) {
					?>
					<td class="variation_prices">
						<?php echo $prices_fields; ?>
					</td>
					<?php
				}
				if ( ic_is_variations_shipping_effect_active() ) {
					?>
					<td class="variation_shipping">
						<?php echo $shipping_fields; ?>
					</td>
					<?php
				}
				?>
				<td class="variation_details">
					<?php echo $edit_buttons; ?>
				</td>
				<?php
				if ( $mod_drop ) {
					?>
					<td class="variation_modificator"><?php echo self::get_price_modificator_dropdown( $i . '_variation_mod', $variation_mod ) ?></td>
				<?php }
				?>
				<td style="vertical-align: top;padding-top: 13px;">
					<div class="button button-small add_variation"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add', 'ecommerce-product-catalog' ) ?></div>
					<?php do_action( 'ic_cat_shopping_variation_buttons', $post->ID, $i ) ?>
				</td>
			</tr> <?php }
				?>
		</tbody></table><?php
		do_action( 'ic_variations_metabox', $post->ID );
	}

	static function get_price_modificator_dropdown( $name, $value ) {
		$drop	 = '<select class="price_modificator" name="' . $name . '">';
		$drop	 .= '<option value="+" ' . selected( '+', $value, 0 ) . '>+</option>';
		$drop	 .= '<option value="%" ' . selected( '%', $value, 0 ) . '>%</option>';
		$drop	 .= '</select>';
		return $drop;
	}

	function save( $product_meta ) {
		$product_variations_settings = get_product_variations_settings();
		for ( $i = 1; $i <= $product_variations_settings[ 'count' ]; $i++ ) {
			if ( !empty( $_POST[ $i . '_variation_values' ] ) ) {
				$product_meta[ $i . '_variation_label' ]	 = !empty( $_POST[ $i . '_variation_label' ] ) ? $_POST[ $i . '_variation_label' ] : '';
				$product_meta[ $i . '_variation_values' ]	 = !empty( $_POST[ $i . '_variation_values' ] ) ? $this->filter_variation_values( $_POST[ $i . '_variation_values' ] ) : '';
				$product_meta[ $i . '_variation_prices' ]	 = isset( $_POST[ $i . '_variation_prices' ] ) ? $this->filter_variation_values( $_POST[ $i . '_variation_prices' ] ) : '';
				$product_meta[ $i . '_variation_shipping' ]	 = isset( $_POST[ $i . '_variation_shipping' ] ) ? $this->filter_variation_values( $_POST[ $i . '_variation_shipping' ] ) : '';
				$product_meta[ $i . '_variation_mod' ]		 = !empty( $_POST[ $i . '_variation_mod' ] ) ? $_POST[ $i . '_variation_mod' ] : '';
				$product_meta[ $i . '_variation_type' ]		 = !empty( $_POST[ $i . '_variation_type' ] ) ? $_POST[ $i . '_variation_type' ] : 'dropdown';
				$product_meta								 = apply_filters( 'save_product_variations', $product_meta, $i );
			}
		}
		return $product_meta;
	}

	function filter_variation_values( $values ) {
		//$values		 = explode( "\r\n", rtrim( $values ) );
		$new_values = array();
		foreach ( $values as $value ) {
			$value			 = str_replace( array( ', ', '_', '::' ), array( '.', ' ', ':' ), $value );
			$new_values[]	 = $value;
		}
		return $new_values;
	}

}

global $ic_cart_variations_meta;
$ic_cart_variations_meta = new ic_cart_variations_meta;
