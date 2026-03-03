<?php
function epson_fp90($msg) {
	/*
	si è verificato un errore con il charset durante i salvataggi
	al posto di � vanno messe le lettere accentate

	$msg = stri_replace ("�", chr(176), $msg);
	$msg = stri_replace ("�", chr(177), $msg);
	$msg = stri_replace ("�", chr(207), $msg);
	$msg = stri_replace ("�", chr(192), $msg);
	$msg = stri_replace ("�", chr(187), $msg);
	$msg = stri_replace ("�", chr(182), $msg);
	*/

	$msg = stri_replace ('{dashes_row}',"-------------------------------",$msg);

	$msg = stri_replace ('{size_normal}',chr(29).'!11',$msg);

	$msg = stri_replace ('{height_double}',chr(29).'!12',$msg);
	$msg = stri_replace ('{/height_double}','{size_normal}',$msg);

	$msg = stri_replace ('{size_triple}',chr(29).'!33',$msg);
	$msg = stri_replace ('{/size_triple}','{size_normal}',$msg);

	$msg = stri_replace ('{size_double}',chr(29).'!22',$msg);
	$msg = stri_replace ('{/size_double}','{size_normal}',$msg);

	$msg = stri_replace ('{align_left}',"\n".chr(27).'a0',$msg);

	$msg = stri_replace ('{align_center}',"\n".chr(27).'a1',$msg);

	$msg = stri_replace ('{align_right}',"\n".chr(27).'a2',$msg);

	$msg = stri_replace ('{highlight}',chr(29).'B1',$msg);
	$msg = stri_replace ('{/highlight}',chr(29).'B0',$msg);

	//Taglia la carta
	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{page_cut}',"\n".chr(29).'V0',$msg);

	$msg = stri_replace ('{barcode_code39}',"\n".chr(29)."k4",$msg);
	$msg = stri_replace ('{/barcode_code39}',"\0",$msg);

	return $msg;
}

?>
