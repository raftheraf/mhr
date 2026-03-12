<?php

$inizio=microtime();

define('ROOTDIR','..');
require(ROOTDIR."/manage/mgmt_funs.php");
require(ROOTDIR."/manage/mgmt_start.php");

$command = isset($_REQUEST['command']) ? $_REQUEST['command'] : 'default';
// Allinea i permessi a quelli della sezione Contabilità:
// solo utenti con diritto ACCOUNTING possono vedere la pagina "Sospesi".
if(!access_allowed(USER_BIT_ACCOUNTING)) $command='access_denied';

$orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'date';

switch($command) {
	case 'access_denied':
		echo access_denied_admin();
		break;

	default:

		main_header('sospesi.php');

		echo "<div align='center'><h1>Pagina dei conti in  SOSPESO</h1></div>";

		table_general($orderby,"show_all",7);

		echo "<div align='center'><h1>Pagina dei conti in  SOSPESO</h1></div>";
		echo "<br><br><br><br>";
		//echo "<br><a href=\"#\" onclick=\"javascript:history.go(-1); return false\">".ucfirst(phr('GO_BACK'))."</a><br>\n";
		break;
}

echo "<br><a href=\"index.php\">".ucfirst(phr('GO_MAIN_REPORT'))."</a><br>";

echo generating_time($inizio);

?>
