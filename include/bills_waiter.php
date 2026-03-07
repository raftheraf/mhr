<?php
function bill_orders_to_print ($sourceid) {
	$query="SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid' AND `price`!='0' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(!mysql_num_rows($res)) return 0;

	while($arr=mysql_fetch_array($res)){
		$topay = $arr['quantity'] - $arr['paid'];
		if ($topay > 0) return 1;
	}
	return 0;
}

function write_log_item($item_id,$quantity,$price,$receipt_id) {

/*
name:
write_log_item($item_id,$quantity,$price,$receipt_id)
returns:
0 - no error
1 - Order record not found
2 - Waiter not found
3 - log writing error
other - mysql error number
*/
    // next line is not necessary, due to automatic mySQL filling when no value is provided
    $log["datetime"] = date("Y-m-d H:i:s",time());     // human format
    $log["datetime"] = date("YmdHis",time());         // timestamp format

    $query="SELECT * FROM `#prefix#orders` WHERE `id`='$item_id'";
    $res_item=common_query($query,__FILE__,__LINE__);
    if(!$res_item) return mysql_errno();

    if(mysql_num_rows($res_item))
        $arr_item=mysql_fetch_array($res_item);
    else {
        $msg='Error in '.__FUNCTION__.' - Order record not found: order id: '.$item_id.'.';
        echo nl2br($msg)."\n";
        error_msg(__FILE__,__LINE__,$msg);
        return 1;

    }



    debug_msg(__FILE__,__LINE__,__FUNCTION__.' - id: '.$arr_item['dishid']);

    $log['waiter']='NotAssigned';
    $table = new table($arr_item['sourceid']);
    if($table->data['userid']) $log["waiter"]=$table->data['userid'];

    if($arr_item==0 || $arr_item['deleted']==1)  return 0;

    if($arr_item['dishid'] == MOD_ID and $arr_item['operation']==0) return 0;

    $dishid=$arr_item['dishid'];
    $log["quantity"]=$quantity;
    $log["price"]=$price;
    $log["payment"]=$receipt_id;
    if($dishid != MOD_ID) {
        $log["dish"]=$arr_item['dishid'];
        $log["ingredient"]="";
        $log["operation"]=0;
        $log["destination"]=$arr_item['dest_id'];
        $dish = new dish ($arr_item['dishid']);
        $log['category'] = $dish->data['category'];
    } elseif ($dishid==MOD_ID) {
        $ingred = new ingredient ($arr_item['ingredid']);
        $log['category'] = $ingred->data['category'];

        $log["dish"]="";
        $log["ingredient"]=$arr_item['ingredid'];
        $log["operation"]=$arr_item['operation'];

        $associated_orderid=$arr_item['associated_id'];
        $associated_dishid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"orders","dishid",$associated_orderid);
        $log["destination"]=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"dishes","destid",$associated_dishid);
    }elseif ($dishid==SERVICE_ID){
        $log["dish"]=SERVICE_ID;
        $log["ingredient"]="";
        $log["operation"]=0;
        $log["category"]=0;
        $log["destination"]=0;
    }

    //RTR impedisce al preconto di essere aggiunto al database
        if ($_SESSION['type'] < 3)    {
                    ;
                    } else {
    //RTR

    $log_table=$GLOBALS['table_prefix']."account_log";
    $query="INSERT INTO `$log_table` (";
    for (reset ($log); list ($key, $value) = each ($log); ) {
        $value = str_replace ("'", "\'", $value);
        $query.="`".$key."`,";
    }
    // strips the last comma that has been put
    $query = substr ($query, 0, strlen($query)-1);
    $query.=") VALUES (";
    for (reset ($log); list ($key, $value) = each ($log); ) {
        $value = str_replace ("'", "\'", $value);
        $query.="'".$value."',";
    }
    // strips the last comma that has been put
    $query = substr ($query, 0, strlen($query)-1);
    $query.=")";

    // CRYPTO
    $res = mysql_db_query ($_SESSION['account'],$query);
    if($errno=mysql_errno()) {
        $msg="Error in ".__FUNCTION__." - ";
        $msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
        $msg.='query: '.$query."\n";
        echo nl2br($msg)."\n";
        error_msg(__FILE__,__LINE__,$msg);
        return 3;
    }

		//chiama la funzione che scrive nel database tabella sources campo 'scontrinato' il valore 1
		tavolo_scontrinato();

    }
    //rtr
    return 0;


}

function bill_check_keep_separated(){
	if(isset($_REQUEST['keep_separated'])) $keep_separated=$_REQUEST['keep_separated'];
	else $keep_separated=0;

	if(!$keep_separated){

		//impedisce di resettare le variabili di sessione
		//va bene quando stampa i preconti così non altera nulla
		//ma fa casino quando cancello un'ordine... lo ripesca dalla sessione ed anche se l'ordine è deleted continua a visualizzarlo
		//if($_SESSION['type']>2) {
		//unset($_SESSION['separated']);
		//}

		//unset($_SESSION['type']);
		unset($_SESSION['separated']);
		unset($_SESSION['account']);
		//unset($_SESSION['tipo_corrispettivo']);
		//unset($_SESSION['discount']);
	}

	return $keep_separated;
}

function bill_check_empty(){

	$empty=true;

	if(is_array($_SESSION['separated'])){
		for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
			if(!$_SESSION['separated'][$key]['special']
				and $_SESSION['separated'][$key]['topay']){
				$empty=false;
			}
		}
	}
	return $empty;
}

function bill_print(){
/*
name:
bill_print()
returns:
0 - no error
1 - Printer not found for output type
2 - No order selected
3 - Printing error
other - mysql error number
*/
	// type: 	0: reserved		0: reserved
	//			1: bill			1: preconto
	//			2: bill1		2: preconto1
	//			3. invoice		3. bill
	//			4. invoice1		4. bill1
	//			5. receipt		5. invoice
	//			6. preconto		6. invoice1
	//			7. preconto1	7. receipt
	//	we have to translate them to the mgmt_type values in order to be correctely
	//	written and read in the log
	//	mgmt_type:	3: invoice - fattura
	//							4: bill - ricevuta
	//							5: receipt - scontrino
	global $tpl;
	global $output_page;
	$output['orders']='';
	$output_page = '';


	if($_SESSION['bill_printed']) return 0;
	$_SESSION['bill_printed']=1;

	$type = $_SESSION['type'];
	$tipo_corrispettivo = $_SESSION['tipo_corrispettivo'];
	$pagato_altri_metodi = $_SESSION['pagato_altri_metodi'];
	$pagato_carte_di_credito = $_SESSION['pagato_carte_di_credito'];
	$codice_lotteria = $_SESSION['codice_lotteria'];

	$keep_separated = bill_check_keep_separated();

	$type = receipt_type_waiter2mgmt($type);

//RTR impedisce di stampare una fattura se non sono presenti Nome e partita iva
	$customer_id = get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"customer",$_SESSION['sourceid']);
	$surname = get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"id", $customer_id);
	$vat_account = get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"vat_account", $customer_id);
	$email = get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"email", $customer_id);
	if($type==3 AND (!$surname OR !$vat_account OR !$email) ) {
		return ERR_NO_SURNAME_OR_VAT_ACCOUNT;
	}
//RTR fine

	// CRYPTO
	// " and $_SESSION['type']<5 "
	//impedisce al preconto di essere registrato come ricevuta nel database account_mgmt_main
	if(!bill_check_empty() and $_SESSION['type']>2) {

//legge dalla tabella sources i valori customer e takeaway_surname
		$customer_id=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"customer",$_SESSION['sourceid']);
		$takeaway_surname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway_surname",$_SESSION['sourceid']);
		$tavolo_numero=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$_SESSION['sourceid']);
	  $takeaway_surname= str_replace ("'", "\'", $takeaway_surname);;
		$receipt_id = receipt_insert($_SESSION['account'], $type, $customer_id, $takeaway_surname, $tavolo_numero, $tipo_corrispettivo);
	}

	$query="SELECT * FROM `#prefix#accounting_dbs` WHERE `db` = '".$_SESSION['account']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr=mysql_fetch_array($res);
	$printing_enabled=$arr['print_bill'];

	$tpl_print = new template;

	switch ($_SESSION['type']) {
		case 1:
			$query="SELECT * FROM `#prefix#dests` WHERE `preconto`='1' AND `deleted`='0'";
			$template_type='preconto';
			break;
		case 2:
			$query="SELECT * FROM `#prefix#dests` WHERE `preconto1`='1' AND `deleted`='0'";
			$template_type='preconto';
			break;
		case 3:
			$query="SELECT * FROM `#prefix#dests` WHERE `bill`='1' AND `deleted`='0'";
			$template_type='bill';
			break;
		case 4:
			$query="SELECT * FROM `#prefix#dests` WHERE `bill1`='1' AND `deleted`='0'";
			$template_type='bill';
			break;
		case 5:
			$query="SELECT * FROM `#prefix#dests` WHERE `invoice`='1' AND `deleted`='0'";
			$template_type='invoice';
			break;
		case 6:
			$query="SELECT * FROM `#prefix#dests` WHERE `invoice1`='1' AND `deleted`='0'";
			$template_type='invoice';
			break;
		case 7:
			$query="SELECT * FROM `#prefix#dests` WHERE `receipt`='1' AND `deleted`='0'";
			$template_type='receipt';
			break;
		default:
			$query="SELECT * FROM `#prefix#dests` WHERE `preconto`='1' AND `deleted`='0'";
			$template_type='preconto';
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr=mysql_fetch_array($res);
	if ($arr['dest']!='') {
		$destid=$arr['id'];
		$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	} else {
		return ERR_PRINTER_NOT_FOUND_FOR_SELECTED_TYPE;
	}

	if($err = $tpl_print->set_print_template_file($destid,$template_type)) return $err;

	// reset the counter and the message to be sent to the printer
	$total=7;
	$msg="";

  $takeaway_surname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway_surname",$_SESSION['sourceid']);
	$tablenum=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"name",$_SESSION['sourceid']);
	$output['table'] = ucfirst(lang_get($dest_language,'PRINTS_TABLE'))." $tablenum";
	$tpl_print->assign("table", $output['table']);

	// writes the table num to video
	$output_page .= ucfirst(phr('TABLE_NUMBER')).": $tablenum <br>";

	$table = new table($_SESSION['sourceid']);
	$table->fetch_data(true);
	if ($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);

		$output['customer']=$cust -> data ['surname'].' '.$cust -> data ['name'];
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
		$output['customer']="Codice SDI: ".$cust -> data ['email'];
		$tpl_print->assign("codice_SDI", $output['customer']);
	} else {
		$tpl_print->assign("customer_name", $takeaway_surname);
	}

	if(bill_check_empty()) {
		return ERR_NO_ORDER_SELECTED;
	}


	$output_page .= "
	<table bgcolor=\"".COLOR_TABLE_GENERAL."\">
	<thead>
	<tr>
	<th scope=col>".ucfirst(phr('QUANTITY_ABBR'))."</th>
	<th scope=col>".ucfirst(phr('NAME'))."</th>
	<th scope=col>".ucfirst(phr('PRICE'))."</th>
	</tr>
	</thead>
	<tbody>";

	$class=COLOR_ORDER_PRINTED;

	ksort($_SESSION['separated']);

	// the next for prints the list and the chosen dishes
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {

		//modifiche per stampare sulla PrintF inserito if else
		if ($_SESSION['type'] == 7){
			$output['orders'] .= bill_print_row_printf($key,$value,$destid);
			}
			else {
		$output['orders'] .= bill_print_row($key,$value,$destid);
		}
	}
	$tpl_print -> assign("orders", $output['orders']);

	if($_SESSION['discount']['type']=="amount"
	or $_SESSION['discount']['type']=="percent") {
		$output['discount']=bill_print_discount($receipt_id,$destid);
		$tpl_print->assign("discount", $output['discount']);
		$tpl_print->assign("discount_printf", $output['discount_printf']);
	}

	$total = bill_calc_vat();
	$total_discounted = bill_calc_discount($total);
	// updates the receipt value, has to be before print totals!
	receipt_update_amounts($_SESSION['account'],$total_discounted,$receipt_id);


//RTR prende il totale del preconto dagli ordini e non dal database mhr_accont_log che risulterebbe zero
//perchè non vogliamo inserire nulla nel database contabilità con i preconti
 if ($_SESSION['type'] < 3)  {
 	$output['total'] = bill_total_preconto();
	$output['discount']=bill_print_discount_preconto();
 } else {
//rtr
	$output['total'] = bill_print_total($receipt_id,$destid);
//RTR
	}
//rtr
	$tpl_print -> assign("total", $output['total']);
	$tpl_print -> assign("discount", $output['discount']);
	$output_page .= "
	</tbody>
	</table>";

	$output['receipt_id'] = bill_print_receipt_id ($receipt_id,$destid);
	$tpl_print -> assign ("receipt_id", $output['receipt_id']);

// RTR stampa la data sui ticket
	$tpl_print->assign("date", printer_print_date());
	$tpl_print->assign("data_senza_ora", printer_print_date_no_time());
	$tpl_print->assign("stampa_solo_ora", printer_print_time());

//RTR stampa il tipo di pagamento sulla ricevuta

	if ($_SESSION['tipo_corrispettivo'] == 'T4') {
		$tpl_print->assign("tipo_corrispettivo", "CORRISPETTIVO NON PAGATO");
		$tpl_print->assign("printf_corrispettivo", "=T4");
			// in lavorazione RTR now
			//if $esiste_valore_campo { $tpl_print->assign("printf_importo", "/".$importo);}
		}

	if ($_SESSION['tipo_corrispettivo'] == 'T2') {
		$tpl_print->assign("tipo_corrispettivo", "PAGAMENTO CON ASSEGNO");
		$tpl_print->assign("printf_corrispettivo", "=T2");
		}

	if ($_SESSION['tipo_corrispettivo'] == 'T3') {
		$tpl_print->assign("tipo_corrispettivo", "PAGAMENTO CARTE DI CREDITO");
			$tpl_print->assign("printf_corrispettivo", "=T3");
			}
		}

	if ($_SESSION['tipo_corrispettivo'] == 'T5') {
		$tpl_print->assign("tipo_corrispettivo", "PAGAMENTO CON ALTRI METODI");
			if ($pagato_altri_metodi){
				$tpl_print->assign("printf_corrispettivo", "=T5/"."\\$".
				number_format($pagato_altri_metodi,2,'','')
				."/(ALTRI MEDODI)"."\n"."=T1");
			}
			else {
				$tpl_print->assign("printf_corrispettivo", "=T5");
				}
		}

	if ($_SESSION['tipo_corrispettivo'] == 'T1') {
		$tpl_print->assign("tipo_corrispettivo", "PAGAMENTO IN CONTANTI");
		if ($pagato_carte_di_credito){
			$tpl_print->assign("printf_corrispettivo", "=T3/"."\\$".
			number_format($pagato_carte_di_credito,2,'','')
			."/(Carte Bancomat)"."\n"."=T1");
		}
		else {
			$tpl_print->assign("printf_corrispettivo", "=T1");
		}


//  Lotteria scontrini se abilitato nel file config.constans.inc.php
//  define ('ABILITA_LOTTERIA_SCONTRINI', true);
//  inserisce nel template/default/prints/receipt.$tpl
//  ="/?L/$1/({codice lotteria})
// 	codice alfanumerico di 8 cifre
//	$codice_lotteria ="12345678";


	if (ABILITA_LOTTERIA_SCONTRINI){

		if ($_SESSION['codice_lotteria']) {
			$tpl_print->assign( "codice_lotteria", "=\"/(Codice lotteria: ".$codice_lotteria.")" );
			$tpl_print->assign("instruzione_lotteria", "=\"/?L/\\$1/(".$codice_lotteria.")" );
		}

	}


//RTR stampa la voce corrispettivo Pagato oppure non pagato

	$output['taxes'] = bill_print_taxes ($receipt_id,$destid);
	$tpl_print -> assign("taxes", $output['taxes']);

	if($err = $tpl_print -> parse ()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='error: '.$err."\n";
		error_msg (__FILE__,__LINE__,$msg);
		echo nl2br ($msg)."\n";
		return ERR_PARSING_TEMPLATE;
	}

	$tpl_print -> restore_curly ();
	$msg = $tpl_print -> getOutput ();

	//RTR stampa gli apostrofi sui ticket
	//se abilito la riga sotto il carattere apostrofo singolo ' viene eliminato
	//$msg = str_replace ("'", "", $msg);

	if($printing_enabled) {
		if($err = print_line($arr['id'],$msg)) {
			// the process is stopped so we delete the created receipt
			receipt_delete($_SESSION['account'],$receipt_id);
			return $err;
		}
	}

	ksort($_SESSION['separated']);

	// sets the log
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($err_logger=bill_logger($key,$receipt_id)){
			debug_msg(__FILE__,__LINE__,__FUNCTION__.' - receipt_id: '.$receipt_id.' - logger return code: '.$err_logger);
		} else {
			debug_msg(__FILE__,__LINE__,__FUNCTION__.' - receipt_id: '.$receipt_id.' - logged');
		}
	}

	return 0;
}

function bill_logger($item_id,$receipt_id){
	$topay=$_SESSION['separated'][$item_id]['topay'];

	if(!$topay) return 1;

	$orderid=$item_id;

	$query="SELECT * FROM `#prefix#orders` WHERE `id`='$orderid' AND `deleted`!='1' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	$arr=mysql_fetch_array($res);
	$price=$arr["price"]/$arr["quantity"]*$topay;
	$oldpaid=$arr["paid"];
	$newpaid=$oldpaid+$topay;

	if($newpaid<0) $newpaid=0;

	//RTR
	switch ($_SESSION['type']) {
		case 1:
			;
			break;
		case 2:
			;
			break;
		case 3:
			$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
			break;
		case 4:
			$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
			break;
		case 5:
			$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
			break;
		case 6:
			$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
			break;
		case 7:
			$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '$orderid'";
			break;
		default:
			;
	}

	//RTR end


	$resupd=common_query($query,__FILE__,__LINE__);

	if(!$resupd) return mysql_errno();

	if($log_error=write_log_item($orderid,$topay,$price,$receipt_id)) {
		$msg = 'Error in '.__FUNCTION__.' - ';
		$msg .= 'Logging Error: '.$log_error;
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return 2;
	}

	$query="SELECT * FROM `#prefix#orders` WHERE `associated_id`='$orderid' AND `id`!='$orderid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	while($arr=mysql_fetch_array($res)) {
		$price=$arr["price"]/$arr["quantity"]*$topay;

		$query = "UPDATE `#prefix#orders` SET `paid` = '$newpaid' WHERE `id` = '".$arr['id']."'";
		$resupd=common_query($query,__FILE__,__LINE__);
		if(!$resupd) return mysql_errno();

		if($log_error=write_log_item($arr['id'],$topay,$price,$receipt_id)) {
			$msg = 'Error in '.__FUNCTION__.' - ';
			$msg .= 'Logging Error: '.$log_error;
			echo nl2br($msg)."\n";
			error_msg(__FILE__,__LINE__,$msg);
			return 2;
		}
	}

	return 0;
}

function bill_order_get_modifications($orderid,$lang='') {
	$max_chars=5;
	$show_priced_only = true;

	if(empty($lang)) $lang=$_SESSION['language'];

	// selects all the mods that have operation != 0, so that actually could have a price
	$query="SELECT * FROM `#prefix#orders` WHERE `associated_id`='$orderid' AND `id`!='$orderid' AND `operation`!='0'";
	if($show_priced_only) $query .= " AND `price`!='0'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	if(!mysql_num_rows($res)) return 0;

	echo $name."<br>\n";
	while($arr=mysql_fetch_array($res)) {
		if($arr['operation']==1) $name='+';
		elseif($arr['operation']==-1) $name='-';
		//$name.=' ';
		$ingredobj = new ingredient ($arr['ingredid']);
		$modname = $ingredobj -> name ($lang);

		$name.=substr($modname,0,$max_chars);
		$name.='.';
		$mods[]=$name;
	}
	return $mods;
}

function bill_print_row($key,$value,$destid){
	global $output_page;
	//RTR prima riga del conto
	$msg='';

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	$dishobj = new dish ($_SESSION['separated'][$key]['dishid']);
	$name = $dishobj -> name ($dest_language);
	$mods=bill_order_get_modifications($key,$dest_language);

	if($_SESSION['separated'][$key]['dishid']==SERVICE_ID) {
		$name=ucfirst(lang_get($dest_language,'SERVICE_FEE'));
	}

	$class=COLOR_ORDER_PRINTED;

	if($_SESSION['separated'][$key]['extra_care']){
		$classextra=COLOR_ORDER_EXTRACARE;
	} else {
		$classextra=$class;
	}

	if(!$_SESSION['separated'][$key]['special']
		and $_SESSION['separated'][$key]['topay']){

		//NUMERO DI ARTICOLI
		$msg.="".sprintf("%3.0f",$_SESSION['separated'][$key]['topay']);


		//NOME NELLA DESCRIZIONE ARTICOLO
		//RTR massima lunghezza del nome articolo a 19 e aggiunge spazi vuoti fino a 19
		//se non applico il driver prima della formattazione in caso di modifiche applicate dal driver
		//la formattazione applicata da sprintf viene compromessa
		$driver=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests','driver',$destid);
		$name = driver_apply($driver,$name);
		$msg.=" ".sprintf("%-19.19s",$name)."";


		// RTR TODO provo ad inserire il prezzo di ogni articolo
		//$msg.=" \t".$prezzo;

		// RTR TODO da formattare con le migliaia
		$msg.=" ".country_conf_currency()." ".sprintf("%7.2f",$_SESSION['separated'][$key]['finalprice']);
		$msg.="\n";

		if($mods) {
			$msg.="";
			$msg.="           ";
			for (reset ($mods); list ($key2, $value2) = each ($mods); ) {
				$msg.=$value2;
				$msgmods.=$value2;
			}
			$msg.="          \n";
		}

		$output_page .= '<tr bgcolor="'.$class.'">';
		$output_page .= '<td bgcolor="'.$class.'">';
		$output_page .= $_SESSION['separated'][$key]['topay'];
		$output_page .= '</td>
		<td bgcolor="'.$class.'">'.$name.'</td>
		<td bgcolor="'.$class.'">';
		$output_page .= sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		$output_page .= '</td></tr>';

		if($mods) {
			$output_page .= '<tr bgcolor="'.$class.'">';
			$output_page .= '
			<td bgcolor="'.$class.'">&nbsp;</td>
			<td bgcolor="'.$class.'">'.$msgmods.'</td>
			<td bgcolor="'.$class.'">&nbsp;</td>
			</tr>
			';
		}
	}

	return $msg;
}

//Funzione per la stampante MCH RCH PRINT-F
function bill_print_row_printf($key,$value,$destid){
	global $output_page;
	//RTR prima riga del conto
	$msg='';

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
	$dishobj = new dish ($_SESSION['separated'][$key]['dishid']);
	$name = $dishobj -> name ($dest_language);

	//$price cerco come inserire il valore del piatto relativo ad articolo TESTING
	//$price_dish=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','price',$id);

	//da verificare le aggiunte agli articoli
	$mods=bill_order_get_modifications($key,$dest_language);

	if($_SESSION['separated'][$key]['dishid']==SERVICE_ID) {
		$name=ucfirst(lang_get($dest_language,'SERVICE_FEE'));
	}

	$class=COLOR_ORDER_PRINTED;

	if($_SESSION['separated'][$key]['extra_care']){
		$classextra=COLOR_ORDER_EXTRACARE;
	} else {
		$classextra=$class;
	}

	if(!$_SESSION['separated'][$key]['special']
		and $_SESSION['separated'][$key]['topay']){

		//per formattare con la PrintF serve prima il prezzo del singolo articolo
		//inserire il prezzo del singolo articolo
		$msg.="=R5/";
		//attenzione agli escape \ sono necessari \\ per far arrivare alla stampante il carattere $
		$msg.="\\$";

		//TODO ricavare il prezzo del singolo articolo
		//utilizzare il dato dalla tabella orders (price/quantity)
		//non riesco ad estrare il prezzo del singolo articolo ed indicarlo
		//nella descrizione dell'articolo
		//$msg.=$price_dish;


		$msg.="".number_format($_SESSION['separated'][$key]['finalprice'],2,'','');

		//descrizione articolo inviata alla stampante tutto il contenuto ta ()
		//Attenzione la prima parentesi è necessaria per consentire la descrizione
		// articolo sulla stampante
		$msg.="/(";
		// numero di articoli
		$msg.="".sprintf("%3.0f",$_SESSION['separated'][$key]['topay']);


		//NOME NELLA DESCRIZIONE ARTICOLO
		//il driver stampante aggiunge spazi vuoti fino al 19° carattere
		$msg.=" ".$name;

		// TODO inserire il prezzo di ogni articolo
		//$msg.=" \t".$prezzo;
		//$msg.=" ".country_conf_currency()." ".sprintf("%7.2f",$_SESSION['separated'][$key]['finalprice']);
		// TODO da formattare con le migliaia

		if($mods) {
			$msg.=" ";
			for (reset ($mods); list ($key2, $value2) = each ($mods); ) {
				$msg.=$value2;
				$msgmods.=$value2;
			$msg.=" ";
			}

			$msg.="";
		}
		//ATTENZIO alla parentesi sotto serve alla stampante PrintF
		$msg.=")";
		//per poter stampare 1 parentesi ) devi metterne 2 ))

		$msg.="";
		$msg.="\n";

		$output_page .= '<tr bgcolor="'.$class.'">';
		$output_page .= '<td bgcolor="'.$class.'">';
		$output_page .= $_SESSION['separated'][$key]['topay'];
		$output_page .= '</td>
										<td bgcolor="'.$class.'">'.$name.'</td>
										<td bgcolor="'.$class.'">';
		$output_page .= sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		$output_page .= '</td></tr>';

		if($mods) {
			$output_page .= '<tr bgcolor="'.$class.'">';
			$output_page .= '
			<td bgcolor="'.$class.'">&nbsp;</td>
			<td bgcolor="'.$class.'">'.$msgmods.'</td>
			<td bgcolor="'.$class.'">&nbsp;</td>
			</tr>';
		}
	}

	return $msg;
}

function bill_print_discount($receipt_id,$destid) {
	global $output_page;

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$msg="";
	$class=COLOR_ORDER_PRINTED;

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}

	if($_SESSION['discount']['type']=="amount") {
		$discount_value=$_SESSION['discount']['amount'];
		$total_discounted=$total+$discount_value;
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	}
	elseif($_SESSION['discount']['type']=="percent") {
		$discount_value=$total/100*$_SESSION['discount']['percent'];
		$total_discounted=$total-$discount_value;
		$discount_label=$_SESSION['discount']['percent'].'%';
		$discount_number=$total/100*$_SESSION['discount']['percent'];
	} else {
		return $msg;
	}
	$total_discounted=round($total_discounted,2);
	$discount_number=round($discount_number,2);

	if(!$discount_number) return $msg;

//RTR impedisce al preconto di essere aggiunto al database
    if ($_SESSION['type'] < 3){ ; }

	elseif ($_SESSION['type'] == 7) {
		$err = write_log_discount($discount_value,$receipt_id);
		$err = discount_save_to_source($discount_value);
		$msg.="=S"."\n";
		$msg.="=V-/*";
		$msg.="";
		//$msg.=$discount_number;
		$msg.=number_format($discount_number,2,'','');

			if ($_SESSION['discount']['type']=="amount") {
				$msg.="/(SCONTO IN EURO)";
				}
			if ($_SESSION['discount']['type']=="percent") {
				$msg.="/(SCONTO DEL ".$_SESSION['discount']['percent']." %)";
				}
		}

	else {

		$err = write_log_discount($discount_value,$receipt_id);
		$err = discount_save_to_source($discount_value);

		$msg.="\t\t"."Sconto ";
		$msg.=" ".country_conf_currency()." ".sprintf("%7.2f","-".$discount_number);
		}

	$output_page .= '
	<tr bgcolor="'.$class.'">
	<td></td>
	<td>'.ucfirst(phr('DISCOUNT')).' '.$discount_label.'</td>
	<td>'.sprintf("%0.2f",$discount_number).'</td>
	</tr>'."<br>\n";

	return $msg;
}

function bill_calc_vat() {
// calculates the taxes amounts for each selected order
	$_SESSION['vat']=array();

// scans all the orders that have a final price != 0
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($_SESSION['separated'][$key]['finalprice']) {
			$dishid=$_SESSION['separated'][$key]['dishid'];
			$price=$_SESSION['separated'][$key]['finalprice'];
			$dish_cat=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"category",$dishid);
			$vat_rate_id=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories',"vat_rate",$dish_cat);
			$vat_rate=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"rate",$vat_rate_id);
			$taxable=$price/($vat_rate+1);
			$tax=$taxable*$vat_rate;

			// creates the vat array with tax, taxable and total divided per vat type
			$_SESSION['vat'][$vat_rate_id]['taxable']+=$taxable;
			$_SESSION['vat'][$vat_rate_id]['tax']+=$tax;
			$_SESSION['vat'][$vat_rate_id]['total']+=$taxable+$tax;
		}
	}

	// adds the human readable info (name and rate) to each vat type
	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
			$vat_rate_name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"name",$key);
			$vat_rate=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'vat_rates',"rate",$key);
			$_SESSION['vat'][$key]['name']=$vat_rate_name;
			$_SESSION['vat'][$key]['rate']=$vat_rate;
	}

	// prepares the return array
	$ret['taxable']=0;
	$ret['tax']=0;


	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		// rounds each value
		$_SESSION['vat'][$key]['taxable']=round($_SESSION['vat'][$key]['taxable'],2);
		$_SESSION['vat'][$key]['tax']=round($_SESSION['vat'][$key]['tax'],2);
		$_SESSION['vat'][$key]['total']=round($_SESSION['vat'][$key]['total'],2);

		// sums all the values from the vat array to get totals and return them
		$ret['taxable']+=$_SESSION['vat'][$key]['taxable'];
		$ret['tax']+=$_SESSION['vat'][$key]['tax'];
		$ret['total']+=$_SESSION['vat'][$key]['total'];
	}

	return $ret;
}

function bill_calc_discount($total) {
	// calculates a mean vat rate
	if($total['taxable']) $mean_vat_rate=$total['total']/$total['taxable']-1;
	else $mean_vat_rate=0;

	// assign the total discount amount
	if($_SESSION['discount']['type']=="amount") {
		$disc_total=$_SESSION['discount']['amount'];
	}
	elseif($_SESSION['discount']['type']=="percent") {
		$disc_total=$total['total']/100*$_SESSION['discount']['percent'];
	} else {
		$disc_total=0;
		//return $total;
	}

	// assigns taxes on the discount
	$disc_taxable=$disc_total/($mean_vat_rate+1);
	$disc_tax=$disc_total-$disc_taxable;


	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		// corrects the tax, taxable and total values for each vat rate, by subracting a weighted part of the discount
		if ($total['tax'] != 0) {
			$_SESSION['vat'][$key]['tax']=$_SESSION['vat'][$key]['tax']-abs($_SESSION['vat'][$key]['tax']/$total['tax']*$disc_tax);
		} else {
			$_SESSION['vat'][$key]['tax']=0;
		}
		if ($total['taxable'] != 0) {
			$_SESSION['vat'][$key]['taxable']=$_SESSION['vat'][$key]['taxable']-abs($_SESSION['vat'][$key]['taxable']/$total['taxable']*$disc_taxable);
		} else {
			$_SESSION['vat'][$key]['taxable']=0;
		}
		if ($total['total'] != 0) {
			$_SESSION['vat'][$key]['total']=$_SESSION['vat'][$key]['total']-abs($_SESSION['vat'][$key]['total']/$total['total']*$disc_total);
		} else {
			$_SESSION['vat'][$key]['total']=0;
		}

		// rounds everything
		$_SESSION['vat'][$key]['taxable']=round($_SESSION['vat'][$key]['taxable'],2);
		$_SESSION['vat'][$key]['tax']=round($_SESSION['vat'][$key]['tax'],2);
		$_SESSION['vat'][$key]['total']=round($_SESSION['vat'][$key]['total'],2);
	}

	$total['total']=$total['total']-abs($disc_total);
	$total['taxable']=$total['taxable']-abs($disc_taxable);
	$total['tax']=$total['tax']-abs($disc_tax);

	$total['total']=round($total['total'],2);
	$total['total']=sprintf("%7.2f",$total['total']);
	$total['taxable']=round($total['taxable'],2);
	$total['taxable']=sprintf("%7.2f",$total['taxable']);
	$total['tax']=round($total['tax'],2);
	$total['tax']=sprintf("%7.2f",$total['tax']);

	return $total;
}

//RTR
function bill_total_preconto(){
global $output_page;
//$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);
$total = 0;
$msg = '';

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}
	if(!isset($_SESSION['discount']) || !isset($_SESSION['discount']['type']) || empty($_SESSION['discount']['type'])){
	$descrizione_totale="Totale";
	$total_discounted= $total;
	}
	if($_SESSION['discount']['type']=="amount") {
		$total_discounted=$total+$_SESSION['discount']['amount'];
		$descrizione_totale="Totale";
	} elseif($_SESSION['discount']['type']=="percent") {
		$total_discounted=$total-$total/100*$_SESSION['discount']['percent'];
		$descrizione_totale="Totale";
	}

	$total_discounted=sprintf("%7.2f",$total_discounted);
	$msg.="\t\t\t".$descrizione_totale." ".country_conf_currency()." $total_discounted";

	return $msg;
}


function bill_print_discount_preconto() {
	global $output_page;
$total = 0;

//	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$msg="";
//	$class=COLOR_ORDER_PRINTED;

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}

	if($_SESSION['discount']['type']=="amount") {
		$discount_value=$_SESSION['discount']['amount'];
		$total_discounted=$total+$discount_value;
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	}
	elseif($_SESSION['discount']['type']=="percent") {
		$discount_value=$total/100*$_SESSION['discount']['percent'];
		$total_discounted=$total-$discount_value;
		$discount_label=$_SESSION['discount']['percent'].'%';
		$discount_number=$total/100*$_SESSION['discount']['percent'];
	} else {
		return $msg;
	}
	$total_discounted=round($total_discounted,2);
	$discount_number=round($discount_number,2);

	if(!$discount_number) return $msg;


//RTR impedisce al preconto di essere aggiunto al database
    if ($_SESSION['type'] < 3){
	//$msg.=$discount_number;
	//$msg.=number_format($discount_number,2,'','');
	if ($_SESSION['discount']['type']=="amount") {
				$msg.="SCONTO IN EURO";
				}
	if ($_SESSION['discount']['type']=="percent") {
				$msg.="SCONTO DEL ".$_SESSION['discount']['percent']."%";
				}
	}

	//$msg.="\t".ucfirst(lang_get($dest_language,'PRINTS_DISCOUNT'))." ".$discount_label;
	$msg.=" ".country_conf_currency()." ".sprintf("%7.2f","-".$discount_number);
	return $msg;
}

//rtr

function bill_print_total($receipt_id,$destid) {
	global $output_page;

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$msg="";
	$class=COLOR_ORDER_PRINTED;

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";
	$res=mysql_db_query($_SESSION['account'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return '';
	}
	$arr=mysql_fetch_array($res);
	$total_discounted=$arr['cash_amount'];

	$output_page .= '<tr>
	<td></td>
	<td>'.ucfirst(phr('TOTAL')).'</td>
	<td>'.$total_discounted.'</td>
	</tr>'."\n";

	$total_discounted=sprintf("%7.2f",$total_discounted);
	$msg.="\t\t".ucfirst(lang_get($dest_language,'PRINTS_TOTAL'))." \t".country_conf_currency()." $total_discounted";

	return $msg;
}

function bill_print_taxes($receipt_id,$destid) {
	$msg="";

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";
	$res=mysql_db_query($_SESSION['account'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return $errno;
	}

	$arr=mysql_fetch_array($res);
	$taxable=$arr['cash_taxable_amount'];
	$vat_total=$arr['cash_vat_amount'];
	$taxable=sprintf("%7.2f",$taxable);
	$msg.="".ucfirst(lang_get($dest_language,'PRINTS_TAXABLE'))." \t\t".country_conf_currency()." $taxable";

	for (reset ($_SESSION['vat']); list ($key, $value) = each ($_SESSION['vat']); ) {
		$vat_rate_name=$_SESSION['vat'][$key]['name'];
		$vat_rate=$_SESSION['vat'][$key]['rate']*100;
		$vat_local=$_SESSION['vat'][$key]['tax'];
		$vat_local=sprintf("%7.2f",$vat_local);
		$msg.="\n".ucfirst(lang_get($dest_language,'PRINTS_TAX'))." ".$vat_rate_name." (".$vat_rate."%) \t".country_conf_currency()." $vat_local";
	}
	$vat_total=sprintf("%7.2f",$vat_total);
	$msg.="\n".ucfirst(lang_get($dest_language,'PRINTS_TAX_TOTAL'))." \t\t".country_conf_currency()." $vat_total";

	return $msg;
}

function bill_print_receipt_id($receipt_id,$destid) {
	$msg="";

	$dest_language=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dests',"language",$destid);

	$table=$GLOBALS['table_prefix'].'account_mgmt_main';
	$query="SELECT * FROM $table WHERE `id`='$receipt_id'";
	$res=mysql_db_query($_SESSION['account'],$query);
	if($errno=mysql_errno()) {
		$msg="Error in ".__FUNCTION__." - ";
		$msg.='mysql: '.mysql_errno().' '.mysql_error()."\n";
		$msg.='query: '.$query."\n";
		echo nl2br($msg)."\n";
		error_msg(__FILE__,__LINE__,$msg);
		return '';
	}

	$arr=mysql_fetch_array($res);
	$internal_id=$arr['internal_id'];
	$type=$arr['type'];

	if($type==3){
		$msg.="FATTURA N."."$internal_id";
	} elseif($type==4) {
		$msg.="RICEVUTA N."."$internal_id";
	} elseif($type==5) {
		$msg.="SCONTRINO N."."$internal_id";
	}

	return $msg;
}

function bill_quantity($id,$operation){
	$query="SELECT * FROM `#prefix#orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr=mysql_fetch_array($res);

	$max_quantity=$arr['quantity']-$arr['paid'];

	if($operation==1 and $_SESSION['separated'][$id]['topay']>=$max_quantity) return 1;
	if($operation==-1 and $_SESSION['separated'][$id]['topay']<=0) return 2;
	if($operation==1) $_SESSION['separated'][$id]['topay']++;
	elseif($operation==-1) $_SESSION['separated'][$id]['topay']--;
	return 0;
}

function bill_clear_prices($sourceid){
	// clears the price of every product,
	// so that adding the mod prices starts from zero instead of the precedent price
	$query="SELECT * FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."'  AND `price`!='0' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return ERR_NO_ORDER_FOUND;
	while($arr=mysql_fetch_array($res)){
		$id=$arr['id'];
		$_SESSION['separated'][$id]['price']=0;
	}
	return 0;
}

function bill_select(){
/*
name:
bill_select()
returns:
0 - no error
1 - generic dish with no price found
2 - internal error clearing prices
3 - other - mysql error number
*/
	global $tpl;

	if(!bill_orders_to_print ($_SESSION['sourceid'])) {
		printing_choose(true);
		return 0;
	}

	$tpl -> set_waiter_template_file ('bill_select');

	$tmp ='';
	$tmp .= navbar_form('form_type','orders.php?command=printing_choose');
	$tpl -> assign ('navbar',$tmp);
	$_SESSION['bill_printed']=0;

	if(order_found_generic_not_priced($_SESSION['sourceid'])){
		$tmp = '<br><br><br>
				<FORM ACTION="orders.php?command=reopen" METHOD=POST>
				<INPUT TYPE="submit" value=" RIAPRI TAVOLO " class="button_big">
				</form>
				<br>
				';
		$tpl -> append ('messages',$tmp);

		return ERR_GENERIC_ORDER_NOT_PRICED_FOUND;
	}

	$keep_separated = bill_check_keep_separated();

	if($err=bill_clear_prices($_SESSION['sourceid'])) return $err;
	if($err=bill_save_session($_SESSION['sourceid'])) return $err;
	$tmp = bill_method_selector();
	$tpl -> assign ('method',$tmp);

	$tmp = bill_type_selection($_SESSION['sourceid']);
	$tpl -> assign ('type',$tmp);

if (!access_allowed(USER_BIT_WAITER) OR access_allowed(USER_BIT_CONFIG)) {
	$tmp = discount_form_javascript($_SESSION['sourceid']);
	$tpl -> assign ('discount',$tmp);
 }


	$tmp = bill_show_list();
	$tpl -> assign ('orders',$tmp);

	return 0;
}


function bill_method_selector(){

//RTR Start blocco Inserisci dati cliente
	$table = new table ($_SESSION['sourceid']);
	$table->fetch_data(true);
	$data=dati_prenotazione($_SESSION['sourceid']);

	if($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		$tmp = '<a href="orders.php?command=customer_search"><img src="'.IMAGE_FIND.'" alt="CAMBIA CLIENTE" border=0 align="absmiddle" width="32px" height="32px"></a>';
		$tmp .= '<b> '.$cust->data['surname'];
		$tmp .= ' </b>';
		$tmp .= ' <a href="orders.php?command=customer_edit_form&amp;data[id]='.$data['customer'].'"><img align="absmiddle" src="'.IMAGE_CUSTOMER_KNOWN.'" width="32" height="32" border="0" alt="Modifica dati"></a>';
		$tmp .= ' <a href="orders.php?command=set_customer&amp;data[customer]=0&amp;data[takeaway_surname]= "><img src="'.IMAGE_LITTLE_TRASH.'" alt="CANCELLA" border=0 align="absmiddle" width="35px" height="35px"></a>';
		$tmp .= '<br>
				'.$cust -> data['name'].'<br>
				'.$cust -> data['address'].'<br>
				'.$cust -> data['zip'].' - '.$cust -> data['city'].'<br>
				Tel. '.$cust -> data['phone'].'<br>
				Cell. '.$cust -> data['mobile'].'<br>
				<b>CODICE SDI: '.$cust -> data['email'].'</b><br>
				P.I.: '.$cust -> data['vat_account'].'<br>
				C.F.: '.$cust -> data['codice_fiscale'].'<br>
				<br>

		';
	} else {

		$takeaway_surname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"takeaway_surname",$_SESSION['sourceid']);
		if($takeaway_surname){
		$tmp .= '
		Cognome prenotazione: <b>'.$takeaway_surname.'</b> <a href=" orders.php?command=set_customer&amp;data[takeaway_surname]= "><img src="'.IMAGE_LITTLE_TRASH.'" alt="CANCELLA" border=0 align="absmiddle" width="35px" height="35px"></a>
		<br><br>
		';
		}
		$tmp .= '
		<FORM ACTION="orders.php?command=customer_search" METHOD=POST>
		<INPUT TYPE="submit" value="Inserisci dati cliente" class="button_big">
		</form>
		';
	}
	$output .= $tmp;
//RTR end blocco Inserisci Cliente


	if(!$_SESSION['select_all']){
		$output .= 	'
					<FORM ACTION="orders.php?sourceid='.$_SESSION['sourceid'].'&amp;command=bill_select_all" METHOD=POST>
					<INPUT TYPE="submit" value=" SELEZIONA TUTTO " class="button_big">
					</form>
					'."\n";
	} else {
		$output .= '
				<FORM ACTION="orders.php?command=bill_select" METHOD=POST>
				<INPUT TYPE="submit" value=" CONTI SEPARATI " class="button_big">
				</form>
				'."\n";
	}

	$user = new user($_SESSION['userid']);
	if ($user->level[USER_BIT_CASHIER]) {
		$output .= '
					<FORM ACTION="orders.php?command=bill_reset" METHOD=POST>
					<INPUT TYPE="submit" value="Azzera conti separati" class="button_big">
					</form>
					';
	}


	$output .= '
				<FORM ACTION="orders.php?command=reopen" METHOD=POST>
				<INPUT TYPE="submit" value=" RIAPRI TAVOLO " class="button_big">
				</form>
				';
	
	$output .= '
		<FORM ACTION="orders.php?command=ask_move" METHOD=POST>
		<INPUT TYPE="submit" value="SPOSTA TAVOLO" class="button_big">
		</form>
		';

	return $output;
}

function bill_show_list(){
	/*
	prints on the page the list of dishes based on the waiter session
	gets the sourceid var from start.php
	*/

	$output .= '';
	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">';
	// RTR riepilogo ordine prima della stampa
	$output .= '<thead>
	<tr>
	<th scope=col>Q.tà</th>
	<th scope=col>Nome</th>
	<th scope=col></th>
	<th scope=col>Euro</th>
	<th scope=col></th>
	<th scope=col></th>
	</tr>
	</thead>
	<tbody>';


	$class=COLOR_ORDER_PRINTED;

	ksort($_SESSION['separated']);

	// the next for prints the list and the chosen dishes
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		if($_SESSION['separated'][$key]['extra_care']){
			$classextra=COLOR_ORDER_EXTRACARE;
		} else {
			$classextra=$class;
		}

		$_SESSION['separated'][$key]['finalprice']=$_SESSION['separated'][$key]['price']/$_SESSION['separated'][$key]['quantity']*$_SESSION['separated'][$key]['topay'];

		$output .= '
		<tr bgcolor="'.$class.'">
		<td bgcolor="'.$class.'" align="center">
		';
		if(!$_SESSION['separated'][$key]['special'])
			$output .= $_SESSION['separated'][$key]['topay'].' / <b>'.$_SESSION['separated'][$key]['max_quantity'].'</b>';

		$output .= '
		</td>
		<td bgcolor="'.$class.'">'.$_SESSION['separated'][$key]['name'].'</td>
		<td bgcolor="'.$classextra.'">
		';
		if($_SESSION['separated'][$key]['extra_care'])
			$output .= ucfirst(phr('EXTRA_CARE_ABBR'));
		$output .= '
		</td>
		<td bgcolor="'.$class.'" align="right">
		';
		if(!$_SESSION['separated'][$key]['special']){
			$output .= sprintf("%0.2f",$_SESSION['separated'][$key]['finalprice']);
		}
		$output .= '
		</td>
		<td align="center" bgcolor="'.$class.'">
		';
		if(!$_SESSION['separated'][$key]['special'] and !$_SESSION['select_all']){
			if($_SESSION['separated'][$key]['topay']<$_SESSION['separated'][$key]['max_quantity']){
				$output .= '<a href="orders.php?command=bill_quantity&amp;keep_separated=1&amp;orderid='.$key.'&amp;operation=1&amp;rndm='.rand(0,100000).'">
		<img src="'.IMAGE_PLUS.'" alt="'.ucfirst(phr('PLUS')).' ('.ucfirst(phr('ADD')).')" border=0>
		</a>';
			}
		}
		$output .= '
		</td>
		<td align="center" bgcolor="'.$class.'">
		';
		if(!$_SESSION['separated'][$key]['special'] and !$_SESSION['select_all']){
			if($_SESSION['separated'][$key]['topay']>0){
				$output .= '<a href="orders.php?command=bill_quantity&amp;keep_separated=1&amp;orderid='.$key.'&amp;operation=-1&amp;rndm='.rand(0,100000).'">
		<img src="'.IMAGE_MINUS.'" alt="'.ucfirst(phr('MINUS')).' ('.ucfirst(phr('ADD')).')" border=0>
		</a>';
			}
		}
		$output .= '
		</td>
		</tr>
		';
	}

	$output .= bill_total();
	
	//Costo a persona
	$output .= totale_a_persona();
	


	$output .= '</table>
				';

	return $output;
}

function bill_save_session($sourceid){
	/*
	Takes every single dish and saves the following info in the waiter session
	saved data:
	price 	(in case of mod adds the price to the associated dish
			and sets 0 to the actual dish)
	name
	special (1 if mod, 0 if not mod)
	quantity
	max_quantity (the max available to pay quantity)
	extra_care
	topay 	(the quantity that the customers asks to pay
			it is set to max_quantity if the customer pays the full bill)
	*/

	$query="SELECT * FROM `#prefix#orders` WHERE `sourceid`='$sourceid' AND `price`!='0' AND `deleted`=0 AND `printed` IS NOT NULL ORDER BY `associated_id` ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return ERR_NO_ORDER_FOUND;

	while($arr=mysql_fetch_array($res)){
		$id=$arr['id'];
		$associated_id=$arr['associated_id'];
		debug_msg(__FILE__,__LINE__,"separated order select - associated id (associated_id): ".$arr['associated_id']);
		if(!isset($_SESSION['separated'][$id]['topay'])) {
			$_SESSION['separated'][$id]['topay']=0;
		}

		$_SESSION['separated'][$id]['quantity']=$arr['quantity'];
		if(order_is_mod($id)==2) {
			$_SESSION['separated'][$associated_id]['price']+=$arr['price'];
			$_SESSION['separated'][$id]['price']=0;
			$ingredobj = new ingredient ($arr['ingredid']);
			$modname = $ingredobj -> name ($_SESSION['language']);
			$_SESSION['separated'][$id]['name']="&nbsp;&nbsp;&nbsp;".ucfirst($modname);
			$_SESSION['separated'][$id]['special']=true;
			debug_msg(__FILE__,__LINE__,"separated order select - found mod: ".$_SESSION['separated'][$id]['name']);
			debug_msg(__FILE__,__LINE__,"separated order select -    added price: ".$arr['price']);
		} else {
			$_SESSION['separated'][$id]['max_quantity']=$arr['quantity']-$arr['paid'];
			$_SESSION['separated'][$id]['price']+=$arr['price'];
			$_SESSION['separated'][$id]['dishid']=$arr['dishid'];
			$dishobj = new dish ($arr['dishid']);
			$_SESSION['separated'][$id]['name'] = ucfirst($dishobj -> name ($_SESSION['language']));
			$_SESSION['separated'][$id]['extra_care']=$arr['extra_care'];
			$_SESSION['separated'][$id]['special']=false;
			if($_SESSION['select_all'])
				$_SESSION['separated'][$id]['topay']=$_SESSION['separated'][$id]['max_quantity'];
		}

		if(order_is_mod($id)==1) {
			$_SESSION['separated'][$id]['name']=ucfirst(phr('SERVICE_FEE'));
		}
	}
	return 0;
}

function bill_account_set($account){
	if(empty($account)) {
		$account='';
	}
	$account=common_find_first_db($account);
	$_SESSION['account']=$account;
	return 0;
}

function bill_type_selection($sourceid){
	/*
	sets the bill/invoice type in waiter's session environment
	types:
	1. bill			1. preconto
	2. bill1		2. preconto1
	3. invoice		3. bill
	4. invoice1		4. bill1
	5. receipt		5. invoice
	6. preconto		6. invoice1
	7. preconto1	7. receipt
	*/

	//Legge dal database users quali stampanti possono essere visualizzate per ciascun utente
	$preconto=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"preconto",$_SESSION['userid']);
	$preconto1=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"preconto1",$_SESSION['userid']);
	$ricevuta=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"ricevuta",$_SESSION['userid']);
	$ricevuta1=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"ricevuta1",$_SESSION['userid']);
	$fattura=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"fattura",$_SESSION['userid']);
	$fattura1=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"fattura1",$_SESSION['userid']);
	$scontrino=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'users',"scontrino",$_SESSION['userid']);

	for($i=1;$i<=7;$i++) $chk[$i]='';

	if(isset($_SESSION['type'])){
		$type=$_SESSION['type'];
	} else {
		if(!$preconto){ $type=2; } else { $type=1; }// if type is not set, it automatically sets it to 1 preconto;
		if(table_is_takeaway($_SESSION['sourceid'])) {
			$type=7; // if type is not set and table is takeaway type is set to 7;
		}
		$_SESSION['type']=$type;
	}

	if(isset($_SESSION['account'])){
		$account=$_SESSION['account'];
	} else {
		$account=common_find_first_db();
		$_SESSION['account']=$account;
	}

	// Next is a micro-form to set a discount in percent value

	$output .= '
	<FIELDSET>
	<LEGEND><B>STAMPA IL CONTO</B></LEGEND>

	<form action="orders.php" NAME="form_type" method=post>
	<input type="hidden" name="command" VALUE="bill_print">
	<INPUT TYPE="HIDDEN" NAME="keep_separated" VALUE="1">
	';

	$chk[$type] = 'checked';

	$output .= '<table border="0" cellspacing="0" cellpadding="5">';

	$codice_lotteria = $_SESSION['codice_lotteria'];
  // Codice Lotteria scontrini
	$output .= '<tr><td align="center"><FIELDSET>
																				<LEGEND><B>Codice lotteria</B></LEGEND>
																					<input type="text" class="button_big" name="codice_lotteria" size="8" minlength="8" maxlength="8" id="codice_lotteria" value="'.$codice_lotteria.'"
																					onblur="verifica_codice_lotteria()">
																					<input type="button" class="button_big" value="X" onclick="javascript:document.getElementById(\'codice_lotteria\').value=null">
																					<br /><i id="legenda-codice-lotteria">8 caratteri lettere e numeri</i>';
	if (!ABILITA_LOTTERIA_SCONTRINI){
	$output .= '														<br /><b>Attenzione!!!</b><br />Codice non abilitato in <br />config.constans.inc.php';
	}
	$output .= '
																				</FIELDSET>
							</td></tr>';
	$output .= '<tr><td></td></tr>';

	if ($preconto){
		$output .= '<tr><td><input type="radio" name="type" value="1" '.$chk[1].' class="radio"> <b>preconto</b><br><br></td></tr>';
	}
	if ($preconto1){
		$output .= '<tr><td bgcolor="lightgrey"><input type="radio" name="type" value="2" '.$chk[2].' class="radio"><b>preconto1 BAR</b><br><br></td></tr>';
	}

//	$output .= '<tr><td><br><hr><br></td></tr>';

	if ($ricevuta){
		$output .= '<tr><td><input type="radio" name="type" value="3" '.$chk[3].' class="radio">ricevuta<br><br></td></tr>';
	}
	if ($ricevuta1){
		$output .= '<tr><td bgcolor="lightgrey"><input type="radio" name="type" value="4" '.$chk[4].' class="radio"> ricevuta1 BAR<br><br></td></tr>';
	}
	if ($fattura){
		$output .= '<tr><td><input type="radio" name="type" value="5" '.$chk[5].' class="radio">fattura<br><br></td></tr>';
	}
	if ($fattura1){
		$output .= '<tr><td bgcolor="lightgrey"><input type="radio" name="type" value="6" '.$chk[6].' class="radio">fattura1 BAR<br><br></td></tr>';
	}
	if ($scontrino){
		$output .= '<tr><td bgcolor="lightgrey"><input type="radio" name="type" value="7" '.$chk[7].' class="radio">scontrino BAR</td></tr>';
	}
	$output .= '</table>';

// RTR sistemare la funzione if era pensata solo per corrispettivo=1 oppure zero un casino!!!
	if (!$_SESSION['tipo_corrispettivo']) { $check1=''; }
	if ($_SESSION['tipo_corrispettivo']=='T1') { $check1='checked'; }
	if ($_SESSION['tipo_corrispettivo']=='T2') { $check2='checked'; }
	if ($_SESSION['tipo_corrispettivo']=='T3') { $check3='checked'; }
	if ($_SESSION['tipo_corrispettivo']=='T4') { $check4='checked'; }
	if ($_SESSION['tipo_corrispettivo']=='T5') { $check5='checked'; }


	//$chk1[$tipo_corrispettivo] = 'checked';

// PrintF CORRISPETTIVI e tipi di pagamento.
// AGGIUNGE I MEDODI DI PAGAMENTO per invio alla PRINTF
// T1 pagato contatni
// T2 pagamento con ASSEGNI
// T3 pagamento con CARTE DI CREDITO
// T4 pagamento non riscosso (il KBill non supporta più di 3 metodi)
// T5 pagamento Altri medodi di pagamento
// T6 pagamento 6 (non utilizzato)
// T7 pagamento 7 (non utilizzato)
// T8 pagamento 8 (non utilizzato)
// T9 pagamento 9 (non utilizzato)
// T10 pagamento 10 (non utilizzato)
//
//per il momento affrontiamo 4 metodi
// 1.Contanti
// 2.Assegni
// 3.Carte di credito
// 4.Non riscosso
// 5.Altri

	if (access_allowed(USER_BIT_MONEY)) {
	// Totale per link POS (stesso calcolo di bill_total: finalprice e sconto)
	$totale_pos = 0;
	for (reset($_SESSION['separated']); list($key, $val) = each($_SESSION['separated']); ) {
		$totale_pos += $_SESSION['separated'][$key]['price'] / $_SESSION['separated'][$key]['quantity'] * $_SESSION['separated'][$key]['topay'];
	}
	if (isset($_SESSION['discount']['type']) && $_SESSION['discount']['type'] === 'amount') {
		$totale_pos += $_SESSION['discount']['amount'];
	} elseif (isset($_SESSION['discount']['type']) && $_SESSION['discount']['type'] === 'percent') {
		$totale_pos -= $totale_pos / 100 * $_SESSION['discount']['percent'];
	}
	$totale_pos = round($totale_pos, 2);
	$totale_pos = max(0.01, $totale_pos);
	$pos_url = ROOTDIR . '/POS/ingenico.php?amount=' . urlencode(sprintf('%0.2f', $totale_pos)) . '&from=waiter';
	$pos_base_url = ROOTDIR . '/POS/ingenico.php?from=waiter&amount=';

	$output .= '
	<br><br>
	<FIELDSET>
	<LEGEND><b>CORRISPETTIVO</b></LEGEND>
		<table>
			<tr align="left">
				<td align="left"><input type="radio" name="tipo_corrispettivo" '.$check1.' value="T1" class="radio" onclick="JavaScript:pagamento_carte_switch();">CONTANTI</td>
				<td width="250px" id="wrap_parziale_carta" '.($check1 === 'checked' ? '' : ' style="display:none"').'> + CARTA <input type="text" name="pagato_carte_di_credito" id="pagato_carte_di_credito" size="8" maxlength="8" disabled placeholder="0.00" oninput="pagato_carta_pos_toggle(this);"> <span id="wrap_pagato_con_pos" style="display:none"><button type="button" id="btn_pagato_con_pos" onclick="invia_pos_popup(this);" data-pos-base-url="'.htmlspecialchars($pos_base_url).'" data-pos-url="" data-pos-amount="" title="Invia importo parziale al POS">POS</button></span></td>
			</tr>

			<tr align="left">
				<td align="left"><input type="radio" name="tipo_corrispettivo" '.$check3.' value="T3" class="radio" onclick="JavaScript:pagamento_carte_switch();">CARTE</td>	
				<td width="250px" id="wrap_link_pos" '.($check3 === 'checked' ? '' : ' style="display:none"').'><button type="button" id="link_invia_pos" onclick="invia_pos_popup(this);" data-pos-url="'.htmlspecialchars($pos_url).'" data-pos-amount="'.htmlspecialchars(sprintf('%0.2f', $totale_pos)).'" title="Invia importo al terminale POS">Invia a POS ('.sprintf('%0.2f', $totale_pos).' &euro;)</button></td>
				
			</tr>

			<tr align="left">
				<td align="left"><input type="radio" name="tipo_corrispettivo" '.$check2.' value="T2" class="radio" onclick="JavaScript:pagamento_carte_switch();">ASSEGNI</td>
				<td width="250px"></td>
				
			</tr>
			<tr align="left">
				<td align="left"><input type="radio" name="tipo_corrispettivo" '.$check4.' value="T4" class="radio" onclick="JavaScript:pagamento_carte_switch();">NON-PAGATO</td>
				<td width="250px"></td>
			</tr>';
	//PAGAMENTO con altri metodi per il momento non utilizzato
	/*
	$output .= '
			<tr><td>IMPORTO <input type="text" name="pagato_altri_metodi" size="8" maxlength="8"></td>
				<td><input type="radio" name="tipo_corrispettivo" '.$check5.' value="T5" class="radio">ALTRI-METODI</td>

			</tr>';
	*/
	$output .= '
		</table>
	</FIELDSET>
	<br><br>
	';
	}
	$output .= '

	<FIELDSET>
	<LEGEND><B>Tipo conto</B></LEGEND>
	';
	// RTR START working
	if (!access_allowed(USER_BIT_WAITER) OR access_allowed(USER_BIT_CONFIG)) {
	$query="SELECT * FROM `#prefix#accounting_dbs`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	}
	//non permette ai camerieri di chiudere il tavolo con un database senza stampa
	else {
	$query="SELECT * FROM `#prefix#accounting_dbs` WHERE `print_bill`='1'";
	$res=common_query($query,__FILE__,__LINE__);
	}
	if(!$res) return '';
	//RTR END

	// MHR original
	//$query="SELECT * FROM `#prefix#accounting_dbs`";
	//$res=common_query($query,__FILE__,__LINE__);
	//if(!$res) return '';

	while($arr=mysql_fetch_array($res)) {
		if(mysql_list_tables($arr['db'])) {
			$checked='';
			if($account==$arr['db'])
				$checked = "checked";
			$output .= '<input type="radio" name="account" value="'.$arr['db'].'" '.$checked.' class="radio"> '.$arr['name'].' '."\n";
		}
	}

	$output .= '
	</FIELDSET>
	</form>
	</FIELDSET>
	';


	return $output;
}

function bill_type_set($type){
	if(empty($type)) {
		$type=1;
	}
	$_SESSION['type']=$type;
	return 0;
}

function bill_total(){
	$output = '';
	$total = 0;
	$class = COLOR_TABLE_TOTAL;

	//Legga nel database della tabella sources il valore scontato del tavolo
	//$discount=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources','discount',$_SESSION['sourceid']);

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}
	$output .= '
		<tr bgcolor="'.$class.'">
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right"><b>TOTALE</b></td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right"><b>'.sprintf("%0.2f",$total).'</b></td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'"></td>
		</tr>
		';

	if(!isset($_SESSION['discount']) or !isset($_SESSION['discount']['type']) or empty($_SESSION['discount']['type'])) return $output;

	if($_SESSION['discount']['type']=="amount") {
		$total_discounted=$total+$_SESSION['discount']['amount'];
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	} elseif($_SESSION['discount']['type']=="percent") {
		$total_discounted=$total-$total/100*$_SESSION['discount']['percent'];
		$discount_label=$_SESSION['discount']['percent'].' %';
		$discount_number=$total/100*$_SESSION['discount']['percent'];
	}

	$output .= '
		<tr bgcolor="'.$class.'">
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right">Sconto '.$discount_label.'</td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right">-'.sprintf("%0.2f",$discount_number).'</td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'"></td>
		</tr>
		<tr bgcolor="'.$class.'">
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right"><b>TOTALE SCONTATO</b></td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'" align="right"><b><u>'.sprintf("%0.2f",$total_discounted).'</u></b></td>
		<td bgcolor="'.$class.'"></td>
		<td bgcolor="'.$class.'"></td>
		</tr>
		';
	return $output;
}

function bill_total_table(){
	$total = 0;

	//Legga nel database sella tabella sources il valore scontato del tavolo
	//$discount=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources','discount',$_SESSION['sourceid']);

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}

	return $total;
}

function bill_total_controllo_totale_negativo(){
	$total = 0;

	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}


	if(!isset($_SESSION['discount']) or !isset($_SESSION['discount']['type']) or empty($_SESSION['discount']['type'])) return $output;

	if($_SESSION['discount']['type']=="amount") {
		$total_discounted=$total+$_SESSION['discount']['amount'];
		$discount_label="";
		$discount_number=-$_SESSION['discount']['amount'];
	} elseif($_SESSION['discount']['type']=="percent") {
		$total_discounted=$total-$total/100*$_SESSION['discount']['percent'];
	}

	if ($total_discounted<0) return 1;
}

function bill_reset_confirm() {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=printing_choose');
	$tpl -> assign ('navbar',$tmp);

	$tmp = '<h3>'.ucfirst(phr('RESET_SEPARATED_EXPLAIN')).'</h3>'.'
		<br>
		<br><br>

	<FORM ACTION="orders.php" METHOD=POST name="form1">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="bill_reset">
	<h1><input type="checkbox" name="reset" value="3" class="radio">
	'.ucfirst(phr('RESET_SEPARATED')).'</h1><br><br>
	</FORM>
	';
	$tpl -> assign ('question',$tmp);
	return 0;
}

function bill_reset($sourceid) {
	$query= "UPDATE `#prefix#orders` SET `paid` = '0' WHERE `sourceid` = '$sourceid'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return mysql_errno();

	return 0;
}
// calcola il totale a persona
function totale_a_persona($sourceid) {
	
	$sourceid = $_SESSION['sourceid'];
	$separated=$_SESSION['separated'];
	
	for (reset ($_SESSION['separated']); list ($key, $value) = each ($_SESSION['separated']); ) {
		$total+=$_SESSION['separated'][$key]['finalprice'];
	}
	
	if($_SESSION['discount']['type']=="amount") {
		$total_discounted=$total+$_SESSION['discount']['amount'];
		$totale_tavolo = $total_discounted;
	} elseif($_SESSION['discount']['type']=="percent") {
		$total_discounted=$total-$total/100*$_SESSION['discount']['percent'];
		$totale_tavolo = $total_discounted;
	} else {$totale_tavolo = $total;
	}

	$query = "
	SELECT SUM(quantity) as numero_coperti  FROM `#prefix#orders` 
	WHERE `dishid`='".SERVICE_ID."'
	AND  `sourceid` = '$sourceid'
	";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	if($arr = mysql_fetch_array ($res)) $totale_coperti = $arr['numero_coperti'];
	if(!$totale_coperti) $totale_coperti = 0;
	
	$totale_a_persona = $totale_tavolo/$totale_coperti;
	
	//if (!$_SESSION['separated']){
	$output .= ' <tr bgcolor="white"><td colspan="6">Costo a persona Euro '.sprintf("%0.2f",$totale_a_persona).'</td></tr>';
	return $output;
	//}
}
?>
