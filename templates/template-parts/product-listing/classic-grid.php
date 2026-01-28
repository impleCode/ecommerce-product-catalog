<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The template to display product listing as classic grid
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/classic-grid.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
$product               = ic_get_product_object();
$classic_grid_settings = get_classic_grid_settings();
?>
    <div class="archive-listing product-<?php echo $product->ID ?> classic-grid <?php echo product_class( $product->ID ) ?>">
        <a href="<?php echo $product->url() ?>">
            <div class="classic-grid-image-wrapper">
                <div class="pseudo"></div>
                <div class="image"><?php echo $product->listing_image_html() ?></div>
            </div>
            <h3 class="product-name"><?php echo $product->name() ?></h3><?php echo $product->archive_price_html() ?>
            <?php
            do_action( 'ic_product_listing_element_inside', $product->ID, $classic_grid_settings );
            do_action( 'classic_grid_product_listing_element_inside', $product->ID, $classic_grid_settings );
            ?>
        </a>
        <?php
        do_action( 'classic_grid_product_listing_element', $product->ID, $classic_grid_settings );
        ?>
    </div>

<?php
