<?php
session_start();

define('ROOTDIR','.');
require_once(ROOTDIR.'/includes.php');

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function mhr_tab_guard_json_response($arr) {
	echo json_encode($arr);
	exit;
}

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : 'status';
$tab_id = isset($_REQUEST['tab_id']) ? trim($_REQUEST['tab_id']) : '';

if (!isset($_SESSION['userid']) || !$_SESSION['userid']) {
	mhr_tab_guard_release();
	mhr_tab_guard_json_response(array('ok' => 1, 'allowed' => 1, 'active' => 0));
}

switch ($action) {
	case 'claim':
	case 'heartbeat':
		$allowed = mhr_tab_guard_claim($tab_id);
		$state = mhr_tab_guard_get_state();
		mhr_tab_guard_json_response(array(
			'ok' => 1,
			'allowed' => $allowed ? 1 : 0,
			'active_tab_id' => isset($state['tab_id']) ? $state['tab_id'] : ''
		));
		break;

	case 'force_claim':
		$allowed = mhr_tab_guard_force_claim($tab_id);
		mhr_tab_guard_json_response(array(
			'ok' => 1,
			'allowed' => $allowed ? 1 : 0
		));
		break;

	case 'release':
		mhr_tab_guard_release($tab_id);
		mhr_tab_guard_json_response(array('ok' => 1, 'released' => 1));
		break;

	case 'status':
	default:
		$state = mhr_tab_guard_get_state();
		$active = !empty($state) ? 1 : 0;
		mhr_tab_guard_json_response(array(
			'ok' => 1,
			'active' => $active,
			'active_tab_id' => isset($state['tab_id']) ? $state['tab_id'] : ''
		));
		break;
}

?>