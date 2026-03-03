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
$target='booking.php?rndm='.rand(0,100000);
if($time_refresh) $tmp = redirect_timed($target,$time_refresh);
$tpl -> append ('scripts',$tmp);

$tpl -> set_waiter_template_file ('booking');

$user = new user($_SESSION['userid']);
if(!access_allowed(USER_BIT_WAITER) && !access_allowed(USER_BIT_CASHIER)) {
	access_denied_waiter();
};

//Lista prenotazioni
	$tpl -> append ('prenotazioni',prenotazioni());
	$tpl -> append ('barra_booking',barra_booking());
	$tpl -> append ('barra_apri_chiudi_coperti',apri_chiudi_coperti());

// html closing stuff and disconnect line
$tmp = disconnect_line();
$tpl -> assign ('logout',$tmp);
if($err=$tpl->parse()) return $err;

$tpl -> clean();
$output = $tpl->getOutput();

//stampa tutto a video
//echo 'cache:<br>'.$GLOBALS['cache_var']->show();
//$tpl ->list_vars();

echo $output;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
