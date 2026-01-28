<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages catalog classic list theme
 *
 * Here classic list theme is defined and managed.
 *
 * @version        1.2.0
 * @package        ecommerce-product-catalog/templates/themes
 * @author        impleCode
 */

/**
 * Shows example classic list in admin
 *
 */
function example_list_archive_theme() {
	?>
    <div class="archive-listing list example">
        <a href="#list-theme">
            <span class="div-link"></span>
        </a>
        <div class="product-image"
             style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg'; ?>'); background-size: 150px; background-position: center;"></div>
        <div class="product-name">White Lamp</div>
        <div class="product-short-descr"><p>Fusce vestibulum augue ac quam tincidunt ullamcorper. Vestibulum scelerisque
                fermentum congue. Proin convallis dolor ac ipsum congue tincidunt. [...]</p>
        </div>
    </div>
	<?php
}

/*
  function list_archive_theme( $post ) {
  ?>
  <div class="archive-listing list example">
  <a href="<?php the_permalink(); ?>"><span class="div-link"></span></a>
  <div class="product-image" style="background-image:url('<?php
  if ( wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ) ) {
  $url = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
  } else {
  $url = default_product_thumbnail_url();
  }
  echo $url;
  ?>'); background-size: 150px; background-position: center; background-repeat: no-repeat;"></div>
  <div class="product-name"><?php the_title(); ?></div>
  <div class="product-short-descr"><p><?php echo c_list_desc( $post->ID ); ?></p></div>
  </div>
  <?php
  }
 */

function get_list_archive_theme( $post, $archive_template = null ) {
	$archive_template = isset( $archive_template ) ? $archive_template : get_product_listing_template();
	$return           = '';
	if ( $archive_template == 'list' ) {
		remove_all_filters( 'ic_listing_image_html' );
		add_filter( 'ic_listing_image_html', 'ic_set_classic_list_image_html', 10, 3 );
		ob_start();
		ic_show_template_file( 'product-listing/classic-list.php' );
		$return .= ob_get_clean();
	}

	return $return;
}

function get_list_category_theme( $product_cat, $archive_template ) {
	if ( $archive_template == 'list' ) {
		$product_cat = ic_set_classic_list_category_image_html( $product_cat );
		ic_save_global( 'ic_current_product_cat', $product_cat );
		ob_start();
		ic_show_template_file( 'product-listing/classic-list-category.php' );
		$return = ob_get_clean();

		return $return;
	}
}

function ic_set_classic_list_image_html( $image_html, $product_id, $product ) {
	$image_id          = $product->image_id();
	$thumbnail_product = wp_get_attachment_image_src( $image_id, 'classic-list-listing' );
	$product_name      = get_product_name();
	if ( $thumbnail_product ) {
		$img_class['alt']   = $product_name;
		$img_class['class'] = 'classic-list-image';
		$image_html         = wp_get_attachment_image( $image_id, 'classic-list-listing', false, $img_class );
	} else {
		$url        = default_product_thumbnail_url();
		$image_html = '<img src="' . $url . '" class="classic-list-image" alt="' . $product_name . '" >';
	}

	return $image_html;
}

function ic_set_classic_list_category_image_html( $product_cat ) {
	$image_id = get_product_category_image_id( $product_cat->term_id );
	if ( $url = wp_get_attachment_url( $image_id, 'classic-list-listing' ) ) {
		$img_class['alt']   = $product_cat->name;
		$img_class['class'] = 'classic-list-image';
		$image              = wp_get_attachment_image( $image_id, 'classic-list-listing', false, $img_class );
	} else {
		$url   = default_product_thumbnail_url();
		$image = '<img src="' . $url . '" class="classic-list-image" alt="' . $product_cat->name . '" >';
	}
	//$product_cat->listing_image_html = $image;
	ic_save_global( 'ic_category_listing_image_html_' . $product_cat->term_id, $image );

	return $product_cat;
}

function get_classic_list_settings() {
	$settings = wp_parse_args( get_option( 'classic_list_settings' ), array(
		'attributes'     => 0,
		'attributes_num' => 3
	) );

	return $settings;
}

add_filter( 'ic_listing_template_file_paths', 'ic_add_classic_list_path' );

function ic_add_classic_list_path( $paths ) {
	$paths['list'] = array( 'file' => 'product-listing/classic-list.php', 'base' => '' );

	return $paths;
}
