<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Database {

    /**
     * Create plugin database tables.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_items = $wpdb->prefix . 'iqr_items';

        $sql = "CREATE TABLE $table_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            sku VARCHAR(100),
            category VARCHAR(100),
            quantity INT(11) NOT NULL DEFAULT 0,
            location VARCHAR(255),
            qr_code TEXT,
            image_url VARCHAR(500),
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_sku (sku),
            KEY idx_category (category)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
