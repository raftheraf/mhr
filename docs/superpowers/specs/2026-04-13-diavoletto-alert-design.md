# Diavoletto Alert — Popup di allerta per I miei Tavoli

**Data:** 2026-04-13  
**Stato:** Approvato

---

## Obiettivo

Quando nella sezione "I miei Tavoli" della pagina `waiter/tables.php` appare un diavoletto 😈 su uno o più tavoli, il cameriere deve essere forzato a prenderne atto tramite un popup bloccante. L'unica azione disponibile è selezionare un tavolo con diavoletto, confermare, e venire reindirizzato alla pagina degli ordini di quel tavolo per cliccare "TOGLI DIAVOLETTO 😈".

---

## Comportamento

- Il popup appare **ad ogni caricamento** della pagina (inclusi i refresh automatici) finché almeno un tavolo in "I miei Tavoli" ha il diavoletto attivo.
- Il popup è **bloccante**: copre tutta la pagina, non ha un pulsante "chiudi" o "annulla" — l'unica interazione è selezionare un tavolo.
- Se ci sono **più tavoli** con diavoletto, tutti vengono elencati nel popup.
- Al click su un tavolo appare un **secondo layer di conferma** (grafico, non `confirm()` nativo).
- Alla conferma → `window.location.href` verso `orders.php?data[sourceid]=X`.
- Al click "Indietro" nella conferma → ritorno al layer 1 (lista tavoli).

---

## Condizione diavoletto

Un tavolo ha il diavoletto quando **tutte** queste condizioni sono vere:

```php
!table_is_closed($sourceid)
&& ci_sono_ordini_nel_tavolo($sourceid)
&& !controlla_tempo_massimo_tavolo_fermo($sourceid)   // catprinted_time > 30 min fa
&& !controlla_tempo_massimo_ordine_fermo($sourceid)
```

Stessa logica già usata in `tavoli_colori_priorita_presenti()` — nessuna nuova query introdotta nel design, le funzioni esistenti vengono richiamate.

---

## Architettura

### File modificati

**`include/tables_waiter.php`** — funzione `tables_list_all()`, ramo `case 2` (I miei Tavoli):

- Durante il loop sui tavoli, per ogni tavolo viene eseguita la verifica diavoletto.
- I tavoli con diavoletto vengono accumulati in `$lista_diavoletti[]` con: `nome`, `sourceid` (int), `link`.
- Dopo il loop, prima del `return`, viene emessa **sempre** (anche vuota) la variabile JS:
  ```php
  $output .= '<script>var miei_diavoletti = ' . json_encode($lista_diavoletti) . ';</script>';
  ```

### File creati

**`waiter/js/diavoletto_alert.js`** — tutto il comportamento del popup:

- Al `DOMContentLoaded`: se `miei_diavoletti.length > 0`, inietta l'overlay nel DOM.
- Stili inline nel JS (nessun CSS esterno aggiuntivo necessario).
- Nessuna dipendenza da jQuery o altre librerie.

**`templates/default/tables.tpl`** (e varianti `BAR/tables.tpl`, `iphone/tables.tpl`):

- Aggiunta del tag `<script src="../waiter/js/diavoletto_alert.js"></script>` nel `<head>` o prima di `</body>`.

---

## UI — Layer 1: Lista tavoli

```
┌─────────────────────────────────┐
│                                 │
│   😈  ATTENZIONE!               │
│                                 │
│  I seguenti tavoli sono fermi   │
│  da troppo tempo:               │
│                                 │
│  ┌─────────────────────────┐    │
│  │  😈 Tavolo 5            │    │
│  └─────────────────────────┘    │
│  ┌─────────────────────────┐    │
│  │  😈 Tavolo 12           │    │
│  └─────────────────────────┘    │
│                                 │
└─────────────────────────────────┘
```

---

## UI — Layer 2: Conferma

```
┌─────────────────────────────────┐
│                                 │
│   😈  Tavolo 5                  │
│                                 │
│  Vuoi aprire questo tavolo?     │
│                                 │
│  ┌──────────┐  ┌─────────────┐  │
│  │ INDIETRO │  │    APRI     │  │
│  └──────────┘  └─────────────┘  │
│                                 │
└─────────────────────────────────┘
```

---

## Stile

- Sfondo overlay: `rgba(0,0,0,0.85)` — copre tutta la pagina, `z-index` altissimo
- Box: sfondo `#8B0000` (rosso scuro), testo bianco, bordo `#FF6600` (arancio fuoco)
- Font grande, ottimizzato per handheld
- Pulsanti: grandi, touch-friendly (min height 50px)
- Pulsante APRI: `#FF6600` con testo bianco
- Pulsante INDIETRO: grigio scuro con testo bianco

---

## Vincoli tecnici

- PHP 5.5 — nessuna sintassi PHP 7+ (no `??`, no `fn=>`)
- `json_encode()` compatibile PHP 5.5 — ok
- JS vanilla (no jQuery, no ES6 arrow functions) — compatibile con browser handheld datati
- I template hanno 3 varianti (`default`, `BAR`, `iphone`) — tutte e tre vanno aggiornate
