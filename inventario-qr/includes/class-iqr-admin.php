<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_iqr_get_section', array( $this, 'ajax_get_section' ) );
    }

    /**
     * Register admin menu pages.
     */
    public function add_menu_pages() {
        add_menu_page(
            __( 'Inventario QR', 'inventario-qr' ),
            __( 'Inventario QR', 'inventario-qr' ),
            'iqr_manage_inventory',
            'inventario-qr',
            array( $this, 'render_dashboard' ),
            'dashicons-screenoptions',
            30
        );
    }

    /**
     * Enqueue CSS and JS assets.
     */
    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_inventario-qr' !== $hook ) {
            return;
        }

        // Hide WP admin sidebar and header for a full-screen app experience.
        wp_enqueue_style(
            'iqr-styles',
            IQR_PLUGIN_URL . 'assets/css/styles.css',
            array(),
            IQR_VERSION
        );

        wp_enqueue_script(
            'iqr-app',
            IQR_PLUGIN_URL . 'assets/js/app.js',
            array( 'jquery' ),
            IQR_VERSION,
            true
        );

        wp_localize_script( 'iqr-app', 'iqrData', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'iqr_ajax_nonce' ),
            'logoutNonce' => wp_create_nonce( 'iqr_logout_nonce' ),
            'pluginUrl'   => IQR_PLUGIN_URL,
            'currentUser' => wp_get_current_user()->display_name,
            'userRole'    => $this->get_user_iqr_role(),
        ) );
    }

    /**
     * Get the current user's IQR role label.
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

    /**
     * Render the main dashboard.
     */
    public function render_dashboard() {
        include IQR_PLUGIN_DIR . 'templates/dashboard.php';
    }

    /**
     * AJAX handler for loading sections.
     */
    public function ajax_get_section() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'iqr_manage_inventory' ) ) {
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
}
