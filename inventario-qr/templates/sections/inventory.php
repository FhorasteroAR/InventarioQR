<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-inventory">
    <div class="iqr-card-row">
        <div class="iqr-card iqr-card-accent">
            <div class="iqr-card-info">
                <span class="iqr-card-label">Total bienes</span>
                <span class="iqr-card-value" id="iqr-total-items">0</span>
            </div>
        </div>
        <div class="iqr-card">
            <div class="iqr-card-info">
                <span class="iqr-card-label">Cuentas</span>
                <span class="iqr-card-value" id="iqr-total-categories">0</span>
            </div>
        </div>
        <div class="iqr-card">
            <div class="iqr-card-info">
                <span class="iqr-card-label">Pendientes</span>
                <span class="iqr-card-value iqr-text-warning" id="iqr-low-stock">0</span>
            </div>
        </div>
    </div>

    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2>Bienes</h2>
            <button class="iqr-btn iqr-btn-primary" id="iqr-add-item-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Agregar bien
            </button>
        </div>
        <div class="iqr-panel-body">
            <div class="iqr-table-wrapper">
                <table class="iqr-table" id="iqr-items-table">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Descripción</th>
                            <th>Cuenta</th>
                            <th>Especie</th>
                            <th>Situación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="iqr-items-tbody">
                        <tr>
                            <td colspan="6" class="iqr-empty-state">
                                No hay bienes todavía. Hacé clic en "Agregar bien" para crear uno o importá desde Exportar/Importar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Agregar/Editar Bien -->
    <div class="iqr-modal" id="iqr-item-modal" style="display:none;">
        <div class="iqr-modal-overlay"></div>
        <div class="iqr-modal-content">
            <div class="iqr-modal-header">
                <h3 id="iqr-modal-title">Agregar bien</h3>
                <button class="iqr-modal-close" id="iqr-modal-close">&times;</button>
            </div>
            <form id="iqr-item-form">
                <input type="hidden" id="iqr-item-id" value="">
                <div class="iqr-form-grid">
                    <div class="iqr-form-group">
                        <label for="iqr-item-numero">Número</label>
                        <input type="text" id="iqr-item-numero" class="iqr-input" required>
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-cuenta">Cuenta</label>
                        <input type="text" id="iqr-item-cuenta" class="iqr-input">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-especie">Especie</label>
                        <input type="text" id="iqr-item-especie" class="iqr-input">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-situacion">Situación</label>
                        <input type="text" id="iqr-item-situacion" class="iqr-input">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-item-denominacion">Denominación</label>
                        <input type="text" id="iqr-item-denominacion" class="iqr-input">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-item-descripcion">Descripción</label>
                        <textarea id="iqr-item-descripcion" class="iqr-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="iqr-modal-footer">
                    <button type="button" class="iqr-btn iqr-btn-secondary" id="iqr-modal-cancel">Cancelar</button>
                    <button type="submit" class="iqr-btn iqr-btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
