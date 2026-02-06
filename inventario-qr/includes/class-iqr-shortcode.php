<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Shortcode {

    public function __construct() {
        add_shortcode( 'inventario_qr', array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
        add_action( 'wp_ajax_nopriv_iqr_get_section', array( $this, 'ajax_get_section_frontend' ) );
        add_action( 'wp_ajax_iqr_get_section_frontend', array( $this, 'ajax_get_section_frontend' ) );
    }

    /**
     * Enqueue assets only on pages that contain the shortcode.
     */
    public function maybe_enqueue_assets() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'inventario_qr' ) ) {
            return;
        }

        wp_enqueue_style(
            'iqr-styles',
            IQR_PLUGIN_URL . 'assets/css/styles.css',
            array(),
            IQR_VERSION
        );

        wp_enqueue_style(
            'iqr-frontend',
            IQR_PLUGIN_URL . 'assets/css/frontend.css',
            array( 'iqr-styles' ),
            IQR_VERSION
        );

        wp_enqueue_script(
            'iqr-app',
            IQR_PLUGIN_URL . 'assets/js/app.js',
            array( 'jquery' ),
            IQR_VERSION,
            true
        );

        $localize = array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'iqr_ajax_nonce' ),
            'loginNonce' => wp_create_nonce( 'iqr_login_nonce' ),
            'pluginUrl'  => IQR_PLUGIN_URL,
            'isLoggedIn' => is_user_logged_in(),
        );

        if ( is_user_logged_in() ) {
            $localize['logoutNonce'] = wp_create_nonce( 'iqr_logout_nonce' );
            $localize['currentUser'] = wp_get_current_user()->display_name;
            $localize['userRole']    = $this->get_user_iqr_role();
        }

        wp_localize_script( 'iqr-app', 'iqrData', $localize );
    }

    /**
     * Render the shortcode output.
     */
    public function render( $atts ) {
        if ( ! is_user_logged_in() ) {
            ob_start();
            include IQR_PLUGIN_DIR . 'templates/frontend-login.php';
            return ob_get_clean();
        }

        $user = wp_get_current_user();
        $allowed_roles = array( 'administrator', 'iqr_admin' );
        $has_access = false;
        foreach ( $allowed_roles as $role ) {
            if ( in_array( $role, (array) $user->roles, true ) ) {
                $has_access = true;
                break;
            }
        }

        if ( ! $has_access ) {
            return '<div class="iqr-no-access"><p>' . esc_html__( 'You do not have permission to access the inventory system.', 'inventario-qr' ) . '</p></div>';
        }

        ob_start();
        include IQR_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for frontend section loading.
     */
    public function ajax_get_section_frontend() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! current_user_can( 'iqr_manage_inventory' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'inventario-qr' ) ) );
        }

        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $allowed = array( 'qr', 'inventory', 'export-import', 'defaults', 'user' );

        if ( ! in_array( $section, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid section.', 'inventario-qr' ) ) );
        }

        $template = IQR_PLUGIN_DIR . 'templates/sections/' . $section . '.php';

        if ( ! file_exists( $template ) ) {
            wp_send_json_error( array( 'message' => __( 'Section not found.', 'inventario-qr' ) ) );
        }

        ob_start();
        include $template;
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * Get the current user's role label.
     */
    private function get_user_iqr_role() {
        $user = wp_get_current_user();
        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            return __( 'Administrator', 'inventario-qr' );
        }
        if ( in_array( 'iqr_admin', (array) $user->roles, true ) ) {
            return __( 'Inventario Admin', 'inventario-qr' );
        }
        return __( 'User', 'inventario-qr' );
    }
}
