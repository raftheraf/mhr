<?php
require_once(ROOTDIR."/funs_common.php");

// explicitily called to be before other extended classes
require_once(ROOTDIR."/include/object_class_admin.php");
include_once(ROOTDIR."/manage/mgmt_funs_stats.php");
include_once(ROOTDIR."/manage/mgmt_funs_other.php");
include_once(ROOTDIR."/manage/mgmt_funs_database.php");
include_once(ROOTDIR."/manage/mgmt_funs_supply.php");
include_once(ROOTDIR."/manage/mgmt_funs_receipt.php");
include_once(ROOTDIR."/manage/mgmt_funs_printable.php");
include_once(ROOTDIR."/manage/mgmt_funs_account.php");
include_once(ROOTDIR."/manage/mgmt_funs_stock.php");
include_once(ROOTDIR."/xtemplate/xtpl.php");
include_once(ROOTDIR."/include/ezpdf_class.php");
include_once(ROOTDIR."/include/ezpdf_class_pdf.php");

//define('CONF_USE_CACHED_INCLUDE',true);

// includes all the files in include dir
clearstatcache();

$dir_scan=ROOTDIR.'/include';
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			// echo 'including '.$dir_scan.'/'.$file;
			require_once ($dir_scan.'/'.$file);
			// echo '.<br>';
		}
	}
	closedir($handle);
}

/* Stock scanning */
$dir_scan=ROOTDIR.'/stock/include';
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			// echo 'including '.$dir_scan.'/'.$file;
			require_once ($dir_scan.'/'.$file);
			// echo '.<br>';
		}
	}
	closedir($handle);
}

/* Modules scanning */
/*
$dir_scan=ROOTDIR;
if ($handle = opendir($dir_scan)) {
	// loops into modules directory
	while (false !== ($file = readdir($handle))) {

		$dir2=$dir_scan.'/'.$file.'/include';
		if (is_dir($dir2) && is_readable($dir2) && $file!='..' && $file!='.' && $handle = opendir($dir2)) {
			while (false !== ($file2 = readdir($handle))) {
				if (is_file($dir2.'/'.$file2) && is_readable($dir2.'/'.$file2) && strtolower(substr($file2,-4))=='.php') {
					include_once ($dir2.'/'.$file2);
				}
			}
		}
	}
	closedir($handle);
}
*/

// includes all the printer drivers
$dir_scan=ROOTDIR.'/drivers';
clearstatcache();
if ($handle = opendir($dir_scan)) {
	while (false !== ($file = readdir($handle))) {
		if (is_file($dir_scan.'/'.$file) && is_readable($dir_scan.'/'.$file) && strtolower(substr($file,-4))=='.php') {
			include_once ($dir_scan.'/'.$file);
		}
	}
	closedir($handle);
}

?>
