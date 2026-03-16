<?php
namespace WishSuite;
/**
 * Installer class
 */
class Installer {

    /**
     * Run the installer
     *
     * @return void
     */
    public function run() {
        $this->add_version();
        $this->create_tables();
        $this->create_page();
        $this->add_redirection_flag();
    }

    /**
     * Add time and version on DB
     */
    public function add_version() {
        $installed = get_option( 'wishsuite_installed' );

        if ( ! $installed ) {
            update_option( 'wishsuite_installed', time() );
        }

        update_option( 'wishsuite_version', WISHSUITE_VERSION );
    }

    /**
     * [add_redirection_flag] redirection flug
     */
    public function add_redirection_flag(){
        add_option( 'wishsuite_do_activation_redirect', true );
    }

    /**
     * [create_tables]
     * @return [void]
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            $charset_collate = $wpdb->get_charset_collate();
        }

        $schema = "CREATE TABLE `{$wpdb->prefix}wishsuite_list` (
          `id` bigint( 20 ) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` bigint( 20 ) NULL DEFAULT NULL,
          `product_id` bigint(20) NULL DEFAULT NULL,
          `quantity` int(11) NULL DEFAULT NULL,
          `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_user_id` (`user_id`),
          KEY `idx_product_id` (`product_id`),
          KEY `idx_user_product` (`user_id`, `product_id`)
        ) $charset_collate";

        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( $schema );

        $this->maybe_add_indexes();
    }

    /**
     * Add missing indexes to the wishlist table via direct SQL.
     * Handles existing installs where dbDelta may not have created indexes.
     *
     * @return void
     */
    public function maybe_add_indexes() {
        global $wpdb;
        $table = $wpdb->prefix . 'wishsuite_list';

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return;
        }

        $existing = $wpdb->get_results( "SHOW INDEX FROM `{$table}`", ARRAY_A );
        $existing_names = array_column( $existing, 'Key_name' );

        $indexes = [
            'idx_user_id'      => '(`user_id`)',
            'idx_product_id'   => '(`product_id`)',
            'idx_user_product' => '(`user_id`, `product_id`)',
        ];

        foreach ( $indexes as $name => $columns ) {
            if ( ! in_array( $name, $existing_names, true ) ) {
                $wpdb->query( "ALTER TABLE `{$table}` ADD INDEX `{$name}` {$columns}" );
            }
        }
    }

    /**
     * [create_page] Create page
     * @return [void]
     */
    private function create_page() {
        if ( function_exists( 'wc_create_page' ) ) {
            $create_page_id = wc_create_page(
                sanitize_title_with_dashes( _x( 'wishsuite', 'page_slug', 'wishsuite' ) ),
                '',
                __( 'WishSuite', 'wishsuite' ),
                '<!-- wp:shortcode -->[wishsuite_table]<!-- /wp:shortcode -->'
            );
            if( $create_page_id ){
                wishsuite_update_option( 'wishsuite_table_settings_tabs','wishlist_page', $create_page_id );
            }
        }
    }

    /**
     * [drop_tables] Delete table
     * @return [void]
     */
    public static function drop_tables() {
        global $wpdb;
        $tables = [
            "{$wpdb->prefix}wishsuite_list",
        ];
        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
        }
    }


}