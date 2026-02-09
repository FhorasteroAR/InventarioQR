<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Ods as WriterOds;
use PhpOffice\PhpSpreadsheet\Writer\Csv as WriterCsv;

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
     * AJAX: importar datos desde archivo XLSX/ODS/CSV a las tablas sp_bienes_origen y ss_bienes_control.
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

        if ( ! in_array( $ext, array( 'xlsx', 'ods', 'csv' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Formato no soportado. Usá XLSX, ODS o CSV.' ) );
        }

        $tmp_path = $file['tmp_name'];

        if ( ! file_exists( $tmp_path ) || 0 === filesize( $tmp_path ) ) {
            wp_send_json_error( array( 'message' => 'El archivo está vacío o no se pudo leer.' ) );
        }

        try {
            $rows = $this->parse_spreadsheet( $tmp_path, $ext );
        } catch ( \Exception $e ) {
            wp_send_json_error( array( 'message' => 'Error al leer el archivo: ' . $e->getMessage() ) );
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

            $wpdb->insert( $table_origen, $data );

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
     * Lee un archivo de hoja de cálculo (XLSX, ODS o CSV) y devuelve un array de filas asociativas.
     */
    private function parse_spreadsheet( $file_path, $ext ) {
        $reader_type = array(
            'xlsx' => 'Xlsx',
            'ods'  => 'Ods',
            'csv'  => 'Csv',
        );

        $reader = IOFactory::createReader( $reader_type[ $ext ] );

        if ( 'csv' === $ext ) {
            $reader->setDelimiter( ',' );
            $reader->setEnclosure( '"' );
        }

        $spreadsheet = $reader->load( $file_path );
        $worksheet   = $spreadsheet->getActiveSheet();
        $data        = $worksheet->toArray( null, true, true, false );

        if ( empty( $data ) ) {
            return array();
        }

        $header = array_map( function ( $h ) {
            return sanitize_key( str_replace( array( '.', ' ' ), '_', strtolower( trim( (string) $h ) ) ) );
        }, array_shift( $data ) );

        $rows = array();
        foreach ( $data as $row_values ) {
            if ( empty( array_filter( $row_values, function ( $v ) { return '' !== trim( (string) $v ); } ) ) ) {
                continue;
            }
            $row = array();
            foreach ( $header as $i => $key ) {
                $row[ $key ] = isset( $row_values[ $i ] ) ? (string) $row_values[ $i ] : '';
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
     * AJAX: exportar datos de ss_bienes_control o sp_bienes_origen en formato XLSX, ODS o CSV.
     */
    public function ajax_export_data() {
        check_ajax_referer( 'iqr_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'iqr_export_import' ) ) {
            wp_send_json_error( array( 'message' => 'No autorizado.' ) );
        }

        $format = isset( $_POST['format'] ) ? sanitize_key( $_POST['format'] ) : 'xlsx';
        $source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'control';

        if ( ! in_array( $format, array( 'xlsx', 'ods', 'csv' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Formato no soportado.' ) );
        }

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

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Escribir encabezados.
        $headers = array_keys( $results[0] );
        foreach ( $headers as $col_index => $header ) {
            $sheet->setCellValue( [ $col_index + 1, 1 ], $header );
        }

        // Escribir filas de datos.
        $row_num = 2;
        foreach ( $results as $row ) {
            $col_index = 1;
            foreach ( $row as $value ) {
                $sheet->setCellValue( [ $col_index, $row_num ], $value );
                $col_index++;
            }
            $row_num++;
        }

        $ext_map = array(
            'xlsx' => 'xlsx',
            'ods'  => 'ods',
            'csv'  => 'csv',
        );

        $mime_map = array(
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'csv'  => 'text/csv',
        );

        $filename = $source . '_' . date( 'Y-m-d_His' ) . '.' . $ext_map[ $format ];

        // Escribir a un archivo temporal y codificar en base64 para enviar vía JSON.
        $tmp_file = wp_tempnam( $filename );

        switch ( $format ) {
            case 'xlsx':
                $writer = new WriterXlsx( $spreadsheet );
                break;
            case 'ods':
                $writer = new WriterOds( $spreadsheet );
                break;
            case 'csv':
                $writer = new WriterCsv( $spreadsheet );
                $writer->setDelimiter( ',' );
                $writer->setEnclosure( '"' );
                break;
        }

        $writer->save( $tmp_file );
        $file_data = base64_encode( file_get_contents( $tmp_file ) );
        unlink( $tmp_file );

        wp_send_json_success( array(
            'format'   => $format,
            'data'     => $file_data,
            'filename' => $filename,
            'mime'     => $mime_map[ $format ],
        ) );
    }
}
