<?php
// if(function_exists('apd_set_pprof_trace')) apd_set_pprof_trace();

$inizio=microtime();
$dont_display_menu=true;
session_start();
define('ROOTDIR','..');
require_once(ROOTDIR."/includes.php");
require(ROOTDIR."/waiter/waiter_start_1.php");

$tpl -> set_waiter_template_file ('standard');

switch($command) {
	case 'disconnect':
		$user = new user ($_SESSION['userid']);
		$user->disconnect();
		$tmp = access_connect_form_waiter();
		$tpl -> assign("content", $tmp);
		break;

	case 'connect':
		$user = new user ($_SESSION['userid']);
		$err = $user -> connect ();
		if (!$err) {
			$tmp = '<H1>OK PASSWORD ACCETTATA</H1><br>';
			$tpl -> append("messages", $tmp);
			$tmp = ucphr('CONNECT');
			$tpl -> assign("title", $tmp);
			$tmp = redirect_waiter('tables.php');
			$tpl -> append ('scripts',$tmp);

			if(isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {
				$tmp = redirect_timed($_REQUEST['url'],0);
				$tpl -> append("scripts", $tmp);
			}
		}
		else {
			$tmp = 'ERRORE : '.error_get($err).'<br>';
			$tpl -> append("messages", $tmp);

			$tmp = redirect_waiter('index.php');
			$tpl -> append ('scripts',$tmp);

		}
		break;

	default:

		//RTR start
		if(isset($_SESSION['userid']) && $_SESSION['userid'])
		{
		$tmp = '<H1>SEI GIA CONNESSO</H1><br>';
			$tpl -> append("messages", $tmp);
			$tmp = ucphr('CONNECT');
			$tpl -> assign("title", $tmp);
			$tmp = redirect_waiter('tables.php');
			$tpl -> append ('scripts',$tmp);
		}
		//RTR end


//RTR START allowed_user_host
//nuova funzione per accesso automatico con ip impostato di default nella tabella users
// funzione nel file include/common_waiter.php

if(allowed_user_host($_SERVER['REMOTE_ADDR']))
{
// connessione a MySQL con l'estensione MySQLi
$mysqli = new mysqli("$cfgserver", "$cfguser", "$cfgpassword", "$db_common");

// verifica dell'avvenuta connessione
if (mysqli_connect_errno()) {
// notifica in caso di errore
echo "Errore in connessione al DBMS: ".mysqli_connect_error()."<br><br>";
// interruzione delle esecuzioni i caso di errore
exit();
}

// estrarre risultati con il metodo mysqli_result::fetch_array
// query argomento del metodo query()
$query = "SELECT id FROM mhr_users WHERE user_host='".$_SERVER['REMOTE_ADDR']."' ";
// esecuzione della query
$result = $mysqli->query($query);
// conteggio dei record restituiti dalla query
if($result->num_rows >0)
{
// generazione di un array numerico
while($row = $result->fetch_array(MYSQLI_NUM))
{
$userid=$row[0];
}
}
// liberazione delle risorse occupate dal risultato
$result->close();
// chiusura della connessione
$mysqli->close();
$_REQUEST['userid']= $userid;
$_SESSION['userid']=$_REQUEST['userid'];
$useridnotsetisok=true;
$_SESSION['passworded']=true;
$tmp = redirect('tables.php',0);
$error_msg .= 'tables.php?userid='.$userid.'';
$error_msg .= $_REQUEST['userid'];
$error_msg .= '<br>';
$error_msg .= '<H1>RTR funzione filtra accessi con IP</H1><br>'."\n";
$error_msg .= '<b>'.$_SERVER['REMOTE_ADDR'].'</b> è il tuo indirizzo IP.<br>'."\n";
$error_msg .= '<b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> è il tuo indirizzo IP long.'."\n";
$error_msg .= common_bottom();
die($error_msg);
}
//RTR END

		else {
		$tmp = access_connect_form_waiter();
		$tpl -> assign("content", $tmp);
		}
		break;
}

$_SESSION['common_db']=$db_common;

$tmp = head_line('Connection');
$tpl -> assign("head", $tmp);
$tmp = show_logo();
$tpl -> assign("logo", $tmp);
$tmp = ucphr('CONNECT');
$tpl -> assign("title", $tmp);

$menu = new menu();
$tmp = $menu -> main ();
$tpl -> assign("menu", $tmp);
$tmp = ucphr('CONNECT');
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
