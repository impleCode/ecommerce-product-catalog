<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The template to display product size on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-size.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
$product_id = ic_get_product_id();
$size       = ic_get_product_size( $product_id );
if ( is_ic_attributes_size_enabled() && ! empty( $size ) && is_string( $size ) ) {
    ?>

    <table class="size-table">
        <tr>
            <td><?php echo ic_attributes_get_size_label() ?>:</td>
            <td class="size-value"><?php echo $size ?></td>
        </tr>
    </table>

    <?php
}