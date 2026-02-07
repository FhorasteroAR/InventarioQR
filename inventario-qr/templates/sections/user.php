<?php if ( ! defined( 'ABSPATH' ) ) exit;
$user = wp_get_current_user();
?>

<div class="iqr-section iqr-section-user">
    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2>Perfil</h2>
        </div>
        <div class="iqr-panel-body">
            <div class="iqr-user-profile">
                <div class="iqr-user-avatar-large">
                    <?php echo esc_html( strtoupper( substr( $user->display_name, 0, 1 ) ) ); ?>
                </div>
                <div class="iqr-user-details">
                    <h3><?php echo esc_html( $user->display_name ); ?></h3>
                    <span class="iqr-user-role-badge">Administrador</span>
                    <p class="iqr-text-muted"><?php echo esc_html( $user->user_email ); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="iqr-panel">
        <div class="iqr-panel-header">
            <h2>Sesión</h2>
        </div>
        <div class="iqr-panel-body">
            <button class="iqr-btn iqr-btn-danger" id="iqr-logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Cerrar sesión
            </button>
        </div>
    </div>
</div>
