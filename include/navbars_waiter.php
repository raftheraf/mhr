<?php

//con il javascript onclick="return confirm(\'VUOI CANCELLARE?\')" l'operazione di cancellazione dell'ordine viene velocizzata
function navbar_trash($form='',$show_abort='',$start_data) {
	$msg = '
	<!-- function navbar_trash -->
	<table width="100%">
		<tr>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width="20%">
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0></a>
			</td>
			<td width="20%">
			</td>
			<td width="20%">';
				if(access_allowed(USER_BIT_CASHIER))
					$msg .='<a href="orders.php?command=delete&amp;data[id]='.$start_data['id'].'">';

				else
					$msg .='<a href="orders.php?command=delete&amp;data[id]='.$start_data['id'].'" onclick="return confirm (\'VUOI CANCELLARE L\\\'ORDINE?\')">';

				$msg .='<img src="'.IMAGE_TRASH.'" alt="'.ucfirst(phr('REMOVE')).'" border=0>
				</a>
			</td>
			<td width="20%">
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';

	$msg .= '
			</td>
			<td width="20%">
			';
	if(!empty($form))
		$msg .= '<a href="#" onclick="JavaScript:document.'.$form.'.submit(); return false"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0></a>';

	$msg .= '
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_empty($show_abort='') {
	$msg = '
	<!-- function navbar_empty -->
		<table width="100%">
			<tr>
				<td width="20%">
					<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
				</td>
				<td width="20%">
					<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0></a>
				</td>
				<td width="20%">';

	if(!empty($show_abort))
	$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';

	$msg .= '
				</td>
				<td width="20%">';

	if(access_allowed(USER_BIT_WAITER)){
		$msg .='<a href="#inizio_prenotati" onclick="FunctionLayerDivPrenotazioniIphone()">
						<img src="'.IMAGE_RICERCA_VELOCE.'" alt="Ricerca veloce" border=0></a>';}

	$msg .= '
				</td>
				<td width="20%"></td>
			';
	$msg .= '';

	$msg .= '
			</tr>
		</table>
	';
	return $msg;
}

function navbar_with_printer($show_abort='') {
	$msg = '
	<!-- function navbar_with_printer -->
	<table width="100%">
		<tr>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="Torna ai Tavoli" border=0></a>
			</td>
			<td width="20%">
				<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="Torna alla Comanda" border=0></a>
			</td>
			';
	$msg .= '
			<td width="20%">
					';
		if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';
	$msg .= '
				</td>';

	//Stampa diretta veloce o al volo
	/*$msg .= '
			<td width="20%"><a href="orders.php?command=print_orders"><img src="'.IMAGE_PRINT_FAST.'" alt="Stampa al volo" border=0></a>
			</td>
			'.
	*/

	$msg .= '
			<td width="20%">
			';
//PROBLEMA. Se attivo la funzione Ricerca Articoli nella pagina dishlist.tpl i link agli articoli
//					smettono di funzionare, il form richiamato 2 volte va in conflitto con il javascritp
//					necessario capire meglio il meccaniscmo
	if(access_allowed(USER_BIT_WAITER)){
		$msg .='<a href="#inizio_prenotati" onclick="FunctionLayerDivPrenotazioniIphone()">
						<img src="'.IMAGE_RICERCA_VELOCE.'" alt="Ricerca veloce" border=0></a>';}
	$msg .= '
			</td>';

	$msg .= '
			<td width="20%"><a href="orders.php?command=printing_choose">
				<img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0>
				</a>
			</td>
		';

	$msg .= '
		</tr>
	</table>
	';
	return $msg;
}

function navbar_tables_only() {
	$msg = '
	<!-- function navbar_tables_only -->
	<table width="100%">
		<tr>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_lock_retry($show_abort='') {
	$msg = '
	<!-- function navbar_lock_retry -->
	<table width="100%">
		<tr>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';

	$msg .= '
			</td>
			<td width="20%">
				<a href="orders.php"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_menu($show_abort='') {
	$msg = '
	<!-- function navbar_menu -->
	<table width="100%">
		<tr>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			</td>
			<td width="20%">
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';

	$msg .= '
			</td>
			<td width="20%">
				<a href="tables.php"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function navbar_form($form,$show_abort='') {
	$msg = '
			<!-- function navbar_form -->
			<table width="100%">
				<tr>
					<td width="20%">
						<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
					</td>
					<td width="20%" valign="middle">
			';


	if ((!access_allowed(USER_BIT_WAITER) OR access_allowed(USER_BIT_CONFIG)) && table_is_closed($_SESSION['sourceid'])) {
	$msg .= '
				<a href="orders.php?command=reopen_confirm"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			';
	}
	else {
	$msg .= '
			<a href="orders.php?command=list"><img src="'.IMAGE_SOURCE.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0></a>
			';
			}


	$msg .= '
					</td>

			<td width="20%" valign="middle">
			</td>

			<td width="20%">
			</td>

			<td width="20%">
			';
	if(!empty($show_abort))
		$msg .= '<a href="'.$show_abort.'"><img src="'.IMAGE_NO.'" alt="'.ucfirst(phr('NO')).'" border=0></a>';

	$msg .= '
			</td>
			<td width="20%">
				<a href="#" onclick="JavaScript:document.'.$form.'.submit(); myFunction(); return false;"><img src="'.IMAGE_OK.'" alt="'.ucfirst(phr('BACK_TO_TABLE')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	return $msg;
}

function command_bar_table_horizontal(){
	$output = '
		<!-- function command_bar_table_horizontal -->
		<table width="100%">
			<tr>
				<td align=center width="20%">
					<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
				</td>
				<td align=center width="20%">

				</td>

				<td align=center width="20%">';

				if( access_allowed(USER_BIT_CASHIER) AND table_is_takeaway($_SESSION['sourceid']) )
					$output .='<a href="orders.php?command=close"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0></a>';
				else
					$output .='<a href="orders.php?command=close" onclick="return confirm (\'VUOI CHIUDERE IL TAVOLO\')">
					<img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0></a>';

				$output .='
				</td>
				<td align=center width="20%">';

	if(access_allowed(USER_BIT_WAITER)){
	$output .='
					<a href="#inizio_prenotati" onclick="FunctionLayerDivPrenotazioniIphone()">
					<img src="'.IMAGE_RICERCA_VELOCE.'" alt="Ricerca veloce" border=0>
					</a>
						';
					}

	$output .= '
				</td>
				<td align=center width="20%">
					<a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0></a>
				</td>
			</tr>
		</table>
	';

	return $output;
}

function command_bar_table_vertical(){
	$output = '
	<!-- function command_bar_table_vertical -->
	<table>
		<tr>
			<td>
				<a href="tables.php"><img src="'.IMAGE_MENU.'" alt="'.ucfirst(phr('BACK_TO_TABLES')).'" border=0></a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="orders.php?command=printing_choose"><img src="'.IMAGE_PRINT.'" alt="'.ucfirst(phr('PRINT')).'" border=0></a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="orders.php?command=close_confirm"><img src="'.IMAGE_CLOSE.'" alt="'.ucfirst(phr('CLOSE_TABLE')).'" border=0></a>
			</td>
		</tr>
	</table>
	';
	return $output;
}

?>
