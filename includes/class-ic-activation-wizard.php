<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Plugin Activation Wizard
 *
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
if ( ! class_exists( 'ic_activation_wizard' ) ) {

	class ic_activation_wizard {

		public static $box_content = '';

		function wizard_box( $name = 'notice-ic-catalog-activation', $attr = '' ) {
			$content = self::$box_content;
			if ( ! empty( $content ) ) {
				if ( is_ic_welcome_page() ) {
					$class = '';
				} else {
					$class = 'notice notice-updated';
				}
				$class .= 'ic_cat-activation-wizard';
				if ( ! empty( $name ) ) {
					$class .= ' is-dismissible ic-notice';
					$attr  .= ' data-ic_dismissible="' . $name . '"';
				}
				?>
                <div class="<?php echo $class ?>"<?php echo $attr ?>>
					<?php
					echo $content;
					do_action( 'ic_cat_activation_wizard_bottom' );
					?>
                </div>
				<?php
				self::$box_content = '';
			}
		}

		function box_header( $content ) {
			if ( ! empty( $content ) ) {
				$h_open            = '<h3>';
				$h_close           = '</h3>';
				self::$box_content .= $h_open . $content . $h_close;
			}
		}

		function box_paragraph( $content, $light = false ) {
			if ( ! empty( $content ) ) {
				if ( $light ) {
					$p_open  = '<p>';
					$p_close = '</p>';
				} else {
					$p_open  = '<h4>';
					$p_close = '</h4>';
				}
				self::$box_content .= $p_open . $content . $p_close;
			}
		}

		function box_list( $sentences, $style = 'left' ) {
			if ( ! empty( $sentences ) && is_array( $sentences ) ) {
				if ( $style === 'left' ) {
					$style = 'text-align: left;list-style: circle inside;margin:0 auto;';
				} else {
					$style = 'text-align:left; list-style:circle inside; margin: 10px auto; display: table;';
				}
				$return = '<ul style="' . $style . '">';
				foreach ( $sentences as $sentence ) {
					$return .= '<li>' . $sentence . '</li>';
				}
				$return            .= '</ul>';
				self::$box_content .= $return;
			}
		}

		function box_choice( $questions, $next_step = false ) {
			if ( ! empty( $questions ) && is_array( $questions ) ) {
				$return = '<h4 class="ic_cat-activation-question">';
				if ( ! empty( $next_step ) ) {
					$submit_url = key( $questions );
					$return     .= '<form method="get" action="' . esc_url( $submit_url ) . '">';
					foreach ( $_GET as $key => $value ) {
						if ( $key != 'ic_catalog_activation_choice' ) {
							$return .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
						}
					}
					$return .= '<input type="hidden" name="ic_catalog_activation_choice" value="' . esc_attr( $next_step ) . '">';

					$return .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'ic_catalog_activation_choice' ) . '">';

					$choice_one = reset( $questions );
					$return     .= $choice_one;

					$return .= '<input type="submit" value="' . esc_attr( __( 'Continue', 'ecommerce-product-catalog' ) ) . '" class="ic_cat-activation-choice">';
					$return .= '</form>';
				} else {
					foreach ( $questions as $url => $question ) {
						$return .= '<a class="ic_cat-activation-choice" href="' . esc_url( $url ) . '">' . $question . '</a>';
					}
				}
				$return            .= '</h4>';
				self::$box_content .= $return;
			}
		}

		function recommended_extensions_box( $container = true ) {
			$recommended = $this->get_recommended_extensions();
			if ( ! empty( $recommended ) ) {
				$this->box_header( __( 'You have some recommended extensions based on your initial setup answers!', 'ecommerce-product-catalog' ) );
				$this->box_paragraph( __( 'See them below:', 'ecommerce-product-catalog' ) );
				$available_extensions = $this->available_recommended_extensions();
				$styling              = '';
				$styling_name         = '';
				$sentences            = array();
				$free                 = '(' . __( 'free', 'ecommerce-product-catalog' ) . ')';
				foreach ( $recommended as $extension_slug ) {
					if ( ! empty( $available_extensions[ $extension_slug ] ) ) {
						if ( ! empty( $styling ) ) {
							$styling      .= ',';
							$styling_name .= ',';
						}
						$styling      .= '#implecode_settings .extension.' . $extension_slug;
						$styling_name .= '#implecode_settings .extension.' . $extension_slug . ' .extension-name h3 span:before';
						$sentences[]  = $available_extensions[ $extension_slug ]['name'] . ' ' . $free;
					}
				}
				$this->box_list( $sentences, 'center' );

				if ( ! isset( $_GET['page'] ) || ( isset( $_GET['page'] ) && $_GET['page'] !== 'extensions.php' ) ) {
					$param = '';
					if ( isset( $_GET['ic_catalog_activation_choice'] ) ) {
						$param = '&ic_catalog_activation_choice=' . esc_attr( $_GET['ic_catalog_activation_choice'] );
					}
					$questions = array(
						admin_url( 'edit.php?post_type=al_product&page=extensions.php' . $param ) => __( 'Extensions Install Page', 'ecommerce-product-catalog' )
					);
					$this->box_choice( $questions );
				} else {
					$this->box_paragraph( sprintf( __( 'Recommended extensions on the list below are highlighted with %s and red border.', 'ecommerce-product-catalog' ), '<span class="dashicons dashicons-thumbs-up"></span>' ) );
					?>
                    <style>
                        <?php echo $styling ?>
                        {
                            border-color: red
                        ;
                        }
                        <?php echo $styling_name ?>
                        {
                            content: "\f529"
                        ;
                            font-family: dashicons
                        ;
                            vertical-align: bottom
                        ;
                        }
                    </style>
					<?php
				}
				if ( $container ) {
					$this->wizard_box( 'notice-ic-catalog-recommended' );
				}
			}
		}

		function add_recommended_extension( $extension_slug ) {
			$recommended   = $this->get_recommended_extensions();
			$recommended[] = $extension_slug;
			update_option( 'ic_cat_recommended_extensions', $recommended );

			return $recommended;
		}

		function get_recommended_extensions() {
			$recommended = array_filter( array_unique( get_option( 'ic_cat_recommended_extensions', array() ) ) );
			if ( ! empty( $recommended ) && function_exists( 'is_plugin_active' ) ) {
				$available_extensions = $this->available_recommended_extensions();
				foreach ( $recommended as $key => $slug ) {
					if ( $slug == 'catalog-booster-for-woocommerce' ) {
						$plugin = $slug . '/woocommerce-catalog-booster.php';
					} else {
						$plugin = $slug . '/' . $slug . '.php';
					}
					if ( is_plugin_active( $plugin ) || is_plugin_active_for_network( $plugin ) || empty( $available_extensions[ $slug ] ) ) {
						unset( $recommended[ $key ] );
					}
				}
			}

			return $recommended;
		}

		function any_recommended_extensions() {
			$recommended = $this->get_recommended_extensions();
			if ( ! empty( $recommended ) ) {
				return true;
			}

			return false;
		}

		function available_recommended_extensions() {
			$available_extensions = array();
			if ( function_exists( 'implecode_x_free_extensions' ) ) {
				$available_extensions = implecode_x_free_extensions();
			}
			if ( function_exists( 'implecode_free_extensions' ) ) {
				$available_extensions = array_merge( $available_extensions, implecode_free_extensions() );
			}

			return $available_extensions;
		}

		function get_notice_status( $notice = null, $type = null ) {
			if ( ! empty( $notice ) ) {
				$type = null;
			}
			$status = array();
			if ( get_current_user_id() ) {
				if ( empty( $type ) || $type === 'user' ) {
					$status = get_user_meta( get_current_user_id(), '_ic_hidden_notices', true );
					if ( empty( $status ) ) {
						$status = array();
					}
				}
				if ( ! empty( $notice ) && ( empty( $type ) || $type === 'temp' ) ) {
					$transient_name = 'ic_hidden_notices_' . $notice;
					if ( get_current_user_id() ) {
						$transient_name .= '_' . get_current_user_id();
					}
					$transient_status = get_transient( $transient_name );
					if ( ! empty( $transient_status ) ) {
						$status[ $notice ] = $transient_status;
					}
				}
			}
			if ( empty( $type ) || $type === 'global' ) {
				$global_status = get_option( 'ic_hidden_notices', array() );
				if ( empty( $global_status ) ) {
					$global_status = array();
				}
				if ( ! empty( $global_status ) ) {
					$status = array_merge( $status, array_filter( $global_status ) );
				}
			}
			if ( empty( $notice ) ) {
				return $status;
			} else if ( empty( $status[ $notice ] ) ) {
				return 0;
			} else {
				return 1;
			}
		}

		function ajax_hide_ic_notice() {
			if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
				$element = sanitize_text_field( $_POST['element'] );
				$type    = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'global';
				if ( ! empty( $element ) ) {
					$status = $this->get_notice_status( null, $type );
					if ( is_array( $status ) && empty( $status[ $element ] ) ) {
						$status[ $element ] = 1;
					}
					if ( $type === 'user' && get_current_user_id() ) {
						update_user_meta( get_current_user_id(), '_ic_hidden_notices', $status );
					} else if ( $type === 'temp' ) {
						$transient_name = 'ic_hidden_notices_' . $element;
						if ( get_current_user_id() ) {
							$transient_name .= '_' . get_current_user_id();
						}
						set_transient( $transient_name, 1, MONTH_IN_SECONDS );
					} else {
						update_option( 'ic_hidden_notices', $status );
					}
				}
			}
			wp_die();
		}

		function show_woocommerce_notice() {
			if ( class_exists( 'WooCommerce' ) ) {
				$count_posts = wp_count_posts( 'product' );
				if ( ! empty( $count_posts->publish ) ) {
					return apply_filters( 'ic_cat_show_woocommerce_notice', true );
				}
			}

			return false;
		}

		function response_to_question( $response ) {

			$questions = array();

			if ( ! empty( $response['one'] ) && ! empty( $response['next_one'] ) ) {
				$choice_one     = $response['one'];
				$choice_one_url = add_query_arg( 'ic_catalog_activation_choice', $response['next_one'] );

				$questions[ $choice_one_url ] = $choice_one;
			}

			if ( ! empty( $response['two'] ) && ! empty( $response['next_two'] ) ) {
				$choice_two     = $response['two'];
				$choice_two_url = add_query_arg( 'ic_catalog_activation_choice', $response['next_two'] );

				$questions[ $choice_two_url ] = $choice_two;
			}
			if ( ! empty( $response['three'] ) && ! empty( $response['three'] ) ) {
				$choice_three     = $response['three'];
				$choice_three_url = add_query_arg( 'ic_catalog_activation_choice', $response['next_three'] );

				$questions[ $choice_three_url ] = $choice_three;
			}

			return $questions;
		}

	}

}