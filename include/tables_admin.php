<?php
class table extends object {
	function table($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'sources';
		$this->id=$id;
		$this -> title = ucphr('TABLES');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->fields_show=array('id','ordernum','name','takeaway','sospeso','visible','utente_abilitato','tablehtmlcolor');
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'ordernum'=>ucphr('ORDER'),
								'name'=>ucphr('NAME'),
								'takeaway'=>ucphr('TAKEAWAY'),
								'utente_abilitato'=>ucphr('Utente abilitato'),
								'sospeso'=>ucphr('Sospeso'),
								'visible'=>ucphr('VISIBLE'),
								'tablehtmlcolor'=>ucphr('Colore'));
		$this->fields_width=array(	'name'=>'10%');
		$this->allow_single_update = array ('takeaway','visible','sospeso');
		$this->fields_boolean=array('takeaway','visible','sospeso');
		$this -> fetch_data();
	}

	function list_search ($search) {
		$query = '';

		$table = $this->table;
		$lang_table = $table."_".$_SESSION['language'];

		$query="SELECT
				$table.`id`,
				$table.`name`,
				RPAD('".ucphr('TABLES')."',30,' ') as `table`,
				'".TABLE_TABLES."' as `table_ID`
				FROM `$table`
				WHERE $table.`name` LIKE '%$search%'
				";

		return $query;
	}

	function list_query_all () {
		$table = "#prefix#sources";

		$query="SELECT
				$table.`name`,
				IF($table.`visible`='0','".ucphr('NO')."','".ucphr('YES')."') as `visible`,
				IF($table.`takeaway`='0','".ucphr('NO')."','".ucphr('YES')."') as `takeaway`,
				IF($table.`sospeso`='0','".ucphr('NO')."','".ucphr('YES')."') as `sospeso`,
				$table.`ordernum`,
				$table.`utente_abilitato`,
				$table.`tablehtmlcolor`,
				$table.`id`
				FROM `$table`
				";

		return $query;
	}

	function list_rows ($arr,$row) {
		global $tpl;
		global $display;

		$col=0;
		if(!$this->disable_mass_delete) {
			$display->rows[$row][$col]='<input type="checkbox" name="delete[]" value="'.$arr['id'].'">';
			$display->width[$row][$col]='1%';
			$col++;
		}

		foreach ($arr as $field => $value) {
			$link = '';
			if (isset($this->allow_single_update) && in_array($field,$this->allow_single_update)) {
				$link = $this->link_base.'&amp;command=update_field&amp;data[id]='.$arr['id'].'&amp;data[field]='.$field;
				if($this->limit_start) $link .= '&amp;data[limit_start]='.$this->limit_start;
				if($this->orderby) $link.='&amp;data[orderby]='.$this->orderby;
				if($this->sort) $link.='&amp;data[sort]='.$this->sort;

				$display->links[$row][$col]=$link;
			} elseif (method_exists($this,'form')) {
				$link = $this->file.'?class='.get_class($this).'&amp;command=edit&amp;data[id]='.$arr['id'];
			}

			// Colonna "Visibile": checkbox centrale
			if ($field == 'visible' && isset($this->allow_single_update) && in_array('visible',$this->allow_single_update)) {
				$is_yes = (strtoupper($value) == strtoupper(ucphr('YES')));
				$checked = $is_yes ? ' checked="checked"' : '';
				$checkbox = '<input type="checkbox" class="table-visible-flag"'.$checked.' onclick="redir(\''.$link.'\'); return false;">';
				$display->rows[$row][$col] = '<div style="text-align:center;">'.$checkbox.'</div>';
			// Colonna "Colore": usa il codice esadecimale come sfondo della cella e mostra il valore
			} elseif ($field == 'tablehtmlcolor') {
				$color = trim($value);
				if ($color === '') {
					$color = '#FFFFFF';
				}
				$safe_color = htmlentities($color);
				$display->rows[$row][$col] = '<div class="color_table_cell" style="background-color:'.$safe_color.';">'.$safe_color.'</div>';
			} else {
				$display->rows[$row][$col]=$value;
				if($link && $field=='name') $display->links[$row][$col]=$link;
				if($link) $display->clicks[$row][$col]='redir(\''.$link.'\');';
			}

			$col++;
		}
	}

	//RTG: included for performance, better than generic get that imply one query
	//see use in
	function getToClose() {
		return $this->fields_boolean['toclose'];
	}

	function is_empty (){
		$query = "SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return true;

		return !mysql_num_rows($res);
	}

	function total ($discount=0) {
		$total=0;
		$query ="SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$this->id."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return 0;
		while ($arr = mysql_fetch_array ($res)) {
			$total=$total+$arr['price'];
		}

		if($discount) {
			$this->get("discount");
			$total=$total+$discount;
		}

		$total=sprintf("%01.2f",$total);
		return $total;
	}

	function list_orders($output='orders_list',$orderid=0,$mods=false) {
		if($this->is_empty()) return 1;

		global $tpl;
		// RTR NOW Tabella riepilogo ordine testata
		$tmp = '';
		$tpl -> append ($output,$tmp);

		if(!$orderid) {
			$tmp = '
		<table id="tabellalistaordini" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'" border="0" cellspacing="1" cellpadding="0">
		<thead>
		<tr>
		<th scope=col>*</th>
		<th scope=col>n</th>
		<th scope=col>Nome</th>

		<th scope=col>P</th>
		<th scope=col>Euro</th>
		<th scope=col>+/-</th>
		<th scope=col>+</th>
		<th scope=col>-</th>
		</tr>
		</thead>
';
			$tpl -> append ($output,$tmp);
		} else {
			$tmp = '
		<table id="tabellalastorder" width="100%" bgcolor="'.COLOR_TABLE_ULTIMA_OPERAZIONE.'" border="0" cellspacing="1" cellpadding="0">
		<thead>
		<tr>
		<th colspan="8" style="color:#ffffff;"> :::::::::: ULTIMA OPERAZIONE :::::::::: </th>
		</tr>
		</thead>
';
			$tpl -> append ($output,$tmp);
		}

		$tmp = '
		<tbody>';
		$tpl -> append ($output,$tmp);
// RTR START NOW
// Funzione Genera Menu Fisso
// ordina in modo che i menu fissi appaiano per primi
// aggiungere nella tabella orders un campo menu_fisso
// _______________________________________________________________________
		$query="SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$this->id."'";
		if($orderid && $mods) $query .= " AND `associated_id`='".$orderid."'";
		elseif($orderid && !$mods) $query .= " AND `id`='".$orderid."'";

		// Applica il filtro "Mostra ordini cancellati" solo alla lista completa (tabellalistaordini),
		// ma NON alla tabella dell'ultima operazione (tabellalastorder), così l'ultimo ordine
		// è visibile anche se cancellato.
		if(!$orderid && !get_conf(__FILE__,__LINE__,"orders_show_deleted")) $query .= " AND `deleted`='0'";
		// Ordine visualizzazione:
		// 1) coperti (SERVICE_ID)
		// 2) menu fissi
		// 3) tutti gli altri piatti nell'ordine logico esistente
		$query .=" ORDER BY
			CASE WHEN dishid=".SERVICE_ID." THEN 0 ELSE 1 END ASC,
			menu_fisso DESC,
			priority ASC,
			associated_id ASC,
			dishid DESC,
			id ASC";

// ______________________________________________________________________
// RTR END

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		while ($arr = mysql_fetch_array ($res)) {
			$ord = new order ($arr['id']);
			$dishnames[] =$ord ->  table_row_name ($arr);
			unset ($ord);
		}

		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		while ($arr = mysql_fetch_array ($res)) {
			$ord = new order ($arr['id']);
			$tmp = $ord -> table_row ($arr);
			$tpl -> append ($output,$tmp);
			unset ($ord);
		}

		$class = COLOR_TABLE_TOTAL;
		// RTR  Tabella riepilogo ordine totali
		// prints a line with the grand total
		$tmp = '
		<tr>
		<td colspan="4" align="right" bgcolor="'.$class.'"><b>TOTALE : </b></td>
		<td colspan="1" align="right" id="mhr-table-total" bgcolor="'.$class.'"><b>'.$this->total().'</b></td>
		<td colspan="3" align="left" bgcolor="'.$class.'"></td>
		</tr>
		</tbody>
		</table>
		';
		if(!$orderid) $tpl -> append ($output,$tmp);

		// prints a line with the grand total
		$tmp = '
		<tr><td></td></tr>
		</tbody>
		</table>
		';
		if($orderid) $tpl -> append ($output,$tmp);

		return 0;
	}

	function move_list_tables($cols=1){
		global $tpl;

		$output = '';

		$base_conditions  = " AND `toclose` = '0'";
		$base_conditions .= " AND `discount` = '0.00'";
		$base_conditions .= " AND `scontrinato` = '0'";
		$base_conditions .= " AND `paid` = '0'";
		$base_conditions .= " AND `catprinted` = ''";
		$base_conditions .= " AND `catprinted_time` = '0000-00-00 00:00:00'";
		$base_conditions .= " AND `takeaway` = '0'";
		$base_conditions .= " AND `takeaway_surname` = ''";
		$base_conditions .= " AND `prefix_telefono` = '39'";
		$base_conditions .= " AND `telefono` = ''";
		$base_conditions .= " AND `ora_prenotazione` = ''";
		$base_conditions .= " AND `customer` = '0'";
		$base_conditions .= " AND `nota_tavolo` = ''";
		$base_conditions .= " AND `visible` = '1'";
		$base_conditions .= " ORDER BY `ordernum` ASC, `name` ASC";

		// --- Tavoli liberi normali (sospeso = 0) ---
		$query = "SELECT * FROM `#prefix#sources` WHERE `userid`='0' AND `sospeso`='0'".$base_conditions;
		$res = common_query($query, __FILE__, __LINE__);
		if (!$res) return mysql_errno();

		$output .= '<h2>ELENCO DEI TAVOLI LIBERI</h2>';

		if (mysql_num_rows($res)) {
			$output .= '
			<table class="tavoli" id="tabella_tutti_i_tavoli" >
				<tbody>'."\n";

			while ($arr = mysql_fetch_array($res)) {
				$output .= '
	<tr>'."\n";
				for ($i = 0; $i < $cols; $i++) {
					if (!$tmp = $this->move_list_cell($arr)) {
						$i--;
					} else $output .= $tmp;

					if ($i != ($cols - 1)) {
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

		// --- Tavoli sospesi liberi (sospeso = 1) ---
		$query_sosp = "SELECT * FROM `#prefix#sources` WHERE `sospeso`='1'".$base_conditions;
		$res_sosp = common_query($query_sosp, __FILE__, __LINE__);
		if (!$res_sosp) return mysql_errno();

		if (mysql_num_rows($res_sosp)) {
			$output .= '<a id="Tuttiisospesi">Tavoli sospesi</a>';
			$output .= '
			<table class="tavoli" id="tabella_tutti_i_sospesi" >
				<tbody>'."\n";

			while ($arr = mysql_fetch_array($res_sosp)) {
				$output .= '
	<tr>'."\n";
				for ($i = 0; $i < $cols; $i++) {
					if (!$tmp = $this->move_list_cell($arr)) {
						$i--;
					} else $output .= $tmp;

					if ($i != ($cols - 1)) {
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

		if (!$output) return 1;

		$tpl -> assign ('tables',$output);

	return 0;
	}

	function move_list_cell ($arr){
		$query = "SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$arr['id']."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return '';

		if(table_there_are_orders($arr['id'])) return '';

		$link = 'orders.php?command=move&amp;data[id]='.$arr['id'];
		$msg=ucfirst(phr('FREE'));
		$color=$arr['tablehtmlcolor'];

		if($arr['id']){
		$output = '

		<td onmouseover=""
				style="cursor: pointer; border: 3px solid '.$color.';" background-color="'.COLOR_TABLE_FREE.'"
				onclick="redir(\''.$link.'\');">
				<!-- function move_list_tables($cols=1) -->
				<div class="SingoloTavolo">
					<div class="tablenum">'.$arr['name'].'</div>
					<div class="tavoli_msg">'.$msg.'</div>
				</div>

		</td>
		'."\n";
		} else {
		$output = '
		<td bgcolor="'.COLOR_TABLE_FREE.'">
		&nbsp;
		</td>'."\n";
		}
		return $output;
	}

	function move($destination){

		// copies old table info
		$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		$arr_old = mysql_fetch_array($res,MYSQL_ASSOC);

		//delete the info we don't want to copy
		unset ($arr_old['id']);
		unset ($arr_old['name']);
		unset ($arr_old['takeaway']);
		unset ($arr_old['sospeso']);
		unset ($arr_old['ordernum']);
		unset ($arr_old['utente_abilitato']);
		unset ($arr_old['tablehtmlcolor']);
		unset ($arr_old['last_access_time']);


		$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$destination."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		$arr_new=mysql_fetch_array($res,MYSQL_ASSOC);

		// last check before begin: is the table really empty?
		if(
			   $arr_new['userid']!=0
			|| $arr_new['toclose']!=0
			|| $arr_new['discount']!=0.00
			|| $arr_new['scontrinato']!=0
			|| $arr_new['paid']!=0
			|| $arr_new['catprinted']!=''
			|| $arr_new['catprinted_time'] !='0000-00-00 00:00:00'
			|| $arr_new['takeaway_surname']!=''
			|| $arr_new['telefono']!=''
			|| $arr_new['ora_prenotazione']!=''
			|| $arr_new['customer']!=0
			|| $arr_new['nota_tavolo']!=''

																					)
			{
			return 'cerca errore 7421';
		  }

		// moves all the orders
		$query="UPDATE `#prefix#orders` SET `sourceid` = '".$destination."' WHERE `sourceid`='".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// copies table properties
		$query="UPDATE `#prefix#sources` SET ";
		foreach ($arr_old as $key => $value ) {
			$query.="`".$key."`='".addslashes($value)."',";
		}
		// strips the last comma that has been put
		$query = substr ($query, 0, strlen($query)-1);
		$query.=" WHERE `id`='".$destination."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		// empties the old table
		$query = "UPDATE `#prefix#sources` SET
		`userid`='0',
		`toclose`='0',
		`discount` = '0.00',
		`scontrinato` = '0',
		`paid` = '0',
		`catprinted` = '',
		`catprinted_time` = '0000-00-00 00:00:00',
		`last_access_userid` = '0',
		`takeaway_surname` = '',
		`prefix_telefono` = '39',
		`telefono` = '',
		`ora_prenotazione` = '',
		`takeaway_time` = '0000-00-00 00:00:00',
		`customer` = '0',
		`nota_tavolo` = ''
		WHERE `id` = '".$this->id."'";
		$res=common_query ($query,__FILE__,__LINE__);
		if(!$res) return mysql_errno();

		return 0;
	}

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
		if(!$arr_b) return 'cerca errore 7422';

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

	function swap_list_cell($arr,$tipo){
		$link = 'orders.php?command=swap&amp;data[id]='.$arr['id'];
		$color = $arr['tablehtmlcolor'];

		// messaggio stato: nome cameriere per i tavoli occupati, "Sospeso" per i sospesi
		if($tipo === 'sospeso'){
			$msg = 'Sospeso';
		} elseif($arr['userid']){
			$owner = new user((int)$arr['userid']);
			$msg = isset($owner->data['name']) ? $owner->data['name'] : 'Occupato';
		} else {
			$msg = 'Occupato';
		}

		$takeaway_surname = isset($arr['takeaway_surname']) ? $arr['takeaway_surname'] : '';
		$ora_prenotazione = isset($arr['ora_prenotazione']) ? $arr['ora_prenotazione'] : '';
		$coperti = totale_coperti_per_tavolo((int)$arr['id']);

		if($arr['id']){
			$output = '
		<td onmouseover=""
				style="cursor: pointer; border: 3px solid '.$color.';" background-color="'.COLOR_TABLE_FREE.'"
				onclick="redir(\''.$link.'\');">
				<!-- function swap_list_tables -->
				<div class="SingoloTavolo">
					<div class="tablenum">'.$arr['name'].'</div>
					<div class="tavoli_msg">'.$msg.'</div>
					<div class="nome_cliente">'.trim($takeaway_surname.' '.$ora_prenotazione).'</div>';
			if($coperti){
				$output .= '<div class="tabella_tavoli"> Cop.'.$coperti.'</div>';
			}
			$output .= '
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

		// --- Tavoli occupati (userid != 0 OPPURE con dati prenotazione, sospeso = 0) escluso il tavolo corrente ---
		$query  = "SELECT * FROM `#prefix#sources`";
		$query .= " WHERE `sospeso` = '0'";
		$query .= " AND `takeaway` = '0'";
		$query .= " AND `visible` = '1'";
		$query .= " AND `id` != '".$current_id."'";
		$query .= " AND (`userid` != '0' OR `takeaway_surname` != '' OR `telefono` != '' OR `ora_prenotazione` != ''";
		$query .= "  OR (SELECT COUNT(*) FROM `#prefix#orders` WHERE `sourceid` = `#prefix#sources`.`id`) > 0)";
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

	function check_values($input_data){
		$msg="";

		if(!isset($input_data['ordernum']) || $input_data['ordernum']==='') {
			$msg=ucphr('CHECK_ORDER');
		}

		if(!isset($input_data['name']) || $input_data['name']=="") {
			$msg=ucphr('CHECK_NUMBER');
		}

		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return -2;
		}

		if(!isset($input_data['visible']) || !$input_data['visible'])
			$input_data['visible']=0;
		if(!isset($input_data['takeaway']) || !$input_data['takeaway'])
			$input_data['takeaway']=0;

		if(!isset($input_data['utente_abilitato']) || !is_array($input_data['utente_abilitato'])) {
			$input_data['utente_abilitato'] = '';
		} else {
			$ids = array();
			foreach ($input_data['utente_abilitato'] as $uid) {
				$uid = (int)$uid;
				if ($uid > 0) $ids[] = $uid;
			}
			$input_data['utente_abilitato'] = implode(',', $ids);
		}

		return $input_data;
	}

	function form(){
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();

			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['visible']=1;
		}
		$arr_name = isset($arr['name']) ? $arr['name'] : '';
		$arr_ordernum = isset($arr['ordernum']) ? $arr['ordernum'] : '';
		$arr_utente_abilitato = isset($arr['utente_abilitato']) ? $arr['utente_abilitato'] : '';
		$arr_tablehtmlcolor = isset($arr['tablehtmlcolor']) ? $arr['tablehtmlcolor'] : '#FFFFFF';
		$arr_visible = isset($arr['visible']) ? $arr['visible'] : 1;
		$arr_sospeso = isset($arr['sospeso']) ? $arr['sospeso'] : 0;
		$arr_takeaway = isset($arr['takeaway']) ? $arr['takeaway'] : 0;
	$output = '';
	$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('TABLE').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
	if($editing){
		$output .= '
	<input type="hidden" name="command" value="update">';
	} else {
		$output .= '
	<input type="hidden" name="command" value="insert">';
	}
	$output .= '
	<table>
		<tr>
			<td>
			'.ucphr('ID').':
			</td>
			<td>
			'.$arr['id'].'
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>
			'.ucphr('TABLE_NUMBER').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.htmlentities($arr_name).'">
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>
			'.ucphr('TABLE_ORDER').':
			</td>
			<td>
			<input type="text" name="data[ordernum]" value="'.$arr_ordernum.'">
			</td>
			<td>&nbsp;</td>
		</tr>

		<tr>
			<td valign="top">
			'.ucphr('Utente Abilitato per Tavolo Asporto').':
			</td>
			<td>';

	$selected_ids = array();
	if ($arr_utente_abilitato !== '') {
		foreach (explode(',', $arr_utente_abilitato) as $uid) {
			$uid = trim($uid);
			if ($uid !== '') $selected_ids[] = $uid;
		}
	}

	$q_users = common_query("SELECT `id`, `name` FROM `#prefix#users` WHERE `disabled`='0' AND `deleted`='0' ORDER BY `name` ASC", __FILE__, __LINE__);
	$output .= '<select name="data[utente_abilitato][]" multiple="multiple" size="5">';
	if ($q_users) {
		while ($u = mysql_fetch_array($q_users)) {
			$sel = in_array($u['id'], $selected_ids) ? ' selected="selected"' : '';
			$output .= '<option value="'.htmlentities($u['id']).'"'.$sel.'>'.htmlentities($u['name']).'</option>';
		}
	}
	$output .= '</select>';
	$output .= '<br><small>Tieni premuto Ctrl per selezionare pi&ugrave; utenti</small>';

	$output .= '
			</td>
			<td>&nbsp;</td>
		</tr>

		<tr>
			<td align="right">
			Colore del tavolo:</td>
			<td>
			<input type="text" name="data[tablehtmlcolor]" maxlength="7" value="'.htmlentities($arr_tablehtmlcolor).'" id="idcolor">
			</td>
			<td width="10px" height="10px" id="tdcolor"  bgcolor="'.$arr_tablehtmlcolor.'" id="idcolor">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>';
	$output .= html_color_table();

	$output .= '
		<tr>
			<td colspan="3">
			<input type="checkbox" name="data[visible]" value="1"';
			if($arr_visible) $output .= ' checked';
			$output .= '>'.ucphr('VISIBLE_TO_WAITERS').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[sospeso]" value="1"';
			if($arr_sospeso) $output .= ' checked';
			$output .= '>'.ucphr('Tavolo sospeso').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[takeaway]" value="1"';
			if($arr_takeaway) $output .= ' checked';
			$output .= '>'.ucphr('TAKEAWAY').'
			</td>
		</tr>
		<tr>
			<td colspan=2 align="center">
			<table>
			<tr>
				<td>';
	if(!$editing){
		$output .= '
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
				</td>';
	} else {
		$output .= '
				<td>
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
				</td>
				<td>
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
				</td>';
	}
	$output .= '
			</tr>
			</table>
			</td>
		</tr>
	</table>


	</fieldset>
	</td>
	</tr>
	</table>
	</div>';

	return $output;
	}
}
function html_color_row ($bit) {
	$size= 10;

	$output = '';
	// $output = '<tr>'."\n";
	for ($i=200;$i<261;$i=$i+6){
		if($i>255) $i=255;

		$more=$i+150;
		if($more>255) $more=255;
		$less=$i-150;
		if($less<0) $less=0;

		switch($bit) {
			case 1:
				$color='#'.sprintf("%02x",0).sprintf("%02x",$i).sprintf("%02x",0);
				break;
			case 2:
				$color='#'.sprintf("%02x",200).sprintf("%02x",$more).sprintf("%02x",$i);
				break;
			case 3:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$i).sprintf("%02x",0);
				break;
			case 4:
				$color='#'.sprintf("%02x",0).sprintf("%02x",0).sprintf("%02x",$i);
				break;
			case 5:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$i).sprintf("%02x",$less);
				break;
			case 6:
				$color='#'.sprintf("%02x",0).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
			case 7:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$more).sprintf("%02x",$i);
				break;
			case 8:
				$color='#'.sprintf("%02x",200).sprintf("%02x",200).sprintf("%02x",$i);
				break;
			case 9:
				$color='#'.sprintf("%02x",$more).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
			case 10:
				$color='#'.sprintf("%02x",$less).sprintf("%02x",$i).sprintf("%02x",$more);
				break;
			case 11:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",0).sprintf("%02x",$i);
				break;
			case 12:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$more).sprintf("%02x",$more);
				break;
			case 13:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",0).sprintf("%02x",0);
				break;
			default:
				$color='#'.sprintf("%02x",$i).sprintf("%02x",$i).sprintf("%02x",$i);
				break;
		}
		$link = 'color_table(\''.$color.'\');';
		// $link = 'category_form.htmlcolor.value=\''.$color.'\';';
		$output .= '<td class="color_table_cell" onclick="'.$link.'" bgcolor="'.$color.'">&nbsp;</td>'."\n";
	}
	return $output;
}

function html_color_table () {
	$output = '<table width="100%">'."\n";
	for ($i=1;$i<15;$i++) {
		$output .= '<tr>'."\n";
		$output .= html_color_row($i);
		$i++;
		$output .= html_color_row($i);
		$output .= '<tr>'."\n";
	}
	$output .= '</table>'."\n";
	return $output;
}
?>
