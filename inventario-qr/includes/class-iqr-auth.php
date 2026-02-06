<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Auth {

    public function __construct() {
        add_action( 'wp_ajax_nopriv_iqr_login', array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_iqr_logout', array( $this, 'handle_logout' ) );
    }

    /**
     * Handle AJAX login requests.
     */
    public function handle_login() {
        check_ajax_referer( 'iqr_login_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';

        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( 'Username and password are required.', 'inventario-qr' ) ) );
        }

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon( $credentials, is_ssl() );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid credentials.', 'inventario-qr' ) ) );
        }

        if ( ! $this->user_has_access( $user ) ) {
            wp_logout();
            wp_send_json_error( array( 'message' => __( 'You do not have permission to access the inventory system.', 'inventario-qr' ) ) );
        }

        wp_send_json_success( array(
            'message'  => __( 'Login successful.', 'inventario-qr' ),
            'redirect' => admin_url( 'admin.php?page=inventario-qr' ),
        ) );
    }

    /**
     * Handle logout requests.
     */
    public function handle_logout() {
        check_ajax_referer( 'iqr_logout_nonce', 'nonce' );
        wp_logout();
        wp_send_json_success( array(
            'redirect' => admin_url( 'admin.php?page=inventario-qr' ),
        ) );
    }

    /**
     * Check if a user has access to the inventory system.
     */
    public function user_has_access( $user ) {
        $allowed_roles = array( 'administrator', 'iqr_admin' );
        foreach ( $allowed_roles as $role ) {
            if ( in_array( $role, (array) $user->roles, true ) ) {
                return true;
            }
        }
        return false;
    }
}
