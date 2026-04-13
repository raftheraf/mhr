<?php
// if(function_exists('apd_set_pprof_trace')) apd_set_pprof_trace();

// has to be before start.php to be precise timer
$inizio=microtime();
session_start();

define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

$GLOBALS['end_require_time']=microtime();

if(!access_allowed(USER_BIT_WAITER) and !access_allowed(USER_BIT_CASHIER)) {
	$command='access_denied';
};

// if no command has been given, tries to infer one
// Usa un valore di default sicuro se sourceid non è impostato
if (empty($command) || $command=="none") {
	$sourceid = isset($_SESSION['sourceid']) ? $_SESSION['sourceid'] : 0;
	$command = table_suggest_command($sourceid);
}

$sourceid = isset($_SESSION['sourceid']) ? $_SESSION['sourceid'] : 0;
$table = new table($sourceid);
if (!$table -> exists() and $command!='access_denied') {
	$tmp = 'table doesn\'t exist.<br>'."\n";
	$tpl -> append ('messages',$tmp);

	$tmp = navbar_menu();
	$tpl -> assign ('navbar',$tmp);
	$tpl -> assign ('vertical_navbar',$tmp);


	$command = 'none';
}

$tpl -> set_waiter_template_file ('orders');

// Normalizza i dati di input una sola volta per tutti i comandi
$start_data = (isset($_REQUEST['data']) && is_array($_REQUEST['data'])) ? $_REQUEST['data'] : array();

// command selection
switch ($command){
	case 'access_denied':
				access_denied_waiter();
				break;
	case 'create':
				$list = array();
				$dishid = trim($_REQUEST['dishid']);

				if(!empty($start_data['quantita_moltiplicata'])) {
					$start_data['quantity'] = $start_data['quantita_moltiplicata'];
				}

				if ($dishid==SERVICE_ID && isset($start_data['quantity']) && $start_data['quantity']==="Zero") {
					orders_list();
				 	break;
				}

				if ($dishid==SERVICE_ID && orders_service_fee_exists()) {
					orders_list();
					break;
				}

				if(empty($start_data['quantity'])) $start_data['quantity']=get_conf(__FILE__,__LINE__,"default_quantity");

				if((!isset($start_data['priority']) || !$start_data['priority']) and $dishid != SERVICE_ID) {
					$tmp = '<b><font color="Red">'.ucfirst(phr('INSERT_PRIORITY'))."</font></b><br>\n";
					$tpl -> append ('messages',$tmp);

					$tmp = navbar_empty('javascript:history.go(-1);');
					$tpl -> assign ('navbar',$tmp);
					break;
				}

				// Regola priorità se la categoria è già stata lanciata (catprinted)
				if ($dishid != SERVICE_ID && isset($start_data['priority'])) {
					$priority_sel = (int)$start_data['priority'];
					if ($priority_sel > 1 &&
						isset($_SESSION['catprinted'][$priority_sel]) &&
						$_SESSION['catprinted'][$priority_sel]
					) {
						// Se NON è un menu fisso: degrada a priorità 1 con warning
						if (!controlla_menu_fisso($dishid)) {
							$tmp = '<script>showPriorityWarning('.json_encode('La priorità '.$priority_sel.' è già stata lanciata per questo tavolo. Il piatto verrà inserito con priorità 1.').');</script>'."
";
							$tpl -> append('messages', $tmp);
							$start_data['priority'] = 1;
						}
					}
				}

				// Se è un menu fisso, controlla TUTTE le priorità dei piatti collegati:
				// se almeno una è già stata lanciata, blocca l'intero inserimento (menu + piatto principale).
				if ($dishid != SERVICE_ID && controlla_menu_fisso($dishid)) {
					$query = "SELECT dishesmenufisso FROM mhr_dishes WHERE id='".$dishid."'";
					$res = common_query($query,__FILE__,__LINE__);
					if(!$res) return ERR_MYSQL;
					$arr = mysql_fetch_array($res);
					$mf = isset($arr['dishesmenufisso']) ? $arr['dishesmenufisso'] : '';
					$parts = explode(" ", trim($mf));
					$count = count($parts);
					$mf_blocked = false;
					if ($count > 1) {
						for ($i = 0; $i+1 < $count; $i += 2) {
							$prio_mf = (int)$parts[$i+1];
							if ($prio_mf > 1 &&
								isset($_SESSION['catprinted'][$prio_mf]) &&
								$_SESSION['catprinted'][$prio_mf]
							) {
								$tmp = '<script>showPriorityWarning('.json_encode('La priorità '.$prio_mf.' di un piatto del menu fisso è già stata lanciata per questo tavolo. Impossibile inserire questo menu fisso.').');</script>'."
";
								$tpl -> append('messages', $tmp);
								$mf_blocked = true;
								break;
							}
						}
					}
					if ($mf_blocked) {
						orders_list();
						break;
					}
				}

				// autosearch
				// the user provided a text instead of a number,
				// we look for dish
				if($dishid!='' and !is_numeric($dishid)) {
					$dish=new dish;
					$num=$dish->search_name_rows($dishid);
					// -1 means found many, go to dish list
					if($num == -1) {
						$list['quantity']=$start_data['quantity'];
						$list['priority']=$start_data['priority'];
						$list['search']=$dishid;
						dish_list($list);
						break;
					} elseif ($num == 0) {
					// found none
						$tmp = '<b><font color="Red">NESSUN RECORD TROVATO</font></b><br>';
						$tpl -> append ('messages',$tmp);

						orders_list ();
						break;
					} else {
					// found one, we directly assume that's the dish the user wanted
						$dishid=$num;
					}
				}

				$dish=new dish($dishid);
				if(!$dish->exists()) {
					$tmp = '<b><font color="Red">IL PIATTO NON ESISTE</font></b><br>';
					$tpl -> append ('messages',$tmp);

					orders_list ();
					break;
				}

				// Impedisci l'inserimento di più conti alla romana sullo stesso tavolo
				if ($dishid == ROMANA_QUOTA_ID && isset($_SESSION['sourceid'])) {
					$sourceid = mysql_real_escape_string($_SESSION['sourceid']);
					$query = "
						SELECT id FROM `#prefix#orders`
						WHERE `sourceid` = '".$sourceid."'
						  AND `deleted` = 0
						  AND `dishid` = '".ROMANA_QUOTA_ID."'
						LIMIT 1
					";
					$res = common_query($query,__FILE__,__LINE__);
					if ($res && mysql_num_rows($res) > 0) {
						$tmp = '<b><font color="Red">È già presente un conto alla romana per questo tavolo.</font></b><br>';
						$tpl -> append('messages', $tmp);
						orders_list();
						break;
					}
				}

				if($dishid) {
					$id = orders_create ($dishid, $start_data);
				}

				// Se è stata inserita una quota "Conto alla romana"
				// e l'ordine è stato creato correttamente:
				// - registra l'incasso di tutti i piatti del tavolo (esclusa la quota)
				//   in un documento tecnico separato, e marca quei piatti come pagati;
				// - azzera eventuali sconti di sessione (lo sconto non deve più essere attivo).
				if ($dishid == ROMANA_QUOTA_ID && isset($_SESSION['sourceid']) && $id) {
					logga_incasso_piatti_romana_e_chiudi_ordini($_SESSION['sourceid']);
					if (isset($_SESSION['discount'])) {
						unset($_SESSION['discount']);
					}
				}

// RTR START NOW
// Funzione Genera Menu Fisso
// aggiungere 2 campi nella tabella dishes: menufisso e dishes_menufisso
// _______________________________________________________________________
				if(controlla_menu_fisso($dishid))
				{
						$query=" SELECT dishesmenufisso FROM mhr_dishes WHERE id='".$dishid."' ";
						$res=common_query($query,__FILE__,__LINE__);
						if(!$res) return ERR_MYSQL;
						$arr = mysql_fetch_array($res);

						$arr = $arr['dishesmenufisso'];
						$arr = explode ( " ", $arr );

						$conteggio= count($arr);
						if ($conteggio > 1)
						{
						$i=0;
							while ($i < $conteggio)
							{
								$dishid=$arr[$i];
								$start_data['priority']=$arr[$i+1];

								$id = orders_create ($dishid,$start_data);

								$i=$i+2;
							}
						}
				}
// ______________________________________________________________________
// RTR END

				if($id) $err=0;
				else $err=ERR_UNKNOWN;

				status_report ('CREATION',$err);

				// Inizializza il flag per evitare Notice su variabile non definita
				$back_to_cat = false;
				if(isset($_REQUEST['from_category']) and $_REQUEST['from_category']) {
					if (isset($_REQUEST['back_to_cat']) and $_REQUEST['back_to_cat']) $back_to_cat = true;
				}
				if($back_to_cat) {
					$_SESSION['go_back_to_cat'] = 1;
					$dish = new dish ($dishid);
					$list['category'] = $dish -> data['category'];
					$list['priority'] = $start_data['priority'];

					dish_list($list);
				} else {
					$_SESSION['go_back_to_cat'] = 0;
					orders_list ();
				}
				break;
	case 'edit':
				orders_edit ($start_data);
				break;
	case 'update':
				if ($start_data['quantity']==0) {
					$err = orders_delete ($start_data);

					status_report ('DELETION',$err);

					orders_list ();
					break;
				}
				$err = orders_update ($start_data);
				status_report ('UPDATE',$err);

				$last_mod = order_get_last_modified();
				if($last_mod and isset($_SESSION['go_back_to_cat']) and $_SESSION['go_back_to_cat']) {
					$ord = new order ((int) $last_mod);

					$dish = new dish ($ord ->  data['dishid']);
					$list['category'] = $dish -> data['category'];
					$list['priority'] = $ord -> data['priority'];

					dish_list($list);
				} else {
					orders_list ();
				}
				break;
	case 'price_modify':
				order_price_modify($start_data['id']);
				break;
	case 'dish_list':
		  	// set to zero so last modified order is not displaid
		  	$_SESSION['go_back_to_cat'] = 1;
			  dish_list($start_data);
			  break;
	case 'set_show_orders':
				if (!isset($_SESSION['show_orders_list'])) $_SESSION['show_orders_list']= false;
				else $_SESSION['show_orders_list'] = !$_SESSION['show_orders_list'];
				orders_list();
				break;
	case 'set_show_toplist':
				if (!isset($_SESSION['show_toplist'])) $_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
				else $_SESSION['show_toplist'] = !$_SESSION['show_toplist'];
				orders_list();
				break;
	case 'ask_delete':
				orders_ask_delete ($start_data);
				break;
	case 'delete':
				if(isset($start_data['silent'])) {
					$silent=$start_data['silent'];
					unset($start_data['silent']);
				} else $silent=false;

				$err = orders_delete ($start_data);

				if(!$silent) {
					status_report ('DELETION',$err);
				}

				orders_list ();
				break;
	case 'ask_substitute':
				orders_ask_substitute ($start_data);
				break;
	case 'substitute':
				$saved_data = orders_get_data ($start_data);

				$err = orders_delete ($start_data);

				status_report ('DELETION',$err);

				$start_data['quantity'] = $saved_data['quantity'];
				$start_data['priority'] = $saved_data['priority'];
				dish_list($start_data);
				break;
	case 'listmods':
				if(isset($_REQUEST['letter'])) $letter=$_REQUEST['letter']{0};
				else $letter='';

				if(!isset($_SESSION['go_back_to_cat'])) $_SESSION['go_back_to_cat']=0;

				mods_list ($start_data,$letter);
				break;
	case 'mod_set':
				$err = mods_set ($start_data);
				status_report ('MODS_SETTING',$err);

				if($_REQUEST['last']) {
					$last_mod = order_get_last_modified();
					if($last_mod and isset($_SESSION['go_back_to_cat']) and $_SESSION['go_back_to_cat']) {
						$ord = new order ((int) $last_mod);

						$dish = new dish ($ord ->  data['dishid']);
						$list['category'] = $dish -> data['category'];
						$list['priority'] = $ord -> data['priority'];

						dish_list($list);
					} else {
						orders_list ();
					}
				}
				else {
					if(isset($_REQUEST['letter']) and $_REQUEST['letter']=='ALL') $letter='ALL';
					elseif(isset($_REQUEST['letter'])) $letter=$_REQUEST['letter']{0};
					else $letter='';

					mods_list ($start_data,$letter);
				}
				break;
	case 'list':
				orders_list ();
				break;
	case 'ask_move':
				// verifica se il tavolo è unito ma la funzione verifica_se_tavolo_unito è da sistemare non funziona
				/*if (verifica_se_tavolo_unito($_SESSION['sourceid'])==TRUE){
					orders_list ();
					status_report ('Impossibile spostare un tavolo unito',$err);
				break;
				}
				*/
				$tpl -> set_waiter_template_file ('tables');

				$tmp = navbar_empty('javascript:history.go(-1);');
				$tpl -> assign('navbar',$tmp);

				$user = new user($_SESSION['userid']);
				if($user->level[USER_BIT_CASHIER]) $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_cashier');
				else $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_waiter');

				$table = new table($_SESSION['sourceid']);
				$table -> move_list_tables($cols);
				break;
	case 'move':
				$newtable = $start_data['id'];

				if (!$newtable) {
					orders_list ();
					break;
				}

				$table = new table($_SESSION['sourceid']);
				$err = $table -> move($newtable);

				status_report('MOVEMENT',$err);

				if (!$err) $_SESSION['sourceid'] = $newtable;
				orders_list ();

				break;
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
	case 'service_fee':
				orders_service_fee_questions ();
				break;
	case 'ask_association':
				table_ask_association ();
				break;
	case 'associate':
				$err = table_associate ();
				status_report ('ASSOCIATION',$err);

				if(get_conf(__FILE__,__LINE__,"service_fee_use")) orders_service_fee_questions ();
				else orders_list ();

				break;
	case 'dissociate':
				$err = table_dissociate ();
				status_report ('DISSOCIATION',$err);

				if(!$err) {
					$redirect = redirect_waiter('tables.php');
					$tpl -> append ('scripts',$redirect);
				}

				orders_list ();
				break;
	case 'set_customer':
				//la condizione sotto impedisce di eliminare il nome cliente nei tavoli asporto
				/*if(table_is_takeaway($_SESSION['sourceid'])) {
					$err = takeaway_set_customer_data ($_SESSION['sourceid'],$start_data);
					status_report ('TAKEAWAY_DATA',$err);
				} else {*/
					$err = table_set_customer ($_SESSION['sourceid'],$start_data);
					status_report ('CUSTOMER',$err);
				//}

				if (isset($_SESSION['select_all'])) {
					$err=bill_select();
					if($err) error_display($err);
				} else orders_list();

				break;
	case 'customer_insert_form':
				customer_insert_page();
				break;
	case 'customer_edit_form':
				customer_edit_page($start_data);
				break;
	case 'customer_search':
				customer_search_page($start_data);
				break;
	case 'customer_list':
				customer_search_page();
				break;
	case 'customer_insert':
				$err=customer_insert($start_data);
				status_report ('INSERT',$err);

				customer_search_page();
				break;
	case 'customer_edit':
				$err = customer_edit($start_data);
				status_report ('UPDATE',$err);

				customer_search_page();
				break;
	case 'bill_select':
				$_SESSION['select_all']=0;
				unset($_SESSION['discount']);
				unset($_SESSION['separated']);
				$err=bill_select();
				if($err) error_display($err);
				break;
	case 'bill_select_all':
				$_SESSION['select_all']=1;
				$err = bill_select();
				if($err) error_display($err);
				break;
	case 'bill_discount':
				if(isset($_REQUEST['discount_type'])) {
					$discount_type=$_REQUEST['discount_type'];
					$err = apply_discount($discount_type);
				} else $err=1;

				status_report ('DISCOUNT',$err);

				$err = bill_select();
				if($err) error_display($err);
				break;
	case 'bill_quantity':
				$_SESSION['select_all']=0;

				if(isset($_REQUEST['orderid']))$orderid=$_REQUEST['orderid'];
				else $orderid=0;

				if(isset($_REQUEST['operation'])) $operation=$_REQUEST['operation'];
				else $operation=0;

				$err = 0;
				if($orderid==0 || $operation==0) {
					$err = 1;
				} else {
					$err = bill_quantity($orderid,$operation);
				}

				status_report ('QUANTITY_UPDATE',$err);
				$err= bill_select();

				//controlla che il totale del tavolo con lo sconto non vada in negativo
				if (bill_total_controllo_totale_negativo()){
					unset($_SESSION['discount']);
					$err= bill_select();
					$err='Totale Negativo';
				}

				if($err) error_display($err);
				break;

	case 'bill_print':
				// Inizializza i parametri di stampa conto con valori di default
				$codice_lotteria = '';
				$type = '';
				$account = '';
				$tipo_corrispettivo = '';
				$pagato_altri_metodi = 0;
				$pagato_carte_di_credito = 0;

				if(isset($_REQUEST['codice_lotteria'])) $codice_lotteria=$_REQUEST['codice_lotteria'];
				//Codice lotteria scontrini tutte le lettere maiuscole
				$codice_lotteria=strtoupper($codice_lotteria);
				if(isset($_REQUEST['type'])) $type=$_REQUEST['type'];
				if(isset($_REQUEST['account'])) $account=$_REQUEST['account'];
				if(isset($_REQUEST['tipo_corrispettivo'])) $tipo_corrispettivo=$_REQUEST['tipo_corrispettivo'];
				if(isset($_REQUEST['pagato_altri_metodi'])) $pagato_altri_metodi=$_REQUEST['pagato_altri_metodi'];
				if(isset($_REQUEST['pagato_carte_di_credito'])) $pagato_carte_di_credito=$_REQUEST['pagato_carte_di_credito'];
				$_SESSION['codice_lotteria']=$codice_lotteria;
				$_SESSION['tipo_corrispettivo']=$tipo_corrispettivo;
				$_SESSION['pagato_altri_metodi']=$pagato_altri_metodi;
				$_SESSION['pagato_carte_di_credito']=$pagato_carte_di_credito;

				//verifiche per il codice lotteria scontrini
				if ( ($codice_lotteria!="") AND (verifica_codice_lotteria($codice_lotteria)=="sbagliato") )
					{
						error_display("Codice lotteria non valido");
						orders_list();
						break;
					}


				//verifica se sono presenti articoli associati al tavolo ($_SESSION['sourceid']) per evitare di stampare 2 volte la stessa ricevuta
				if(!bill_orders_to_print($_SESSION['sourceid'])) {
					error_display(405);
					orders_list();
				//end verifica
				} else {
					//se non è stata selezionata una stampante bill_type_set imposta a $type=1 così stampa un preconto e non fa casino
					if(!bill_type_set($type) and !bill_account_set($account)) {

					$err = bill_print();

					status_report ('BILL_PRINT',$err);
					if(!$err) {
						// this allows bill_select to forget precedent selection
						$_REQUEST['keep_separated']=0;
					}
				}
				bill_select();
				}
				break;

	case 'bill_reset':
				if(isset($_REQUEST['reset'])){
					$err=bill_reset($_SESSION['sourceid']);
					status_report ('BILL_RESET',$err);

					if($err) {
						printing_choose();
					} else {
						orders_list ();
					}
				} else {
					bill_reset_confirm();
				}
				break;
	case 'print_orders':
				$err=print_orders($_SESSION['sourceid']);
				status_report ('ORDERS_PRINT',$err);
				if (!$err) {
					orders_list ();
				} else {
					printing_choose();
				}
				break;
	//Ristampa comanda
	case 'ristampa_comanda':
				$err=rispampa_ordini($_SESSION['sourceid']);
				status_report ('ORDERS_PRINT',$err);
				if (!$err) {
					orders_list ();
				} else {
					printing_choose();
				}
				break;
	//fine

	case 'print_category':
				$category=(int) $start_data['category'];
				$err=print_category($category);
				status_report ('CATEGORY_PRINT',$err);
				if (!$err) {
					orders_list ();
				} else {
					printing_choose();
				}

				break;
	case 'printing_choose':
				printing_choose();
				break;
	case 'reopen_confirm';
				table_reopen_confirm ();
				break;
	case 'reopen':
				$err = table_reopen($_SESSION['sourceid']);
				status_report ('REOPEN',$err);
				unset_session_vars();
				orders_list ();
				break;
	case 'close_confirm':
				table_ask_close();
				break;
	case 'close':
				$err = table_close($_SESSION['sourceid']);
				status_report ('CLOSE',$err);
				if (!$err) {
					table_closed_interface();
				} else {
					orders_list ();
				}

				break;
	case 'closed':
				table_closed_interface();
				break;
	case 'pay':
				$err = table_pay($start_data['paid']);
				status_report ('PAYMENT',$err);

				table_closed_interface();
				break;
	case 'clear':
				$err = table_clear();
				status_report ('CLEARING',$err);
				if (!$err) {
					table_cleared_interface();
				} else {
					table_closed_interface();
				}
				break;
	case 'unisci':
				$err = 0;
				if (presenza_ordini($_SESSION['sourceid'])==TRUE){
					orders_list ();
					status_report ('Impossibile unire il tavolo sono presenti ordini',$err);
				} else {
				
				$query = " UPDATE `#prefix#sources` SET `unito`='1' WHERE `id`='".$_SESSION['sourceid']."' ";
				$res=common_query($query,__FILE__,__LINE__);
				if(!$res) return ERR_MYSQL;
				
				$err = table_close($_SESSION['sourceid']);
				status_report ('Tavolo unito',$err);
				if (!$err) {
					table_closed_interface();
					status_report ('Tavolo unito',$err);
					$redirect = redirect_waiter('tables.php');
					$tpl -> append ('scripts',$redirect);
				} else {
					orders_list ();
				}
				}
				
				break;
	case 'togli_diavoletto':
				$query = "UPDATE `#prefix#sources` SET `catprinted_time`=NOW() WHERE `id`='".$_SESSION['sourceid']."' ";
				$res = common_query($query,__FILE__,__LINE__);
				orders_list();
				break;
	case 'none':
				break;

	default:
				orders_list ();
				break;
}
// this line is already in waiter_start, but it's here repeated because of possible modifications from waiter start till now
$sourceid_footer = isset($_SESSION['sourceid']) ? $_SESSION['sourceid'] : 0;
$tmp = table_people_number_line ($sourceid_footer);
$tpl -> assign("people_number", $tmp);

// html closing stuff and disconnect line
$tmp = disconnect_line();
$tpl -> assign ('logout',$tmp);

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err;

$tpl -> clean();
echo $tpl->getOutput();

//stampa tutto a video
//echo 'cache:<br>'.$GLOBALS['cache_var']->show();
//$tpl ->list_vars();

if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
