<?php
/**
 * Plugin Name: TM ACF Form Builder
 * Description: Interface for building forms w/ acf
 * Version: 1.2
 * Author: Will Nahmens - Think Modular
 * Author URI: https://willnahmens.com 
 * Author URI: https://willnahmens.com 
 * 
 */


global $tm_acf_form_db_version;
$tm_acf_form_db_version = '1.0';

function tm_acf_form_install() {
	global $wpdb;
	global $tm_acf_form_db_version;

	$table_name = $wpdb->prefix . 'tm_acf_form';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'tm_acf_form_db_version', $tm_acf_form_db_version );
}


register_activation_hook( __FILE__, 'tm_acf_form_install' );

add_action('admin_menu', 'tm_acf_form_setup_menu');
function tm_acf_form_setup_menu(){
    add_menu_page( 'TM ACF Form', 'TM ACF Form', 'manage_options', 'tm-acf-form', 'tm_acf_form_init' );
}
 
function tm_acf_form_init(){
	global $wpdb;

	if(isset($_POST['name'])) {
		// double check if page is created
		if ( ! current_user_can( 'activate_plugins' ) ) return;

		$formname = $_POST['name'];
      
		$postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = 'TM Form' AND post_type = 'page'" );

		if( !$postid) {
			
			$current_user = wp_get_current_user();
			
			// create post object
			$page = array(
			'post_title'  => __( 'TM Form' ),
			'post_status' => 'publish',
			'post_author' => $current_user->ID,
			'post_type'   => 'page',
			);
			
			// insert the post into the database
			wp_insert_post( $page );
		}
		$table = $wpdb->prefix.'tm_acf_form';
		$data = array('name' => $formname);
		$wpdb->insert($table,$data);

		$formslug = 'tm_form_'.str_replace(' ', '', $formname);
		// $postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = $formslug" );
		// var_dump($postid);

		/*global $wpdb;

		$table_name = $wpdb->prefix . "tm_acf_form";
	
		$forms = $wpdb->get_results( "SELECT * FROM $table_name" );
		if(count($forms)) {
			foreach ($forms as $form) {
				$formname = $form->name;
				if($formname) {
					init_form_post_type($formname);
				}
			}
		}

		var_dump(get_post_types());

		$new_field_group = array(
			'post_title'     => $formname,
			'post_excerpt'   => sanitize_title( $formname ),
			'post_name'      => $formslug,
			'post_date'      => date( 'Y-m-d H:i:s' ),
			'comment_status' => 'closed',
			'post_status'    => 'publish',
			'post_type'      => 'acf-field-group',
		);
		$field_id = wp_insert_post( $new_field_group );

		$post = get_post($field_id);
		var_dump($post);
		$groups = acf_get_field_groups(array('post_type' => $formslug));
		if(count($groups == 0)) {
			// create new form group with the title of the 
			var_dump(function_exists('acf_add_local_field_group'));
			// if( function_exists('acf_add_local_field_group') ){
				acf_add_local_field_group(array(
					'key' => $formslug,
					'title' => $formname,
					'fields' => array (
						array (
							'key' => 'field_1',
							'label' => 'Sub Title',
							'name' => 'sub_title',
							'type' => 'text',
						)
					),
					'location' => array (
						array (
							array (
								'param' => 'post_type',
								'operator' => '==',
								'value' => $formslug,
							),
						),
					),
				));
			// }
		}*/

		echo '<div class="notice notice-success is-dismissible">
       			<p>'.$formname.' successfully created!</p>
    		</div>';
	}
	echo '
		<div style="padding: 1.5rem 0;">
			<form style="" action="" method="post">
				<label for="name" style="display: block; margin-bottom: 0.5rem;">Form Name:</label> 
				<input style="" type="text" name="name">
				<input style="" class="button button-primary" type="submit" value="Create New Form">
			</form>
		</div>
	';

	echo '<table class="wp-list-table widefat fixed striped table-view-list">
		<thead>
			<tr>
				<th scope="col">Form Name</th>
				<th scope="col">Edit Form Fields</th>
				<th scope="col">Form Link</th>
				<th scope="col">Form Submissions</th>
			</tr>
		</thead>
		<tbody>';
	
	// need to get a post id for the existing field group
	$rows = $wpdb->get_results( "SELECT * FROM wp_tm_acf_form");
	foreach ($rows as $row) {
		// var_dump($row->name);
		$slug = str_replace(' ', '-', $row->name);
		$slug = strtolower($slug);
		$formtypeslug = strtolower(str_replace(' ', '', $row->name));
		// var_dump($slug);
		echo '<tr>';
			echo "<td>{$row->name}</td>";
			echo "<td><a href='". home_url() ."/wp-admin/edit.php?post_type=acf-field-group'>Edit Form</a></td>";
			echo "<td><a href='". home_url() ."/tm-form?formname={$slug}'>View Form</a></td>";
			echo "<td><a href='". home_url() ."/wp-admin/edit.php?post_type=tm_form_{$formtypeslug}'>View Submissions</a></td>";
		echo '</tr>';
	}
	echo '</tbody>'; 
	echo '</table>';
}


define( 'TM_ACF_FORM_PLUGIN_FILE', __FILE__ );
register_activation_hook( TM_ACF_FORM_PLUGIN_FILE, 'tm_acf_form_plugin_activation' );
function tm_acf_form_plugin_activation() {
	global $wpdb;
    if ( ! current_user_can( 'activate_plugins' ) ) return;
      
	$postid = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = 'TM Form' AND post_type = 'page'" );

    if( !$postid) {
        
        $current_user = wp_get_current_user();
        
        // create post object
        $page = array(
        'post_title'  => __( 'TM Form' ),
        'post_status' => 'publish',
        'post_author' => $current_user->ID,
        'post_type'   => 'page',
        );
        
        // insert the post into the database
        wp_insert_post( $page );
    }
}

add_filter( 'page_template', 'tm_acf_form_page_template' );
function tm_acf_form_page_template( $page_template ) {
    if ( is_page( 'tm-form' ) ) {
        $page_template = dirname( __FILE__ ) . '/tm-form-layout.php';
    }
    return $page_template;
}


// Register Custom Post Type TM ACF Form
function create_tm_acf_form_cpt() {

	// get all post types from the db and create them on init
	global $wpdb;

	$table_name = $wpdb->prefix . "tm_acf_form";
  
	$forms = $wpdb->get_results( "SELECT * FROM $table_name" );
	if(count($forms)) {
		foreach ($forms as $form) {
			$formname = $form->name;
			if($formname) {
				$formslug = 'tm_form_'.strtolower(str_replace(' ', '', $formname));

				$labels = array(
					'name' => _x( "{$formname}s", 'Post Type General Name', 'textdomain' ),
					'singular_name' => _x( ''.$formname.'', 'Post Type Singular Name', 'textdomain' ),
					'menu_name' => _x( ''.$formname.'s', 'Admin Menu text', 'textdomain' ),
					'name_admin_bar' => _x( ''.$formname.'', 'Add New on Toolbar', 'textdomain' ),
					'archives' => __( ''.$formname.' Archives', 'textdomain' ),
					'attributes' => __( ''.$formname.' Attributes', 'textdomain' ),
					'parent_item_colon' => __( 'Parent '.$formname.':', 'textdomain' ),
					'all_items' => __( 'All '.$formname.'s', 'textdomain' ),
					'add_new_item' => __( 'Add New '.$formname.'', 'textdomain' ),
					'add_new' => __( 'Add New', 'textdomain' ),
					'new_item' => __( 'New '.$formname.'', 'textdomain' ),
					'edit_item' => __( 'Edit '.$formname.'', 'textdomain' ),
					'update_item' => __( 'Update '.$formname.'', 'textdomain' ),
					'view_item' => __( 'View '.$formname.'', 'textdomain' ),
					'view_items' => __( 'View '.$formname.'s', 'textdomain' ),
					'search_items' => __( 'Search '.$formname.'', 'textdomain' ),
					'not_found' => __( 'Not found', 'textdomain' ),
					'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
					'featured_image' => __( 'Featured Image', 'textdomain' ),
					'set_featured_image' => __( 'Set featured image', 'textdomain' ),
					'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
					'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
					'insert_into_item' => __( 'Insert into '.$formname.'', 'textdomain' ),
					'uploaded_to_this_item' => __( 'Uploaded to this '.$formname.'', 'textdomain' ),
					'items_list' => __( ''.$formname.'s list', 'textdomain' ),
					'items_list_navigation' => __( ''.$formname.'s list navigation', 'textdomain' ),
					'filter_items_list' => __( 'Filter '.$formname.'s list', 'textdomain' ),
				);
				$args = array(
					'label' => __( ''.$formname.'', 'textdomain' ),
					'description' => __( '', 'textdomain' ),
					'labels' => $labels,
					'menu_icon' => 'dashicons-admin-customizer',
					'supports' => array('title', 'custom-fields'),
					'taxonomies' => array(),
					'public' => true,
					'show_ui' => true,
					'show_in_menu' => false,
					'menu_position' => 5,
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'can_export' => true,
					'has_archive' => true,
					'hierarchical' => false,
					'exclude_from_search' => false,
					'show_in_rest' => true,
					'publicly_queryable' => true,
					'capability_type' => 'post',
				);
				register_post_type( $formslug, $args );

				$new_field_group = array(
					'post_title'     => $formname,
					'post_excerpt'   => sanitize_title( $formname ),
					'post_name'      => $formslug,
					'post_date'      => date( 'Y-m-d H:i:s' ),
					'comment_status' => 'closed',
					'post_status'    => 'publish',
					'post_type'      => 'acf-field-group',
				);
				$field_id = wp_insert_post( $new_field_group );

				/*if( function_exists('acf_add_local_field_group') ) {

					acf_add_local_field_group(array(
						'key' => $formslug,
						'title' => $formname,
						'fields' => array(
						),
						'location' => array(
							array(
								array(
									'param' => 'post_type',
									'operator' => '==',
									'value' => $formslug,
								),
							),
						),
						'menu_order' => 0,
						'position' => 'normal',
						'style' => 'default',
						'label_placement' => 'top',
						'instruction_placement' => 'label',
						'hide_on_screen' => '',
						'active' => true,
						'description' => '',
					));
					
				}*/

				/*$current_user = wp_get_current_user();
        
				// create post object
				$post = array(
					'post_title'  => __( 'TM Empty '. $formname.' Form' ),
					'post_status' => 'publish',
					'post_author' => $current_user->ID,
					'post_type'   => $formslug,
					'post_content' => 'This is a test'
				);
				
				// insert the post into the database
				wp_insert_post( $post );*/
			}
		}

	}

}
add_action( 'init', 'create_tm_acf_form_cpt', 0 );