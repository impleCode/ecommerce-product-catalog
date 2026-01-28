<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display sitewide icon bar
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/icon-bar.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/sitewide-icons
 * @author 		impleCode
 */
?>

<div id="ic-catalog-bar" class="<?php echo ic_sitewide_bar::container_class() ?>">
	<?php
	do_action( 'ic_catalog_bar_content' );
	?>
</div>

<?php
