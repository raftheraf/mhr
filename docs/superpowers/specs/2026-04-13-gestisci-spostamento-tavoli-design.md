# Design — Gestisci Spostamento Tavoli (Pagina Admin)

**Data**: 2026-04-13
**Contesto**: Aggiunge una pagina admin accessibile dalla schermata principale dei tavoli che permette a cassiere/admin di spostare o scambiare due tavoli senza partire dagli ordini di un tavolo specifico. I pulsanti "SPOSTA TAVOLO" e "SCAMBIA TAVOLO" esistenti rimangono invariati.

---

## Obiettivo

Ridurre la confusione del cameriere offrendo un'interfaccia unificata dove si scelgono i due tavoli e il sistema capisce autonomamente se fare uno spostamento (→ tavolo libero) o uno scambio (→ tavolo occupato/sospeso).

---

## Entry Point

In `waiter/tables.php`, aggiungere un pulsante visibile solo a cassiere (`USER_BIT_CASHIER`) e admin (`USER_BIT_CONFIG`), posizionato subito dopo la `barra_booking` (la riga con `<<--Tavoli` e `Prenotazioni-->>`):

```php
if ($user->level[USER_BIT_CASHIER] OR access_allowed(USER_BIT_CONFIG)) {
    $tpl->append('barra_booking', '
        <form action="table_move_admin.php" method="get">
        <input type="submit" value="GESTISCI SPOSTAMENTO TAVOLI" class="button_big">
        </form>
    ');
}
```

Si appende a `{barra_booking}` (non a `{tables}`) così il pulsante appare visivamente sotto la riga `<<--Tavoli | Prenotazioni-->>`.

---

## Pagina `waiter/table_move_admin.php`

### Bootstrap

Identico a `waiter/tables.php`:

```php
session_start();
define('ROOTDIR', '..');
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");
```

Usa il template `tables` esistente (`$tpl->set_waiter_template_file('tables')`).
Navbar: `navbar_empty('tables.php')` — pulsante indietro verso `tables.php`.

### Permessi

Se l'utente non è `USER_BIT_CASHIER` né `USER_BIT_CONFIG`:
```php
$redirect = redirect_waiter('tables.php');
$tpl->append('scripts', $redirect);
// render e exit
```

---

## Modalità GET — Mostra il form

### Select A — "Da tavolo"

Tavoli visibili con `userid != 0` OR `sospeso = 1`, ordinati per `ordernum ASC, name ASC`.

Ogni option mostra:
```
Tavolo 3 — Mario (4 cop.)        ← userid != 0: nome del cameriere + coperti
Tavolo 5 — Sospeso               ← sospeso = 1
```

### Select B — "A tavolo"

Tutti i tavoli visibili tranne quello selezionato in A (gestito via JS `onchange`), ordinati per `ordernum ASC, name ASC`.

Ogni option mostra:
```
Tavolo 7 — Libero                ← userid = 0, nessun ordine
Tavolo 3 — Mario (4 cop.)        ← occupato
Tavolo 5 — Sospeso               ← sospeso
```

### Attributi data per il confirm JS

Ogni `<option>` dei due select porta `data-label="Tavolo 3 — Mario (4 cop.)"`.
Al submit, JS legge i due label e mostra:

```
confirm("Stai spostando Tavolo 3 → Tavolo 7 (libero). Confermare?")
// oppure
confirm("Stai scambiando Tavolo 3 ↔ Tavolo 5 (Bianchi). Confermare?")
```

Il tipo di operazione nel messaggio si determina in JS leggendo se la label di B contiene "Libero".

### HTML del form

```html
<h2>GESTISCI SPOSTAMENTO TAVOLI</h2>
<form method="post" action="table_move_admin.php" onsubmit="return confirmMove(this)">
  <table>
    <tr>
      <td>Da tavolo:</td>
      <td><select name="id_from" id="sel_from" onchange="filterTo()">...</select></td>
    </tr>
    <tr>
      <td>A tavolo:</td>
      <td><select name="id_to" id="sel_to">...</select></td>
    </tr>
  </table>
  <input type="submit" value="ESEGUI" class="button_big">
</form>
```

---

## Modalità POST — Esecuzione

```php
$id_from = (int)$_POST['id_from'];
$id_to   = (int)$_POST['id_to'];

// validazione base
if (!$id_from || !$id_to || $id_from === $id_to) → redirect con errore

// legge stato tavolo destinazione
SELECT userid, sospeso FROM sources WHERE id = $id_to

// rilevamento automatico operazione
if (dest.userid == 0 AND !table_there_are_orders($id_to)):
    $table = new table($id_from)
    $err   = $table->move($id_to)        // sposta
else:
    $table = new table($id_from)
    $err   = $table->swap($id_to)        // scambia

status_report('MOVEMENT', $err)
$redirect = redirect_waiter('tables.php')
$tpl->append('scripts', $redirect)
// render pagina con messaggio e redirect automatico
```

---

## JavaScript (inline nella pagina)

```javascript
function filterTo() {
    var fromVal = document.getElementById('sel_from').value;
    var opts = document.getElementById('sel_to').options;
    for (var i = 0; i < opts.length; i++) {
        opts[i].disabled = (opts[i].value === fromVal);
    }
}

function confirmMove(form) {
    var fromLabel = form.id_from.options[form.id_from.selectedIndex].getAttribute('data-label');
    var toOpt     = form.id_to.options[form.id_to.selectedIndex];
    var toLabel   = toOpt.getAttribute('data-label');
    var isLibero  = toOpt.getAttribute('data-libero') === '1';
    var op        = isLibero ? 'spostando' : 'scambiando';
    var sep       = isLibero ? '\u2192' : '\u2194';
    return confirm('Stai ' + op + ': ' + fromLabel + ' ' + sep + ' ' + toLabel + '. Confermare?');
}
```

---

## File modificati / creati

| File | Modifica |
|---|---|
| `waiter/table_move_admin.php` | **Nuovo** — form GET + esecuzione POST |
| `waiter/tables.php` | Aggiunge pulsante "GESTISCI SPOSTAMENTO TAVOLI" |

---

## Vincoli tecnici

- PHP 5.5 — niente `??`, `fn=>`, spread operator; usare `mysql_*`
- `totale_coperti_per_tavolo()` disponibile da `include/tables_waiter.php`
- `move()` e `swap()` già nella classe `table` in `include/tables_admin.php`
- `table_there_are_orders()` disponibile da `include/tables_waiter.php`
- Compatibilità single-tab: `mhr_tab_id` già gestito da `redir()` e dai redirect standard
