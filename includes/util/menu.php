<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

class ic_catalog_menu_element {
	/**
	 * @var mixed
	 */
	private $section_name;

	/**
	 * @var mixed
	 */
	private $section_id;

	/**
	 * @var mixed
	 */
	private $fields;

	/**
	 * @var mixed
	 */
	private $description;

	/**
	 * @var mixed
	 */
	private $front;

	/**
	 * @var mixed
	 */
	private $front_submenu;

	public $main_item;

	function __construct( $section_name, $fields = array(), $description = '', $front_func = '', $front_submenu_func = '' ) {
		$this->section_name  = $section_name;
		$this->section_id    = sanitize_title( $section_name );
		$this->fields        = $this->sanitize_fields( $fields );
		$this->description   = $description;
		$this->front         = $front_func;
		$this->front_submenu = $front_submenu_func;
		if ( $this->fields === false ) {
			return;
		}
		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {
		add_action( 'admin_init', array( $this, 'add_section' ) );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'item_label' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_menu_item' ), 10, 2 );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'fields' ), 10, 2 );
		add_filter( 'walker_nav_menu_start_el', array( $this, 'start_el' ), 10, 4 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'submenu' ), 10, 2 );
		add_filter( 'nav_menu_css_class', array( $this, 'css_class' ), 10, 2 );
	}

	function css_class( $classes, $menu_item ) {
		if ( ! empty( $menu_item->ic_fields ) ) {
			foreach ( $menu_item->ic_fields as $name => $value ) {
				$classes[] = sanitize_title( $name . '-' . $value );
			}

		}

		return $classes;
	}

	function submenu( $items, $args ) {
		if ( empty( $this->front_submenu ) ) {
			return $items;
		}
		foreach ( $items as $menu_item ) {
			if ( empty( $menu_item->ic_type ) ) {
				continue;
			}
			if ( $menu_item->ic_type === $this->section_id ) {
				$item = array(
					'title'            => 'label',
					'menu_item_parent' => $menu_item->db_id,
					'ID'               => $this->section_id . '_submenu',
					'db_id'            => 'ic_fake' . $menu_item->db_id . 'ic_fake',
					'url'              => '',
					'type'             => $this->section_id . '_submenu',
					'xfn'              => '',
					'current'          => false,
					'target'           => '',
					'classes'          => $menu_item->classes,
				);

				$items[] = (object) $item;
			}
		}


		return $items;
	}

	function add_section() {
		add_meta_box( $this->section_id . '-meta-box', $this->section_name, array(
			$this,
			'section'
		), 'nav-menus', 'side', 'default' );

	}

	function section() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : - 1;
		$section_name          = 'ic-menu-section-' . $this->section_id;
		?>
        <div class="posttypediv" id="<?php echo $section_name ?>">
            <div id="tabs-panel-lang-switch" class="tabs-panel tabs-panel-active">
                <ul id="lang-switch-checklist" class="categorychecklist form-no-clear">
                    <li>
                        <label class="menu-item-title">
                            <input type="checkbox" class="menu-item-checkbox"
                                   name="menu-item[<?php echo (int) $_nav_menu_placeholder; ?>][menu-item-object-id]"
                                   value="-1"> <?php echo $this->description ?>
                        </label>
                        <input type="hidden" value="custom"
                               name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]"/>
                        <input name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]"
                               type="hidden" value="#<?php echo $this->section_id ?>"/>
                        <input name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]"
                               type="hidden" value="<?php echo $this->section_name ?>"/>
                    </li>
                </ul>
            </div>
            <p class="button-controls wp-clearfix">
			<span class="add-to-menu">
                <input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right"
                       value="<?php esc_attr_e( 'Add to Menu', 'implecode-quote-cart' ); ?>"
                       name="add-post-type-menu-item" id="submit-<?php echo $section_name ?>">

				<span class="spinner"></span>
			</span>
            </p>

        </div>
		<?php
	}

	function fields( $menu_item_id, $item ) {
		if ( 'custom' !== $item->type ) {
			return;
		}
		if ( empty( $item->ic_type ) || $item->ic_type !== $this->section_id ) {
			return;
		}
		$current = ob_get_clean();
		ob_start();
		$settings_start = '<div class="menu-item-settings wp-clearfix" id="menu-item-settings-' . $menu_item_id . '">';
		if ( ic_string_contains( $current, $settings_start ) ) {
			$current_modified = substr( $current, 0, strpos( $current, $settings_start ) );
			echo $current_modified;
			echo $settings_start;
		} else {
			echo $current;
		}

		?>
        <input type="hidden" name="menu-item-url[<?php echo $menu_item_id ?>]" value="">
        <input type="hidden" name="menu-item-title[<?php echo $menu_item_id ?>]"
               value="<?php echo $this->section_name ?>">


		<?php
		foreach ( $this->fields as $field ) {
			$id            = 'edit-menu-item-' . $field['name'] . '-' . $menu_item_id;
			$type          = isset( $field['type'] ) ? $field['type'] : 'text';
			$current_value = isset( $item->ic_fields[ $field['name'] ] ) ? $item->ic_fields[ $field['name'] ] : '';
			$value         = $type === 'checkbox' ? $field['value'] : $current_value;
			$additional    = '';
			if ( $type === 'checkbox' ) {
				$additional = ' ' . checked( $current_value, $value, false ) . ' ';
			}
			?>
            <p class="field-title description description-wide">
                <label for="<?php echo $id ?>">
					<?php
					if ( $type !== 'checkbox' ) {
						echo $field['label'];
						echo '<br/>';
					}
					?>
                    <input type="<?php echo $type ?>" id="<?php echo $id ?>"
                           class="widefat edit-menu-item-<?php echo $field['name'] ?>"
                           name="<?php echo $field['name'] . '[' . $menu_item_id . ']' ?>"
                           value="<?php echo esc_attr( $value ) ?>"<?php echo $additional ?>/>
					<?php
					if ( $type === 'checkbox' ) {
						echo ' ' . $field['label'];
					}
					?>
                </label>
            </p>
			<?php
		}

	}

	function start_el( $item_output, $item, $depth, $args ) {
		if ( 'custom' !== $item->type ) {
			if ( $item->type === $this->section_id . '_submenu' ) {
				if ( ! empty( $this->front_submenu ) && function_exists( $this->front_submenu ) ) {

					return call_user_func( $this->front_submenu, $item, $this );
				}
			}

			return $item_output;
		}
		if ( empty( $item->ic_type ) || $item->ic_type !== $this->section_id ) {
			return $item_output;
		}
		if ( ! empty( $this->front ) && function_exists( $this->front ) ) {
			$this->main_item = $item;

			return call_user_func( $this->front, $item, $this );
		} else {
			return $item_output;
		}
	}

	function update_menu_item( $menu_id = 0, $menu_item_db_id = 0 ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Add new menu item via ajax.
		if ( isset( $_REQUEST['menu-settings-column-nonce'] ) && wp_verify_nonce( $_REQUEST['menu-settings-column-nonce'], 'add-menu_item' ) ) {
			if ( ! empty( $_POST['menu-item'] ) && is_array( $_POST['menu-item'] ) ) {
				foreach ( $_POST['menu-item'] as $item ) {
					if ( empty( $item['menu-item-object-id'] ) || $item['menu-item-object-id'] != - 1 ) {
						continue;
					}
					if ( ! empty( $item['menu-item-url'] ) && $item['menu-item-url'] === '#' . $this->section_id ) {
						update_post_meta( $menu_item_db_id, '_menu_item_ic_type', $this->section_id );
						update_post_meta( $menu_item_db_id, '_menu_item_url', '' );
					}
				}
			}
		}

		// Update settings for existing menu items.
		if ( isset( $_REQUEST['update-nav-menu-nonce'] ) && wp_verify_nonce( $_REQUEST['update-nav-menu-nonce'], 'update-nav_menu' ) ) {
			foreach ( $this->fields as $field ) {
				$value = isset( $_POST[ $field['name'] ][ $menu_item_db_id ] ) ? $_POST[ $field['name'] ][ $menu_item_db_id ] : '';
				if ( ! empty( $value ) ) {
					update_post_meta( $menu_item_db_id, $this->meta_name( $field['name'] ), ic_sanitize( $value ) );
				} else {
					delete_post_meta( $menu_item_db_id, $this->meta_name( $field['name'] ) );
				}
			}
		}

	}

	function item_label( $menu_item ) {
		if ( 'custom' !== $menu_item->type ) {
			return $menu_item;
		}
		$menu_item_type = $this->get_value( $menu_item->ID, 'type' );
		if ( $menu_item_type !== $this->section_id ) {
			return $menu_item;
		}
		$menu_item->ic_type    = $menu_item_type;
		$menu_item->type_label = $this->section_name;

		foreach ( $this->fields as $field ) {
			$meta_value = $this->get_value( $menu_item->ID, $field['name'] );
			if ( ! empty( $field['is-button-label'] ) ) {
				$menu_item->post_title = $meta_value;
				$menu_item->title      = $meta_value;
			}
			if ( empty( $menu_item->ic_fields ) ) {
				$menu_item->ic_fields = array();
			}
			$menu_item->ic_fields[ $field['name'] ] = $meta_value;
		}

		return $menu_item;
	}

	function get_value( $menu_item_id, $name ) {
		$meta_value = get_post_meta( $menu_item_id, $this->meta_name( $name ), true );
		if ( empty( $meta_value ) ) {
			$meta_value = $this->default_value( $name );
		}

		return $meta_value;
	}

	function meta_name( $name ) {
		return '_menu_item_ic_' . $name;
	}

	function default_value( $name ) {
		foreach ( $this->fields as $field ) {
			if ( $field['name'] === $name ) {
				$field['type'] = isset( $field['type'] ) ? $field['type'] : 'text';
				if ( $field['type'] === 'checkbox' ) {
					return '';
				} else {
					return $field['value'];
				}
			}
		}

		return '';
	}

	function sanitize_fields( $fields ) {
		foreach ( $fields as $key => $field ) {
			$fields[ $key ]['name'] = empty( $field['name'] ) ? '' : sanitize_title( $field['name'] );
			if ( empty( $fields[ $key ]['name'] ) ) {
				return false;
			}
			$fields[ $key ]['value']           = isset( $fields[ $key ]['value'] ) ? ic_sanitize( $fields[ $key ]['value'] ) : '';
			$fields[ $key ]['label']           = isset( $fields[ $key ]['label'] ) ? ic_sanitize( $fields[ $key ]['label'] ) : '';
			$fields[ $key ]['is-button-label'] = isset( $fields[ $key ]['is-button-label'] ) ? intval( $fields[ $key ]['is-button-label'] ) : '';
		}

		return $fields;
	}
}