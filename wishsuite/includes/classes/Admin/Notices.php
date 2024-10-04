<?php
namespace WishSuite\Admin;
/**
 * Notices handlers class
 */
class Notices {

    /**
     * Run the Notices
     *
     * @return void
     */
    public function notice() {
        $this->all_notices();
    }

    /**
     * Add Notices
     */
    public function all_notices() {

        // Notice for WooCommerce
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_woocommerce_plugin' ] );
            return;
        }

    }

    /**
    * [is_plugins_active] Check Plugin is Installed or not
    * @param  [string]  $pl_file_path plugin file path
    * @return boolean  true|false
    */
    public function is_plugins_active( $pl_file_path = NULL ){
        $installed_plugins_list = get_plugins();
        return isset( $installed_plugins_list[$pl_file_path] );
    }

    /**
     * [admin_notice_missing_woocommerce_plugin] Admin Notice For missing WooCommerce
     * @return [void]
     */
    public function admin_notice_missing_woocommerce_plugin(){
        $woocommerce = 'woocommerce/woocommerce.php';
        if( $this->is_plugins_active( $woocommerce ) ) {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $woocommerce . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $woocommerce );
            $message = sprintf( __( '%1$sWishSuite%2$s requires %1$s"WooCommerce"%2$s plugin to be active. Please activate WooCommerce to continue.', 'wishsuite' ), '<strong>', '</strong>');
            $button_text = __( 'Activate WooCommerce', 'wishsuite' );
        } else {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
            $message = sprintf( __( '%1$sWishSuite%2$s requires %1$s"WooCommerce"%2$s plugin to be installed and activated. Please install WooCommerce to continue.', 'wishsuite' ), '<strong>', '</strong>' );
            $button_text = __( 'Install WooCommerce', 'wishsuite' );
        }
        $button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
        printf( '<div class="error"><p>%1$s</p>%2$s</div>', __( $message ), $button );
    }
    

}