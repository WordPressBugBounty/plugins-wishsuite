<?php
/*
 * Wishsuite Uninstall plugin
 * Uninstalling Wishsuite deletes tables, and options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

if( ! isset( get_option('woolentor_others_tabs')['wishlist'] ) ){

	include_once dirname( __FILE__ ) . '/includes/classes/Installer.php';

	function wishsuite_uninstall(){

		if( !empty( get_option('wishsuite_version', '') ) ){

			// Delete page created for this plugin
			$option_data = get_option( 'wishsuite_table_settings_tabs' );
			if( isset($option_data['wishlist_page'])){
				wp_delete_post( $option_data['wishlist_page'], true );
			}

			// Option delete
			delete_option( 'wishsuite_version' );
			delete_option( 'wishsuite_settings_tabs' );
			delete_option( 'wishsuite_table_settings_tabs' );
			delete_option( 'wishsuite_style_settings_tabs' );

			// Delete table
			if( class_exists( '\WishSuite\Installer' ) ){
				\WishSuite\Installer::drop_tables();
			}

		}
		
	}
	
	wishsuite_uninstall();
}