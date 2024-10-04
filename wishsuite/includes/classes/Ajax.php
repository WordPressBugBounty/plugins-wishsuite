<?php
namespace WishSuite;
/**
 * Ajax handlers class
 */
class Ajax {

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

        // Add Ajax Callback
        add_action( 'wp_ajax_wishsuite_add_to_list', [ $this, 'add_to_wishlist' ] );
        add_action( 'wp_ajax_nopriv_wishsuite_add_to_list', [ $this, 'add_to_wishlist' ] );

        // Remove Ajax Callback
        add_action( 'wp_ajax_wishsuite_remove_from_list', [ $this, 'remove_wishlist' ] );
        add_action( 'wp_ajax_nopriv_wishsuite_remove_from_list', [ $this, 'remove_wishlist' ] );

        // Variation Quick cart Form Ajax Callback
        add_action( 'wp_ajax_wishsuite_quick_variation_form', [ $this, 'variation_form_html' ] );
        add_action( 'wp_ajax_nopriv_wishsuite_quick_variation_form', [ $this, 'variation_form_html' ] );

        // For Add to cart
        add_action( 'wp_ajax_wishsuite_insert_to_cart', [ $this, 'insert_to_cart' ] );
        add_action( 'wp_ajax_nopriv_wishsuite_insert_to_cart', [ $this, 'insert_to_cart' ] );

    }

    /**
     * [add_to_wishlist] Product add ajax callback
     */
    public function add_to_wishlist(){
        check_ajax_referer('wishsuite_nonce', 'nonce');
        $id = sanitize_text_field( $_GET['id'] );
        $product = wc_get_product($id);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            $inserted = \WishSuite\Frontend\Manage_Wishlist::instance()->add_product( $id );
            if ( ! $inserted ) {
                wp_send_json_error([
                    'message' => __( 'Product do not add!', 'wishsuite' )
                ]);
            }else{
                $message = wishsuite_get_option( 'success_added_notification_text', 'wishsuite_general_tabs', 'Product successfully added!' );
                $message = str_replace('{product_name}',$product->get_name(), $message);
                wp_send_json_success([
                    'item_count' => count( \WishSuite\Frontend\Manage_Wishlist::instance()->get_products_data() ),
                    'message' => $message,
                ]);
            }
        } else {
            wp_send_json_error([
                'message' => __( 'Invalid product, not added to the wishlist!', 'wishsuite' )
            ]);
        }

    }

    /**
     * [remove_wishlist] Product delete ajax callback
     * @return [void]
     */
    public function remove_wishlist(){
        check_ajax_referer('wishsuite_nonce', 'nonce');
        $id = sanitize_text_field( $_GET['id'] );
        $current_page = sanitize_text_field( $_GET['current_page'] );
        $deleted = \WishSuite\Frontend\Manage_Wishlist::instance()->remove_product( $id );
        if ( ! $deleted ) {
            wp_send_json_success([
                'message' => __( 'Product do not delete!', 'wishsuite' )
            ]);
        }else{
            $product_per_page = wishsuite_get_option( 'wishlist_product_per_page', 'wishsuite_table_settings_tabs', 20 );
            $total_items = count( \WishSuite\Frontend\Manage_Wishlist::instance()->get_products_data() );
            $current_page_items = count( \WishSuite\Frontend\Manage_Wishlist::instance()->get_products_data($product_per_page, $current_page) );
            $total_pages = ceil($total_items / $product_per_page);

            $response = [
                'item_count' => (int) $total_items,
                'message' => __( 'Product successfully deleted!', 'wishsuite' ),
                'html' => \WishSuite\Frontend\Shortcode::instance()->table_shortcode([]),
                'total_pages' => (int) $total_pages,
                'current_page' => $current_page_items <= 0 && $current_page > 1 ? $current_page - 1 : $current_page,
            ];
            if($current_page_items <= 0 && $current_page > 1) {
                array_push($response, ['current_page' => $current_page - 1]);
            }
            wp_send_json_success($response);
        }

    }

    /**
     * [variation_form_html]
     * @param  boolean $id product id
     * @return [void]
     */
    public function variation_form_html( $id = false ){
        check_ajax_referer('wishsuite_nonce', 'nonce');
        if( isset( $_POST['id'] ) ) {
            $id = sanitize_text_field( (int) $_POST['id'] );
        }
        if( ! $id || ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        global $post;

        $args = array( 
            'post_type' => 'product',
            'post__in' => array( $id ) 
        );

        $get_posts = get_posts( $args );

        foreach( $get_posts as $post ) :
            setup_postdata( $post );
            woocommerce_template_single_add_to_cart();
        endforeach; 

        wp_reset_postdata(); 

        wp_die();

    }

    /**
     * [insert_to_cart] Insert add to cart
     * @return [JSON]
     */
    public function insert_to_cart(){
        check_ajax_referer('wishsuite_nonce', 'nonce');
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( ! isset( $_POST['product_id'] ) ) {
            return;
        }

        $product_id         = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
        $quantity           = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
        $variation_id       = !empty( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $variations         = !empty( $_POST['variations'] ) ? array_map( 'sanitize_text_field', $_POST['variations'] ) : array();
        $passed_validation  = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );
        $product_status     = get_post_status( $product_id );

        if ( $passed_validation && \WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) && 'publish' === $product_status ) {
            do_action( 'woocommerce_ajax_added_to_cart', $product_id );
            if ( 'yes' === get_option('woocommerce_cart_redirect_after_add') ) {
                wc_add_to_cart_message( array( $product_id => $quantity ), true );
            }
            \WC_AJAX::get_refreshed_fragments();
        } else {
            $data = array(
                'error' => true,
                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
            );
            echo wp_send_json( $data );
        }
        wp_send_json_success();
        
    }


}