<?php

function driver_RCH_PrintF($msg) {

	//elimina i caratteri pericolosi che bloccano la Print-F
	$msg = stri_replace (chr(150),'-' ,$msg);

	//codifica caratteri il codice chr(224) corrisponde
	//Standard ASCII set, HTML Entity names, ISO 10646, ISO 8879, ISO 8859-1 Latin alphabet No. 1
	//Browser support: All browsers
	$msg = stri_replace ("à",chr(224) ,$msg);
	$msg = stri_replace ("è",chr(232) ,$msg);
	$msg = stri_replace ("ì",chr(236) ,$msg);
	$msg = stri_replace ("ò",chr(242) ,$msg);
	$msg = stri_replace ("ù",chr(249) ,$msg);

	$msg = stri_replace ('{align_center}','',$msg);
	$msg = stri_replace ('{align_left}','',$msg);
	$msg = stri_replace ('{barcode_code39}','',$msg);
	$msg = stri_replace ('{/barcode_code39}','',$msg);
	$msg = stri_replace ('{dashes_row}','',$msg);
	$msg = stri_replace ('{feed_reverse}','',$msg);
	$msg = stri_replace ('{feed_reverse2}','',$msg);
	$msg = stri_replace ('{init}','',$msg);
	$msg = stri_replace ('{height_double}','',$msg);
	$msg = stri_replace ('{/height_double}','',$msg);
	$msg = stri_replace ('{highlight}','',$msg);
	$msg = stri_replace ('{/highlight}','',$msg);
	$msg = stri_replace ('{no_paper_print_disabler}','',$msg);
	$msg = stri_replace ('{page_cut}','',$msg);
	$msg = stri_replace ('{paper_release}','',$msg);
	$msg = stri_replace ('{size_double}','',$msg);
	$msg = stri_replace ('{/size_double}','',$msg);
	$msg = stri_replace ('{size_normal}','',$msg);
	$msg = stri_replace ('{size_triple}','',$msg);
	$msg = stri_replace ('{/size_triple}','',$msg);
	$msg = stri_replace ('{tab_define}','',$msg);
	$msg = stri_replace ('{unknown}','',$msg);

	return $msg;
}

?>
