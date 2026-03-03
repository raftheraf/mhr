<?php
function driver_star($msg) {

	//si è verificato un errore con il charset durante i salvataggi
	//al posto di � vanno messe le lettere accentate
	$msg = stri_replace ("�", chr(176), $msg);
	$msg = stri_replace ("�", chr(177), $msg);
	$msg = stri_replace ("�", chr(207), $msg);
	$msg = stri_replace ("�", chr(192), $msg);
	$msg = stri_replace ("�", chr(187), $msg);
	$msg = stri_replace ("�", chr(182), $msg);

	//inizializza la stampante
	$msg = stri_replace ('{init}',chr(27)."@",$msg);

	$msg = stri_replace ('{dashes_row}',"------------------------------------------------",$msg);
	$msg = stri_replace ('{inizio_comanda}',"* * * * * * * * * * * * * * * * * * * * * * * *",$msg);
	$msg = stri_replace ('{fine_comanda}',"* * * * * * * * * * * fine * * * * * * * * * * *",$msg);

	//Select emphasized printing
	$msg = stri_replace ('{emphatize}',chr(27)."E",$msg);
	$msg = stri_replace ('{/empatize}'," ".chr(27)."F",$msg);

	//Set line spacing to 3 mm
	$msg = stri_replace ('{line_15}',chr(27)."0",$msg);
	$msg = stri_replace ('{line_20}',chr(27)."z1",$msg);

	//One time n/4 mm feed
	$msg = stri_replace ('{one_time_4_mm_feed}',chr(27).'Q89',$msg);

	//Set left margin NON VA
	//$msg = stri_replace ('{left_margin}',chr(27).'Q09',$msg);

	//Set line spacing to 3 mm
	$msg = stri_replace ('{line_10}',chr(27).'0',$msg);

	$msg = stri_replace ('{LF}',chr(10),$msg);
	$msg = stri_replace ('CAN}',chr(24),$msg);

	$msg = stri_replace ('{size_normal}',chr(27).'i00',$msg);

	$msg = stri_replace ('{height_double}',chr(27).'i10',$msg);
	$msg = stri_replace ('{/height_double}',chr(27).'i00',$msg);

	$msg = stri_replace ('{size_double}',chr(27).'i11',$msg);
	$msg = stri_replace ('{/size_double}',chr(27).'i00',$msg);

	$msg = stri_replace ('{size_triple}',chr(27).'i22',$msg);
	$msg = stri_replace ('{/size_triple}',chr(27).'i00',$msg);

	$msg = stri_replace ('{highlight}',chr(27).'4',$msg);
	$msg = stri_replace ('{/highlight}',chr(27).'5',$msg);

	$msg = stri_replace ('{align_center}',chr(27).chr(29)."a1",$msg);
	$msg = stri_replace ('{align_left}',chr(27).chr(29)."a0",$msg);
	$msg = stri_replace ('{align_right}',chr(27).chr(29)."a2",$msg);

	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{page_cut}',chr(27).'d2',$msg);

	$msg = stri_replace ('{barcode_code39}',chr(27)."b422".chr(100),$msg);
	$msg = stri_replace ('{/barcode_code39}',chr(30),$msg);

	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{page_cut}',chr(27).'d2',$msg);

	//risolve il problema allineamento nella stampa preconto star2000 e tmu295
	$msg = stri_replace ('{star_align_left}',chr(27).chr(29)."a0",$msg);
	$msg = stri_replace ('{star_align_right}',chr(27).chr(29)."a2",$msg);

	return $msg;
}

?>
