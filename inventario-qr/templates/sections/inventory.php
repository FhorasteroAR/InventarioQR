<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-section iqr-section-inventory">
    <div class="iqr-card-row">
        <div class="iqr-card iqr-card-accent">
            <div class="iqr-card-info">
                <span class="iqr-card-label"><?php esc_html_e( 'Total Items', 'inventario-qr' ); ?></span>
                <span class="iqr-card-value" id="iqr-total-items">0</span>
            </div>
        </div>
        <div class="iqr-card">
            <div class="iqr-card-info">
                <span class="iqr-card-label"><?php esc_html_e( 'Categories', 'inventario-qr' ); ?></span>
                <span class="iqr-card-value" id="iqr-total-categories">0</span>
            </div>
        </div>
        <div class="iqr-card">
            <div class="iqr-card-info">
                <span class="iqr-card-label"><?php esc_html_e( 'Low Stock', 'inventario-qr' ); ?></span>
                <span class="iqr-card-value iqr-text-warning" id="iqr-low-stock">0</span>
            </div>
        </div>
    </div>

    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2><?php esc_html_e( 'Items', 'inventario-qr' ); ?></h2>
            <button class="iqr-btn iqr-btn-primary" id="iqr-add-item-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <?php esc_html_e( 'Add Item', 'inventario-qr' ); ?>
            </button>
        </div>
        <div class="iqr-panel-body">
            <div class="iqr-table-wrapper">
                <table class="iqr-table" id="iqr-items-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'inventario-qr' ); ?></th>
                            <th><?php esc_html_e( 'SKU', 'inventario-qr' ); ?></th>
                            <th><?php esc_html_e( 'Category', 'inventario-qr' ); ?></th>
                            <th><?php esc_html_e( 'Qty', 'inventario-qr' ); ?></th>
                            <th><?php esc_html_e( 'Location', 'inventario-qr' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'inventario-qr' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="iqr-items-tbody">
                        <tr>
                            <td colspan="6" class="iqr-empty-state">
                                <?php esc_html_e( 'No items yet. Click "Add Item" to create one.', 'inventario-qr' ); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div class="iqr-modal" id="iqr-item-modal" style="display:none;">
        <div class="iqr-modal-overlay"></div>
        <div class="iqr-modal-content">
            <div class="iqr-modal-header">
                <h3 id="iqr-modal-title"><?php esc_html_e( 'Add Item', 'inventario-qr' ); ?></h3>
                <button class="iqr-modal-close" id="iqr-modal-close">&times;</button>
            </div>
            <form id="iqr-item-form">
                <input type="hidden" id="iqr-item-id" value="">
                <div class="iqr-form-grid">
                    <div class="iqr-form-group">
                        <label for="iqr-item-name"><?php esc_html_e( 'Name', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-item-name" class="iqr-input" required>
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-sku"><?php esc_html_e( 'SKU', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-item-sku" class="iqr-input">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-category"><?php esc_html_e( 'Category', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-item-category" class="iqr-input">
                    </div>
                    <div class="iqr-form-group">
                        <label for="iqr-item-quantity"><?php esc_html_e( 'Quantity', 'inventario-qr' ); ?></label>
                        <input type="number" id="iqr-item-quantity" class="iqr-input" min="0" value="0">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-item-location"><?php esc_html_e( 'Location', 'inventario-qr' ); ?></label>
                        <input type="text" id="iqr-item-location" class="iqr-input">
                    </div>
                    <div class="iqr-form-group iqr-form-full">
                        <label for="iqr-item-description"><?php esc_html_e( 'Description', 'inventario-qr' ); ?></label>
                        <textarea id="iqr-item-description" class="iqr-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="iqr-modal-footer">
                    <button type="button" class="iqr-btn iqr-btn-secondary" id="iqr-modal-cancel"><?php esc_html_e( 'Cancel', 'inventario-qr' ); ?></button>
                    <button type="submit" class="iqr-btn iqr-btn-primary"><?php esc_html_e( 'Save', 'inventario-qr' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
