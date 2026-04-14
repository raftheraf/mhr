# reset.php Fix Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Correggere i 6 problemi rilevati in `reset.php`: autenticazione mancante, `mysql_db_query` deprecato, typo nel case, `each()` rimosso in PHP 8, shutdown senza protezione, codice stock commentato in sospeso.

**Architecture:** Tutte le modifiche sono confinate al file `reset.php`. Non si creano nuovi file. Si seguono i pattern del progetto già esistenti (`access_allowed`, `common_query`, `database_query`).

**Tech Stack:** PHP 5/7, MySQL (mysql_* extension), MHR framework (common_query, access_control.php)

---

## File coinvolti

- **Modifica:** `reset.php` (unico file)
- **Riferimento (solo lettura):** `include/access_control.php` — contiene `access_allowed()`
- **Riferimento (solo lettura):** `funs_common.php` — contiene `common_query()` e `database_query()`
- **Riferimento pattern:** `admin/restore.php` — esempio di `access_allowed(USER_BIT_CONFIG)`

---

## Task 1: Aggiungere controllo autenticazione

**Problema:** `reset.php` è accessibile da chiunque senza login. Può fare TRUNCATE di tutte le tabelle o spegnere il server.

**Pattern di riferimento:** `admin/restore.php` riga 48: `if(!access_allowed(USER_BIT_CONFIG)) $command='access_denied';`

**Files:**
- Modifica: `reset.php`

- [ ] **Step 1: Aggiungere l'include di access_control.php**

In `reset.php`, dopo la riga `require("./include/cache_class.php");` (riga 39), aggiungere:

```php
require_once("./include/access_control.php");
```

- [ ] **Step 2: Aggiungere il blocco di controllo accesso**

Dopo la riga `unset_source_vars();` (riga 52) e prima dello `switch`, aggiungere:

```php
if(!access_allowed(USER_BIT_CONFIG)) {
    echo '<p style="color:red;font-weight:bold;">Accesso negato. Permesso CONFIG richiesto.</p>';
    echo "<a href=\"waiter/tables.php\">".ucfirst(lang_get($_SESSION['language'],'BACK_TO_TABLES'))."</a><br>\n";
    echo generating_time($inizio);
    echo '</center></body></html>';
    exit;
}
```

- [ ] **Step 3: Test manuale**

Aprire `http://localhost/mhr/reset.php` senza essere loggati.
Risultato atteso: messaggio "Accesso negato" e nessuna operazione eseguita.

Aprire dopo il login con utente che ha `USER_BIT_CONFIG`.
Risultato atteso: menu reset visibile normalmente.

- [ ] **Step 4: Commit**

```bash
git add reset.php
git commit -m "fix: aggiunge controllo accesso USER_BIT_CONFIG a reset.php"
```

---

## Task 2: Sostituire mysql_db_query con i wrapper del progetto

**Problema:** `mysql_db_query($db, $query)` è una funzione rimossa in PHP 7. Il progetto usa già `common_query()` e `database_query()` come wrapper.

**Note:** `mysql_pconnect` è pattern progetto-wide, rimane invariato.

**Files:**
- Modifica: `reset.php`

- [ ] **Step 1: Sostituire le query su $db_common**

Ci sono 4 occorrenze di `mysql_db_query($db_common, ...)` nelle righe 75, 99, 139, 230, 258.
Sostituirle tutte con `common_query($query, __FILE__, __LINE__)`:

Riga 75 — `reset_orders1` e `halt1`:
```php
// PRIMA:
$res = mysql_db_query($db_common,"TRUNCATE $table");
// DOPO:
$res = common_query("TRUNCATE $table", __FILE__, __LINE__);
```

Riga 99 — `reset_orders1`:
```php
// PRIMA:
$res = mysql_db_query($db_common,"TRUNCATE $table");
// DOPO:
$res = common_query("TRUNCATE $table", __FILE__, __LINE__);
```

Riga 119-120 — `reset_sources1` (TRUNCATE orders):
```php
// PRIMA:
$res = mysql_db_query($db_common,"TRUNCATE $table");
// DOPO:
$res = common_query("TRUNCATE $table", __FILE__, __LINE__);
```

Riga 139 — `reset_sources1` (UPDATE sources):
```php
// PRIMA:
$res = mysql_db_query($db_common,$query);
// DOPO:
$res = common_query($query, __FILE__, __LINE__);
```

Riga 166-169 — `reset_all1` (TRUNCATE customers, orders, last_orders):
```php
// PRIMA:
$res = mysql_db_query($db_common,"TRUNCATE $table");
// DOPO:
$res = common_query("TRUNCATE $table", __FILE__, __LINE__);
```

Riga 230 — `reset_all1` (UPDATE sources):
```php
// PRIMA:
$res = mysql_db_query($db_common,$query);
// DOPO:
$res = common_query($query, __FILE__, __LINE__);
```

Riga 259 — `reset_access_times1`:
```php
// PRIMA:
$res = mysql_db_query($db_common,$query);
// DOPO:
$res = common_query($query, __FILE__, __LINE__);
```

- [ ] **Step 2: Sostituire le query su $_SESSION['common_db']**

Riga 174 — `reset_all1` (SELECT accounting_dbs):
```php
// PRIMA:
$res = mysql_db_query ($_SESSION['common_db'],$query);
// DOPO:
$res = common_query($query, __FILE__, __LINE__);
```

- [ ] **Step 3: Sostituire le query su $arr['db'] (database contabilità)**

Riga 196 — check esistenza tabella:
```php
// PRIMA:
$res_local = mysql_db_query ($arr['db'],$query);
// DOPO:
$res_local = database_query($query, __FILE__, __LINE__, $arr['db']);
```

Riga 199 — TRUNCATE tabella contabilità:
```php
// PRIMA:
$res3 = mysql_db_query($arr['db'],$query_local);
// DOPO:
$res3 = database_query($query_local, __FILE__, __LINE__, $arr['db']);
```

- [ ] **Step 4: Verifica che non restino occorrenze di mysql_db_query**

```bash
grep -n "mysql_db_query" reset.php
```
Risultato atteso: nessun output.

- [ ] **Step 5: Commit**

```bash
git add reset.php
git commit -m "fix: sostituisce mysql_db_query con common_query/database_query in reset.php"
```

---

## Task 3: Fix typo nel case 'unset_all_1'

**Problema:** Il link generato a riga 304 porta a `?command=unset_all0`, che poi posta su `command=unset_all1`. Il case handler è però `'unset_all_1'` (con underscore extra), quindi non viene mai raggiunto.

**Files:**
- Modifica: `reset.php` riga 284

- [ ] **Step 1: Correggere il nome del case**

```php
// PRIMA (riga 284):
case 'unset_all_1':
// DOPO:
case 'unset_all1':
```

- [ ] **Step 2: Test manuale**

Navigare a `reset.php` → cliccare "Reset Sessione utente" → spuntare la checkbox → Submit.
Risultato atteso: sessione azzerata, pagina con sfondo `COLOR_BACK_OK`.

- [ ] **Step 3: Commit**

```bash
git add reset.php
git commit -m "fix: corregge typo case unset_all_1 → unset_all1 in reset.php"
```

---

## Task 4: Sostituire each() deprecato con foreach

**Problema:** `each()` è stato rimosso in PHP 8. Riga 193 usa il pattern `for (reset($arr); list($key,$value) = each($arr);)`.

**Files:**
- Modifica: `reset.php` righe 193-209

- [ ] **Step 1: Sostituire il loop**

```php
// PRIMA (riga 193):
for (reset ($truncate); list ($key, $value) = each ($truncate); ) {
    $table_local=$GLOBALS['table_prefix'].$value;
    $query="SELECT * FROM `$table_local`";
    $res_local = database_query($query, __FILE__, __LINE__, $arr['db']);
    if(mysql_num_rows($res_local)) {
        $query_local='TRUNCATE TABLE `'.$table_local.'`';
        $res3 = database_query($query_local, __FILE__, __LINE__, $arr['db']);
        if($errno=mysql_errno()) {
            $msg="Error in ".__FUNCTION__." - ";
            $msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
            $msg.='query: '.$query_local."\n";
            error_msg(__FILE__,__LINE__,$msg);
            echo nl2br($msg)."\n";
            return $errno;
        }
    }
}

// DOPO:
foreach ($truncate as $value) {
    $table_local=$GLOBALS['table_prefix'].$value;
    $query="SELECT * FROM `$table_local`";
    $res_local = database_query($query, __FILE__, __LINE__, $arr['db']);
    if(mysql_num_rows($res_local)) {
        $query_local='TRUNCATE TABLE `'.$table_local.'`';
        $res3 = database_query($query_local, __FILE__, __LINE__, $arr['db']);
        if($errno=mysql_errno()) {
            $msg="Error in ".__FUNCTION__." - ";
            $msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
            $msg.='query: '.$query_local."\n";
            error_msg(__FILE__,__LINE__,$msg);
            echo nl2br($msg)."\n";
            return $errno;
        }
    }
}
```

- [ ] **Step 2: Test manuale**

Navigare a `reset.php` → "Reset totale" → spuntare → Submit.
Risultato atteso: nessun errore PHP, sfondo `COLOR_BACK_OK`, tabelle contabilità svuotate.

- [ ] **Step 3: Commit**

```bash
git add reset.php
git commit -m "fix: sostituisce each() deprecato con foreach in reset.php"
```

---

## Task 5: Proteggere il comando halt (spegni PC)

**Problema:** Il comando `halt0`/`halt1` esegue `/sbin/shutdown -h now` via `system()`. Già protetto da Task 1 (access_allowed globale), ma il comando è così distruttivo che merita una verifica doppia esplicita. Inoltre funziona solo su Linux; su Windows genererebbe un errore silenzioso.

**Files:**
- Modifica: `reset.php` case `halt1` (riga 70-83)

- [ ] **Step 1: Aggiungere guard esplicito e gestione OS**

```php
case 'halt1':
    if(!access_allowed(USER_BIT_CONFIG)) {
        echo '<p style="color:red;">Accesso negato.</p>';
        break;
    }
    if($_POST['halt']==1){
        echo '<body bgcolor='.COLOR_BACK_OK.'>';
        if($_POST['reset']==1){
            $table=$GLOBALS['table_prefix'].'orders';
            $res = common_query("TRUNCATE $table", __FILE__, __LINE__);
            echo "$msg_reset_orders_ok<br>";
        }
        if(PHP_OS_FAMILY === 'Windows') {
            $out = system("shutdown /s /t 0", $outerr);
        } else {
            $out = system("/sbin/shutdown -h now", $outerr);
        }
        echo "$msg_halt_ok<br>";
    }
    break;
```

- [ ] **Step 2: Test manuale (solo verifica che non dia errori)**

Navigare a `reset.php` → "spegni PC" → verificare che la pagina mostri il form di conferma con checkbox. Non procedere oltre in produzione (spegnerebbe il server).

- [ ] **Step 3: Commit**

```bash
git add reset.php
git commit -m "fix: aggiunge guard accesso e supporto Windows al comando halt in reset.php"
```

---

## Task 6: Rimuovere il codice stock commentato

**Problema:** Riga 213-214 contiene codice morto commentato:
```php
$table=$GLOBALS['table_prefix'].'dishes';
$query="UPDATE $table SET `stock` = '0'";
//$res = mysql_db_query($db_common,$query);
```
La query viene costruita ma mai eseguita. È codice in sospeso da anni. Va rimosso o attivato con decisione esplicita.

**Decisione:** rimuovere le 3 righe (non attivare l'azzera-stock nel reset totale senza una feature request esplicita).

**Files:**
- Modifica: `reset.php` righe 212-214

- [ ] **Step 1: Rimuovere il blocco commentato**

Eliminare le righe:
```php
				$table=$GLOBALS['table_prefix'].'dishes';
				$query="UPDATE $table SET `stock` = '0'";
				//$res = mysql_db_query($db_common,$query);
```

- [ ] **Step 2: Verifica**

```bash
grep -n "dishes\|stock.*=.*0" reset.php
```
Risultato atteso: nessun output.

- [ ] **Step 3: Commit**

```bash
git add reset.php
git commit -m "chore: rimuove blocco stock commentato mai attivato da reset.php"
```

---

## Checklist finale post-implementazione

- [ ] Nessuna occorrenza di `mysql_db_query` in reset.php
- [ ] Nessuna occorrenza di `each(` in reset.php
- [ ] Case `unset_all1` raggiungibile (non `unset_all_1`)
- [ ] Blocco `access_allowed` presente prima dello switch
- [ ] Guard esplicito nel case `halt1`
- [ ] Blocco stock rimosso
- [ ] Test manuale del menu default: tutti e 6 i link funzionano
