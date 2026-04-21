<?php
/**
 * FDR Sedi — Admin pages: Import, Export, Documentazione
 * Sviluppato da Ares 2.0 s.r.l. per Federconsumatori
 */

add_action('admin_menu', 'fdr_sedi_admin_menu');
function fdr_sedi_admin_menu() {
    add_submenu_page('edit.php?post_type=fdr_sede', 'Importa / Esporta Sedi', 'Importa / Esporta CSV', 'manage_options', 'fdr-sedi-import', 'fdr_sedi_import_page');
}

function fdr_sedi_import_page() {
    $imported     = isset($_GET['imported'])     ? intval($_GET['imported'])     : null;
    $skipped      = isset($_GET['skipped'])      ? intval($_GET['skipped'])      : 0;
    $error        = isset($_GET['error'])        ? intval($_GET['error'])        : null;
    $deleted      = isset($_GET['deleted'])      ? intval($_GET['deleted'])      : null;
    $delete_error = isset($_GET['delete_error']) ? sanitize_text_field($_GET['delete_error']) : null;
    ?>
    <div class="wrap">
        <h1>📍 Importa / Esporta Sedi</h1>

        <?php if ($imported !== null): ?>
        <div class="notice notice-success"><p>✅ Importate <strong><?php echo $imported; ?></strong> sedi. Saltate: <?php echo $skipped; ?>.</p></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="notice notice-error"><p>❌ Errore nell'importazione. Assicurati di caricare un file CSV valido.</p></div>
        <?php endif; ?>

        <?php if ($deleted !== null): ?>
        <div class="notice notice-warning"><p>🗑️ Cancellate <strong><?php echo $deleted; ?></strong> sedi dal database.</p></div>
        <?php endif; ?>

        <?php if ($delete_error === 'confirm'): ?>
        <div class="notice notice-error"><p>❌ Cancellazione annullata: il codice di conferma non era corretto.</p></div>
        <?php endif; ?>

        <!-- ── IMPORTA ── -->
        <div style="background:white;padding:24px;border-radius:8px;max-width:700px;margin-top:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1)">
            <h2 style="margin-top:0">⬆️ Importa da CSV</h2>
            <p style="color:#666;margin-bottom:16px">Il file CSV deve avere le colonne: <code>name, company, address1, zip, city, region, telephone, email, website, lat, lng</code> più le colonne degli orari (<code>Monday_open</code>, <code>Monday_close</code> ecc.). Usa il CSV esportato da questo plugin come template.</p>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="fdr_sedi_import">
                <?php wp_nonce_field('fdr_import', 'fdr_import_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th>File CSV</th>
                        <td><input type="file" name="fdr_import_file" accept=".csv" required></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button button-primary" value="⬆️ Importa sedi"></p>
            </form>
        </div>

        <!-- ── ESPORTA ── -->
        <div style="background:white;padding:24px;border-radius:8px;max-width:700px;margin-top:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1)">
            <h2 style="margin-top:0">⬇️ Esporta tutte le sedi</h2>
            <p style="color:#666;margin-bottom:16px">Scarica tutte le sedi in formato CSV. Il file contiene tutti i campi nel formato compatibile con il re-import. Tienilo come backup prima di qualsiasi operazione massiva.</p>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="fdr_sedi_export">
                <?php wp_nonce_field('fdr_export', 'fdr_export_nonce'); ?>
                <p class="submit"><input type="submit" class="button button-secondary" value="⬇️ Esporta CSV" style="background:#004A99;color:white;border-color:#003a7a"></p>
            </form>
        </div>

        <!-- ── SHORTCODE ── -->
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

        <!-- ── ZONA PERICOLOSA ── -->
        <div style="margin-top:40px;max-width:700px">
            <details style="border:1px solid #ccc;border-radius:8px;overflow:hidden">
                <summary style="padding:12px 16px;cursor:pointer;color:#666;font-size:13px;background:#f9f9f9;list-style:none;user-select:none">
                    ▸ Zona avanzata — operazioni irreversibili
                </summary>
                <div style="padding:20px;background:white">
                    <div style="background:#fff0f0;border:2px solid #d63638;border-radius:8px;padding:18px">
                        <h3 style="color:#d63638;margin:0 0 10px 0;font-size:15px">🗑️ Cancella TUTTE le sedi</h3>
                        <p style="color:#555;font-size:13px;margin-bottom:14px">
                            Questa operazione <strong>elimina definitivamente tutte le sedi dal database</strong>. Non è reversibile.<br>
                            <strong>Prima di procedere esporta il CSV.</strong> Una volta cancellate le sedi non è possibile recuperarle.
                        </p>

                        <!-- Step 1: Export forced reminder -->
                        <div style="background:#fff8e1;border:1px solid #ffb300;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px">
                            ⚠️ <strong>Hai già esportato il backup?</strong> Usa il pulsante "Esporta CSV" qui sopra prima di continuare.
                        </div>

                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>"
                              onsubmit="return fdrConfirmDelete(this)">
                            <input type="hidden" name="action" value="fdr_sedi_delete_all">
                            <?php wp_nonce_field('fdr_delete_all', 'fdr_delete_all_nonce'); ?>
                            <p style="font-size:13px;margin-bottom:6px">
                                Per confermare digita <strong>CANCELLA</strong> nel campo qui sotto:
                            </p>
                            <input type="text" name="fdr_delete_confirm" id="fdrDeleteConfirm"
                                   placeholder="CANCELLA"
                                   style="border:2px solid #d63638;border-radius:4px;padding:6px 10px;font-size:14px;width:180px">
                            <br><br>
                            <input type="submit" class="button" value="🗑️ Cancella tutte le sedi"
                                   style="background:#d63638;color:white;border-color:#b32d2e;font-weight:600">
                        </form>
                    </div>
                </div>
            </details>
        </div>

        <script>
        function fdrConfirmDelete(form) {
            var val = document.getElementById('fdrDeleteConfirm').value;
            if (val !== 'CANCELLA') {
                alert('Devi digitare esattamente CANCELLA per confermare.');
                return false;
            }
            if (!confirm('⚠️ ATTENZIONE\n\nStai per cancellare TUTTE le sedi dal database.\nQuesta operazione è IRREVERSIBILE.\n\nHai esportato il CSV di backup?\n\nPremi OK solo se sei assolutamente sicuro.')) {
                return false;
            }
            return confirm('✋ ULTIMA CONFERMA\n\nSei sicuro di voler cancellare tutte le sedi?\nNon sarà possibile recuperarle senza un backup CSV.\n\nPremi OK per procedere definitivamente.');
        }
        </script>

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
