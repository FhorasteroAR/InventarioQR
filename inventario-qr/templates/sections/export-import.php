<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-export-import">
    <div class="iqr-panel-row">
        <div class="iqr-panel">
            <div class="iqr-panel-header">
                <h2><?php esc_html_e( 'Export', 'inventario-qr' ); ?></h2>
            </div>
            <div class="iqr-panel-body">
                <p class="iqr-text-muted"><?php esc_html_e( 'Download your inventory data in your preferred format.', 'inventario-qr' ); ?></p>
                <div class="iqr-export-options">
                    <div class="iqr-form-group">
                        <label for="iqr-export-format"><?php esc_html_e( 'Format', 'inventario-qr' ); ?></label>
                        <select id="iqr-export-format" class="iqr-input">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <button class="iqr-btn iqr-btn-primary" id="iqr-export-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <?php esc_html_e( 'Export Data', 'inventario-qr' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="iqr-panel">
            <div class="iqr-panel-header">
                <h2><?php esc_html_e( 'Import', 'inventario-qr' ); ?></h2>
            </div>
            <div class="iqr-panel-body">
                <p class="iqr-text-muted"><?php esc_html_e( 'Upload a CSV or JSON file to import inventory data.', 'inventario-qr' ); ?></p>
                <div class="iqr-import-dropzone" id="iqr-import-dropzone">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p><?php esc_html_e( 'Drag & drop a file here, or click to browse', 'inventario-qr' ); ?></p>
                    <input type="file" id="iqr-import-file" accept=".csv,.json" style="display:none;">
                </div>
                <button class="iqr-btn iqr-btn-secondary" id="iqr-import-btn" disabled>
                    <?php esc_html_e( 'Import Data', 'inventario-qr' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>
