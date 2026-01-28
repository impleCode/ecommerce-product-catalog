<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */
$selected_mode = sanitize_text_field( $_GET['selected_mode'] );
ob_start();
do_action( 'ic_epc_mode_selected', $selected_mode );
$additional_content = ob_get_clean();
?>
    <style>


        .ic_cat-activation-wizard .bottom-container {
            display: none;
        }

        .ic_cat-activation-question input {
            font-size: inherit;
        }

        .ic_cat-activation-question span.ic_tip {
            position: relative;
            top: 5px;
        }

        .ic-mode-selected-container {
            background: #fff;
        }

        .ic-mode-selected-container h1 {
            text-align: center;
        }
    </style>
    <div class="about__section is-feature ic-mode-selected-container">
        <?php if ( empty( $_GET['ic_catalog_activation_choice'] ) ) { ?>
            <h1><?php printf( __( '%s mode is active now!', 'ecommmerce-product-catalog' ), ic_ucfirst( $selected_mode ) ) ?></h1>
        <?php } ?>
        <?php echo $additional_content ?>
        <div class="column">
            <p style="text-align: center;"><a
                        href="<?php echo esc_url( admin_url( 'edit.php?post_type=al_product&page=implecode_welcome' ) ) ?>"><?php _e( 'GO BACK TO MODE SELECTION', 'ecommerce-product-catalog' ) ?></a>
            </p>
        </div>
    </div>

    <hr/>

    <div class="about__section has-2-columns">
        <header class="is-section-header">
            <h2><?php _e( 'Display Catalog' ); ?></h2>
            <p><?php _e( 'You can select the main catalog page in the general settings screen. It will show categories and products according to the catalog settings.', 'ecommerce-product-catalog' ) ?></p>
            <p><?php _e( 'Apart from the main catalog page, you can display products and categories anywhere on the website.', 'ecommerce-product-catalog' ) ?></p>
        </header>
        <?php if ( function_exists( 'register_block_type' ) ) { ?>
            <div class="column">
                <h3><?php _e( 'Catalog Blocks', 'ecommerce-product-catalog' ) ?></h3>
                <p><?php _e( 'You can use three different blocks to display catalog parts.', 'ecommerce-product-catalog' ) ?></p>
                <p>
                    <a href="https://implecode.com/docs/ecommerce-product-catalog/all-product-catalog-blocks/#cam=welcome&key=blocks"><?php _e( 'Blocks usage', 'ecommerce-product-catalog' ) ?></a>
                </p>
            </div>
        <?php } ?>
        <div class="column">
            <h3><?php _e( 'Catalog Shortcodes', 'ecommerce-product-catalog' ) ?></h3>
            <p><?php _e( 'You can use many different shortcodes to displays catalog parts.', 'ecommerce-product-catalog' ) ?></p>
            <p>
                <a href="https://implecode.com/docs/ecommerce-product-catalog/product-catalog-shortcodes/#cam=welcome&key=shortcodes"><?php _e( 'Available Shortcodes', 'ecommerce-product-catalog' ) ?></a>
            </p>
        </div>
    </div>

    <hr/>

    <div class="about__section has-subtle-background-color has-2-columns">
        <header class="is-section-header">
            <h2><?php _e( 'For developers', 'ecommerce-product-catalog' ); ?></h2>
            <p><?php printf( __( '%s is designed to make it easy for developers to customize things.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></p>
        </header>
        <div class="column">
            <h3><?php _e( 'Theme integration', 'ecommerce-product-catalog' ); ?></h3>
            <p><?php _e( 'Even if the catalog works fine with any theme, you can take full control of the output.', 'ecommerce-product-catalog' ); ?></p>
            <p><a taget="_blank"
                  href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#theme_integration&cam=welcome&key=theme-integration-guide"><?php _e( 'Check the advanced theme integration method', 'ecommerce-product-catalog' ) ?></a>
            </p>
        </div>
        <div class="column">
            <h3><?php _e( 'Template Customization', 'ecommerce-product-catalog' ); ?></h3>
            <p><?php _e( "You can customize the output by placing the template file in your theme 'implecode' folder.", 'ecommerce-product-catalog' ) ?></p>
            <p><?php _e( 'All the templates are located in the plugin templates folder.', 'ecommerce-product-catalog' ) ?></p>
            <p><a target="_blank"
                  href="https://implecode.com/docs/ecommerce-product-catalog/product-page-template/#cam=welcome&key=product-page-template"><?php _e( 'Check the details about template modification', 'ecommerce-product-catalog' ); ?></a>
            </p>
        </div>
    </div>

    <div class="about__section has-subtle-background-color has-2-columns">
        <div class="column">
            <h3><?php _e( 'Shortcodes' ); ?></h3>
            <p><?php _e( 'You can use many shortcodes to display the entire catalog or even each smallest part.', 'ecommerce-product-catalog' ) ?></p>
            <p><a target="_blank"
                  href="https://implecode.com/docs/ecommerce-product-catalog/product-catalog-shortcodes/#cam=welcome&key=product-catalog-shortcodes"><?php _e( 'Check all the shortcodes', 'ecommerce-product-catalog' ) ?></a>
            </p>
        </div>
        <div class="column">
            <h3><?php _e( 'CSS & PHP code snippets', 'ecommerce-product-catalog' ); ?></h3>
            <p><?php _e( 'We keep the list of most useful code snippets to adjust things.', 'ecommerce-product-catalog' ) ?></p>
            <p><a target="_blank"
                  href="https://implecode.com/docs/ecommerce-product-catalog/css-adjustments/#cam=welcome&key=css"><?php _e( 'CSS code snippets', 'ecommerce-product-catalog' ) ?></a>
                | <a target="_blank"
                     href="https://implecode.com/docs/ecommerce-product-catalog/php-adjustments/#cam=welcome&key=php"><?php _e( 'PHP code snippets', 'ecommerce-product-catalog' ) ?></a>
            </p>
        </div>
    </div>

    <div class="about__section has-2-columns has-subtle-background-color is-wider-right">
        <div class="column">
            <h3><?php _e( 'Catalog Custom Coding', 'ecommerce-product-catalog' ); ?></h3>
            <p><?php _e( 'If you need a custom feature, do not hesitate to contact the developers.', 'ecommerce-product-catalog' ) ?></p>
            <p><?php _e( 'We know the plugin and WordPress to the ground, can adjust small things and create very complex features or integrations.', 'ecommerce-product-catalog' ) ?></p>
            <p><?php _e( 'We provide custom coding services in a professional and timely manner.', 'ecommerce-product-catalog' ) ?></p>
            <p><a href="https://implecode.com/support/?support_type=custom_job#cam=welcome&key=support"
                  class="button-primary"
                  target="_blank"><?php _e( 'Contact the developers', 'ecommerce-product-catalog' ) ?></a></p>
        </div>
        <div class="column about__image is-vertically-aligned-center">
            <figure aria-labelledby="about-block-pattern" class="about__image">
                <img src="<?php echo AL_PLUGIN_BASE_PATH . 'img/example-customization-feedback.png' ?>">
            </figure>
        </div>
    </div>

    <hr class="is-small"/>

    <div class="about__section">
        <div class="column">
            <h3><?php _e( 'Check the documentation for more!', 'ecommerce-product-catalog' ); ?></h3>
            <p>
                <?php
                printf( __( 'There’s a lot more for developers to love in %1$s. To discover more and learn how to make the catalog shine on your sites, themes, plugins and more, check the %2$sdocumentation.%3$s', 'ecommerce-product-catalog' ),
                        IC_CATALOG_PLUGIN_NAME . ' ' . IC_CATALOG_VERSION, '<a href="https://implecode.com/docs/#cam=welcome&key=docs">', '</a>' );
                ?>
            </p>
        </div>
    </div>
<?php
