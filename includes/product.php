<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        Norbert Dreszer
 */
add_action( 'register_catalog_styles', 'frontend_scripts' );

/**
 * Registers product related front-end scripts
 */
function frontend_scripts() {
	if ( !is_admin() ) {
		if ( is_lightbox_enabled() && is_ic_product_gallery_enabled() ) {
			wp_register_script( 'colorbox', AL_PLUGIN_BASE_PATH . 'js/colorbox/jquery.colorbox-min.js', array( 'jquery' ) );
			wp_register_style( 'colorbox', AL_PLUGIN_BASE_PATH . 'js/colorbox/colorbox.css' );
			wp_register_script( 'al_product_scripts', AL_PLUGIN_BASE_PATH . 'js/product.js?' . filemtime( AL_BASE_PATH . '/js/product.js' ), array( 'jquery', 'colorbox' ) );
		} else {
			wp_register_script( 'al_product_scripts', AL_PLUGIN_BASE_PATH . 'js/product.js?' . filemtime( AL_BASE_PATH . '/js/product.js' ), array( 'jquery' ) );
		}
	}
}

add_action( 'init', 'create_product' );

/**
 * Registers products post type
 * @global type $wp_version
 */
function create_product() {
	global $wp_version;
	$slug = get_product_slug();
	if ( is_ic_product_listing_enabled() && get_integration_type() != 'simple' ) {
		$product_listing_t = $slug;
	} else {
		$product_listing_t = false;
	}
	$names		 = get_catalog_names();
	$query_var	 = get_product_query_var();
	if ( is_plural_form_active() ) {
		$labels = array(
			'name'				 => $names[ 'plural' ],
			'singular_name'		 => $names[ 'singular' ],
			'add_new'			 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'add_new_item'		 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'edit_item'			 => sprintf( __( 'Edit %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'new_item'			 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'view_item'			 => sprintf( __( 'View %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'search_items'		 => sprintf( __( 'Search %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'plural' ] ) ),
			'not_found'			 => sprintf( __( 'No %s found', 'al-ecommerce-product-catalog' ), $names[ 'plural' ] ),
			'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'al-ecommerce-product-catalog' ), $names[ 'plural' ] )
		);
	} else {
		$labels = array(
			'name'				 => $names[ 'plural' ],
			'singular_name'		 => $names[ 'singular' ],
			'add_new'			 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'add_new_item'		 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'edit_item'			 => sprintf( __( 'Edit %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'new_item'			 => sprintf( __( 'Add New %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'view_item'			 => sprintf( __( 'View %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ),
			'search_items'		 => sprintf( __( 'Search %s', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'plural' ] ) ),
			'not_found'			 => __( 'Nothing found', 'al-ecommerce-product-catalog' ),
			'not_found_in_trash' => __( 'Nothing found in trash', 'al-ecommerce-product-catalog' )
		);
	}
	if ( $wp_version < 3.8 ) {
		$reg_settings = array(
			'labels'				 => $labels,
			'public'				 => true,
			'has_archive'			 => $product_listing_t,
			'rewrite'				 => array( 'slug' => apply_filters( 'product_slug_value_register', $slug ), 'with_front' => false ),
			'query_var'				 => $query_var,
			'supports'				 => array( 'title', 'thumbnail' ),
			'register_meta_box_cb'	 => 'add_product_metaboxes',
			'taxonomies'			 => array( 'al_product_cat' ),
			'menu_icon'				 => plugins_url() . '/ecommerce-product-catalog/img/product.png',
			'capability_type'		 => 'product',
			'capabilities'			 => array(
				'publish_posts'			 => 'publish_products',
				'edit_posts'			 => 'edit_products',
				'edit_others_posts'		 => 'edit_others_products',
				'edit_published_posts'	 => 'edit_published_products',
				'edit_private_posts'	 => 'edit_private_products',
				'delete_posts'			 => 'delete_products',
				'delete_others_posts'	 => 'delete_others_products',
				'delete_private_posts'	 => 'delete_private_products',
				'delete_published_posts' => 'delete_published_products',
				'read_private_posts'	 => 'read_private_products',
				'edit_post'				 => 'edit_product',
				'delete_post'			 => 'delete_product',
				'read_post'				 => 'read_product',
			),
			'exclude_from_search'	 => false,
		);
	} else {
		$reg_settings = array(
			'labels'				 => $labels,
			'public'				 => true,
			'has_archive'			 => $product_listing_t,
			'rewrite'				 => array( 'slug' => apply_filters( 'product_slug_value_register', $slug ), 'with_front' => false ),
			'query_var'				 => $query_var,
			'supports'				 => array( 'title', 'thumbnail' ),
			'register_meta_box_cb'	 => 'add_product_metaboxes',
			'taxonomies'			 => array( 'al_product-cat' ),
			'capability_type'		 => 'product',
			'capabilities'			 => array(
				'publish_posts'			 => 'publish_products',
				'edit_posts'			 => 'edit_products',
				'edit_others_posts'		 => 'edit_others_products',
				'edit_published_posts'	 => 'edit_published_products',
				'edit_private_posts'	 => 'edit_private_products',
				'delete_posts'			 => 'delete_products',
				'delete_others_posts'	 => 'delete_others_products',
				'delete_private_posts'	 => 'delete_private_products',
				'delete_published_posts' => 'delete_published_products',
				'read_private_posts'	 => 'read_private_products',
				'edit_post'				 => 'edit_product',
				'delete_post'			 => 'delete_product',
				'read_post'				 => 'read_product',
			),
			'exclude_from_search'	 => false,
		);
	}
	register_post_type( 'al_product', $reg_settings );
}

function get_product_query_var() {
	$names		 = get_catalog_names();
	$query_var	 = sanitize_title( strtolower( $names[ 'singular' ] ) );
	$query_var	 = (strpos( $query_var, '%' ) !== false) ? __( 'product', 'al-ecommerce-product-catalog' ) : $query_var;
	return $query_var;
}

function product_icons() {
	global $post_type;
	?>
	<style>
	<?php if ( isset( $_GET[ 'post_type' ] ) == 'al_product' ) : ?>
			#icon-edit {
				background: transparent url('<?php echo plugins_url() . '/ecommerce-product-catalog/img/product-32.png'; ?>') no-repeat;
			}

	<?php endif; ?>
	</style>
	<?php
}

add_action( 'admin_head', 'product_icons' );

function add_product_metaboxes() {
	$names				 = get_catalog_names();
	$names[ 'singular' ] = ucfirst( $names[ 'singular' ] );
	add_meta_box( 'al_product_short_desc', sprintf( __( '%s Short Description', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), 'al_product_short_desc', 'al_product', apply_filters( 'short_desc_box_column', 'normal' ), apply_filters( 'short_desc_box_priority', 'default' ) );
	add_meta_box( 'al_product_desc', sprintf( __( '%s description', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), 'al_product_desc', 'al_product', apply_filters( 'desc_box_column', 'normal' ), apply_filters( 'desc_box_priority', 'default' ) );
	if ( is_ic_price_enabled() || is_ic_sku_enabled() ) {
		add_meta_box( 'al_product_price', sprintf( __( '%s Details', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), 'al_product_price', 'al_product', apply_filters( 'product_price_box_column', 'side' ), apply_filters( 'product_price_box_priority', 'default' ) );
	}
	if ( get_option( 'product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER ) > 0 ) {
		add_meta_box( 'al_product_shipping', sprintf( __( '%s Shipping', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), 'al_product_shipping', 'al_product', apply_filters( 'product_shipping_box_column', 'side' ), apply_filters( 'product_shipping_box_priority', 'default' ) );
	}
	if ( get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER ) > 0 ) {
		add_meta_box( 'al_product_attributes', sprintf( __( '%s Attributes', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), 'al_product_attributes', 'al_product', apply_filters( 'product_attributes_box_column', 'normal' ), apply_filters( 'product_attributes_box_priority', 'default' ) );
	}
	do_action( 'add_product_metaboxes', $names );
}

function al_product_price() {
	global $post;
	echo '<input type="hidden" name="pricemeta_noncename" id="pricemeta_noncename" value="' .
	wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	$price_table = '';
	if ( is_ic_price_enabled() ) {
		$set		 = get_currency_settings();
		$price		 = get_post_meta( $post->ID, '_price', true );
		$price_table = apply_filters( 'admin_price_table', '<table><tr><td class="label-column">' . __( 'Price', 'al-ecommerce-product-catalog' ) . ':</td><td class="price-column"><input type="text" title="' . sprintf( __( 'Example price format: %s or %s', 'al-ecommerce-product-catalog' ), price_format( '1587.89', 1, 0 ), '1587' . $set[ 'dec_sep' ] . '89' ) . '" pattern="^(([1-9](\\d*|\\d{0,2}(' . $set[ 'th_sep' ] . '\\d{3})*))|0)(\\' . $set[ 'dec_sep' ] . '\\d{1,2})?$" name="_price" value="' . $price . '" class="widefat" /></td><td>' . product_currency() . '</td></tr></table>', $post );
		$price_table .= '<div id="invalid-_price" class="ui-state-error ui-corner-all message" style="padding: 0 .7em; display: none;"><p>' . sprintf( __( 'Please provide a correct price format according to your currency settings. Example price format: %s or %s', 'al-ecommerce-product-catalog' ), price_format( '1587.89', 1, 0 ), '1587' . $set[ 'dec_sep' ] . '89' ) . '</p></div>';
	}
	if ( is_ic_sku_enabled() ) {
		$sku		 = get_post_meta( $post->ID, '_sku', true );
		$sku_table	 = apply_filters( 'admin_sku_table', '<table><tr><td class="label-column">' . __( 'SKU', 'al-ecommerce-product-catalog' ) . ':</td><td class="sku-column"><input type="text" name="_sku" value="' . $sku . '" class="widefat" /></td></tr></table>', $post );
		$price_table .= $sku_table;
	}
	echo $price_table;
}

function al_product_shipping() {
	global $post;
	echo '<input type="hidden" name="shippingmeta_noncename" id="shippingmeta_noncename" value="' .
	wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	$currency = product_currency();
	echo '<table class="sort-settings shipping"><tbody>';
	for ( $i = 1; $i <= get_option( 'product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER ); $i++ ) {
		$shipping_option		 = get_option( 'product_shipping_cost' );
		$shipping_label_option	 = get_option( 'product_shipping_label' );
		$shipping_option_field	 = get_post_meta( $post->ID, '_shipping' . $i, true );
		$shipping_label_field	 = get_post_meta( $post->ID, '_shipping-label' . $i, true );
		if ( $shipping_option_field != null ) {
			$shipping = $shipping_option_field;
		} else {
			$shipping = isset( $shipping_option[ $i ] ) ? $shipping_option[ $i ] : '';
		}
		if ( !empty( $shipping_label_field ) ) {
			$shipping_label = $shipping_label_field;
		} else {
			$shipping_label = isset( $shipping_label_option[ $i ] ) ? $shipping_label_option[ $i ] : '';
		}
		echo '<tr><td class="dragger"></td><td class="shipping-label-column"><input class="shipping-label" type="text" name="_shipping-label' . $i . '" value="' . $shipping_label . '" /></td><td><input class="shipping-value" type="number" min="0" name="_shipping' . $i . '" value="' . $shipping . '" />' . $currency . '</td></tr>';
	}
	echo '</tbody></table>';
}

function al_product_attributes() {
	global $post;
	echo '<input type="hidden" name="attributesmeta_noncename" id="attributesmeta_noncename" value="' .
	wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	echo '<div class="al-box info">' . __( 'Only attributes with values set will be shown on product page.', 'al-ecommerce-product-catalog' ) . ' ' . sprintf( __( 'See <a target="_blank" href="%s">docs</a>.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/docs/ecommerce-product-catalog/product-attributes/?cam=catalog-add-page-box&key=product-attributes' ) . '</div>';
	do_action( 'before_product_attributes_edit_single' );
	echo '<table class="sort-settings attributes">
	<thead><tr>
	<th class="title"><b>Name</b></th>
	<th></th>
	<th class="title"><b>Value</b></th>
	<th class="title"><b>Unit</b></th>
	<th class="dragger"></th>
	</tr>
	</thead>
	<tbody><tr style="height: 6px;"></tr>';
	do_action( 'inside_attributes_edit_table' );
	$attributes_option		 = get_option( 'product_attribute' );
	$attributes_label_option = get_option( 'product_attribute_label' );
	$attributes_unit_option	 = get_option( 'product_attribute_unit' );
	for ( $i = 1; $i <= get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER ); $i++ ) {
		$attributes_option_field		 = get_post_meta( $post->ID, '_attribute' . $i, true );
		$attributes_label_option_field	 = get_post_meta( $post->ID, '_attribute-label' . $i, true );
		$attributes_unit_option_field	 = get_post_meta( $post->ID, '_attribute-unit' . $i, true );
		$attributes_option[ $i ]		 = isset( $attributes_option[ $i ] ) ? $attributes_option[ $i ] : '';
		$attributes_label_option[ $i ]	 = isset( $attributes_label_option[ $i ] ) ? $attributes_label_option[ $i ] : '';
		$attributes_unit_option[ $i ]	 = isset( $attributes_unit_option[ $i ] ) ? $attributes_unit_option[ $i ] : '';
		if ( is_ic_new_product_screen() ) {
			$attributes_option_field		 = !empty( $attributes_option_field ) ? $attributes_option_field : $attributes_option[ $i ];
			$attributes_label_option_field	 = !empty( $attributes_label_option_field ) ? $attributes_label_option_field : $attributes_label_option[ $i ];
			$attributes_unit_option_field	 = !empty( $attributes_unit_option_field ) ? $attributes_unit_option_field : $attributes_unit_option[ $i ];
		}
		$attribute_value_field = '<input class="attribute-value" type="text" name="_attribute' . $i . '" value="' . $attributes_option_field . '" />';
		?>
		<tr>
			<td class="attributes-label-column"><input class="attribute-label" type="text"
													   name="_attribute-label<?php echo $i ?>"
													   value="<?php echo $attributes_label_option_field ?>"/></td>
			<td class="break-column">:</td>
			<td class="value-column"><?php echo apply_filters( 'product_attribute_value_edit', $attribute_value_field, $i, $attributes_option_field ) ?></td>
			<td class="unit-column"><input class="attribute-unit admin-number-field" type="text"
										   name="_attribute-unit<?php echo $i ?>"
										   value="<?php echo $attributes_unit_option_field ?>"/></td>
			<td class="dragger"></td>
		</tr>
	<?php } ?>
	</tbody>
	</table><?php
	do_action( 'product_attributes_edit_single', $post );
}

function al_product_short_desc() {
	global $post;
	echo '<input type="hidden" name="shortdescmeta_noncename" id="shortdescmeta_noncename" value="' .
	wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	$shortdesc			 = get_product_short_description( $post->ID );
	$short_desc_settings = array( 'media_buttons'	 => false, 'textarea_rows'	 => 5, 'tinymce'		 => array(
			'menubar'	 => false,
			'toolbar1'	 => 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,fullscreen',
			'toolbar2'	 => '',
			'toolbar3'	 => '',
			'toolbar4'	 => '',
		) );
	wp_editor( $shortdesc, 'excerpt', $short_desc_settings );
}

function al_product_desc() {
	global $post;
	echo '<input type="hidden" name="descmeta_noncename" id="descmeta_noncename" value="' .
	wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	$desc			 = get_product_description( $post->ID );
	$desc_settings	 = array( 'textarea_rows' => 30 );
	wp_editor( $desc, 'content', $desc_settings );
}

function implecode_save_products_meta( $post_id, $post ) {
	$post_type_now = substr( $post->post_type, 0, 10 );
	if ( $post_type_now == 'al_product' ) {
		$pricemeta_noncename = isset( $_POST[ 'pricemeta_noncename' ] ) ? $_POST[ 'pricemeta_noncename' ] : '';
		if ( !empty( $pricemeta_noncename ) && !wp_verify_nonce( $pricemeta_noncename, plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}
		if ( !isset( $_POST[ 'action' ] ) ) {
			return $post->ID;
		} else if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] != 'editpost' ) {
			return $post->ID;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post->ID;
		}
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $post->ID;
		}
		if ( !current_user_can( 'edit_post', $post->ID ) )
			return $post->ID;
		$product_meta[ '_price' ]	 = !empty( $_POST[ '_price' ] ) ? $_POST[ '_price' ] : '';
		$product_meta[ '_sku' ]		 = !empty( $_POST[ '_sku' ] ) ? $_POST[ '_sku' ] : '';
		$product_meta[ 'excerpt' ]	 = !empty( $_POST[ 'excerpt' ] ) ? $_POST[ 'excerpt' ] : '';
		$product_meta[ 'content' ]	 = !empty( $_POST[ 'content' ] ) ? $_POST[ 'content' ] : '';
		for ( $i = 1; $i <= get_option( 'product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER ); $i++ ) {
			$product_meta[ '_shipping' . $i ]		 = isset( $_POST[ '_shipping' . $i ] ) ? $_POST[ '_shipping' . $i ] : '';
			$product_meta[ '_shipping-label' . $i ]	 = !empty( $_POST[ '_shipping-label' . $i ] ) ? $_POST[ '_shipping-label' . $i ] : '';
		}
		for ( $i = 1; $i <= get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER ); $i++ ) {
			$product_meta[ '_attribute' . $i ]		 = !empty( $_POST[ '_attribute' . $i ] ) ? $_POST[ '_attribute' . $i ] : '';
			$product_meta[ '_attribute-label' . $i ] = !empty( $_POST[ '_attribute-label' . $i ] ) ? $_POST[ '_attribute-label' . $i ] : '';
			$product_meta[ '_attribute-unit' . $i ]	 = !empty( $_POST[ '_attribute-unit' . $i ] ) ? $_POST[ '_attribute-unit' . $i ] : '';
		}
		$product_meta = apply_filters( 'product_meta_save', $product_meta );
		foreach ( $product_meta as $key => $value ) {
			$current_value = get_post_meta( $post->ID, $key, true );
			if ( isset( $value ) && !isset( $current_value ) ) {
				add_post_meta( $post->ID, $key, $value, true );
			} else if ( isset( $value ) && $value != $current_value ) {
				update_post_meta( $post->ID, $key, $value );
			} else if ( !isset( $value ) && $current_value ) {
				delete_post_meta( $post->ID, $key );
			}
		}
		do_action( 'product_edit_save', $post );
	}
}

add_action( 'post_updated', 'implecode_save_products_meta', 1, 2 );

add_action( 'do_meta_boxes', 'change_image_box' );

function change_image_box() {
	$names = get_catalog_names();
	remove_meta_box( 'postimagediv', 'al_product', 'side' );
	add_meta_box( 'postimagediv', sprintf( __( '%s Image', 'al-ecommerce-product-catalog' ), ucfirst( $names[ 'singular' ] ) ), 'post_thumbnail_meta_box', 'al_product', apply_filters( 'product_image_box_column', 'side' ), apply_filters( 'product_image_box_priority', 'high' ) );
}

function change_thumbnail_html( $content ) {
	if ( is_ic_catalog_admin_page() ) {
		add_filter( 'admin_post_thumbnail_html', 'modify_add_product_image_label' );
	}
}

add_action( 'admin_head-post-new.php', 'change_thumbnail_html' );
add_action( 'admin_head-post.php', 'change_thumbnail_html' );

function modify_add_product_image_label( $label ) {
	if ( is_plural_form_active() ) {
		$names				 = get_catalog_names();
		$names[ 'singular' ] = strtolower( $names[ 'singular' ] );
		$label				 = str_replace( __( 'Set featured image' ), sprintf( __( 'Set %s image', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), $label );
		$label				 = str_replace( __( 'Remove featured image' ), sprintf( __( 'Remove %s image', 'al-ecommerce-product-catalog' ), $names[ 'singular' ] ), $label );
	} else {
		$label	 = str_replace( __( 'Set featured image' ), __( 'Set image', 'al-ecommerce-product-catalog' ), $label );
		$label	 = str_replace( __( 'Remove featured image' ), __( 'Remove image', 'al-ecommerce-product-catalog' ), $label );
	}
	return $label;
}

function set_product_messages( $messages ) {
	global $post, $post_ID;
	$quasi_post_type = get_quasi_post_type();
	$post_type		 = get_post_type( $post_ID );
	if ( $quasi_post_type == 'al_product' ) {
		$obj		 = get_post_type_object( $post_type );
		$singular	 = $obj->labels->singular_name;

		$messages[ $post_type ] = array(
			0	 => '',
			1	 => sprintf( __( '%s updated. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), $singular, esc_url( get_permalink( $post_ID ) ) ),
			2	 => __( 'Custom field updated.' ),
			3	 => __( 'Custom field deleted.' ),
			4	 => sprintf( __( '%s updated.', 'al-ecommerce-product-catalog' ), $singular ),
			5	 => isset( $_GET[ 'revision' ] ) ? sprintf( __( $singular . ' restored to revision from %s' ), $singular, wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
			6	 => sprintf( __( $singular . ' published. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), esc_url( get_permalink( $post_ID ) ), $singular ),
			7	 => __( 'Page saved.' ),
			8	 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>' ), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) ),
			9	 => sprintf( __( '%3$s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $singular ) . '</a>' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $singular ),
			10	 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>' ), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
	}
	return $messages;
}

add_filter( 'post_updated_messages', 'set_product_messages' );

/**
 * Returns product description
 *
 * @param int $product_id
 * @return string
 */
function get_product_description( $product_id ) {
	$product_desc = get_post_meta( $product_id, 'content', true );
	return apply_filters( 'get_product_description', $product_desc, $product_id );
}

/**
 * Returns product short description
 *
 * @param int $product_id
 * @return string
 */
function get_product_short_description( $product_id ) {
	$product_desc = get_post_meta( $product_id, 'excerpt', true );
	return apply_filters( 'get_product_short_description', $product_desc, $product_id );
}

require_once(AL_BASE_PATH . '/includes/product-categories.php');
require_once(AL_BASE_PATH . '/includes/search-widget.php');
// require_once('product-types.php');
?>