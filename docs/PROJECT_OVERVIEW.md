## My Handy Restaurant (MHR)

Software POS/gestionale per ristoranti scritto in PHP+MySQL, originalmente da Fabio 'Kilyerd' De Pascale.
Installato su XAMPP Windows 10, accessibile da dispositivi handheld via browser.

---

## Stack tecnico

- **PHP**: 5.5.38 (x86, Thread Safe) — NON supporta `??`, `??=`, `fn=>`, né altre feature PHP 7+
- **Database**: MariaDB (tramite XAMPP)
- **Web server**: Apache + FastCGI (non mod_php)
- **Estensione PHP attiva**: `printer` (stampa diretta su stampanti Windows)
- **Architettura**: client-server HTTP, interfaccia ottimizzata per handheld

> Qualsiasi fix PHP deve usare sintassi compatibile con PHP 5.5.
> Evitare: `??`, `??=`, spread su array associativi, `fn()=>`, `match`, named arguments, `#[Attributes]`.
> Usare sempre `isset($x) ? $x : 0` invece di `$x ?? 0`.

---

## Struttura principale dei moduli

| Cartella/File | Funzione |
|---|---|
| `waiter/` | Sezione cameriere: login, tavoli, ordini, conto |
| `pos/` | Modulo POS (cassa) |
| `manage/` | Sezione gestionale/amministrativa |
| `include/` | Logica core condivisa (bills, orders, ecc.) |
| `include/bills_waiter.php` | Logica conti cameriere (stampa, sconto, separazione) |
| `install.php` | Script di installazione/upgrade |
| `conf/config.constants.inc.php` | Costanti di configurazione avanzate |

---

## Funzionalità chiave

- **Ordini**: tavoli, priorità piatti (1°/2°/3°), modifiche ingredienti
- **Stampa**: ordini su stampanti di reparto, conti/fatture, ticket asporto
- **Conto separato** (`$_SESSION['separated']`): ogni voce ha `finalprice`; può mancare se l'entry è incompleta
- **Sconto**: `$_SESSION['discount']['type']` = `"amount"` | `"percent"` | `""`
- **Asporto (takeaway)**: gestione separata con dati cliente
- **Stock**: aggiornamento automatico giacenze
- **Contabilità**: banche, fornitori, dipendenti, report PDF
- **Multi-lingua**: file lingua in DB + file XML
- **Template di stampa**: personalizzabili per utente

---

## File da NON sovrascrivere mai durante aggiornamenti

- `conf/config.constants.inc.php`
- `conf/config.inc.php`
- `templates/default/prints/receipt.tpl` (contiene IP stampante fiscale)

---

## TODO aperti

- **DANGER**: `str_replace` nei template TM88 cancella parti di codice ESC/POS → bug formattazione su `ticket.tpl`, `priority_to_go.tpl`, `preconto.tpl`. Funzioni coinvolte: `printer_print_row`, `printing_orders_printed_category`, `ristampa_ordini`
- Valutare abbandono stampanti TMU-295 → stampa fatture su TM88 con doppia stampa
- Migliorare integrazione Print-F in Sistema→Stampanti (dati da DB invece che da template/config)
- Funzione **UNISCI TAVOLI** per prenotazioni
- Funzione **UNISCI CONTI** di 2+ tavoli
- Sistemare metodi di pagamento con Print-F (T1=contanti, T2=carte, T3=assegni, T4=non riscosso, T5-T10=altri). Form in `include/bills_waiter.php` → `<LEGEND>CORRISPETTIVO</LEGEND>`
- DB contabilità: rinominare colonna `corrispettivo_non_pagato` → `tipo_corrispettivo`, tipo TEXT, default vuoto

---

## Pattern PHP utili

```php
get_conf(__FILE__,__LINE__,"default_priority");       // legge da mhr_conf
if (access_allowed(USER_BIT_WAITER)) { }              // controllo ruolo cameriere
if (!access_allowed(USER_BIT_WAITER) OR access_allowed(USER_BIT_CONFIG)) { } // solo admin
date("Y-m-d H:i:s");                                  // timestamp per DB
if (table_is_takeaway($_SESSION['sourceid'])) { }     // check asporto
```

---

## Integrazione POS Nexi (ECR17)

Protocollo testuale con pacchetti `STX + payload + ETX + LRC (XOR 0x7F)`.
Implementazione: `POS/ingenico.php` nel worktree `eloquent-williams`.

Comandi principali:
- `s` — Stato terminale
- `P` — Pagamento (importo in centesimi, 8 cifre, es. `00000650` = 6,50€)
- `R` — Reso/storno
- `C` — Chiusura giornaliera
- `A` — Annullamento

Risposta positiva: campo risultato = `"00"`. Il POS risponde con messaggio tipo `E`.
Comunicazione sempre avviata dalla cassa (ECR → POS).

---

## Funzionalità single-tab (anti-duplicazione schede)

- Una sola scheda browser attiva per sessione
- Seconda scheda → redirect a `multi_tab_error.php`
- Chiusura scheda principale → heartbeat scade → lock rilasciato → altra scheda recupera
- Logout (`disconnect.php`) rilascia il lock immediatamente
- Stesso meccanismo in `waiter/` e `pos/`
- Implementato il 02/03/2026, testato con esito positivo

---

## Pattern noti e bug ricorrenti

- `$_SESSION['separated'][$key]['finalprice']` può essere `undefined` → usare `isset()`
- `$_SESSION['discount']` va sempre controllato con `isset` prima di accedere a `['type']`
- Race condition su link con countdown (`<a href="*.php">`) + `enhanceLinks`: il listener si attacca prima che l'href venga completato, generando URL senza `?`
