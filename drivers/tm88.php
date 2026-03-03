<?php
function driver_tm88($msg) {

//Accenti per la tm-t88 verificare gli accenti non funzionano
//tabelle prese dalla pagina "Page 0 [PC437: USA, Standard Europe]"
//https://reference.epson-biz.com/modules/ref_charcode_en/index.php?content_id=29
//problema con il simbolo Euro
//$tosend = str_replace ("€", chr(213), $tosend);
$msg = stri_replace (chr(150),'-' ,$msg);

$msg = stri_replace ("é", chr(130), $msg);
$msg = stri_replace ("è", chr(138), $msg);
$msg = stri_replace ("à", chr(133), $msg);
$msg = stri_replace ("ù", chr(151), $msg);
$msg = stri_replace ("ò", chr(149), $msg);
$msg = stri_replace ("ì", chr(141), $msg);

	//beeper la tm-t88 iii non supporta la funzione BELL per emettere un suono bisogna utilizzare un buzzer esterno collegato con la uscita DK
	/*Protocol
	Cash drawer kick-out
	From the internet, not experimented: The 'Open Drawer' code for Epson TM-T88II printers is "27,112,0,64,240"
	From the internet, not experimented: 27,112,0,25,250
	From official manual:
	27
	112
	m: 0 (48) if connected to pin 2, 1 (49) if connected to pin 5
	tON: on time (value x 2ms)
	tOFF: off time (value x 2ms)
	*/
	$msg = stri_replace ('{beep}',chr(27).chr(112).chr(48).chr(50).chr(10),$msg);

	//Nel file printing_waiter.php
	//Next commented code is born to drive an external beeper.
	// we should still work on the hardware.
	//$msg=chr(27).chr(7).chr(50).chr(1);
	//for($i=0;$i<10;$i++){
	//	$msg.=chr(28);
	//}


	//inizializza la stampante
	$msg = stri_replace ('{init}',chr(27)."@",$msg);

	//Print and Line feed non va
	//$msg = stri_replace ('{line_feed}',chr(10),$msg);

	$msg = stri_replace ('{dashes_row}',		"------------------------------------------",$msg);
	$msg = stri_replace ('{doubdashes_row}',"==========================================",$msg);
	$msg = stri_replace ('{dot_row}',				"..........................................",$msg);
	$msg = stri_replace ('{inizio_comanda}',"* * * * * * * * * inizio * * * * * * * * *",$msg);
	$msg = stri_replace ('{fine_comanda}',	"* * * * * * * * *  fine  * * * * * * * * *",$msg);
	$msg = stri_replace ('{linea}',					"__________________________________________",$msg);


	//Underline OK
	$msg = stri_replace ('{underline}',chr(27).'-',$msg);

	//Inizio spiegazione dimensione caratteri
	//
	//	Dimensioni	0,	1,	2,	3,	4,	5,	6,	7
	//	larghezza 	0, 16, 32, 48, 64, 80, 96, 112
	//	altezza		0,	1,	2,	3,	4,	5,	6,	7
	//
	//	esempio
	//	$msg = stri_replace ('{prova}',chr(29)."!".chr(17),$msg);
	//
	//	lil valore chr(17) è dato da 16 + 1 (larghezza 2 ed altezza 2)
	//
	//Fine spiegazione dimensione caratteri
	// ESC=chr(27) - GS=chr(29)


	//size_normal
	$msg = stri_replace ('{size_normal}',chr(29)."!".chr(0),$msg);

	//height_double
	$msg = stri_replace ('{height_double}',chr(29)."!".chr(1),$msg);
	$msg = stri_replace ('{/height_double}','{size_normal}',$msg);

	//size_double
	$msg = stri_replace ('{size_double}',chr(29)."!".chr(17),$msg);
	$msg = stri_replace ('{/size_double}','{size_normal}',$msg);

	//size_triple
	$msg = stri_replace ('{size_triple}',chr(29)."!".chr(34),$msg);
	$msg = stri_replace ('{/size_triple}','{size_normal}',$msg);

	//size_quadruple
	$msg = stri_replace ('{size_quadruple}',chr(29)."!".chr(51),$msg);
	$msg = stri_replace ('{/size_quadruple}','{size_normal}',$msg);


	//highlight OK
	$msg = stri_replace ('{highlight}',chr(29)."B1",$msg);
	$msg = stri_replace ('{/highlight}',chr(29)."B0",$msg);

	//align_left OK
	$msg = stri_replace ('{align_left}',chr(27).'a0',$msg);

	//align_center OK
	$msg = stri_replace ('{align_center}',chr(27).'a1',$msg);

	//align_right OK
	$msg = stri_replace ('{align_right}',chr(27).'a2',$msg);

	//Line spacing
	// default line spacing ESC 2 - Set line spacing ESC 3 n ( )
	//Linea 1,0 lasciare la riga dopo le altre serve per il reset
	$msg = stri_replace ('{line_10}',chr(27).'2',$msg);

	//linea 1,5
	$msg = stri_replace ('{line_15}',chr(27).'3'.chr(90),$msg);
	$msg = stri_replace ('{/line_15}','{line_10}',$msg);

	//linea 2
	$msg = stri_replace ('{line_20}',chr(27).'3'.chr(120),$msg);
	$msg = stri_replace ('{/line_20}','{line_10}',$msg);

	//linea 3
	$msg = stri_replace ('{line_30}',chr(27).'3'.chr(180),$msg);
	$msg = stri_replace ('{/line_30}','{line_10}',$msg);

	//Taglia la carta OK
	$msg = stri_replace ('{paper_release}','{page_cut}',$msg);
	$msg = stri_replace ('{page_cut}',"\n".chr(29).'V0',$msg);

	//non testato
	$msg = stri_replace ('{barcode_code39}',"\n".chr(29)."k4",$msg);
	$msg = stri_replace ('{/barcode_code39}',"\0",$msg);




	//PROVA ______________________________________________
	/*
	default line spacing ESC 2

	Set line spacing ESC 3 n (

	*/

	//$msg = stri_replace ('{prova}',chr(27)."3".chr(50),$msg);
	//$msg = stri_replace ('{/prova}',chr(27)."2",$msg);

	//PROVA ______________________________________________

	return $msg;
}

?>
