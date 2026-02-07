<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-qr">
    <div class="iqr-card-row">
        <div class="iqr-card iqr-card-accent">
            <div class="iqr-card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="3" height="3"/>
                    <line x1="21" y1="14" x2="21" y2="21"/>
                    <line x1="14" y1="21" x2="21" y2="21"/>
                </svg>
            </div>
            <div class="iqr-card-info">
                <span class="iqr-card-label">Total Códigos QR</span>
                <span class="iqr-card-value" id="iqr-total-qr">0</span>
            </div>
        </div>

        <div class="iqr-card">
            <div class="iqr-card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
            </div>
            <div class="iqr-card-info">
                <span class="iqr-card-label">Generados hoy</span>
                <span class="iqr-card-value">0</span>
            </div>
        </div>

        <div class="iqr-card">
            <div class="iqr-card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
            <div class="iqr-card-info">
                <span class="iqr-card-label">Escaneos esta semana</span>
                <span class="iqr-card-value">0</span>
            </div>
        </div>
    </div>

    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2>Generar Código QR</h2>
        </div>
        <div class="iqr-panel-body">
            <div class="iqr-form-group">
                <label for="iqr-qr-item">Seleccionar bien</label>
                <select id="iqr-qr-item" class="iqr-input">
                    <option value="">-- Seleccionar un bien --</option>
                </select>
            </div>
            <div class="iqr-form-group">
                <label for="iqr-qr-content">Contenido QR</label>
                <input type="text" id="iqr-qr-content" class="iqr-input" placeholder="Generado automáticamente desde los datos del bien" readonly>
            </div>
            <button class="iqr-btn iqr-btn-primary" id="iqr-generate-qr">
                Generar QR
            </button>
            <div class="iqr-qr-preview" id="iqr-qr-preview">
                <p class="iqr-text-muted">Seleccioná un bien para generar su código QR.</p>
            </div>
        </div>
    </div>
</div>
