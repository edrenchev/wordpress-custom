<?php

/*
	
@package sunsettheme
	
	========================
		THEME CUSTOM POST TYPES
	========================
*/

$contact = get_option( 'activate_contact' );
if( @$contact == 1 ){
	
	add_action( 'init', 'sunset_contact_custom_post_type' );
	
	add_filter( 'manage_sunset-contact_posts_columns', 'sunset_set_contact_columns' );
		
	add_filter( "manage_edit-sunset-contact_sortable_columns", "sunset_set_contact_sortable_columns" );
	add_filter( 'pre_get_posts', 'custom_search_query');
	
	add_action( 'manage_sunset-contact_posts_custom_column', 'sunset_contact_custom_column', 10, 2 );
	
	add_action( 'add_meta_boxes', 'sunset_contact_add_meta_box' );
	add_action( 'save_post', 'sunset_save_contact_email_data' );
	
}

function custom_search_query( $query ) {
	if ( !is_admin() && $query->is_search ) {
		$query->set('meta_query', array(
			array(
				'key' => '_contact_email_value_key',
				'value' => $query->query_vars['s'],
				'compare' => 'LIKE'
			)
		));
                // you can add additional params like a specific 'post_type'
		// $query->set('post_type', 'project');
	};
}



/* CONTACT CPT */
function sunset_contact_custom_post_type() {
	$labels = array(
		'name' 				=> 'Messages',
		'singular_name' 	=> 'Message',
		'menu_name'			=> 'Messages',
		'name_admin_bar'	=> 'Message'
	);
	
	$args = array(
		'labels'			=> $labels,
		'show_ui'			=> true,
		'show_in_menu'		=> true,
		'capability_type'	=> 'post',
		'hierarchical'		=> false,
		'menu_position'		=> 26,
		'menu_icon'			=> 'dashicons-email-alt',
		'supports'			=> array( 'title', 'editor', 'author' )
	);
	
	register_post_type( 'sunset-contact', $args );
	
}

function sunset_set_contact_columns( $columns ){
	$newColumns = array();
	$newColumns['cb'] = '<input type="checkbox" />';
	$newColumns['title'] = 'Full Name';
	$newColumns['message'] = 'Message';
	$newColumns['email'] = 'Email';
	$newColumns['date'] = 'Date';
	return $newColumns;
}

function sunset_contact_custom_column( $column, $post_id ){
	
	switch( $column ){
		
		case 'message' :
			echo get_the_excerpt();
			break;
			
		case 'email' :
			//email column
			$email = get_post_meta( $post_id, '_contact_email_value_key', true );
			echo '<a href="mailto:'.$email.'">'.$email.'</a>';
			break;
	}
	
}

function sunset_set_contact_sortable_columns() {
	return array(
			'email' => 'email',
			'title' => 'title',
			'date' => 'date',
	);
}

/* CONTACT META BOXES */

function sunset_contact_add_meta_box() {
	add_meta_box( 'contact_email', 'User Email', 'sunset_contact_email_callback', 'sunset-contact', 'side' );
}

function sunset_contact_email_callback( $post ) {
	wp_nonce_field( 'sunset_save_contact_email_data', 'sunset_contact_email_meta_box_nonce' );
	
	$value = get_post_meta( $post->ID, '_contact_email_value_key', true );
	
	echo '<label for="sunset_contact_email_field">User Email Address: </lable>';
	echo '<input type="text" id="sunset_contact_email_field" name="sunset_contact_email_field" value="' . esc_attr( $value ) . '" size="25" />';
}

function sunset_save_contact_email_data( $post_id ) {
	
	if( ! isset( $_POST['sunset_contact_email_meta_box_nonce'] ) ){
		return;
	}
	
	if( ! wp_verify_nonce( $_POST['sunset_contact_email_meta_box_nonce'], 'sunset_save_contact_email_data') ) {
		return;
	}
	
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
		return;
	}
	
	if( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	if( ! isset( $_POST['sunset_contact_email_field'] ) ) {
		return;
	}
	
	$my_data = is_email($_POST['sunset_contact_email_field']);
	$my_data = sanitize_text_field( $my_data );
	
	update_post_meta( $post_id, '_contact_email_value_key', $my_data );
	
}













