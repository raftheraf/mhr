<?php
require(ROOTDIR."/conf/config.inc.php");
require(ROOTDIR."/conf/config.constants.inc.php");

global $header_printed, $dont_display_menu;
if (!isset($header_printed)) {
	$header_printed = 0;
}
if (!isset($dont_display_menu)) {
	$dont_display_menu = false;
}

if(!$header_printed){
	//session_start();

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	common_set_error_reporting ();

	if(!isset($_SESSION['section']) || $_SESSION['section']!="waiter"){
		unset_session_vars();
		$_SESSION['section']="waiter";
	}

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
	}
	*/

	unset($_SESSION['catprinted']);

	if(isset($_REQUEST['command'])) $command=$_REQUEST['command'];
	else $command='none';

	if(isset($_REQUEST['id'])){
		$start_id=$_REQUEST['id'];
		$_SESSION['id']=$start_id;
	} elseif(isset($_SESSION['id'])){
		$start_id=$_SESSION['id'];
	}

	if(isset($_REQUEST['data'])){
		$start_data=$_REQUEST['data'];
	} else $start_data = array();

	if (isset($_SESSION['userid']) && $_SESSION['userid'] && isset($_REQUEST['mhr_tab_id']) && $_REQUEST['mhr_tab_id']) {
		if (!mhr_tab_guard_validate_request($_REQUEST['mhr_tab_id'])) {
			header('Location: '.ROOTDIR.'/multi_tab_error.php?reason=duplicate_tab');
			die();
		}
	}

	if(!$dont_display_menu) {
		$menu = new menu();
		$tmp = $menu -> main ();
		$tpl -> assign("menu", $tmp);
	}
	$header_printed=2;
}

?>
