<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-export-import">
    <div class="iqr-panel-row">
        <div class="iqr-panel">
            <div class="iqr-panel-header">
                <h2>Exportar</h2>
            </div>
            <div class="iqr-panel-body">
                <p class="iqr-text-muted">Descargá los datos del inventario en el formato que prefieras.</p>
                <div class="iqr-export-options">
                    <div class="iqr-form-group">
                        <label for="iqr-export-source">Origen de datos</label>
                        <select id="iqr-export-source" class="iqr-input">
                            <option value="control">Bienes Control (Auditoría)</option>
                            <option value="origen">Bienes Origen (Respaldo)</option>
                        </select>
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-export-format">Formato</label>
                        <select id="iqr-export-format" class="iqr-input">
                            <option value="csv">CSV</option>
                            <option value="xlsx">XLSX (Excel)</option>
                            <option value="ods">ODS (LibreOffice)</option>
                        </select>
                    </div>
                    <button class="iqr-btn iqr-btn-primary" id="iqr-export-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Exportar datos
                    </button>
                </div>
            </div>
        </div>

        <div class="iqr-panel">
            <div class="iqr-panel-header">
                <h2>Importar</h2>
            </div>
            <div class="iqr-panel-body">
                <p class="iqr-text-muted">Subí un archivo CSV, XLSX o ODS para importar datos de bienes al inventario.</p>
                <div class="iqr-import-dropzone" id="iqr-import-dropzone">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p>Arrastrá y soltá un archivo aquí, o hacé clic para buscar</p>
                    <input type="file" id="iqr-import-file" accept=".csv,.xlsx,.ods" style="display:none;">
                </div>
                <div id="iqr-import-status" style="display:none;" class="iqr-text-muted"></div>
                <button class="iqr-btn iqr-btn-secondary" id="iqr-import-btn" disabled>
                    Importar datos
                </button>
            </div>
        </div>
    </div>
</div>
