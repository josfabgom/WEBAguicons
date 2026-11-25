<?php
/**
 * Permiso propio del panel ("agui_manage") y rol "Gestor Aguicons": una persona que administra solo
 * lo de Aguicons (líneas, consultas, estructura, ajustes, visitas) sin acceder a plugins, temas,
 * usuarios ni ajustes generales de WordPress.
 */

if (!defined('ABSPATH')) {
    exit;
}

const AGUI_ROLES_VER = '1';

add_action('init', function () {
    if (get_option('agui_roles_ver') === AGUI_ROLES_VER) {
        return;
    }
    if ($admin = get_role('administrator')) {
        $admin->add_cap('agui_manage');
        $admin->add_cap('agui_view_stats');
    }
    // Base: lo que puede hacer un Editor (crear y editar contenido, subir fotos) + el panel de Aguicons.
    $editor = get_role('editor');
    $caps = $editor ? $editor->capabilities : ['read' => true, 'upload_files' => true, 'edit_posts' => true];
    $caps['agui_manage'] = true;
    $caps['agui_view_stats'] = true;
    remove_role('agui_gestor');
    add_role('agui_gestor', 'Gestor Aguicons (solo el panel de Aguicons)', $caps);
    update_option('agui_roles_ver', AGUI_ROLES_VER, false);
});

/** Guardar "Ajustes" del panel también con el permiso propio. */
add_filter('option_page_capability_aguicons', fn() => 'agui_manage');

/** Al gestor solo le queda el menú de Aguicons y la biblioteca de fotos; entra directo al Panel. */
function agui_is_gestor(): bool
{
    return current_user_can('agui_manage') && !current_user_can('manage_options');
}

add_action('admin_menu', function () {
    if (!agui_is_gestor()) {
        return;
    }
    foreach (['index.php', 'edit.php', 'edit-comments.php', 'tools.php', 'edit.php?post_type=page', 'edit.php?post_type=elementor_library'] as $slug) {
        remove_menu_page($slug);
    }
}, 999);

add_filter('login_redirect', function ($to, $req, $user) {
    return ($user instanceof WP_User && user_can($user, 'agui_manage') && !user_can($user, 'manage_options')) ? admin_url('admin.php?page=' . AGUI_PANEL) : $to;
}, 5, 3);

add_action('admin_init', function () {
    global $pagenow;
    if (agui_is_gestor() && $pagenow === 'index.php' && !wp_doing_ajax()) {
        wp_safe_redirect(admin_url('admin.php?page=' . AGUI_PANEL));
        exit;
    }
});
