<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-defaults">
    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2><?php esc_html_e( 'Default Settings', 'inventario-qr' ); ?></h2>
        </div>
        <div class="iqr-panel-body">
            <form id="iqr-defaults-form">
                <div class="iqr-form-grid">
                    <div class="iqr-form-group">
                        <label for="iqr-default-category"><?php esc_html_e( 'Default Category', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-default-category" class="iqr-input" placeholder="<?php esc_attr_e( 'General', 'inventario-qr' ); ?>">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-default-location"><?php esc_html_e( 'Default Location', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-default-location" class="iqr-input" placeholder="<?php esc_attr_e( 'Warehouse A', 'inventario-qr' ); ?>">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-default-quantity"><?php esc_html_e( 'Default Quantity', 'inventario-qr' ); ?></label>
                        <input type="number" id="iqr-default-quantity" class="iqr-input" min="0" value="1">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-low-stock-threshold"><?php esc_html_e( 'Low Stock Threshold', 'inventario-qr' ); ?></label>
                        <input type="number" id="iqr-low-stock-threshold" class="iqr-input" min="0" value="5">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-qr-prefix"><?php esc_html_e( 'QR Code Prefix', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-qr-prefix" class="iqr-input" placeholder="<?php esc_attr_e( 'INV-', 'inventario-qr' ); ?>">
                    </div>
                </div>
                <div class="iqr-form-actions">
                    <button type="submit" class="iqr-btn iqr-btn-primary">
                        <?php esc_html_e( 'Save Defaults', 'inventario-qr' ); ?>
                    </button>
                    <button type="button" class="iqr-btn iqr-btn-secondary" id="iqr-reset-defaults">
                        <?php esc_html_e( 'Reset', 'inventario-qr' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
