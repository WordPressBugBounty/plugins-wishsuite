<?php

namespace WishSuite\Admin;

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

use WishSuite\Manage_Data;
use WP_List_Table;


if( !class_exists('WP_List_Table') ){
	require_once ABSPATH."wp-admin/includes/class-wp-list-table.php";
}

/**
 * Wishlist Table List Manager
*/
class Wishlist_Table_List extends WP_List_Table
{
	private $user_id;
	private $user;
	private $view_type;
	private $limit;
	private $offset;
	private $order_by;
	private $order;

	public function __construct() {

        parent::__construct(
            array(
                'singular' => 'wishlist',
                'plural'   => 'wishlists',
                'ajax'     => false
            )
        );

    }
	
	function set_data(){
		$this->user_id = isset($_GET['user_id']) ? sanitize_text_field($_GET['user_id']) : '';
		$this->user = get_userdata($this->user_id);
		$current_page = !empty( $_GET['paged'] ) ? absint($_GET['paged']) : 1;
		$page = $current_page - 1;
		$this->limit = 20;
		$this->offset = $page * $this->limit;
        $this->order_by = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
        $this->order = !empty($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC';
        $data = [];

        $query_args = [
            'limit'=>$this->limit,
            'offset'=>$this->offset,
            'orderby'=>$this->order_by,
            'order'=>$this->order
        ];
        if($this->user_id === '') {
            $data = Manage_Data::instance()->getWishlist( $query_args )['items'];
        }
        if($this->user_id && $this->user_id !== '') {
            $query_args = array_merge($query_args, ['user_id'=>$this->user_id]);
            $data = Manage_Data::instance()->getWishlistByUserId( $query_args )['items'];
        }
        $this->items= $data;
	}
    

    /**
	 * Gets the list of views available on this table.
	 *
	 * The format is an associative array:
	 * - `'id' => 'link'`
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	protected function get_views() {
        $current_url = admin_url('admin.php?page=wishsuite');
        $view = !empty($_GET['view']) ? $_GET['view'] : '';
		return [
            'all' => sprintf('<a href="%1$s" %2$s >%3$s</a>',
                esc_url(add_query_arg(['view'=> 'list'], $current_url)),
                $view === 'list' ? 'class="current"' : '',
                esc_html__('All', 'wishsuite'),
            ),
            'user' => sprintf('<a href="%1$s" %2$s >%3$s</a>',
                esc_url(add_query_arg(['view'=> 'user'], $current_url)),
                $view === 'user' ? 'class="current"' : '',
                esc_html__('View by User', 'wishsuite'),
            ),
        ];
	}

    function extra_tablenav( $which ){
        if('top' == $which) {
            $user_id = !empty( $_GET['user_id'] ) ? sanitize_text_field($_GET['user_id']) : '';
            $query_args = [
                'limit'=> 1000,
                'offset'=>0,
                'orderby'=>$this->order_by,
                'order'=>$this->order
            ];
            $data = Manage_Data::instance()->getWishlistUsers( $query_args )['items'];
            echo '<div class="actions alignleft">';
            echo '<select name="user_id">';
            printf('<option value="%1$s" %2$s>%3$s</option>',
                '',
                $user_id === '' ? 'selected' : '',
                esc_html__('All User Items')
            );
            foreach ($data as $item) {
                $user = get_userdata($item['user_id']);
                $name = $user ? $user->display_name : __('Guest' . $item['user_id'], 'wishsuite');
                printf('<option value="%1$s" %2$s>%3$s</option>',
                    esc_attr($item['user_id']),
                    $user_id === $item['user_id'] ? 'selected' : '',
                    esc_html(ucfirst($name)) . " (". esc_html($item['product_count']) .")"
                );
            }
            echo '</select>';
            submit_button( esc_html__('Filter', 'wishsuite'), 'button', 'submit', false );
            echo '</div>';
        }
    }

	/**
     * Define check box for bulk action (each row)
     * @param  $item
     * @return string
    */
    public function column_cb($item){
        return "<input type='checkbox' name='wishlist_id[]' value='".esc_attr($item['id'])."' />";
    }
    public function column_product_image($item){
        $product = wc_get_product($item['product_id']);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            return sprintf('<a href="%1$s" aria-label="%2$s">%3$s</a>',
                esc_url($product->get_permalink()),
                esc_attr($product->get_name()),
                wp_kses_post($product->get_image('thumbnail'))
            );
        }
        return '';
    }
    public function column_product_name($item){
        $product = wc_get_product($item['product_id']);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            return sprintf('<a class="row-title" href="%1$s">%2$s</a>',
                esc_url($product->get_permalink()),
                esc_html($product->get_name())
            );
        }
        return '';
    }
    public function column_product_price($item){
        $product = wc_get_product($item['product_id']);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            return wp_kses_post($product->get_price_html());
        }
        return 'Invalid Product.';
    }
    public function column_date_added($item){
        $product = wc_get_product($item['product_id']);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            return esc_html(date_format(date_create($item['date_added']), "F j, Y \\a\\t h:i A"));
        }
        return '';
    }
    public function column_product_count($item){
        $product = wc_get_product($item['product_id']);
        if ( $product && is_a( $product, 'WC_Product' ) ) {
            return esc_html($item['product_count']);
        }
        return '';
    }
    public function column_author($item){
        $user = get_userdata($item['user_id']);
        $name = $user ? $user->display_name : __('Guest ' . $item['user_id'], 'wishsuite');
        return sprintf('<a class="row-title" href="%1$s">%2$s %3$s</a>',
            esc_url(add_query_arg( 'user_id', $item['user_id'], admin_url('admin.php?page=wishsuite') )),
            wp_kses_post(get_avatar($item['user_id'], 40, '', $name)),
            esc_html($name)
        );
    }

	function get_columns(){
        if($this->user_id && $this->user_id !== '') {
            return [
                'product_image' => esc_html__( 'Image', 'wishsuite' ),
                'product_name' => esc_html__( 'Product Name', 'wishsuite' ),
                'product_price' => esc_html__( 'Price', 'wishsuite' ),
                'date_added' => esc_html__( 'Date Added
                ', 'wishsuite' ),
            ];
        }
        return [
            'product_image' => esc_html__( 'Image', 'wishsuite' ),
            'product_name' => esc_html__( 'Product Name', 'wishsuite' ),
            'product_price' => esc_html__( 'Price', 'wishsuite' ),
            'product_count' => esc_html__( 'Items in Wishlist
            ', 'wishsuite' ),
        ];
	}

	function prepare_items(){
		$this->_column_headers = array($this->get_columns(),array('id'),$this->get_sortable_columns());
        $total_items = 0;

        $query_args = [
            'limit'=>$this->limit,
            'offset'=>$this->offset,
            'orderby'=>$this->order_by,
            'order'=>$this->order
        ];

        if($this->user_id === '') {
            $result = Manage_Data::instance()->getWishlist( $query_args );
            $total_items = $result['total_items'];
        }
        if($this->user_id && $this->user_id !== '' ) {
            $query_args = array_merge($query_args, ['user_id'=>$this->user_id]);
            $result = Manage_Data::instance()->getWishlistByUserId( $query_args );
            $total_items = $result['total_items'];
        }

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $this->limit
        ] );
	}

}