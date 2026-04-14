<?php

/**
* Marks a table as paid
*
* @param integer $paid The value to be set to the paid field in sources table
* @return integer error code
*/
function table_pay($paid){
	$sourceid = $_SESSION['sourceid'];
	if(order_found_generic_not_priced($sourceid))
	return
	ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$total=table_total($_SESSION['sourceid']);
	if(!access_allowed(USER_BIT_MONEY) && $total!=0) {
		access_denied_waiter();
		return ERR_ACCESS_DENIED;
	}

	$query = "UPDATE `#prefix#sources` SET `paid` = '$paid' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

// Scrive nel database tabella sources campo 'scontrinato' il valore 1
// serve per identificare i tavoli con una ricevuta o scontrino già stampato
// oppure solamente registrato nel database contabilità.
function tavolo_scontrinato(){
	$sourceid = $_SESSION['sourceid'];

	$query = "UPDATE `#prefix#sources` SET `scontrinato` = '1' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Clear a table
*
* When a table is cleared all the associated orders are deleted and all the table properties resetted
*
* @return integer error code
*/
function table_clear(){
	$sourceid = $_SESSION['sourceid'];
	if(order_found_generic_not_priced($sourceid)) return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;

	$query = "DELETE FROM `#prefix#orders` WHERE `sourceid`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$query="UPDATE `#prefix#sources` SET
	`userid` = '0'
	,`toclose` = '0'
	,`discount` = '0.00'
	,`scontrinato` = '0'
	,`paid` = '0'
	,`catprinted` = ''
	,`catprinted_time` = '0000-00-00 00:00:00'
	,`last_access_time`='0000-00-00 00:00:00'
	,`last_access_userid`='0'
	,`unito`='0'
	,`takeaway_surname`=''
	,`prefix_telefono`='39'
	,`telefono`=''
	,`ora_prenotazione`=''
	,`takeaway_time`='0'
	,`customer`='0'
	,`nota_tavolo`=''
	WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}


/**
* Interface for closed tables
*
* Template: closed_table.tpl
* Assigns: navbar, pay, clear, total.
*
* @return integer error code
*/
function table_closed_interface() {
	global $tpl;

	if(bill_orders_to_print ($_SESSION['sourceid'])) {
		$_SESSION['select_all']=1;
		$err=bill_select();
		if($err) error_display($err);
		return 0;
	}

	$tpl -> set_waiter_template_file ('closed_table');

	$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	$unito=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"unito",$_SESSION['sourceid']);
	$total=table_total($_SESSION['sourceid']);
	$discount=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources','discount',$_SESSION['sourceid']);

	if ($total == 0 && $paid==0) {
		$err = table_pay(1);
		status_report ('PAYMENT',$err);
		$paid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"paid",$_SESSION['sourceid']);
	}

	$tmp = navbar_tables_only();
	$user = new user($_SESSION['userid']);
	if($user->level[USER_BIT_CASHIER]) $tmp = navbar_empty();
	$tpl -> assign ('navbar',$tmp);

	$tmp = '
		'.ucfirst(phr('TABLE_TOTAL_DISCOUNTED')).': <b>'.country_conf_currency(true).' '.$total.'</b>
	';
	if($discount!=0) {
		$discount=format_price_html(abs($discount));
		$tmp .= '
		 ('.ucfirst(phr('DISCOUNT')).': '.country_conf_currency(true).' '.$discount.')';
	}
	$tmp .= '<br>'."\n";
	$tpl -> assign ('total',$tmp);

	if($paid and !$unito){
		$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="clear">
		'.ucfirst(phr('PAID_ALREADY')).'<br>
		'.ucfirst(phr('EMPTY_TABLE_EXPLAIN')).'
		<INPUT TYPE="submit" value="Libera il tavolo" class="button_big">
		</FORM>
		';
		$tmp .= '<br>'."\n";
		$tpl -> assign ('clear',$tmp);
	}
	if($unito){
		$tmp = '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="clear">
		'.ucfirst(phr('<H1>TAVOLO UNITO</H1>')).'<br>
		'.ucfirst(phr('<H1>NON PUOI APPORTARE MODIFICHE</H1>')).'<br>
		<INPUT TYPE="submit" value="Libera il tavolo" class="button_big">
		</FORM>
		';
		$tmp .= '<br>'."\n";
		$tpl -> assign ('clear',$tmp);
	}
	

	// user is not allowed to pay, so don't display the button
	if(!access_allowed(USER_BIT_MONEY)) return 0;

	$tmp = '';
	if(order_found_generic_not_priced($_SESSION['sourceid'])) {
		$tmp .= '<br>
				Devi riaprire il tavolo per aggiornare il prezzo oppure eliminare l\'articolo generico
				<br><br>
				<FORM ACTION="orders.php?command=reopen" METHOD=POST>
				<INPUT TYPE="submit" value=" RIAPRI TAVOLO " class="button_big">
				</form>
				<br><br><br><br>';
		}
	if ($paid){
		$tmp .= '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="pay">
		'.ucfirst(phr('PAID_ASK')).'<br>

		<INPUT TYPE="hidden" name="data[paid]" value="0">
		<INPUT TYPE="submit" value="Segna non pagato" class="button_big">
		<br><br>
		</FORM>
		';
	} elseif (!order_found_generic_not_priced($_SESSION['sourceid'])) {
		$tmp .= '
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="pay">
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="clear">
		<br>
		Il tavolo ha già pagato ed è libero<br>
		<br>
		<INPUT TYPE="submit" value="Paga + Libera Tavolo" class="button_big">
		</FORM>
		<B>Se azzeri il tavolo sarà irrecuperabile</B>
		<br><br><br><br>
		<FORM ACTION="orders.php" METHOD=POST>
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="pay">
		Il tavolo ha pagato ma è ancora occupato
		<br><br>
		<INPUT TYPE="hidden" name="data[paid]" value="1">
		<INPUT TYPE="submit" value="Segna pagato" class="button_big">
		</FORM>
		';

		// $user = new user($_SESSION['userid']);
		if ($user->level[USER_BIT_CASHIER]) {
			$tmp .= '
						<br /><br /><br />
						<i>Se sa stampa del documento non è riuscita correttamente
						<br />Attenzione è necessario verificare in contabilità se presente un documento</i>
						<FORM ACTION="orders.php?command=bill_reset" METHOD=POST>
						<INPUT TYPE="submit" value="Ritorna al conto">
						</form>
						';
					}

	}
	$tmp .= ' ';
	$tmp .= '<br>'."\n";
	$tpl -> assign ('pay',$tmp);

	return 0;
}

/**
* Interface for cleared tables
*
* Template: question.tpl
* Assigns: navbar, question, .
*
* @return integer error code
*/
function table_cleared_interface() {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_menu();
	$tpl -> assign ('navbar',$tmp);

	$tmp = '
	'.ucfirst(phr('TABLE_HAS_BEEN_CLEARED'));
	$tpl -> assign ('question',$tmp);

	$redirect = redirect_waiter('tables.php');
	$tpl -> append ('scripts',$redirect);

	return 0;
}

/**
* Closes a table
*
* @param integer $sourceid
* @return integer error code
*/
function table_close($sourceid){
	global $tpl;

	$query = "SELECT * FROM `#prefix#sources` WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	if(!mysql_num_rows($res)) return ERR_TABLE_NOT_FOUND;

	$query = "UPDATE `#prefix#sources` SET `toclose`='1' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$print=false;
	if(get_conf(__FILE__,__LINE__,"print_remaining_tickets_anyway")) {
		$print=true;
	} elseif (table_is_takeaway($sourceid) && get_conf(__FILE__,__LINE__,"print_remaining_tickets_if_takeaway")) {
		$print=true;
	}

	if($print && printing_orders_to_print($sourceid)){
		$err = print_orders($sourceid);
		status_report ('ORDERS_PRINT',$err);
	}

	return 0;
}

/**
* Table closing confirmation page
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_ask_close() {
	global $tpl;


/* eliminata 07/01/2021 serviva per impedire di chiudere il tavolo senza prima aver inserito un nome del cliente
	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list();
		return 0;
	}
*/

	if(table_is_closed($_SESSION['sourceid'])) {
		table_closed_interface();
		return 0;
	}
	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="close">
	'.ucfirst(phr('CLOSE_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

/**
* Table reopening confirmation page
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_reopen_confirm() {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="reopen">
	'.ucfirst(phr('REOPEN_TABLE_ASK')).'
	</FORM>
	';
	$tpl -> assign ('question',$tmp);

	$tmp = navbar_form('form1','orders.php?command=none');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

/**
* Table reopening
*
* @return integer error code
*/
function table_reopen($sourceid) {
	global $tpl;

	$query = "SELECT * FROM `#prefix#sources` WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	if(!mysql_num_rows($res)) return ERR_TABLE_NOT_FOUND;

	$query = "UPDATE `#prefix#sources` SET `toclose`='0',`paid`='0' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Calculates total with discounts
*
* This function reads all the orders associated to a given table
* and sums their price field values.
* After this the value is formatted with 2 decimal places and returned.
*
* The returned value includes discounts.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return string Total value formatted
*/
function table_total($sourceid){
	$total=table_total_without_discount($sourceid);

	$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr=mysql_fetch_array($res);
	$discount=$arr["discount"];

	$total=$total+$discount;
	$total=sprintf("%01.2f",$total);

	return $total;
}

/**
* Calculates total without discount
*
* This function reads all the orders associated to a given table
* and sums their price field values.
* After this the value is formatted with 2 decimal places and returned.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return string Total value formatted
*/
function table_total_without_discount($sourceid){
	$total=0;

	// Verifica se esiste una quota "conto alla romana": in quel caso il suo price
	// rappresenta già il totale dell'intero tavolo, quindi si somma solo quella
	// ed si escludono i piatti originali (già contabilizzati nella quota).
	$romana_query = "SELECT `price` FROM `#prefix#orders`
		WHERE `sourceid`='$sourceid' AND `deleted`=0 AND `dishid`='".ROMANA_QUOTA_ID."'
		LIMIT 1";
	$romana_res = common_query($romana_query,__FILE__,__LINE__);
	if ($romana_res && mysql_num_rows($romana_res) > 0) {
		$romana_arr = mysql_fetch_array($romana_res);
		$total = $romana_arr['price'];
		$total = sprintf("%01.2f", $total);
		return $total;
	}

	$query ="SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid' AND `deleted`=0 AND `dishid`!='".ROMANA_QUOTA_ID."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	while ($arr = mysql_fetch_array ($res)) {

		//BIG PROBLEM... IL TOTALE TAVOLO SCONTATO RISULTA ZERO perchè TUTTI I PIATTI SONO STATI PAGATI
		//$total=$total+($arr['price']/$arr['quantity'])*($arr['quantity']-$arr['paid']);
		$total=$total+$arr['price'];

	}
	$total=sprintf("%01.2f",$total);

	return $total;
}

/**
* Association confirmation page
*
* Creates navigation bar with home, abort to service fee or orders, ok button.
* Also displays message asking for association.
*
* Template: question.tpl
* Assigns: question, navbar.
*
* @return integer error code
*/
function table_ask_association() {
	global $tpl;

	if (table_is_takeaway($_SESSION['sourceid'])) 
	{
		$err = table_associate ();
		status_report ('ASSOCIATION',$err);

		orders_list ();
		return 0;
	}
	
	if (table_is_sospeso($_SESSION['sourceid'])) 
	{
		//$err = table_associate ();
		orders_service_fee_questions ();
		return 0;
	}


	$tpl -> set_waiter_template_file ('question');

	$tmp = '
	<table>
		<tr>
			<td width=35>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>
			</td>
			<td width=35>';
	if(get_conf(__FILE__,__LINE__,"service_fee_use"))
		$tmp .= '
				<a href="orders.php?command=service_fee"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	else 	$tmp .= '
				<a href="orders.php"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	$tmp .= '
			</td>
			<td width=35>
				<a href="orders.php?command=associate"><img src="'.IMAGE_YES.'" alt="'.ucfirst(phr('YES')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	$tpl -> assign ('navbar',$tmp);

	$tmp = ucfirst(phr('ASSOCIATE_ASK')).'<br>'."\n";
	$tpl -> assign ('question',$tmp);

	return 0;
}

/**
* Associates table
*
* This function checks if the opened table is already associated to a waiter.
* If this is not the case, it associates it to the working waiter.
*
* @return integer error code
*/
function table_associate(){
	// another waiter already is associated to the source
	if (table_is_associated()) return ERR_TABLE_ALREADY_ASSOCIATED;

	$query = "UPDATE `#prefix#sources` SET `userid` = '".$_SESSION['userid']."' WHERE `id` = '".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL_ERROR;

	return 0;
}

/**
* Checks if a table is associated to a waiter
*
* Returns the userid field value of the currently opened table.
* It returns zero on mysql error, too (false not associated).
*
* @return integer Waiterid field value (0 if not associated)
*/
function table_is_associated() {
	$query = "SELECT * FROM `#prefix#sources` WHERE `id`='".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr = mysql_fetch_array($res);
	return $arr['userid'];
}

/**
* Checks if a table is closed
*
* It returns zero on mysql error, too (false not closed).
*
* @param integer $sourceid
* @return integer
*/
function table_is_closed($sourceid) {
	if($cache_out=$GLOBALS['cache_var'] -> get ($GLOBALS['table_prefix'].'sources',$sourceid,'toclose')) return $cache_out;

	$query = "SELECT `toclose` FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr = mysql_fetch_array($res);
	$GLOBALS['cache_var'] -> set ($GLOBALS['table_prefix'].'sources',$sourceid,'toclose',$arr['toclose']);
	return $arr['toclose'];
}

/**
* Dissociates a table from its waiter
*
* First check if it is allowed to dissociate tables (disassociation_allow conf value)
* If dissociation is allowed update table sources setting userid to 0.
*
* @return integer error code
*/
function table_dissociate(){
	if (!get_conf(__FILE__,__LINE__,"disassociation_allow")) return ERR_NOT_ALLOWED_TO_DISSOCIATE;

	$query = "UPDATE `#prefix#sources` SET `userid` = '0' WHERE `id` = '".$_SESSION['sourceid']."'";
	$res=common_query ($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

/**
* Check if the given table is takeaway
*
* This function reads the takeaway field value in the sources table and returns it.
*
* @param integer $table_id
* @return integer takeaway value from sources table
*/
function table_is_takeaway($table_id) {
	$takeaway=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway",$table_id);

	return $takeaway;
}

/**
* Check if the given table is takeaway
*
* This function reads the takeaway field value in the sources table and returns it.
*
* @param integer $table_id
* @return integer takeaway value from sources table
*/

// verifica se il tavolo è un sospeso
function table_is_sospeso($table_id) {
	$sospeso=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"sospeso",$table_id);

	return $sospeso;
}


function table_exists($sourceid) {
	if($cache_out=$GLOBALS['cache_var'] -> get ($GLOBALS['table_prefix'].'sources',$sourceid,'id')) return $cache_out;

	$query="SELECT `toclose` FROM `#prefix#sources` WHERE `id`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr=mysql_fetch_assoc($res);

	$GLOBALS['cache_var'] -> set ($GLOBALS['table_prefix'].'sources',$sourceid,'toclose',$arr['toclose']);
	return mysql_num_rows($res);
}

/**
* Suggests a command for orders.php page
*
* this function analyse the following elements:
* table open/closed
* Waiter cashier or not
* Table is paid
* Waiter is associated to the table
* Table is empty
*
* And suggests a proper command to be executed.
*
* @param integer $sourceid
* @return string command
*/
function table_suggest_command($sourceid) {
	$tbl = new table ($sourceid);

	$paid=$tbl -> data['paid'];
	$tableclosed=$tbl -> data['toclose'];
	$owneruserid=$tbl -> data ['userid'];
	$empty = $tbl -> is_empty();

	$user = new user($_SESSION['userid']);

	if ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid){
		$command="list";
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && $paid){
		$command="list";
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid) {
		$command="closed";
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid) {
		$command="closed";
	} elseif ($_SESSION['userid']==$owneruserid){
		$command="list";
	} elseif(!$empty && $owneruserid==0) {
		if(get_conf(__FILE__,__LINE__,'association_automatic')) $command="associate";
		else $command="ask_association";
	} elseif ($owneruserid==0 && !$tableclosed && $empty) {
		if(get_conf(__FILE__,__LINE__,'association_automatic')) $command="associate";
		else $command="ask_association";
	} else {
		$command="list";
	}
	return $command;
}

/**
* Creates the top line (table - people)
*
* First the table name is taken out from source table,
* then it looks for the service fee orders associated to the table and sums them.
* This second step is active only if the conf value service_fee_use is not zero
* Returns the line formatted like: Table TABLE [- NUMBER people]
*
* @param integer $sourceid
* @return string top_line_string
*/
function table_people_number_line ($sourceid) {
	$output = '';
	$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	if ($arr=mysql_fetch_array ($res)) {
		$_SESSION['tablenum']=$arr['name'];

		// Inizializza i flag per evitare Notice su variabili non definite
		$toprint_2 = false;
		$toprint_3 = false;
		$toprint_4 = false;

		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) $toprint_2=true;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) $toprint_3=true;
		if(categories_orders_present ($sourceid,4) && !categories_printed ($sourceid,4)) $toprint_4=true;

		$output .= '<div>';
		if($toprint_2) $output .= '<div class="titolo_tavolo_quadratino" style=" background-color:'.COLOR_ORDER_PRIORITY_2.';"></div>';
		$output .= ' ';
		$output .= "<b>Tavolo ".$_SESSION['tablenum']."</b>";
		if($sourceid && get_conf(__FILE__,__LINE__,'service_fee_use')) {
			$service_quantity=table_people_number($sourceid);
			$output .= " - ".$service_quantity." Persone";
		}
		$output .= '';
		if($toprint_3) $output .= ' <div class="titolo_tavolo_quadratino" style="background-color:'.COLOR_ORDER_PRIORITY_3.';"></div> ';
		if($toprint_4) $output .= '<div class="titolo_tavolo_quadratino" style="background-color:'.COLOR_ORDER_PRIORITY_4.';"></div>';
		$output .= "</div>";
		//$output .= "<br>\n";
	}
	unset($res);
	unset($arr);
	unset($service_quantity);

	return $output;
}


//RTR Stampa sui tavoli 3 quadratini colorati a seconda che siano presenti dei VAI CON
//RTR Stampa sui tavoli 1 quadratino colorato se sono presenti ordini da stampare fermi da più di 15 minuti
// TODO aggioungere una costante nel config.constants.inc.php

function tavoli_colori_priorita_presenti($sourceid) {
	$output = '';
	$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$query1="SELECT `printed` FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' AND `printed` IS NULL";
	$res1=common_query($query1,__FILE__,__LINE__);
	if(!$res1) return '';
	$num_rows=mysql_num_rows($res1);
	if(!$num_rows OR $num_rows==0) $ordine_non_stampato='';
	if(!$num_rows>0) $ordine_non_stampato='1';

	if ($arr=mysql_fetch_array ($res)) {
		$_SESSION['tablenum']=$arr['name'];

		$toprint_0='';
		$toprint_1='';
		$toprint_2='';
		$toprint_3='';
		$toprint_4='';

		if(!table_is_closed($sourceid) AND ci_sono_ordini_nel_tavolo($sourceid) AND !controlla_tempo_massimo_tavolo_fermo($sourceid) AND !controlla_tempo_massimo_ordine_fermo($sourceid))	$toprint_0=true;
		if(controlla_ordini_da_stampare($sourceid)) $toprint_1=true;
		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) 				$toprint_2=true;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) 				$toprint_3=true;
		if(categories_orders_present ($sourceid,4) && !categories_printed ($sourceid,4)) 				$toprint_4=true;

		$output .= '';

		if($toprint_0) $output .= '<span class="diavoletto-animato">'.ICONA_TAVOLO_TROPPO_TEMPO_FERMO.'</span>'; // inserite le emoticon
		//'<div style=" border:1px solid black; height:10px; width:10px; background-color:'.ICONA_TAVOLO_TROPPO_TEMPO_FERMO.'; margin:0 auto; border-radius:2px; display: inline-block; "></div> ';

		if($toprint_1) $output .= '<span class="campanella-animata">'.ICONA_ORDINE_DA_STAMPARE.'</span>'; //inserite le emoticon
		//'<div style=" border:1px solid black; height:10px; width:10px; background-color:'.ICONA_ORDINE_DA_STAMPARE.'; margin:0 auto; border-radius:2px; display: inline-block; "></div> ';

		if($toprint_2) $output .= '<div style=" border:1px solid black; height:10px; width:10px; background-color:'.COLOR_ORDER_PRIORITY_2.'; margin:0 auto; border-radius:2px; display: inline-block; "></div> ';

		if($toprint_3) $output .= '<div style=" border:1px solid black; height:10px; width:10px; background-color:'.COLOR_ORDER_PRIORITY_3.'; margin:0 auto; border-radius:2px; display: inline-block; "></div> ';

		if($toprint_4) $output .= '<div style=" border:1px solid black; height:10px; width:10px; background-color:'.COLOR_ORDER_PRIORITY_4.'; margin:0 auto; border-radius:2px; display: inline-block; "></div>';

		$output .= '';

	}
	unset($res);
	unset($arr);
	unset($service_quantity);

	return $output;
}

function table_people_number ($sourceid) {
	$query="SELECT SUM(quantity) as quantity FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' AND `dishid`='".SERVICE_ID."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if($arr = mysql_fetch_array ($res)) $qty = $arr['quantity'];
	if(!$qty) $qty = 0;

	return $qty;
}

/**
* Calculates the remaining time before a table is unlocked
*
* When a table is accessed by anyone, it cannot be accessed anymore for a certain period of time defined in
* the lock_time configuration value.
*
* This function gets the last access time from the db table sources (field last_access_time) and subtracts it
* from the current timestamp.
* Compating the result with the confugred lock_time gives you the remaining lock time.
*
* The returned time is in seconds and cannot be less than zero.
*
* @param integer $sourceid
* @return integer Remaining time in seconds
*/
function table_lock_remaining_time($sourceid) {
	$query="SELECT `last_access_time`, `toclose` FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr=mysql_fetch_array($res);
	if(!$arr) return 0;

	// tavolo chiuso: usa refresh_automatic_to_menu come durata del lock
	// se refresh_automatic_to_menu è 0 (disabilitato), fallback su lock_time
	if($arr['toclose']) {
		$lock_time=get_conf(__FILE__,__LINE__,"refresh_automatic_to_menu");
		if(!$lock_time) $lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	} else {
		$lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	}

	$elapsed_time=time()-strtotime($arr['last_access_time']);

	$remaining_time=$lock_time-$elapsed_time;

	if ($remaining_time<0) $remaining_time=0;

	return $remaining_time;
}

/**
* Checks if the table is locked
*
* This function makes cross check to authorize a user to open a table.
*
* The cross check consists of user id check, table owner, lock time expiration.
* It also corrects wrong last_access_time values in the future, by setting them now.
*
* @param integer $sourceid
* @return integer 0 if table is not locked, other on locke table or on mysql error
*/
function table_lock_check($sourceid) {

	$query="SELECT `last_access_time`, `last_access_userid`, `toclose` FROM `#prefix#sources` WHERE `id`='".$sourceid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr=mysql_fetch_array($res);

	// tavolo chiuso: usa refresh_automatic_to_menu come durata del lock
	// se refresh_automatic_to_menu è 0 (disabilitato), fallback su lock_time
	if($arr['toclose']) {
		$lock_time=get_conf(__FILE__,__LINE__,"refresh_automatic_to_menu");
		if(!$lock_time) $lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	} else {
		$lock_time=get_conf(__FILE__,__LINE__,"lock_time");
	}

	$last_access_userid=$arr['last_access_userid'];
	$elapsed_time=time()-strtotime($arr['last_access_time']);

	if($elapsed_time<0 || $elapsed_time>$lock_time){
		// timestamp nel futuro (anomalia) oppure lock scaduto: prendiamo possesso del tavolo

		$query="UPDATE `#prefix#sources` SET `last_access_time` = NULL , `last_access_userid` = '".$_SESSION['userid']."' WHERE `id` = '".$sourceid."' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

	} elseif($last_access_userid==$_SESSION['userid']){
		// lock attivo, siamo i proprietari del tavolo: rinnoviamo il lock

		$query="UPDATE `#prefix#sources` SET `last_access_time` = NULL WHERE `id` = '".$sourceid."' LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

	} else {
		// lock attivo, proprietario diverso: tavolo bloccato

		return ERR_TABLE_IS_LOCKED;
	}

	return 0;
}

function tables_list_all($cols=1,$show=0,$quiet=true){
	/*
	$show possible values:
	0 shows all but takeaway
	1 shows takeaway
	2 shows mine
	3 tavoli sospesi
	*/

	$output = '';

	if(!$quiet) {
		$query = "SELECT * FROM `#prefix#sources`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return '';
		if(!mysql_num_rows ($res)) return ucphr('NO_TABLE_FOUND')."<br>\n";
	}

	switch($show) {
		case 0:
			$query = "SELECT `#prefix#sources`.`id`, `name`, `userid`, `toclose`, `tablehtmlcolor`, `customer`, `takeaway_surname`, `ora_prenotazione`,`#prefix#sources`.`scontrinato`,`#prefix#sources`.`paid`, `#prefix#orders`.`id` AS `order` FROM `#prefix#sources` LEFT JOIN `#prefix#orders` ON `sourceid`=`#prefix#sources`.`id` WHERE `takeaway` = '0' ";
			$query .= " AND `visible` = '1'";
			$query .= " AND `sospeso` = '0'";
			$query .= " GROUP BY `#prefix#sources`.`id` ASC";
			$query .= " ORDER BY `#prefix#sources`.`ordernum`,`name` ASC";
			break;
		case 1:
			$query = "SELECT `#prefix#sources`.`id`, `name`, `userid`, `toclose`, `tablehtmlcolor`, `customer`, `utente_abilitato`, `takeaway_surname`, `ora_prenotazione`,`#prefix#sources`.`scontrinato`,`#prefix#sources`.`paid`, `#prefix#orders`.`id` AS `order` FROM `#prefix#sources` LEFT JOIN `#prefix#orders` ON `sourceid`=`#prefix#sources`.`id` WHERE `takeaway` = '1'";
			$query .= " AND FIND_IN_SET('".$_SESSION['userid']."', `utente_abilitato`) ";
			$query .= " AND `visible` = '1' ";
			$query .= " AND `sospeso` = '0' ";
			$query .= " GROUP BY `#prefix#sources`.`id` ASC";
			$query .= " ORDER BY `#prefix#sources`.`ordernum`,`name` ASC";
			break;
		case 2:
			$query="SELECT `#prefix#sources`.`id`, `name`, `userid`, `toclose`, `tablehtmlcolor`, `customer`, `takeaway_surname`, `ora_prenotazione`, `#prefix#sources`.`scontrinato`,`#prefix#sources`.`paid`, `#prefix#orders`.`id` AS `order` FROM `#prefix#sources` LEFT JOIN `#prefix#orders` ON `sourceid`=`#prefix#sources`.`id` WHERE `userid`='".$_SESSION['userid']."'";
			$query .= " AND `visible` = '1'";
			$query .= " AND `unito` = '0'";
			$query .= " GROUP BY `#prefix#sources`.`id` ASC";
			$query .= " ORDER BY `#prefix#sources`.`ordernum`,`name` ASC";
			break;
		case 3:
			$query = "SELECT `#prefix#sources`.`id`, `name`, `userid`, `toclose`, `tablehtmlcolor`, `customer`, `utente_abilitato`, `takeaway_surname`, `ora_prenotazione`,`#prefix#sources`.`scontrinato`,`#prefix#sources`.`paid`, `#prefix#orders`.`id` AS `order` FROM `#prefix#sources` LEFT JOIN `#prefix#orders` ON `sourceid`=`#prefix#sources`.`id` WHERE `sospeso` = '1'";
			$query .= " AND `visible` = '1' ";
			$query .= " GROUP BY `#prefix#sources`.`id` ASC";
			$query .= " ORDER BY `#prefix#sources`.`ordernum`,`name` ASC";
			break;
		
	}

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$therearerecords=mysql_num_rows ($res);

	if(!$therearerecords) return '';

	$idTabella = '';
	switch($show) {
	case 0:
		//if(access_allowed(USER_BIT_WAITER))
		$output .= '<a id="Tuttiitavoli">Tutti i tavoli</a>';
		$idTabella = ' id="tabella_tutti_i_tavoli" ';
		//aggiorna la pagina tables.php con un hyperlink
		//$output .= '<a href="tables.php">Tutti i tavoli</a>';
		if(access_allowed(USER_BIT_WAITER)){
		$output .= '<input type="text" id="ricerca_tabella_tavoli"
										onkeyup="RicercaNeiTavoli(); RicercaNeiTavoliSospesi()"
										name="ricerca_tabella_tavoli" placeholder="Cerca nei tavoli">
								';
			}
		break;
	case 1:
		$output .= 'Tavoli da Asporto';
		break;
	case 2:
		$output .= 'I miei Tavoli';
		break;
	case 3:
		$output .= 'Tavoli sospesi';
		$idTabella= ' id="tabella_tutti_i_sospesi" ';
		break;
	}

	$output .= '
		<table class="tavoli" '.$idTabella.'>
		<tbody>'."\n";

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
		$output .= '<script>var miei_diavoletti = '.json_encode($lista_diavoletti, JSON_HEX_TAG).';</script>';
	}

	return $output;
}
//Riepilogo Totale Tavoli aperti
function riepilogo_totali_tavoli(){
	$output = '';
	//RTR NOW
	//Totale coperti
	$query = "SELECT SUM(quantity) as numero_coperti  FROM `#prefix#orders` WHERE dishid='".SERVICE_ID."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if($arr = mysql_fetch_array ($res)) $totale_coperti = $arr['numero_coperti'];
	if(!$totale_coperti) $totale_coperti = 0;

	//Totale ordini da incassare
	$query = "SELECT SUM(price) as totale FROM `#prefix#orders`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if($arr = mysql_fetch_array ($res)) $totale_ordini = $arr['totale'];
	if(!$totale_ordini) $totale_ordini = 0;

	//Totale Tavoli aperti
	$query = "SELECT `id` FROM `#prefix#orders` GROUP BY `sourceid`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$tavoli_aperti=mysql_num_rows ($res);

	$output .= '
				<div align="center">
				Tav:
				';
	$output .= '<b>'.$tavoli_aperti.'</b>';

	$output .= ' - Cop: ';
	$output .= '<b>'.$totale_coperti.'</b>';
	$output .= ' - Euro: ';
	$totale_ordini = number_format($totale_ordini, 2, ',','.');
	$output .= '<b>'.$totale_ordini.'</b>';

	$output .= '</div>';

	$output .= '		'."\n";

	return $output;
}


//Funzione Totale coperti per tavolo
function totale_coperti_per_tavolo($sourceid){
$totale_coperti='';
$query = " SELECT SUM(quantity) as totale_coperti_per_tavolo FROM `#prefix#orders`
			WHERE dishid='".SERVICE_ID."' AND `sourceid` ='".$sourceid."' ";
$res=common_query($query,__FILE__,__LINE__);
if(!$res) return 0;

if($arr = mysql_fetch_array ($res)) $totale_coperti_per_tavolo = $arr['totale_coperti_per_tavolo'];
	if(!$totale_coperti) $totale_coperti = 0;
	return $totale_coperti_per_tavolo;
}

function tables_list_cell($row){
	$output = '<!-- function tables_list_cell($row) -->';

	$sourceid=$row['id'];
	$tablenum=$row['name'];
	$owneruserid=$row['userid'];
	$tableclosed=$row['toclose'];
	$scontrinato=$row['scontrinato'];
	$paid=$row['paid'];
	$takeaway_surname=$row['takeaway_surname'];
	// Utente abilitato (può non essere presente in tutte le query)
	$utente_abilitato = isset($row['utente_abilitato']) ? $row['utente_abilitato'] : null;
	// Flag tavolo unito (non sempre selezionato nelle query)
	$se_unito = isset($row['unito']) ? $row['unito'] : 0;
	$totale_coperti_per_tavolo =  totale_coperti_per_tavolo($sourceid);
	$ora_prenotazione = $row['ora_prenotazione'];

	//RTR nome del cliente sui tavoli
	$customer=$row['customer'];
	$customer_name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"name",$customer);
	$customer_surname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"surname",$customer);
	//RTR colore dei tavoli
	$color=$row['tablehtmlcolor'];
	//RTG: in the master query we get the num of the order associated to this table,
	//if any, so we don't need create a table object just to see if is empty.
	$empty = $row['order']==null || $row['order'] == 0;

	$user = new user($_SESSION['userid']);

	// regole per Cassieri
	if ($user->level[USER_BIT_CASHIER] && order_found_generic_not_priced($sourceid)){
		$msg=ucfirst(phr('GENERIC_NOT_PRICED'))."";
		$class=COLOR_TABLE_GENERIC_NOT_PRICED;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid && !$scontrinato){
		$msg=ucfirst(phr('CLOSED'))."";
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && !$paid && $scontrinato){
		$msg="SCONTRINO<br>EMESSO";
		$class=COLOR_TABLE_SCONTRINATO;
	} elseif ( $tableclosed && $user->level[USER_BIT_CASHIER] && $paid && $se_unito ){
		$msg=ucfirst(phr('PAID'))."";
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($tableclosed && $user->level[USER_BIT_CASHIER] && $paid && !$se_unito ){
		$msg=ucfirst(phr('TAVOLO<br>UNITO'))."";
		$class=COLOR_TABLE_NOT_OPENABLE;
	//regole per Camerieri
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid && !$scontrinato) {
		$msg=ucfirst(phr('CLOSED'))."";
		$class=COLOR_TABLE_CLOSED_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && !$paid && $scontrinato){
		$msg="SCONTRINO<br>EMESSO";
		$class=COLOR_TABLE_SCONTRINATO;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid && $se_unito ) {
		$msg=ucfirst(phr('PAID'))."";
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($tableclosed && !$user->level[USER_BIT_CASHIER] && $paid && !$se_unito) {
		$msg=ucfirst(phr('TAVOLO<br>UNITO'))."";
		$class=COLOR_TABLE_NOT_OPENABLE;
	} elseif ($_SESSION['userid']==$owneruserid){
		$msg=ucfirst(phr('OPEN'))."";
		$class=COLOR_TABLE_MINE;
		if(categories_orders_present ($sourceid,4) && !categories_printed ($sourceid,4)) $class=COLOR_ORDER_PRIORITY_2;
		if(categories_orders_present ($sourceid,3) && !categories_printed ($sourceid,3)) $class=COLOR_ORDER_PRIORITY_2;
		if(categories_orders_present ($sourceid,2) && !categories_printed ($sourceid,2)) $class=COLOR_ORDER_PRIORITY_2;
	} elseif(!$empty && $owneruserid==0) {
		$msg=ucfirst(phr('TO_BE_ASSOCIATED'));
		$class=COLORE_TAVOLO_DA_ASSOCIARE;
	} elseif ($owneruserid==0 && !$tableclosed && $empty) {
		$msg=ucfirst(phr('FREE'));
		$class=COLOR_TABLE_FREE;
	} else {
		$user = new user ($owneruserid);
		$ownerusername=$user->data['name'];
		unset($user);

		$msg=$ownerusername;
		$class=COLOR_TABLE_OTHER;
	}

	if($sourceid){
		$link = 'orders.php?data[sourceid]='.$sourceid;
		if(isset($command) && !empty($command)) $link .= '&amp;command='.$command;

		$output .= '

		<td onmouseover=""
				style="cursor: pointer; border: 4px solid '.$color.';	background-color:'.$class.';"
				onclick="redir(\''.$link.'\');">

		<!--Div che contiene il tavolo e le sue info -->
		<div class="SingoloTavolo">
			<div class="tablenum">'.$tablenum.'</div>
			<div class="tavoli_msg">'.$msg.'</div>';	
			
		$output .= '	
			<div class="nome_cliente">'.$takeaway_surname.' '.$ora_prenotazione.'</div>';
			if ($totale_coperti_per_tavolo){
				$output .= '<div class="tabella_tavoli"> Cop.'.$totale_coperti_per_tavolo.'</div>';
				}
		$output .= tavoli_colori_priorita_presenti($sourceid);
		$output .= '
		</div>'."\n";

		/* vecchio codice creava una tabella per ogni tavolo
			$output .= '
			<table class="SingoloTavolo" cellpadding="0px" cellspacing="0px" border="3px" bgcolor="'.$class.'" bordercolor="'.$color.'" bordercolorlight="'.$color.'" bordercolordark="'.$color.'" style="{border: 3px solid '.$color.'}" width="100%" height="100%">
				<tr>
					<td align="center" style="{border: 3px solid '.$color.'}">
					<b>'.$tablenum.'</b><br>
					<div class="tabella_tavoli">'.$msg.'</div>';
				$output .= '<div class="nome_cliente">'.$takeaway_surname.'</div>';

					if ($totale_coperti_per_tavolo){
						$output .= '<div class="tabella_tavoli"> Cop.'.$totale_coperti_per_tavolo.'</div>';
						}
				$output .= '';

				$output .= tavoli_colori_priorita_presenti($sourceid);

				$output .= '

				</td>
			</tr>
			</table>
		</td>
		'."\n";
		*/

	} else {
		$output .= '
		<td bgcolor="'.COLOR_TABLE_FREE.'">
		&nbsp;
		</td>'."\n";
	}
	return $output;
}

function table_there_are_orders($sourceid){
	$query = "SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return mysql_num_rows($res);
}

//Controlla i dati prima di cambiare il takeaway_surname
function table_check_values($input_data){
	global $tpl;

	$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$_SESSION['sourceid']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_TABLE_NOT_FOUND;

	$takeaway_surname_old=$arr['takeaway_surname'];
	$takeaway_time=$arr['takeaway_time'];

	foreach ($input_data as $key => $value) $input_data[$key] =trim ($value);

	$takeaway_time_int=(int) $takeaway_time;
	if($takeaway_time_int &&
	empty($input_data['takeaway_year']) &&
	empty($input_data['takeaway_month']) &&
	empty($input_data['takeaway_day']) &&
	empty($input_data['takeaway_minute']) &&
	empty($input_data['takeaway_hour'])) {
		$input_data['takeaway_year'] = substr($takeaway_time,0,4);
		$input_data['takeaway_month'] = substr($takeaway_time,5,2);
		$input_data['takeaway_day'] = substr($takeaway_time,8,2);
		$input_data['takeaway_hour']=substr($takeaway_time,11,2);
		$input_data['takeaway_minute']=substr($takeaway_time,14,2);
	}

	if($takeaway_time_int==0 &&
	empty($input_data['takeaway_year']) &&
	empty($input_data['takeaway_month']) &&
	empty($input_data['takeaway_day']) &&
	empty($input_data['takeaway_minute']) &&
	empty($input_data['takeaway_hour'])) {
		$input_data['takeaway_day'] = date("d",time());
		$input_data['takeaway_month'] = date("m",time());
		$input_data['takeaway_year'] = date("Y",time());
		$input_data['takeaway_hour'] = date("H",time());
		$input_data['takeaway_minute'] = date("i",time());
	}

	$msg="";
	if(!isset($input_data['customer']) &&
	$takeaway_surname_old!=$input_data['takeaway_surname']) {
		$input_data['customer']=0;
	}

//	if(empty($input_data['takeaway_surname'])) {
//		$msg=ucfirst(phr('CHECK_SURNAME'));
//	}

	if($msg){
		$msg='<font color="Red">'.$msg.'</font> ';
		$tpl -> append ('messages',$msg);
		return -1;
	}

	$input_data['takeaway_time'] = sprintf ("%04d", $input_data['takeaway_year']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_month']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_day']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_hour']);
	$input_data['takeaway_time'] .= sprintf ("%02d", $input_data['takeaway_minute']);
	$input_data['takeaway_time'] .= '00';
	unset($input_data['takeaway_year']);
	unset($input_data['takeaway_month']);
	unset($input_data['takeaway_day']);
	unset($input_data['takeaway_hour']);
	unset($input_data['takeaway_minute']);

	return $input_data;
}

function table_set_customer($sourceid,$input_data){
$input_data=table_check_values($input_data);
	if(!is_array($input_data)) return $input_data;



	// Now we'll build the correct UPDATE query, based on the fields provided
	$query="UPDATE `#prefix#sources` SET ";

	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		//riga di codice originale genera problema con apostrofi
		//$query.="`".$key."`='".addslashes($value)."',";

		//problema risolto con la funzione addslashes()
		$query.="`".$key."`='".addslashes($value)."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);

	$query.=" WHERE `id`='$sourceid'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function dati_prenotazione($sourceid) {
	$query="SELECT * FROM `#prefix#sources` WHERE `id`=$sourceid";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_TABLE_NOT_FOUND;

	$data['takeaway_surname']=$arr['takeaway_surname'];
	$data['customer']=$arr['customer'];
	$takeaway_time=$arr['takeaway_time'];
	$data['prefix_telefono']=$arr['prefix_telefono'];
	$data['telefono']=$arr['telefono'];
	$data['ora_prenotazione']=$arr['ora_prenotazione'];
	$data['nota_tavolo']=$arr['nota_tavolo'];

	if($takeaway_time) {
		$data['prenotazione'] = 'Prenotato alle ';
		$data['takeaway_year'] = substr($takeaway_time,0,4);
		$data['takeaway_month'] = substr($takeaway_time,5,2);
		$data['takeaway_day'] = substr($takeaway_time,8,2);
		$data['takeaway_hour']=substr($takeaway_time,11,2);
		$data['takeaway_minute']=substr($takeaway_time,14,2);
	}
	if($takeaway_time && !empty($data['takeaway_surname'])) {
		return $data;
	}
	// we create a dataset with the actual time


	$data['prenotazione'] = 'Sono le ore ';
	$data['takeaway_day'] = date("d",time());
	$data['takeaway_month'] = date("m",time());
	$data['takeaway_year'] = date("Y",time());
	$data['takeaway_hour'] = date("H",time());
	$data['takeaway_minute'] = date("i",time());

	return $data;
}

//estrae il numero di coperti nel tavolo


//customer_data_form
// template customerdataform
function customer_data_form() {
	global $tpl;
	$data=dati_prenotazione($_SESSION['sourceid']);
	$coperti=totale_coperti_per_tavolo($_SESSION['sourceid']);
	$user = new user($_SESSION['userid']);

	$tmp = '<br>';
	$tmp .= '
	<form action="orders.php" method="post" name="form_takeaway">
		<input type="hidden" name="command" value="set_customer">
		<table>
		<tbody>
		<tr>
		<td colspan="2">
		<b>Cognome prenotazione</b><br>
		';
	if(get_conf(__FILE__,__LINE__,'takeaway_allow_unknown_customer')) {
		$tmp .= '
			<input name="data[takeaway_surname]" type="text" value="'.$data['takeaway_surname'].'" maxlength="255" size="22">';
	} else {
		$tmp .= '
			<input name="data[takeaway_surname]" type="hidden" value="'.$data['takeaway_surname'].'">
			<b>'.$data['takeaway_surname'].'</b>';
	}
	$tmp .= '
			<input type="image" src="'.IMAGE_OK.'" alt="'.ucfirst(phr('TAKEAWAY_SUBMIT')).'" style=" vertical-align:middle; width:32px; height:32px;">
		';
	if ($data['customer']) {
		$cust_id = $data['customer'];
		$cust = New customer ($cust_id);

		$tmp .= ' <a href="orders.php?command=customer_edit_form&amp;data[id]='.$data['customer'].'">
							<img align="absmiddle" src="'.IMAGE_CUSTOMER_KNOWN.'" width="32" height="32" border="0">
							</a>';
		$tmp .= '
						</td>
					</tr>
				<tr>
		<td>
			'.ucfirst(phr('NAME')).'
		</td>
		<td>
			'.$cust -> data['name'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ADDRESS')).'
		</td>
		<td>
			'.$cust -> data['address'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('CITY')).'
		</td>
		<td>
			'.$cust -> data['city'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ZIP')).'
		</td>
		<td>
			'.$cust -> data['zip'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('PHONE')).'
		</td>
		<td>
			'.$cust -> data ['phone'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('MOBILE')).'
		</td>
		<td>
			'.$cust -> data ['mobile'].'
		</td>
		</tr>
		<tr>
		<td>
			<b>CODICE SDI: </b>
		</td>
		<td>
			'.$cust -> data['email'].'
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('VAT_ACCOUNT')).'
		</td>
		<td>
			'.$cust -> data['vat_account'].'
		</td>
		</tr>
		<tr>
		<td>
			Codice Fiscale
		</td>
		<td>
			'.$cust -> data['codice_fiscale'].'
		</td>
		</tr>
		';
	} else {
	$tmp .= '
		</td>
		</tr>';
	}
	
		// inserimento ora prenotazione	
	$tmp .= '
		<tr>
		<td td colspan="2">
		<input type="time" name="data[ora_prenotazione]" value="'.$data['ora_prenotazione'].'" maxlength="9" size="6" 
		list="limittimeslist"> Ora prenotazione
		
		<datalist id="limittimeslist">
		<option value="12:00">
		<option value="12:30">
		<option value="13:00">
		<option value="13:30">
		<option value="14:00">
		<option value="14:30">
		<option value="19:00">
		<option value="19:30">
		<option value="20:00">
		<option value="20:30">
		<option value="21:00">
		<option value="21:30">
		</datalist>
		
		</td>
		</tr>
		<tr>
		<td colspan="2" height="15px"></td>
		</tr>
		';
	
	// inizio numero di telefono
	$tmp .= '
		<tr>
		<td td colspan="2"> <b>+</b><input name="data[prefix_telefono]" type="tel" value="'.$data['prefix_telefono'].'" maxlength="255" size="1"> 
							<input oninput="formatPhoneNum(this)" name="data[telefono]" type="tel" value="'.$data['telefono'].'" maxlength="255" size="10">';	
	if 	($data['telefono']==''){
	$tmp .= ' Telefono';
		} else {
	$tmp .= '	
		<a href="tel:+
						'.str_replace(' ', '', $data['prefix_telefono']).'
						'.str_replace(' ', '', $data['telefono']).'
				">
			<b> 📞CHIAMA</b>
		</a>';
	}
	$tmp .= '
		</td>
		</tr>';
	// fine numero telefono	
	$tmp .= '
		<tr>
		<td colspan="2" height="15px"></td>
		</tr>';
	// inizio WhatsApp
	
		//Nota tavolo
	$tmp .= '	
		<tr>
		<td colspan="2" align="left">
		<i>Nota per il tavolo:</i><br>
		<TEXTAREA NAME="data[nota_tavolo]" COLS=30 ROWS=4>'.$data['nota_tavolo'].'</TEXTAREA>
		</td>
		</tr>
		<tr>
		<td class="tabella_tavoli" colspan="2" align="right">

		<input name="data[takeaway_hour]" type="hidden" value="'.$data['takeaway_hour'].'">
		<input name="data[takeaway_minute]" type="hidden" value="'.$data['takeaway_minute'].'">
		<input name="data[takeaway_day]" type="hidden" value="'.$data['takeaway_day'].'">
		<input name="data[takeaway_month]" type="hidden" value="'.$data['takeaway_month'].'">
		<input name="data[takeaway_year]" type="hidden" value="'.$data['takeaway_year'].'">

			'.$data['prenotazione'].$data['takeaway_hour'].':'.$data['takeaway_minute'].'
			del
			'.$data['takeaway_day'].'/'.$data['takeaway_month'].'/'.$data['takeaway_year'].'
		</td>
		</tr>';
	//fine nota tavolo	
	
	$tmp .= '
		<tr><td colspan="2" height="15px"></td></tr>
		<tr>
		<td td colspan="2">';	
	if 	($data['telefono']==''){
	$tmp .= '';
		} else {
		
		if ($user->level[USER_BIT_CASHIER]){
			//cassiere
			
				//Messaggio di benvenuto
			$tmp .= '	
				<img align="absmiddle" src="../images/Chat_on_WhatsApp.png" width="198" height="42" border="0"><br><br>
				<a href="https://web.whatsapp.com/send/?phone=
				'.str_replace(' ', '', $data['prefix_telefono']).'
				'.str_replace(' ', '', $data['telefono']).'
				&text='.$data['takeaway_surname'].' benvenuto al ristorante Biscione&app_absent=0" target="whatsapp">
				<img align="absmiddle" src="../images/whatsapp-png-image.png" width="32" height="32" border="0">
				<b>Benvenuto</b>
				</a>';
				//Messaggio di tavolo disponibile
				$tmp .= '
				<br><br>
				<a href="https://web.whatsapp.com/send/?phone=
				'.str_replace(' ', '', $data['prefix_telefono']).'
				'.str_replace(' ', '', $data['telefono']).'
				&text='.$data['takeaway_surname'].', '.WHATSAPP_MSG.'&app_absent=0" target="whatsapp">
				<img align="absmiddle" src="../images/whatsapp-png-image.png" width="32" height="32" border="0">
				<b>Tavolo disponibile</b>
				</a>';
				//Messaggio di conferma prenotazione
				$tmp .= '
				<br><br>
				==============================
				<br><br>
				<a href="https://web.whatsapp.com/send/?phone=
				'.str_replace(' ', '', $data['prefix_telefono']).'
				'.str_replace(' ', '', $data['telefono']).'
				&text=
				Buongiorno '.$data['takeaway_surname'].', %0A
				ti chiediamo gentilmente conferma della prenotazione odierna presso il Ristorante Biscione. %0A%0A
				Ora di arrivo: '.$data['ora_prenotazione'].'%0A
				Tavolo con '.$coperti.' posti%0A
				Note: '.$data['nota_tavolo'].'%0A%0A
				Grazie.
				&app_absent=0" target="whatsapp">
				<img align="absmiddle" src="../images/whatsapp-png-image.png" width="32" height="32" border="0">
				<b>Conferma prenotazione</b>
				</a>
				<br><br>';
			
			} else {
			//cameriere	
				$tmp .= '	
				<img align="absmiddle" src="../images/Chat_on_WhatsApp.png" width="198" height="42" border="0"><br><br>
				
				<a href="whatsapp://send/?phone=
				'.str_replace(' ', '', $data['prefix_telefono']).'
				'.str_replace(' ', '', $data['telefono']).'
				&text='.$data['takeaway_surname'].' benvenuto al ristorante Biscione">
				<img align="absmiddle" src="../images/whatsapp-png-image.png" width="32" height="32" border="0">
				<b>Benvenuto</b>
				</a>';
				$tmp .= '
				<br><br>
				<a href="whatsapp://send/?phone=
				'.str_replace(' ', '', $data['prefix_telefono']).'
				'.str_replace(' ', '', $data['telefono']).'
				&text='.$data['takeaway_surname'].', '.WHATSAPP_MSG.'">
				<img align="absmiddle" src="../images/whatsapp-png-image.png" width="32" height="32" border="0">
				<b>Tavolo disponibile</b>
				</a>';
				}
		
		
	}
	$tmp .= '
		</td>
		</tr>';
	// fine WhatsApp	
	
		
	$tmp .= '	
		</tbody>
		</table>
	</form>
	';
	$tpl -> assign ('customerdataform',$tmp);

	return 0;
}

?>
