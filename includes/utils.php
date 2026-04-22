<?php
/**
 * FDR Sedi — Utility condivise
 */

// ── CACHE ─────────────────────────────────────────────────────
// Versione della chiave: incrementare qui per busted cache forzata
define( 'FDR_SEDI_CACHE_KEY', 'fdr_sedi_json_v2' );

add_action( 'save_post_fdr_sede', 'fdr_sedi_clear_cache' );
add_action( 'delete_post',        'fdr_sedi_clear_cache_on_delete' );

function fdr_sedi_clear_cache() {
    delete_transient( FDR_SEDI_CACHE_KEY );
}
function fdr_sedi_clear_cache_on_delete( $post_id ) {
    if ( get_post_type( $post_id ) === 'fdr_sede' ) {
        delete_transient( FDR_SEDI_CACHE_KEY );
    }
}

// ── TITOLI UNIVOCI ─────────────────────────────────────────────
/**
 * Restituisce un titolo univoco per fdr_sede.
 * Estrae il base (senza " (N)"), conta i post esistenti, aggiunge suffisso progressivo.
 */
function fdr_unique_sede_title( $title, $exclude_id = 0 ) {
    global $wpdb;

    $base = trim( preg_replace( '/\s*\(\d+\)$/', '', trim( $title ) ) );
    if ( ! $base ) return $title;

    $like = $wpdb->esc_like( $base );
    $rows = $wpdb->get_col( $wpdb->prepare(
        "SELECT post_title FROM {$wpdb->posts}
         WHERE post_type    = 'fdr_sede'
           AND post_status NOT IN ('trash', 'auto-draft')
           AND ( post_title = %s OR post_title LIKE %s )
           AND ID != %d",
        $base,
        $like . ' (%)',
        (int) $exclude_id
    ) );

    $pattern = '/^' . preg_quote( $base, '/' ) . '( \(\d+\))?$/';
    $matches = array_filter( $rows, fn( $t ) => preg_match( $pattern, $t ) );

    $count = count( $matches );
    return $count === 0 ? $base : $base . ' (' . ( $count + 1 ) . ')';
}

// ── VISUALIZZAZIONE ORARI ──────────────────────────────────────
/**
 * Legge i meta orari di un post e restituisce un array di gruppi
 * di giorni consecutivi con orario identico.
 *
 * @param int $post_id
 * @return array  [ ['days'=>'Lun – Ven', 'hours'=>'08:00–13:00 / 15:00–18:00'], ... ]
 */
function fdr_hours_display( $post_id ) {
    $giorni     = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
    $giorni_ita = [ 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom' ];

    $active = [];
    foreach ( $giorni as $i => $g ) {
        $o1 = get_post_meta( $post_id, '_fdr_' . $g . '_open',   true );
        $c1 = get_post_meta( $post_id, '_fdr_' . $g . '_close',  true );
        if ( ! $o1 || ! $c1 ) continue;
        $o2 = get_post_meta( $post_id, '_fdr_' . $g . '_open2',  true );
        $c2 = get_post_meta( $post_id, '_fdr_' . $g . '_close2', true );
        $h  = $o1 . '–' . $c1;
        if ( $o2 && $c2 ) $h .= ' / ' . $o2 . '–' . $c2;
        $active[] = [ 'idx' => $i, 'label' => $giorni_ita[ $i ], 'hours' => $h ];
    }

    if ( empty( $active ) ) return [];

    // Raggruppa giorni consecutivi con stesso orario
    $groups = [];
    $start  = $active[0];
    $prev   = $active[0];

    for ( $k = 1, $n = count( $active ); $k < $n; $k++ ) {
        $cur = $active[ $k ];
        if ( $cur['hours'] === $prev['hours'] && $cur['idx'] === $prev['idx'] + 1 ) {
            $prev = $cur;
        } else {
            $groups[] = [ 'start' => $start, 'end' => $prev ];
            $start = $prev = $cur;
        }
    }
    $groups[] = [ 'start' => $start, 'end' => $prev ];

    $result = [];
    foreach ( $groups as $gr ) {
        $days     = $gr['start']['label'] === $gr['end']['label']
            ? $gr['start']['label']
            : $gr['start']['label'] . ' – ' . $gr['end']['label'];
        $result[] = [ 'days' => $days, 'hours' => $gr['start']['hours'] ];
    }
    return $result;
}
