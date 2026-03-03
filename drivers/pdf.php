<?php
// list of all the available commands
// in alphabetical order
function driver_pdf($msg) {

// utilizzato su linux con stampa cups-pdf
//per ovviare agli accenti utilizzo il carattere ` chr(96)
//non � bellissimo ma ho gi� perso una sera a trovare la soluzione cacchio

//si è verificato un errore con il charset durante i salvataggi
//al posto di � vanno messe le lettere accentate

//	$msg = stri_replace ("�", "e`", $msg);
//	$msg = stri_replace ("�", "e`", $msg);
//	$msg = stri_replace ("�", "a`", $msg);
//	$msg = stri_replace ("�", "u`", $msg);
//	$msg = stri_replace ("�", "o`", $msg);
//	$msg = stri_replace ("�", "i`", $msg);


//	$msg = stri_replace ('{align_center}','',$msg);
//	$msg = stri_replace ('{align_left}','',$msg);
//	$msg = stri_replace ('{barcode_code39}','',$msg);
//	$msg = stri_replace ('{/barcode_code39}','',$msg);
//	$msg = stri_replace ('{dashes_row}','',$msg);
//	$msg = stri_replace ('{feed_reverse}','',$msg);
//	$msg = stri_replace ('{feed_reverse2}','',$msg);
//	$msg = stri_replace ('{init}','',$msg);
//	$msg = stri_replace ('{height_double}','',$msg);
//	$msg = stri_replace ('{/height_double}','',$msg);
//	$msg = stri_replace ('{highlight}','',$msg);
//	$msg = stri_replace ('{/highlight}','',$msg);
//	$msg = stri_replace ('{no_paper_print_disabler}','',$msg);
//	$msg = stri_replace ('{page_cut}','',$msg);
//	$msg = stri_replace ('{paper_release}','',$msg);
//	$msg = stri_replace ('{size_double}','',$msg);
//	$msg = stri_replace ('{/size_double}','',$msg);
//	$msg = stri_replace ('{size_normal}','',$msg);
//	$msg = stri_replace ('{size_triple}','',$msg);
//	$msg = stri_replace ('{/size_triple}','',$msg);
//	$msg = stri_replace ('{tab_define}','',$msg);
//	$msg = stri_replace ('{unknown}','',$msg);

	return $msg;
}

?>
