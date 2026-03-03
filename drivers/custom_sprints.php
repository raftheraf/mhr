<?php

// custom s'print-S thermal printer
// 24 cols (52 mm paper roll)
function driver_custom_sprints($msg) {

	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{size_triple}','{size_double}',$msg);
	$msg = stri_replace ('{/size_triple}','{/size_double}',$msg);

	$msg = stri_replace ('{init}',"\n",$msg);
	$msg = stri_replace ('{height_double}',"\n".chr(0x02),$msg);
	$msg = stri_replace ('{/height_double}',"\n".chr(0x04),$msg);
	$msg = stri_replace ('{size_double}',"\n".chr(0x03),$msg);
	$msg = stri_replace ('{/size_double}',"\n".chr(0x04),$msg);
	$msg = stri_replace ('{page_cut}',"\n--CUT_PAGE--\n",$msg);
	$msg = stri_replace ('{dashes_row}',"------------------------",$msg);
	$msg = stri_replace ('{barcode_code39}',chr(0x1B)."N".chr(0x1B).'cC'.chr(0x50).chr(0x3C).chr(0x14).chr(0x06)."SPRINT",$msg);
	$msg = stri_replace ('{/barcode_code39}',"\n",$msg);

	return $msg;
}

?>
