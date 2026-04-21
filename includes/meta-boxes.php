<?php
/**
 * FDR Sedi - Meta Boxes
 * Sviluppato da Ares 2.0 s.r.l. per Federconsumatori
 */

add_action('add_meta_boxes', 'fdr_sedi_meta_boxes');
function fdr_sedi_meta_boxes() {
    add_meta_box('fdr_sede_pubblica', '🌐 Pubblicazione sul sito', 'fdr_sede_pubblica_callback', 'fdr_sede', 'side', 'high');
    add_meta_box('fdr_sede_map',      '📍 Posizione e tipo sede',  'fdr_sede_map_callback',      'fdr_sede', 'side', 'default');
    add_meta_box('fdr_sede_info',     'ℹ️ Informazioni sede',      'fdr_sede_info_callback',     'fdr_sede', 'normal', 'high');
    add_meta_box('fdr_sede_logo',     '🖼️ Logo sede',              'fdr_sede_logo_callback',     'fdr_sede', 'normal', 'default');
    add_meta_box('fdr_sede_extra',    '📝 Contenuto aggiuntivo',   'fdr_sede_extra_callback',    'fdr_sede', 'normal', 'default');
    add_meta_box('fdr_sede_orari',    '🕐 Orari di apertura',      'fdr_sede_orari_callback',    'fdr_sede', 'normal', 'low');
}

// ── PUBBLICAZIONE ──────────────────────────────────────────────
function fdr_sede_pubblica_callback($post) {
    wp_nonce_field('fdr_sede_save', 'fdr_sede_nonce');
    $pubblica = (int) get_post_meta($post->ID, '_fdr_pubblica', true);
    ?>
    <?php if ($pubblica): ?>
    <div style="background:#e8f5e9;border-radius:8px;padding:14px;border:2px solid #4caf50;margin-bottom:10px">
        <strong style="color:#2e7d32;font-size:13px">✅ Questa sede ha una pagina pubblica sul sito</strong><br>
        <small style="color:#555">I visitatori possono vedere la pagina e il nome appare cliccabile nella mappa.</small>
    </div>
    <?php else: ?>
    <div style="background:#fff8e1;border-radius:8px;padding:14px;border:2px solid #ffb300;margin-bottom:10px">
        <strong style="color:#e65100;font-size:13px">⚠️ Questa sede non ha ancora una pagina pubblica</strong><br>
        <small style="color:#555">La sede appare nella mappa ma non ha una pagina dedicata visitabile. Se vuoi creare la pagina pubblica, spunta la casella qui sotto.</small>
    </div>
    <?php endif; ?>
    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;background:white;border:2px solid #004A99;border-radius:8px;padding:12px">
        <input type="checkbox" name="fdr_pubblica" value="1" <?php checked($pubblica, 1); ?> style="width:20px;height:20px;margin-top:2px;flex-shrink:0">
        <span>
            <strong style="font-size:13px;display:block;margin-bottom:3px;color:#004A99">
                Crea la pagina pubblica per questa sede
            </strong>
            <small style="color:#666;line-height:1.5">
                Spuntando questa casella, verrà creata una pagina visitabile con tutte le informazioni della sede (indirizzo, orari, mappa, contatti). Il nome della sede nella mappa generale diventerà cliccabile.<br><br>
                <strong>Nota:</strong> questa opzione riguarda solo la pagina di dettaglio. La sede appare sempre nella mappa principale, indipendentemente da questa scelta.
            </small>
        </span>
    </label>
    <?php
}

// ── INFORMAZIONI ──────────────────────────────────────────────
function fdr_sede_info_callback($post) {
    $fields = ['company','address','zip','city','telephone','mobile','fax','email','website','description'];
    $labels = ['Nome esteso / Associazione','Indirizzo','CAP','Città','Telefono','Cellulare','Fax','Email','Sito web','Note interne'];
    echo '<table style="width:100%;border-collapse:collapse">';
    foreach ($fields as $i => $f) {
        $val  = get_post_meta($post->ID, '_fdr_'.$f, true);
        $type = $f === 'email' ? 'email' : ($f === 'website' ? 'url' : 'text');
        echo '<tr><td style="padding:6px 8px;width:160px;font-weight:600;color:#444;vertical-align:top;padding-top:10px">'.$labels[$i].'</td>';
        if ($f === 'description') {
            echo '<td style="padding:6px 8px"><textarea name="fdr_'.$f.'" rows="2" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px">'.esc_textarea($val).'</textarea>'
               . '<p style="margin:4px 0 0;font-size:11px;color:#888">💡 Per gli orari usa la sezione <strong>Orari di apertura</strong> qui sotto — non inserirli nelle note per evitare problemi di visualizzazione sul sito.</p>'
               . '</td></tr>';
        } else {
            echo '<td style="padding:6px 8px"><input type="'.$type.'" name="fdr_'.$f.'" value="'.esc_attr($val).'" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></td></tr>';
        }
    }
    echo '</table>';
}

// ── LOGO ──────────────────────────────────────────────────────
function fdr_sede_logo_callback($post) {
    $logo_id = get_post_meta($post->ID, '_fdr_logo_id', true);
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
    ?>
    <p style="color:#555;font-size:13px;margin-bottom:12px">
        Carica il logo ufficiale della sede. <strong>Dimensione consigliata: 400×200 px</strong> (formato orizzontale, sfondo trasparente o bianco). Il logo apparirà nella pagina pubblica della sede.
    </p>
    <div id="fdr-logo-preview" style="margin-bottom:12px">
        <?php if ($logo_url): ?>
            <img src="<?php echo esc_url($logo_url); ?>" style="max-width:200px;max-height:100px;border:1px solid #ddd;border-radius:6px;padding:6px">
        <?php endif; ?>
    </div>
    <input type="hidden" name="fdr_logo_id" id="fdr_logo_id" value="<?php echo esc_attr($logo_id); ?>">
    <button type="button" id="fdr-upload-logo" class="button" style="background:#004A99;color:white;border-color:#004A99">
        <?php echo $logo_id ? '🔄 Cambia logo' : '📤 Carica logo'; ?>
    </button>
    <?php if ($logo_id): ?>
        <button type="button" id="fdr-remove-logo" class="button" style="margin-left:8px;color:#d63638;border-color:#d63638">✕ Rimuovi</button>
    <?php endif; ?>
    <script>
    jQuery(function($) {
        var frame;
        $('#fdr-upload-logo').on('click', function() {
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'Seleziona logo sede', button: { text: 'Usa questo logo' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                $('#fdr_logo_id').val(att.id);
                $('#fdr-logo-preview').html('<img src="' + (att.sizes.medium ? att.sizes.medium.url : att.url) + '" style="max-width:200px;max-height:100px;border:1px solid #ddd;border-radius:6px;padding:6px">');
                $('#fdr-upload-logo').text('🔄 Cambia logo');
            });
            frame.open();
        });
        $('#fdr-remove-logo').on('click', function() {
            $('#fdr_logo_id').val('');
            $('#fdr-logo-preview').html('');
            $(this).hide();
            $('#fdr-upload-logo').text('📤 Carica logo');
        });
    });
    </script>
    <p style="font-size:11px;color:#999;margin-top:10px">Il campo logo non è obbligatorio. Se non presente, nella pagina pubblica comparirà solo il nome della sede.</p>
    <?php
}

// ── CONTENUTO AGGIUNTIVO ─────────────────────────────────────
function fdr_sede_extra_callback($post) {
    $extra = get_post_meta($post->ID, '_fdr_extra', true);
    wp_editor($extra, 'fdr_extra_editor', [
        'textarea_name' => 'fdr_extra',
        'media_buttons' => true,
        'textarea_rows' => 6,
        'teeny'         => true,  // editor semplificato: no font, no stili avanzati
        'tinymce'       => [
            'toolbar1' => 'bold,italic,underline,bullist,numlist,link,image,undo,redo',
            'toolbar2' => '',
        ],
    ]);
    echo '<p style="font-size:12px;color:#888;margin-top:8px">Puoi inserire testo formattato e immagini. Gli strumenti avanzati (font, colori, ecc.) sono deliberatamente disabilitati per mantenere la coerenza visiva del sito Federconsumatori.</p>';
}

// ── POSIZIONE E TIPO ─────────────────────────────────────────
function fdr_sede_map_callback($post) {
    $lat       = get_post_meta($post->ID, '_fdr_lat',       true);
    $lng       = get_post_meta($post->ID, '_fdr_lng',       true);
    $regione   = get_post_meta($post->ID, '_fdr_region',    true);
    $premium   = (int) get_post_meta($post->ID, '_fdr_premium',   true);
    $nazionale = (int) get_post_meta($post->ID, '_fdr_nazionale', true);
    ?>
    <p>
        <strong>Latitudine</strong><br>
        <input type="text" name="fdr_lat" value="<?php echo esc_attr($lat); ?>" placeholder="es. 41.9028" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;margin-top:4px">
    </p>
    <p>
        <strong>Longitudine</strong><br>
        <input type="text" name="fdr_lng" value="<?php echo esc_attr($lng); ?>" placeholder="es. 12.4964" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;margin-top:4px">
    </p>
    <p style="font-size:12px;color:#555;margin-top:6px;background:#f9f9f9;padding:8px;border-radius:4px">
        📌 Non conosci le coordinate? Cercale qui:<br>
        <a href="https://www.latlong.net/" target="_blank" style="color:#004A99;font-weight:600">→ LatLong.net</a> &nbsp;|&nbsp;
        <a href="https://nominatim.openstreetmap.org/" target="_blank" style="color:#004A99;font-weight:600">→ OpenStreetMap</a>
    </p>
    <hr style="margin:12px 0;border-color:#eee">
    <p style="font-weight:600;color:#004A99;margin-bottom:8px">Tipo di sede</p>
    <p>
        <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;margin-bottom:10px">
            <input type="checkbox" name="fdr_premium" value="1" <?php checked($premium, 1); ?> style="width:16px;height:16px;margin-top:3px;flex-shrink:0">
            <span><strong>Sede Regionale</strong><br><small style="color:#666;font-weight:normal">Evidenziata in blu nella mappa e nella lista sedi</small></span>
        </label>
    </p>
    <p>
        <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer">
            <input type="checkbox" name="fdr_nazionale" value="1" <?php checked($nazionale, 1); ?> style="width:16px;height:16px;margin-top:3px;flex-shrink:0">
            <span><strong>Sede Nazionale</strong><br><small style="color:#666;font-weight:normal">★ Sempre in cima, evidenziata in giallo. Una sola sede dovrebbe avere questa opzione.</small></span>
        </label>
    </p>
    <p style="font-size:11px;color:#999;margin-top:12px;border-top:1px solid #eee;padding-top:8px">
        Plugin FDR Sedi v1.3 — <strong>Ares 2.0 s.r.l.</strong> per Federconsumatori
    </p>
    <?php
}

// ── ORARI ─────────────────────────────────────────────────────
function fdr_sede_orari_callback($post) {
    $giorni     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $giorni_ita = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
    echo '<table style="width:100%;border-collapse:collapse">';
    echo '<tr><th style="text-align:left;padding:4px 8px;color:#004A99">Giorno</th><th style="padding:4px 8px;color:#004A99">Apertura</th><th style="padding:4px 8px;color:#004A99">Chiusura</th><th style="padding:4px 8px;color:#004A99">Apertura 2ª</th><th style="padding:4px 8px;color:#004A99">Chiusura 2ª</th></tr>';
    foreach ($giorni as $i => $g) {
        $o1 = get_post_meta($post->ID, '_fdr_'.$g.'_open',   true);
        $c1 = get_post_meta($post->ID, '_fdr_'.$g.'_close',  true);
        $o2 = get_post_meta($post->ID, '_fdr_'.$g.'_open2',  true);
        $c2 = get_post_meta($post->ID, '_fdr_'.$g.'_close2', true);
        echo '<tr><td style="padding:4px 8px;font-weight:600">'.$giorni_ita[$i].'</td>';
        echo '<td style="padding:4px 8px"><input type="time" name="fdr_'.$g.'_open"   value="'.esc_attr($o1).'" style="border:1px solid #ddd;border-radius:4px;padding:4px"></td>';
        echo '<td style="padding:4px 8px"><input type="time" name="fdr_'.$g.'_close"  value="'.esc_attr($c1).'" style="border:1px solid #ddd;border-radius:4px;padding:4px"></td>';
        echo '<td style="padding:4px 8px"><input type="time" name="fdr_'.$g.'_open2"  value="'.esc_attr($o2).'" style="border:1px solid #ddd;border-radius:4px;padding:4px"></td>';
        echo '<td style="padding:4px 8px"><input type="time" name="fdr_'.$g.'_close2" value="'.esc_attr($c2).'" style="border:1px solid #ddd;border-radius:4px;padding:4px"></td>';
        echo '</tr>';
    }
    echo '</table>';
}

// ── SAVE ──────────────────────────────────────────────────────
add_action('save_post_fdr_sede', 'fdr_sedi_save_meta');
function fdr_sedi_save_meta($post_id) {
    if (!isset($_POST['fdr_sede_nonce']) || !wp_verify_nonce($_POST['fdr_sede_nonce'], 'fdr_sede_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = ['company','address','zip','city','telephone','mobile','fax','email','website','description','lat','lng'];
    foreach ($fields as $f) {
        if (isset($_POST['fdr_'.$f])) {
            update_post_meta($post_id, '_fdr_'.$f, sanitize_text_field($_POST['fdr_'.$f]));
        }
    }

    update_post_meta($post_id, '_fdr_premium',   isset($_POST['fdr_premium'])   ? 1 : 0);
    update_post_meta($post_id, '_fdr_nazionale', isset($_POST['fdr_nazionale']) ? 1 : 0);
    update_post_meta($post_id, '_fdr_pubblica',  isset($_POST['fdr_pubblica'])  ? 1 : 0);

    if (isset($_POST['fdr_logo_id'])) {
        update_post_meta($post_id, '_fdr_logo_id', absint($_POST['fdr_logo_id']));
    }
    if (isset($_POST['fdr_extra'])) {
        update_post_meta($post_id, '_fdr_extra', wp_kses_post($_POST['fdr_extra']));
    }

    $giorni = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    foreach ($giorni as $g) {
        foreach (['_open','_close','_open2','_close2'] as $suffix) {
            $key = 'fdr_'.$g.$suffix;
            if (isset($_POST[$key])) {
                update_post_meta($post_id, '_fdr_'.$g.$suffix, sanitize_text_field($_POST[$key]));
            }
        }
    }
}
