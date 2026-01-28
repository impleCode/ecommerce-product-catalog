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

class ic_catalog_filter {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string Sanitization function
	 */
	public $sanitization;

	/**
	 * @var string
	 */
	public $taxonomy_name;

	/**
	 * @var string|array
	 */
	public $meta_name;

	/**
	 * @var string|array Possible values are ‘=’, ‘!=’, ‘>’, ‘>=’, ‘<‘, ‘<=’, ‘LIKE’, ‘NOT LIKE’, ‘IN’, ‘NOT IN’, ‘BETWEEN’, ‘NOT BETWEEN’, ‘EXISTS’, ‘NOT EXISTS’, ‘REGEXP’, ‘NOT REGEXP’, ‘RLIKE’
	 */
	public $meta_compare;

	/**
	 * @var string|array
	 */
	public $meta_compare_value;

	/**
	 * @var bool
	 */
	public $enable_by_default;

	/**
	 * @var string AND, OR
	 */
	public $relation;

	/**
	 * @var bool
	 */
	public $permanent;

	/**
	 * @var int|string|null
	 */
	public $apply_value;

	/**
	 * @var int|string
	 */
	public $disable_value;

	/**
	 * @var array|void
	 */
	public $applied_tax_query;

	/**
	 * @var array|void
	 */
	public $applied_meta_query;

	/**
	 * @var WP_Query
	 */
	public $pre_shortcode_query;

	/**
	 * @var WP_Query
	 */
	public $pre_query;

	public $date_query;
	public $applied_date_query;


	function __construct( $name, $sanitization = 'intval', $taxonomy_name = '', $meta_name = '', $meta_compare = '=', $meta_compare_value = '1', $relation = 'AND', $enable_by_default = false, $permanent = false, $apply_value = null, $disable_value = 'all' ) {
		$this->name          = $name;
		$this->sanitization  = $sanitization;
		$this->taxonomy_name = apply_filters( 'ic_filter_taxonomy_name', $taxonomy_name );
		if ( $meta_name === 'date_query' ) {
			$this->date_query = $meta_compare_value;
		} else {
			$this->meta_name = apply_filters( 'ic_filter_meta_name', is_array( $meta_name ) ? $meta_name : array( $meta_name ) );
		}
		$this->meta_compare       = is_array( $meta_compare ) ? $meta_compare : array( $meta_compare );
		$this->meta_compare_value = apply_filters( 'ic_filter_meta_compare_value', is_array( $meta_compare_value ) ? $meta_compare_value : array( $meta_compare_value ) );
		$this->enable_by_default  = $enable_by_default;
		$this->relation           = $relation;
		$this->permanent          = $permanent;
		$this->apply_value        = $apply_value;
		$this->disable_value      = $disable_value;
		add_action( 'ic_set_product_filters', array( $this, 'set' ) );
		add_filter( 'active_product_filters', array( $this, 'enable' ) );

		if ( ! empty( $this->taxonomy_name ) ) {
			add_filter( 'init', array( $this, 'register_taxonomy' ), 50 );
			add_filter( 'ic_filter_taxonomies', array( $this, 'set_filter_taxonomy' ) );

			//add_filter( 'product_meta_save', array( $this, 'taxonomy_save' ), 10, 2 );
			add_action( 'product_meta_save_update', array( $this, 'taxonomy_save' ), 10, 2 );
			add_filter( 'ic_product_ajax_query_vars', array( $this, 'ajax_query_vars_remove' ) );
		}
		add_action( 'apply_product_filters', array( $this, 'applied' ), 52 );
		add_filter( 'apply_shortcode_product_filters', array( $this, 'applied' ), 52 );
		do_action( 'ic_catalog_filter_construct', $this );
	}

	function set_filter_taxonomy( $taxonomies ) {
		$taxonomies[ $this->taxonomy_name ] = $this->name;

		return $taxonomies;
	}

	function ajax_query_vars_remove( $query_vars ) {
		if ( empty( $query_vars['tax_query'] ) ) {
			return $query_vars;
		}
		$tax_query = $this->tax_query();
		if ( ! empty( $tax_query ) ) {
			$remove_key = array_search( $tax_query, $query_vars['tax_query'] );
			if ( $remove_key !== false ) {
				unset( $query_vars['tax_query'][ $remove_key ] );
			}
		}

		return $query_vars;
	}

	function applied( $query ) {
		//remove_action( 'apply_product_filters', array( $this, 'applied' ), 52 );
		//remove_filter( 'apply_shortcode_product_filters', array( $this, 'applied' ), 52 );
		if ( ! empty( $this->applied_tax_query ) ) {

			return apply_filters( 'ic_catalog_filter_applied_tax_query', $query, $this->applied_tax_query, $this->name );
		} else {

			return apply_filters( 'ic_catalog_filter_not_applied_tax_query', $query, $this );
		}
	}

	function apply( $query ) {
		if ( $query->get( 'ic_filter_applied_' . $this->name ) ) {
			return;
		}
		$filter_tax_query = $this->tax_query();
		if ( $filter_tax_query === array() ) {
			$this->reset();

			return;
		}
		$this->pre_query = clone $query;
		if ( ! empty( $filter_tax_query ) ) {
			if ( empty( $query->query['ic_exclude_tax'] ) || ( ! empty( $query->query['ic_exclude_tax'] ) && ! in_array( $this->taxonomy_name, $query->query['ic_exclude_tax'] ) ) ) {
				$tax_query = $query->get( 'tax_query' );
				if ( empty( $tax_query ) ) {
					$tax_query = array();
				}
				if ( ! in_array( $filter_tax_query, $tax_query ) ) {
					$tax_query[] = $filter_tax_query;
					$query->set( 'tax_query', $tax_query );
				}
				$this->applied_tax_query = $filter_tax_query;
			}
		} else if ( ! empty( $this->date_query ) ) {
			if ( empty( $query->query['ic_exclude'] ) || ( ! empty( $query->query['ic_exclude'] ) && ! in_array( 'date_query', $query->query['ic_exclude'] ) ) ) {
				$date_query = $query->get( 'date_query' );
				if ( empty( $date_query ) ) {
					$date_query = array();
				}
				if ( ! in_array( $this->date_query, $date_query ) ) {
					$date_query[] = $this->date_query;
					$query->set( 'date_query', $date_query );
				}
				$this->applied_date_query = $this->date_query;
			}
		} else {
			$filter_meta_query = $this->meta_query();
			if ( ! empty( $filter_meta_query ) ) {
				$meta_query = $query->get( 'meta_query' );
				if ( empty( $meta_query ) ) {
					$meta_query = array();
				}
				if ( ! in_array( $filter_meta_query, $meta_query ) ) {
					$meta_query[] = $filter_meta_query;
					$query->set( 'meta_query', $meta_query );
				}
				$this->applied_meta_query = $filter_meta_query;
			} else {
				do_action( 'ic_apply_' . $this->name . '_filter', $query );
			}
		}
		$query->set( 'ic_filter_applied_' . $this->name, '1' );
	}

	function check_if_empty( $query ) {
		if ( $query->get( 'ic_filter_' . $this->name . 'checked_if_empty' ) ) {
			return;
		}

		if ( ! empty( $this->applied_tax_query ) && ! empty( $this->pre_query ) /* && ! is_product_filters_active( array( $this->name ) )*/ ) {
			$removed = remove_action( 'apply_product_filters', array( $this, 'check_if_empty' ), 51 );
			//global $wp_filter;
			global $wp_query;
			//$prev_filters = $wp_filter['pre_get_posts'];
			//unset( $wp_filter['pre_get_posts'] );
			if ( ! empty( $wp_query->request ) ) {
				$posts = $wp_query->posts;
			} else {
				$new_temp_query = ic_wp_query( $query->query_vars );
				$posts          = $new_temp_query->posts;
			}
			$pre_tax_query = $this->pre_query->get( 'tax_query' );
			if ( ! is_product_filters_active( array( $this->name ) ) ) {
				$temp_pre_query  = ic_wp_query( $this->pre_query->query_vars );
				$pre_query_posts = $temp_pre_query->posts;
			}
			if ( empty( $posts ) || ( isset( $pre_query_posts ) && count( $pre_query_posts ) < $query->query_vars['posts_per_page'] ) ) {
				$query->set( 'tax_query', $pre_tax_query );
				$this->reset();
			}
			$query->set( 'ic_filter_' . $this->name . 'checked_if_empty', '1' );

			//$wp_filter['pre_get_posts'] = $prev_filters;
			if ( $removed ) {
				add_action( 'apply_product_filters', array( $this, 'check_if_empty' ), 51 );
			}

		}

	}

	function apply_shortcode( $shortcode_query ) {
		$filter_tax_query = $this->tax_query();
		if ( $filter_tax_query === array() ) {
			$this->reset();

			return $shortcode_query;
		}
		$this->pre_shortcode_query = $shortcode_query;
		if ( ! empty( $filter_tax_query ) ) {
			if ( empty( $shortcode_query['tax_query'] ) ) {
				$shortcode_query['tax_query'] = array();
			}
			if ( ! in_array( $filter_tax_query, $shortcode_query['tax_query'] ) ) {
				$shortcode_query['tax_query'][] = $filter_tax_query;
			}
			$this->applied_tax_query = $filter_tax_query;
		} else if ( ! empty( $this->date_query ) ) {
			if ( empty( $shortcode_query['date_query'] ) ) {
				$shortcode_query['date_query'] = array();
			}
			if ( ! in_array( $this->date_query, $shortcode_query['date_query'] ) ) {
				$shortcode_query['date_query'][] = $this->date_query;
			}
		} else {
			$filter_meta_query = $this->meta_query();
			if ( ! empty( $filter_meta_query ) ) {
				if ( empty( $shortcode_query['meta_query'] ) ) {
					$shortcode_query['meta_query'] = array();
				}
				if ( ! in_array( $filter_meta_query, $shortcode_query['meta_query'] ) ) {
					$shortcode_query['meta_query'][] = $filter_meta_query;
				}
			}
		}

		return $shortcode_query;
	}

	function check_if_empty_shortcode( $shortcode_query ) {
		if ( ! empty( $this->applied_tax_query ) && ! empty( $this->pre_shortcode_query ) && ! is_product_filters_active( array( $this->name ) ) ) {
			$archive_multiple_settings = get_multiple_settings();
			$per_page                  = isset( $shortcode_query['posts_per_page'] ) ? $shortcode_query['posts_per_page'] : $archive_multiple_settings['archive_products_limit'];
			//global $wp_filter;
			//$prev_filters = $wp_filter['pre_get_posts'];
			//unset( $wp_filter['pre_get_posts'] );
			$query           = ic_wp_query( $shortcode_query );
			$posts           = $query->posts;
			$pre_query       = ic_wp_query( array_merge( $this->pre_shortcode_query, array( 'posts_per_page' => $per_page ) ) );
			$pre_query_posts = $pre_query->posts;

			if ( empty( $posts ) || count( $pre_query_posts ) < $per_page ) {
				$shortcode_query = $this->pre_shortcode_query;
				$this->reset();
			}
			//$wp_filter['pre_get_posts'] = $prev_filters;
		}

		return $shortcode_query;
	}

	function tax_query() {
		if ( empty( $this->taxonomy_name ) ) {
			return;
		}
		$terms    = ic_get_terms( array( 'taxonomy' => $this->taxonomy_name ) );
		$term_ids = wp_list_pluck( $terms, 'term_taxonomy_id' );
		if ( empty( $term_ids ) ) {
			return array();
		}
		$tax_query = array(
			'taxonomy' => $this->taxonomy_name,
			'field'    => 'term_taxonomy_id',
			'terms'    => $term_ids
		);

		return apply_filters( 'ic_catalog_filter_tax_query', $tax_query, $this->name, $this->taxonomy_name, $terms );
	}

	function meta_query() {
		if ( empty( $this->meta_name ) ) {
			return;
		}
		$meta_query = array();
		foreach ( $this->meta_name as $key => $meta_name ) {
			if ( empty( $meta_name ) ) {
				continue;
			}
			$meta_query[] = array(
				'key'     => $meta_name,
				'compare' => $this->meta_compare[ $key ],
				'value'   => $this->meta_compare_value[ $key ],
			);
		}
		if ( ! empty( $meta_query ) ) {
			$meta_query['relation'] = $this->relation;
		}

		return apply_filters( 'ic_catalog_filter_meta_query', $meta_query, $this->name, $this->meta_name );
	}

	function date_query() {
		if ( empty( $this->meta_name ) || $this->meta_name !== 'after' ) {
			return;
		}

		return array(
			$this->meta_compare_value
		);
	}

	function enable( $filters ) {
		$filters[] = $this->name;

		return $filters;
	}

	function set() {
		if ( empty( $this->name ) ) {
			return;
		}
		$session        = get_product_catalog_session();
		$check_if_empty = false;
		$permanent      = $this->permanent;
		if ( isset( $_GET[ $this->name ] ) || ( $this->enable_by_default && ! isset( $session['filters'][ $this->name ] ) ) ) {
			if ( isset( $_GET[ $this->name ] ) ) {
				$filter_value = is_array( $_GET[ $this->name ] ) ? array_map( $this->sanitization, $_GET[ $this->name ] ) : call_user_func( $this->sanitization, $_GET[ $this->name ] );
			} else if ( $this->enable_by_default ) {
				$filter_value   = 1;
				$check_if_empty = true;
				$permanent      = false;
			} else {
				$filter_value = '';
			}
			if ( ! empty( $filter_value ) || is_numeric( $filter_value ) ) {
				if ( ! isset( $session['filters'] ) ) {
					$session['filters'] = array();
				}
				if ( ! isset( $session['permanent-filters'] ) ) {
					$session['permanent-filters'] = array();
				}
				if ( $permanent && ! in_array( $this->name, $session['permanent-filters'] ) ) {
					$session['permanent-filters'][] = $this->name;
				}

				$session['filters'][ $this->name ] = $filter_value;
			} else if ( isset( $session['filters'][ $this->name ] ) ) {
				unset( $session['filters'][ $this->name ] );
			}
		} else if ( ! isset( $_GET[ $this->name ] ) ) {
			if ( ! $this->enable_by_default && isset( $session['filters'][ $this->name ] ) ) {
				unset( $session['filters'][ $this->name ] );
			}
			$check_if_empty = true;
		}
		set_product_catalog_session( $session );
		if ( is_product_filter_active( $this->name, $this->apply_value ) || apply_filters( 'ic_force_filter_active', false, $this->name, $this->apply_value ) ) {
			add_action( 'apply_product_filters', array( $this, 'apply' ), 50 );
			add_filter( 'apply_shortcode_product_filters', array( $this, 'apply_shortcode' ), 50 );
			if ( $check_if_empty ) {
				add_action( 'apply_product_filters', array( $this, 'check_if_empty' ), 51 );
				add_filter( 'apply_shortcode_product_filters', array( $this, 'check_if_empty_shortcode' ), 51 );
			}
		} else {
			do_action( 'ic_dont_apply_' . $this->name . '_filter', $this );
		}
	}

	function reset() {
		$_GET[ $this->name ]     = $this->disable_value;
		$this->applied_tax_query = '';
	}

	function taxonomy_save( $product_meta, $post ) {
		foreach ( $this->meta_name as $key => $meta_name ) {
			$filtered_meta_value = apply_filters( 'ic_catalog_filter_compare_meta_value', false, $meta_name, $product_meta, $this->taxonomy_name );
			if ( $filtered_meta_value === 'false' || ( $filtered_meta_value === false && ! isset( $product_meta[ $meta_name ] ) ) ) {
				$this->save_term( $meta_name . $key, $post->ID, true );
				continue;
			}
			if ( $filtered_meta_value !== false ) {
				$compare_value = $filtered_meta_value;
			} else {
				$compare_value = $product_meta[ $meta_name ];
			}
			$different_than = $this->meta_compare_value[ $key ];
			if ( substr( $different_than, 0, 1 ) === '_' ) {
				if ( empty( $compare_value ) ) {
					$this->save_term( $meta_name . $key, $post->ID, true );
					continue;
				}
				if ( isset( $product_meta[ $different_than ] ) ) {
					$different_than_value = $product_meta[ $different_than ];
				} else {
					$different_than_value = '';
				}
				$different_than = apply_filters( 'ic_catalog_filter_compare_different_than_meta', $different_than_value, $different_than, $product_meta, $this->taxonomy_name );
				if ( $different_than === 'false' || empty( $different_than ) ) {
					$this->save_term( $meta_name . $key, $post->ID, true );
					continue;
				}
			}
			if ( $this->compare( $compare_value, $different_than, $this->meta_compare[ $key ] ) ) {
				$this->save_term( $meta_name . $key, $post->ID );
			} else {
				$this->save_term( $meta_name . $key, $post->ID, true );
			}
		}

		return $product_meta;
	}

	function save_term( $name, $product_id, $remove = false ) {
		$term = get_term_by( 'name', $name, $this->taxonomy_name );
		if ( is_wp_error( $term ) ) {
			return;
		}
		$prev_term_ids = wp_get_object_terms( $product_id, $this->taxonomy_name, array( 'fields' => 'ids' ) );
		$term_ids      = $prev_term_ids;
		if ( empty( $term ) ) {
			if ( $remove ) {
				return;
			}
			$term       = wp_insert_term( $name, $this->taxonomy_name );
			$term_ids[] = $term['term_id'];
		} else {
			$terms = array( $term->term_id );
			if ( $remove ) {
				$term_ids = array_diff( $term_ids, $terms );
			} else {
				$term_ids = array_merge( $term_ids, $terms );
			}
		}
		$term_ids = array_unique( $term_ids );
		if ( $term_ids !== $prev_term_ids ) {
			wp_set_object_terms( $product_id, $term_ids, $this->taxonomy_name );
		}
		do_action( 'ic_catalog_filter_term_saved', $this->name, $product_id, $remove, $term_ids );
	}

	function register_taxonomy() {
		if ( empty( $this->taxonomy_name ) ) {
			return;
		}
		if ( taxonomy_exists( $this->taxonomy_name ) ) {
			return;
		}
		$args       = array(
			'label'        => $this->name,
			'hierarchical' => false,
			'public'       => false,
			'query_var'    => false,
			'rewrite'      => false,
		);
		$post_types = apply_filters( 'ic_catalog_filter_taxonomy_post_types', product_post_type_array() );
		register_taxonomy( $this->taxonomy_name, $post_types, $args );
	}

	function compare( $var1, $var2, $op ) {
		switch ( $op ) {
			case "=":
				return strval( $var1 ) == strval( $var2 );
			case "!=":
				return strval( $var1 ) != strval( $var2 );
			case ">=":
				return $var1 >= $var2;
			case "<=":
				return $var1 <= $var2;
			case ">":
				return $var1 > $var2;
			case "<":
				return $var1 < $var2;
			default:
				return false;
		}
	}
}