<?php
add_action('admin_post_fdr_sedi_import', 'fdr_sedi_process_import');
function fdr_sedi_process_import() {
    if (!current_user_can('manage_options')) wp_die('Accesso negato');
    if (!isset($_POST['fdr_import_nonce']) || !wp_verify_nonce($_POST['fdr_import_nonce'], 'fdr_import')) wp_die('Nonce non valido');
    
    if (!isset($_FILES['fdr_import_file']) || $_FILES['fdr_import_file']['error'] !== 0) {
        wp_redirect(admin_url('admin.php?page=fdr-sedi-import&error=1'));
        exit;
    }
    
    $file = $_FILES['fdr_import_file']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['fdr_import_file']['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'csv') {
        wp_redirect(admin_url('admin.php?page=fdr-sedi-import&error=2'));
        exit;
    }
    
    $imported = 0;
    $skipped = 0;
    
    if (($handle = fopen($file, 'r')) !== false) {
        // Rileva separatore (virgola o punto e virgola)
        $first_line = fgets($handle);
        rewind($handle);
        $sep = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';
        
        $header = fgetcsv($handle, 0, $sep);
        $header = array_map('trim', $header);
        // Rimuovi BOM se presente
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF\xFF\xFE");
        
        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            if (count($row) !== count($header)) { $skipped++; continue; }
            $data = array_combine($header, $row);
            
            $name = isset($data['name']) ? sanitize_text_field(trim($data['name'])) : '';
            if (empty($name)) { $skipped++; continue; }
            
            // Fix coordinate con virgola come decimale
            $lat_raw = isset($data['lat']) ? str_replace(',', '.', trim($data['lat'])) : '0';
            $lng_raw = isset($data['lng']) ? str_replace(',', '.', trim($data['lng'])) : '0';
            $lat = floatval($lat_raw);
            $lng = floatval($lng_raw);
            // Sovrascrivi i valori corretti nel dataset prima del loop campi
            $data['lat'] = $lat_raw;
            $data['lng'] = $lng_raw;
            
            // Crea sempre un post nuovo — il titolo univoco viene assegnato
            // automaticamente dal filter wp_insert_post_data (MODENA → MODENA (2) ecc.)
            $post_id = wp_insert_post([
                'post_title'  => $name,
                'post_type'   => 'fdr_sede',
                'post_status' => 'publish',
            ]);
            
            if (is_wp_error($post_id)) { $skipped++; continue; }
            
            $fields = ['company','address1','zip','city','telephone','mobile','fax','email','website','description','lat','lng','region','premium'];
            $meta_map = ['address1' => 'address'];
            
            foreach ($fields as $f) {
                if (isset($data[$f])) {
                    $meta_key = '_fdr_' . (isset($meta_map[$f]) ? $meta_map[$f] : $f);
                    update_post_meta($post_id, $meta_key, sanitize_text_field($data[$f]));
                }
            }
            
            // Orari
            $giorni = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            foreach ($giorni as $g) {
                foreach (['_open','_close','_open2','_close2'] as $suffix) {
                    $col = $g . $suffix;
                    if (isset($data[$col]) && !empty($data[$col])) {
                        update_post_meta($post_id, '_fdr_' . $g . $suffix, sanitize_text_field($data[$col]));
                    }
                }
            }
            
            // Assegna tassonomia regione
            $region = isset($data['region']) ? ucwords(strtolower(trim($data['region']))) : '';
            if ($region) {
                $term = term_exists($region, 'fdr_regione');
                if (!$term) $term = wp_insert_term($region, 'fdr_regione');
                if (!is_wp_error($term)) {
                    $term_id = is_array($term) ? $term['term_id'] : $term;
                    wp_set_post_terms($post_id, [$term_id], 'fdr_regione');
                }
            }
            
            $imported++;
        }
        fclose($handle);
    }
    
    wp_redirect(admin_url('admin.php?page=fdr-sedi-import&imported=' . $imported . '&skipped=' . $skipped));
    exit;
}
