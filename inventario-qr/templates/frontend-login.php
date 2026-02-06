<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="iqr-login-wrapper">
    <div class="iqr-login-card">
        <div class="iqr-login-logo">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                <rect x="14" y="14" width="4" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                <line x1="21" y1="14" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <line x1="14" y1="21" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <h2><?php esc_html_e( 'Inventario QR', 'inventario-qr' ); ?></h2>
        <p class="iqr-login-subtitle"><?php esc_html_e( 'Sign in to access the inventory system', 'inventario-qr' ); ?></p>

        <form id="iqr-login-form" class="iqr-login-form">
            <div class="iqr-form-group">
                <label for="iqr-login-user"><?php esc_html_e( 'Username', 'inventario-qr' ); ?></label>
                <input type="text" id="iqr-login-user" class="iqr-input" required autocomplete="username">
            </div>
            <div class="iqr-form-group">
                <label for="iqr-login-pass"><?php esc_html_e( 'Password', 'inventario-qr' ); ?></label>
                <input type="password" id="iqr-login-pass" class="iqr-input" required autocomplete="current-password">
            </div>
            <div class="iqr-login-error" id="iqr-login-error" style="display:none;"></div>
            <button type="submit" class="iqr-btn iqr-btn-primary iqr-btn-full" id="iqr-login-submit">
                <?php esc_html_e( 'Sign In', 'inventario-qr' ); ?>
            </button>
        </form>
    </div>
</div>
