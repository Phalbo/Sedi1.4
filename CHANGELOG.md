# Changelog — FDR Sedi

## [1.4] — 2026-04-21

### Aggiunto
- **Export CSV**: nuovo pulsante "Esporta CSV" nella pagina Importa/Esporta che scarica tutte le sedi con tutti i campi nel formato compatibile con il re-import. Include BOM UTF-8 per compatibilità Excel.
- **Cancella tutte le sedi**: pulsante nascosto nella "Zona avanzata" della pagina admin. Richiede:
  1. Digitare la parola `CANCELLA` nel campo di testo
  2. Conferma JavaScript con doppio alert di warning
  Operazione irreversibile; il pannello avverte di esportare prima il backup.
- **Cache transient 12 ore**: lo shortcode `[fdr_sedi]` ora memorizza il JSON delle sedi in un transient WordPress (TTL 12 ore) riducendo il carico sul database. La cache viene invalidata automaticamente al salvataggio o alla cancellazione di qualsiasi sede (`save_post_fdr_sede`, `delete_post`).
- **Orari nel popup mappa**: il popup Leaflet ora mostra anche il secondo turno (open2/close2) quando presente, coerentemente con il template pagina singola.
- **Nuovo file `includes/export.php`**: gestisce le action `fdr_sedi_export` e `fdr_sedi_delete_all`.
- **CSV pulito `sedi-import-clean.csv`**: 554 sedi normalizzate dall'export Store Locator originale. Correzioni applicate:
  - Regioni normalizzate in Title Case e unificato il case misto (es. `emilia romagna` → `Emilia Romagna`)
  - Regioni mancanti inferite dalla città/coordinate (16 righe)
  - `pesaro urbino` → `Marche`, `Roma` (città) → `Lazio`
  - CAP `BRINDISI` non numerico rimosso
  - CAP `PISTOIA` corretto da `5100` → `51100`
  - Città normalizzate in Title Case
  - Coordinate con virgola come separatore decimale convertite in punto
  - Email con spazio iniziale rimosso (`lstrip`)
  - Tutte le 554 righe con coordinate valide mantenute (inclusi duplicati legittimi di città)

### Corretto
- **Bug import lat/lng**: le coordinate con virgola come decimale venivano corrette in variabili locali ma poi salvate con il valore originale. Ora il valore corretto viene riscritto in `$data` prima del loop di salvataggio campi.
- **Admin page**: il titolo e il menu ora recitano "Importa / Esporta CSV" invece di solo "Importa CSV".

### Cache
- **Impostazione**: 12 ore (`12 * HOUR_IN_SECONDS`)
- **Chiave transient**: `fdr_sedi_json`
- **Scope**: solo per `[fdr_sedi]` senza parametro `regione`. Quando si usa `[fdr_sedi regione="..."]` la cache non viene usata (dati già filtrati server-side).
- **Invalidazione**: automatica su `save_post_fdr_sede` e su cancellazione di un post `fdr_sede`.

---

## [1.3] — versione precedente

Plugin FDR Sedi v1.3 — Ares 2.0 s.r.l. per Federconsumatori.
Gestione sedi con mappa Leaflet/OpenStreetMap, import CSV, ruolo editor dedicato, template pagina singola.
