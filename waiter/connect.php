<?php
// if(function_exists('apd_set_pprof_trace')) apd_set_pprof_trace();

$inizio=microtime();
$dont_display_menu=true;
session_start();
define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");

// Se arriviamo qui dopo un logout esplicito o da \"Torna al login\",
// puliamo comunque la sessione per sicurezza, così l'utente NON risulta connesso.
if (!empty($_REQUEST['from_logout'])) {
	$_SESSION = array();
	session_unset();
}

require(ROOTDIR."/waiter/waiter_start_1.php");

$tpl -> set_waiter_template_file ('standard');

switch($command) {
	case 'disconnect':
		if (isset($_SESSION['userid']) && $_SESSION['userid']) {
			$user = new user ($_SESSION['userid']);
			$user->disconnect();
		}
		$tmp = access_connect_form_waiter();
		$tpl -> assign("content", $tmp);
		break;

	case 'connect':
		if (!isset($_SESSION['userid']) || !$_SESSION['userid']) {
			if (isset($_REQUEST['userid']) && $_REQUEST['userid']) {
				$_SESSION['userid'] = $_REQUEST['userid'];
			}
		}
		if (!isset($_SESSION['userid']) || !$_SESSION['userid']) {
			$tmp = access_connect_form_waiter();
			$tpl -> assign("content", $tmp);
			break;
		}
		$user = new user ($_SESSION['userid']);
		$err = $user -> connect ();
		if (!$err) {
			$tmp = '<H1>OK PASSWORD ACCETTATA</H1><br>';
			$tpl -> append("messages", $tmp);
			$tmp = 'Connettiti';
			$tpl -> assign("title", $tmp);
			// Redirect dopo login: attesa fissa di 2 secondi
			$tmp = redirect_timed('tables.php', 2000);
			$tpl -> append ('scripts',$tmp);

			if(isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {
				$tmp = redirect_timed($_REQUEST['url'],0);
				$tpl -> append("scripts", $tmp);
			}
		}
		else {
			// Password errata o vuota: mostra il messaggio e ripresenta il form di login,
			// senza fare redirect verso altre pagine che possono generare "Accesso negato".
			// Pulisce anche l'utente di sessione per evitare il messaggio "Sei già connesso".
			if (isset($_SESSION['userid'])) {
				unset($_SESSION['userid']);
			}
			if (isset($_SESSION['passworded'])) {
				unset($_SESSION['passworded']);
			}

			$tmp = 'ERRORE : '.error_get($err).'<br>';
			$tpl -> append("messages", $tmp);

			$tmp = access_connect_form_waiter();
			$tpl -> assign("content", $tmp);
		}
		break;

	default:

		//RTR start
		if(isset($_SESSION['userid']) && $_SESSION['userid'])
		{
		$tmp = '<H1>SEI GIA CONNESSO</H1><br>';
			$tpl -> append("messages", $tmp);
			$tmp = 'Connettiti';
			$tpl -> assign("title", $tmp);
			$tmp = redirect_waiter('tables.php');
			$tpl -> append ('scripts',$tmp);
		}
		//RTR end
		// Dopo logout o "Torna al login" da multi_tab_error: mostra sempre il form (no auto-login per IP)
		elseif (!empty($_REQUEST['from_logout'])) {
			$ip_button = '';
			if (allowed_user_host($_SERVER['REMOTE_ADDR'])) {
				$ip_button = '<div style="text-align:center; margin-bottom:18px;">'."\n"
					.'<a href="'.ROOTDIR.'/waiter/connect.php" style="'
					.'display:inline-block;'
					.'background:#2e7d32;'
					.'color:#ffffff;'
					.'font-size:32px;'
					.'font-weight:bold;'
					.'padding:18px 48px;'
					.'border-radius:12px;'
					.'border:3px solid #1b5e20;'
					.'box-shadow:0 4px 8px rgba(0,0,0,0.35);'
					.'text-decoration:none;'
					.'letter-spacing:1px;'
					.'">&#10003; Accedi con IP</a>'."\n"
					.'</div>'."\n";
			}
			$tmp = $ip_button . access_connect_form_waiter();
			$tpl -> assign("content", $tmp);
		}
		// Accesso normale all'area waiter (es. waiter/ o index): auto-login per IP se configurato
		elseif (allowed_user_host($_SERVER['REMOTE_ADDR'])) {
			$mysqli = new mysqli("$cfgserver", "$cfguser", "$cfgpassword", "$db_common");
			if (mysqli_connect_errno()) {
				echo "Errore in connessione al DBMS: ".mysqli_connect_error()."<br><br>";
				exit();
			}
			$query = "SELECT id FROM mhr_users WHERE user_host='".$_SERVER['REMOTE_ADDR']."' ";
			$result = $mysqli->query($query);
			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_array(MYSQLI_NUM);
				$userid = $row[0];
				$result->close();
				$mysqli->close();
				$_REQUEST['userid'] = $userid;
				$_SESSION['userid'] = $userid;
				$_SESSION['passworded'] = true;
				header('Location: '.ROOTDIR.'/waiter/tables.php?userid='.$userid);
				exit();
			}
			if ($result) $result->close();
			$mysqli->close();
			$tmp = access_connect_form_waiter();
			$tpl -> assign("content", $tmp);
		}
		else {
			$tmp = access_connect_form_waiter();
			$tpl -> assign("content", $tmp);
		}
		break;
}

$_SESSION['common_db']=$db_common;

$tmp = head_line('Connection');
$tpl -> assign("head", $tmp);
$tmp = 'Connettiti';
$tpl -> assign("title", $tmp);

$menu = new menu();
$tmp = $menu -> main ();
$tpl -> assign("menu", $tmp);
$tmp = 'Connettiti';
$tpl -> assign("title", $tmp);

// prints page generation time
$tmp = generating_time($inizio);
$tpl -> assign ('generating_time',$tmp);

if($err=$tpl->parse()) return $err;

$tpl -> clean();
$output = $tpl->getOutput();

header("Content-Language: ".$_SESSION['language']);
header("Content-type: text/html; charset=".phr('CHARSET'));

 //$tpl ->list_vars();

// prints everything to screen
echo $output;
if(CONF_DEBUG_PRINT_PAGE_SIZE) echo $tpl -> print_size();
?>
