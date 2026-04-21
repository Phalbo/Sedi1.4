<?php
/**
 * FDR Sedi - Custom Post Type
 * Sviluppato da Ares 2.0 s.r.l. per Federconsumatori
 */

add_action('init', 'fdr_sedi_register_post_type');
function fdr_sedi_register_post_type() {
    register_post_type('fdr_sede', [
        'labels' => [
            'name'               => 'Sedi Federconsumatori',
            'singular_name'      => 'Sede',
            'add_new'            => 'Aggiungi sede',
            'add_new_item'       => 'Aggiungi nuova sede',
            'edit_item'          => 'Modifica sede',
            'new_item'           => 'Nuova sede',
            'view_item'          => 'Visualizza sede',
            'search_items'       => 'Cerca sede',
            'not_found'          => 'Nessuna sede trovata',
            'not_found_in_trash' => 'Nessuna sede nel cestino',
            'menu_name'          => 'Sedi',
            'all_items'          => 'Tutte le sedi',
        ],
        'public'             => true,
        'publicly_queryable' => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-location-alt',
        'supports'           => ['title'],
        'has_archive'        => false,
        'rewrite'            => ['slug' => 'sede'],
        'template'           => [],
    ]);

    register_taxonomy('fdr_regione', 'fdr_sede', [
        'labels' => [
            'name'          => 'Regioni',
            'singular_name' => 'Regione',
            'add_new_item'  => 'Aggiungi regione',
            'edit_item'     => 'Modifica regione',
            'search_items'  => 'Cerca regione',
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'regione-sede'],
    ]);
}

// Template pagina singola sede
add_filter('single_template', 'fdr_sede_single_template');
function fdr_sede_single_template($template) {
    if (is_singular('fdr_sede')) {
        $custom = FDR_SEDI_PATH . 'templates/single-sede.php';
        if (file_exists($custom)) return $custom;
    }
    return $template;
}

// ── RUOLO EDITOR SEDI ────────────────────────────────────────
add_action('init', 'fdr_sedi_register_role');
function fdr_sedi_register_role() {
    // Crea ruolo se non esiste
    if (!get_role('fdr_editor_sedi')) {
        add_role('fdr_editor_sedi', 'Editor Sedi Federconsumatori', [
            'read'                    => true,
            'edit_posts'              => false,
            'edit_fdr_sede'           => true,
            'edit_fdr_sedes'          => true,
            'edit_others_fdr_sedes'   => true,
            'publish_fdr_sedes'       => true,
            'read_private_fdr_sedes'  => true,
            'delete_fdr_sedes'        => false,
            'upload_files'            => true,  // per caricare logo
        ]);
    }
}

// Capabilities custom per il CPT
add_filter('map_meta_cap', 'fdr_sedi_map_caps', 10, 4);
function fdr_sedi_map_caps($caps, $cap, $user_id, $args) {
    if (in_array($cap, ['edit_fdr_sede', 'edit_fdr_sedes', 'edit_others_fdr_sedes', 'publish_fdr_sedes'])) {
        $role = get_userdata($user_id);
        if ($role && in_array('fdr_editor_sedi', $role->roles)) {
            return ['exist'];
        }
    }
    return $caps;
}

// Nascondi tutto il menu admin tranne Sedi per il ruolo editor sedi
add_action('admin_menu', 'fdr_sedi_restrict_menu', 999);
function fdr_sedi_restrict_menu() {
    if (!current_user_can('manage_options') && current_user_can('edit_fdr_sedes')) {
        global $menu, $submenu;
        $allowed = ['edit.php?post_type=fdr_sede'];
        foreach ($menu as $key => $item) {
            if (!in_array($item[2], $allowed)) {
                remove_menu_page($item[2]);
            }
        }
    }
}
