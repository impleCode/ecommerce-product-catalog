<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * System status screen
 *
 * Created: Jul 9, 2015
 * Package: system
 */

function register_product_system() {
	add_submenu_page( 'edit.php?post_type=al_product', __( 'System Status', 'ecommerce-product-catalog' ), __( 'System Status', 'ecommerce-product-catalog' ), apply_filters( 'ic_system_status_cap', 'manage_product_settings' ), basename( __FILE__ ), 'ic_system_status' );
}

add_action( 'product_settings_menu', 'register_product_system' );

function ic_system_status() {
	if ( current_user_can( 'manage_product_settings' ) ) {
		if ( isset( $_GET[ 'reset_product_settings' ] ) ) {
			if ( isset( $_GET[ 'reset_product_settings_confirm' ] ) ) {
				foreach ( all_ic_options( 'options' ) as $option ) {
					delete_option( $option );
				}
				implecode_success( __( 'Catalog Settings successfully reset to default!', 'ecommerce-product-catalog' ) );
			} else {
				echo '<h3>' . __( 'All catalog settings will be reset to defaults. Would you like to proceed?', 'ecommerce-product-catalog' ) . '</h3>';
				echo '<a class="button" href="' . esc_url( add_query_arg( 'reset_product_settings_confirm', 1 ) ) . '">' . __( 'Yes', 'ecommerce-product-catalog' ) . '</a> <a class="button" href="' . esc_url( remove_query_arg( 'reset_product_settings' ) ) . '">' . __( 'No', 'ecommerce-product-catalog' ) . '</a>';
			}
		} else if ( isset( $_GET[ 'delete_all_products' ] ) ) {
			if ( isset( $_GET[ 'delete_all_products_confirm' ] ) ) {
				global $wpdb;
				$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'al_product' );" );
				$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
				if ( function_exists( 'ic_delete_all_attribute_terms' ) ) {
					ic_delete_all_attribute_terms();
				}
				implecode_success( __( 'All Catalog Products successfully deleted!', 'ecommerce-product-catalog' ) );
			} else {
				echo '<h3>' . __( 'All items will be permanently deleted. Would you like to proceed?', 'ecommerce-product-catalog' ) . '</h3>';
				echo '<a class="button" href="' . esc_url( add_query_arg( 'delete_all_products_confirm', 1 ) ) . '">' . __( 'Yes', 'ecommerce-product-catalog' ) . '</a> <a class="button" href="' . esc_url( remove_query_arg( 'delete_all_products' ) ) . '">' . __( 'No', 'ecommerce-product-catalog' ) . '</a>';
			}
		} else if ( isset( $_GET[ 'delete_all_product_categories' ] ) ) {
			if ( isset( $_GET[ 'delete_all_product_categories_confirm' ] ) ) {
				global $wpdb;
				$taxonomy	 = 'al_product-cat';
				$terms		 = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

				// Delete Terms
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
						$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
						$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
						delete_option( 'al_product_cat_image_' . $term->term_id );
					}
				}

				// Delete Taxonomy
				$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
				implecode_success( __( 'All Catalog Categories successfully deleted!', 'ecommerce-product-catalog' ) );
			} else {
				echo '<h3>' . __( 'All catalog categories will be permanently deleted. Would you like to proceed?', 'ecommerce-product-catalog' ) . '</h3>';
				echo '<a class="button" href="' . esc_url( add_query_arg( 'delete_all_product_categories_confirm', 1 ) ) . '">' . __( 'Yes', 'ecommerce-product-catalog' ) . '</a> <a class="button" href="' . esc_url( remove_query_arg( 'delete_all_product_categories' ) ) . '">' . __( 'No', 'ecommerce-product-catalog' ) . '</a>';
			}
		} else if ( isset( $_GET[ 'delete_old_filters_bar' ] ) ) {
			if ( isset( $_GET[ 'delete_old_filters_bar_confirm' ] ) ) {
				delete_option( 'old_sort_bar' );
				implecode_success( __( 'Filters bar is now empty by default!', 'ecommerce-product-catalog' ) );
			} else {
				echo '<h3>' . __( 'Default filters bar will become empty.', 'ecommerce-product-catalog' ) . '</h3>';
				echo '<a class="button" href="' . esc_url( add_query_arg( 'delete_old_filters_bar_confirm', 1 ) ) . '">' . __( 'OK', 'ecommerce-product-catalog' ) . '</a> <a class="button" href="' . esc_url( remove_query_arg( 'delete_old_filters_bar' ) ) . '">' . __( 'Cancel', 'ecommerce-product-catalog' ) . '</a>';
			}
		} else {
			?>
			<style>table.widefat {width: 95%}table tbody tr td:first-child {width: 350px } table tbody tr:nth-child(even) {background: #fafafa;}</style>
			<p></p>
			<table class="widefat" cellspacing="0" id="ic_tools">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'impleCode Tools', 'ecommerce-product-catalog' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e( 'Reset product settings', 'ecommerce-product-catalog' ); ?>:</td>
						<td><a class="button" href="<?php echo esc_url( add_query_arg( 'reset_product_settings', 1 ) ) ?>"><?php _e( 'Reset Catalog Settings', 'ecommerce-product-catalog' ) ?></a></td>
					</tr>
					<tr>
						<td><?php _e( 'Delete all items', 'ecommerce-product-catalog' ); ?>:</td>
						<td><a class="button" href="<?php echo esc_url( add_query_arg( 'delete_all_products', 1 ) ) ?>"><?php _e( 'Delete all Catalog Items', 'ecommerce-product-catalog' ) ?></a></td>
					</tr>
					<tr>
						<td><?php _e( 'Delete all categories', 'ecommerce-product-catalog' ); ?>:</td>
						<td><a class="button" href="<?php echo esc_url( add_query_arg( 'delete_all_product_categories', 1 ) ) ?>"><?php _e( 'Delete all Catalog Categories', 'ecommerce-product-catalog' ) ?></a></td>
					</tr>
					<?php
					if ( get_option( 'old_sort_bar' ) == 1 ) {
						?>
						<tr>
							<td><?php _e( 'Make default filters bar empty.', 'ecommerce-product-catalog' ); ?>:</td>
							<td><a class="button" href="<?php echo esc_url( add_query_arg( 'delete_old_filters_bar', 1 ) ) ?>"><?php _e( 'Empty Default Filters Bar', 'ecommerce-product-catalog' ) ?></a></td>
						</tr>
					<?php } ?>
					<tr>
						<td><?php _e( 'Delete all items and categories on uninstall', 'ecommerce-product-catalog' ); ?>:</td>
						<?php $checked		 = get_option( 'ic_delete_products_uninstall', 0 ); ?>
						<td><input type="checkbox" name="delete_products_uninstall" <?php checked( 1, $checked ) ?> /></td>
					</tr>
					<?php do_action( 'ic_system_tools' ); ?>
				</tbody>
			</table>
			<p></p>
			<table class="widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'WordPress Environment', 'ecommerce-product-catalog' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e( 'Home URL', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo home_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Site URL', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo site_url(); ?></td>
					</tr>
					<tr>
						<td><?php
							echo sprintf( __( '%s Version', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME );
							?>:
						</td>
						<td>
							<?php
							$plugin_data	 = get_plugin_data( AL_PLUGIN_MAIN_FILE );
							$plugin_version	 = $plugin_data[ "Version" ];
							echo $plugin_version;
							?>
						</td>
					</tr>
					<tr>
						<td><?php echo sprintf( __( '%s Database Version', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?>:</td>
						<td><?php echo get_option( 'ecommerce_product_catalog_ver', $plugin_version ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Version', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php bloginfo( 'version' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Multisite', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php
							if ( is_multisite() )
								echo '&#10004;';
							else
								echo '&ndash;';
							?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Memory Limit', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php
							$memory			 = WP_MEMORY_LIMIT;
							echo size_format( $memory );
							?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Debug Mode', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
								echo '&#10004;';
							else
								echo '&ndash;';
							?></td>
					</tr>
					<tr>
						<td><?php _e( 'Language', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo get_locale() ?></td>
					</tr>
				</tbody>
			</table>
			<p></p>
			<table class="widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'Server Environment', 'ecommerce-product-catalog' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e( 'Server Info', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo esc_html( $_SERVER[ 'SERVER_SOFTWARE' ] ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'PHP Version', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
					</tr>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
						<tr>
							<td><?php _e( 'PHP Post Max Size', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo size_format( ini_get( 'post_max_size' ) ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'PHP Time Limit', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo ini_get( 'max_execution_time' ); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'PHP Max Input Vars', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo ini_get( 'max_input_vars' ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td><?php _e( 'MySQL Version', 'ecommerce-product-catalog' ); ?>:</td>
						<td>
							<?php
							/** @global wpdb $wpdb */
							global $wpdb;
							echo $wpdb->db_version();
							?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Max Upload Size', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo size_format( wp_max_upload_size() ); ?></td>
					</tr>
				</tbody>
			</table>
			<p></p>
			<table class="widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'Server Locale', 'ecommerce-product-catalog' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$locale = localeconv();
					foreach ( $locale as $key => $val ) {
						if ( in_array( $key, array( 'decimal_point', 'mon_decimal_point', 'thousands_sep', 'mon_thousands_sep' ) ) ) {
							echo '<tr><td>' . $key . ':</td><td>' . ( $val ? $val : __( 'N/A', 'ecommerce-product-catalog' ) ) . '</td></tr>';
						}
					}
					?>
				</tbody>
			</table>
			<p></p>
			<table class="widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'Active Plugins', 'ecommerce-product-catalog' ); ?> (<?php echo count( (array) get_option( 'active_plugins' ) ); ?>)</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$active_plugins = (array) get_option( 'active_plugins', array() );

					if ( is_multisite() ) {
						$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
					}

					foreach ( $active_plugins as $plugin ) {

						$plugin_data	 = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
						$dirname		 = dirname( $plugin );
						$version_string	 = '';
						$network_string	 = '';

						if ( !empty( $plugin_data[ 'Name' ] ) ) {

							// link the plugin name to the plugin url if available
							$plugin_name = esc_html( $plugin_data[ 'Name' ] );

							if ( !empty( $plugin_data[ 'PluginURI' ] ) ) {
								$plugin_name = '<a href="' . esc_url( $plugin_data[ 'PluginURI' ] ) . '" title="' . __( 'Visit plugin homepage', 'ecommerce-product-catalog' ) . '">' . $plugin_name . '</a>';
							}
							?>
							<tr>
								<td><?php echo $plugin_name; ?></td>
								<td><?php echo sprintf( _x( 'by %s', 'by author', 'ecommerce-product-catalog' ), $plugin_data[ 'Author' ] ) . ' &ndash; ' . esc_html( $plugin_data[ 'Version' ] ) . $version_string . $network_string; ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			<p></p>
			<table class="widefat" cellspacing="0">
				<thead>
					<tr>
						<th colspan="2"><?php _e( 'Theme', 'ecommerce-product-catalog' ); ?></th>
					</tr>
				</thead>
				<?php
				$active_theme = wp_get_theme();
				?>
				<tbody>
					<tr>
						<td><?php _e( 'Name', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo $active_theme->Name; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Version', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php
							echo $active_theme->Version;

							if ( !empty( $theme_version_data[ 'version' ] ) && version_compare( $theme_version_data[ 'version' ], $active_theme->Version, '!=' ) ) {
								echo ' &ndash; <strong style="color:red;">' . $theme_version_data[ 'version' ] . ' ' . __( 'is available', 'ecommerce-product-catalog' ) . '</strong>';
							}
							?></td>
					</tr>
					<tr>
						<td><?php _e( 'Author URL', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php echo $active_theme->{'Author URI'}; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Child Theme', 'ecommerce-product-catalog' ); ?>:</td>
						<td><?php
							echo is_child_theme() ? '<mark class="yes">' . '&#10004;' . '</mark>' : '&#10005; &ndash; ' . sprintf( __( 'If you\'re modifying %s or a parent theme you didn\'t build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, 'http://codex.wordpress.org/Child_Themes' );
							?></td>
					</tr>
					<?php
					if ( is_child_theme() ) :
						$parent_theme = wp_get_theme( $active_theme->Template );
						?>
						<tr>
							<td><?php _e( 'Parent Theme Name', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo $parent_theme->Name; ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Parent Theme Version', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo $parent_theme->Version; ?></td>
						</tr>
						<tr>
							<td><?php _e( 'Parent Theme Author URL', 'ecommerce-product-catalog' ); ?>:</td>
							<td><?php echo $parent_theme->{'Author URI'}; ?></td>
						</tr>
					<?php endif ?>
					<tr>
						<td><?php echo sprintf( __( '%s Support', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?>:</td>
						<td><?php
							if ( !is_theme_implecode_supported() ) {
								_e( 'Not Declared', 'ecommerce-product-catalog' );
							} else {
								echo '&#10004;';
							}
							?></td>
					</tr>
				</tbody>
			</table>
			<script>
			    jQuery( document ).ready( function () {
			        jQuery( "input[type='checkbox']" ).change( function () {
			            checkbox = jQuery( this );
			            if ( checkbox.is( ":checked" ) ) {
			                checked = 1;
			            } else {
			                checked = 0;
			            }
			            data = {
			                action: "save_implecode_tools",
			                field: checkbox.attr( 'name' ) + "|" + checked
			            };
			            jQuery.post( "<?php echo admin_url( 'admin-ajax.php' ) ?>", data, function ( response ) {
			                checkbox.after( "<span class='saved'>Saved!</span>" );
			                jQuery( ".saved" ).delay( 2000 ).fadeOut( 300, function () {
			                    jQuery( this ).remove();
			                } );
			            } );
			        } );
			    } );
			</script>
			<?php
		}
	}
}

add_action( 'wp_ajax_save_implecode_tools', 'ajax_save_implecode_tools' );

function ajax_save_implecode_tools() {
	if ( current_user_can( 'manage_product_settings' ) ) {
		if ( isset( $_POST[ 'field' ] ) ) {
			$checked = strval( $_POST[ 'field' ] );
			if ( strpos( $checked, '|' ) !== false ) {
				$checked = explode( '|', $checked );
				update_option( 'ic_' . $checked[ 0 ], $checked[ 1 ] );
			}
		}
	}
	echo 'done';

	wp_die(); // this is required to terminate immediately and return a proper response
}
