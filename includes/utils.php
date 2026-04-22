<?php
/**
 * FDR Sedi — Utility condivise
 */

/**
 * Restituisce un titolo univoco per post fdr_sede.
 *
 * Estrae il titolo base (senza suffisso " (N)"), conta i post esistenti
 * con lo stesso titolo base, e aggiunge il suffisso progressivo se necessario.
 *
 * @param string $title      Titolo in ingresso (può già avere " (N)")
 * @param int    $exclude_id ID del post da escludere dalla ricerca (0 = nessuno)
 * @return string Titolo univoco
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

    // Filtraggio PHP: "base" esatto oppure "base (N)" con N numerico
    $pattern = '/^' . preg_quote( $base, '/' ) . '( \(\d+\))?$/';
    $matches = array_filter( $rows, fn( $t ) => preg_match( $pattern, $t ) );

    $count = count( $matches );
    return $count === 0 ? $base : $base . ' (' . ( $count + 1 ) . ')';
}
