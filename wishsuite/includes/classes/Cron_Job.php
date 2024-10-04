<?php
namespace WishSuite;
/**
 * Cron Job class
 */
class Cron_Job {

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Ajax]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Initialize the class
     */
    private function __construct() {
        if(wishsuite_get_option( 'delete_guest_user_wishlist', 'wishsuite_general_tabs', 'off' ) === 'on') {
            add_action( 'wishsuite_remove_guest_old_wishlist_items', [$this, 'remove_guest_old_wishlist_items'] );
            $this->scheduled_remove_wishlist();
        }
    }
    public static function remove_guest_old_wishlist_items () {
        global $wpdb;
        $delete_guest_user_wishlist_days = wishsuite_get_option( 'delete_guest_user_wishlist_days', 'wishsuite_general_tabs' );
        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, id
                FROM {$wpdb->prefix}wishsuite_list
                WHERE date_added < DATE_SUB(CURDATE(), INTERVAL %d DAY)",
                (int) $delete_guest_user_wishlist_days
            )
        );
        if($result) {
            foreach ($result as $item) {
                if(!get_userdata($item->user_id)) {
                    $wpdb->delete(
                        $wpdb->prefix . 'wishsuite_list',
                        ['id' => (int) $item->id],
                        ['%d']
                    );
                }
            }
        }
    }
    function scheduled_remove_wishlist() {
		if ( ! wp_next_scheduled( 'wishsuite_remove_guest_old_wishlist_items' ) ) {
            $time = strtotime( '00:00 today' );
			wp_schedule_event( $time, 'daily', 'wishsuite_remove_guest_old_wishlist_items' );
		}
	}

}