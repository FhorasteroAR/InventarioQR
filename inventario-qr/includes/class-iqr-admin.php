<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_iqr_get_section', array( $this, 'ajax_get_section' ) );
        add_action( 'wp_ajax_iqr_import_excel', array( $this, 'ajax_import_excel' ) );
        add_action( 'wp_ajax_iqr_export_data', array( $this, 'ajax_export_data' ) );
    }

    public function add_menu_pages() {
        add_menu_page(
            'Inventario QR',
            'Inventario QR',
            'iqr_manage_inventory',
            'inventario-qr',
            array( $this, 'render_dashboard' ),
            'dashicons-screenoptions',
            30
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_inventario-qr' !== $hook ) {
            return;
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
            'userRole'    => $this->get_user_iqr_role(),
        ) );
    }

    private function get_user_iqr_role() {
        $user = wp_get_current_user();
        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            return 'Administrador';
        }
        if ( in_array( 'iqr_admin', (array) $user->roles, true ) ) {
            return 'Admin Inventario';
        }
        return 'Usuario';
    }

    public function render_dashboard() {
        include IQR_PLUGIN_DIR . 'templates/dashboard.php';
    }

    public function ajax_get_section() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'iqr_manage_inventory' ) ) {
            wp_send_json_error( array( 'message' => 'No autorizado.' ) );
        }

        $section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';
        $allowed = array( 'qr', 'inventory', 'export-import', 'defaults', 'user' );

        if ( ! in_array( $section, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => 'Sección inválida.' ) );
        }

        $template = IQR_PLUGIN_DIR . 'templates/sections/' . $section . '.php';

        if ( ! file_exists( $template ) ) {
            wp_send_json_error( array( 'message' => 'Sección no encontrada.' ) );
        }

        ob_start();
        include $template;
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * AJAX: importar datos desde archivo CSV/JSON a las tablas sp_bienes_origen y ss_bienes_control.
     */
    public function ajax_import_excel() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'iqr_export_import' ) ) {
            wp_send_json_error( array( 'message' => 'No autorizado.' ) );
        }

        if ( empty( $_FILES['import_file'] ) ) {
            wp_send_json_error( array( 'message' => 'No se recibió ningún archivo.' ) );
        }

        $file = $_FILES['import_file'];
        $ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, array( 'csv', 'json' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Formato no soportado. Usá CSV o JSON.' ) );
        }

        $content = file_get_contents( $file['tmp_name'] );

        if ( false === $content || empty( $content ) ) {
            wp_send_json_error( array( 'message' => 'El archivo está vacío o no se pudo leer.' ) );
        }

        global $wpdb;
        $table_origen  = $wpdb->prefix . 'sp_bienes_origen';
        $table_control = $wpdb->prefix . 'ss_bienes_control';
        $imported      = 0;

        $columns = array(
            'numero', 'descripcion', 'cuenta', 'especie', 'situacion',
            'grupo', 'p_principal', 'f_alta', 'f_recepcion', 'nro_serie',
            'caract_patrimonial', 'sub_caract_patrimonial', 'denominacion',
            'ver_bien', 'etiqueta', 'seleccione',
        );

        if ( 'csv' === $ext ) {
            $rows = $this->parse_csv( $content );
        } else {
            $rows = json_decode( $content, true );
            if ( ! is_array( $rows ) ) {
                wp_send_json_error( array( 'message' => 'El archivo JSON no tiene un formato válido.' ) );
            }
        }

        if ( empty( $rows ) ) {
            wp_send_json_error( array( 'message' => 'No se encontraron datos en el archivo.' ) );
        }

        foreach ( $rows as $row ) {
            $data = array();
            foreach ( $columns as $i => $col ) {
                $value = '';
                if ( isset( $row[ $col ] ) ) {
                    $value = $row[ $col ];
                } elseif ( isset( $row[ $i ] ) ) {
                    $value = $row[ $i ];
                }
                $data[ $col ] = sanitize_text_field( $value );
            }

            if ( ! empty( $data['f_alta'] ) ) {
                $data['f_alta'] = $this->parse_date( $data['f_alta'] );
            }
            if ( ! empty( $data['f_recepcion'] ) ) {
                $data['f_recepcion'] = $this->parse_date( $data['f_recepcion'] );
            }

            // Insertar en sp_bienes_origen (respaldo crudo)
            $wpdb->insert( $table_origen, $data );

            // Insertar en ss_bienes_control (gestión)
            $data['estado_auditoria'] = 'pendiente';
            $wpdb->insert( $table_control, $data );

            $imported++;
        }

        wp_send_json_success( array(
            'message' => sprintf( 'Se importaron %d registros correctamente.', $imported ),
            'count'   => $imported,
        ) );
    }

    private function parse_csv( $content ) {
        $rows   = array();
        $lines  = preg_split( '/\r\n|\r|\n/', $content );
        $header = null;

        foreach ( $lines as $line ) {
            if ( empty( trim( $line ) ) ) {
                continue;
            }

            $fields = str_getcsv( $line, ',', '"' );

            if ( null === $header ) {
                $header = array_map( function ( $h ) {
                    return sanitize_key( str_replace( array( '.', ' ' ), '_', strtolower( trim( $h ) ) ) );
                }, $fields );
                continue;
            }

            $row = array();
            foreach ( $header as $i => $key ) {
                $row[ $key ] = isset( $fields[ $i ] ) ? $fields[ $i ] : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function parse_date( $date_string ) {
        $timestamp = strtotime( $date_string );
        if ( false !== $timestamp ) {
            return date( 'Y-m-d', $timestamp );
        }
        return null;
    }

    /**
     * AJAX: exportar datos de ss_bienes_control o sp_bienes_origen.
     */
    public function ajax_export_data() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'iqr_export_import' ) ) {
            wp_send_json_error( array( 'message' => 'No autorizado.' ) );
        }

        $format = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'csv';
        $source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'control';

        global $wpdb;
        if ( 'origen' === $source ) {
            $table = $wpdb->prefix . 'sp_bienes_origen';
        } else {
            $table = $wpdb->prefix . 'ss_bienes_control';
        }

        $results = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );

        if ( empty( $results ) ) {
            wp_send_json_error( array( 'message' => 'No hay datos para exportar.' ) );
        }

        if ( 'json' === $format ) {
            wp_send_json_success( array(
                'format'   => 'json',
                'data'     => $results,
                'filename' => $source . '_' . date( 'Y-m-d_His' ) . '.json',
            ) );
        } else {
            $csv = '';
            $csv .= implode( ',', array_keys( $results[0] ) ) . "\n";
            foreach ( $results as $row ) {
                $csv .= implode( ',', array_map( function ( $v ) {
                    return '"' . str_replace( '"', '""', $v ) . '"';
                }, $row ) ) . "\n";
            }

            wp_send_json_success( array(
                'format'   => 'csv',
                'data'     => $csv,
                'filename' => $source . '_' . date( 'Y-m-d_His' ) . '.csv',
            ) );
        }
    }
}
