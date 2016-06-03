<?php
/*
 * Plugin Name: ITTI Carousel WITH TABLE
 * Plugin URI: http://studioitti.com/
 * Description: Carousel plugin
 * Author: Ervin Drenchev
 * Version: 1.0
 * Author URI: http://drenchev.com/
 */
class PluginTable {
	
	private $jal_db_version = '1.0';
	
	function __construct() {
		register_activation_hook( __FILE__, array($this, 'jal_install') );
		register_activation_hook( __FILE__, array($this, 'jal_install_data') );
	}
	
	public function jal_install() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'itti_carousel';
		
		$charset_collate = $wpdb->get_charset_collate ();
		
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta ( $sql );
		
		add_option( 'jal_db_version', $this->jal_db_version );
	}
	
	public function jal_install_data() {
		global $wpdb;
	
		$welcome_name = 'Mr. WordPress';
		$welcome_text = 'Congratulations, you just completed the installation!';
	
		$table_name = $wpdb->prefix . 'itti_carousel';
	
		$wpdb->insert(
				$table_name,
				array(
						'time' => current_time( 'mysql' ),
						'name' => $welcome_name,
						'text' => $welcome_text,
				)
				);
	}
}

new PluginTable();