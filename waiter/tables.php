<?php
$inizio=microtime();			//has to be before start.php requirement!!!
session_start();

define('ROOTDIR','..');
$dont_get_session_sourceid=true;
$dont_redirect_to_menu=true;
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

$GLOBALS['end_require_time']=microtime();

unset_source_vars();
unset ($tmp);
$time_refresh=1000*get_conf(__FILE__,__LINE__,'refresh_automatic_on_menu');
$time_for_countdown=$time_refresh/1000-1;
$target='tables.php?rndm='.rand(0,100000);
if($time_refresh) $tmp = redirect_timed($target,$time_refresh);
$tpl -> append ('scripts',$tmp);

$tpl -> set_waiter_template_file ('tables');

$user = new user($_SESSION['userid']);
if(!access_allowed(USER_BIT_WAITER) && !access_allowed(USER_BIT_CASHIER)) {
	access_denied_waiter();
};

if($user->level[USER_BIT_CASHIER])
	//tables_list_all(5,1) primo numero all'interno delle parentesi indica il numero di colonne
	$tpl -> append ('tables',tables_list_all(5,1));
if($user->level[USER_BIT_WAITER])
	$tpl -> append ('tables',tables_list_all(1,2));

if($user->level[USER_BIT_CASHIER]) $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_cashier');
else $cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_waiter');

$tpl -> append ('tables',tables_list_all($cols,0,false));
$tpl -> append ('barra_booking',barra_booking());
$tpl -> append ('barra_apri_chiudi_coperti',apri_chiudi_coperti());
$tpl -> append ('prenotazioni',prenotazioni());
$tpl -> append ('giorno',date("d.m.y"));
$tpl -> append ('sono_le_ore',date("H:i"));
$tpl -> append ('countdown',"<div id=\"timer\"><script>countdown($time_for_countdown)</script></div>");



if(!$user->level[USER_BIT_CASHIER])
	$tpl -> append ('tables',tables_list_all(1,1));

if($user->level[USER_BIT_CASHIER]) {
	$cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_cashier');
	$tpl -> append ('tables',tables_list_all($cols,3));
	}
if($user->level[USER_BIT_WAITER]) {
	$cols=get_conf(__FILE__,__LINE__,'menu_tables_per_row_waiter');
	$tpl -> append ('tables',tables_list_all($cols,3));
	}

//Riepilogo Totale Tavoli aperti
if (access_allowed(USER_BIT_CONFIG))
	$tpl -> append ('riepilogo',riepilogo_totali_tavoli());
// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

// html closing stuff and disconnect line
$tmp = disconnect_line();
$tpl -> assign ('logout',$tmp);



if($err=$tpl->parse()) return $err;

$tpl -> clean();
$output = $tpl->getOutput();

//stampa tutto a video
//echo 'cache:<br>'.$GLOBALS['cache_var']->show();
//$tpl ->list_vars();

// prints everything to screen
echo $output;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
