<?php
/*
Plugin Name: ITTI Carousel
Plugin URI: http://studioitti.com/
Description: Carousel plugin
Author: Ervin Drenchev
Version: 1.0
Author URI: http://drenchev.com/
*/

class ITTI_Carousel {
		
	const POST_TYPE = 'itti_carousel';
	
	public $SQL;
	public $args = array();
	
	function __construct() {
		global $wpdb;
		
		$this->args['url'] = array(
				'meta_key' => '_itti_carousel_url',
				'meta_label' => 'ITTI Carousel Url',
				'nonce_action' => 'itti_carousel_url_nonce_action',
				'nonce_name' => 'itti_carousel_url_nonce_name',
				'not_valid_query_string_key' => 'not_valid',
				'not_unique_query_string_key' => 'not_unique',
				'class' => 'itti_carousel_url',
				'type' => 'text',
				'required' => false,
		);
		
		$this->SQL = "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s AND post_id != %d";
		
		add_action( 'init', array( $this, 'register_itti_carousel_cpt' ) );
		
		add_filter( 'manage_itti_carousel_posts_columns', array($this, 'itti_carousel_set_columns') );
		add_action( 'manage_itti_carousel_posts_custom_column', array($this, 'itti_carousel_custom_column'), 10, 2 );
		
		add_filter( "manage_edit-itti_carousel_sortable_columns", array($this, "itti_carousel_set_sortable_columns") );
		
		add_action('add_meta_boxes', array($this, 'register_itti_carousel_url_mb'));
		add_action('save_post', array($this, 'itti_carousel_save'));
		
		add_filter('wp_insert_post_data', array($this, 'itti_carousel_url_required_insert_data'));
		add_action('admin_notices', array($this, 'itti_carousel_url_required_admin_notices'));
		
		add_filter('wp_insert_post_data', array($this, 'itti_carousel_url_unique_insert_data'), 10, 2);
		add_action('admin_notices', array($this, 'itti_carousel_url_unique_admin_notices'));
		
		if ( is_admin() ) {
			add_filter( 'posts_join', array ( &$this, 'search_meta_data_join' ) );
			add_filter( 'posts_where', array( &$this, 'search_meta_data_where' ) );
		}
		
	}
	
	/**
	 * Adds a join to the WordPress meta table for license key searches in the WordPress Administration
	 *
	 * @param string $join SQL JOIN statement
	 * @return string SQL JOIN statement
	 */
	function search_meta_data_join($join) {
		global $wpdb;
		 
		// Only join the post meta table if we are performing a search
		if ( empty ( get_query_var( 's' ) ) ) {
			return $join;
		}
		 
		// Only join the post meta table if we are on the Contacts Custom Post Type
		if ( self::POST_TYPE != get_query_var( 'post_type' ) ) {
			return $join;
		}
		 
		// Join the post meta table
		$join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
		 
		return $join;
	}
	
	/**
	 * Adds a where clause to the WordPress meta table for license key searches in the WordPress Administration
	 *
	 * @param string $where SQL WHERE clause(s)
	 * @return string SQL WHERE clauses
	 */
	function search_meta_data_where($where) {
		global $wpdb;
	
		// Only join the post meta table if we are performing a search
		if ( empty ( get_query_var( 's' ) ) ) {
			return $where;
		}
		 
		// Only join the post meta table if we are on the Contacts Custom Post Type
		if ( self::POST_TYPE != get_query_var( 'post_type' ) ) {
			return $where;
		}
		 
		// Get the start of the query, which is ' AND ((', and the rest of the query
		$startOfQuery = substr( $where, 0, 7 );
		$restOfQuery = substr( $where ,7 );
		 
		// Inject our WHERE clause in between the start of the query and the rest of the query
		$where = $startOfQuery .
		"(" . $wpdb->postmeta . ".meta_value LIKE '%" . get_query_var( 's' ) . "%' OR " . $restOfQuery .
		") GROUP BY " . $wpdb->posts . ".id";
		 
		// Return revised WHERE clause
		return $where;
	}
	
	/**
	 * Setup the ITTI Carousel post type
	 */
	public function register_itti_carousel_cpt() {
		
		$labels = array(
				'name' => __( 'ITTI Carousel', 'itti' ),
				'singular_name' => __( 'Carousel', 'itti' ),
				'add_new' => _x( 'Add New', 'pluginbase', 'itti' ),
				'add_new_item' => __( 'Add New Carousel', 'itti' ),
				'edit_item' => __( 'Edit Carousel', 'itti' ),
				'new_item' => __( 'New Carousel', 'itti' ),
				'view_item' => __( 'View Carousel', 'itti' ),
				'search_items' => __( 'Search Carousel', 'itti' ),
				'not_found' =>  __( 'No carousel found', 'itti' ),
				'not_found_in_trash' => __( 'No carousel found in Trash', 'itti' ),
		);
		
		register_post_type(self::POST_TYPE, array(
				'labels' => $labels,
				'description' => __( 'Carousel for the ITTI Carousel', 'itti' ),
				'public' => true,
				'publicly_queryable' => true,
				'query_var' => true,
				'rewrite' => true,
				'exclude_from_search' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'menu_position' => 40, // probably have to change, many plugins use this
				'supports' => array(
						'title',
						'thumbnail',
						'page-attributes',
				),
		));
	}
	
	/**
	 * Setup the ITTI Carousel meta box
	 */
	public function register_itti_carousel_url_mb() {
				
		add_meta_box($this->args['url']['meta_key'], $this->args['url']['meta_label'], array($this, 'itti_carousel_mb_callback') , self::POST_TYPE, 'normal', 'low', $this->args['url']);
		
	}
	
	public function itti_carousel_mb_callback(WP_Post $post, $metabox) {
		
		wp_nonce_field($metabox['args']['nonce_action'], $metabox['args']['nonce_name']);
		
		$attributes = array(
				'id' => $metabox['args']['meta_key'],
				'name' => $metabox['args']['meta_key'],
				'value' => get_post_meta($post->ID, $metabox['args']['meta_key'], true),
				'class' => $metabox['args']['class'],
				'type' => $metabox['args']['type'],
				//'type' => 'url',
				//'required' => 'required'
		);
		
		if($metabox['args']['required'] === true ) $attributes['required'] = 'required';
		
		$attributes = implode(' ',
			array_map(
				function($key) use($attributes) {
					return sprintf('%s="%s"', $key, esc_attr($attributes[$key]));
				}, array_keys($attributes)
			)
		);
		echo '<input ' . $attributes . ' />';
		
	}
	
	public function itti_carousel_save($post_id) {
		
		if (!isset($_POST[$this->args['url']['nonce_name']])) return;
		
		if (!wp_verify_nonce($_POST[$this->args['url']['nonce_name']], $this->args['url']['nonce_action'])) return;
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		
		if (!isset($_POST[$this->args['url']['meta_key']])) return;
		
		if (!current_user_can('edit_post', $post_id)) return;
		
		update_post_meta($post_id, $this->args['url']['meta_key'], sanitize_text_field($_POST[$this->args['url']['meta_key']]));
		
	}
	
	
	public function itti_carousel_url_required_insert_data(array $data) {
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $data;
		
		if ( !isset( $_POST[$this->args['url']['nonce_name']] ) || !wp_verify_nonce($_POST[$this->args['url']['nonce_name']], $this->args['url']['nonce_action']) ) return $data;
		
		if(!empty($_GET['action']) && $_GET['action'] === 'trash') return $data; // Make sure to do nothing for posts that are going to be deleted
		
		// If there is not Video URL or it is not valid URL - mark post as draft and notify user
		if(empty($_POST[$this->args['url']['meta_key']]) || !filter_var($_POST[$this->args['url']['meta_key']], FILTER_VALIDATE_URL)) {
			
			$data['post_status'] = 'draft';
			
			add_filter('redirect_post_location', function($location) {
				
				$location = remove_query_arg('message', $location);
				
				$location = add_query_arg('message', 10, $location); // 10 is for "Post draft updated" message
				
				return add_query_arg($this->args['url']['not_valid_query_string_key'], 1, $location);
				
			});
			
		} else {
			add_filter('redirect_post_location', function($location) {
				return remove_query_arg($this->args['url']['not_valid_query_string_key'], $location);
			});
		}
		
		return $data;
		
	}
	
	public function itti_carousel_url_required_admin_notices() {
		
		if (isset($_GET[$this->args['url']['not_valid_query_string_key']])) {
			$link = sprintf('<b><a href="#%s">%s</a></b>', $this->args['url']['meta_key'], $this->args['url']['meta_label']);
			$message = sprintf(__('Your post was saved as draft because there is no required %s or it is invalid!'), $link);
			echo sprintf('<div class="error"><p>%s</p></div>', $message);
		}
		
	}
	
	
	public function itti_carousel_url_unique_insert_data(array $data, array $raw) {
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $data;
		
		if ( !isset( $_POST[$this->args['url']['nonce_name']] ) || !wp_verify_nonce($_POST[$this->args['url']['nonce_name']], $this->args['url']['nonce_action']) ) return $data;
				
		if(!empty($_GET['action']) && $_GET['action'] === 'trash') return $data; // Make sure to do nothing for posts that are going to be deleted
		
		$post_id = (empty($data['ID']) ? $raw['ID'] : $data['ID']) ?: 0;
		
		/** @var wpdb $wpdb */
		global $wpdb;
		$query = $wpdb->prepare($this->SQL, $this->args['url']['meta_key'], @$_POST[$this->args['url']['meta_key']], $post_id);
		$found = $wpdb->get_results($query, ARRAY_A);
		
		// If we found posts with save Video URL - notify user and save post as a draft
		if($found) {
			
			$data['post_status'] = 'draft';
			
			add_filter('redirect_post_location', function ($location){
				
				$location = remove_query_arg('message', $location);
				$location = add_query_arg('message', 10, $location); // 10 is for "Post draft updated" message from `edit-form-advanced.php`
				
				return add_query_arg($this->args['url']['not_unique_query_string_key'], 1, $location);
				
			});
			
		} else {
			
			add_filter('redirect_post_location', function ($location){
				
				return remove_query_arg($this->args['url']['not_unique_query_string_key'], $location);
				
			});
			
		}
		
		return $data;
		
	}
	
	public function itti_carousel_url_unique_admin_notices() {
		
		if (isset($_GET[$this->args['url']['not_unique_query_string_key']])) {
			/** @var wpdb $wpdb */
			global $wpdb;
			
			/** @var WP_Post $post */
			global $post;
			
			// <editor-fold desc="Optional: Get post links that has same Video URL">
			$meta_value = get_post_meta($post->ID, $this->args['url']['meta_key'], true);
			
			$query = $wpdb->prepare($this->SQL, $this->args['url']['meta_key'], $meta_value, $post->ID);
			$items = $wpdb->get_results($query, ARRAY_A);
			$items = array_map(function ($item) {
				return sprintf('<a target="_blank" href="%s">%s</a>', get_permalink($item['post_id']), get_the_title($item['post_id']));
			}, $items);
				$items = implode(', ', $items);
				$used_by = sprintf('by %s', $items);
				// </editor-fold>
				$link = sprintf('<b><a href="#%s">%s</a></b>', $this->args['url']['meta_key'], $this->args['url']['meta_label']);
				$message = sprintf(__('Your post was saved as draft because %s is already used %s'), $link, $used_by);
				echo sprintf('<div class="error"><p>%s</p></div>', $message);
		
		}
	}
	
	public function itti_carousel_set_columns( $columns ){
		return array(
				'cb' => '<input type="checkbox" />',
				'title' => __('Title'),
				'url' => __('Url'),
				'menu_order' => __('Order'),
				'date' => __('Date'),
		);
	}
	
	public function itti_carousel_custom_column( $column, $post_id ){
	
		switch( $column ){
	
			case 'url' :
				echo get_post_meta( $post_id, $this->args['url']['meta_key'], true );
				break;
			case 'menu_order' :
				echo get_post_field('menu_order', $post_id);
				break;
		}
	
	}
	
	public function itti_carousel_set_sortable_columns() {
		return array(
				'title' => 'title',
				'date' => 'date',
				'menu_order' => 'menu_order',
		);
	}
	
}

/* INIT */
new ITTI_Carousel();

