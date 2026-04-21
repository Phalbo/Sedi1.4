<?php
add_action('admin_menu', 'fdr_sedi_admin_menu');
function fdr_sedi_admin_menu() {
    add_submenu_page('edit.php?post_type=fdr_sede', 'Importa Sedi', 'Importa CSV', 'manage_options', 'fdr-sedi-import', 'fdr_sedi_import_page');
}

function fdr_sedi_import_page() {
    $imported = isset($_GET['imported']) ? intval($_GET['imported']) : null;
    $skipped  = isset($_GET['skipped'])  ? intval($_GET['skipped'])  : 0;
    $error    = isset($_GET['error'])    ? intval($_GET['error'])    : null;
    ?>
    <div class="wrap">
        <h1>📍 Importa Sedi da CSV</h1>
        
        <?php if ($imported !== null): ?>
        <div class="notice notice-success"><p>✅ Importate <strong><?php echo $imported; ?></strong> sedi. Saltate: <?php echo $skipped; ?>.</p></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="notice notice-error"><p>❌ Errore nell'importazione. Assicurati di caricare un file CSV valido.</p></div>
        <?php endif; ?>
        
        <div style="background:white;padding:24px;border-radius:8px;max-width:700px;margin-top:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1)">
            <h2 style="margin-top:0">Carica file CSV</h2>
            <p style="color:#666;margin-bottom:16px">Il file CSV deve avere le stesse colonne dell'export dello Store Locator precedente: <code>name, company, address1, zip, city, region, telephone, email, website, lat, lng</code> più le colonne degli orari.</p>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="fdr_sedi_import">
                <?php wp_nonce_field('fdr_import', 'fdr_import_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th>File CSV</th>
                        <td><input type="file" name="fdr_import_file" accept=".csv" required></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="Importa sedi"></p>
            </form>
        </div>
        
        <div style="background:white;padding:24px;border-radius:8px;max-width:700px;margin-top:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1)">
            <h2 style="margin-top:0">Come usare la mappa sul sito</h2>
            <p>Aggiungi questo shortcode in qualsiasi pagina o widget:</p>
            <code style="display:block;background:#f5f5f5;padding:12px;border-radius:4px;font-size:15px;margin:8px 0">[fdr_sedi]</code>
            <p>Per mostrare solo le sedi di una regione specifica:</p>
            <code style="display:block;background:#f5f5f5;padding:12px;border-radius:4px;font-size:15px;margin:8px 0">[fdr_sedi regione="lazio"]</code>
            <p style="color:#666;font-size:13px;margin-top:12px">💡 Lo slug della regione è il nome in minuscolo con trattini al posto degli spazi (es. "emilia-romagna").</p>
        </div>
        
        <div style="background:#fff3cd;padding:16px;border-radius:8px;max-width:700px;margin-top:16px;border-left:4px solid #FDC513">
            <strong>📋 Istruzioni per convertire l'XLSX in CSV:</strong>
            <ol style="margin:8px 0 0 16px;color:#555">
                <li>Apri il file XLSX in Excel o LibreOffice</li>
                <li>File → Salva come → CSV (delimitato da virgole)</li>
                <li>Carica il file CSV qui sopra</li>
            </ol>
        </div>
    </div>
    <?php
}

// ── PAGINA README ─────────────────────────────────────────────
add_action('admin_menu', 'fdr_sedi_readme_menu');
function fdr_sedi_readme_menu() {
    add_submenu_page(
        'edit.php?post_type=fdr_sede',
        'Documentazione FDR Sedi',
        '📖 Documentazione',
        'manage_options',
        'fdr-sedi-readme',
        'fdr_sedi_readme_page'
    );
}

function fdr_sedi_readme_page() {
    $readme = FDR_SEDI_PATH . 'README.md';
    $content = file_exists($readme) ? file_get_contents($readme) : 'README non trovato.';
    // Converti markdown base in HTML
    $content = htmlspecialchars($content);
    $content = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $content);
    $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
    $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
    $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
    $content = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $content);
    $content = preg_replace('/^---$/m', '<hr>', $content);
    $content = preg_replace('/^- (.+)$/m', '<li>$1</li>', $content);
    $content = nl2br($content);
    ?>
    <div class="wrap" style="max-width:800px">
        <div style="background:white;padding:30px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.1);font-family:'Inter',Arial,sans-serif;line-height:1.7">
            <div style="background:#004A99;color:white;padding:16px 20px;border-radius:6px;margin-bottom:24px;display:flex;align-items:center;gap:12px">
                <span style="font-size:24px">📍</span>
                <div>
                    <strong style="font-size:16px">FDR Sedi — Documentazione</strong><br>
                    <small>Sviluppato da Ares 2.0 s.r.l. per Federconsumatori</small>
                </div>
            </div>
            <?php echo $content; ?>
        </div>
    </div>
    <?php
}
