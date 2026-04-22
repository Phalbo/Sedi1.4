<?php
/**
 * Plugin Name:  FDR Sedi — Federconsumatori
 * Plugin URI:   https://www.ares20.it
 * Description:  Gestione sedi Federconsumatori con mappa interattiva, ricerca, filtro regionale e pagine sede dedicate. Plugin sviluppato su misura da Ares 2.0 s.r.l.
 * Version:      1.6
 * Author:       Ares 2.0 s.r.l.
 * Author URI:   https://www.ares20.it
 * Text Domain:  fdr-sedi
 */

if (!defined('ABSPATH')) exit;

define('FDR_SEDI_PATH', plugin_dir_path(__FILE__));
define('FDR_SEDI_URL',  plugin_dir_url(__FILE__));

// Link README nella lista plugin
add_filter('plugin_row_meta', 'fdr_sedi_plugin_row_meta', 10, 2);
function fdr_sedi_plugin_row_meta($links, $file) {
    if (strpos($file, 'fdr-sedi.php') !== false) {
        $links[] = '<a href="' . admin_url('admin.php?page=fdr-sedi-readme') . '" style="color:#004A99;font-weight:600">📖 Documentazione</a>';
        $links[] = '<a href="https://www.ares20.it" target="_blank" style="color:#004A99">Ares 2.0 s.r.l.</a>';
    }
    return $links;
}

require_once FDR_SEDI_PATH . 'includes/utils.php';
require_once FDR_SEDI_PATH . 'includes/post-type.php';
require_once FDR_SEDI_PATH . 'includes/meta-boxes.php';
require_once FDR_SEDI_PATH . 'includes/shortcode.php';
require_once FDR_SEDI_PATH . 'includes/import.php';
require_once FDR_SEDI_PATH . 'includes/export.php';
require_once FDR_SEDI_PATH . 'includes/admin-page.php';

register_activation_hook(__FILE__, 'fdr_sedi_activate');
function fdr_sedi_activate() {
    fdr_sedi_register_post_type();
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'fdr_sedi_deactivate');
function fdr_sedi_deactivate() {
    flush_rewrite_rules();
}
