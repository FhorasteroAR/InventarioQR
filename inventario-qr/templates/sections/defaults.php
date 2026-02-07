<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-defaults">
    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2>Configuración por defecto</h2>
        </div>
        <div class="iqr-panel-body">
            <form id="iqr-defaults-form">
                <div class="iqr-form-grid">
                    <div class="iqr-form-group">
                        <label for="iqr-default-category">Cuenta por defecto</label>
                        <input type="text" id="iqr-default-category" class="iqr-input" placeholder="General">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-default-location">Ubicación por defecto</label>
                        <input type="text" id="iqr-default-location" class="iqr-input" placeholder="Depósito A">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-default-quantity">Cantidad por defecto</label>
                        <input type="number" id="iqr-default-quantity" class="iqr-input" min="0" value="1">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-low-stock-threshold">Umbral de stock bajo</label>
                        <input type="number" id="iqr-low-stock-threshold" class="iqr-input" min="0" value="5">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-qr-prefix">Prefijo del código QR</label>
                        <input type="text" id="iqr-qr-prefix" class="iqr-input" placeholder="INV-">
                    </div>
                </div>
                <div class="iqr-form-actions">
                    <button type="submit" class="iqr-btn iqr-btn-primary">
                        Guardar configuración
                    </button>
                    <button type="button" class="iqr-btn iqr-btn-secondary" id="iqr-reset-defaults">
                        Restablecer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
