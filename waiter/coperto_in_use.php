<?php
// RTR  attiva disattiva coperti
//funzione per attivare disattivare i coperti
$inizio=microtime();
$dont_display_menu=true;
session_start();
define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require_once(ROOTDIR."/waiter/waiter_start.php");

$coperto=$_POST['coperto'];

if ($coperto==0){
$query = " UPDATE `#prefix#conf` SET `value`='0' WHERE name='service_fee_use' ";
}
if ($coperto==1){
$query = " UPDATE `#prefix#conf` SET `value`='1' WHERE name='service_fee_use' ";
}

$res=common_query($query,__FILE__,__LINE__);
if(!$res) return ERR_MYSQL;
$tmp = redirect(basename($_SERVER["HTTP_REFERER"]),0);

?>
