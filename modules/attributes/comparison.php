<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_attribute_comparison {

	function __construct() {
		add_action( 'attributes-settings', array( $this, 'settings_page' ) );
		add_action( 'product_details', array( $this, 'button' ), 10, 0 );
		add_action( 'product-settings-list', array( $this, 'register_setting' ) );
		add_filter( 'the_content', array( $this, 'add_shortcode' ) );
		add_shortcode( 'catalog_comparison', array( $this, 'shortcode' ) );
	}

	function shortcode() {
		$products  = $this->products();
		$shortcode = '';
		if ( ! empty( $products ) && is_array( $products ) ) {
			$rows      = $this->rows();
			$shortcode .= '<div class="ic-comparison-table-container">';
			$shortcode .= '<div class="table ic-comparison-table">';
			foreach ( $rows as $row ) {
				$shortcode .= '<div class="table-row">';
				foreach ( $products as $i => $product_id ) {
					switch ( $row ) {
						case 'name':
							$shortcode .= '<div class="table-cell">';
							if ( ! empty( $product_id ) ) {
								$shortcode .= get_product_name( $product_id );
							}
							$shortcode .= '</div>';
							break;
						case 'choose_compare':
							$shortcode .= '<div class="table-cell">';
							$shortcode .= $this->choose( $i, $products );
							$shortcode .= '</div>';
							break;
						case 'image':
							$shortcode .= '<div class="table-cell">';
							if ( ! empty( $product_id ) ) {
								$shortcode .= get_product_image( $product_id );
							}
							$shortcode .= '</div>';
							break;
						case 'short_desc':
							$shortcode .= '<div class="table-cell">';
							if ( ! empty( $product_id ) ) {
								$shortcode .= get_product_short_description( $product_id );
							}
							$shortcode .= '</div>';
							break;
						case 'attributes':
							$shortcode .= '<div class="table-cell">';
							if ( ! empty( $product_id ) ) {
								$shortcode .= get_product_attributes( $product_id );
							}
							$shortcode .= '</div>';
							break;
						default:
							$shortcode .= apply_filters( 'ic_comparison_table_row', '', $row, $product_id );
					}
				}
				$shortcode .= '</div>';
			}
			$shortcode .= '</div>';
			$shortcode .= '</div>';
		} else {
			$shortcode .= $this->choose();
		}

		return $shortcode;
	}

	function rows() {
		$rows = array( 'name', 'choose_compare', 'image', 'short_desc', 'attributes' );

		return apply_filters( 'ic_comparison_table_rows', $rows );
	}

	function products( $i = null ) {
		$max     = 2;
		$session = get_product_catalog_session();
		if ( ! isset( $session['comparison'] ) || ! is_array( $session['comparison'] ) ) {
			$session['comparison'] = array();
		}
		if ( ! empty( $_GET['compare'] ) ) {
			if ( ! is_array( $_GET['compare'] ) ) {
				$new_compare = intval( $_GET['compare'] );
				if ( ! empty( $new_compare ) && ! in_array( $new_compare, $session['comparison'] ) ) {
					$session['comparison'][] = $new_compare;
				}
			} else {
				$session['comparison'] = array_map( 'intval', array_filter( $_GET['compare'] ) );
			}
			set_product_catalog_session( $session );
		}
		ksort( $session['comparison'] );

		if ( count( $session['comparison'] ) < $max ) {
			$session['comparison'][] = '';
		}
		if ( $i !== null ) {
			if ( isset( $session['comparison'][ $i ] ) ) {
				return intval( $session['comparison'][ $i ] );
			} else {
				return 0;
			}
		}

		return apply_filters( 'ic_comparison_products', $session['comparison'] );
	}

	function choose( $i = 0, $exclude = null ) {
		$names               = get_catalog_names( 'singular' );
		$label               = sprintf( __( 'Select %s to compare', 'ecommerce-product-catalog' ), $names );
		$selected_product_id = $this->products( $i );
		if ( ! empty( $selected_product_id ) && ( $key = array_search( $selected_product_id, $exclude ) ) !== false ) {
			unset( $exclude[ $key ] );
		}
		$select = '<form method="get" action="' . $this->url() . '">';

		foreach ( $exclude as $key => $product_id ) {
			if ( ! empty( $product_id ) && $product_id !== $selected_product_id ) {
				$select .= '<input type="hidden" name="compare[' . $key . ']" value="' . $product_id . '" >';
			}
		}
		$select .= ic_select_product( $label, $selected_product_id, 'compare[' . $i . ']', 'ic_self_submit', 0, null, $exclude );
		$select .= '</form>';

		return $select;
	}

	function add_shortcode( $content ) {
		if ( $this->is_comparison() && ! has_shortcode( $content, 'catalog_comparison' ) ) {
			$content .= '[catalog_comparison]';
		}

		return $content;
	}

	function is_comparison() {
		$page_id = $this->page_id();
		if ( ! empty( $page_id ) && is_ic_page( $page_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Registers advanced attributes settings
	 *
	 */
	function register_setting() {
		register_setting( 'product_attributes', 'ic_attributes_compare' );
	}

	function button() {
		$product_id = ic_get_product_id();
		$url        = $this->url( $product_id );
		if ( empty( $url ) ) {
			return;
		}
		?>
        <div class="ic-compare-container">
            <a class="button ic-compare-product" href="<?php echo $url ?>">
                <span><?php echo $this->label() ?></span>
            </a>
        </div>
		<?php
	}

	function label() {
		$names   = get_catalog_names( 'singular' );
		$default = sprintf( __( 'Compare %s', 'ecommerce-product-catalog' ), $names );

		return $default;
	}

	function url( $product_id = null ) {
		$page_id = $this->page_id();
		if ( empty( $page_id ) ) {
			return '';
		}

		$url = get_permalink( $page_id );
		if ( ! empty( $url ) && ! empty( $product_id ) ) {
			$url = add_query_arg( 'compare', $product_id, $url );
		}
		if ( ! empty( $url ) ) {
			return $url;
		}

		return '';
	}

	function settings_page() {
		$settings = $this->settings();
		?>
        <h3><?php _e( 'Comparison', 'ecommerce-product-catalog' ); ?></h3>
        <table>
            <tr>
                <td><?php _e( 'Comparison Page', 'ecommerce-product-catalog' ) ?>:</td>
                <td>
					<?php
					ic_select_page( 'ic_attributes_compare[url]', __( 'Comparison Disabled', 'ecommerce-product-catalog' ), $settings['url'] );
					?>
                </td>
            </tr>
			<?php do_action( 'ic_comparison_settings_html', $settings ) ?>
        </table>
		<?php
	}

	function settings() {
		$settings = get_option( 'ic_attributes_compare', array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		$settings['url'] = isset( $settings['url'] ) ? $settings['url'] : '';

		return apply_filters( 'ic_attribute_comparison_settings', $settings );
	}

	function page_id() {
		$settings = $this->settings();
		if ( ! empty( $settings['url'] ) ) {
			$page_id = intval( $settings['url'] );
			if ( ! empty( $page_id ) ) {
				return $page_id;
			}
		}

		return '';
	}

}

global $ic_attribute_comparison;
$ic_attribute_comparison = new ic_attribute_comparison;
