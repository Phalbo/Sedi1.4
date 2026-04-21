# FDR Sedi — Plugin per Federconsumatori

**Versione:** 1.3  
**Sviluppato da:** [Ares 2.0 s.r.l.](https://www.ares20.it) — Roma  
**Per:** Federconsumatori APS  
**Contatto:** andrea.falbo@ares20.it

---

## Descrizione

Plugin WordPress sviluppato su misura da Ares 2.0 s.r.l. per la gestione delle sedi territoriali di Federconsumatori. Permette di gestire, visualizzare e aggiornare le 512+ sedi nazionali tramite una mappa interattiva con ricerca, filtri regionali e pagine dedicate per ogni sede.

---

## Funzionalità principali

### Mappa interattiva (shortcode `[fdr_sedi]`)
- Mappa Leaflet/OpenStreetMap — nessun costo API
- Cluster automatico dei pin con contatore
- Lista sedi scrollabile sincronizzata con la mappa
- Ricerca per città, nome sede o indirizzo
- Filtro per regione
- Popup con indirizzo, telefono, email, orari e link Google Maps
- Nome sede cliccabile (solo se la pagina è pubblica)
- Pin colorati per tipo: giallo con stella per Sede Nazionale, giallo per Sedi Regionali, blu per le altre

### Gestione backend
Ogni sede ha i seguenti campi:
- **Informazioni:** nome esteso, indirizzo, CAP, città, telefono, cellulare, fax, email, sito web, note interne
- **Orari:** apertura e chiusura per ogni giorno della settimana, doppio turno supportato
- **Logo:** caricamento immagine (400×200px consigliati)
- **Contenuto aggiuntivo:** editor semplificato per testo e immagini
- **Posizione:** latitudine, longitudine con link a servizi geocoding gratuiti
- **Tipo sede:** checkbox Sede Regionale / Sede Nazionale
- **Pubblicazione:** controllo visibilità pubblica sul sito

### Pagina pubblica sede
Ogni sede può avere una pagina pubblica con:
- Logo, badge tipo sede, nome
- Informazioni di contatto complete
- Orari di apertura
- Mappa Leaflet centrata sulla sede
- Contenuto aggiuntivo
- Link "Apri in Google Maps"
- Pulsante "← Tutte le sedi"

### Import massivo
Importa tutte le sedi da file CSV tramite **Sedi → Importa CSV**.  
Il CSV deve avere le colonne: `name, company, address1, zip, city, region, telephone, mobile, fax, email, website, lat, lng, premium` più le colonne orari.

### Ruolo utente dedicato
Il ruolo **"Editor Sedi Federconsumatori"** permette di:
- Modificare e pubblicare sedi
- Caricare loghi e immagini
- Vedere **solo** il menu Sedi nel backend (nessun accesso ad articoli, pagine o impostazioni)

---

## Installazione

1. Carica il file `fdr-sedi.zip` da **Plugin → Aggiungi nuovo → Carica plugin**
2. Attiva il plugin
3. Vai su **Sedi → Importa CSV** e carica il file CSV delle sedi
4. Crea una pagina "Sedi" e aggiungi il widget Shortcode con `[fdr_sedi]`
5. Pubblica la pagina

---

## Shortcode

```
[fdr_sedi]                        — tutte le sedi
[fdr_sedi regione="lazio"]        — solo le sedi di una regione (slug in minuscolo)
```

---

## Aggiornamento plugin

1. Disattiva il plugin attuale
2. Carica il nuovo zip da **Plugin → Aggiungi nuovo → Carica plugin**
3. Attiva — il database con tutte le sedi rimane intatto

---

## Note tecniche

- Mappa: [Leaflet.js](https://leafletjs.com/) + [OpenStreetMap](https://www.openstreetmap.org/) — nessun costo, nessuna API key
- Cluster: [Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster)
- Geocoding coordinate: [LatLong.net](https://www.latlong.net/) o [OpenStreetMap Nominatim](https://nominatim.openstreetmap.org/)
- Tutti i dati sono salvati nel database WordPress come custom post type `fdr_sede`
- L'editor del contenuto aggiuntivo è deliberatamente semplificato (no font, no colori) per mantenere la coerenza visiva del sito

---

## Sviluppato da

**Ares 2.0 s.r.l.**  
Via Taranto, 59 — 00182 Roma  
[www.ares20.it](https://www.ares20.it)  
info@ares20.it

*Plugin sviluppato su commissione per Federconsumatori APS — uso riservato.*
