# Diavoletto Alert — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mostrare un popup bloccante nella pagina `waiter/tables.php` ogni volta che uno o più tavoli di "I miei Tavoli" hanno il diavoletto 😈 attivo, forzando il cameriere a selezionare il tavolo e navigare verso di esso.

**Architecture:** PHP inietta una variabile JS `miei_diavoletti` (array JSON) direttamente nell'HTML della sezione "I miei Tavoli". Un file JS separato legge questa variabile al `DOMContentLoaded` e, se non vuota, mostra un overlay modale a due layer (lista tavoli → conferma → redirect). Il popup appare ad ogni caricamento/refresh.

**Tech Stack:** PHP 5.5 (no `??`, no `fn=>`), JS vanilla (no ES6 arrow functions, no jQuery), HTML inline styles.

---

## File map

| File | Azione | Responsabilità |
|---|---|---|
| `include/tables_waiter.php` | Modifica righe 985–1000 | Accumula `$lista_diavoletti[]` nel loop, emette `var miei_diavoletti = ...` dopo il loop per `$show==2` |
| `javascript/diavoletto_alert.js` | Crea | Tutta la logica del popup (overlay, layer lista, layer conferma, redirect) |
| `templates/default/tables.tpl` | Modifica | Aggiunge `<script src>` per il nuovo file JS |
| `templates/BAR/tables.tpl` | Modifica | Aggiunge `<script src>` per il nuovo file JS |
| `templates/iphone/tables.tpl` | Modifica | Aggiunge `<script src>` per il nuovo file JS |

---

## Task 1: Modifica PHP — emetti `miei_diavoletti` per `$show==2`

**Files:**
- Modify: `include/tables_waiter.php:985-1000`

- [ ] **Step 1: Sostituisci il blocco loop+return**

Apri `include/tables_waiter.php`. Trova le righe 985–1001 (il blocco `while...return $output`).

Sostituisci questo blocco:

```php
	while ($arr = mysql_fetch_array ($res)) {
		$output .= '	<tr>'."\n";
		for ($i=0;$i<$cols;$i++){

			$output .= tables_list_cell($arr);
			if($i != ($cols - 1)) {
				$arr = mysql_fetch_array ($res);
			}
		}
		$output .= '	</tr>'."\n";
	}
	$output .= '	</tbody>
				</table>
			';
	return $output;
}
```

Con questo:

```php
	$lista_diavoletti = array();

	while ($arr = mysql_fetch_array ($res)) {
		$output .= '	<tr>'."\n";
		for ($i=0;$i<$cols;$i++){

			if ($show == 2 && $arr) {
				$sid = (int)$arr['id'];
				if (!table_is_closed($sid)
					&& ci_sono_ordini_nel_tavolo($sid)
					&& !controlla_tempo_massimo_tavolo_fermo($sid)
					&& !controlla_tempo_massimo_ordine_fermo($sid)) {
					$lista_diavoletti[] = array(
						'nome'     => $arr['name'],
						'sourceid' => $sid,
						'link'     => 'orders.php?data[sourceid]='.$sid
					);
				}
			}

			$output .= tables_list_cell($arr);
			if($i != ($cols - 1)) {
				$arr = mysql_fetch_array ($res);
			}
		}
		$output .= '	</tr>'."\n";
	}
	$output .= '	</tbody>
				</table>
			';

	if ($show == 2) {
		$output .= '<script>var miei_diavoletti = '.json_encode($lista_diavoletti).';</script>';
	}

	return $output;
}
```

- [ ] **Step 2: Verifica visiva nel browser**

Apri `http://localhost/mhr/waiter/tables.php` con un utente cameriere.

Apri DevTools → Console. Digita `miei_diavoletti` e premi invio.

Risultato atteso: array (vuoto `[]` se nessun diavoletto attivo, oppure con oggetti `{nome, sourceid, link}` per ogni tavolo fermo).

- [ ] **Step 3: Commit**

```bash
git add include/tables_waiter.php
git commit -m "feat: emetti miei_diavoletti JSON in tables_list_all show=2"
```

---

## Task 2: Crea `javascript/diavoletto_alert.js`

**Files:**
- Create: `javascript/diavoletto_alert.js`

- [ ] **Step 1: Crea il file**

Crea `javascript/diavoletto_alert.js` con questo contenuto:

```javascript
document.addEventListener('DOMContentLoaded', function () {
    if (typeof miei_diavoletti === 'undefined' || miei_diavoletti.length === 0) {
        return;
    }
    mostraListaDiavoletti(miei_diavoletti);
});

function creaOverlay() {
    var overlay = document.createElement('div');
    overlay.id = 'diavoletto-overlay';
    overlay.style.cssText = [
        'position:fixed',
        'top:0',
        'left:0',
        'width:100%',
        'height:100%',
        'background:rgba(0,0,0,0.85)',
        'z-index:99999',
        'display:flex',
        'align-items:center',
        'justify-content:center'
    ].join(';');
    return overlay;
}

function creaBox() {
    var box = document.createElement('div');
    box.id = 'diavoletto-box';
    box.style.cssText = [
        'background:#8B0000',
        'color:white',
        'border:3px solid #FF6600',
        'border-radius:10px',
        'padding:30px',
        'max-width:420px',
        'width:90%',
        'text-align:center',
        'box-sizing:border-box'
    ].join(';');
    return box;
}

function mostraListaDiavoletti(lista) {
    var overlay = creaOverlay();
    var box = creaBox();

    var titolo = document.createElement('h2');
    titolo.style.cssText = 'color:white;font-size:28px;margin:0 0 10px 0;';
    titolo.innerHTML = '&#128520; ATTENZIONE!';

    var sottotitolo = document.createElement('p');
    sottotitolo.style.cssText = 'color:white;font-size:16px;margin:0 0 20px 0;';
    sottotitolo.textContent = 'I seguenti tavoli sono fermi da troppo tempo:';

    box.appendChild(titolo);
    box.appendChild(sottotitolo);

    for (var i = 0; i < lista.length; i++) {
        (function (item) {
            var btn = document.createElement('button');
            btn.style.cssText = [
                'display:block',
                'width:100%',
                'padding:15px',
                'margin-bottom:10px',
                'background:#FF6600',
                'color:white',
                'border:none',
                'border-radius:5px',
                'font-size:20px',
                'cursor:pointer',
                'min-height:50px'
            ].join(';');
            btn.innerHTML = '&#128520; ' + item.nome;
            btn.onclick = function () {
                mostraConferma(item, lista);
            };
            box.appendChild(btn);
        })(lista[i]);
    }

    overlay.appendChild(box);
    document.body.appendChild(overlay);
}

function mostraConferma(item, lista) {
    var box = document.getElementById('diavoletto-box');
    box.innerHTML = '';

    var titolo = document.createElement('h2');
    titolo.style.cssText = 'color:white;font-size:28px;margin:0 0 10px 0;';
    titolo.innerHTML = '&#128520; ' + item.nome;

    var domanda = document.createElement('p');
    domanda.style.cssText = 'color:white;font-size:18px;margin:0 0 30px 0;';
    domanda.textContent = 'Vuoi aprire questo tavolo?';

    var btnRow = document.createElement('div');
    btnRow.style.cssText = 'display:flex;gap:10px;justify-content:center;';

    var btnIndietro = document.createElement('button');
    btnIndietro.style.cssText = [
        'flex:1',
        'padding:15px',
        'background:#555555',
        'color:white',
        'border:none',
        'border-radius:5px',
        'font-size:18px',
        'cursor:pointer',
        'min-height:50px'
    ].join(';');
    btnIndietro.textContent = 'INDIETRO';
    btnIndietro.onclick = function () {
        var overlay = document.getElementById('diavoletto-overlay');
        overlay.parentNode.removeChild(overlay);
        mostraListaDiavoletti(lista);
    };

    var btnApri = document.createElement('button');
    btnApri.style.cssText = [
        'flex:1',
        'padding:15px',
        'background:#FF6600',
        'color:white',
        'border:none',
        'border-radius:5px',
        'font-size:18px',
        'cursor:pointer',
        'min-height:50px'
    ].join(';');
    btnApri.textContent = 'APRI';
    btnApri.onclick = function () {
        window.location.href = item.link;
    };

    btnRow.appendChild(btnIndietro);
    btnRow.appendChild(btnApri);

    box.appendChild(titolo);
    box.appendChild(domanda);
    box.appendChild(btnRow);
}
```

- [ ] **Step 2: Commit**

```bash
git add javascript/diavoletto_alert.js
git commit -m "feat: aggiungi popup diavoletto alert JS"
```

---

## Task 3: Aggiorna i template — includi il file JS

**Files:**
- Modify: `templates/default/tables.tpl`
- Modify: `templates/BAR/tables.tpl`
- Modify: `templates/iphone/tables.tpl`

- [ ] **Step 1: `templates/default/tables.tpl`**

Trova la riga `</body>` e aggiungi prima di essa:

```html
	<script type="text/javascript" src="../javascript/diavoletto_alert.js"></script>
</body>
```

Il file risultante diventa:

```html
<!-- Template default tables.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		<center>
		  <p>{messages}
		{navbar} </p>
		  <table width="100%" border="0" cellspacing="10" cellpadding="0">
		    <tr>
		      <td align="center" valign="top">{tables} </td>
	        </tr>
	      </table>
		  <br>
		    <div>
			{barra_booking}

{riepilogo}
			</div>
			<br>
			{logout}<br>
		    {generating_time}
		</center>
	<script type="text/javascript" src="../javascript/diavoletto_alert.js"></script>
	</body>
</html>
```

- [ ] **Step 2: `templates/BAR/tables.tpl`**

Trova la riga `</body>` e aggiungi prima di essa:

```html
	<script type="text/javascript" src="../javascript/diavoletto_alert.js"></script>
</body>
```

- [ ] **Step 3: `templates/iphone/tables.tpl`**

Trova la riga `</body>` e aggiungi prima di essa:

```html
	<script type="text/javascript" src="../javascript/diavoletto_alert.js"></script>
</body>
```

- [ ] **Step 4: Commit**

```bash
git add templates/default/tables.tpl templates/BAR/tables.tpl templates/iphone/tables.tpl
git commit -m "feat: includi diavoletto_alert.js nei template tables"
```

---

## Task 4: Test manuale end-to-end

- [ ] **Step 1: Verifica popup con diavoletto attivo**

Prerequisito: un tavolo assegnato al cameriere di test deve avere `catprinted_time` più vecchio di 30 minuti e ordini presenti. Se non esiste, aggiorna manualmente il DB:

```sql
UPDATE mhr_sources SET catprinted_time = DATE_SUB(NOW(), INTERVAL 35 MINUTE)
WHERE userid = <ID_CAMERIERE_TEST> LIMIT 1;
```

Apri `http://localhost/mhr/waiter/tables.php`.

Risultato atteso: popup rosso appare immediatamente, copre la pagina, mostra il tavolo con diavoletto.

- [ ] **Step 2: Verifica layer conferma**

Clicca sul tavolo nel popup.

Risultato atteso: il box mostra il nome del tavolo, la domanda "Vuoi aprire questo tavolo?", i pulsanti INDIETRO e APRI.

- [ ] **Step 3: Verifica INDIETRO**

Clicca INDIETRO.

Risultato atteso: torna alla lista dei tavoli con diavoletto (Layer 1).

- [ ] **Step 4: Verifica APRI**

Clicca sul tavolo, poi APRI.

Risultato atteso: redirect verso `orders.php?data[sourceid]=X` per il tavolo corretto.

- [ ] **Step 5: Verifica assenza popup senza diavoletti**

Assicurati che nessun tavolo del cameriere abbia il diavoletto (o usa un utente cameriere senza tavoli fermi).

Apri `http://localhost/mhr/waiter/tables.php`.

Risultato atteso: nessun popup. In DevTools → Console: `miei_diavoletti` restituisce `[]`.

- [ ] **Step 6: Verifica popup su template BAR**

Se il ristorante usa il template BAR, cambia il template nelle impostazioni utente e ripeti lo Step 1.

Risultato atteso: popup identico.

- [ ] **Step 7: Verifica refresh automatico**

Con diavoletto attivo, lascia girare il refresh automatico (~1 secondo).

Risultato atteso: ad ogni refresh il popup riappare (non viene soppresso tra un refresh e l'altro).
