<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages digital orders fields
 *
 * Here all digital orders post type is defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-orders/includes
 * @author        Norbert Dreszer
 */
class ic_orders {

	function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'formbuilder_style' ) );
		add_action( 'post_updated', array( $this, 'save' ), 1, 2 );

		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_action( 'do_meta_boxes', array( $this, 'unregister_external_boxes' ) );
		add_action( 'admin_init', array( $this, 'wpseo_compatible' ) );

		add_filter( 'posts_where', array( $this, 'admin_search_where' ) );
		add_filter( 'posts_join', array( $this, 'admin_search_join' ) );

		add_filter( 'formbuilder_raw_fields_fields', array( $this, 'delete_phone' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'disable_autosave' ) );

		add_action( 'ic_save_order', array( $this, 'save_fields' ) );
		add_action( 'ic_save_order', array( $this, 'save_pre_name' ), 10, 2 );
	}

	function register() {
		$reg_settings = array(
			'labels'               => array(
				'name'               => __( 'Orders', 'ecommerce-product-catalog' ),
				'singular_name'      => __( 'Order', 'ecommerce-product-catalog' ),
				'add_new'            => __( 'New Order', 'ecommerce-product-catalog' ),
				'add_new_item'       => __( 'Add New Order', 'ecommerce-product-catalog' ),
				'edit_item'          => __( 'Edit Order', 'ecommerce-product-catalog' ),
				'new_item'           => __( 'Add Order', 'ecommerce-product-catalog' ),
				'view_item'          => __( 'View Order', 'ecommerce-product-catalog' ),
				'search_items'       => __( 'Search Orders', 'ecommerce-product-catalog' ),
				'not_found'          => __( 'No orders found', 'ecommerce-product-catalog' ),
				'not_found_in_trash' => __( 'No orders found in the trash', 'ecommerce-product-catalog' )
			),
			'public'               => false,
			'show_in_menu'         => true,
			'show_ui'              => true,
			'publicly_queryable'   => false,
			'show_in_nav_menus'    => false,
			'has_archive'          => false,
			'supports'             => false,
			'register_meta_box_cb' => array( $this, 'metaboxes' ),
			'capability_type'      => 'digital_order',
			'capabilities'         => array(
				'publish_posts'          => 'publish_digital_orders',
				'edit_posts'             => 'edit_digital_orders',
				'edit_others_posts'      => 'edit_others_digital_orders',
				'edit_published_posts'   => 'edit_published_digital_orders',
				'edit_private_posts'     => 'edit_private_digital_orders',
				'delete_posts'           => 'delete_digital_orders',
				'delete_others_posts'    => 'delete_others_digital_orders',
				'delete_private_posts'   => 'delete_private_digital_orders',
				'delete_published_posts' => 'delete_published_digital_orders',
				'read_private_posts'     => 'read_private_digital_orders',
				'edit_post'              => 'edit_digital_order',
				'delete_post'            => 'delete_digital_order',
				'read_post'              => 'read_digital_order',
			),
			'exclude_from_search'  => true,
		);

		register_post_type( 'al_digital_orders', $reg_settings );
	}

	function metaboxes() {
		add_meta_box( 'al_digital_order_details', __( 'Details', 'ecommerce-product-catalog' ), array(
			$this,
			'details'
		), 'al_digital_orders', 'normal', 'default' );
		add_meta_box( 'al_digital_order_products', __( 'Products', 'ecommerce-product-catalog' ), array(
			$this,
			'products'
		), 'al_digital_orders', 'normal', 'default' );
		add_meta_box( 'al_digital_order_summary', __( 'Summary', 'ecommerce-product-catalog' ), array(
			$this,
			'summary'
		), 'al_digital_orders', 'side', 'default' );
		do_action( 'add_digital_order_metaboxes' );
	}

	function details() {
		global $post;
		$payment_details = ic_get_order_payment_details( $post->ID );
		$order_id        = get_post_meta( $post->ID, '_order_id' );
		if ( empty( $payment_details['name'] ) && isset( $_GET['payment_details'] ) ) {
			$payment_details         = isset( $_GET['payment_details'] ) ? ic_sanitize_order_payment_details( url_to_array( $_GET['payment_details'], false ) ) : '';
			$payment_details['date'] = current_time( 'timestamp' );
		}
		//$req_fields	 = get_eo_required_fields();
		//$disp_fields = get_option( 'disp_fields', unserialize( DEFAULT_DISP_FIELDS ) );
		//$name_fields = get_option( 'name_fields', unserialize( DEFAULT_NAME_FIELDS ) );
		?>
        <table>
        <thead>
        <tr>
            <th>
				<?php _e( 'Order Data', 'ecommerce-product-catalog' ) ?>
            </th>
            <th>
				<?php _e( 'Customer Details', 'ecommerce-product-catalog' ) ?>
            </th>
            <th>
				<?php _e( 'Additional Information', 'ecommerce-product-catalog' ) ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <label
                                    for="payment_first_name"><?php _e( 'Order Status', 'ecommerce-product-catalog' ) ?></label>
                            <select name="payment_details_status">
								<?php
								$statuses = ic_available_payment_status();
								foreach ( $statuses as $name => $status ) {
									?>
                                    <option name="payment_status[<?php echo $name ?>]"
                                            value="<?php echo $name ?>" <?php selected( $name, esc_attr( $payment_details['status'] ) ) ?> ><?php echo $status ?></option>
									<?php
								}
								?>
                            </select>
                        </td>
                    </tr>
					<?php
					if ( ! empty( $payment_details['date'] ) ) {
						if ( ! is_numeric( $payment_details['date'] ) ) {
							$payment_details['date'] = strtotime( $payment_details['date'] );
						}
						?>
                        <tr>
                            <td>
                                <label for="payment_details_date"><?php _e( 'Date', 'ecommerce-product-catalog' ) ?>
                                    : <?php echo date( get_option( 'date_format' ), $payment_details['date'] ); ?></label>
                                <input type="hidden" name="payment_details_date"
                                       value="<?php echo esc_attr( $payment_details['date'] ) ?>"/>
                            </td>
                        </tr>
						<?php
					}
					if ( ! empty( $order_id[0] ) ) {
						?>
                        <tr>
                            <td>
								<?php echo __( 'Transaction ID', 'ecommerce-product-catalog' ) . ': ' . $order_id[0]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
								<?php echo __( 'Currency', 'ecommerce-product-catalog' ) . ': ' . esc_attr( $payment_details['currency'] ); ?>
                                <input type=hidden name="payment_details_currency"
                                       value="<?php echo esc_attr( $payment_details['currency'] ) ?>"/>
                            </td>
                        </tr>
						<?php
					} else {
						?>
                        <tr>
                            <td>
								<?php implecode_settings_text( __( 'Order Currency', 'al-implecode-invoice-system' ), 'payment_details_currency', $payment_details['currency'], 'req' ); ?>
                            </td>
                        </tr>
						<?php
					}
					do_action( 'digital_order_data', $post->ID, $payment_details );
					?>
                </table>
            </td>
            <td class="ic-order-checkout-data">
                <table class="payment-details"><?php
					echo $this->get_fields( $post->ID, $payment_details );
					?>
                </table>
            </td>
            <td>
                <table>
					<?php if ( function_exists( 'digital_products_dropdown' ) ) { ?>
                        <tr>
                            <td colspan=2>
                                <label
                                        for="payment_details_shipping_email"><?php _e( 'Shipping Email', 'ecommerce-product-catalog' ) ?>
                                    <span class="star"> *</span></label>
                                <input type="text" required="required" name="payment_details_shipping_email"
                                       value="<?php echo esc_attr( $payment_details['shipping_email'] ) ?>"/>
                            </td>
                        </tr>
						<?php
					}
					if ( $payment_details['vatid'] != '' ) {
						?>
                        <tr>
                            <td><?php _e( 'EU VAT', 'ecommerce-product-catalog' ) ?>:</td>
                            <td><?php echo htmlspecialchars( $payment_details['vatid'] ) ?></td>
                        </tr>
                        <tr>
                            <td><?php _e( 'VAT Name', 'ecommerce-product-catalog' ) ?>:</td>
                            <td><input type="text" name="payment_details_vat_name"
                                       value="<?php echo htmlspecialchars( $payment_details['vat_name'] ) ?>"/></td>
                        </tr>
                        <tr>
                            <td><?php _e( 'VAT Address', 'ecommerce-product-catalog' ) ?>:</td>
                            <td><?php $payment_details['vat_address'] = ic_format_vat_address( $payment_details['vat_address'] ); ?>
                                <textarea
                                        name="payment_details_vat_address"><?php echo esc_texarea( $payment_details['vat_address'][0] ) ?><?php if ( $payment_details['vat_address'][1] != '' )
										echo '&#10;' . esc_texarea( $payment_details['vat_address'][1] ) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e( 'Ver. ID', 'ecommerce-product-catalog' ) ?>:</td>
                            <td><?php echo esc_attr( $payment_details['vat_ver_id'] ) ?></td>
                        </tr>
						<?php
					}
					do_action( 'digital_order_delivery_details', $post->ID, $payment_details );
					?>
                </table>
            </td>
        </tr>
        </tbody>
        </table><?php
	}

	function is_order_taxed( $order_products ) {
		if ( empty( $order_products['product_subtotal_net'][0] ) || empty( $order_products['product_summary'][0] ) ) {
			return false;
		}
		if ( $order_products['product_subtotal_net'][0] !== $order_products['product_summary'][0] ) {
			return true;
		}

		return false;
	}

	function products() {
		global $post;
		$fields         = ic_order_product_fields();
		$order_id       = $post->ID;
		$order_products = ic_get_order_products( $order_id );
		//$manual_order_product  = ic_get_manual_order_products( $order_id );
		$manual_order_product  = ic_get_all_manual_order_products( $order_id );
		$manual_products_count = get_post_meta( $order_id, 'manual_products', true );
		if ( empty( $manual_products_count ) ) {
			if ( ! empty( $manual_order_product ) && is_array( $manual_order_product ) ) {
				$manual_products_count = count( $manual_order_product );
			} else {
				$manual_products_count = 1;
			}
		}
		?>
        <table id="order_product_list">
            <thead>
            <tr>
                <th>
					<?php _e( 'Product Name', 'ecommerce-product-catalog' ) ?>
                </th>
                <th>
					<?php _e( 'Product Quantity', 'ecommerce-product-catalog' ) ?>
                </th>
                <th>
					<?php
					if ( $this->is_order_taxed( $order_products ) && ! is_ic_tax_included() ) {
						_e( 'Product Net Price', 'implecode-shopping-cart' );
					} else {
						_e( 'Product Price', 'implecode-shopping-cart' );
					}
					?>
                </th>
				<?php do_action( 'digital_order_products_th' ) ?>
                <th>
					<?php
					//if ( $this->is_order_taxed( $order_products ) ) {
					//	_e( 'Summary with Tax', 'implecode-shopping-cart' );
					//} else {
					_e( 'Summary', 'implecode-shopping-cart' );
					//}
					?>
                </th>
            </tr>
            </thead>
            <tbody>
			<?php if ( empty( $order_products['product_name'] ) ) {
				?>
                <input hidden=hidden class="manual_products_count" name="manual_products"
                       value="<?php echo $manual_products_count ?>"/> <?php
				if ( function_exists( 'digital_product_prices' ) ) {
					digital_product_prices();
				} else {
					$this->all_product_prices();
				}

				for ( $i = 1; $i <= $manual_products_count; $i ++ ) {
					$manual_order_product[ $i ]["id"] = isset( $manual_order_product[ $i ]["id"] ) ? $manual_order_product[ $i ]["id"] : '';
					if ( ! empty( $manual_order_product[ $i ]["id"] ) ) {
						$manual_order_product[ $i ]["name"] = get_the_title( $manual_order_product[ $i ]["id"] );
					}
					$manual_order_product[ $i ]["name"]     = isset( $manual_order_product[ $i ]["name"] ) ? $manual_order_product[ $i ]["name"] : '';
					$manual_order_product[ $i ]["quantity"] = isset( $manual_order_product[ $i ]["quantity"] ) ? $manual_order_product[ $i ]["quantity"] : '1';
					$manual_order_product[ $i ]["price"]    = isset( $manual_order_product[ $i ]["price"] ) ? $manual_order_product[ $i ]["price"] : '';
					$manual_order_product[ $i ]["summary"]  = isset( $manual_order_product[ $i ]["summary"] ) ? $manual_order_product[ $i ]["summary"] : '';
					$manual_order_product[ $i ]["c_id"]     = isset( $manual_order_product[ $i ]["c_id"] ) ? $manual_order_product[ $i ]["c_id"] : '';
					?>
                    <tr>
                        <td><?php
							if ( ! empty( $manual_order_product[ $i ]["id"] ) || empty( $manual_order_product[ $i ]["name"] ) ) {
								if ( ! isset( $_GET['custom_product'] ) && function_exists( 'digital_products_dropdown' ) ) {
									echo digital_products_dropdown( 'manual_order_product_id_' . $i, __( 'Choose product from catalog', 'ecommerce-product-catalog' ), $manual_order_product[ $i ]["id"] );
								} else {
									echo ic_select_product( __( 'Choose product from catalog', 'ecommerce-product-catalog' ), $manual_order_product[ $i ]["id"], 'manual_order_product_id_' . $i, 'digital_products_dropdown' );
								}
							}
							?>
                            <input <?php
					if ( ! isset( $_GET['custom_product'] ) ) {
						if ( ! empty( $manual_order_product[ $i ]["id"] ) || empty( $manual_order_product[ $i ]["name"] ) ) {
							echo 'style="display:none"';
						}
					}
					echo 'type="text" id="input_manual_order_product_name_' . $i . '" name="manual_order_product_name_' . $i . '" value="' . $manual_order_product[ $i ]["name"] . '" />
				</td>
				<td>
					<input custom="' . $i . '" type="number" min="0" step="1" class="product-quantity number_box" id="manual_order_product_quantity_' . $i . '" name="manual_order_product_quantity_' . $i . '" value="' . $manual_order_product[ $i ]["quantity"] . '" />
				</td>
				<td>
					<input custom="' . $i . '"';
					if ( ! isset( $_GET['custom_product'] ) ) {
						if ( ! empty( $manual_order_product[ $i ]["id"] ) || empty( $manual_order_product[ $i ]["name"] ) ) {
							echo 'type="text"';
						} else {
							echo 'type="number" min="0" step="0.01"';
						}
					} else {
						echo 'type="number" min="0" step="0.01"';
					}
					echo ' class="product-price number_box" id="manual_order_product_price_' . $i . '" name="manual_order_product_price_' . $i . '" value="' . $manual_order_product[ $i ]["price"] . '" />
				</td>';
					do_action( 'digital_order_manual_products_td', $post->ID, ! empty( $manual_order_product[ $i ]["id"] ) ? $manual_order_product[ $i ]["id"] : $manual_order_product[ $i ]["c_id"], $i );
					echo '<td>
					<input readonly type="text" class="number_box" id="manual_order_product_summary_' . $i . '" name="manual_order_product_summary_' . $i . '" value="' . $manual_order_product[ $i ]["summary"] . '" />
				</td>
			</tr>';
				}
			} else if ( ! is_array( $order_products['product_name'] ) ) {
				?>
                <tr>
                    <td>
						<?php
						$product_name_link = $this->linked_name( $order_products['product_id'], false );
						echo apply_filters( 'ic_order_product_name', $product_name_link, $order_products );
						?>
                    </td>
                    <td>
						<?php echo $order_products['product_quantity']; ?>
                    </td>
                    <td>
						<?php echo $order_products['product_price']; ?>
                    </td>
					<?php do_action( 'digital_order_products_td', $post->ID, $order_products['product_id'] ); ?>
                    <td>
						<?php echo $order_products['product_summary']; ?>
                    </td>
                </tr> <?php
			} else {
				foreach ( $order_products['product_name'] as $i => $product_name ) {
					?>
                    <tr>
                        <td>
							<?php
							$product_name_link = $this->linked_name( $order_products['product_id'][ $i ], false );
							echo apply_filters( 'ic_order_product_name', $product_name_link, $order_products, $i )
							?>
                        </td>
                        <td>
							<?php echo $order_products['product_quantity'][ $i ]; ?>
                        </td>
                        <td>
							<?php
							if ( ! empty( $order_products['product_gross_price'][ $i ] ) && $this->maybe_display_gross( $order_products ) ) {
								echo $order_products['product_gross_price'][ $i ];
							} else if ( ! empty( $order_products['product_net_price'][ $i ] ) ) {
								echo $order_products['product_net_price'][ $i ];
							} else {
								echo $order_products['product_price'][ $i ];
							}
							?>
                        </td>
						<?php do_action( 'digital_order_products_td', $post->ID, $order_products['product_id'][ $i ] ); ?>
                        <td>
							<?php
							if ( ! empty( $order_products['product_subtotal_net'][ $i ] ) && ! $this->maybe_display_gross( $order_products ) ) {
								echo $order_products['product_subtotal_net'][ $i ];
							} else {
								echo $order_products['product_summary'][ $i ];
							}
							?>
                        </td>
                    </tr> <?php
				}
			}
			?>
            </tbody>
        </table>
		<?php
		if ( empty( $order_products['product_name'] ) ) {
			ic_add_row_button();
		}
	}

	function maybe_display_gross( $order_products ) {
		if ( isset( $this->display_gross ) ) {
			return $this->display_gross;
		}
		if ( empty( $order_products['product_gross_price'] ) ) {
			return false;
		}
		$return = true;
		foreach ( $order_products['product_gross_price'] as $i => $gross_price ) {
			if ( empty( $gross_price ) || $gross_price * $order_products['product_quantity'][ $i ] != $order_products['product_summary'][ $i ] ) {
				$return = false;
			}
		}
		$this->display_gross = $return;

		return $this->display_gross;
	}

	function summary( $post ) {
		echo '<input type="hidden" name="order_summary_meta_noncename" id="order_summary_meta_noncename" value="' .
		     wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
		$fields          = array( 'price', 'email' );
		$order_summary   = implecode_array_variables_init( $fields, get_post_meta( $post->ID, '_order_summary', true ) );
		$fields          = ic_order_details_fields();
		$payment_details = implecode_array_variables_init( $fields, get_post_meta( $post->ID, '_payment_details', true ) );
		if ( empty( $order_summary['email'] ) && isset( $_GET['payment_details'] ) ) {
			$payment_details        = isset( $_GET['payment_details'] ) ? url_to_array( $_GET['payment_details'], false ) : '';
			$order_summary['email'] = $payment_details['shipping_email'];
		}
		if ( $order_summary['price'] == 0 ) {
			$order_summary['price'] = '';
		}
		?>
        <div class="order_price">
			<?php /* <label for="order_summary_price"><?php _e( 'Order Total', 'ecommerce-product-catalog' ) ?>:</label> */ ?>
			<?php
			if ( empty( $order_summary['price'] ) ) {
				echo '<div class="al-box info">' . __( 'Order summary will show up after digital order is ready.', 'ecommerce-product-catalog' ) . '</div>';
			}
			?>
            <table>
				<?php if ( ! empty( $order_summary['total_net'] ) ) { ?>
                    <tr>
                        <td><?php _e( 'Total Net', 'ecommerce-product-catalog' ) ?>:</td>
                        <td>
							<?php echo price_format( $order_summary['total_net'] ) ?>
                            <input type="hidden" name="order_summary[total_net]"
                                   value="<?php echo esc_attr( $order_summary['total_net'] ) ?>"/>
                        </td>
                    </tr>
				<?php } ?>
				<?php
				if ( ! empty( $order_summary['tax'] ) && floatval( $order_summary['tax'] ) !== floatval( 0 ) ) {
					?>
                    <tr>
                        <td><?php _e( 'Order Tax', 'ecommerce-product-catalog' ) ?>:</td>
                        <td>
							<?php echo price_format( $order_summary['tax'] ) ?>
                            <input type="hidden" name="order_summary[tax]"
                                   value="<?php echo esc_attr( $order_summary['tax'] ) ?>"/>
                        </td>
                    </tr>
				<?php } ?>
				<?php if ( ! empty( $order_summary['handling'] ) ) { ?>
                    <tr>
                        <td><?php _e( 'Shipping & Handling', 'ecommerce-product-catalog' ) ?>:</td>
                        <td>
							<?php echo price_format( $order_summary['handling'] ) ?>
                            <input type="hidden" name="order_summary[handling]"
                                   value="<?php echo esc_attr( $order_summary['handling'] ) ?>"/>
                        </td>
                    </tr>
				<?php } ?>
				<?php if ( ! empty( $order_summary['price'] ) ) { ?>
                    <tr>
                        <td><?php _e( 'Order Total', 'ecommerce-product-catalog' ) ?>:</td>
                        <td>
							<?php echo price_format( $order_summary['price'] ) ?>
                            <input type="hidden" name="order_summary[price]"
                                   value="<?php echo esc_attr( $order_summary['price'] ) ?>"/>
                        </td>
                    </tr>
				<?php } ?>
            </table>
			<?php /* <div id="order_summary_price" class="order-total-price" style="display: inline-block;vertical-align: middle;"><?php echo price_format( $order_summary[ 'price' ] ) ?></div> */ ?>

        </div>
        <div class="order_email">
            <label for="order_summary_email"><?php _e( 'Contact Email', 'ecommerce-product-catalog' ) ?>:</label>
			<?php
			if ( empty( $order_summary['email'] ) ) {
				echo '<div class="al-box info">' . __( 'Contact email will be set as system user name. This email will be used to send all account credentials to a customer.', 'ecommerce-product-catalog' ) . '</div>';
			}
			?>
            <input type="text" required="required" name="order_summary[email]"
                   value="<?php echo esc_attr( $order_summary['email'] ) ?>"/>
        </div>
		<?php
		do_action( 'digital-order-summary', $post->ID );
	}

	/**
	 * Saves manual digital order
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 * @return int If failed
	 */
	function save( $post_id, $post ) {
		$post_type_now = $post->post_type;
		if ( $post_type_now == 'al_digital_orders' ) {
			$order_summary_meta_noncename = isset( $_POST['order_summary_meta_noncename'] ) ? $_POST['order_summary_meta_noncename'] : '';
			if ( empty( $order_summary_meta_noncename ) || ( ! empty( $order_summary_meta_noncename ) && ! wp_verify_nonce( $order_summary_meta_noncename, plugin_basename( __FILE__ ) ) ) ) {
				return $post->ID;
			}
			if ( ! current_user_can( 'edit_digital_order', $post->ID ) ) {
				return $post->ID;
			}

			if ( ! isset( $_POST['payment_details_status'] ) ) {
				return $post_id;
			}
			$order_summary                                    = isset( $_POST['order_summary'] ) ? $_POST['order_summary'] : '';
			$order_meta['_payment_details']                   = ic_get_order_payment_details( $post_id );
			$prev_status                                      = isset( $order_meta['_payment_details']['status'] ) ? $order_meta['_payment_details']['status'] : '';
			$order_meta['_payment_details']['status']         = ! empty( $_POST['payment_details_status'] ) ? $_POST['payment_details_status'] : '';
			$order_meta['_payment_details']['date']           = ! empty( $_POST['payment_details_date'] ) ? $_POST['payment_details_date'] : date( 'd-m-Y' );
			$order_meta['_payment_details']['name']           = ! empty( $_POST['payment_details_name'] ) ? $_POST['payment_details_name'] : '';
			$order_meta['_payment_details']['email']          = ! empty( $_POST['payment_details_email'] ) ? $_POST['payment_details_email'] : '';
			$order_meta['_payment_details']['vatid']          = ! empty( $_POST['payment_details_vatid'] ) ? $_POST['payment_details_vatid'] : '';
			$order_meta['_payment_details']['street']         = ! empty( $_POST['payment_details_address'] ) ? $_POST['payment_details_address'] : '';
			$order_meta['_payment_details']['postcode']       = ! empty( $_POST['payment_details_postcode'] ) ? $_POST['payment_details_postcode'] : '';
			$order_meta['_payment_details']['city']           = ! empty( $_POST['payment_details_city'] ) ? $_POST['payment_details_city'] : '';
			$order_meta['_payment_details']['country']        = ! empty( $_POST['payment_details_country'] ) ? $_POST['payment_details_country'] : '';
			$order_meta['_payment_details']['country_code']   = ! empty( $_POST['payment_details_country_code'] ) ? $_POST['payment_details_country_code'] : '';
			$order_meta['_payment_details']['billing_name']   = ! empty( $_POST['payment_details_billing_name'] ) ? $_POST['payment_details_billing_name'] : '';
			$order_meta['_payment_details']['shipping_email'] = ! empty( $_POST['payment_details_shipping_email'] ) ? $_POST['payment_details_shipping_email'] : '';
			if ( empty( $order_meta['_payment_details']['shipping_email'] ) ) {
				$order_meta['_payment_details']['shipping_email'] = ! empty( $order_summary['email'] ) ? $order_summary['email'] : '';
			}
			$order_meta['_payment_details']['currency']    = ! empty( $_POST['payment_details_currency'] ) ? $_POST['payment_details_currency'] : '';
			$order_meta['_payment_details']['vat_name']    = ! empty( $_POST['payment_details_vat_name'] ) ? $_POST['payment_details_vat_name'] : '';
			$order_meta['_payment_details']['vat_address'] = ! empty( $_POST['payment_details_vat_address'] ) ? $_POST['payment_details_vat_address'] : '';
			if ( function_exists( 'get_shopping_checkout_form_fields' ) ) {
				$fields = json_decode( get_shopping_checkout_form_fields() );
				foreach ( $fields->fields as $field ) {
					$pre_name                               = 'cart_';
					$cid                                    = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
					$order_meta['_payment_details'][ $cid ] = ! empty( $_POST[ $cid ] ) ? $_POST[ $cid ] : '';
				}
			}
			$manual_order_product = array();
			if ( isset( $_POST['manual_products'] ) ) {
				$order_meta['manual_products'] = ! empty( $_POST['manual_products'] ) ? $_POST['manual_products'] : '';
				$order_summary['price']        = 0;
				$i                             = 1;
				for ( $a = 1; $a <= $order_meta['manual_products']; $a ++ ) {
					$manual_order_product[ $i ]["id"] = ! empty( $_POST[ 'manual_order_product_id_' . $a ] ) ? $_POST[ 'manual_order_product_id_' . $a ] : '';
					if ( ! empty( $manual_order_product[ $i ]["id"] ) ) {
						$manual_order_product[ $i ]["name"] = get_the_title( $manual_order_product[ $i ]["id"] );
						$manual_order_product[ $i ]["c_id"] = $i . '_' . $manual_order_product[ $i ]["id"] . '_t_' . $post_id;
					} else {
						$manual_order_product[ $i ]["name"] = ! empty( $_POST[ 'manual_order_product_name_' . $a ] ) ? $_POST[ 'manual_order_product_name_' . $a ] : '';
						$manual_order_product[ $i ]["c_id"] = 'c_' . $i . '_t_' . $post_id;
					}
					$manual_order_product[ $i ]["name"]     = isset( $manual_order_product[ $i ]["name"] ) ? $manual_order_product[ $i ]["name"] : '';
					$manual_order_product[ $i ]["quantity"] = ! empty( $_POST[ 'manual_order_product_quantity_' . $a ] ) ? $_POST[ 'manual_order_product_quantity_' . $a ] : '';
					$manual_order_product[ $i ]["price"]    = ! empty( $_POST[ 'manual_order_product_price_' . $a ] ) ? $_POST[ 'manual_order_product_price_' . $a ] : '';
					$manual_order_product[ $i ]["summary"]  = ! empty( $_POST[ 'manual_order_product_summary_' . $a ] ) ? $_POST[ 'manual_order_product_summary_' . $a ] : '';
					$order_summary['price']                 = floatval( $order_summary['price'] ) + floatval( $manual_order_product[ $i ]["summary"] );
					do_action( 'order_save_products', $post_id, $manual_order_product[ $i ]["id"], $manual_order_product[ $i ]["c_id"], $a );
					if ( $manual_order_product[ $i ]["name"] == '' ) {
						foreach ( $manual_order_product[ $i ] as $key => $value ) {
							$manual_order_product[ $i ][ $key ] = '';
						}
						$removed_last = 1;
					} else {
						$i ++;
						$removed_last = 0;
					}
				}
				if ( empty( $removed_last ) ) {
					$i = $i - 1;
				}
				$order_meta['manual_products']      = $i;
				$order_meta['manual_order_product'] = $manual_order_product;
			}
			$order_meta['_order_summary'] = $order_summary;
			//error_log( print_r( $order_meta, 1 ) );
			foreach ( $order_meta as $key => $value ) {
				$current_value = get_post_meta( $post->ID, $key, true );
				if ( isset( $value ) && ! isset( $current_value ) ) {
					add_post_meta( $post->ID, $key, $value, true );
				} else if ( isset( $value ) && $value != $current_value ) {
					update_post_meta( $post->ID, $key, $value );
				} else if ( ! isset( $value ) && $current_value ) {
					delete_post_meta( $post->ID, $key );
				}
			}
			$triggered = get_post_meta( $post->ID, '_order_completed_triggered', true );
			if ( empty( $triggered ) && ( $order_meta['_payment_details']['status'] == ic_order_completed_status_trigger() || $order_meta['_payment_details']['status'] == 'completed' ) && ( $prev_status != $order_meta['_payment_details']['status'] || ! empty( $manual_order_product ) ) ) {
				$order_products = ic_get_order_products( $post->ID );
				do_action( 'order_completed', $post_id, $order_meta['_payment_details'], $order_products, $manual_order_product );
				update_post_meta( $post->ID, '_order_completed_triggered', 1 );
			}

			do_action( 'update_digital_order', $post_id, $post );
		}
	}

	/**
	 * Returns digital order fields
	 *
	 * @return json
	 */
	function get_fields( $order_id, $payment_details ) {
		$form = apply_filters( 'ic_order_admin_fields', '', $payment_details );
		if ( ! empty( $form ) ) {
			return $form;
		}
		$pre_name                   = $this->order_pre_name( $order_id );
		$fields                     = $this->fields( $order_id );
		$payment_details['country'] = empty( $payment_details['country'] ) ? get_supported_country_name( $payment_details['country_code'] ) : $payment_details['country'];
		$payment_details['address'] = $payment_details['street'];
		if ( function_exists( 'get_shopping_checkout_form_fields' ) ) {
			$form = '<div class="ic-checkout-form-data table" data-pre_name="' . $pre_name . '" style="margin: 0 auto;border-collapse:collapse;">';
			$form .= formbuilder_raw_fields( $fields, 2, $pre_name, $payment_details, array() );
			$form .= '</div>';
		} else if ( defined( 'PAYPAL_PLUGIN_BASE_DIR' ) && function_exists( 'default_order_form_editor_settings' ) ) {
			$form = formbuilder_raw_fields( $fields, 2, 'payment_details_', $payment_details, array(
				'surname',
				'state'
			) );
		} else if ( function_exists( 'get_order_form_fields' ) ) {
			$form = formbuilder_raw_fields( $fields, 2, 'payment_details_', $payment_details, array(
				'surname',
				'state'
			) );
		}

		return $form;
	}

	function fields( $order_id ) {
		$fields = get_post_meta( $order_id, '_ic_form_fields', true );
		if ( empty( $fields ) ) {
			$fields = $this->default_fields();
		}

		return $fields;
	}

	function default_fields() {
		$fields = array();
		if ( function_exists( 'get_shopping_checkout_form_fields' ) ) {
			$fields = get_shopping_checkout_form_fields();
		} else if ( defined( 'PAYPAL_PLUGIN_BASE_DIR' ) && function_exists( 'default_order_form_editor_settings' ) ) {
			$fields = default_order_form_editor_settings();
		} else if ( function_exists( 'get_order_form_fields' ) ) {
			$fields = get_order_form_fields();
		}

		return $fields;
	}

	function save_fields( $order_id ) {
		if ( empty( $order_id ) ) {
			return;
		}
		$fields = $this->fields( $order_id );
		if ( ! empty( $fields ) ) {
			update_post_meta( $order_id, '_ic_form_fields', $fields );
		}
	}

	function order_pre_name( $order_id ) {
		$pre_name = get_post_meta( $order_id, '_pre_name', true );
		if ( empty( $pre_name ) ) {
			$pre_name = 'cart_';
		}

		return $pre_name;
	}

	function save_pre_name( $order_id, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			update_post_meta( $order_id, '_pre_name', $pre_name );
		}
	}

	/**
	 * Adds digital order formbuilder styles
	 *
	 */
	function formbuilder_style() {
		if ( get_post_type() == 'al_digital_orders' ) {
			wp_enqueue_style( 'implecode-form-builder-css' );
		}
	}

	function all_product_prices( $offset = null ) {
		if ( $offset > 500 ) {
			return;
		}
		$pages = get_all_catalog_products( null, null, 10, $offset );
		if ( empty( $pages ) ) {
			return;
		}
		if ( empty( $offset ) ) {
			$offset = 0;
			echo '<input hidden=hidden id="product_price_noid" value="0" />';
		}
		foreach ( $pages as $page ) {
			$price = apply_filters( 'digital_product_prices_table_price', get_post_meta( $page->ID, '_price', true ), $page->ID );
			if ( ! empty( $price ) ) {
				echo '<input type=hidden id="product_price_' . $page->ID . '" value="' . $price . '" />';
			}
		}
		$offset += count( $pages );

		return $this->all_product_prices( $offset );
	}

	function linked_name( $product_id, $front = true ) {
		$product_name = get_product_name( $product_id );
		if ( $front ) {
			$url = get_product_url( $product_id );
		} else {
			$url = ic_product_edit_url( $product_id );
		}
		$link = '<a href="' . $url . '">' . $product_name . '</a>';

		return $link;
	}

	/**
	 * Defines digital orders messages
	 *
	 * @param array $messages
	 *
	 * @return array
	 * @global object $post
	 * @global int $post_ID
	 */
	function messages( $messages ) {
		global $post, $post_ID;
		$post_type = get_post_type();
		if ( $post_type == 'al_digital_orders' ) {

			$messages[ $post_type ] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Digital order updated.', 'ecommerce-product-catalog' ),
				2  => __( 'Custom field updated.', 'ecommerce-product-catalog' ),
				3  => __( 'Custom field deleted.', 'ecommerce-product-catalog' ),
				4  => __( 'Digital order updated.', 'ecommerce-product-catalog' ),
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Digital order restored to revision from %s', 'ecommerce-product-catalog' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __( 'Digital order published.', 'ecommerce-product-catalog' ),
				7  => __( 'Page saved.', 'ecommerce-product-catalog' ),
				8  => __( 'Digital order submitted.', 'ecommerce-product-catalog' ),
				9  => sprintf( __( 'Digital order scheduled for: <strong>%1$s</strong>.', 'ecommerce-product-catalog' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
				10 => __( 'Digital order draft updated.', 'ecommerce-product-catalog' ),
			);
		}

		return $messages;
	}

	/**
	 * Disables some WordPress SEO plugin features for digital orders screen
	 */
	function unregister_external_boxes() {
		remove_meta_box( 'wpseo_meta', 'al_digital_orders', 'normal' );
	}

	/**
	 * Disables some WordPress SEO plugin features for digital orders screen
	 * @global string $pagenow
	 */
	function wpseo_compatible() {
		global $pagenow;
		if ( isset( $_GET['post_type'] ) ) {
			if ( 'edit.php' == $pagenow && 'al_digital_orders' == $_GET['post_type'] ) {
				add_filter( 'wpseo_use_page_analysis', '__return_false' );
			}
		} else if ( isset( $_GET['post'] ) ) {
			if ( 'post.php' == $pagenow && 'al_digital_orders' == get_post_type( $_GET['post'] ) ) {
				add_filter( 'wpseo_use_page_analysis', '__return_false' );
			}
		}
	}

	function disable_autosave() {
		if ( 'al_digital_orders' == get_post_type() ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Deletes phone field from digital orders screen
	 *
	 * @param object $fields
	 * @param string $form
	 *
	 * @return object
	 */
	function delete_phone( $fields, $form ) {
		if ( $form == 'payment_details_' ) {
			unset( $fields->fields[4] );
		}

		return $fields;
	}

	/**
	 * Search digital orders by custom fields also
	 *
	 * @param string $join
	 *
	 * @return string
	 * @global string $pagenow
	 * @global type $wpdb
	 */
	function admin_search_join( $join ) {
		global $pagenow, $wpdb;
		if ( is_admin() && $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'al_digital_orders' && isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
		}

		return $join;
	}

	/**
	 * Search digital orders by custom fields also
	 *
	 * @param type $where
	 *
	 * @return type
	 * @global string $pagenow
	 * @global type $wpdb
	 */
	function admin_search_where( $where ) {
		global $pagenow, $wpdb;
		if ( is_admin() && $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'al_digital_orders' && isset( $_GET['s'] ) && $_GET['s'] != '' ) {
			$where = preg_replace(
				"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/", "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
		}

		return $where;
	}

}

global $ic_orders;
$ic_orders = new ic_orders;


if ( ! function_exists( 'ic_get_supported_country_code' ) ) {

	/**
	 * Gets country code by its name
	 *
	 * @param string $country_name
	 *
	 * @return string
	 */
	function ic_get_supported_country_code( $country_name ) {
		$return    = 'none';
		$countries = implecode_supported_countries();
		foreach ( $countries as $key => $country ) {
			if ( $country_name == $country ) {
				$return = $key;
			}
		}

		return $return;
	}

}

if ( ! function_exists( 'ic_select_product' ) ) {

	function ic_select_product( $first_option, $selected_value, $select_name, $class = null, $echo = 1, $attr = null ) {
		$product_count = ic_products_count();
		if ( $product_count < 1000 ) {
			$catalogs = product_post_type_array();
			$set      = array(
				'posts_per_page'   => - 1,
				'offset'           => 0,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'post_type'        => $catalogs,
				'post_status'      => 'publish',
				'suppress_filters' => true,
				'fields'           => 'ids'
			);

			$pages        = get_posts( $set );
			$field_number = filter_var( $select_name, FILTER_SANITIZE_NUMBER_INT );

			$select_box = '<select custom="' . $field_number . '" id="' . $select_name . '" name="' . $select_name . '" class="all-products-dropdown ' . $class . '" ' . $attr . '>';
			if ( ! empty( $first_option ) ) {
				$select_box .= '<option value="noid">' . $first_option . '</option>';
			}
			foreach ( $pages as $product_id ) {
				if ( is_array( $selected_value ) ) {
					$selected = in_array( $product_id, $selected_value ) ? 'selected' : '';
				} else {
					$selected = selected( $product_id, $selected_value, 0 );
				}
				$select_box .= '<option class="id_' . $product_id . '" value="' . $product_id . '" ' . $selected . '>' . get_product_name( $product_id ) . ' (' . $product_id . ')</option>';
			}
			$select_box .= '</select>';
		} else {
			$select_box = '<input type="text" name="' . $select_name . '" placeholder="' . __( 'Set Product ID', 'al-implecode-product-sidebar' ) . '" value="' . $selected_value . '"/>';
		}

		return echo_ic_setting( $select_box, $echo );
	}

}






