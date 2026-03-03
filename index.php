<?php
$inizio=microtime();
$useridnotsetisok=1;			//has to be before start.php requirement!!!
define('ROOTDIR','.');

session_start();

require_once(ROOTDIR."/includes.php");

require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

common_set_error_reporting ();

if(isset($_SESSION['section']) && $_SESSION['section']!="admin"){
	unset_session_vars();
	$_SESSION['section']="admin";
}

if(!$link = @mysql_pconnect ($cfgserver, $cfguser,$cfgpassword)) {
	header('Location: '.ROOTDIR.'/install.php');
	die ('Error connecting to the db');
}

$_SESSION['common_db']=$db_common;

check_db_status(true);

start_language ();

$dbman = new db_manager ('', '', '', $link);
/* CANCELLATA LA FUNZIONE upgrade_available
if(!in_array(basename($_SERVER['SCRIPT_NAME']),$allowed_not_upgraded) && $dbman->upgrade_available()) {
	$url=ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1';
	header('Location: '.$url);
	echo redirectJS($url);
	echo 'Upgrades available.';
	die();
}
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- /index.php -->
<html>
<head>
<link rel="shortcut icon" href="./favicon.ico" />
<meta name="viewport" content = "width = device-width, initial-scale = 1.0, minimum-scale = 1, maximum-scale = 1, user-scalable = no" />
<script type="text/javascript" language="JavaScript" src="./generic.js"></script>

<script type="text/javascript" language="JavaScript" src="./javascript/ajax-script.js"></script>
<script type="text/javascript" language="JavaScript" src="./javascript/jquery-1.3.2.min.js"></script>

<link rel="stylesheet" href="reset.css" type="text/css">
<link rel="stylesheet" href="./styles.css">
<title>RTR Restaurant</title>
</head>
<body class=mgmt_body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<center>
<div class="aligncenter">
<img src="./images/mhr_logo.jpg" alt="RTR Restaurant">
<br><br><br>
<a href="waiter/"><b>SEZIONE DEI CAMERIERI</b>
<br> Gestione tavoli e ordini </a>
<br><br><br><br>
<br>
<a href="admin/connect.php?command=none"><b>SEZIONE AMMINISTRATIVA</b><br> Menu, utenti, magazzino </a>
<br><br><br><br><br><br>

<i> ..:: RTR optimized ::.. </i>
</div>
</center>
<?php
	if (CONF_DEBUG) {
		$servrd=$_SERVER['HTTP_USER_AGENT'];
		display_todo($servrd);
	}
?>
</body>
</html>
