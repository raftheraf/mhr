# Scambia Tavolo — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Aggiungere la funzione "SCAMBIA TAVOLO" che permette a cassiere/admin di scambiare ordini e dati operativi tra due tavoli occupati o sospesi.

**Architecture:** Il flusso ricalca esattamente "SPOSTA TAVOLO": pulsante in orders_waiter.php → `ask_swap` mostra la griglia → click → `swap` esegue lo scambio. Lo scambio degli ordini usa `sourceid=0` come valore temporaneo sicuro per evitare collisioni nei due UPDATE.

**Tech Stack:** PHP 5.5, MySQL (`mysql_*`), nessun template nuovo, nessuna modifica al DB.

---

## File modificati

| File | Cosa cambia |
|---|---|
| `include/tables_admin.php` | Aggiunge `swap_list_tables()`, `swap_list_cell()`, `swap()` dopo il metodo `move()` (linea 466) |
| `include/orders_waiter.php` | Aggiunge pulsante "SCAMBIA TAVOLO" dopo il blocco "SPOSTA TAVOLO" (linea 1183) |
| `waiter/orders.php` | Aggiunge `case 'ask_swap'` e `case 'swap'` dopo il blocco `case 'move'` (linea 401) |

---

## Task 1: Metodo `swap()` nella classe `table`

**File:** Modify `include/tables_admin.php` dopo la riga 466 (fine del metodo `move()`)

- [ ] **Step 1: Inserire il metodo `swap($destination)` dopo la chiusura di `move()`**

Inserire il seguente blocco subito dopo `return 0; }` che chiude `move()` (dopo la riga 466):

```php
	function swap($destination){

		$fields_to_skip = array('id','name','ordernum','takeaway','sospeso',
			'utente_abilitato','tablehtmlcolor','last_access_time');

		// legge lo stato del tavolo A (corrente)
		$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		$arr_a = mysql_fetch_array($res,MYSQL_ASSOC);

		// legge lo stato del tavolo B (destinazione)
		$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$destination."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();
		$arr_b = mysql_fetch_array($res,MYSQL_ASSOC);

		// rimuove i campi fissi che non si scambiano
		foreach($fields_to_skip as $f){
			unset($arr_a[$f]);
			unset($arr_b[$f]);
		}

		// sposta ordini A → sourceid temporaneo (0 non è mai un ID tavolo reale)
		$query="UPDATE `#prefix#orders` SET `sourceid`='0' WHERE `sourceid`='".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// sposta ordini B → A
		$query="UPDATE `#prefix#orders` SET `sourceid`='".$this->id."' WHERE `sourceid`='".$destination."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// sposta ordini temporanei (ex-A) → B
		$query="UPDATE `#prefix#orders` SET `sourceid`='".$destination."' WHERE `sourceid`='0'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// copia lo stato di B su A
		$query="UPDATE `#prefix#sources` SET ";
		foreach($arr_b as $key => $value){
			$query.="`".$key."`='".addslashes($value)."',";
		}
		$query = substr($query,0,strlen($query)-1);
		$query.=" WHERE `id`='".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// copia lo stato di A su B
		$query="UPDATE `#prefix#sources` SET ";
		foreach($arr_a as $key => $value){
			$query.="`".$key."`='".addslashes($value)."',";
		}
		$query = substr($query,0,strlen($query)-1);
		$query.=" WHERE `id`='".$destination."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		return 0;
	}
```

- [ ] **Step 2: Verificare la sintassi PHP**

```bash
php -l include/tables_admin.php
```
Risultato atteso: `No syntax errors detected in include/tables_admin.php`

- [ ] **Step 3: Commit**

```bash
git add include/tables_admin.php
git commit -m "feat: aggiungi metodo swap() nella classe table"
```

---

## Task 2: Metodi `swap_list_tables()` e `swap_list_cell()` nella classe `table`

**File:** Modify `include/tables_admin.php` — aggiungere subito dopo il metodo `swap()` appena inserito.

- [ ] **Step 1: Inserire `swap_list_cell()` e `swap_list_tables()`**

Aggiungere il seguente blocco subito dopo la chiusura del metodo `swap()`:

```php
	function swap_list_cell($arr,$tipo){
		$link = 'orders.php?command=swap&amp;data[id]='.$arr['id'];
		$color = $arr['tablehtmlcolor'];
		$msg = ($tipo === 'sospeso') ? ucfirst(phr('SUSPENDED')) : ucfirst(phr('BUSY'));
		// fallback se la chiave non è tradotta
		if(ctype_digit($msg)) $msg = ($tipo === 'sospeso') ? 'Sospeso' : 'Occupato';

		if($arr['id']){
			$output = '
		<td onmouseover=""
				style="cursor: pointer; border: 3px solid '.$color.';" background-color="'.COLOR_TABLE_FREE.'"
				onclick="redir(\''.$link.'\');">
				<!-- function swap_list_tables -->
				<div class="SingoloTavolo">
					<div class="tablenum">'.$arr['name'].'</div>
					<div class="tavoli_msg">'.$msg.'</div>
				</div>
		</td>
		'."\n";
		} else {
			$output = '
		<td>&nbsp;</td>'."\n";
		}
		return $output;
	}

	function swap_list_tables($cols=1){
		global $tpl;

		$current_id = (int)$this->id;
		$output = '';

		$order = " ORDER BY `ordernum` ASC, `name` ASC";

		// --- Tavoli occupati (userid != 0, sospeso = 0) escluso il tavolo corrente ---
		$query  = "SELECT * FROM `#prefix#sources`";
		$query .= " WHERE `userid` != '0'";
		$query .= " AND `sospeso` = '0'";
		$query .= " AND `visible` = '1'";
		$query .= " AND `id` != '".$current_id."'";
		$query .= $order;
		$res = common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		if(mysql_num_rows($res)){
			$output .= '<a id="Tuttiitavoli">Tavoli occupati</a>';
			$output .= '
		<table class="tavoli" id="tabella_tutti_i_tavoli">
			<tbody>'."\n";
			while($arr = mysql_fetch_array($res)){
				$output .= '
	<tr>'."\n";
				for($i=0;$i<$cols;$i++){
					$output .= $this->swap_list_cell($arr,'occupato');
					if($i != ($cols-1)){
						$arr = mysql_fetch_array($res);
					}
				}
				$output .= '
	</tr>';
			}
			$output .= '
	</tbody>
</table>
';
		}

		// --- Tavoli sospesi (sospeso = 1) escluso il tavolo corrente ---
		$query  = "SELECT * FROM `#prefix#sources`";
		$query .= " WHERE `sospeso` = '1'";
		$query .= " AND `visible` = '1'";
		$query .= " AND `id` != '".$current_id."'";
		$query .= $order;
		$res_sosp = common_query($query,__FILE__,__LINE__);
		if(!$res_sosp) return mysql_errno();

		if(mysql_num_rows($res_sosp)){
			$output .= '<a id="Tuttiisospesi">Tavoli sospesi</a>';
			$output .= '
		<table class="tavoli" id="tabella_tutti_i_sospesi">
			<tbody>'."\n";
			while($arr = mysql_fetch_array($res_sosp)){
				$output .= '
	<tr>'."\n";
				for($i=0;$i<$cols;$i++){
					$output .= $this->swap_list_cell($arr,'sospeso');
					if($i != ($cols-1)){
						$arr = mysql_fetch_array($res_sosp);
					}
				}
				$output .= '
	</tr>';
			}
			$output .= '
	</tbody>
</table>
';
		}

		if(!$output) return 1;

		$tpl -> assign('tables',$output);
		return 0;
	}
```

- [ ] **Step 2: Verificare la sintassi PHP**

```bash
php -l include/tables_admin.php
```
Risultato atteso: `No syntax errors detected in include/tables_admin.php`

- [ ] **Step 3: Commit**

```bash
git add include/tables_admin.php
git commit -m "feat: aggiungi swap_list_tables() e swap_list_cell()"
```

---

## Task 3: Pulsante "SCAMBIA TAVOLO" in orders_waiter.php

**File:** Modify `include/orders_waiter.php:1176-1183`

- [ ] **Step 1: Aggiungere il pulsante dopo il blocco "SPOSTA TAVOLO"**

Trovare il blocco (righe 1176-1183):
```php
	if ( $user->level[USER_BIT_CASHIER] OR access_allowed(USER_BIT_CONFIG) ) {
		$tmp = '
		<FORM ACTION="orders.php?command=ask_move" METHOD=POST>
		<INPUT TYPE="submit" value="SPOSTA TAVOLO" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}
```

Sostituirlo con:
```php
	if ( $user->level[USER_BIT_CASHIER] OR access_allowed(USER_BIT_CONFIG) ) {
		$tmp = '
		<FORM ACTION="orders.php?command=ask_move" METHOD=POST>
		<INPUT TYPE="submit" value="SPOSTA TAVOLO" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);

		$tmp = '
		<FORM ACTION="orders.php?command=ask_swap" METHOD=POST>
		<INPUT TYPE="submit" value="SCAMBIA TAVOLO" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}
```

- [ ] **Step 2: Verificare la sintassi PHP**

```bash
php -l include/orders_waiter.php
```
Risultato atteso: `No syntax errors detected in include/orders_waiter.php`

- [ ] **Step 3: Commit**

```bash
git add include/orders_waiter.php
git commit -m "feat: aggiungi pulsante SCAMBIA TAVOLO in orders_waiter"
```

---

## Task 4: Gestione `ask_swap` e `swap` in waiter/orders.php

**File:** Modify `waiter/orders.php` — aggiungere dopo il blocco `case 'move':` (dopo la riga 401)

- [ ] **Step 1: Aggiungere i due case dopo `case 'move':`**

Trovare la riga (dopo `break;` di `case 'move':`):
```php
	case 'service_fee':
```

Inserire prima di essa:
```php
	case 'ask_swap':
				$tpl -> set_waiter_template_file ('tables');

				$tmp = navbar_empty('javascript:history.go(-1);');
				$tpl -> assign('navbar',$tmp);

				$user = new user($_SESSION['userid']);
				if($user->level[USER_BIT_CASHIER]) $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_cashier');
				else $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_waiter');

				$table = new table($_SESSION['sourceid']);
				$table -> swap_list_tables($cols);
				break;
	case 'swap':
				$newtable = $start_data['id'];

				if(!$newtable){
					orders_list();
					break;
				}

				$table = new table($_SESSION['sourceid']);
				$err = $table -> swap($newtable);

				status_report('MOVEMENT',$err);

				orders_list();
				break;
```

- [ ] **Step 2: Verificare la sintassi PHP**

```bash
php -l waiter/orders.php
```
Risultato atteso: `No syntax errors detected in waiter/orders.php`

- [ ] **Step 3: Commit**

```bash
git add waiter/orders.php
git commit -m "feat: aggiungi case ask_swap e swap in waiter/orders.php"
```

---

## Test manuale

- [ ] Aprire un tavolo con ordini (tavolo A)
- [ ] Verificare che il pulsante "SCAMBIA TAVOLO" appaia solo per cassiere/admin
- [ ] Cliccare "SCAMBIA TAVOLO": verificare che appaiano solo tavoli occupati e sospesi, escluso il tavolo A
- [ ] Cliccare un tavolo B: verificare che si torni alla lista ordini del tavolo A
- [ ] Verificare che il tavolo A mostri ora i dati del cliente di B (nome, telefono, ecc.) e gli ordini di B
- [ ] Aprire il tavolo B: verificare che mostri i dati del cliente di A e gli ordini di A
- [ ] Verificare che i dati fissi (nome tavolo, colore) siano rimasti invariati su entrambi
