<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Auth {

    public function __construct() {
        add_action( 'wp_ajax_nopriv_iqr_login', array( $this, 'handle_login' ) );
        add_action( 'wp_ajax_iqr_logout', array( $this, 'handle_logout' ) );
    }

    public function handle_login() {
        check_ajax_referer( 'iqr_login_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';

        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => 'El usuario y la contraseña son obligatorios.' ) );
        }

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon( $credentials, is_ssl() );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => 'Credenciales inválidas.' ) );
        }

        if ( ! $this->user_has_access( $user ) ) {
            wp_logout();
            wp_send_json_error( array( 'message' => 'No tenés permiso para acceder al sistema de inventario.' ) );
        }

        wp_send_json_success( array(
            'message'  => 'Inicio de sesión exitoso.',
            'redirect' => admin_url( 'admin.php?page=inventario-qr' ),
        ) );
    }

    public function handle_logout() {
        check_ajax_referer( 'iqr_logout_nonce', 'nonce' );
        wp_logout();
        wp_send_json_success( array(
            'redirect' => admin_url( 'admin.php?page=inventario-qr' ),
        ) );
    }

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
