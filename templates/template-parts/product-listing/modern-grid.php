<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The template to display product listing as modern grid
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/modern-grid.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
$product              = ic_get_product_object();
$modern_grid_settings = get_modern_grid_settings();
?>


    <div class="al_archive product-<?php echo $product->ID ?> modern-grid-element <?php echo design_schemes( 'box', 0 ) ?> <?php echo product_class( $product->ID ) ?>">
        <?php do_action( 'modern_grid_product_start', $product->ID, $modern_grid_settings ) ?>
        <div class="pseudo"></div>
        <a href="<?php echo $product->url() ?>"><?php echo $product->listing_image_html() ?>
            <h3 class="product-name <?php echo design_schemes( 'box', 0 ) ?>"><?php echo wp_strip_all_tags( $product->name() ) ?></h3>
            <?php
            do_action( 'ic_product_listing_element_inside', $product->ID, $modern_grid_settings );
            do_action( 'modern_grid_entry_inside', $product->ID, $modern_grid_settings );
            echo $product->archive_price_html();
            ?>
        </a>
        <?php do_action( 'modern_grid_product_end', $product->ID, $modern_grid_settings ) ?>
    </div>

<?php
