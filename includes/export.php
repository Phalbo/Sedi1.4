<?php
/**
 * FDR Sedi — Export CSV
 * Sviluppato da Ares 2.0 s.r.l. per Federconsumatori
 */

add_action('admin_post_fdr_sedi_export', 'fdr_sedi_process_export');
function fdr_sedi_process_export() {
    if (!current_user_can('manage_options')) wp_die('Accesso negato');
    if (!isset($_POST['fdr_export_nonce']) || !wp_verify_nonce($_POST['fdr_export_nonce'], 'fdr_export')) wp_die('Nonce non valido');

    $posts = get_posts([
        'post_type'      => 'fdr_sede',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $cols = [
        'name', 'company', 'address1', 'zip', 'city', 'region',
        'telephone', 'mobile', 'fax', 'email', 'website', 'description',
        'lat', 'lng', 'premium', 'nazionale', 'pubblica',
        'Monday_open', 'Monday_close', 'Monday_open2', 'Monday_close2',
        'Tuesday_open', 'Tuesday_close', 'Tuesday_open2', 'Tuesday_close2',
        'Wednesday_open', 'Wednesday_close', 'Wednesday_open2', 'Wednesday_close2',
        'Thursday_open', 'Thursday_close', 'Thursday_open2', 'Thursday_close2',
        'Friday_open', 'Friday_close', 'Friday_open2', 'Friday_close2',
        'Saturday_open', 'Saturday_close', 'Saturday_open2', 'Saturday_close2',
        'Sunday_open', 'Sunday_close', 'Sunday_open2', 'Sunday_close2',
    ];

    $giorni = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    $filename = 'fdr-sedi-export-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, $cols);

    foreach ($posts as $p) {
        $id = $p->ID;
        $region_terms = get_the_terms($id, 'fdr_regione');
        $region = '';
        if ($region_terms && !is_wp_error($region_terms)) {
            $region = $region_terms[0]->name;
        } else {
            $region = get_post_meta($id, '_fdr_region', true);
        }

        $row = [
            'name'        => $p->post_title,
            'company'     => get_post_meta($id, '_fdr_company',     true),
            'address1'    => get_post_meta($id, '_fdr_address',     true),
            'zip'         => get_post_meta($id, '_fdr_zip',         true),
            'city'        => get_post_meta($id, '_fdr_city',        true),
            'region'      => $region,
            'telephone'   => get_post_meta($id, '_fdr_telephone',   true),
            'mobile'      => get_post_meta($id, '_fdr_mobile',      true),
            'fax'         => get_post_meta($id, '_fdr_fax',         true),
            'email'       => get_post_meta($id, '_fdr_email',       true),
            'website'     => get_post_meta($id, '_fdr_website',     true),
            'description' => get_post_meta($id, '_fdr_description', true),
            'lat'         => get_post_meta($id, '_fdr_lat',         true),
            'lng'         => get_post_meta($id, '_fdr_lng',         true),
            'premium'     => get_post_meta($id, '_fdr_premium',     true),
            'nazionale'   => get_post_meta($id, '_fdr_nazionale',   true),
            'pubblica'    => get_post_meta($id, '_fdr_pubblica',    true),
        ];

        foreach ($giorni as $g) {
            foreach (['_open', '_close', '_open2', '_close2'] as $suffix) {
                $row[$g . $suffix] = get_post_meta($id, '_fdr_' . $g . $suffix, true);
            }
        }

        fputcsv($out, array_map(function($v) { return $v === false ? '' : $v; }, $row));
    }

    fclose($out);
    exit;
}

// ── CANCELLA TUTTE ─────────────────────────────────────────────
add_action('admin_post_fdr_sedi_delete_all', 'fdr_sedi_process_delete_all');
function fdr_sedi_process_delete_all() {
    if (!current_user_can('manage_options')) wp_die('Accesso negato');
    if (!isset($_POST['fdr_delete_all_nonce']) || !wp_verify_nonce($_POST['fdr_delete_all_nonce'], 'fdr_delete_all')) wp_die('Nonce non valido');

    $confirm = isset($_POST['fdr_delete_confirm']) ? sanitize_text_field($_POST['fdr_delete_confirm']) : '';
    if ($confirm !== 'CANCELLA') {
        wp_redirect(admin_url('admin.php?page=fdr-sedi-import&delete_error=confirm'));
        exit;
    }

    $posts = get_posts([
        'post_type'      => 'fdr_sede',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ]);

    $deleted = 0;
    foreach ($posts as $id) {
        if (wp_delete_post($id, true)) $deleted++;
    }

    // Svuota cache transient
    delete_transient('fdr_sedi_json');

    wp_redirect(admin_url('admin.php?page=fdr-sedi-import&deleted=' . $deleted));
    exit;
}
