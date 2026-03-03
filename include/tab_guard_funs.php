<?php

function mhr_tab_guard_ttl_seconds() {
	return 20;
}

function mhr_tab_guard_cleanup() {
	if (!isset($_SESSION['mhr_tab_guard']) || !is_array($_SESSION['mhr_tab_guard'])) {
		unset($_SESSION['mhr_tab_guard']);
		return;
	}

	$state = $_SESSION['mhr_tab_guard'];
	$ttl = mhr_tab_guard_ttl_seconds();
	$updated = isset($state['updated_at']) ? (int) $state['updated_at'] : 0;

	if (!$updated || (time() - $updated) > $ttl) {
		unset($_SESSION['mhr_tab_guard']);
	}
}

function mhr_tab_guard_get_state() {
	mhr_tab_guard_cleanup();
	if (!isset($_SESSION['mhr_tab_guard']) || !is_array($_SESSION['mhr_tab_guard'])) {
		return array();
	}

	return $_SESSION['mhr_tab_guard'];
}

function mhr_tab_guard_set_state($tab_id) {
	$_SESSION['mhr_tab_guard'] = array(
		'tab_id' => (string) $tab_id,
		'updated_at' => time()
	);
}

function mhr_tab_guard_claim($tab_id) {
	$tab_id = trim((string) $tab_id);
	if ($tab_id === '') {
		return false;
	}

	$state = mhr_tab_guard_get_state();
	if (empty($state)) {
		mhr_tab_guard_set_state($tab_id);
		return true;
	}

	if (isset($state['tab_id']) && $state['tab_id'] === $tab_id) {
		mhr_tab_guard_set_state($tab_id);
		return true;
	}

	return false;
}

function mhr_tab_guard_validate_request($tab_id) {
	return mhr_tab_guard_claim($tab_id);
}

function mhr_tab_guard_release($tab_id = '') {
	$tab_id = trim((string) $tab_id);
	if (!isset($_SESSION['mhr_tab_guard']) || !is_array($_SESSION['mhr_tab_guard'])) {
		return;
	}

	if ($tab_id === '' || (isset($_SESSION['mhr_tab_guard']['tab_id']) && $_SESSION['mhr_tab_guard']['tab_id'] === $tab_id)) {
		unset($_SESSION['mhr_tab_guard']);
	}
}

?>