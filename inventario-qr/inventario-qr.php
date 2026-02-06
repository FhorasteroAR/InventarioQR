<?php
/**
 * Plugin Name: Inventario QR
 * Plugin URI:  https://github.com/FhorasteroAR/InventarioQR
 * Description: Sistema de inventario con generación de códigos QR para WordPress.
 * Version:     1.0.0
 * Author:      FhorasteroAR
 * Text Domain: inventario-qr
 * License:     GPL v2 or later
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
