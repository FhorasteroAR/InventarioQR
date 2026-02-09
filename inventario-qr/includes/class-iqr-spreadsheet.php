<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lightweight spreadsheet helper for CSV, XLSX and ODS.
 * No external dependencies – uses PHP's ZipArchive and SimpleXML.
 */
class IQR_Spreadsheet {

    /* ================================================================
       EXPORT helpers – return raw file contents (string)
       ================================================================ */

    /**
     * Generate CSV string from rows.
     */
    public static function export_csv( array $rows ) {
        if ( empty( $rows ) ) {
            return '';
        }

        $handle = fopen( 'php://temp', 'r+' );
        fputcsv( $handle, array_keys( $rows[0] ) );

        foreach ( $rows as $row ) {
            fputcsv( $handle, array_values( $row ) );
        }

        rewind( $handle );
        $csv = stream_get_contents( $handle );
        fclose( $handle );

        return $csv;
    }

    /**
     * Generate an XLSX file and return the binary contents.
     */
    public static function export_xlsx( array $rows ) {
        if ( empty( $rows ) ) {
            return '';
        }

        $tmp = wp_tempnam( 'iqr_export_' ) . '.xlsx';
        $zip = new ZipArchive();

        if ( true !== $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
            return '';
        }

        $columns = array_keys( $rows[0] );
        $col_count = count( $columns );
        $row_count = count( $rows ) + 1; // +1 for header

        // [Content_Types].xml
        $zip->addFromString( '[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '</Types>' );

        // _rels/.rels
        $zip->addFromString( '_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>' );

        // xl/_rels/workbook.xml.rels
        $zip->addFromString( 'xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>' );

        // xl/workbook.xml
        $zip->addFromString( 'xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Datos" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>' );

        // Build shared strings table and sheet data.
        $strings     = array();
        $string_index = array();

        $get_index = function ( $val ) use ( &$strings, &$string_index ) {
            $val = (string) $val;
            if ( ! isset( $string_index[ $val ] ) ) {
                $string_index[ $val ] = count( $strings );
                $strings[] = $val;
            }
            return $string_index[ $val ];
        };

        // Sheet XML
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>';

        // Header row
        $sheet .= '<row r="1">';
        foreach ( $columns as $ci => $col_name ) {
            $cell_ref = self::col_letter( $ci ) . '1';
            $si = $get_index( $col_name );
            $sheet .= '<c r="' . $cell_ref . '" t="s"><v>' . $si . '</v></c>';
        }
        $sheet .= '</row>';

        // Data rows
        foreach ( $rows as $ri => $row ) {
            $row_num = $ri + 2;
            $sheet .= '<row r="' . $row_num . '">';
            $ci = 0;
            foreach ( $columns as $col ) {
                $cell_ref = self::col_letter( $ci ) . $row_num;
                $val = isset( $row[ $col ] ) ? $row[ $col ] : '';
                $si = $get_index( $val );
                $sheet .= '<c r="' . $cell_ref . '" t="s"><v>' . $si . '</v></c>';
                $ci++;
            }
            $sheet .= '</row>';
        }

        $sheet .= '</sheetData></worksheet>';
        $zip->addFromString( 'xl/worksheets/sheet1.xml', $sheet );

        // Shared strings
        $ss = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count( $strings ) . '" uniqueCount="' . count( $strings ) . '">';
        foreach ( $strings as $s ) {
            $ss .= '<si><t>' . htmlspecialchars( $s, ENT_XML1, 'UTF-8' ) . '</t></si>';
        }
        $ss .= '</sst>';
        $zip->addFromString( 'xl/sharedStrings.xml', $ss );

        $zip->close();

        $data = file_get_contents( $tmp );
        @unlink( $tmp );

        return $data;
    }

    /**
     * Generate an ODS file and return the binary contents.
     */
    public static function export_ods( array $rows ) {
        if ( empty( $rows ) ) {
            return '';
        }

        $tmp = wp_tempnam( 'iqr_export_' ) . '.ods';
        $zip = new ZipArchive();

        if ( true !== $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
            return '';
        }

        $columns = array_keys( $rows[0] );

        // mimetype (must be first entry, uncompressed)
        $zip->addFromString( 'mimetype', 'application/vnd.oasis.opendocument.spreadsheet' );
        $zip->setCompressionName( 'mimetype', ZipArchive::CM_STORE );

        // META-INF/manifest.xml
        $zip->addFromString( 'META-INF/manifest.xml', '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">'
            . '<manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '</manifest:manifest>' );

        // content.xml
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"'
            . ' xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"'
            . ' office:version="1.2">'
            . '<office:body><office:spreadsheet>'
            . '<table:table table:name="Datos">';

        // Header
        $xml .= '<table:table-row>';
        foreach ( $columns as $col ) {
            $xml .= '<table:table-cell office:value-type="string"><text:p>'
                . htmlspecialchars( $col, ENT_XML1, 'UTF-8' )
                . '</text:p></table:table-cell>';
        }
        $xml .= '</table:table-row>';

        // Data
        foreach ( $rows as $row ) {
            $xml .= '<table:table-row>';
            foreach ( $columns as $col ) {
                $val = isset( $row[ $col ] ) ? $row[ $col ] : '';
                $xml .= '<table:table-cell office:value-type="string"><text:p>'
                    . htmlspecialchars( $val, ENT_XML1, 'UTF-8' )
                    . '</text:p></table:table-cell>';
            }
            $xml .= '</table:table-row>';
        }

        $xml .= '</table:table></office:spreadsheet></office:body></office:document-content>';
        $zip->addFromString( 'content.xml', $xml );

        $zip->close();

        $data = file_get_contents( $tmp );
        @unlink( $tmp );

        return $data;
    }

    /* ================================================================
       IMPORT helpers – return array of associative rows
       ================================================================ */

    /**
     * Parse CSV content into rows.
     */
    public static function import_csv( $content ) {
        $rows   = array();
        $lines  = preg_split( '/\r\n|\r|\n/', $content );
        $header = null;

        foreach ( $lines as $line ) {
            if ( '' === trim( $line ) ) {
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

    /**
     * Parse an XLSX file into rows.
     */
    public static function import_xlsx( $file_path ) {
        $zip = new ZipArchive();

        if ( true !== $zip->open( $file_path ) ) {
            return array();
        }

        // Read shared strings
        $shared = array();
        $ss_xml = $zip->getFromName( 'xl/sharedStrings.xml' );

        if ( false !== $ss_xml ) {
            $ss = new SimpleXMLElement( $ss_xml );
            foreach ( $ss->si as $si ) {
                // Handle both simple <t> and rich text <r><t> nodes.
                if ( isset( $si->t ) ) {
                    $shared[] = (string) $si->t;
                } else {
                    $parts = '';
                    foreach ( $si->r as $r ) {
                        $parts .= (string) $r->t;
                    }
                    $shared[] = $parts;
                }
            }
        }

        // Read sheet1
        $sheet_xml = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
        $zip->close();

        if ( false === $sheet_xml ) {
            return array();
        }

        $sheet = new SimpleXMLElement( $sheet_xml );
        $raw_rows = array();

        foreach ( $sheet->sheetData->row as $row ) {
            $cells = array();
            foreach ( $row->c as $c ) {
                $val = '';
                $type = (string) $c['t'];
                $v = (string) $c->v;

                if ( 's' === $type && isset( $shared[ (int) $v ] ) ) {
                    $val = $shared[ (int) $v ];
                } else {
                    $val = $v;
                }
                $cells[] = $val;
            }
            $raw_rows[] = $cells;
        }

        if ( count( $raw_rows ) < 2 ) {
            return array();
        }

        $header = array_map( function ( $h ) {
            return sanitize_key( str_replace( array( '.', ' ' ), '_', strtolower( trim( $h ) ) ) );
        }, $raw_rows[0] );

        $rows = array();
        for ( $i = 1; $i < count( $raw_rows ); $i++ ) {
            $row = array();
            foreach ( $header as $ci => $key ) {
                $row[ $key ] = isset( $raw_rows[ $i ][ $ci ] ) ? $raw_rows[ $i ][ $ci ] : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Parse an ODS file into rows.
     */
    public static function import_ods( $file_path ) {
        $zip = new ZipArchive();

        if ( true !== $zip->open( $file_path ) ) {
            return array();
        }

        $content_xml = $zip->getFromName( 'content.xml' );
        $zip->close();

        if ( false === $content_xml ) {
            return array();
        }

        $xml = new SimpleXMLElement( $content_xml );
        $xml->registerXPathNamespace( 'table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0' );
        $xml->registerXPathNamespace( 'text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0' );

        $table_rows = $xml->xpath( '//table:table[1]/table:table-row' );

        if ( empty( $table_rows ) ) {
            return array();
        }

        $raw_rows = array();
        foreach ( $table_rows as $tr ) {
            $cells = array();
            $tr->registerXPathNamespace( 'table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0' );
            $tr->registerXPathNamespace( 'text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0' );

            foreach ( $tr->xpath( 'table:table-cell' ) as $cell ) {
                $cell->registerXPathNamespace( 'table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0' );
                $cell->registerXPathNamespace( 'text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0' );

                $repeat = 1;
                $attrs = $cell->attributes( 'urn:oasis:names:tc:opendocument:xmlns:table:1.0' );
                if ( isset( $attrs['number-columns-repeated'] ) ) {
                    $repeat = (int) $attrs['number-columns-repeated'];
                    if ( $repeat > 100 ) {
                        break; // Empty trailing cells
                    }
                }

                $text_nodes = $cell->xpath( 'text:p' );
                $val = '';
                if ( ! empty( $text_nodes ) ) {
                    $val = (string) $text_nodes[0];
                }

                for ( $r = 0; $r < $repeat; $r++ ) {
                    $cells[] = $val;
                }
            }
            $raw_rows[] = $cells;
        }

        if ( count( $raw_rows ) < 2 ) {
            return array();
        }

        $header = array_map( function ( $h ) {
            return sanitize_key( str_replace( array( '.', ' ' ), '_', strtolower( trim( $h ) ) ) );
        }, $raw_rows[0] );

        // Trim empty header columns
        $header = array_filter( $header, function ( $h ) {
            return '' !== $h;
        } );

        $rows = array();
        for ( $i = 1; $i < count( $raw_rows ); $i++ ) {
            $row = array();
            $has_data = false;
            foreach ( $header as $ci => $key ) {
                $val = isset( $raw_rows[ $i ][ $ci ] ) ? $raw_rows[ $i ][ $ci ] : '';
                $row[ $key ] = $val;
                if ( '' !== $val ) {
                    $has_data = true;
                }
            }
            if ( $has_data ) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /* ================================================================
       Utilities
       ================================================================ */

    /**
     * Convert column index (0-based) to Excel column letter (A, B, …, Z, AA, …).
     */
    private static function col_letter( $index ) {
        $letter = '';
        $index++;
        while ( $index > 0 ) {
            $index--;
            $letter = chr( 65 + ( $index % 26 ) ) . $letter;
            $index = intval( $index / 26 );
        }
        return $letter;
    }
}
