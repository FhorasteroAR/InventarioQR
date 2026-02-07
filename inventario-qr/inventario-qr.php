<?php
/**
 * Plugin Name: Inventario QR
 * Plugin URI:  https://github.com/FhorasteroAR/InventarioQR
 * Description: Sistema de inventario con generación de códigos QR para WordPress. Shortcode: [inventario_qr]
 * Version:     1.0.0
 * Author:      FhorasteroAR
 * Text Domain: inventario-qr
 * License:     GPL v2 or later
 *
 * Uso: Agregar el shortcode [inventario_qr] en cualquier página o post para mostrar el panel de inventario.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IQR_VERSION', '1.0.0' );
define( 'IQR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IQR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once IQR_PLUGIN_DIR . 'includes/class-iqr-roles.php';
require_once IQR_PLUGIN_DIR . 'includes/class-iqr-auth.php';
require_once IQR_PLUGIN_DIR . 'includes/class-iqr-admin.php';
require_once IQR_PLUGIN_DIR . 'includes/class-iqr-database.php';

/**
 * Plugin activation.
 */
function iqr_activate() {
    IQR_Database::create_tables();
    IQR_Roles::add_roles();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'iqr_activate' );

/**
 * Plugin deactivation.
 */
function iqr_deactivate() {
    IQR_Roles::remove_roles();
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'iqr_deactivate' );

/**
 * Initialize the plugin.
 */
function iqr_init() {
    $auth  = new IQR_Auth();
    $admin = new IQR_Admin();
}
add_action( 'init', 'iqr_init' );

/**
 * Shortcode [inventario_qr] — renders the Inventario QR dashboard on the front end.
 */
function iqr_shortcode( $atts ) {
    if ( ! is_user_logged_in() || ! current_user_can( 'iqr_manage_inventory' ) ) {
        return '<p>' . esc_html__( 'You do not have permission to view the inventory.', 'inventario-qr' ) . '</p>';
    }

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
        'userRole'    => iqr_get_current_user_role(),
    ) );

    ob_start();
    include IQR_PLUGIN_DIR . 'templates/dashboard.php';
    return ob_get_clean();
}
add_shortcode( 'inventario_qr', 'iqr_shortcode' );

/**
 * Helper: get the current user IQR role label.
 */
function iqr_get_current_user_role() {
    $user = wp_get_current_user();
    if ( in_array( 'administrator', (array) $user->roles, true ) ) {
        return __( 'Administrator', 'inventario-qr' );
    }
    if ( in_array( 'iqr_admin', (array) $user->roles, true ) ) {
        return __( 'Inventario Admin', 'inventario-qr' );
    }
    return __( 'User', 'inventario-qr' );
}
