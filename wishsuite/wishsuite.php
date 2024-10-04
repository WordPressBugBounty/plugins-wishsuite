<?php
/**
 * Plugin Name: WishSuite - Wishlist for WooCommerce
 * Description: WooCommerce Product wishlist plugin
 * Plugin URI: https://hasthemes.com/plugins/
 * Author: HasThemes
 * Author URI: https://hasthemes.com/
 * Version: 1.4.3
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wishsuite
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * WC tested up to: 9.2.2
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Main Class
 */
final class WishSuite_Base{

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.4.3';

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Base]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * [__construct] Class Constructor
     */
    private function __construct(){
        $this->define_constants();
        $this->includes();
        register_activation_hook( WISHSUITE_FILE, [ $this, 'activate' ] );
        if( empty( get_option('wishsuite_version', '') ) ){
            $this->activate();
        }
        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        
        // Compatible With WooCommerce Custom Order Tables
        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WISHSUITE_FILE, true );
            }
        } );
    }


    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'WISHSUITE_VERSION', self::version );
        define( 'WISHSUITE_FILE', __FILE__ );
        define( 'WISHSUITE_PATH', __DIR__ );
        define( 'WISHSUITE_URL', plugins_url( '', WISHSUITE_FILE ) );
        define( 'WISHSUITE_DIR', plugin_dir_path( WISHSUITE_FILE ) );
        define( 'WISHSUITE_ASSETS', WISHSUITE_URL . '/assets' );
        define( 'WISHSUITE_BASE', plugin_basename( WISHSUITE_FILE ) );
    }

    /**
     * [i18n] Load text domain
     * @return [void]
     */
    public function i18n() {
        load_plugin_textdomain( 'wishsuite', false, dirname( plugin_basename( WISHSUITE_FILE ) ) . '/languages/' );
    }

    /**
     * [includes] Load file
     * @return [void]
     */
    public function includes(){
        require_once WISHSUITE_PATH . '/vendor/autoload.php';
        if ( ! function_exists('is_plugin_active') ){ include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {

        WishSuite\Cron_Job::instance();
        WishSuite\Assets::instance();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            WishSuite\Ajax::instance();
        }

        if ( is_admin() ) {
            $this->admin_notices();
            WishSuite\Admin::instance();
        }
        WishSuite\Frontend::instance();

        // add image size
        $this->set_image_size();

        // let's filter the woocommerce image size
        add_filter( 'woocommerce_get_image_size_wishsuite-image', [ $this, 'wc_image_filter_size' ], 10, 1 );
        

    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate() {
        $installer = new WishSuite\Installer();
        $installer->run();
    }

    /**
     * Admin Notices
     * @return void
     */
    public function admin_notices() {
        $notice = new WishSuite\Admin\Notices();
        $notice->notice();
    }

    /**
     * [set_image_size] Set Image Size
     */
    public function set_image_size(){

        $image_dimention = wishsuite_get_option( 'image_size', 'wishsuite_table_settings_tabs', array( 'width'=>80,'height'=>80 ) );
        if( isset( $image_dimention ) && is_array( $image_dimention ) ){
            $hard_crop = !empty( wishsuite_get_option( 'hard_crop', 'wishsuite_table_settings_tabs' ) ) ? true : false;
            add_image_size( 'wishsuite-image', absint( $image_dimention['width'] ), absint( $image_dimention['height'] ), $hard_crop );
        }

    }

    /**
     * [wc_image_filter_size]
     * @return [array]
     */
    public function wc_image_filter_size(){

        $image_dimention = wishsuite_get_option( 'image_size', 'wishsuite_table_settings_tabs', array( 'width'=>80,'height'=>80 ) );
        $hard_crop = !empty( wishsuite_get_option( 'hard_crop', 'wishsuite_table_settings_tabs' ) ) ? true : false;

        if( isset( $image_dimention ) && is_array( $image_dimention ) ){
            return array(
                'width'  => isset( $image_dimention['width'] ) ? absint( $image_dimention['width'] ) : 80,
                'height' => isset( $image_dimention['height'] ) ? absint( $image_dimention['height'] ) : 80,
                'crop'   => isset( $hard_crop ) ? 1 : 0,
            );
        }
        
    }

}

/**
 * Initializes the main plugin
 *
 * @return WishSuite
 */
function WishSuite() {
    if( ! class_exists('Woolentor_WishSuite_Base') ){
        return WishSuite_Base::instance();
    }
}

// Get the plugin running.
WishSuite();
