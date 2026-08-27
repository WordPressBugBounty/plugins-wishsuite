<?php
namespace WishSuite\Admin;
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Dashboard handlers class
 */
class Dashboard {

    /**
     * Menu capability
     */
    const MENU_CAPABILITY = 'manage_options';

    /**
     * Parent Menu Page Slug
     */
    const MENU_PAGE_SLUG = 'wishsuite';

    /**
     * [$parent_menu_hook] Parent Menu Hook
     * @var string
     */
    static $parent_menu_hook = '';

    /**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Admin]
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

        Admin_Fields::instance();

        if( is_plugin_active('woolentor-addons/woolentor_addons_elementor.php') && isset( get_option('woolentor_others_tabs')['wishlist'] ) && get_option('woolentor_others_tabs')['wishlist'] == 'on' ){
            add_action( 'admin_menu', [ $this, 'add_menu_if_woolentor' ], 226 );
        }else{
            add_action( 'admin_menu', [ $this, 'add_menu' ], 20 );
        }

        add_filter('plugin_action_links_'.WISHSUITE_BASE, [ $this, 'action_links' ] );

        // Add a post display state for special WishSuite page.
        add_filter( 'display_post_states', [ $this, 'add_display_post_states' ], 10, 2 );

        // Redirect Option page
        $this->redirect_option_page();

        // Recommended plugin
        add_action('init', [ $this, 'plugin_recommendations' ]);

    }

    /**
    * [action_links] add plugin action link
    * @param  [array] $links default plugin action link
    * @return [array] plugin action link
    */
    public function action_links( $links ) {

        if ( ! current_user_can( self::MENU_CAPABILITY ) ) {
            return $links;
        }

        $settings_link = '<a href="'.admin_url( 'admin.php?page='.self::MENU_PAGE_SLUG ).'">'.esc_html__( 'Settings', 'wishsuite' ).'</a>'; 

        array_unshift( $links, $settings_link );

        return $links; 
    }

    /**
     * [add_menu_if_woolentor] Admin Menu If WooLentor active
     */
    public function add_menu_if_woolentor(){

        self::$parent_menu_hook = add_submenu_page(
            'woolentor_page',
            esc_html__( 'Wishlist', 'wishsuite' ),
            esc_html__( 'Wishlist', 'wishsuite' ),
            'manage_options',
            self::MENU_PAGE_SLUG,
            [ $this,'dashboard' ]
        );

        add_action( 'load-' . self::$parent_menu_hook, [ $this, 'init_hooks'] );

    }

    /**
     * [add_menu] Admin Menu
     */
    public function add_menu(){

        global $submenu;

        self::$parent_menu_hook = add_menu_page(
            esc_html__( 'WishSuite', 'wishsuite' ), 
            esc_html__( 'WishSuite', 'wishsuite' ), 
            self::MENU_CAPABILITY,
            self::MENU_PAGE_SLUG,
            [ $this,'dashboard' ],
            'dashicons-heart',
            59
        );

        if ( current_user_can( self::MENU_CAPABILITY ) ) {

            foreach ( $this->sub_menu_nav() as $menukey => $menu ) {

                $page_slug = !empty( $menu['page_slug'] ) ? $menu['page_slug'] : self::MENU_PAGE_SLUG;

                $submenu[ self::MENU_PAGE_SLUG ][] = array(
                    esc_html( $menu['title'] ),
                    self::MENU_CAPABILITY,
                    'admin.php?page='.$page_slug.'#'.$menukey,
                );

            }

        }

        add_action( 'load-' . self::$parent_menu_hook, [ $this, 'init_hooks'] );
        

    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * [enqueue_scripts] Add Scripts Base Menu Slug
     * @param  [string] $hook
     * @return [void]
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'wishsuite-admin' );
        wp_enqueue_script( 'wishsuite-admin' );
    }

    /**
     * [dashboard] Dashboard plugin page
     * @return [HTML]
     */
    public function dashboard(){
        Admin_Fields::instance()->plugin_page();
    }

    /**
     * [plugin_recommendations]
     * @return [void]
     */
    public function plugin_recommendations(){

        $get_instance = Recommended_Plugins::instance( 
            array( 
                'text_domain'       => 'wishsuite', 
                'parent_menu_slug'  => self::MENU_PAGE_SLUG, 
                'menu_capability'   => self::MENU_CAPABILITY, 
                'menu_page_slug'    => 'wishsuite_recommendations',
                'priority'          => 25,
                'assets_url'        => WISHSUITE_ASSETS,
                'hook_suffix'       => 'wishsuite_page_wishsuite_recommendations'
            )
        );

        $get_instance->add_new_tab( array(

            'title' => esc_html__( 'Recommended Plugins', 'wishsuite' ),
            'active' => true,
            'plugins' => array(

                array(
                    'slug'      => 'support-genix-lite',
                    'location'  => 'support-genix-lite.php',
                    'name'      => esc_html__( 'Support Genix – Helpdesk, AI Chatbot, Knowledge Base & Customer Support Ticketing System', 'wishsuite' )
                ),
                array(
                    'slug'      => 'hashbar-wp-notification-bar',
                    'location'  => 'init.php',
                    'name'      => esc_html__( 'HashBar – Announcement, Notification Bar & Popup Campaign', 'wishsuite' )
                ),
                array(
                    'slug'      => 'wp-plugin-manager',
                    'location'  => 'plugin-main.php',
                    'name'      => esc_html__( 'WP Plugin Manager – Deactivate plugins per page', 'wishsuite' )
                ),
                array(
                    'slug'      => 'ht-contactform',
                    'location'  => 'contact-form-widget-elementor.php',
                    'name'      => esc_html__( 'HT Contact Form – Drag & Drop Form Builder for WordPress', 'wishsuite' )
                ),
                array(
                    'slug'      => 'cookieray',
                    'location'  => 'cookieray.php',
                    'name'      => esc_html__( 'CookieRay – Cookie Banner for Cookie Consent (GDPR/CCPA Compliant)', 'wishsuite' )
                ),
                array(
                    'slug'      => 'kelune-crm',
                    'location'  => 'kelune-crm.php',
                    'name'      => esc_html__( 'Kelune CRM – Contact Management, Email Marketing, Newsletter & Marketing Automation', 'wishsuite' )
                ),
            )

        ) );

        $get_instance->add_new_tab( array(
            'title' => esc_html__( 'WooCommerce', 'wishsuite' ),
            'plugins' => array(
                array(
                    'slug'      => 'woolentor-addons',
                    'location'  => 'woolentor_addons_elementor.php',
                    'name'      => esc_html__( 'ShopLentor – All-in-One WooCommerce Growth & Store Enhancement Plugin', 'wishsuite' )
                ),
                array(
                    'slug'      => 'whols',
                    'location'  => 'whols.php',
                    'name'      => esc_html__( 'Whols – Wholesale Prices and B2B Store Solution for WooCommerce', 'wishsuite' )
                ),
                array(
                    'slug'      => 'recurio',
                    'location'  => 'recurio.php',
                    'name'      => esc_html__( 'Recurio – Ultimate Subscription for WooCommerce', 'wishsuite' )
                ),
            )
        ) );

        $get_instance->add_new_tab(array(
            'title' => esc_html__( 'Popular', 'wishsuite' ),
            'plugins' => array(
                array(
                    'slug'      => 'ht-mega-for-elementor',
                    'location'  => 'htmega_addons_elementor.php',
                    'name'      => esc_html__( 'HT Mega Addons for Elementor – Elementor Widgets & Template Builder', 'wishsuite' )
                ),
                array(
                    'slug'      => 'wp-plugin-manager',
                    'location'  => 'plugin-main.php',
                    'name'      => esc_html__( 'WP Plugin Manager – Deactivate plugins per page', 'wishsuite' )
                ),
                array(
                    'slug'      => 'ht-easy-google-analytics',
                    'location'  => 'ht-easy-google-analytics.php',
                    'name'      => esc_html__( 'HT Easy GA4 – Google Analytics WordPress Plugin', 'wishsuite' )
                ),
                array(
                    'slug'      => 'cookieray',
                    'location'  => 'cookieray.php',
                    'name'      => esc_html__( 'CookieRay – Cookie Banner for Cookie Consent (GDPR/CCPA Compliant)', 'wishsuite' )
                ),
                array(
                    'slug'      => 'insert-headers-and-footers-script',
                    'location'  => 'init.php',
                    'name'      => esc_html__( 'Insert Headers and Footers Code – HT Script', 'wishsuite' )
                ),
                array(
                    'slug'      => 'pixelavo',
                    'location'  => 'pixelavo.php',
                    'name'      => esc_html__( 'Pixelavo – Server Side Tracking & Pixel + AI Ads Tools', 'wishsuite' )
                ),
                array(
                    'slug'      => 'courseglade-lms',
                    'location'  => 'courseglade-lms.php',
                    'name'      => esc_html__( 'CourseGlade LMS – Online Course & eLearning Platform', 'wishsuite' )
                ),
            )
        ));


    }

    /**
     * [sub_menu_nav]
     * @return [array]
     */
    public function sub_menu_nav() {

        $submenu = [
            'wishsuite_general_tabs' => [
                'title'     => esc_html__( 'Settings', 'wishsuite' ),
                'subtitle'  => esc_html__( 'Settings', 'wishsuite' ),
                'icon'      => '',
                'class'     => '',
            ],
            'wishsuite_wishlist_tabs' => [
                'title'     => esc_html__( 'Wishlist Items', 'wishsuite' ),
                'subtitle'  => esc_html__( 'Wishlist Items', 'wishsuite' ),
                'icon'      => '',
                'class'     => '',
            ],
        ];

        return apply_filters( 'wishsuite_dashboard_submenu', $submenu );

    }

    /**
     * [redirect_option_page] After Active the plugin then redirect to option page
     * @return [void]
     */
    public function redirect_option_page() {
        if ( get_option( 'wishsuite_do_activation_redirect', FALSE ) ) {
            delete_option('wishsuite_do_activation_redirect');
            if( !isset( $_GET['activate-multi'] ) ){
                wp_redirect( admin_url( "admin.php?page=".self::MENU_PAGE_SLUG ) );
            }
        }
    }

    /**
     * Add a post display state for special WishSuite page in the page list table.
     *
     * @param array   $post_states An array of post display states.
     * @param WP_Post $post  The current post object.
     */
    public function add_display_post_states( $post_states, $post ){
        if ( (int)wishsuite_get_option( 'wishlist_page', 'wishsuite_table_settings_tabs' ) === $post->ID ) {
            $post_states['wishsuite_page_for_wishlist_table'] = __( 'WishSuite', 'wishsuite' );
        }
        return $post_states;
    }
    

}