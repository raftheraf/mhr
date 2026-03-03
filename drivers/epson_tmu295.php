<?php
// epson dot matrix printer used for bills
function driver_epson_tmu295($msg) {


	$msg = stri_replace (chr(150),'-' ,$msg);

	$msg = stri_replace ("é", chr(130), $msg);
	$msg = stri_replace ("è", chr(138), $msg);
	$msg = stri_replace ("à", chr(133), $msg);
	$msg = stri_replace ("ù", chr(151), $msg);
	$msg = stri_replace ("ò", chr(149), $msg);
	$msg = stri_replace ("ì", chr(141), $msg);

	$msg = stri_replace ('{init}',chr(0x1B)."@",$msg);
	$msg = stri_replace ('{feed_reverse}',chr(0x1B).'F1',$msg);
	$msg = stri_replace ('{feed_reverse2}',chr(0x1B)."f".chr(0).chr(10),$msg);
	$msg = stri_replace ('{no_paper_print_disabler}',chr(0x1B)."c4".chr(48),$msg);

	$msg = stri_replace ('{unknown}',chr(0x1B)."D".chr(2).chr(4).chr(20).chr(0),$msg);

	$msg = stri_replace ('{tab_define}','',$msg);

	$msg = stri_replace ('{dashes_row}',"-----------------------------------",$msg);
	$msg = stri_replace ('{paper_release}',"\n\n".chr(0x0c).chr(27)."q",$msg);
	$msg = stri_replace ('{height_double}',chr(27).'h1',$msg);
	$msg = stri_replace ('{/height_double}',chr(27).'h0',$msg);

	$msg = stri_replace ('{align_rigth}',chr(27).chr(29).'a2',$msg);
	$msg = stri_replace ('{align_center}',chr(27).chr(29).'a1',$msg);
	$msg = stri_replace ('{align_left}',chr(27).chr(29).'a0',$msg);

	return $msg;
}

/*
// wrong data, don't modify it
function driver_epson_bill_old($msg) {

	$msg = stri_replace ('{init}',chr(0x1B)."@",$msg);
	$msg = stri_replace ('{feed_reverse}',chr(0x1B).'F1',$msg);
	$msg = stri_replace ('{feed_reverse2}',chr(0x1B)."f".chr(0).chr(10),$msg);
	$msg = stri_replace ('{no_paper_print_disabler}',chr(0x1B)."c4".chr(48),$msg);
	$msg = stri_replace ('{unknown}',chr(0x1B)."D".chr(2).chr(4).chr(20).chr(0),$msg);

	return $msg;
}
*/
?>
