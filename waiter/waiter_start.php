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

	if($_SESSION['section']!="waiter"){
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
	$tmp['tableslist'] = mysql_list_tables ($_SESSION['common_db'],$link);
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
	} elseif(!$useridnotsetisok && !$_SESSION['userid']) {
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

	/*
	The next if contains a primitive access control
	to avoid that 2 waiters simultaneously work on the same table

	this is done by updating a timestamp field in the sources table
	and by checking (if the waiter changed)
	if the elapsed time from the last update is > than the config lock_time
	*/
	if (isset($_SESSION['sourceid']) && $_SESSION['sourceid']){
		if(table_lock_check($_SESSION['sourceid'])) {
			$remaining_time=table_lock_remaining_time($_SESSION['sourceid']);

			$user = new user ($_SESSION['userid']);

			if($remaining_time==0) $remaining_time = 1;
			$error_msg = common_header('Table in use');
			$error_msg .=  navbar_lock_retry('');
			$error_msg .=  '<center>';
			$error_msg .=  '<br><br>';
			$error_msg .=  '';
			$error_msg .= '<h2>Un altro cameriere<br>sta lavorando sul tavolo</h2>';
			$error_msg .= '<h3>Riprova tra '.$remaining_time.' '.ucfirst(phr('SECONDS')).'.</h3><br><br>'."\n";
			$error_msg .=  '<br><br>';
			$error_msg .= 'Se non sei <b>'.$user->data['name'].'</b><br> ti devi disconnettere<br>'."\n";
			$error_msg .=  '</center>';
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
