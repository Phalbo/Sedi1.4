<?php
/**
 * FDR Sedi — Shortcode [fdr_sedi]
 * Cache: transient 12h, invalidata al salvataggio/cancellazione di qualsiasi sede.
 */
add_shortcode('fdr_sedi', 'fdr_sedi_shortcode');

add_action('save_post_fdr_sede', 'fdr_sedi_clear_cache');
add_action('delete_post',        'fdr_sedi_clear_cache_on_delete');

function fdr_sedi_clear_cache() {
    delete_transient('fdr_sedi_json');
}
function fdr_sedi_clear_cache_on_delete($post_id) {
    if (get_post_type($post_id) === 'fdr_sede') delete_transient('fdr_sedi_json');
}

function fdr_sedi_shortcode($atts) {
    $atts = shortcode_atts(['regione' => ''], $atts);

    $use_cache       = empty($atts['regione']);
    $sedi_json       = null;
    $count           = 0;
    $regioni_options = '';

    if ($use_cache) {
        $cached = get_transient('fdr_sedi_json');
        if (is_array($cached)) {
            $sedi_json       = $cached['json'];
            $count           = $cached['count'];
            $regioni_options = $cached['regioni'];
        }
    }

    if ($sedi_json === null) {
        $args = [
            'post_type'      => 'fdr_sede',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if (!empty($atts['regione'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'fdr_regione',
                'field'    => 'slug',
                'terms'    => $atts['regione'],
            ]];
        }

        $posts = get_posts($args);
        $sedi  = [];

        foreach ($posts as $p) {
            $lat = get_post_meta($p->ID, '_fdr_lat', true);
            $lng = get_post_meta($p->ID, '_fdr_lng', true);
            if (!$lat || !$lng) continue;

            $giorni     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            $giorni_ita = ['Lun','Mar','Mer','Gio','Ven','Sab','Dom'];
            $orari = '';
            foreach ($giorni as $i => $g) {
                $o1 = get_post_meta($p->ID, '_fdr_'.$g.'_open',  true);
                $c1 = get_post_meta($p->ID, '_fdr_'.$g.'_close', true);
                $o2 = get_post_meta($p->ID, '_fdr_'.$g.'_open2', true);
                $c2 = get_post_meta($p->ID, '_fdr_'.$g.'_close2',true);
                if ($o1 && $c1) {
                    $riga = $giorni_ita[$i].': '.$o1.'-'.$c1;
                    if ($o2 && $c2) $riga .= ' / '.$o2.'-'.$c2;
                    $orari .= $riga.' ';
                }
            }

            $regione_terms = get_the_terms($p->ID, 'fdr_regione');
            $regione = ($regione_terms && !is_wp_error($regione_terms))
                ? $regione_terms[0]->name
                : get_post_meta($p->ID, '_fdr_region', true);

            $address = get_post_meta($p->ID, '_fdr_address', true);
            $city    = get_post_meta($p->ID, '_fdr_city', true);
            $premium = (int) get_post_meta($p->ID, '_fdr_premium', true);
            $name    = $p->post_title;

            $nazionale    = (int) get_post_meta($p->ID, '_fdr_nazionale', true);
            $is_nazionale = ($nazionale === 1) || ($premium === 1 && stripos($name, 'Nazionale') !== false);

            $gmaps_query = urlencode(trim($address . ' ' . $city));

            $sedi[] = [
                'id'           => $p->ID,
                'name'         => $name,
                'company'      => get_post_meta($p->ID, '_fdr_company', true),
                'address'      => $address,
                'city'         => $city,
                'zip'          => get_post_meta($p->ID, '_fdr_zip', true),
                'region'       => $regione,
                'tel'          => get_post_meta($p->ID, '_fdr_telephone', true),
                'email'        => get_post_meta($p->ID, '_fdr_email', true),
                'website'      => get_post_meta($p->ID, '_fdr_website', true),
                'gmaps'        => 'https://maps.google.com/?q=' . $gmaps_query,
                'orari'        => trim($orari),
                'lat'          => (float)$lat,
                'lng'          => (float)$lng,
                'premium'      => $premium,
                'is_nazionale' => $is_nazionale,
                'pubblica'     => (int) get_post_meta($p->ID, '_fdr_pubblica', true),
                'url'          => get_permalink($p->ID),
            ];
        }

        usort($sedi, function($a, $b) {
            if ($a['is_nazionale'] !== $b['is_nazionale']) return $b['is_nazionale'] - $a['is_nazionale'];
            if ($a['premium']      !== $b['premium'])      return $b['premium']      - $a['premium'];
            return strcmp($a['name'], $b['name']);
        });

        $regioni_terms = get_terms(['taxonomy' => 'fdr_regione', 'hide_empty' => true, 'orderby' => 'name']);
        $regioni_options = '';
        if (!is_wp_error($regioni_terms)) {
            foreach ($regioni_terms as $term) {
                $regioni_options .= '<option value="'.esc_attr($term->name).'">'.esc_html($term->name).'</option>';
            }
        }

        $sedi_json = json_encode($sedi, JSON_UNESCAPED_UNICODE);
        $count     = count($sedi);

        if ($use_cache) {
            set_transient('fdr_sedi_json', [
                'json'    => $sedi_json,
                'count'   => $count,
                'regioni' => $regioni_options,
            ], 12 * HOUR_IN_SECONDS);
        }
    }

    ob_start();
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css"/>

    <style>
    .fdr-sedi-wrapper { font-family: 'Inter', Arial, sans-serif; max-width: 100%; }
    .fdr-sedi-controls { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
    .fdr-sedi-search { flex: 1; min-width: 200px; padding: 10px 16px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px; outline: none; transition: border-color 0.2s; }
    .fdr-sedi-search:focus { border-color: #004A99; }
    .fdr-sedi-select { padding: 10px 16px; border: 2px solid #ddd; border-radius: 8px; font-size: 15px; background: white; cursor: pointer; }
    .fdr-sedi-select:focus { border-color: #004A99; outline: none; }
    .fdr-sedi-count { color: #004A99; font-weight: 700; font-size: 14px; white-space: nowrap; }
    .fdr-sedi-layout { display: grid; grid-template-columns: 360px 1fr; gap: 16px; height: 620px; }
    .fdr-sedi-list { background: white; border-radius: 12px; overflow-y: auto; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #eee; }
    .fdr-sede-item { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.15s; }
    .fdr-sede-item:hover { background: #f0f6ff; }
    .fdr-sede-item.fdr-active { background: #e8f0ff; border-left: 4px solid #004A99; }
    .fdr-sede-item.fdr-nazionale { background: #fffbe6; border-left: 4px solid #FDC513; }
    .fdr-sede-item.fdr-nazionale:hover { background: #fff5cc; }
    .fdr-sede-item.fdr-regionale { background: #f0f6ff; border-left: 3px solid #004A99; }
    .fdr-sede-item.fdr-regionale:hover { background: #e0ecff; }
    .fdr-sede-name { font-weight: 700; color: #004A99; font-size: 14px; margin-bottom: 2px; display: flex; align-items: center; gap: 6px; }
    .fdr-badge-nazionale { background: #FDC513; color: #004A99; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .fdr-badge-regionale { background: #004A99; color: white; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .fdr-sede-city { color: #666; font-size: 13px; margin-bottom: 4px; }
    .fdr-sede-tag { display: inline-block; background: #FDC513; color: #004A99; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; }
    #fdr-map { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .fdr-popup-name { font-weight: 700; color: #004A99; font-size: 15px; margin-bottom: 8px; border-bottom: 2px solid #FDC513; padding-bottom: 6px; }
    .fdr-popup-badge { display: inline-block; background: #FDC513; color: #004A99; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 20px; margin-bottom: 6px; text-transform: uppercase; }
    .fdr-popup-badge-reg { background: #004A99; color: white; }
    .fdr-popup-row { font-size: 13px; color: #444; margin-bottom: 5px; display: flex; gap: 8px; align-items: flex-start; }
    .fdr-popup-link { color: #004A99; text-decoration: none; }
    .fdr-popup-link:hover { text-decoration: underline; }
    .fdr-popup-gmaps { display: inline-block; margin-top: 8px; background: #FDC513; color: #004A99 !important; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 700; text-decoration: none !important; }
    .fdr-popup-gmaps:hover { background: #e6b000; color: #004A99 !important; text-decoration: none !important; }
    .fdr-no-results { padding: 40px; text-align: center; color: #999; font-size: 15px; }
    @media (max-width: 768px) {
        .fdr-sedi-layout { grid-template-columns: 1fr; height: auto; }
        .fdr-sedi-list { height: 300px; }
        #fdr-map { height: 400px !important; }
    }
    </style>

    <div class="fdr-sedi-wrapper">
        <div class="fdr-sedi-controls">
            <input type="text" class="fdr-sedi-search" id="fdrSearch" placeholder="🔍 Cerca per città, nome o indirizzo...">
            <select class="fdr-sedi-select" id="fdrRegione">
                <option value="">Tutte le regioni</option>
                <?php echo $regioni_options; ?>
            </select>
            <span class="fdr-sedi-count" id="fdrCount"><?php echo $count; ?> sedi</span>
        </div>
        <div class="fdr-sedi-layout">
            <div class="fdr-sedi-list" id="fdrList"></div>
            <div id="fdr-map" style="height:620px"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.min.js"></script>
    <script>
    (function() {
        const SEDI = <?php echo $sedi_json; ?>;
        let filtered = [...SEDI];
        let activeIdx = -1;
        let allMarkers = [];

        const map = L.map('fdr-map').setView([42.5, 12.5], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors', maxZoom: 18
        }).addTo(map);

        const clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 40,
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div style="background:#004A99;color:white;border-radius:50%;width:38px;height:38px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3)">' + cluster.getChildCount() + '</div>',
                    iconSize: [38,38], iconAnchor: [19,19], className: ''
                });
            }
        });

        const blueIcon = L.divIcon({
            html: '<div style="background:#004A99;width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4)"></div>',
            iconSize: [14,14], iconAnchor: [7,7], popupAnchor: [0,-8], className: ''
        });

        const goldIcon = L.divIcon({
            html: '<div style="background:#FDC513;width:18px;height:18px;border-radius:50%;border:2px solid #004A99;box-shadow:0 2px 6px rgba(0,0,0,0.4)"></div>',
            iconSize: [18,18], iconAnchor: [9,9], popupAnchor: [0,-10], className: ''
        });

        const nazionaleIcon = L.divIcon({
            html: '<div style="background:#FDC513;width:22px;height:22px;border-radius:50%;border:3px solid #004A99;box-shadow:0 2px 8px rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;font-size:11px">★</div>',
            iconSize: [22,22], iconAnchor: [11,11], popupAnchor: [0,-12], className: ''
        });

        function makePopup(s) {
            let h = '';
            if (s.is_nazionale) h += '<div class="fdr-popup-badge">★ Sede Nazionale</div><br>';
            else if (s.premium) h += '<div class="fdr-popup-badge fdr-popup-badge-reg">Sede Regionale</div><br>';
            var nameHtml = s.pubblica
                ? '<a href="' + s.url + '" style="color:#004A99;text-decoration:none;font-weight:700">' + (s.company||s.name) + '</a>'
                : (s.company||s.name);
            h += '<div class="fdr-popup-name">' + nameHtml + '</div>';
            if (s.address) h += '<div class="fdr-popup-row">📍 ' + s.address + (s.zip ? ', ' + s.zip : '') + ' ' + s.city + '</div>';
            if (s.tel)     h += '<div class="fdr-popup-row">📞 <a href="tel:' + s.tel + '" class="fdr-popup-link">' + s.tel + '</a></div>';
            if (s.email)   h += '<div class="fdr-popup-row">✉️ <a href="mailto:' + s.email + '" class="fdr-popup-link">' + s.email + '</a></div>';
            if (s.website && s.website !== '') h += '<div class="fdr-popup-row">🌐 <a href="' + s.website + '" target="_blank" class="fdr-popup-link">Sito web</a></div>';
            if (s.orari)   h += '<div class="fdr-popup-row">🕐 ' + s.orari + '</div>';
            h += '<a href="' + s.gmaps + '" target="_blank" class="fdr-popup-gmaps">📍 Apri in Google Maps</a>';
            return h;
        }

        function getItemClass(s) {
            if (s.is_nazionale) return 'fdr-sede-item fdr-nazionale';
            if (s.premium)      return 'fdr-sede-item fdr-regionale';
            return 'fdr-sede-item';
        }

        function getBadge(s) {
            if (s.is_nazionale) return '<span class="fdr-badge-nazionale">★ Nazionale</span>';
            if (s.premium)      return '<span class="fdr-badge-regionale">Regionale</span>';
            return '';
        }

        function renderList() {
            const list = document.getElementById('fdrList');
            document.getElementById('fdrCount').textContent = filtered.length + ' sedi';
            if (!filtered.length) {
                list.innerHTML = '<div class="fdr-no-results">Nessuna sede trovata</div>';
                return;
            }
            list.innerHTML = filtered.map((s,i) =>
                '<div class="' + (i===activeIdx
                    ? getItemClass(s).replace('fdr-sede-item','fdr-sede-item fdr-active')
                    : getItemClass(s)) + '" onclick="fdrSelect(' + i + ')">' +
                '<div class="fdr-sede-name">' +
                    (s.pubblica
                        ? '<a href="' + s.url + '" style="color:#004A99;text-decoration:none;font-weight:700">' + (s.company||s.name||s.city) + '</a>'
                        : '<span>' + (s.company||s.name||s.city) + '</span>') +
                    ' ' + getBadge(s) +
                '</div>' +
                '<div class="fdr-sede-city">' + s.city + (s.address ? ' · ' + s.address : '') + '</div>' +
                '<span class="fdr-sede-tag">' + (s.region||'') + '</span>' +
                '</div>'
            ).join('');
        }

        function getIcon(s) {
            if (s.is_nazionale) return nazionaleIcon;
            if (s.premium)      return goldIcon;
            return blueIcon;
        }

        function renderMarkers() {
            clusterGroup.clearLayers();
            allMarkers = filtered.map((s,i) => {
                const m = L.marker([s.lat, s.lng], {icon: getIcon(s)});
                m.bindPopup(makePopup(s), {maxWidth: 280});
                m.on('click', () => fdrSelect(i, false));
                return m;
            });
            clusterGroup.addLayers(allMarkers);
            map.addLayer(clusterGroup);
        }

        window.fdrSelect = function(i, panMap) {
            activeIdx = i;
            const s = filtered[i];
            renderList();
            const el = document.getElementById('fdrList').children[i];
            if (el) el.scrollIntoView({behavior:'smooth', block:'nearest'});
            if (panMap !== false && allMarkers[i]) {
                map.setView([s.lat, s.lng], 14);
                allMarkers[i].openPopup();
            }
        };

        function applyFilters() {
            const q = document.getElementById('fdrSearch').value.toLowerCase();
            const r = document.getElementById('fdrRegione').value;
            filtered = SEDI.filter(s => {
                const mq = !q ||
                    s.name.toLowerCase().includes(q) ||
                    s.city.toLowerCase().includes(q) ||
                    (s.company||'').toLowerCase().includes(q) ||
                    (s.address||'').toLowerCase().includes(q);
                const mr = !r || s.region === r;
                return mq && mr;
            });
            activeIdx = -1;
            renderList();
            renderMarkers();
        }

        document.getElementById('fdrSearch').addEventListener('input', applyFilters);
        document.getElementById('fdrRegione').addEventListener('change', applyFilters);

        renderList();
        renderMarkers();
    })();
    </script>
    <?php
    return ob_get_clean();
}
