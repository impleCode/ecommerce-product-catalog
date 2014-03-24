<?php
/**
 * Manages wordpress core fields
 *
 * Here all wordpress fields are redefined.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'enter_title_here', 'al_enter_title_here', 8 );
function al_enter_title_here( $message ){
  $screen         = get_current_screen();
  $post_type_slug = $screen->post_type;
  $post_type_ob   = get_post_type_object( $post_type_slug );

  if( $extras = $post_type_ob->extras ):
    $message = $extras['enter_title_here'];
  endif;

  return $message;
}


?>