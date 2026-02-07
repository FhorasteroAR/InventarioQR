<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Database {

    /**
     * Crear las tablas de la base de datos del plugin.
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // 1. sp_bienes_origen — Respaldo crudo del Sistema Principal (Excel).
        $table_origen = $wpdb->prefix . 'sp_bienes_origen';
        $sql_origen = "CREATE TABLE $table_origen (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            numero VARCHAR(50),
            descripcion TEXT,
            cuenta VARCHAR(100),
            especie VARCHAR(100),
            situacion VARCHAR(100),
            grupo VARCHAR(100),
            p_principal VARCHAR(255),
            f_alta DATE,
            f_recepcion DATE,
            nro_serie VARCHAR(100),
            caract_patrimonial VARCHAR(255),
            sub_caract_patrimonial VARCHAR(255),
            denominacion VARCHAR(255),
            ver_bien TEXT,
            etiqueta VARCHAR(255),
            seleccione VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_numero (numero),
            KEY idx_cuenta (cuenta),
            KEY idx_especie (especie)
        ) $charset_collate;";
        dbDelta( $sql_origen );

        // 2. ss_bienes_control — Gestión y datos de auditoría.
        $table_control = $wpdb->prefix . 'ss_bienes_control';
        $sql_control = "CREATE TABLE $table_control (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            numero VARCHAR(50),
            descripcion TEXT,
            cuenta VARCHAR(100),
            especie VARCHAR(100),
            situacion VARCHAR(100),
            grupo VARCHAR(100),
            p_principal VARCHAR(255),
            f_alta DATE,
            f_recepcion DATE,
            nro_serie VARCHAR(100),
            caract_patrimonial VARCHAR(255),
            sub_caract_patrimonial VARCHAR(255),
            denominacion VARCHAR(255),
            ver_bien TEXT,
            etiqueta VARCHAR(255),
            seleccione VARCHAR(100),
            ubicacion_id BIGINT(20) UNSIGNED,
            estado_auditoria VARCHAR(50) DEFAULT 'pendiente',
            observaciones TEXT,
            auditado_por BIGINT(20) UNSIGNED,
            fecha_auditoria DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_numero (numero),
            KEY idx_cuenta (cuenta),
            KEY idx_especie (especie),
            KEY idx_ubicacion (ubicacion_id),
            KEY idx_estado (estado_auditoria)
        ) $charset_collate;";
        dbDelta( $sql_control );

        // 3. ubicaciones — Estructura de oficinas, depósitos y talleres.
        $table_ubicaciones = $wpdb->prefix . 'iqr_ubicaciones';
        $sql_ubicaciones = "CREATE TABLE $table_ubicaciones (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            tipo VARCHAR(50) NOT NULL DEFAULT 'oficina',
            direccion VARCHAR(255),
            responsable VARCHAR(255),
            descripcion TEXT,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_tipo (tipo),
            KEY idx_activo (activo)
        ) $charset_collate;";
        dbDelta( $sql_ubicaciones );
    }
}
