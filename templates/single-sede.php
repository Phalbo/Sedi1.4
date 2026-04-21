<?php
/**
 * Template pagina singola sede — FDR Sedi v1.2
 * Sviluppato da Ares 2.0 s.r.l. per Federconsumatori
 */
$post_id   = get_the_ID();
$pubblica  = (int) get_post_meta($post_id, '_fdr_pubblica', true);

// Se non pubblica reindirizza alla pagina sedi
if (!$pubblica && !current_user_can('edit_posts')) {
    $sedi_page = get_page_by_path('sedi');
    wp_redirect($sedi_page ? get_permalink($sedi_page) : home_url());
    exit;
}

get_header();

$name      = get_the_title();
$company   = get_post_meta($post_id, '_fdr_company',   true);
$address   = get_post_meta($post_id, '_fdr_address',   true);
$zip       = get_post_meta($post_id, '_fdr_zip',       true);
$city      = get_post_meta($post_id, '_fdr_city',      true);
$region    = get_post_meta($post_id, '_fdr_region',    true);
$tel       = get_post_meta($post_id, '_fdr_telephone', true);
$mobile    = get_post_meta($post_id, '_fdr_mobile',    true);
$email     = get_post_meta($post_id, '_fdr_email',     true);
$website   = get_post_meta($post_id, '_fdr_website',   true);
$desc      = get_post_meta($post_id, '_fdr_description', true);
$lat       = get_post_meta($post_id, '_fdr_lat',       true);
$lng       = get_post_meta($post_id, '_fdr_lng',       true);
$premium   = (int) get_post_meta($post_id, '_fdr_premium',   true);
$nazionale = (int) get_post_meta($post_id, '_fdr_nazionale', true);
$logo_id   = get_post_meta($post_id, '_fdr_logo_id',   true);
$extra     = get_post_meta($post_id, '_fdr_extra',     true);

$giorni     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$giorni_ita = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica'];
$orari = [];
foreach ($giorni as $i => $g) {
    $o1 = get_post_meta($post_id, '_fdr_'.$g.'_open',  true);
    $c1 = get_post_meta($post_id, '_fdr_'.$g.'_close', true);
    $o2 = get_post_meta($post_id, '_fdr_'.$g.'_open2', true);
    $c2 = get_post_meta($post_id, '_fdr_'.$g.'_close2',true);
    if ($o1 && $c1) {
        $riga = $giorni_ita[$i] . ': ' . $o1 . ' - ' . $c1;
        if ($o2 && $c2) $riga .= ' / ' . $o2 . ' - ' . $c2;
        $orari[] = $riga;
    }
}

$gmaps        = 'https://maps.google.com/?q=' . urlencode(trim($address . ' ' . $zip . ' ' . $city));
$display_name = $company ?: $name;
?>
<style>
.fdr-sede-page{max-width:1100px;margin:40px auto;padding:0 20px;font-family:'Inter',Arial,sans-serif}
.fdr-back-link{display:inline-flex;align-items:center;gap:6px;color:#004A99;font-size:14px;font-weight:600;text-decoration:none;margin-bottom:24px}
.fdr-back-link:hover{text-decoration:underline}
.fdr-sede-header{display:flex;align-items:flex-start;gap:20px;margin-bottom:28px;flex-wrap:wrap}
.fdr-sede-header-text{flex:1}
.fdr-sede-header h1{color:#004A99;font-size:26px;font-weight:700;margin:0 0 8px 0}
.fdr-sede-logo img{max-width:200px;max-height:80px;object-fit:contain;border:1px solid #eee;border-radius:6px;padding:8px;background:white}
.fdr-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:4px}
.fdr-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase}
.fdr-badge-naz{background:#FDC513;color:#004A99}
.fdr-badge-reg{background:#004A99;color:white}
.fdr-badge-reg2{background:#f0f0f0;color:#444}
.fdr-layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px}
.fdr-card{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.07)}
.fdr-card h3{color:#004A99;font-size:15px;font-weight:700;margin:0 0 14px 0;padding-bottom:8px;border-bottom:2px solid #FDC513}
.fdr-row{display:flex;gap:10px;margin-bottom:10px;font-size:14px;color:#444;align-items:flex-start}
.fdr-icon{width:20px;flex-shrink:0;font-size:15px}
.fdr-link{color:#004A99;text-decoration:none}
.fdr-link:hover{text-decoration:underline}
.fdr-orari{list-style:none;padding:0;margin:0}
.fdr-orari li{padding:5px 0;font-size:13px;border-bottom:1px solid #f5f5f5}
.fdr-orari li:last-child{border:none}
.fdr-map{border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);min-height:380px}
.fdr-gmaps-btn{display:inline-block;margin-top:16px;background:#FDC513;color:#004A99!important;padding:10px 20px;border-radius:8px;font-weight:700;font-size:14px;text-decoration:none!important}
.fdr-gmaps-btn:hover{background:#e6b000}
.fdr-extra{background:white;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.07);margin-top:8px}
.fdr-extra h3{color:#004A99;font-size:15px;font-weight:700;margin:0 0 14px 0;padding-bottom:8px;border-bottom:2px solid #FDC513}
.fdr-extra img{max-width:100%;height:auto;border-radius:6px}
@media(max-width:768px){.fdr-layout{grid-template-columns:1fr}.fdr-map{min-height:280px}.fdr-sede-header{flex-direction:column}}
</style>

<div class="fdr-sede-page">
    <a href="<?php echo esc_url(get_permalink(get_page_by_path('sedi'))); ?>" class="fdr-back-link">← Tutte le sedi</a>

    <div class="fdr-sede-header">
        <div class="fdr-sede-header-text">
            <?php if ($logo_id): ?>
            <div class="fdr-sede-logo" style="margin-bottom:12px">
                <?php echo wp_get_attachment_image($logo_id, [400, 200], false, ['style'=>'max-width:220px;max-height:90px;object-fit:contain;border:1px solid #eee;border-radius:6px;padding:8px;background:white']); ?>
            </div>
            <?php endif; ?>
            <div class="fdr-badges">
                <?php if ($nazionale): ?><span class="fdr-badge fdr-badge-naz">★ Sede Nazionale</span><?php endif; ?>
                <?php if (!$nazionale && $premium): ?><span class="fdr-badge fdr-badge-reg">Sede Regionale</span><?php endif; ?>
                <?php if ($region): ?><span class="fdr-badge fdr-badge-reg2"><?php echo esc_html($region); ?></span><?php endif; ?>
            </div>
            <h1><?php echo esc_html($display_name); ?></h1>
        </div>
    </div>

    <div class="fdr-layout">
        <div class="fdr-card">
            <h3>Informazioni</h3>
            <?php if ($address || $city): ?>
            <div class="fdr-row"><span class="fdr-icon">📍</span><span><?php echo esc_html(trim($address.', '.$zip.' '.$city)); ?></span></div>
            <?php endif; ?>
            <?php if ($tel): ?>
            <div class="fdr-row"><span class="fdr-icon">📞</span><a href="tel:<?php echo esc_attr($tel); ?>" class="fdr-link"><?php echo esc_html($tel); ?></a></div>
            <?php endif; ?>
            <?php if ($mobile): ?>
            <div class="fdr-row"><span class="fdr-icon">📱</span><a href="tel:<?php echo esc_attr($mobile); ?>" class="fdr-link"><?php echo esc_html($mobile); ?></a></div>
            <?php endif; ?>
            <?php if ($email): ?>
            <div class="fdr-row"><span class="fdr-icon">✉️</span><a href="mailto:<?php echo esc_attr($email); ?>" class="fdr-link"><?php echo esc_html($email); ?></a></div>
            <?php endif; ?>
            <?php if ($website && $website !== 'nan'): ?>
            <div class="fdr-row"><span class="fdr-icon">🌐</span><a href="<?php echo esc_url($website); ?>" target="_blank" class="fdr-link"><?php echo esc_html($website); ?></a></div>
            <?php endif; ?>
            <?php if (!empty($orari)): ?>
            <h3 style="margin-top:18px">Orari di apertura</h3>
            <ul class="fdr-orari">
                <?php foreach ($orari as $o): ?><li><?php echo esc_html($o); ?></li><?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if ($desc): ?>
            <div class="fdr-row" style="margin-top:10px;align-items:flex-start">
                <span class="fdr-icon" style="padding-top:5px"><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#4caf50"></span></span>
                <span style="color:#555;font-style:italic;font-size:13px"><?php echo esc_html($desc); ?></span>
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($gmaps); ?>" target="_blank" class="fdr-gmaps-btn">📍 Apri in Google Maps</a>
        </div>

        <?php if ($lat && $lng): ?>
        <div class="fdr-map" id="fdr-single-map"></div>
        <?php endif; ?>
    </div>

    <?php if ($extra): ?>
    <div class="fdr-extra">
        <h3>Informazioni aggiuntive</h3>
        <?php echo wp_kses_post($extra); ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($lat && $lng): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('fdr-single-map').setView([<?php echo floatval($lat); ?>, <?php echo floatval($lng); ?>], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 18 }).addTo(map);
    var icon = L.divIcon({ html: '<div style="background:#004A99;width:18px;height:18px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.4)"></div>', iconSize:[18,18], iconAnchor:[9,9], className:'' });
    L.marker([<?php echo floatval($lat); ?>, <?php echo floatval($lng); ?>], {icon:icon})
     .addTo(map)
     .bindPopup('<strong><?php echo esc_js($display_name); ?></strong><br><?php echo esc_js(trim($address.", ".$city)); ?>')
     .openPopup();
});
</script>
<?php endif; ?>
<?php get_footer(); ?>
