<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages licenses columns
 *
 * Here all license columns defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-licenses/includes
 * @author        impleCode
 */
add_filter( 'manage_edit-al_product_columns', 'add_product_columns' );

function add_product_columns( $product_columns ) {
	//$new_columns			 = $product_columns;
	foreach ( $product_columns as $index => $column ) {
		if ( $index == 'cb' ) {
			$new_columns[ $index ] = $column;
			$new_columns['id']     = __( 'ID', 'ecommerce-product-catalog' );
			$new_columns           = apply_filters( 'product_columns_after_id', $new_columns );
			$new_columns['image']  = __( 'Image', 'ecommerce-product-catalog' );
		} else if ( $index == 'title' ) {
			$new_columns[ $index ] = __( 'Product Name', 'ecommerce-product-catalog' );
			$new_columns           = apply_filters( 'product_columns_after_name', $new_columns );
		} else if ( $index == 'date' ) {
			$new_columns['taxonomy-al_product-cat'] = __( 'Product Categories', 'ecommerce-product-catalog' );
			$new_columns['shortcode']               = __( 'Shortcode', 'ecommerce-product-catalog' );
			$new_columns                            = apply_filters( 'product_columns_before_date', $new_columns );
			$new_columns[ $index ]                  = $column;
		} else {
			$new_columns[ $index ] = $column;
		}
	}

	return apply_filters( 'product_columns', $new_columns );
}

add_action( 'manage_al_product_posts_custom_column', 'manage_product_columns', 10, 2 );

function manage_product_columns( $column_name, $product_id ) {
	switch ( $column_name ) {
		case 'id':
			echo $product_id;
			break;
		case 'shortcode':
			echo '[show_products product="' . $product_id . '"]';
			break;
		case 'image':
			echo the_post_thumbnail( array( 40, 40 ) );
			break;
		case 'product_cat':
			echo get_the_term_list( $product_id, 'al_product-cat', '', ', ', '' );
			break;

		default:
			do_action( 'ic_manage_product_columns', $column_name, $product_id );
			break;
	}
}

add_filter( 'manage_edit-al_product_sortable_columns', 'product_sortable_columns' );

function product_sortable_columns( $columns ) {
	$columns['price']                   = 'price';
	$columns['id']                      = 'ID';
	$columns['taxonomy-al_product-cat'] = 'taxonomy-al_product-cat';

	//To make a column 'un-sortable' remove it from the array
	//unset($columns['date']);

	return apply_filters( 'product_sortable_columns', $columns );
}

add_filter( 'posts_orderby', 'orderby_product_cat', 10, 2 );

/**
 * Order by product categories when clicking on table header label
 *
 * @param string $orderby
 * @param object $wp_query
 *
 * @return string
 * @global object $wpdb
 */
function orderby_product_cat( $orderby, $wp_query ) {
	global $wpdb;

	if ( is_admin() && isset( $wp_query->query['orderby'] ) && 'taxonomy-al_product-cat' == $wp_query->query['orderby'] ) {
		$orderby = "(
			SELECT GROUP_CONCAT(name ORDER BY name ASC)
			FROM $wpdb->term_relationships
			INNER JOIN $wpdb->term_taxonomy USING (term_taxonomy_id)
			INNER JOIN $wpdb->terms USING (term_id)
			WHERE $wpdb->posts.ID = object_id
			AND taxonomy = 'al_product-cat'
			GROUP BY object_id
		) ";
		$orderby .= ( 'ASC' == strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
	}

	return $orderby;
}

function restrict_listings_by_product_cat() {
	global $typenow;
	global $wp_query;
	if ( $typenow == 'al_product' ) {
		$taxonomy         = 'al_product-cat';
		$current_taxonomy = get_taxonomy( $taxonomy );
		$selected         = isset( $wp_query->query['al_product-cat'] ) ? $wp_query->query['al_product-cat'] : '';
		wp_dropdown_categories(
			array(
				'walker'          => new ic_walker_tax_slug_dropdown(),
				'value'           => 'slug',
				'show_option_all' => __( "All", "ecommerce-product-catalog" ) . ' ' . $current_taxonomy->label,
				'taxonomy'        => $taxonomy,
				'name'            => 'al_product-cat',
				'orderby'         => 'name',
				'selected'        => $selected,
				'hierarchical'    => true,
				'depth'           => 0,
				//'show_count'		 => true,
				'hide_empty'      => true,
				'hide_if_empty'   => true,
				'value_field'     => 'slug'
			)
		);
	}
}

add_action( 'restrict_manage_posts', 'restrict_listings_by_product_cat' );

class ic_walker_tax_slug_dropdown extends Walker_CategoryDropdown {

	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad      = str_repeat( '&nbsp;', $depth * 3 );
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( ! isset( $args['value'] ) ) {
			$args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
		}

		$value = ( $args['value'] == 'slug' ? $category->slug : $category->term_id );

		$output .= "\t<option class=\"level-$depth\" value=\"" . $value . "\"";
		if ( $value === (string) $args['selected'] ) {
			$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad . $cat_name;
		//if ( $args[ 'show_count' ] ) {
		$output .= '&nbsp;&nbsp;(' . $category->count . ')';
		//	}

		$output .= "</option>\n";

		return $output;
	}

}

add_action( 'quick_edit_custom_box', 'display_custom_quickedit_product' );

/**
 * Adds quick edit support for product fields
 *
 * @staticvar boolean $product_quick_edit_nonce
 *
 * @param str $column_name
 */
function display_custom_quickedit_product( $column_name ) {
	/*
	static $product_quick_edit_nonce = true;
	if ( $product_quick_edit_nonce ) {
		$product_quick_edit_nonce = false;
		wp_nonce_field( plugin_basename( __FILE__ ), 'al_product_quick_edit_nonce' );
	}
	*/
	?>
    <fieldset class="inline-edit-col-right inline-edit-product">
        <div class="inline-edit-col column-<?php echo $column_name; ?>">
            <label class="inline-edit-group">
				<?php
				do_action( 'product_quickedit', $column_name );
				?>
            </label>
        </div>
    </fieldset>
	<?php
}

add_action( 'save_post', 'save_product_quick_edit' );

/**
 * Handles quick edit save for products
 *
 * @param int $product_id
 */
function save_product_quick_edit( $product_id ) {
	$slug = 'al_product';
	if ( empty( $_POST['post_type'] ) ) {
		return;
	}
	if ( strpos( $slug, $_POST['post_type'] ) === false ) {
		return;
	}
	if ( ! current_user_can( 'edit_product', $product_id ) ) {
		return;
	}
	/*
	$_POST += array( "{$slug}_quick_edit_nonce" => '' );
	if ( !wp_verify_nonce( $_POST[ "{$slug}_quick_edit_nonce" ], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	*/
	if ( empty( $_POST['_inline_edit'] ) || ! wp_verify_nonce( $_POST['_inline_edit'], 'inlineeditnonce' ) ) {
		return;
	}
	do_action( 'save_product_quick_edit', $product_id );
}

add_filter( 'views_edit-al_product', 'ic_admin_products_tabs', 10, 1 );

function ic_admin_products_tabs( $views ) {
	do_action( 'ic_products_edit_screen' );
	ic_display_admin_products_tabs();

	return $views;
}

add_action( 'settings-menu', 'ic_admin_products_custom_tabs', 3 );

function ic_admin_products_custom_tabs() {
	$class          = '';
	$category_class = '';
	if ( is_ic_product_list_admin_screen() ) {
		$class = ' nav-tab-active';
	} else if ( is_ic_product_categories_admin_screen() || is_ic_product_categories_edit_admin_screen() ) {
		$category_class = ' nav-tab-active';
	}
	?>
    <a id="al_products" class="nav-tab<?php echo $class ?>"
       href="<?php echo admin_url( 'edit.php?post_type=al_product' ) ?>"><?php echo get_catalog_names( 'plural' ) ?></a>
    <a id="al_categories" class="nav-tab<?php echo $category_class ?>"
       href="<?php echo admin_url( 'edit-tags.php?taxonomy=al_product-cat&post_type=al_product' ) ?>"><?php echo __( 'Categories', 'ecommerce-product-catalog' ) ?></a>
	<?php
}

//add_action( 'settings-menu', 'ic_admin_add_new_custom_tabs', 99 );

function ic_admin_add_new_custom_tabs() {
	if ( current_user_can( 'publish_products' ) ) {
		?>
        <a id="add-new-product-page" class="page-title-action"
           href="<?php echo admin_url( 'post-new.php?post_type=al_product' ) ?>"><?php echo sprintf( __( 'Add new %s', 'ecommerce-product-catalog' ), get_catalog_names( 'singular' ) ) ?></a>
		<?php
	}
}

function ic_display_admin_products_tabs() {
	ic_product_settings_html();
}
