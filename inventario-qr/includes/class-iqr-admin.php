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
     * AJAX: importar datos desde archivo ODS/XLSX a las tablas sp_bienes_origen y ss_bienes_control.
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

        if ( ! in_array( $ext, array( 'xlsx', 'ods' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Formato no soportado. Usá archivos XLSX o ODS.' ) );
        }

        if ( ! class_exists( 'ZipArchive' ) ) {
            wp_send_json_error( array( 'message' => 'El servidor no tiene la extensión ZIP habilitada. Contactá al administrador.' ) );
        }

        if ( 'xlsx' === $ext ) {
            $rows = $this->parse_xlsx( $file['tmp_name'] );
        } else {
            $rows = $this->parse_ods( $file['tmp_name'] );
        }

        if ( is_string( $rows ) ) {
            wp_send_json_error( array( 'message' => $rows ) );
        }

        if ( empty( $rows ) ) {
            wp_send_json_error( array( 'message' => 'No se encontraron datos en el archivo.' ) );
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

    /**
     * Parsear archivo XLSX (Office Open XML) usando ZipArchive.
     * Retorna array de filas asociativas o string de error.
     */
    private function parse_xlsx( $filepath ) {
        $zip = new ZipArchive();
        if ( true !== $zip->open( $filepath ) ) {
            return 'No se pudo abrir el archivo XLSX.';
        }

        // Leer strings compartidos
        $shared_strings = array();
        $ss_xml = $zip->getFromName( 'xl/sharedStrings.xml' );
        if ( $ss_xml ) {
            $ss_doc = new DOMDocument();
            $ss_doc->loadXML( $ss_xml );
            $si_nodes = $ss_doc->getElementsByTagName( 'si' );
            foreach ( $si_nodes as $si ) {
                $text = '';
                $t_nodes = $si->getElementsByTagName( 't' );
                foreach ( $t_nodes as $t ) {
                    $text .= $t->nodeValue;
                }
                $shared_strings[] = $text;
            }
        }

        // Leer la primera hoja (sheet1)
        $sheet_xml = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
        if ( ! $sheet_xml ) {
            $zip->close();
            return 'No se encontró la hoja de datos en el archivo XLSX.';
        }

        $doc = new DOMDocument();
        $doc->loadXML( $sheet_xml );
        $row_nodes = $doc->getElementsByTagName( 'row' );

        $all_rows = array();
        foreach ( $row_nodes as $row_node ) {
            $cells = $row_node->getElementsByTagName( 'c' );
            $row_data = array();
            foreach ( $cells as $cell ) {
                $col_index = $this->xlsx_col_index( $cell->getAttribute( 'r' ) );
                $type = $cell->getAttribute( 't' );
                $v_nodes = $cell->getElementsByTagName( 'v' );
                $value = $v_nodes->length ? $v_nodes->item( 0 )->nodeValue : '';

                if ( 's' === $type && isset( $shared_strings[ (int) $value ] ) ) {
                    $value = $shared_strings[ (int) $value ];
                }

                $row_data[ $col_index ] = $value;
            }
            $all_rows[] = $row_data;
        }

        $zip->close();

        return $this->map_rows_with_header( $all_rows );
    }

    /**
     * Parsear archivo ODS (OpenDocument Spreadsheet) usando ZipArchive.
     * Retorna array de filas asociativas o string de error.
     */
    private function parse_ods( $filepath ) {
        $zip = new ZipArchive();
        if ( true !== $zip->open( $filepath ) ) {
            return 'No se pudo abrir el archivo ODS.';
        }

        $content_xml = $zip->getFromName( 'content.xml' );
        $zip->close();

        if ( ! $content_xml ) {
            return 'No se encontró content.xml en el archivo ODS.';
        }

        $doc = new DOMDocument();
        $doc->loadXML( $content_xml );

        // Buscar la primera tabla
        $tables = $doc->getElementsByTagNameNS( 'urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table' );
        if ( 0 === $tables->length ) {
            return 'No se encontró ninguna tabla en el archivo ODS.';
        }

        $table = $tables->item( 0 );
        $table_rows = $table->getElementsByTagNameNS( 'urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-row' );

        $all_rows = array();
        foreach ( $table_rows as $tr ) {
            $cells = $tr->getElementsByTagNameNS( 'urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-cell' );
            $row_data = array();
            $col = 0;

            foreach ( $cells as $cell ) {
                // Manejar columnas repetidas
                $repeat = $cell->getAttributeNS( 'urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'number-columns-repeated' );
                $repeat = $repeat ? (int) $repeat : 1;

                $p_nodes = $cell->getElementsByTagNameNS( 'urn:oasis:names:tc:opendocument:xmlns:text:1.0', 'p' );
                $value = '';
                foreach ( $p_nodes as $p ) {
                    $value .= $p->nodeValue;
                }

                for ( $r = 0; $r < $repeat; $r++ ) {
                    $row_data[ $col ] = $value;
                    $col++;
                }
            }

            // Ignorar filas completamente vacías
            $non_empty = array_filter( $row_data, function ( $v ) { return '' !== $v; } );
            if ( ! empty( $non_empty ) ) {
                $all_rows[] = $row_data;
            }
        }

        return $this->map_rows_with_header( $all_rows );
    }

    /**
     * Convertir letra de columna XLSX (ej: "A1", "B2", "AA3") a índice numérico (0-based).
     */
    private function xlsx_col_index( $cell_ref ) {
        $letters = preg_replace( '/[0-9]/', '', $cell_ref );
        $index = 0;
        $len = strlen( $letters );
        for ( $i = 0; $i < $len; $i++ ) {
            $index = $index * 26 + ( ord( strtoupper( $letters[ $i ] ) ) - ord( 'A' ) + 1 );
        }
        return $index - 1;
    }

    /**
     * Tomar la primera fila como header y mapear el resto como arrays asociativos.
     */
    private function map_rows_with_header( $all_rows ) {
        if ( count( $all_rows ) < 2 ) {
            return array();
        }

        $header_row = array_shift( $all_rows );
        $header = array();
        foreach ( $header_row as $i => $h ) {
            $header[ $i ] = sanitize_key( str_replace( array( '.', ' ' ), '_', strtolower( trim( $h ) ) ) );
        }

        $rows = array();
        foreach ( $all_rows as $raw ) {
            $row = array();
            foreach ( $header as $i => $key ) {
                $row[ $key ] = isset( $raw[ $i ] ) ? $raw[ $i ] : '';
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
