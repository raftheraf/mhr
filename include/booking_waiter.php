<?php

//Lista prenotazioni
function prenotazioni(){

	$output = '';

	$query = "SELECT * FROM `#prefix#sources`";
	$query .= "WHERE `takeaway_surname`!='' OR `customer`!='0' OR `nota_tavolo`!=''";
	$query .= "ORDER BY `takeaway_surname` ASC ";


	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

		if(!mysql_num_rows($res)) {
		$output .= '<br><div align=center><B>Nessun tavolo prenotato</B></div><br><br><br>';
		return $output;
		}

		$output .= '<div>
								<input class="form-control" type="text" id="reservationListInput" onkeyup="RicercaPrenotatiFunction()" placeholder="Ricerca nelle prenotazioni..">
							';
		$output .= '<table class="booking" id="reservationTable">
						<colgroup>
						<col class="col1">
						<col class="col2 odd">
						<col class="col3">
						<col class="col4 odd">
						<col class="col5">
						<col class="col6" odd>
						</colgroup>
						<thead>
						<tr class="header">
							<th>Cliente</th>
							<th>Telefono</th>
							<th>Orario</th>
							<th>Tav</th>
							<th>Cop</th>
							<th>Note</th>
						</tr>
						</thead>
						<tbody>
							';

		$i = 0;
		while ($arr = mysql_fetch_array ($res)) {

		$sourceid=$arr['id'];
		$tablenum=$arr['name'];
		$takeaway_surname=$arr['takeaway_surname'];
		$nota_tavolo=$arr['nota_tavolo'];
		$prefisso_telefono=$arr['prefix_telefono'];
		$numero_telefono=$arr['telefono'];
		$ora_prenotazione=$arr['ora_prenotazione'];
		//RTR nome del cliente sui tavoli
		$customer=$arr['customer'];
		$totale_coperti_per_tavolo =  totale_coperti_per_tavolo($sourceid);
		if ($customer==0)
		{$customer_surname=$takeaway_surname;}
		else {
		$customer_surname=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"surname",$customer);
		}
		$customer_name=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'customers',"name",$customer);

		$link = 'orders.php?data[sourceid]='.$sourceid;
		if(isset($command) && !empty($command)) $link .= '&amp;command='.$command;

		$output .= '<tr>';
		$output .= '<td height="30px"><a href="'.$link.'">';
		//$output .= '<a href="'.$link.'">';
		//$output .= $takeaway_surname.' ';
		$output .= $customer_surname.' ';
		$output .= $customer_name;
		$output .= '</a>';
		$output .= '</td>';
		$output .= '<td align="center"><a href="'.$link.'">+'.$prefisso_telefono.' '.$numero_telefono.'</a></td>';
		$output .= '<td align="center">'.$ora_prenotazione.'</td>';
		$output .= '<td align="center"><a href="'.$link.'">'.$tablenum.'</a></td>';
		$output .= '<td align="center">'.$totale_coperti_per_tavolo.'</td>';
		$output .= '<td align="left">'.$nota_tavolo.'</td>';
		$output .= '</tr>';
		$i++;
		}

	$output .= '</tbody>
				</table>
				</div>
				<br><br><br>';

	return $output;
	}

//Funzione attiva disattiva coperti
function apri_chiudi_coperti(){
	$selected_on='';
	$selected_off='';
	$query="SELECT value FROM `#prefix#conf` WHERE name='service_fee_use' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);

	$value=$arr['value'];
	if($value==1) $selected_on='selected';
	if($value==0) $selected_off='selected';

			$output = '';
			$output .= '
				<div>
					<form name="form1" method="post" action="coperto_in_use.php">
						<select name="coperto" onChange="document.form1.submit()">
						<option value="1" '.$selected_on.'>Coperti ON</option>
						<option value="0" '.$selected_off.'>Copperti OFF</option>
					</select>

					</form>
				</div>
				';
	return $output;
	}

function barra_booking(){
$output = '';
$output .= '<table border="0" cellpadding="10px" cellspacing="10px">
			<tr>
			<td bgcolor="#679FC5" align="left"  onclick="redir(\''.ROOTDIR.'/waiter/tables.php\');return(false);"><b><a href="'.ROOTDIR.'/waiter/tables.php"><<--Tavoli</a></b></td>
			<td> </td>
			<td bgcolor="#C4E3F7" align="right"  onclick="redir(\''.ROOTDIR.'/waiter/booking.php\');return(false);"><b><a href="'.ROOTDIR.'/waiter/booking.php">Prenotazioni-->></a></b></td>
			</tr>
			</table>';

	return $output;
}
?>
