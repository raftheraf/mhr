<?php
function printing_choose ($from_bill_print=false) {
	global $tpl;

/*funzione disattivata 01/01/2021 serviva per impedire di stampare ordini senza inserire prima il Cliente

	if(!takeaway_is_set($_SESSION['sourceid'])) {
		$tmp = '<font color="Red">'.ucfirst(phr('SET_TAKEAWAY_SURNAME_FIRST')).'</font>';
		$tpl -> append ('messages',$tmp);
		orders_list();
		return 0;
	}

*/
	$user = new user($_SESSION['userid']);

	if(table_is_closed($_SESSION['sourceid']) && (!$user->level[USER_BIT_CASHIER] || $from_bill_print)) {
		table_closed_interface();
		return 0;
	}

	$tpl -> set_waiter_template_file ('printing');

	$tmp = printing_commands();
	$tpl -> append ('commands',$tmp);

	$tmp = navbar_empty();
	$tpl -> assign ('navbar',$tmp);
}

//RTR START riepilogo stampa priorità
function riepilogo_stampa_categorie ($sourceid, $category) {
$query ="SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$_SESSION['sourceid']."'";
	$query.=" AND `priority`=$category AND `deleted`=0 AND `printed` IS NOT NULL";
	//$query.=" AND `dishid`!=".MOD_ID;
	$query.=" AND `dishid`!='".SERVICE_ID."'";
	$query.=" AND `suspend`=0";
	$query.=" ORDER BY dest_id ASC, priority ASC, menu_fisso DESC, associated_id ASC, id ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

		$output = '';
		$output .= '<br><br>';
		$output .= '<table  class="ticket">';
		$output .= '<tr><td colspan="3" align="left"><br><div class="ticket" id="negativo">&nbsp; Priorità: '.$category.'</div><br></td></tr>';
		while ($arr = mysql_fetch_array ($res)) {
		$priority = $arr['priority'];
		$dishid = $arr['dishid'];
		$id = $dishid;
		$disname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','name',$id);
		$quantity = $arr['quantity'];
		$dishprice = $arr['price'];
		$nota_ordine = $arr['nota_ordine'];

		$ingreid = $arr['ingredid'];
		$id = $ingreid;
		$ingreidname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'ingreds','name',$id);

			//Stampa la Tabella a Video
			//$output .= $sourceid;
			//$output .= 'Priorità: '.$priority';
			if ($dishid==MOD_ID){
			$output .= '<tr>';
			} else {
			$output .= '<tr>';
			}

			$output .= '<td align="right">';
			//$output .= $dishid;
			$output .= '</td>';

			$output .= '<td align="right">';

			if ($dishid==MOD_ID){
			$output .= ' ';
			} else {
			$output .= $quantity;
			$output .= ' &nbsp;';
			}
			$output .= '</td>';

			if ($dishid==MOD_ID){
			$output .= '<td align="left" class="modified">';
			$output .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			if ($arr['operation']==1) {
				$output .= '<b>+ </b>';
			} elseif ($arr['operation']==-1) {
				$output .= '<b>- </b>';
			} elseif ($arr['operation']==0) {
				$output .= '';
			}
			$output .= $ingreidname;
			if($arr['ingred_qty']==1) {
				$output .= ' '.lang_get($dest_language,'PRINTS_LOT');
			} elseif($arr['ingred_qty']==-1) {
				$output .= ' '.lang_get($dest_language,'PRINTS_FEW');
			}	elseif($arr['ingred_qty']==0) {
				$output .= '';
			}
			$output .= '</td>';
			} else {
			$output .= '<td align="left">';
			$output .= $disname;
			$output .= '<br><div class="font10px">'.$nota_ordine.'</div>';
			$output .= '</td>';
			}

			//if ($dishid==MOD_ID){
			//$output .= '<td align="right" class="modified">';
			//$output .= '';
			//$output .= $dishprice;
			//$output .= '</td>';
			//} else {
			//$output .= '<td align="right">';
			//$output .= $dishprice;
			//$output .= '</td>';
			//}

			$output .= '</tr>';

		}
		$output .= '<tr><td colspan="3" align="center"><br></td></tr>';
		$output .= '</table>';
		return $output;
}
//FINE RTR fine riepilogo stampa priorità

//RTR START riepilogo stampa
function riepilogo_stampa ($sourceid, $priority){

	$query="SELECT * FROM `#prefix#orders`  WHERE `sourceid`='$sourceid' AND `priority`='$priority' AND `suspend`='0' AND `printed` IS NULL AND `deleted`='0' ORDER BY
	dest_id ASC, priority ASC, menu_fisso DESC, associated_id ASC, id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$output = '';
	$i = 0;

	if(!mysql_num_rows($res)) {
		$output .= '<br><div align=center><h2>Nessun ordine da stampare</h2></div>';
		return $output;
	}


	while ($arr = mysql_fetch_array ($res)) {
	//priorità
	$priority = $arr['priority'];
	$dishid = $arr['dishid'];
	$id = $dishid;
	$disname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','name',$id);
	$quantity = $arr['quantity'];
	$dishprice = $arr['price'];
	$nota_ordine = $arr['nota_ordine'];

	$ingreid = $arr['ingredid'];
	$id = $ingreid;
	$ingreidname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'ingreds','name',$id);



	//Stampa la Tabella a Video
	//$output .= $sourceid;
	//$output .= 'Priorità: '.$priority';
	if ($dishid==MOD_ID){
	$output .= '<tr>';
	} else {
	$output .= '<tr><td colspan="4">&nbsp</td></tr>
							<tr>';
	}

	$output .= '<td align="right" valign="top">';
	//$output .= $dishid;
	$output .= '</td>';

	$output .= '<td align="right" valign="top">';

	if ($dishid==MOD_ID){
	$output .= '';
	} else {
	$output .= '<b class="ticket_numero_cerchiato">'.$quantity.'</b>';
	$output .= '';
	}
	$output .= '</td>';

	if ($dishid==MOD_ID){
	$output .= '<td align="left" class="modified" valign="top">';
	$output .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	if ($arr['operation']==1) {
				$output .= '<b>+ </b>';
			} elseif ($arr['operation']==-1) {
				$output .= '<b>- </b>';
			} elseif ($arr['operation']==0) {
				$output .= '';
			}
	$output .= $ingreidname;
	if($arr['ingred_qty']==1) {
				$output .= ' '.lang_get($dest_language,'PRINTS_LOT');
			} elseif($arr['ingred_qty']==-1) {
				$output .= ' '.lang_get($dest_language,'PRINTS_FEW');
			}	elseif($arr['ingred_qty']==0) {
				$output .= '';
	}
	$output .= '</td>';
	} else {
	$output .= '<td align="left" valign="bottom">';
	$output .= '<text class="font20px">'.$disname.'</text>';
	$output .= '<br><div class="font10px">'.$nota_ordine.'</div>';

	$output .= '</td>';
	}

	if ($dishid==MOD_ID){
	$output .= '<td align="right" valign="top" class="modified">';
	$output .= '';
	$output .= $dishprice;
	$output .= '</td>';
	} else {
	$output .= '<td align="right" valign="top">';
	$output .= $dishprice;
	$output .= '</td>';
	}
	$output .= '</tr>';

	$i++;

	}
	return $output;
}
//RTR END riepilogo di stampa

function printing_commands(){
	$output = '';

	$sourceid=$_SESSION['sourceid'];

	$query="SELECT `priority` FROM `#prefix#orders`  WHERE `suspend`='0' AND `sourceid`='$sourceid' AND `printed` IS NULL AND `deleted`='0' GROUP BY `priority`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(!mysql_num_rows($res)) {
	$output .= '<br><div align=center><h2>Nessun ordine da stampare</h2></div>';
	} else {

	//inizia la tabella del riepilogo ordine prima di stamparlo a video
	$output .= '';

	$coperti = table_people_number($sourceid);
	$cliente=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway_surname",$sourceid);
	$nota_tavolo=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"nota_tavolo",$sourceid);

	$output .= '
				<!-- Tabella riepilogo ordini da stampare -->
				<table class="ticket">
				<tr><td colspan="4"><br><div class="ticket" id="cliente">
				<b>&nbsp; Coperti: '.$coperti.'</b>
				</div></td></tr>';
	if($cliente){
	$output .= '
				<tr><td colspan="4"><div class="ticket" id="cliente">&nbsp; Cliente: '.$cliente.'
				</div></td></tr>
			';
	}
	if($nota_tavolo){
	$output .= '
				<tr><td colspan="4"><div class="ticket" id="cliente"><i>&nbsp; Nota: '.$nota_tavolo.'</i>
				</div></td></tr>
			';
	}


	while ($arr = mysql_fetch_array ($res)) {
		$priority = $arr['priority'];
		$table_number=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$sourceid);

		// Stampa la priorità a video
		$output .= '
			<tr><td colspan="4"><br><div class="ticket" id="negativo">&nbsp; Priorità: '.$priority.'</div><br></td></tr>';

		$output .= riepilogo_stampa($sourceid, $priority);

		$output .= '<tr><td colspan="4" align="center"><br></td></tr>';
	}
	$output .= '</table>';
	$output .= '';
	}
	if(printing_orders_to_print($sourceid)){

		$output .= '
		<form action="orders.php?command=print_orders" METHOD=POST>
		<INPUT TYPE="submit" value="STAMPA ORDINI" class="button_big">
		</form>
		';
		$output .= '';
	}

	if(!$_SESSION['catprinted'][2] && printing_orders_printed_category (2)){

	$output .=riepilogo_stampa_categorie ($sourceid, 2);

		$output .='
		<FORM ACTION="orders.php?command=print_category&amp;data[category]=2" METHOD=POST>
		<INPUT TYPE="submit" value="Parti con i secondi" class="button_big">
		</form>
		';
		$output .= '';
	}
	//se tolti i commenti non stampa più messaggi parti con i primi
	if(!$_SESSION['catprinted'][3] && printing_orders_printed_category (3)){

		$output .=riepilogo_stampa_categorie ($sourceid, 3);

		$output .= '
		<FORM ACTION="orders.php?command=print_category&amp;data[category]=3" METHOD=POST>
		<INPUT TYPE="submit" value="Parti con i terzi" class="button_big">
		</form>
		';
		$output .= '';
	}
	//RTR
	if(!$_SESSION['catprinted'][4] && printing_orders_printed_category (4)){
		//Tolta la riga <a href="orders.php?command=print_category&amp;data[category]=4"><h1><b>PARTI CON GLI ULTIMI</b></h1></a>

		$output .=riepilogo_stampa_categorie ($sourceid, 4);

		$output .= '
		<FORM ACTION="orders.php?command=print_category&amp;data[category]=4" METHOD=POST>
		<INPUT TYPE="submit" value="Parti con ultimi" class="button_big">
		</form>
		';
		$output .= '';
	}

// Se attive le rihe sotto fanno apparire alcune righe per la stampa dei conti separati
// e del reset conti separati nella pagina di stampa ordini
/*
	if(bill_orders_to_print ($_SESSION['sourceid'])) {
	//RTR eliminato poichè i camerieri stampano il conto senza chiudere il tavolo CASINO
		$output .= '
					<hr>
					<br>
					<a href="orders.php?command=bill_select_all"><h1><b>STAMPA CONTO</b></h1></a>
					<br>
					';
	}

	$user = new user($_SESSION['userid']);
	if ($user->level[USER_BIT_CASHIER]) {


	if(bill_orders_to_print ($_SESSION['sourceid'])) {
		$output .= '
					<hr>
					<br>
					<a href="orders.php?command=bill_select">Stampa conti separati</a>
					<br><br>
					<hr>

					<br>
					';
		}
		$output .= '
					<a href="orders.php?command=bill_reset">Azzera contatore conti sepatati</a><br>
					<br>
					';
	}
*/


	return $output;
}

/**
* Number of orders to be printed
*
* This function reads all the orders associated to a given table
* that have not yet been deleted or printed and that are not suspended and quantify them.
* The returned number includes modifications orders, thus means it could be greater than real but never less than that.
*
* Note: this function will return 0 if a MySQL error occurs.
*
* @param integer $sourceid
* @return integer
*/
function printing_orders_to_print($sourceid) {
	$query="SELECT * FROM `#prefix#orders`  WHERE `sourceid`='$sourceid' AND `suspend`='0' AND `printed` IS NULL AND `deleted`='0' ORDER BY id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return mysql_num_rows($res);
}

function printer_print_row($arr,$destid){
	$msg = '';
	$extra = '';
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);


	if ($arr['dishid']==MOD_ID){
		$modingred=$arr['ingredid'];
		$modingrednumber= $modingred;

		$query = "SELECT * FROM `#prefix#ingreds` WHERE id='$modingrednumber'
		AND #prefix#ingreds.deleted = '0'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return '';
		$arr2 = mysql_fetch_array ($res2);
		if ($arr2!=FALSE) {
			$ingredobj = new ingredient ($modingrednumber);
			$moddeddishname = $ingredobj -> name ($dest_language);
		}

		if ($arr['operation']==1) {
			$dishname="+";
		} elseif ($arr['operation']==-1) {
			$dishname="-";
		}

		$dishname.=" ".$moddeddishname;

		if($arr['ingred_qty']==1) {
			$dishname.=" ".lang_get($dest_language,'PRINTS_LOT');
		} elseif($arr['ingred_qty']==-1) {
			$dishname.=" ".lang_get($dest_language,'PRINTS_FEW');
		}

	} else {
		$dishobj = new dish ($arr['dishid']);
		$dishname = $dishobj -> name ($dest_language);
			//RTR nota_ordine stampa sul ticket la nota relativa al piatto ordinato
			$nota_ordine = $arr['nota_ordine'];
	}

	if ($arr['extra_care']) {
		$extra.="\n";
		$extra.='{height_double}{highlight}';
		$extra.=" * * * * ATTENZIONE * * * * ";
		$extra.='{/highlight}';
		$extra.="";
	}

	if ($arr['dishid']==MOD_ID){
		$msg.= "\n";
		$msg.= '{height_double}';
		$msg.= "     ".$dishname;
		$msg.= '{/height_double}';
	} else {

		// mette una riga tra un piatto ed il successivo
		if (MOD_ID){ $msg.="\n"; }
		if ($extra != "") {	$msg.=$extra; }
		$msg.= "\n";
		$msg.= '{size_double}';
		$msg.= $arr['quantity']." ".$dishname;
		$msg.= '{/size_double}';

		//RTR nota_ordine stampa sul ticket la nota relativa al piatto ordinato
		if($arr['nota_ordine']) {
		$msg.= "\n";
		$msg.= '{height_double}';
		$msg.= "".$nota_ordine;
		$msg.= "{/height_double}";
		}
		//fine {orders}
	}
	//Stampa delle singole comade
	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);

	return $msg;
}

function printing_orders_printed_category($category) {
	$query="SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$_SESSION['sourceid']."'";
	$query.=" AND `priority`=$category AND `deleted`=0 AND `printed` IS NOT NULL";
	$query.=" AND `dishid`!=".MOD_ID;
	$query.=" AND `dishid`!=".SERVICE_ID;
	$query.=" AND `suspend`=0";
	$query.=" ORDER BY `associated_id`";
	$res_ord=common_query($query,__FILE__,__LINE__);
	if(!$res_ord) return 0;

	return mysql_num_rows($res_ord);
}

function print_category($category){
/*
name:
print_category($category)
returns:
0 - no error
1 - no orders in this category printed
2 - category already printed
3 - template parsing error
other - mysql error number
*/
	$sourceid = $_SESSION['sourceid'];

	// decided to give back the possibility to print again even if already printed

	//RTR originale escluso
	if(categories_printed ($sourceid,$category)) return 2;

	if(!printing_orders_printed_category($category)) return ERR_NO_ORDERS_PRINTED_CATEGORY;

	$query = "SELECT * FROM `#prefix#sources` WHERE id='$sourceid'";
	$res2=common_query($query,__FILE__,__LINE__);
	if(!$res2) return ERR_MYSQL;

	$row2 = mysql_fetch_array ($res2);
	if ($row2!=FALSE) {
		$otablenum = $row2['name'];
		$ouserid = $row2['userid'];
		$query = "SELECT * FROM `#prefix#users` WHERE id='$ouserid'";
		$res3=common_query($query,__FILE__,__LINE__);
		if(!$res3) return mysql_errno();

		$row3 = mysql_fetch_array ($res3);
		$ousername=$row3['name'];
	}

	switch ($category){
		case 1: $category_name="1";
				break;
		case 2: $category_name="2";
				break;
		case 3: $category_name="3";
				break;
		case 4: $category_name="4";
				break;
	}

	$query="SELECT * FROM `#prefix#dests` WHERE `deleted`='0'";
	$res_dest=common_query($query,__FILE__,__LINE__);
	if(!$res_dest) return ERR_MYSQL;

	while($arr_dest=mysql_fetch_array($res_dest)){
		$destid=$arr_dest['id'];
		$lang=$arr_dest['language'];

		$query="SELECT #prefix#orders.extra_care, #prefix#orders.quantity, #prefix#orders.dishid, #prefix#orders.associated_id, #prefix#orders.ingredid, #prefix#orders.nota_ordine FROM #prefix#orders";
		$query.=" JOIN #prefix#dishes";
		$query.=" WHERE #prefix#dishes.id=#prefix#orders.dishid";
		$query.=" AND #prefix#orders.sourceid ='".$_SESSION['sourceid']."'";
		$query.=" AND #prefix#orders.priority =$category";
		$query.=" AND #prefix#orders.deleted = 0";
		$query.=" AND #prefix#orders.printed IS NOT NULL";
		//$query.=" AND #prefix#orders.dishid != ".MOD_ID;
		$query.=" AND #prefix#orders.dishid != ".SERVICE_ID;
		$query.=" AND #prefix#orders.suspend = 0";
		$query.=" AND #prefix#dishes.destid ='$destid'";
		$query.=" ORDER BY #prefix#orders.associated_id";

//echo "query: ".$query."<br><br>";
		$res_ord=common_query($query,__FILE__,__LINE__);
		if(!$res_ord) return ERR_MYSQL;

		$tpl_print = new template;

		$msg="";

		$output['table'] ="";
		$output['table'].="{size_triple}";
		$output['table'].="{highlight}";
		$output['table'].="Tavolo: ".$_SESSION['tablenum'];
		$output['table'].="{/highlight}";
		$output['table'].="{/size_triple}";

		$tpl_print->assign("table", $output['table']);
		$user = new user($_SESSION['userid']);

		//Stampa il numero di coperti sulla comanda VAI CON
		$output['people']="Coperti: ".table_people_number($sourceid)."\n";
		$tpl_print->assign("people", $output['people']);

		//Stampa la takeaway_surname sulla Comanda solo se esiste un valore
		$takeaway_data = takeaway_get_customer_data($sourceid);
		if($takeaway_data['takeaway_surname']){
		$output['takeawaysurname'] = $takeaway_data['takeaway_surname']."\n";
		$tpl_print->assign("takeawaysurname", $output['takeawaysurname']);
		}
		//Stampa la nota_tavolo sulla Comanda solo se esiste un valore
		if($takeaway_data['nota_tavolo']){
		$output['nota_tavolo'] = "{highlight}     Attenzione nota per il tavolo:     "."\n"."{/highlight}".$takeaway_data['nota_tavolo']."\n";
		$tpl_print->assign("notatavolo", $output['nota_tavolo']);
		}

		$output['waiter']="Cameriere: ".$user->data['name'];
		$tpl_print->assign("waiter", $output['waiter']);

		$output['priority_go']="VAI CON ";
		$output['priority_go'].=$category_name;
		$tpl_print->assign("go_priority", $output['priority_go']);

		$tpl_print->assign("stampa_solo_ora", printer_print_time());
		$tpl_print->assign("date", printer_print_date());


		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
		$output['orders'] = '';

		while($arr=mysql_fetch_array($res_ord)){

			$dishobj = new dish ($arr['dishid']);
			$dishname = $dishobj -> name ($dest_language);
			$nota_ordine = $arr['nota_ordine'];

			$associated_id = $arr['associated_id'];
			$dishid=$arr['dishid'];

			if($arr['extra_care']){
				$extra_care = "\n";
				$extra_care .= "{highlight}";
				$extra_care .= " ATTENZIONE ";
				$extra_care .= "{/highlight}";
				$extra_care .= "";
			} else {
				$extra_care = "";
			}
			$output['orders'].="\n";
			$output['orders'].="{size_double}";
			$output['orders'].=$arr['quantity']." ".$dishname;
			$output['orders'].="";
			$output['orders'].=$extra_care;
			$output['orders'].="{/size_double}";

			//RTR stampa la nota_ordine anche sui ticket vai con
			if($arr['nota_ordine']){
			$output['orders'].="\n";
			$output['orders'].="{size_double}";
			$output['orders'].="".$nota_ordine;
			$output['orders'].="{/size_double}";
			}

			//RTR estrai dalla tabella ordini gli ordini MOD id da stampare sui vai con
			$query="SELECT dishid, associated_id, ingredid, operation, ingred_qty FROM #prefix#orders ";
			$query.=" WHERE dishid= ".MOD_ID;
			$query.=" AND associated_id ='".$associated_id."' ";
			$res_mod_id=common_query($query,__FILE__,__LINE__);
			if(!$res_mod_id) return ERR_MYSQL;

			while($arr=mysql_fetch_array($res_mod_id)){
			//RTR tentativo di far stampare le varianti sui ticket VAI CON
			$ingredid=$arr['ingredid'];
			$operation=$arr['operation'];
			$ingred_qty=$arr['ingred_qty'];

			$ingrediente=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'ingreds',"name",$ingredid);

			if ($operation==1) {
			$segno="+";
			} elseif ($operation==-1) {
			$segno="-";
			}

			if($ingred_qty==1) {
				$molto_poco = lang_get($dest_language,'PRINTS_LOT');
			} elseif($ingred_qty==-1) {
				$molto_poco = lang_get($dest_language,'PRINTS_FEW');
			}	elseif($ingred_qty==0) {
				$molto_poco = '';
			}

			$output['orders'].="\n";
			$output['orders'].="{height_double}";
			$output['orders'].="     ".$segno.' '.$ingrediente.' '.$molto_poco;
			$output['orders'].="";
			$output['orders'].="{/height_double}";
			}
			//RTR END
		$output['orders'].="\n";

		}
		// strips the last newline that has been put
		$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

		$tpl_print->assign("orders", $output['orders']);
		$tpl_print->assign("page_cut", printer_print_cut());

		if($err = $tpl_print->set_print_template_file($destid,'priority_go')) return $err;

		if($err=$tpl_print->parse()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='error: '.$err."\n";
			error_msg(__FILE__,__LINE__,$msg);
			echo nl2br($msg)."\n";
			return 3;
		}
		$tpl_print -> restore_curly ();
		$msg = $tpl_print->getOutput();
		unset($tpl_print);
		$tpl_print = new template;
		$tpl_print->reset_vars();
		$output['orders']='';

		//RTR stampa gli apostrofi sui ticket
		//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
		//$msg = str_replace ("'", "", $msg);
		if(mysql_num_rows($res_ord)){
			if($err=print_line($destid,$msg)) return $err;
		}
	}

	$catprintedtext=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"catprinted",$sourceid);
	$catprintedtext.=" ".$category;
	$query = "UPDATE `#prefix#sources` SET `catprinted`='$catprintedtext', `catprinted_time`='".date("Y-m-d H:i:s")."' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function print_test_page(){
	$print_tpl = new template;
	$msg="";

	$query="SELECT * FROM `#prefix#dests` WHERE `deleted`='0'";
	$res_dest=common_query($query,__FILE__,__LINE__);
	if(!$res_dest) return ERR_MYSQL;

	while($arr_dest=mysql_fetch_array($res_dest)){
		$destid = $arr_dest['id'];
		$print_tpl -> reset_vars();
		$print_tpl -> string = '
******************
'.ucphr('PRINTER_TEST_PAGE').'
******************
'.ucphr('PRINTER_INTERNAL_NAME').': {tpl_print_name}
'.ucphr('PRINTING_QUEUE').': {tpl_print_queue}
'.ucphr('PRINTING_DRIVER').': {tpl_print_driver}
'.ucphr('PRINTING_TEMPLATE').': {tpl_print_template}
'.ucphr('DATE').': '.date('d F Y H:i').'
******************
'.ucphr('PRINTER_TEST_PAGE_END').'
******************{end}';

		// next line is needed to make the template parser leave the line without deleting it, so that it gets to the driver level
		$print_tpl -> assign("end", '{page_cut}');
		$print_tpl -> assign("tpl_print_queue", $arr_dest['dest']);
		$print_tpl -> assign("tpl_print_name", $arr_dest['name']);
		$print_tpl -> assign("tpl_print_driver", $arr_dest['driver']);
		$print_tpl -> assign("tpl_print_template", $arr_dest['template']);

		if($err=$print_tpl->parse()) {
			$msg="Error in ".__FUNCTION__." - ";
			$msg.='error: '.$err."\n";
			error_msg(__FILE__,__LINE__,$msg);
			echo nl2br($msg)."\n";
			return 3;
		}

		$print_tpl -> restore_curly ();
		$msg = $print_tpl -> getOutput();

		//RTR stampa gli apostrofi sui ticket
		//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
		$msg = str_replace ("'", "", $msg);
		if($err=print_line($destid,$msg)) return $err;
	}

	unset($print_tpl);
	return 0;
}

function printAddTicketID($msg,$idLog)
{
	if(!CONF_PRINT_TICKET_ID) return $msg;

	$tmpMsg = "Ticket ID: $idLog";
	$msg = preg_replace ("/{[^}]*ticketID[^}]*}/i", "$dest_msg", $msg);
	$msg = preg_replace("/\{.*?".'ticketID'.".*?\}/",$tmpMsg,$msg);

	return $msg;
}

// Controllo stato stampante Windows usando WMIC (best effort, non blocca se WMIC non è disponibile)
function windows_printer_is_online($printerName, &$statusText = null) {
	$printerName = trim((string)$printerName);
	if ($printerName === '') {
		$statusText = 'Nome stampante vuoto.';
		return false;
	}

	// Sanifica minimamente il nome per la query WMIC (evita virgolette che rompono la where)
	$wmicName = str_replace(array('"', "'"), '', $printerName);

	$cmd = 'wmic printer where "Name=\''.$wmicName.'\'" get Name,WorkOffline,PrinterStatus,DetectedErrorState /value 2>&1';
	@exec($cmd, $output, $ret);

	// Se WMIC non è disponibile o non restituisce dati, non blocchiamo la stampa
	if ($ret !== 0 || empty($output)) {
		$statusText = 'Impossibile determinare lo stato della stampante (WMIC non disponibile o nessun dato).';
		return true;
	}

	$data = array(
		'Name'               => null,
		'WorkOffline'        => null,
		'PrinterStatus'      => null,
		'DetectedErrorState' => null,
	);

	foreach ($output as $line) {
		$line = trim($line);
		if ($line === '') continue;
		$parts = explode('=', $line, 2);
		if (count($parts) != 2) continue;
		list($k, $v) = $parts;
		if (array_key_exists($k, $data)) {
			$data[$k] = trim($v);
		}
	}

	// Stampante non trovata
	if ($data['Name'] === null || $data['Name'] === '') {
		$statusText = "Stampante '$printerName' non trovata nel sistema.";
		return false;
	}

	// WorkOffline = TRUE -> spooler la considera offline
	if (strcasecmp($data['WorkOffline'], 'TRUE') === 0) {
		$statusText = "La stampante '$printerName' risulta OFFLINE (WorkOffline=TRUE).";
		return false;
	}

	// DetectedErrorState: 0 nessun errore, altri valori indicano problemi
	if ($data['DetectedErrorState'] !== null && $data['DetectedErrorState'] !== '' && $data['DetectedErrorState'] !== '0') {
		$statusText = "La stampante '$printerName' segnala un errore (DetectedErrorState=".$data['DetectedErrorState'].").";
		return false;
	}

	// Altri stati (PrinterStatus) li accettiamo, ma li annotiamo nel testo
	$statusText = "Stampante '$printerName' OK (Status=".$data['PrinterStatus'].", WorkOffline=".$data['WorkOffline'].").";
	return true;
}

function print_line($destid,$msg){
	$debug = __FUNCTION__.' - Printing to destid '.$destid.' - line '.$msg.' '."\n";
	debug_msg(__FILE__,__LINE__,$debug);

	if($destid=="all") {
		$query="SELECT * FROM `#prefix#dests` WHERE `bill`=0 AND `invoice`=0 AND `receipt`=0 AND `deleted`='0' ORDER BY id ASC";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		if(!mysql_num_rows($res)) return ERR_NO_PRINT_DESTINATION_FOUND;

		while ($arr = mysql_fetch_array ($res)) {
			$destnow=$arr['id'];
			if($result = print_line($destnow,$msg))
			return $result;
		}
		return 0;
	}

	if(CONF_DEBUG_PRINT_TICKET_DEST) {
		$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','name',$destid);
		$dest_msg.="destid: $destid";
		$dest_msg.="\ndestname: $destname";
		$msg = eregi_replace ("{[^}]*destination[^}]*}", "$dest_msg", $msg);
		$msg=preg_replace("/\{.*?".'destination'.".*?\}/",$dest_msg,$msg);
	}


	$driver=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','driver',$destid);
	$msg = driver_apply($driver,$msg);

	// Salvataggio opzionale del ticket in log (feature disattivata di default)
	$saveToLog = false;
	if ($saveToLog && function_exists('printSaveToLog')) {
		if($idLog = printSaveToLog ($msg,$destid)) {
			$msg = printAddTicketID($msg,$idLog);
		}
	}


	if(CONF_DEBUG_PRINT_DISPLAY_MSG) {
		echo "".nl2br(htmlentities($msg))."\n";

		echo "<br>".$msg;
		}

	// Next commented code is born to drive an external beeper.
	// we should still work on the hardware.

	//$msg=chr(27).chr(7).chr(50).chr(1);
	//for($i=0;$i<10;$i++){
	//	$msg.=chr(28);
	//}

	if(CONF_DEBUG_DONT_PRINT){
		return 0;
	}

// Print-F inizio stampa il contenuto su di un file
// se $_SESSION['type'] == 7 (7 è il numero corrispondente allo scontrino)
if (isset($_SESSION['type']) && $_SESSION['type'] == 7)
	{

		//elimina le righe vuote
		$tosend = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n", $msg);

		//NEL CASO DI UTILIZZO DEL PROGRAMMA MULTISERVERDRIVER
		//utilizza il numero dell'ultimo scontrino registrato sul database
		//$type = 5 poichè nel database mgmt_main il lo scontrino è 5 e non 7
		//$type = 5;
		//$last_internal_id=find_last_receipt($_SESSION['account'], $type, date("Y"));
		//$numero_scontrino = $last_internal_id;
		//file_put_contents('../TOSEND/'.$numero_scontrino.'.txt', $tosend);

		//NEL CASO DI UTILIZZO DEL PROGRAMMA MULTIDRIVER
	  //configurare il programma per leggere lo scontrino dalla directory ../PATH_IN/
		

	// 1 versione di $tosendfunzionante	
	//Se però il tuo $msg/$tosend è in realtà UTF‑8 (tipico con PHP moderni, pagine text/html; charset=utf-8, DB in utf8, ecc.), iconv lo interpreta come ISO‑8859‑1 e gli accenti vengono rovinati.
	$tosend = iconv("ISO-8859-1", "WINDOWS-1252", $tosend);
	
	
	// 2 versione di $tosend NON testata
	//per non rovinare gli accenti: converti solo se la stringa è realmente UTF-8 valida
	/*if (function_exists('mb_detect_encoding') && mb_detect_encoding($tosend, 'UTF-8', true)) {
		$converted = iconv("UTF-8", "WINDOWS-1252//TRANSLIT", $tosend);
		if ($converted !== false) {
			$tosend = $converted;
		}
	}
	*/
	  file_put_contents(PATH_SCONTRINO_INP, $tosend);

		//Righe per eseguire il programma MULTIDRIVER in windows
		if (!file_exists(PATH_MULTIDRIVER)){ return ("Percorso errato applicazione MULTIDRIVER_APP.exe"); }

		if(!file_exists(PATH_SCONTRINO_OUT)) {
			if (file_exists(PATH_PAPER_OUT)) { return ("Errore fine carta nella stampante");}
					else { return ("Errore non esiste il file scontrino.out verificare se la stampante è occupata");}
		}

		unlink (PATH_SCONTRINO_OUT);
		//con la funzione sotto il processo di multidriver viene staccato dal processo PHP di Apache
		pclose(popen('START /B '.PATH_MULTIDRIVER." 2>&1", "r"));
		/////////////////////////////////////////////////////////////////////////////
	  //Come funziona la MULTIDRIVER_APP
	  // 1) Legge il contenuto del file scontrino.inp ed invia alla stampante
	  //
	  // 2) Cancella il file scontrino.out
	  //    a) Se END_PAPER_FILE=1 Verifica il se la carta è presente altrimenti
	  //       crea il file Paper.out ed attende il cambio carta.
	  //    b) Appena cambiata la carta ricrea il file scontrino.out con scritto
	  //       solo il numero del vecchio scontrino andato a buon fine senza più
	  //       la scritta OK. Non si capisce la logica del Driver poichè questa
	  //       procedura può generare un errore nei cicli di controllo.
	  //    c) Viene annullato lo scontrino in stampa
	  //    d) Viene AGGIUNTO al contenuto del file scontrino.out il numero del
	  //       nuovo scontrino (a capo) OK.
	  //
	  // 3) Crea il file scontrino.out con scritto il numero dell'ultimo
	  //    scontrino + (a capo) OK.
	  /////////////////////////////////////////////////////////////////////////////


		$max_time = ini_get("max_execution_time"); // prende il valore del tempo massimo di esecuzione script dal file php.ini
		$max_time = $max_time - 2; // fermiamo l'attesa 2 secondi prima che php interrompa lo script

  	//Inizia un ciclo di if ed attende che il driver finisca l'invio dello scontrino
		for( $i = 1; $i <= $max_time ; $i++ )
	  {
	      $fp = file_get_contents(PATH_SCONTRINO_OUT);
	      if ( stristr($fp, 'OK') || stristr($fp, 'RIFIUTATA') || stristr($fp, 'ERRORE') )
	          {
	           break;    // terminate loop
	          }
	      else { sleep(1); }
	  }

		// verifica se lo scontrino ha avuto esito positivo o negativo oppure se è finita la carta
	  // oppure se è trascorso il tempo massimo per lo script PHP (max_execution_time)
	  // controlla se esiste ancora il file Paper.out
	  $fp = file_get_contents(PATH_SCONTRINO_OUT);

		// se è presente ancora il file Paper.out
		if (file_exists(PATH_PAPER_OUT)) {
			$messaggio = "<div align='center' style='border:10px solid red'>
										<h1>ATTENZIONE FINE CARTA <br><br>
										lo scontrino è nella memoria della stampante<br><br>
										CAMBIARE LA CARTA IL PRIMA POSSIBILE</h1></div>";
			echo $messaggio;
			return 0;
			}
  	// se non esiste il file scontrino.out
		if (!file_exists(PATH_SCONTRINO_OUT)) {
			return ('Errore non esiste il file scontrino.out - '.$fp);
			}
  	// se la connessione è rifiutata
		if (strstr($fp, 'CONNESSIONE RIFIUTATA')) {
			return ('Connessione con la stampante risiutata - '.$fp);
			}
  	// se lo scontrino è stato emesso correttamente
		if (strstr($fp, 'OK')) {
			$messaggio = "<div align='center' style='border:10px solid red'>
										<h1>Scontrino emesso con successo<br><br>
										Scontrino numero ".$fp."<br></h1></div>";
			echo $messaggio;
			return 0;
			} else {
			return ('errore nella stampa dello scontrino<br>'.$fp);
				}

	}
// Print-F fine stampa il contenuto su di un file

	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','dest',$destid);
	//se il sistema di stampa è "win" verifica il se il percorso della stampante funziona
	if ((strtolower(get_conf(__FILE__,__LINE__,"printing_system"))) == "win") {
				$handle = @printer_open($dest);
				if($handle) {
					printer_close($handle);
				} else {
					return ERR_COULD_NOT_OPEN_PRINTER;
				}
				}
	$result = print_line_os_chooser($msg,$dest);

	if($result !== 0) {
					$error_msg='Printing error: '.$result;
					error_msg(__FILE__,__LINE__,$error_msg);
					return $result;
					}
	return 0;
}

function print_line_os_chooser($value,$dest) {
	switch(strtolower(get_conf(__FILE__,__LINE__,"printing_system"))) {
		case "win":
					$debug = __FUNCTION__.' - calling windows printing function'."\n";
					$res = print_line_win($value, $dest);
					break;
		default:
					$debug = __FUNCTION__.' - calling unix printing function'."\n";
					$res = print_line_lp($value, $dest);
					break;
	}
	debug_msg(__FILE__,__LINE__,$debug);
	return $res;
}

function print_line_lp($value,$dest) {
	$last_output_line=exec("echo '$value' | lp -d $dest 2>&1",$out_arr,$outerr);
	if($outerr) {
		$error_msg="Printing system error: ".$outerr."\n\tcomplete output: ".var_dump_string($out_arr)."\n\tlast output_line: ".$last_output_line;
		error_msg(__FILE__,__LINE__,$error_msg);
		return ERR_PRINTING_ERROR;
	}
	return 0;
}

function print_line_win($value, $dest) {

	//$title='My Handy Restaurant';

	//ATTENZIONE ALLA PAGINA STAMPANTI
	//NON SELEZIONARE 2 STAMPANTI CON LO STESSO PRECONTO O RICEVUTA
	//IL SISTEMA UTILIZZA SEMPRE E SOLO LA PRIMA TROVATA (ID IN ORDINE CRESCENTE)

	$handle = @printer_open($dest);

	//if(!$handle) non interromepe il processo di stampa deve essere messo anche nella prima funzione
	//che richiama tutto il processo ovvero $result = print_line_os_chooser($msg,$dest); -> print_line_os_chooser -> print_line_win
	if(!$handle)  { return ERR_COULD_NOT_OPEN_PRINTER;}

	$debug = __FUNCTION__.' - Windows Printing to dest '.$dest.' - line '.$value.' '."\n";
	debug_msg(__FILE__,__LINE__,$debug);

	printer_set_option($handle, PRINTER_MODE, "RAW");

	//$value = stri_replace ("\n","\r\n",$value);
	$value = str_replace ("\n","\r\n",$value);

	//printer_start_doc($handle, $title);
	//printer_start_page($handle);

	// Esegue la scrittura e controlla l'errore senza usare variabili non inizializzate
	$res = printer_write($handle, $value);
	if($res === false) return ERR_PRINTING_ERROR;

	//printer_end_page($handle);
	//printer_end_doc($handle);

	printer_close($handle);

	return 0;
}

function print_set_printed($orderid){
	if(CONF_DEBUG_DONT_SET_PRINTED) return 0;

	$query = "UPDATE `#prefix#orders` SET `printed` = NOW() WHERE `id` = '$orderid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function print_ticket($orderid,$deleted=false) {
	$output['orders']='';
	$tpl_print = new template;

	$query = "SELECT * FROM `#prefix#orders` WHERE `id`='".$orderid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return ERR_ORDER_NOT_FOUND;
	$arr=mysql_fetch_array($res);

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$arr['sourceid']);
	$priority=$arr['priority'];

	$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",$arr['dishid']);
	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
	$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	if($deleted) $tpl_print -> append ("warning", "\n"."{size_triple}{highlight} STORNATO {/highlight}{/size_triple}"."\n");
	if(!$deleted) $tpl_print->assign("gonow", printer_print_gonow($priority,$dest_language));

	if (table_is_takeaway($arr['sourceid'])) {
		$takeaway_data = takeaway_get_customer_data($arr['sourceid']);
		$output['takeaway'] = ucfirst(lang_get($dest_language,'PRINTS_TAKEAWAY'))." - ";
		$output['takeaway'] .= $takeaway_data['takeaway_hour'].":".$takeaway_data['takeaway_minute']."";
		$output['takeaway'] .= $takeaway_data['takeaway_surname']."";
		$tpl_print->assign("takeaway", $output['takeaway']);
	}

	$table = new table($arr['sourceid']);
	$table->fetch_data(true);
	if ($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		//Cliente:
		$output['customer']=ucfirst(lang_get($dest_language,'CUSTOMER')).": ".$cust -> data ['surname'].' '.$cust -> data ['name'];
		$tpl_print->assign("customer_name", $output['customer']);
		$output['customer']=$cust -> data ['address'];
		$tpl_print->assign("customer_address", $output['customer']);
		$output['customer']=$cust -> data ['zip'];
		$tpl_print->assign("customer_zip_code", $output['customer']);
		$output['customer']=$cust -> data ['city'];
		$tpl_print->assign("customer_city", $output['customer']);
		$output['customer']=ucfirst(lang_get($dest_language,'VAT_ACCOUNT')).": ".$cust -> data ['vat_account'];
		$tpl_print->assign("customer_vat_account", $output['customer']);
		$output['customer']="Codice Fiscale: ".$cust -> data ['codice_fiscale'];
		$tpl_print->assign("customer_codice_fiscale", $output['customer']);
	}

	$output['table']="";
	$output['table'].="{size_triple}";
	$output['table'].="Tavolo: ".$tablenum;
	$output['table'].="{/size_triple}";
	$tpl_print->assign("table", $output['table']);
	$user = new user($_SESSION['userid']);
	$output['waiter']=ucfirst(lang_get($dest_language,'PRINTS_WAITER')).": ".$user->data['name'];
	$tpl_print->assign("waiter", $output['waiter']);

if((CONF_PRINT_ONLY_HIGH_PRIORITY_NUMBER && $priority!=1)
		|| !CONF_PRINT_ONLY_HIGH_PRIORITY_NUMBER)
	{
	$output['priority']=ucfirst(lang_get($dest_language,'PRINTS_PRIORITY')).": ".$priority;
	$tpl_print->assign("priority", $output['priority']);
	}
	$output['people']=ucfirst(lang_get($dest_language,'PRINTS_PEOPLE')).": ".table_people_number($arr['sourceid']);
	$tpl_print->assign("people", $output['people']);

	$output['orders'].=printer_print_row($arr,$destid);

	$query = "SELECT * FROM `#prefix#orders` WHERE `associated_id`='".$orderid."' AND `id` != '".$orderid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	while($arr_mods=mysql_fetch_array($res))
		$output['orders'].=printer_print_row($arr_mods,$destid);

	if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
		$output['orders'].= print_barcode($arr['associated_id']);
	}
	$tpl_print->assign("orders", $output['orders']);


	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("stampa_solo_ora", printer_print_time());
	$tpl_print->assign("page_cut", printer_print_cut());

	if (table_is_takeaway($arr['sourceid'])) $print_tpl_file='ticket_takeaway';
	else $print_tpl_file='ticket';
	if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;

	if($err=$tpl_print->parse()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='error: '.$err."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return ERR_PARSING_TEMPLATE;
	}
	$tpl_print -> restore_curly ();
	$msg = $tpl_print->getOutput();

	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);
	if($err= print_line($destid,$msg)) return $err;

	$err = print_set_printed($orderid);
	// there was an error setting orders as printed
	if($err) return ERR_ORDER_NOT_SET_AS_PRINTED;

	return 0;
}

function printer_print_gonow($priority,$dest_language) {
	// function disabled
	return '';
	if($_SESSION['catprinted'][$priority]) return ucfirst(lang_get($dest_language,'PRINTS_GO_NOW'));
	return '';
}

function printer_print_date_no_time() {
	$msg = date("j/n/Y",time());
	return $msg;
}

function printer_print_date() {
	$msg = date("j/n/Y G:i.s",time());
	return $msg;
}
function printer_print_time() {
	$msg = date("G:i.s",time());
	return $msg;
}

function printer_print_cut(){
	$msg='{page_cut}';
	return $msg;
}

function print_barcode($code){
	$codedata=sprintf("%07d",$code);
	debug_msg(__FILE__,__LINE__,"INFO print_barcode - codedata: $codedata");
	$msg='{align_center}';
	$msg.='{barcode_code39}'."$codedata".'{/barcode_code39}'; //barcode CODE39

	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);
	return $msg;
}

function find_last_receipt($db,$type,$year){
	if(!$type) return 0;

	$timestart=date("Y")."0000000000";

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table WHERE `type`='$type' AND `internal_id`!='' AND `date`>='$timestart'";
	// CRYPTO
	// Use mysql_query instead of deprecated mysql_db_query
	if (!empty($db)) {
		mysql_select_db($db);
	}
	$res=mysql_query($query);

	if(mysql_num_rows($res)){
		while($row=mysql_fetch_array($res)){
			$year_record=substr($row['internal_id'],6,4);
			if($year_record==$year) {
				$internal_id[]=substr($row['internal_id'],0,6);
			}
		}
	} else {
		$internal_id[0]="000000";
	}

	if (sizeof($internal_id)==0) {
		$internal_id[0]="000000";
	}
	mysql_free_result($res);
	rsort($internal_id);
	return $internal_id[0];
}
// RTR aggiusta il PRECONTO
function receipt_type_waiter2mgmt($type){
	switch($type){
	//nel caso che qualche preconto fosse scritto nel database contabilità verrà scritto con il 99 o 98
		case 1: $type=98; break;
		case 2: $type=99; break;
		case 3: $type=4; break;
		case 4: $type=4; break;
		case 5: $type=3; break;
		case 6: $type=3; break;
		case 7: $type=5; break;
	}
	return $type;
}


function receipt_insert($accountdb, $type, $customer_id, $takeaway_surname, $tavolo_numero, $tipo_corrispettivo){
    //crea i record nella tabella mhr_account_mgmt_main

    // CRYPTO
    // finds the last issued bill or invoice, and increments by one
    // internal invoice receipt number format is: NNNNNNYYYY
    // where NNNNNN is the incremental number padded with 0s and YYYY is
    // the current 4 digits year.
    $last_internal_id=find_last_receipt($accountdb,$type,date("Y"));
    $internal_id=$last_internal_id + 1;
    $internal_id=sprintf("%06d",$internal_id);
    $internal_id.=date("Y");

    // creates the new receipt voice in management db, to be next filled
    // with actual amount values

   $table=$GLOBALS['table_prefix'].'account_mgmt_main';
   $query="INSERT INTO $table (`description`,`who`,`internal_id`,`type`,`waiter_income`,`customer_id`,`takeaway_surname`,`tavolo_numero`,`tipo_corrispettivo`)
	VALUES ('".ucfirst(phr('INCOME')).": $internal_id','.','$internal_id','$type','1','$customer_id','$takeaway_surname','$tavolo_numero','$tipo_corrispettivo')";
   // Use mysql_query instead of deprecated mysql_db_query
   if (!empty($accountdb)) {
	   mysql_select_db($accountdb);
   }
   $res=mysql_query($query);
    $receipt_id=mysql_insert_id();
    return $receipt_id;
}


function receipt_delete($accountdb,$receipt_id){
	// deletes the receipt voice in management db
	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="DELETE FROM $table WHERE `id`='".$receipt_id."'";
	// Use mysql_query instead of deprecated mysql_db_query
	if (!empty($accountdb)) {
		mysql_select_db($accountdb);
	}
	$res=mysql_query($query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return ERR_MYSQL;
	}

	return 0;
}


function receipt_update_amounts($accountdb,$total,$receipt_id){
	//$total_total=$_SESSION['separated'][$key]['finalprice'];
	$total_total=$total['total'];
	$taxable=$total['taxable'];
	$vat=$total['tax'];

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="UPDATE $table SET `waiter_income` = '1',`cash_amount` = '$total_total',`cash_taxable_amount` = '$taxable',`cash_vat_amount` = '$vat' WHERE `id` = '$receipt_id'";
	// Use mysql_query instead of deprecated mysql_db_query
	if (!empty($accountdb)) {
		mysql_select_db($accountdb);
	}
	$res = mysql_query($query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return $errno;
	}
	return 0;
}


function print_orders($sourceid){
/*
name:
print_orders($sourceid)
returns:
0 - no error
1 - no orders to be printed
2 - template parsing error
3 - error setting orders printed
other - mysql error number
*/
	$sourceid = $_SESSION['sourceid'];
	debug_msg(__FILE__,__LINE__,"BEGIN PRINTING");

	$query = "SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid' AND `printed` IS NULL AND `suspend`='0' ORDER BY dest_id ASC, priority ASC, menu_fisso DESC, associated_id ASC, id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)) return ERR_ORDER_NOT_FOUND;

	$newassociated_id="";

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$sourceid);

	$tpl_print = new template;
	$output['orders']='';
	$msg="";

	while ($arr = mysql_fetch_array ($res)) {

		$oldassociated_id=$newassociated_id;
		$newassociated_id=$arr['associated_id'];

		if(isset($priority)) $oldpriority=$priority;
		else $oldpriority = 0;

		$priority=$arr['priority'];

		if($oldassociated_id!=""){
			$olddestid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
			get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$oldassociated_id)
			);
			$olddest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$olddestid);
			$olddestname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$olddestid);
		} else {
			$olddestid = 0;
		}

		$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
		get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
		);
		$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);

		$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);

		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

		if ($destid!=$olddestid || $priority!=$oldpriority) {
			if($destid!=$olddestid && $olddestid!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("stampa_solo_ora", printer_print_time());
				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));
				$tpl_print->assign("page_cut", printer_print_cut());

				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($olddestid,$print_tpl_file)) return $err;

				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					echo nl2br($msg)."\n";
					error_msg(__FILE__,__LINE__,$msg);
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				//unset($tpl_print);
				//$tpl_print = new template;
				unset($tpl_print);
				$tpl_print = new template;
				$tpl_print->reset_vars();
				$output['orders']='';

				//RTR stampa gli apostrofi sui ticket
				//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
				//$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($olddestid,$msg)) return $outerr;

			} elseif($priority!=$oldpriority && $oldpriority!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("stampa_solo_ora", printer_print_time());

				if(!CONF_PRINT_TICKETS_ONE_PAGE_PER_TABLE) $tpl_print->assign("page_cut", printer_print_cut());
				else $tpl_print->assign("page_cut", '');

				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));

				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;

				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					error_msg(__FILE__,__LINE__,$msg);
					echo nl2br($msg)."\n";
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				$tpl_print->reset_vars();
				$output['orders']='';

				//RTR stampa gli apostrofi sui ticket
				//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
				//$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($destid,$msg)) return $outerr;
			}

			if(table_is_takeaway($sourceid)) {
				$takeaway_data = takeaway_get_customer_data($sourceid);
				$output['takeaway'] = ucfirst(lang_get($dest_language,'PRINTS_TAKEAWAY'))." - ";
				$output['takeaway'] .= $takeaway_data['takeaway_hour'].":".$takeaway_data['takeaway_minute']."\n";
				$output['takeaway'] .= $takeaway_data['takeaway_surname']."";
				$tpl_print->assign("takeaway", $output['takeaway']);
			}

			// if($oldpriority <= $priority) {
			$output['table']="";
			$output['table'].="{size_triple}";
			$output['table'].="Tavolo: ".$tablenum;
			$output['table'].="{/size_triple}";

			// }else{		$output['table']=""};

			$tpl_print->assign("table", $output['table']);
			$user = new user($_SESSION['userid']);

			$takeaway_data = takeaway_get_customer_data($sourceid);

			$output['people']= "Coperti: ".table_people_number($sourceid)."\n";
			$tpl_print->assign("people", $output['people']);

			//Stampa la takeaway_surname sulla Comanda solo se esiste un valore
			if($takeaway_data['takeaway_surname']){
			$output['takeawaysurname'] = "".$takeaway_data['takeaway_surname']."\n";
			$tpl_print->assign("takeawaysurname", $output['takeawaysurname']);
			}

			//Stampa la nota_tavolo sulla Comanda solo se esiste un valore
			if($takeaway_data['nota_tavolo']){
			$output['nota_tavolo'] = "{highlight}     Attenzione nota per il tavolo:     "."\n"."{/highlight}".$takeaway_data['nota_tavolo']."\n";
			$tpl_print->assign("notatavolo", $output['nota_tavolo']);
			}

			$output['waiter']="Camerie: ".$user->data['name'];
			$tpl_print->assign("waiter", $output['waiter']);

			$output['priority']="Priorità: ".$priority;
			$tpl_print->assign("priority", $output['priority']);

			$table = new table($sourceid);
			$table->fetch_data(true);
			if ($cust_id=$table->data['customer']) {
				$cust = new customer ($cust_id);
				$output['customer']=ucfirst(lang_get($dest_language,'CUSTOMER')).": ".$cust -> data ['surname'].' '.$cust -> data ['name'];
				$tpl_print->assign("customer_name", $output['customer']);
				$output['customer']=$cust -> data ['address'];
				$tpl_print->assign("customer_address", $output['customer']);
				$output['customer']=$cust -> data ['zip'];
				$tpl_print->assign("customer_zip_code", $output['customer']);
				$output['customer']=$cust -> data ['city'];
				$tpl_print->assign("customer_city", $output['customer']);
				$output['customer']=ucfirst(lang_get($dest_language,'VAT_ACCOUNT')).": ".$cust -> data ['vat_account'];
				$tpl_print->assign("customer_vat_account", $output['customer']);
				$output['customer']="Codice Fiscale: ".$cust -> data ['codice_fiscale'];
				$tpl_print->assign("customer_codice_fiscale", $output['customer']);
			}
		}


		$output['orders'].=printer_print_row($arr,$destid);
		$printed_orders[]=$arr['id'];

		if ($newassociated_id!=$oldassociated_id) {
			// if we're in this function, it means that we changed associated_id id
			// and also that mods have been printed on the same sheet

			if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
				$output['orders'].= print_barcode($newassociated_id);
			}
		}

		if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
			//$output['orders'].= print_barcode($newassociated_id);
		}
		$tpl_print->assign("orders", $output['orders']);
	}

	$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
	get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
	);
	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
	$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

//RTR START Stampa nella tabella sources nel campo catprinted il numero 1
	$catprintedtext=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"catprinted",$sourceid);
	$catprintedtext.=" 1";
	$query = "UPDATE `#prefix#sources` SET `catprinted`='$catprintedtext', `catprinted_time`='".date("Y-m-d H:i:s")."' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
//RTR END

	if(CONF_PRINT_BARCODES){
		//$tpl_print->assign("barcode", print_barcode($newassociated_id));
	}

	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("stampa_solo_ora", printer_print_time());
	$tpl_print->assign("gonow", printer_print_gonow($priority,$dest_language));
	$tpl_print->assign("page_cut", printer_print_cut());

	// strips the last newline that has been put
	$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

	if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
	else $print_tpl_file='ticket';
	if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;

	if($err=$tpl_print->parse()) {
		$err_msg="Error in ".__FUNCTION__." - ";
		$err_msg.='error: '.$err."\n";
		error_msg(__FILE__,__LINE__,$err_msg);
		echo nl2br($err_msg)."\n";
		return ERR_PARSING_TEMPLATE;
	}
	$tpl_print -> restore_curly ();
	$msg = $tpl_print->getOutput();
	$tpl_print->reset_vars();
	$output['orders']='';

	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);

	if($outerr=print_line($destid,$msg)) return $outerr;

	foreach ($printed_orders as $val)
		if($err = print_set_printed($val)) return $err;
	// there was an error setting orders as printed
	if($err) return ERR_ORDER_NOT_SET_AS_PRINTED;

	return 0;
}


function rispampa_ordini($sourceid){
/*
name:
print_orders($sourceid)
returns:
0 - no error
1 - no orders to be printed
2 - template parsing error
3 - error setting orders printed
other - mysql error number
*/
	$sourceid = $_SESSION['sourceid'];
	debug_msg(__FILE__,__LINE__,"BEGIN PRINTING");

	$query = "SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid' AND `deleted`='0' AND `suspend`='0' AND `dishid`!='".SERVICE_ID."' ORDER BY dest_id ASC, priority ASC, menu_fisso DESC, associated_id ASC, id ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)) return ERR_ORDER_NOT_FOUND;

	$newassociated_id="";

	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$sourceid);

	$tpl_print = new template;
	$output['orders']='';
	$msg="";

	while ($arr = mysql_fetch_array ($res)) {

		$oldassociated_id=$newassociated_id;
		$newassociated_id=$arr['associated_id'];

		if(isset($priority)) $oldpriority=$priority;
		else $oldpriority = 0;

		$priority=$arr['priority'];

		if($oldassociated_id!=""){
			$olddestid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
			get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$oldassociated_id)
			);
			$olddest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$olddestid);
			$olddestname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$olddestid);
		} else {
			$olddestid = 0;
		}

		$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
		get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
		);
		$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);

		$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);

		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

		if ($destid!=$olddestid || $priority!=$oldpriority) {
			if($destid!=$olddestid && $olddestid!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("stampa_solo_ora", printer_print_time());
				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));
				$tpl_print->assign("page_cut", printer_print_cut());

				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($olddestid,$print_tpl_file)) return $err;

				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					echo nl2br($msg)."\n";
					error_msg(__FILE__,__LINE__,$msg);
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				//unset($tpl_print);
				//$tpl_print = new template;
				unset($tpl_print);
				$tpl_print = new template;
				$tpl_print->reset_vars();
				$output['orders']='';

				//RTR stampa gli apostrofi sui ticket
				//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
				//$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($olddestid,$msg)) return $outerr;

			} elseif($priority!=$oldpriority && $oldpriority!="") {
				$tpl_print->assign("date", printer_print_date());
				$tpl_print->assign("stampa_solo_ora", printer_print_time());

				if(!CONF_PRINT_TICKETS_ONE_PAGE_PER_TABLE) $tpl_print->assign("page_cut", printer_print_cut());
				else $tpl_print->assign("page_cut", '');

				// strips the last newline that has been put
				$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

				$tpl_print->assign("gonow", printer_print_gonow($oldpriority,$dest_language));

				if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
				else $print_tpl_file='ticket';
				if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;

				if($err=$tpl_print->parse()) {
					$msg="Error in ".__FUNCTION__." - ";
					$msg.='error: '.$err."\n";
					error_msg(__FILE__,__LINE__,$msg);
					echo nl2br($msg)."\n";
					return ERR_PARSING_TEMPLATE;
				}
				$tpl_print -> restore_curly ();
				$msg = $tpl_print->getOutput();
				$tpl_print->reset_vars();
				$output['orders']='';

				//RTR stampa gli apostrofi sui ticket
				//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
				//$msg = str_replace ("'", "", $msg);
				if($outerr=print_line($destid,$msg)) return $outerr;
			}

			if(table_is_takeaway($sourceid)) {
				$takeaway_data = takeaway_get_customer_data($sourceid);
				$output['takeaway'] = ucfirst(lang_get($dest_language,'PRINTS_TAKEAWAY'))." - ";
				$output['takeaway'] .= $takeaway_data['takeaway_hour'].":".$takeaway_data['takeaway_minute']."\n";
				$output['takeaway'] .= $takeaway_data['takeaway_surname']."";
				$tpl_print->assign("takeaway", $output['takeaway']);
			}

			$output['table']='';
			$output['table'].='{size_triple}';
			$output['table'].='{highlight}';
			$output['table'].="   RISTAMPA   ";
			$output['table'].='{/highlight}';
			$output['table'].="\n\n";
			$output['table'].="Tavolo: ".$tablenum;
			$output['table'].='{/size_triple}';

			$tpl_print->assign("table", $output['table']);
			$user = new user($_SESSION['userid']);

			$output['people']= ""."Coperti: ".table_people_number($sourceid)."\n";
			$tpl_print->assign("people", $output['people']);

			//Stampa la takeaway_surname sulla Comanda solo se esiste un valore
			$takeaway_data = takeaway_get_customer_data($sourceid);
			if($takeaway_data['takeaway_surname']){
			$output['takeawaysurname'] = "".$takeaway_data['takeaway_surname']."\n";
			$tpl_print->assign("takeawaysurname", $output['takeawaysurname']);
			}

			//Stampa la nota_tavolo sulla Comanda solo se esiste un valore
			if($takeaway_data['nota_tavolo']){
			$output['nota_tavolo'] = "{highlight}     Attenzione nota per il tavolo:     "."\n"."{/highlight}".$takeaway_data['nota_tavolo']."\n";
			$tpl_print->assign("notatavolo", $output['nota_tavolo']);
			}

			$output['waiter']=""."Cameriere: ".$user->data['name'];
			$tpl_print->assign("waiter", $output['waiter']);

			$output['priority'] = "Priorità: ".$priority;
			$tpl_print->assign("priority", $output['priority']);

			$table = new table($sourceid);
			$table->fetch_data(true);
			if ($cust_id=$table->data['customer']) {
				$cust = new customer ($cust_id);
				$output['customer']=ucfirst(lang_get($dest_language,'CUSTOMER')).": ".$cust -> data ['surname'].' '.$cust -> data ['name'];
				$tpl_print->assign("customer_name", $output['customer']);
				$output['customer']=$cust -> data ['address'];
				$tpl_print->assign("customer_address", $output['customer']);
				$output['customer']=$cust -> data ['zip'];
				$tpl_print->assign("customer_zip_code", $output['customer']);
				$output['customer']=$cust -> data ['city'];
				$tpl_print->assign("customer_city", $output['customer']);
				$output['customer']=ucfirst(lang_get($dest_language,'VAT_ACCOUNT')).": ".$cust -> data ['vat_account'];
				$tpl_print->assign("customer_vat_account", $output['customer']);
				$output['customer']="Codice Fiscale: ".$cust -> data ['codice_fiscale'];
				$tpl_print->assign("customer_codice_fiscale", $output['customer']);
			}
		}


		$output['orders'].=printer_print_row($arr,$destid);
		$printed_orders[]=$arr['id'];

		if ($newassociated_id!=$oldassociated_id) {
			// if we're in this function, it means that we changed associated_id id
			// and also that mods have been printed on the same sheet

			if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
				$output['orders'].= print_barcode($newassociated_id);
			}
		}

		if(CONF_PRINT_BARCODES && $arr['dishid']!=MOD_ID){
			//$output['orders'].= print_barcode($newassociated_id);
		}
		$tpl_print->assign("orders", $output['orders']);
	}

	$destid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"destid",
	get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'orders','dishid',$newassociated_id)
	);
	$dest=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"dest",$destid);
	$destname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"name",$destid);
	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

//RTR START Stampa nella tabella sources nel campo catprinted il numero 1 e ritorna disponibili i turni
	$query = "UPDATE `#prefix#sources` SET `catprinted`='1', `catprinted_time`='".date("Y-m-d H:i:s")."' WHERE `id` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
//RTR END

	if(CONF_PRINT_BARCODES){
		//$tpl_print->assign("barcode", print_barcode($newassociated_id));
	}

	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("stampa_solo_ora", printer_print_time());
	$tpl_print->assign("gonow", printer_print_gonow($priority,$dest_language));
	$tpl_print->assign("page_cut", printer_print_cut());

	// strips the last newline that has been put
	$output['orders'] = substr ($output['orders'], 0, strlen($output['orders'])-1);

	if (table_is_takeaway($sourceid)) $print_tpl_file='ticket_takeaway';
	else $print_tpl_file='ticket';
	if($err = $tpl_print->set_print_template_file($destid,$print_tpl_file)) return $err;

	if($err=$tpl_print->parse()) {
		$err_msg="Error in ".__FUNCTION__." - ";
		$err_msg.='error: '.$err."\n";
		error_msg(__FILE__,__LINE__,$err_msg);
		echo nl2br($err_msg)."\n";
		return ERR_PARSING_TEMPLATE;
	}
	$tpl_print -> restore_curly ();
	$msg = $tpl_print->getOutput();
	$tpl_print->reset_vars();
	$output['orders']='';

	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);

	if($outerr=print_line($destid,$msg)) return $outerr;

	//se attivi ristampano l'ora nel campo printed della tabella orders
	//foreach ($printed_orders as $val)
	//if($err = print_set_printed($val)) return $err;
	// there was an error setting orders as printed
	//if($err) return ERR_ORDER_NOT_SET_AS_PRINTED;

	return 0;
}

?>
