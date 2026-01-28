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
?>

    <div class="about__section is-feature has-subtle-background-color">
        <h2>
            <?php
            printf(
            /* translators: %s: The current EPC version number. */
                    __( 'Welcome to %s.', 'ecommerce-product-catalog' ),
                    IC_CATALOG_PLUGIN_NAME . ' ' . IC_CATALOG_VERSION
            );
            ?>
        </h2>
        <p>
            <?php
            _e( 'In this release cycle, your catalog gets more power in CTA, theme integration, speed and usability.', 'ecommerce-product-catalog' );
            ?>
        </p>
    </div>

    <hr/>

    <div class="about__section has-1-column">
        <div class="column">
            <h2><?php _e( 'CTA Options', 'ecommerce-product-catalog' ); ?></h2>
            <p>
                <strong><?php _e( "CTA stands for call to action, and it's the part of a webpage that encourages the audience to do something.", 'ecommerce-product-catalog' ) ?></strong>
            </p>
            <p><?php _e( 'The aim of the 3.x releases is to make a variety of CTAs available so your catalog can convert your users to customers.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'Until now there are 3 CTAs available: Shopping Cart add to cart, Quote Cart add to cart and an affiliate button.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php printf( __( 'If you have an idea of a great CTA that could be included, please %1$scontact the developers%2$s!', 'ecommerce-product-catalog' ), '<a target="_blank" href="https://implecode.com/support/?support_type=different&cam=welcome&key=idea">', '</a>' ) ?></p>
        </div>
    </div>

    <div class="about__section has-1-column">
        <div class="column">
            <h2><?php _e( 'Theme integration', 'ecommerce-product-catalog' ); ?></h2>
            <p>
                <strong><?php _e( 'The theme integration is continuously being improved to support more themes and page builders.', 'ecommerce-product-catalog' ) ?></strong>
            </p>
            <p><?php _e( 'The aim of the 3.x releases is to make it compatible with 99% of the themes and page builders.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'In the current release, the theme integration goal is almost 100% complete.', 'ecommerce-product-catalog' ) ?></p>
            <p><?php printf( __( 'If you still face any theme or page builder integration issues, please report them on the %1$ssupport forum%2$s.', 'ecommerce-product-catalog' ), '<a href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">', '</a>' ) ?></p>
            <p><?php _e( 'Thank you for all your feedback regarding theme integration issues!', 'ecommerce-product-catalog' ); ?></p>
        </div>
    </div>

    <div class="about__section has-1-column">
        <div class="column">
            <h2><?php _e( 'Speed', 'ecommerce-product-catalog' ); ?></h2>
            <p>
                <strong><?php _e( 'Say hello to the fastest catalog experience.', 'ecommerce-product-catalog' ); ?></strong>
            </p>
            <p><?php printf( __( '%s is tested on websites with more than 40,000 products with many parameters, full-featured shopping cart and automatic product updates.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></p>
            <p><?php _e( 'Thanks to many optimization tasks, your catalog pages and search results will load in less than a second.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'So your catalog can rank higher in the search engines and give the user the fastest experience possible.', 'ecommerce-product-catalog' ); ?></p>
        </div>
    </div>

    <div class="about__section has-1-column">
        <div class="column">
            <h2><?php _e( 'Usability', 'ecommerce-product-catalog' ); ?></h2>
            <p>
                <strong><?php printf( __( 'In this release, %s is continuously improved in the field of front-end and back-end usability', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></strong>
            </p>
            <p><?php _e( 'Small tweaks have been added in catalog design for the users.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'Most changes were added in the admin side to make the configuration easier for new users.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php printf( __( 'If you face any configuration issues, please report it on the %1$ssupport forum%2$s.', 'ecommerce-product-catalog' ), '<a href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">', '</a>' ) ?></p>
            <p><?php _e( 'Thanks to your feedback, we can make things easier together!', 'ecommerce-product-catalog' ); ?></p>
        </div>
    </div>

    <hr/>

    <div class="about__section has-2-columns has-accent-background-color is-wider-right">
        <div class="column">
            <h2><?php _e( 'Security, errors, feedback', 'ecommerce-product-catalog' ); ?></h2>
            <p>
                <strong><?php _e( 'Frequent updates guarantee high security. The plugin is continuously being monitored for any security issues.', 'ecommerce-product-catalog' ); ?></strong>
            </p>
            <p><?php printf( __( 'You can report any bugs or feedback on the %1$ssupport forum%3$s or on the %2$splugin website%3$s.', 'ecommerce-product-catalog' ), '<a href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">', '<a href="https://implecode.com/support/?support_type=bug_report&cam=welcome&key=bug">', '</a>' ) ?></p>
            <p><?php printf( __( 'If you find any security issue, please report it %1$shere%2$s.', 'ecommerce-product-catalog' ), '<a href="https://implecode.com/support/?support_type=bug_report&cam=welcome&key=security">', '</a>' ) ?></p>
            <p>
                <strong><?php printf( __( 'Thank you for all the effort that you put in testing and reporting. Without your involvement it would never be possible to make %s so reliable and secure!', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></strong>
            </p>
        </div>
        <div class="column about__image is-vertically-aligned-center">
            <figure aria-labelledby="about-security" class="about__image">
                <img src="<?php echo AL_PLUGIN_BASE_PATH . 'img/implecode.png' ?>">
            </figure>
        </div>
    </div>

    <hr/>

    <div class="about__section has-2-columns has-subtle-background-color">
        <div class="column about__image is-vertically-aligned-center">
            <figure aria-labelledby="about-block-pattern" class="about__image">
                <img src="<?php echo AL_PLUGIN_BASE_PATH . 'img/example-feedback.png' ?>">
            </figure>
        </div>
        <div class="column">
            <h2><?php printf( __( '%s reviews on WordPress.org', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></h2>
            <p><?php _e( 'Your reviews on WordPress.org help us to spread the word about this awesome catalog plugin.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'This is very important for the developers. We stay motivated and passionate in the sphere of continuous catalog development.', 'ecommerce-product-catalog' ); ?></p>
            <p><?php _e( 'Constructive feedback helps to take the right direction in the development.', 'ecommerce-product-catalog' ); ?></p>
            <p><a href="https://wordpress.org/support/plugin/ecommerce-product-catalog/reviews/#new-post"
                  class="button-primary"
                  target="_blank"><?php _e( 'Add your review', 'ecommerce-product-catalog' ) ?></a></p>
        </div>
    </div>

    <hr/>

    <div class="about__section has-1-column">
        <div class="column">
            <h2><?php _e( 'Documentation & Help', 'ecommerce-product-catalog' ); ?></h2>
            <p><?php _e( 'Now you can search through the catalog settings and docs from your admin dashboard!', 'ecommerce-product-catalog' ); ?></p>
            <p><?php printf( __( 'You can find the search box in the %1$scatalog settings%3$s or %2$shelp tab%3$s.', 'ecommerce-product-catalog' ), '<a target="_blank" href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) . '">', '<a target="_blank" href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) . '">', '</a>' ) ?></p>
        </div>
    </div>

    <hr/>

    <div class="about__section has-subtle-background-color has-2-columns">
        <header class="is-section-header">
            <h2><?php _e( 'For developers' ); ?></h2>
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
