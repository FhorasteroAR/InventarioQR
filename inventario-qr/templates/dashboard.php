<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
?>
<div id="iqr-app" class="iqr-app">
    <!-- Sidebar -->
    <aside class="iqr-sidebar">
        <div class="iqr-sidebar-top">
            <div class="iqr-logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="14" y="14" width="4" height="4" rx="1" stroke="currentColor" stroke-width="2"/>
                    <line x1="21" y1="14" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="14" y1="21" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>

            <nav class="iqr-nav">
                <a href="#" class="iqr-nav-item active" data-section="qr" title="<?php esc_attr_e( 'QR Codes', 'inventario-qr' ); ?>">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="3" height="3" rx="0.5"/>
                        <line x1="21" y1="14" x2="21" y2="21"/>
                        <line x1="14" y1="21" x2="21" y2="21"/>
                    </svg>
                    <span class="iqr-nav-label"><?php esc_html_e( 'QR', 'inventario-qr' ); ?></span>
                </a>

                <a href="#" class="iqr-nav-item" data-section="inventory" title="<?php esc_attr_e( 'Inventory', 'inventario-qr' ); ?>">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4M4 7l8 4M4 7v10l8 4m0-10v10"/>
                    </svg>
                    <span class="iqr-nav-label"><?php esc_html_e( 'Inventory', 'inventario-qr' ); ?></span>
                </a>

                <a href="#" class="iqr-nav-item" data-section="export-import" title="<?php esc_attr_e( 'Export / Import', 'inventario-qr' ); ?>">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12M3 15l9 6 9-6"/>
                        <path d="M8 7l4-4 4 4"/>
                    </svg>
                    <span class="iqr-nav-label"><?php esc_html_e( 'Export/Import', 'inventario-qr' ); ?></span>
                </a>

                <a href="#" class="iqr-nav-item" data-section="defaults" title="<?php esc_attr_e( 'Defaults', 'inventario-qr' ); ?>">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
                    </svg>
                    <span class="iqr-nav-label"><?php esc_html_e( 'Defaults', 'inventario-qr' ); ?></span>
                </a>
            </nav>
        </div>

        <div class="iqr-sidebar-bottom">
            <a href="#" class="iqr-nav-item" data-section="user" title="<?php echo esc_attr( $current_user->display_name ); ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="iqr-nav-label"><?php echo esc_html( $current_user->display_name ); ?></span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="iqr-main">
        <header class="iqr-header">
            <div class="iqr-header-left">
                <h1 class="iqr-page-title" id="iqr-page-title"><?php esc_html_e( 'QR Codes', 'inventario-qr' ); ?></h1>
            </div>
            <div class="iqr-header-right">
                <div class="iqr-search-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="iqr-search" placeholder="<?php esc_attr_e( 'Search...', 'inventario-qr' ); ?>">
                </div>
                <div class="iqr-user-badge">
                    <span class="iqr-user-avatar">
                        <?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="iqr-content" id="iqr-content">
            <!-- Content loaded dynamically -->
        </div>
    </main>
</div>
