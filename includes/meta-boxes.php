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
        <input type="text" name="fdr_lat" id="fdr_lat" value="<?php echo esc_attr($lat); ?>" placeholder="es. 41.9028" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;margin-top:4px">
    </p>
    <p>
        <strong>Longitudine</strong><br>
        <input type="text" name="fdr_lng" id="fdr_lng" value="<?php echo esc_attr($lng); ?>" placeholder="es. 12.4964" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;margin-top:4px">
    </p>
    <p>
        <button type="button" id="fdr_geocode_btn" class="button" style="background:#004A99;color:white;border-color:#003a7a;width:100%;justify-content:center">
            📍 Ottieni coordinate dall'indirizzo
        </button>
        <span id="fdr_geocode_msg" style="display:none;font-size:12px;margin-top:6px;display:block"></span>
    </p>
    <script>
    document.getElementById('fdr_geocode_btn').addEventListener('click', function() {
        var address = (document.querySelector('[name="fdr_address"]') || {}).value || '';
        var zip     = (document.querySelector('[name="fdr_zip"]')     || {}).value || '';
        var city    = (document.querySelector('[name="fdr_city"]')    || {}).value || '';
        var query   = [address, zip, city, 'Italia'].filter(Boolean).join(', ');
        var msg     = document.getElementById('fdr_geocode_msg');
        var btn     = this;

        if (!address && !city) {
            msg.style.color = '#d63638';
            msg.textContent = '⚠️ Compila prima Indirizzo e Città.';
            msg.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = '⏳ Ricerca in corso…';
        msg.style.display = 'none';

        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query), {
            headers: { 'Accept-Language': 'it', 'User-Agent': 'FDR-Sedi-Plugin/1.4' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = '📍 Ottieni coordinate dall\'indirizzo';
            if (data && data.length > 0) {
                document.getElementById('fdr_lat').value = parseFloat(data[0].lat).toFixed(7);
                document.getElementById('fdr_lng').value = parseFloat(data[0].lon).toFixed(7);
                msg.style.color = '#2e7d32';
                msg.textContent = '✅ Coordinate trovate: ' + data[0].display_name.split(',').slice(0,3).join(',');
            } else {
                msg.style.color = '#d63638';
                msg.textContent = '❌ Indirizzo non trovato. Prova a semplificare (solo via e città).';
            }
            msg.style.display = 'block';
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = '📍 Ottieni coordinate dall\'indirizzo';
            msg.style.color = '#d63638';
            msg.textContent = '❌ Errore di rete. Riprova.';
            msg.style.display = 'block';
        });
    });
    </script>
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
function fdr_sede_orari_callback( $post ) {
    $giorni      = [ 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday' ];
    $giorni_ita  = [ 'Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica' ];

    // ── Leggi meta esistenti ──
    $day_hours = [];
    $has_any   = false;
    foreach ( $giorni as $g ) {
        $h = [
            'open'   => get_post_meta( $post->ID, '_fdr_'.$g.'_open',   true ),
            'close'  => get_post_meta( $post->ID, '_fdr_'.$g.'_close',  true ),
            'open2'  => get_post_meta( $post->ID, '_fdr_'.$g.'_open2',  true ),
            'close2' => get_post_meta( $post->ID, '_fdr_'.$g.'_close2', true ),
        ];
        $day_hours[$g] = $h;
        if ( $h['open'] && $h['close'] ) $has_any = true;
    }

    // ── Reverse-engineer base + override ──
    $base_open = $base_close = $base_open2 = $base_close2 = '';
    $base_key  = '';
    $active_days = [];
    $overrides   = [];

    if ( $has_any ) {
        foreach ( $giorni as $g ) {
            if ( $day_hours[$g]['open'] && $day_hours[$g]['close'] ) $active_days[] = $g;
        }
        $freq = [];
        foreach ( $active_days as $g ) {
            $h   = $day_hours[$g];
            $key = $h['open'].'|'.$h['close'].'|'.$h['open2'].'|'.$h['close2'];
            $freq[$key] = ( $freq[$key] ?? 0 ) + 1;
        }
        arsort( $freq );
        $base_key = array_key_first( $freq );
        [ $base_open, $base_close, $base_open2, $base_close2 ] = explode( '|', $base_key );
        foreach ( $active_days as $g ) {
            $h   = $day_hours[$g];
            $key = $h['open'].'|'.$h['close'].'|'.$h['open2'].'|'.$h['close2'];
            if ( $key !== $base_key ) $overrides[$g] = $h;
        }
    } else {
        $active_days = [ 'Monday','Tuesday','Wednesday','Thursday','Friday' ]; // default nuovi post
    }
    ?>
    <style>
    .fdr-oh-section{margin-bottom:18px}
    .fdr-oh-section h4{color:#004A99;font-size:11px;text-transform:uppercase;letter-spacing:.6px;margin:0 0 8px;padding-bottom:4px;border-bottom:2px solid #FDC513}
    .fdr-oh-row{display:flex;align-items:center;gap:6px;margin-bottom:6px;font-size:13px}
    .fdr-oh-row label{width:90px;color:#666;font-size:12px;flex-shrink:0}
    .fdr-oh-row input[type=time]{border:1px solid #ddd;border-radius:4px;padding:4px 6px;font-size:13px}
    .fdr-oh-sep{color:#aaa;font-size:12px}
    .fdr-day-row{padding:5px 0;border-bottom:1px solid #f5f5f5}
    .fdr-day-header{display:flex;align-items:center;gap:10px}
    .fdr-day-label{display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:600;width:110px}
    .fdr-day-label input[type=checkbox]{width:15px;height:15px;flex-shrink:0}
    .fdr-cust-btn{font-size:11px;color:#7c3aed;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;font-family:inherit}
    .fdr-override-box{background:#faf5ff;border:1px solid #ddd6fe;border-radius:6px;padding:10px 12px;margin-top:6px}
    .fdr-override-title{font-size:11px;color:#7c3aed;font-weight:700;margin:0 0 8px;display:block}
    .fdr-close-btn{font-size:11px;color:#7c3aed;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;font-family:inherit;margin-top:4px}
    </style>

    <div class="fdr-oh-section">
        <h4>Orario base</h4>
        <p style="color:#888;font-size:12px;margin:0 0 10px">Si applica a tutti i giorni attivi (salvo personalizzazioni).</p>
        <div class="fdr-oh-row">
            <label>Mattina</label>
            <input type="time" name="fdr_base_open"  value="<?php echo esc_attr($base_open); ?>">
            <span class="fdr-oh-sep">–</span>
            <input type="time" name="fdr_base_close" value="<?php echo esc_attr($base_close); ?>">
        </div>
        <div class="fdr-oh-row">
            <label style="color:#bbb">Pomeriggio</label>
            <input type="time" name="fdr_base_open2"  value="<?php echo esc_attr($base_open2); ?>">
            <span class="fdr-oh-sep">–</span>
            <input type="time" name="fdr_base_close2" value="<?php echo esc_attr($base_close2); ?>">
        </div>
    </div>

    <div class="fdr-oh-section">
        <h4>Giorni attivi</h4>
        <?php foreach ( $giorni as $i => $g ):
            $is_active    = in_array( $g, $active_days, true );
            $has_override = isset( $overrides[$g] );
            $ov = $has_override ? $overrides[$g] : [ 'open'=>'','close'=>'','open2'=>'','close2'=>'' ];
        ?>
        <div class="fdr-day-row">
            <div class="fdr-day-header">
                <label class="fdr-day-label">
                    <input type="checkbox" name="fdr_days_active[]" value="<?php echo $g; ?>"
                           id="fdr_day_<?php echo $g; ?>"
                           <?php checked( $is_active ); ?>
                           onchange="fdrDayToggle('<?php echo $g; ?>', this.checked)">
                    <?php echo $giorni_ita[$i]; ?>
                </label>
                <button type="button" id="fdr-cust-<?php echo $g; ?>"
                        class="fdr-cust-btn"
                        onclick="fdrToggleOverride('<?php echo $g; ?>')"
                        style="display:<?php echo $is_active ? 'inline' : 'none'; ?>">
                    <?php echo $has_override ? '● personalizzato ▲' : '✎ personalizza'; ?>
                </button>
            </div>
            <div id="fdr-ov-<?php echo $g; ?>"
                 style="display:<?php echo $has_override ? 'block' : 'none'; ?>">
                <div class="fdr-override-box">
                    <span class="fdr-override-title">⚙ Orario personalizzato — <?php echo $giorni_ita[$i]; ?></span>
                    <input type="checkbox" name="fdr_override_<?php echo $g; ?>_active" id="fdr_ovchk_<?php echo $g; ?>"
                           value="1" <?php checked( $has_override ); ?> style="display:none">
                    <div class="fdr-oh-row">
                        <label>Mattina</label>
                        <input type="time" name="fdr_override_<?php echo $g; ?>_open"  value="<?php echo esc_attr($ov['open']); ?>">
                        <span class="fdr-oh-sep">–</span>
                        <input type="time" name="fdr_override_<?php echo $g; ?>_close" value="<?php echo esc_attr($ov['close']); ?>">
                    </div>
                    <div class="fdr-oh-row">
                        <label style="color:#bbb">Pomeriggio</label>
                        <input type="time" name="fdr_override_<?php echo $g; ?>_open2"  value="<?php echo esc_attr($ov['open2']); ?>">
                        <span class="fdr-oh-sep">–</span>
                        <input type="time" name="fdr_override_<?php echo $g; ?>_close2" value="<?php echo esc_attr($ov['close2']); ?>">
                    </div>
                    <button type="button" class="fdr-close-btn"
                            onclick="fdrToggleOverride('<?php echo $g; ?>')">▲ chiudi e usa orario base</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    function fdrToggleOverride(day) {
        var box = document.getElementById('fdr-ov-' + day);
        var chk = document.getElementById('fdr_ovchk_' + day);
        var btn = document.getElementById('fdr-cust-' + day);
        var open = box.style.display === 'none';
        box.style.display = open ? 'block' : 'none';
        chk.checked = open;
        btn.textContent = open ? '● personalizzato ▲' : '✎ personalizza';
    }
    function fdrDayToggle(day, active) {
        var btn = document.getElementById('fdr-cust-' + day);
        if ( btn ) btn.style.display = active ? 'inline' : 'none';
        if ( !active ) {
            var box = document.getElementById('fdr-ov-' + day);
            var chk = document.getElementById('fdr_ovchk_' + day);
            if ( box ) box.style.display = 'none';
            if ( chk ) chk.checked = false;
            if ( btn ) btn.textContent = '✎ personalizza';
        }
    }
    </script>
    <?php
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

    // ── Orari: base + giorni attivi + override per giorno ──
    if ( array_key_exists( 'fdr_base_open', $_POST ) ) {
        $giorni      = [ 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday' ];
        $active_days = isset( $_POST['fdr_days_active'] )
            ? array_map( 'sanitize_text_field', (array) $_POST['fdr_days_active'] )
            : [];
        $base = [
            'open'   => sanitize_text_field( $_POST['fdr_base_open']   ?? '' ),
            'close'  => sanitize_text_field( $_POST['fdr_base_close']  ?? '' ),
            'open2'  => sanitize_text_field( $_POST['fdr_base_open2']  ?? '' ),
            'close2' => sanitize_text_field( $_POST['fdr_base_close2'] ?? '' ),
        ];
        foreach ( $giorni as $g ) {
            if ( ! in_array( $g, $active_days, true ) ) {
                update_post_meta( $post_id, '_fdr_'.$g.'_open',   '' );
                update_post_meta( $post_id, '_fdr_'.$g.'_close',  '' );
                update_post_meta( $post_id, '_fdr_'.$g.'_open2',  '' );
                update_post_meta( $post_id, '_fdr_'.$g.'_close2', '' );
                continue;
            }
            $h = ( ! empty( $_POST[ 'fdr_override_'.$g.'_active' ] ) ) ? [
                'open'   => sanitize_text_field( $_POST[ 'fdr_override_'.$g.'_open'   ] ?? '' ),
                'close'  => sanitize_text_field( $_POST[ 'fdr_override_'.$g.'_close'  ] ?? '' ),
                'open2'  => sanitize_text_field( $_POST[ 'fdr_override_'.$g.'_open2'  ] ?? '' ),
                'close2' => sanitize_text_field( $_POST[ 'fdr_override_'.$g.'_close2' ] ?? '' ),
            ] : $base;
            update_post_meta( $post_id, '_fdr_'.$g.'_open',   $h['open'] );
            update_post_meta( $post_id, '_fdr_'.$g.'_close',  $h['close'] );
            update_post_meta( $post_id, '_fdr_'.$g.'_open2',  $h['open2'] );
            update_post_meta( $post_id, '_fdr_'.$g.'_close2', $h['close2'] );
        }
    }
}
