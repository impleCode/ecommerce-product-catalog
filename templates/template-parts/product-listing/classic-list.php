<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The template to display product listing as classic list
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/classic-list.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
$product = ic_get_product_object();
?>

    <div class="archive-listing product-<?php echo $product->ID ?> list <?php echo product_class( $product->ID ) ?>">
        <a href="<?php echo $product->url() ?>"><span class="div-link"></span></a>
        <div class="classic-list-image-wrapper">
            <div class="pseudo"></div><?php echo $product->listing_image_html() ?></div>
        <div class="product-name"><?php echo $product->name() ?></div>
        <div class="product-short-descr"><p><?php echo c_list_desc( $product->ID ) ?></p></div>
        <?php
        $classic_list_settings = get_classic_list_settings();
        do_action( 'ic_product_listing_element_inside', $product->ID, add_product_listing_name(), $classic_list_settings );
        do_action( 'classic_list_entry_bottom', $product->ID, $classic_list_settings );
        ?>
    </div>

<?php
