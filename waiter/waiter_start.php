<?php

	//define('ROOTDIR','..');
	require(ROOTDIR."/conf/config.inc.php");
	require(ROOTDIR."/conf/config.constants.inc.php");

	// session_start();

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();

	/*
	database connection.
	we put it here, so that it is the very first thing done,
	and we always an available connection ready to use
	*/

	if(!$link = @mysql_pconnect ($cfgserver, $cfguser, $cfgpassword)) {
		header('Location: '.ROOTDIR.'/install.php');
		die ('Error connecting to the db');
	}

	$_SESSION['common_db']=$db_common;

	check_db_status();

	start_language ();

	$tpl = new template;

	$dbman = new db_manager ('', '', '', $link);
	/*
	if($dbman->upgrade_available()) {
		if(CONF_FORCE_UPGRADE && !in_array(basename($_SERVER['SCRIPT_NAME']),$allowed_not_upgraded)) {
			header('Location: '.ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1');
			echo 'Upgrades available.';
			die();
		}
		$tmp = '<font color="red">'.ucphr('UPGRADES_AVAILABLE').'<br><a href="'.ROOTDIR.'/admin/upgrade.php?command=none&data[redirected]=1">'.ucphr('CLICK_HERE_TO_UPGRADE').'</a></font><br>'."\n";
		$tpl -> append("messages", $tmp);
		unset($tmp);
	}
	*/

	/*
	we almost always use this command var, so we get it here
	to make it available to other functions whithout other hassle
	*/
	if(isset($_REQUEST['command'])){
		$command=$_REQUEST['command'];
	} else {
		$command='none';
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	}

	if(!isset($_SESSION['section']) || $_SESSION['section']!="waiter"){
		unset_session_vars();
		$_SESSION['section']="waiter";
	}

	if(isset($dont_get_session_sourceid) && $dont_get_session_sourceid) {
		unset($_SESSION['sourceid']);
	}

	/*
	we check at least to have some tables in each db
	otherwise we stop execution and report an error
	*/
	$tmp['tableslist'] = mysql_query('SHOW TABLES IN `'.$_SESSION['common_db'].'`', $link);
	$tmp['numtables'] = mysql_num_rows ($tmp['tableslist']);
	if($tmp['numtables']==0) die(GLOBALMSG_DB_NO_TABLES_ERROR);
	unset($tmp);

	if(!common_allowed_ip($_SERVER['REMOTE_ADDR'])) {
		$error_msg = common_header('IP address not authorized');
		$error_msg .= 'IP <b>'.$_SERVER['REMOTE_ADDR'].'</b> is not authorized.<br>'."\n";
		$error_msg .= 'IP <b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> is not authorized.'."\n";

		$error_msg .= common_bottom();
		die($error_msg);
	}

	$GLOBALS['cache_var']=new cache();

	if(!find_accounting_db()) {
		$error_msg = common_header('No accounting db has been found');
		$error_msg .=  navbar_empty();

		$error_msg .= GLOBALMSG_NO_ACCOUNTING_DB_FOUND."<br><br>\n";
		$error_msg .= GLOBALMSG_CONFIGURE_DATABASES."\n";
		$error_msg .= common_bottom();
		error_msg(__FILE__,__LINE__,'No accounting db has been found');
		die($error_msg);
	}

	if($res_loc=check_output_files ()) {
			$error_msg = common_header('Output files not writeable');
			$error_msg .=  navbar_empty();

			switch($res_loc) {
				case 1: $err='error file not writeable.<br>Solution: set write permission for everybody (or at least for the user running the webserver) on file error.log'; break;
				case 2: $err='error dir not writeable<br>Solution: set write permission for everybody (or at least for the user running the webserver) on the directory containing My Handy Restaurant files (typically called myhandyrestaurant)'; break;
				case 3: $err='debug file not writeable.<br>Solution: set write permission for everybody (or at least for the user running the webserver) on file debug.log'; break;
				case 4: $err='debug dir not writeable'; break;
			}

			$error_msg .= GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE.'<br><br>Error #'.$res_loc.': '.$err.'<br>'."\n";
			$error_msg .= GLOBALMSG_CONFIG_SYSTEM.'<br>'."\n";
			$error_msg .= common_bottom();
			die($error_msg);
	}
	unset($res_loc);

	/*
	getting the source id.
	first we check if we already know this id, otherwise we try to catch it
	from a GET or POST feed (from tables.php) or we get it from the user SESSION

	Note: if $dont_get_session_sourceid is true, we won't get the sourceid from
	SESSION. this is useful when you want the source id to be unset.
	*/
	if(isset($start_data['sourceid'])){
		$_SESSION['sourceid']=(int) ($start_data['sourceid']);
	}

	if(!isset($useridnotsetisok)) $useridnotsetisok=false;

	// Waiter identification
	if (isset($_REQUEST['userid'])) {
		/*
		Case 1: we get the waiter id from a POST
		this happens when we receive data from the index.php page

		here we save into session the waiter id and name, for later use
		*/
		$_SESSION['userid']=$_REQUEST['userid'];

		start_language ();
	} elseif(!$useridnotsetisok && (!isset($_SESSION['userid']) || !$_SESSION['userid'])) {
		/*
		2 case: we didn't find any POST nor any SESSION giving us userid,
		and we don't like it!

		the var $useridnotsetisok is checked because
		we assume that having not authenticated is good in some cases
		eg when the waiter has not authenticated yet (index.php)
		or in other cases when it is not required to be authenticated

		to activate this flag, just write the following line BEFORE the require line:
		// ---- example begin ----
		$useridnotsetisok=1;			//has to be before start.php requirement!!!
		require("./start.php");
		// ---- example end ----
		*/

		/*
		We stop execution, because the waiter has to authenticate first
		so we suggest him/her to authenticate on index.php page
		*/
		$error_msg = common_header('non sei connesso');
		//$error_msg .= redirect_waiter('connect.php');
		$error_msg .= redirectJS('connect.php');
		$error_msg .= '<h1><b>Errore!!!<br>non sei connesso</b></h1><br><br>
	<a href="connect.php"><h1><b>CONNETTITI</b></h1></a>';
		$error_msg .= common_bottom();
		die($error_msg);
	}
	unset($res);
	unset($arr);

	if (isset($_SESSION['userid']) && $_SESSION['userid'] && isset($_REQUEST['mhr_tab_id']) && $_REQUEST['mhr_tab_id']) {
		if (!mhr_tab_guard_validate_request($_REQUEST['mhr_tab_id'])) {
			header('Location: '.ROOTDIR.'/multi_tab_error.php?reason=duplicate_tab');
			die();
		}
	}

	/*
	The next if contains a primitive access control
	to avoid that 2 waiters simultaneously work on the same table

	this is done by updating a timestamp field in the sources table
	and by checking (if the waiter changed)
	if the elapsed time from the last update is > than the config lock_time
	*/
	if (isset($_SESSION['sourceid']) && $_SESSION['sourceid']){

		// BLOCCO TAVOLO - sblocco forzato (solo cassiere + denaro)
		if(isset($_POST['force_unlock']) && $_POST['force_unlock']=='1') {
			if(access_allowed(USER_BIT_CASHIER) && access_allowed(USER_BIT_MONEY)) {
				$query="UPDATE `#prefix#sources` SET `last_access_time` = NULL, `last_access_userid` = '".$_SESSION['userid']."' WHERE `id` = '".$_SESSION['sourceid']."' LIMIT 1";
				common_query($query, __FILE__, __LINE__);
			}
		}

		// BLOCCO TAVOLO
		if(table_lock_check($_SESSION['sourceid'])) {
			$remaining_time=table_lock_remaining_time($_SESSION['sourceid']);

			$user = new user ($_SESSION['userid']);

			$lock_owner_name = '';
			$res_lock=common_query("SELECT `last_access_userid` FROM `#prefix#sources` WHERE `id`='".$_SESSION['sourceid']."'",__FILE__,__LINE__);
			if($res_lock) {
				$arr_lock=mysql_fetch_array($res_lock);
				if($arr_lock && $arr_lock['last_access_userid']) {
					$lock_owner = new user($arr_lock['last_access_userid']);
					$lock_owner_name = $lock_owner->data['name'];
				}
			}

			if($remaining_time==0) $remaining_time = 1;
			$error_msg = common_header('Tavolo occupato');
			$error_msg .= '
<!-- BLOCCO TAVOLO -->
<style>
.lock-container {
	max-width: 480px;
	margin: 60px auto;
	background: #ffffff;
	border-radius: 6px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.18);
	padding: 36px 28px;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
}
.lock-title {
	color: #c0392b;
	font-size: 26px;
	margin: 0 0 14px 0;
}
.lock-msg {
	font-size: 18px;
	color: #333;
	margin: 0 0 8px 0;
}
.lock-user {
	font-size: 15px;
	color: #777;
	margin: 16px 0 24px 0;
	border-top: 1px solid #eee;
	padding-top: 16px;
}
.lock-countdown {
	font-size: 56px;
	font-weight: bold;
	color: #e74c3c;
	line-height: 1;
	margin: 8px 0 4px 0;
}
.lock-countdown-label {
	font-size: 14px;
	color: #999;
	margin-bottom: 24px;
}
.lock-buttons {
	display: flex;
	gap: 12px;
	justify-content: center;
	flex-wrap: wrap;
	margin-top: 8px;
}
.lock-btn {
	padding: 14px 28px;
	border: none;
	border-radius: 4px;
	font-size: 20px;
	cursor: pointer;
	text-decoration: none;
	display: inline-block;
	font-family: Arial, Helvetica, sans-serif;
}
.lock-owner {
	font-size: 20px;
	color: #555;
	margin: 0 0 16px 0;
}
.lock-btn-back   { background: #95a5a6; color: #fff; }
.lock-btn-retry  { background: #27ae60; color: #fff; }
.lock-btn-unlock { background: #e67e22; color: #fff; width: 100%; margin-top: 16px; }
.lock-unlock-section {
	margin-top: 20px;
	padding-top: 16px;
	border-top: 1px solid #eee;
}
</style>
<div class="lock-container">
	<h2 class="lock-title">Tavolo occupato</h2>
	<p class="lock-msg">Un altro cameriere sta lavorando su questo tavolo.</p>
	<p class="lock-owner">Bloccato da: <strong>'.(isset($lock_owner_name) && $lock_owner_name ? htmlspecialchars($lock_owner_name) : 'cameriere sconosciuto').'</strong></p>
	<div class="lock-countdown" id="lock-timer">'.$remaining_time.'</div>
	<div class="lock-countdown-label">secondi al prossimo tentativo automatico</div>
	<div class="lock-buttons">
		<a href="javascript:void(0)" onclick="redir(\'tables.php\')" class="lock-btn lock-btn-back">&#8592; Tavoli</a>
		<a href="javascript:void(0)" onclick="redir(\'orders.php\')" class="lock-btn lock-btn-retry">Riprova ora</a>
	</div>
	<p class="lock-user">Se non sei <strong>'.$user->data['name'].'</strong> devi disconnetterti.</p>';

			// Pulsante sblocco forzato: solo cassiere + denaro
			if(access_allowed(USER_BIT_CASHIER) && access_allowed(USER_BIT_MONEY)) {
				$error_msg .= '
	<div class="lock-unlock-section">
		<form method="post" action="orders.php" onsubmit="return confirm(\'Sbloccare il tavolo e prenderne il controllo?\')">
			<input type="hidden" name="force_unlock" value="1">
			<button type="submit" class="lock-btn lock-btn-unlock">Sblocca tavolo</button>
		</form>
	</div>';
			}

			$error_msg .= '
</div>
<script>
(function(){
	var t = '.$remaining_time.';
	var el = document.getElementById("lock-timer");
	var iv = setInterval(function(){
		t--;
		if(t <= 0){
			clearInterval(iv);
			redir("orders.php");
		} else {
			el.innerHTML = t;
		}
	}, 1000);
})();
</script>
';
			$error_msg .= common_bottom();
			die($error_msg);
		}
	}

	/*
	We get the printed categories flag, and write to $_SESSION['catprinted'][]
	*/
	if (isset($_SESSION['sourceid'])){
		$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$_SESSION['sourceid']."'";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		$arr = mysql_fetch_array ($res);
		$catprinted_total=$arr['catprinted'];

		$catprinted_total=explode (" ", $catprinted_total);
		for($i=1;$i<=4;$i++){
			if (in_array ("$i", $catprinted_total)){
				$_SESSION['catprinted'][$i]=true;
			} else {
				$_SESSION['catprinted'][$i]=false;
			}
		}
		unset($res);
		unset($arr);
		unset($catprinted_total);
	}


//RTR START allowed_user_host
//nuova funzione per accesso automatico con ip impostato di default nella tabella users
// funzione nel file include/common_waiter.php
if (!isset($_SESSION['userid'])){
if(allowed_user_host($_SERVER['REMOTE_ADDR'])) {
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
$error_msg .= 'tables.php?userid='.$userid.'';
$tmp = redirect('tables.php?userid='.$userid.'',0);
$error_msg .= $_REQUEST['userid'];
$error_msg .= '<br>';
$error_msg .= '<H1>RTR funzione filtra accessi con IP</H1><br>'."\n";
$error_msg .= '<b>'.$_SERVER['REMOTE_ADDR'].'</b> è il tuo indirizzo IP.<br>'."\n";
$error_msg .= '<b>'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'</b> è il tuo indirizzo IP long.'."\n";
$error_msg .= common_bottom();
die($error_msg);
}
}
//RTR END


	header("Content-Language: ".$_SESSION['language']);
	header("Content-type: text/html; charset=".phr('CHARSET'));

	$tmp = head_line('Waiters\' section');
	$tpl -> assign("head", $tmp);

	if(!isset($dont_redirect_to_menu)) {
		$time_refresh=1000*get_conf(__FILE__,__LINE__,'refresh_automatic_to_menu');
		if($time_refresh) {
			$tmp = redirect_timed('tables.php',$time_refresh);
			$tpl -> append("scripts", $tmp);
		}
	}

	if(isset($_SESSION['sourceid']) && $_SESSION['sourceid']) $tmp = table_people_number_line ($_SESSION['sourceid']);
	$tpl -> assign("people_number", $tmp);
?>
