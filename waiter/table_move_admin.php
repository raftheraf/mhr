<?php
$inizio = microtime();
session_start();

define('ROOTDIR', '..');
$dont_get_session_sourceid = true;
$dont_redirect_to_menu = true;
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

$GLOBALS['end_require_time'] = microtime();

$tpl->set_waiter_template_file('tables');

$user = new user($_SESSION['userid']);

// Controllo permessi: solo cassiere o admin
if (!$user->level[USER_BIT_CASHIER] && !access_allowed(USER_BIT_CONFIG)) {
	$tpl->append('scripts', redirect_waiter('tables.php'));
	if ($err = $tpl->parse()) return $err;
	$tpl->clean();
	echo $tpl->getOutput();
	exit;
}

// Navbar con pulsante indietro verso tables.php
$tmp = navbar_empty('tables.php');
$tpl->assign('navbar', $tmp);

// Funzione locale: costruisce la label descrittiva di un tavolo
function build_table_label($arr) {
	$label = $arr['name'];
	if ($arr['sospeso'] == '1') {
		$label .= ' — Sospeso';
	} elseif ($arr['userid'] != '0') {
		$owner = new user((int)$arr['userid']);
		$owner_name = (isset($owner->data['name']) && $owner->data['name'] != '') ? $owner->data['name'] : 'Occupato';
		$coperti = (int)totale_coperti_per_tavolo((int)$arr['id']);
		$label .= ' — ' . $owner_name;
		if ($coperti) $label .= ' (' . $coperti . ' cop.)';
	} else {
		$label .= ' — Libero';
	}
	return $label;
}

// Funzione locale: 1 se il tavolo è libero (nessun userid e nessun ordine)
function is_table_free_flag($arr) {
	return ($arr['userid'] == '0' && !table_there_are_orders((int)$arr['id'])) ? '1' : '0';
}

// ---- POST: esecuzione spostamento o scambio ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$id_from = (int)(isset($_POST['id_from']) ? $_POST['id_from'] : 0);
	$id_to   = (int)(isset($_POST['id_to'])   ? $_POST['id_to']   : 0);

	if (!$id_from || !$id_to || $id_from === $id_to) {
		status_report('MOVEMENT', 'parametri non validi');
	} else {
		$query = "SELECT `userid`, `sospeso` FROM `#prefix#sources` WHERE `id`='".$id_to."'";
		$res_dest = common_query($query, __FILE__, __LINE__);
		$arr_dest = ($res_dest) ? mysql_fetch_array($res_dest, MYSQL_ASSOC) : null;

		if (!$arr_dest) {
			status_report('MOVEMENT', 'tavolo destinazione non trovato');
		} else {
			$table = new table($id_from);
			if ($arr_dest['userid'] == '0' && !table_there_are_orders($id_to)) {
				$err = $table->move($id_to);
			} else {
				$err = $table->swap($id_to);
			}
			status_report('MOVEMENT', $err);
		}
	}

	$tpl->append('scripts', redirect_waiter('tables.php'));
	if ($err = $tpl->parse()) return $err;
	$tpl->clean();
	echo $tpl->getOutput();
	exit;
}

// ---- GET: mostra il form ----

// Select A: tavoli occupati (userid != 0) OPPURE sospesi (sospeso = 1)
$query_a  = "SELECT * FROM `#prefix#sources`";
$query_a .= " WHERE (`userid` != '0' OR `sospeso` = '1')";
$query_a .= " AND `visible` = '1'";
$query_a .= " ORDER BY `ordernum` ASC, `name` ASC";
$res_a = common_query($query_a, __FILE__, __LINE__);

// Select B: tutti i tavoli visibili
$query_b  = "SELECT * FROM `#prefix#sources`";
$query_b .= " WHERE `visible` = '1'";
$query_b .= " ORDER BY `ordernum` ASC, `name` ASC";
$res_b = common_query($query_b, __FILE__, __LINE__);

if (!$res_a || !$res_b) {
	$tpl->append('tables', '<p>Errore nel caricamento dei tavoli.</p>');
} elseif (!mysql_num_rows($res_a)) {
	$tpl->append('tables', '<p>Nessun tavolo occupato o sospeso da cui spostarsi.</p>');
} elseif (!mysql_num_rows($res_b)) {
	$tpl->append('tables', '<p>Nessun tavolo disponibile come destinazione.</p>');
} else {
	// Costruisce opzioni select A
	$opts_a = '';
	while ($arr = mysql_fetch_array($res_a, MYSQL_ASSOC)) {
		$label   = build_table_label($arr);
		$escaped = htmlspecialchars($label, ENT_QUOTES);
		$opts_a .= '<option value="'.$arr['id'].'" data-label="'.$escaped.'">'.$escaped.'</option>'."\n";
	}

	// Costruisce opzioni select B
	$opts_b = '';
	while ($arr = mysql_fetch_array($res_b, MYSQL_ASSOC)) {
		$label   = build_table_label($arr);
		$libero  = is_table_free_flag($arr);
		$escaped = htmlspecialchars($label, ENT_QUOTES);
		$opts_b .= '<option value="'.$arr['id'].'" data-label="'.$escaped.'" data-libero="'.$libero.'">'.$escaped.'</option>'."\n";
	}

	$output = '
<h2>GESTISCI SPOSTAMENTO TAVOLI</h2>
<form method="post" action="table_move_admin.php" onsubmit="return confirmMove(this)">
<table cellpadding="8">
<tr>
	<td><b>Da tavolo:</b></td>
	<td>
		<select name="id_from" id="sel_from" onchange="filterTo()" class="button_big">
'.$opts_a.'
		</select>
	</td>
</tr>
<tr>
	<td><b>A tavolo:</b></td>
	<td>
		<select name="id_to" id="sel_to" class="button_big">
'.$opts_b.'
		</select>
	</td>
</tr>
</table>
<br>
<input type="submit" value="ESEGUI" class="button_big">
</form>
<script type="text/javascript">
function filterTo() {
	var fromVal = document.getElementById("sel_from").value;
	var opts = document.getElementById("sel_to").options;
	for (var i = 0; i < opts.length; i++) {
		opts[i].disabled = (opts[i].value === fromVal);
	}
	var selTo = document.getElementById("sel_to");
	if (selTo.options[selTo.selectedIndex] && selTo.options[selTo.selectedIndex].disabled) {
		for (var j = 0; j < selTo.options.length; j++) {
			if (!selTo.options[j].disabled) { selTo.selectedIndex = j; break; }
		}
	}
}
function confirmMove(form) {
	var fromLabel = form.id_from.options[form.id_from.selectedIndex].getAttribute("data-label");
	var toOpt     = form.id_to.options[form.id_to.selectedIndex];
	if (!toOpt || toOpt.disabled) {
		alert("Seleziona un tavolo di destinazione diverso da quello di partenza.");
		return false;
	}
	var toLabel  = toOpt.getAttribute("data-label");
	var isLibero = toOpt.getAttribute("data-libero") === "1";
	var op       = isLibero ? "spostando" : "scambiando";
	var sep      = isLibero ? "\u2192" : "\u2194";
	return confirm("Stai " + op + ": " + fromLabel + " " + sep + " " + toLabel + ". Confermare?");
}
filterTo();
</script>
';

	$tpl->append('tables', $output);
}

// Footer standard
$tmp = disconnect_line();
$tpl->assign('logout', $tmp);
$tmp = generating_time($inizio);
$tpl->assign('generating_time', $tmp);

if ($err = $tpl->parse()) return $err;
$tpl->clean();
$output_final = $tpl->getOutput();
echo $output_final;
if (CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl->print_size();
?>
