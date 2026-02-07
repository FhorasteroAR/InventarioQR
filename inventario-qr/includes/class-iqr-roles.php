<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IQR_Roles {

    public static function add_roles() {
        add_role(
            'iqr_admin',
            'Admin Inventario',
            array(
                'read'                 => true,
                'iqr_manage_inventory' => true,
                'iqr_manage_qr'        => true,
                'iqr_export_import'    => true,
                'iqr_manage_defaults'  => true,
                'iqr_manage_users'     => true,
            )
        );

        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->add_cap( 'iqr_manage_inventory' );
            $admin->add_cap( 'iqr_manage_qr' );
            $admin->add_cap( 'iqr_export_import' );
            $admin->add_cap( 'iqr_manage_defaults' );
            $admin->add_cap( 'iqr_manage_users' );
        }
    }

    public static function remove_roles() {
        remove_role( 'iqr_admin' );

        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->remove_cap( 'iqr_manage_inventory' );
            $admin->remove_cap( 'iqr_manage_qr' );
            $admin->remove_cap( 'iqr_export_import' );
            $admin->remove_cap( 'iqr_manage_defaults' );
            $admin->remove_cap( 'iqr_manage_users' );
        }
    }
}
