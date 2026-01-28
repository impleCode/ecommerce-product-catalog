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
global $wp_version;
?>
    <div class="wrap about__container">
        <div class="ic-welcome-trap" style="display: none">
            <h2></h2>
        </div>
        <style>
            .ic-select-mode-container,
            .branch-6 .ic-select-mode-container,
            .branch-6-1 .ic-select-mode-container,
            .branch-6-2 .ic-select-mode-container,
            .branch-6-4 .ic-select-mode-container {
                margin: 0 0 var(--gap);
            }

            .ic-select-mode-container .is-feature,
            .branch-6 .ic-select-mode-container .is-feature,
            .branch-6-1 .ic-select-mode-container .is-feature,
            .branch-6-2 .ic-select-mode-container .is-feature,
            .branch-6-4 .ic-select-mode-container .is-feature {
                text-align: center;
                margin-bottom: 0;
            }

            .ic-select-mode-container .column,
            .branch-6 .ic-select-mode-container .column,
            .branch-6-1 .ic-select-mode-container .column,
            .branch-6-2 .ic-select-mode-container .column,
            .branch-6-4 .ic-select-mode-container .column {
                border: 1px solid #0a58ca;
                margin: 10px;
                position: relative;
            }

            .ic-select-mode-container .has-2-columns,
            .branch-6 .ic-select-mode-container .has-2-columns,
            .branch-6-1 .ic-select-mode-container .has-2-columns,
            .branch-6-2 .ic-select-mode-container .has-2-columns,
            .branch-6-4 .ic-select-mode-container .has-2-columns {
                margin: 0;
            }

            .ic-select-mode-container .button-primary,
            .branch-6 .ic-select-mode-container .button-primary,
            .branch-6-1 .ic-select-mode-container .button-primary,
            .branch-6-2 .ic-select-mode-container .button-primary,
            .branch-6-4 .ic-select-mode-container .button-primary {
                width: 85%;
                position: absolute;
                bottom: var(--gap);
                text-align: center;
                font-size: 1.2em;
            }

            .about__section .column p.ic-select-mode-button-container:last-of-type,
            .branch-6 .about__section .column p.ic-select-mode-button-container:last-of-type,
            .branch-6-1 .about__section .column p.ic-select-mode-button-container:last-of-type,
            .branch-6-2 .about__section .column p.ic-select-mode-button-container:last-of-type,
            .branch-6-4 .about__section .column p.ic-select-mode-button-container:last-of-type {
                margin-bottom: 4em;
            }

            .ic-select-mode-container hr,
            .branch-6 .ic-select-mode-container hr,
            .branch-6-1 .ic-select-mode-container hr,
            .branch-6-2 .ic-select-mode-container hr,
            .branch-6-4 .ic-select-mode-container hr {
                display: none;
            }

            .al_product_page_implecode_welcome .about__container,
            .branch-6.al_product_page_implecode_welcome .about__container,
            .branch-6-1.al_product_page_implecode_welcome .about__container,
            .branch-6-2.al_product_page_implecode_welcome .about__container,
            .branch-6-4.al_product_page_implecode_welcome .about__container {
                margin-top: 0;
            }

            .al_product_page_implecode_welcome .about__header,
            .branch-6.al_product_page_implecode_welcome .about__header,
            .branch-6-1.al_product_page_implecode_welcome .about__header,
            .branch-6-2.al_product_page_implecode_welcome .about__header,
            .branch-6-4.al_product_page_implecode_welcome .about__header {
                background-image: url(https://ps.w.org/ecommerce-product-catalog/assets/banner-772x250.jpg?rev=1662948);
                background-size: 100%;
                background-position: 0 23%;
                background-repeat: no-repeat;
            }

            .al_product_page_implecode_welcome .about__header-title span,
            .branch-6.al_product_page_implecode_welcome .about__header-text span, .branch-6.al_product_page_implecode_welcome .about__header-title span,
            .branch-6-1.al_product_page_implecode_welcome .about__header-text span, .branch-6-1.al_product_page_implecode_welcome .about__header-title span,
            .branch-6-2.al_product_page_implecode_welcome .about__header-text span, .branch-6-2.al_product_page_implecode_welcome .about__header-title span,
            .branch-6-4.al_product_page_implecode_welcome .about__header-text span, .branch-6-4.al_product_page_implecode_welcome .about__header-title span {
                display: none;
            }

            .al_product_page_implecode_welcome .about__container .has-subtle-background-color,
            .branch-6-1.al_product_page_implecode_welcome .about__container .has-subtle-background-color,
            .branch-6-2.al_product_page_implecode_welcome .about__container .has-subtle-background-color,
            .branch-6-4.al_product_page_implecode_welcome .about__container .has-subtle-background-color {
                background: transparent;
            }

            .al_product_page_implecode_welcome .about__header,
            .branch-6-1.al_product_page_implecode_welcome .about__header,
            .branch-6-2.al_product_page_implecode_welcome .about__header,
            .branch-6-4.al_product_page_implecode_welcome .about__header {
                background-color: transparent;
            }

            .al_product_page_implecode_welcome .about__header-text,
            .branch-6-2.al_product_page_implecode_welcome .about__header-text,
            .branch-6-4.al_product_page_implecode_welcome .about__header-text {
                margin: 0 auto 10rem;
            }

            .al_product_page_implecode_welcome .about__header-title,
            .branch-6-2.al_product_page_implecode_welcome .about__header-title,
            .branch-6-4.al_product_page_implecode_welcome .about__header-title {
                padding: 9rem 0 0;
            }

            .al_product_page_implecode_welcome .about__header,
            .branch-6-2.al_product_page_implecode_welcome .about__header,
            .branch-6-4.al_product_page_implecode_welcome .about__header {
                padding: 0 0 20px 0;
            }

            .al_product_page_implecode_welcome .about__header-navigation,
            .branch-6-2.al_product_page_implecode_welcome .about__header-navigation,
            .branch-6-4.al_product_page_implecode_welcome .about__header-navigation {
                width: 100%;
            }

            .post-type-al_product.al_product_page_implecode_welcome #wpbody-content .nav-tab-wrapper {
                margin: 0;
            }
        </style>
        <div class="about__header">
            <?php if ( version_compare( $wp_version, 5.4 ) !== - 1 ) { ?>
            <div class="ic-welcome-bg">
                <?php } ?>
                <div class="about__header-text">
                    <span>That's it. Enjoy sales and beauty!</span>&nbsp
                </div>

                <div class="about__header-title">
                    <p><span>
						<?php echo IC_CATALOG_PLUGIN_NAME ?>
                        <span><?php echo IC_CATALOG_VERSION ?></span></span>&nbsp
                    </p>
                </div>
                <?php if ( version_compare( $wp_version, 5.4 ) !== - 1 ) { ?>
            </div>
        <?php } ?>

            <nav class="about__header-navigation nav-tab-wrapper wp-clearfix"
                 aria-label="<?php esc_attr_e( 'Secondary menu' ); ?>">
                <?php
                $getting_started = '';
                $whats_new       = '';
                if ( empty( $_GET['tab'] ) ) {
                    $getting_started = ' nav-tab-active';
                } elseif ( $_GET['tab'] === 'new' ) {
                    $whats_new = ' nav-tab-active';
                }
                ?>
                <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=implecode_welcome' ) ?>"
                   class="nav-tab<?php echo $getting_started ?>"
                   aria-current="page"><?php _e( 'Getting Started', 'ecommerce-product-catalog' ) ?></a>
                <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=implecode_welcome&tab=new' ) ?>"
                   class="nav-tab<?php echo $whats_new ?>"><?php _e( 'What&#8217;s New', 'ecommerce-product-catalog' ) ?></a>
                <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) ?>"
                   class="nav-tab"><?php _e( 'Settings', 'ecommerce-product-catalog' ); ?></a>
                <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php' ) ?>"
                   class="nav-tab"><?php _e( 'Add-ons & Integrations', 'ecommerce-product-catalog' ); ?></a>
                <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) ?>"
                   class="nav-tab"><?php _e( 'Help', 'ecommerce-product-catalog' ); ?></a>
            </nav>
        </div>

        <?php
        if ( ! empty( $getting_started ) ) {
            if ( ! empty( $_GET['selected_mode'] ) ) {
                require_once( AL_BASE_PATH . '/includes/welcome/mode-selected.php' );
            } else {
                require_once( AL_BASE_PATH . '/includes/welcome/start.php' );
            }
        } elseif ( ! empty( $whats_new ) ) {
            require_once( AL_BASE_PATH . '/includes/welcome/new.php' );
        }
        ?>
        <hr/>

        <div class="return-to-dashboard">
            <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) ?>"><?php _e( 'Go to settings', 'ecommerce-product-catalog' ) ?></a>
        </div>
    </div>
<?php
