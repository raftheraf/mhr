<?php
function personal_list_show(){
	global $tpl;

	$_SESSION['order_added']=0;
	//$query = "SELECT * FROM `#prefix#dishes` WHERE `personal_list` = '1' AND `visible` = '1' ORDER BY personal_list_order, name ASC";

	//condizione per la lista asporto_list
	if(table_is_takeaway($_SESSION['sourceid'])){
		$query = "SELECT * FROM `#prefix#dishes` WHERE `asporto_list` = '1' AND `visible` = '1'ORDER BY asporto_list_order, name ASC";
		}
		else
		$query = "SELECT * FROM `#prefix#dishes` WHERE `personal_list` = '1' AND `visible` = '1'ORDER BY personal_list_order, name ASC";
	//fine condizione


	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) {
	$tmp .= '<br><br><div align=center><b>Nessun Piatto </b><br>è presente nella Lista Personalizzata</div>';
	$tpl -> assign ('toplist',$tmp);
	return 0;
	}

	$chk[1]="";
	$chk[2]="";
	$chk[3]="";
	$chk[4]="";

	$tmp .= '
	<form action="orders.php" method="POST" name="personal_list_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">

	<table id="tabellalistapersonale" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'">
	<tr><td colspan=2 height="40px" nowrap>';

	if(table_is_takeaway($_SESSION['sourceid'])){
		$tmp .= '
						<INPUT TYPE="HIDDEN" NAME="data[priority]" VALUE="1">
						';
	}else {
		$tmp .= '
			P:
			<select name="data[priority]" size="1" class="button">
				<option value="1" selected> 1 </option>
				<option value="2"> 2 </option>
				<option value="3"> 3 </option>
				<option value="4"> 4 </option>
				</select>';
			}

	$qtydata['nolabel']=1;

	if(table_is_takeaway($_SESSION['sourceid'])){
			$tmp .= '
							<script>
								function cancella(){
									var x = document.getElementById("quantita_moltiplicata").value="";
									}
							</script>
							 x1<input type="radio" checked name="data[quantity]" value="1" class="radio" onClick="cancella()">
							 x2<input type="radio" name="data[quantity]" value="2" class="radio" onClick="cancella()">
							 x3<input type="radio" name="data[quantity]" value="3" class="radio" onClick="cancella()">
							 N°<input type="number" name="data[quantita_moltiplicata]" value="" min="1" max="200" step="1" class="radio" id="quantita_moltiplicata">
							';
		} else {
					$tmp .= ' - ';
					$tmp .= 'Q.tà:'.quantity_list($qtydata).'';
					$tmp .= '</td></tr>';
		}

	while ($arr = mysql_fetch_array ($res)) {

	$category = $arr['category'];
	$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$category);

	$dishid = $arr['id'];
	$disname = $arr['name'];
	$dishprice = $arr['price'];
	//$bgcolor = 'Yellow';



	$tmp .= '<tr bgcolor="'.$bgcolor.'">';

/* if(table_is_takeaway($_SESSION['sourceid'])){

	 $tmp .= '<td height="40px" style="cursor: pointer"
								 onMouseOver="this.style.background=\'#f1f1f1\'"
								 onMouseOut="this.style.background=\''.$bgcolor.'\'"
								 bgcolor="'.$bgcolor.'" onclick="order_select(\''.$dishid.'\',\'personal_list_form\');">'
									 .$dishprice.
						 '</td>';
 } else { */
	 $tmp .= '<td height="40px" style="cursor: pointer"
								 onMouseOver="this.style.background=\'#f1f1f1\'"
								 onMouseOut="this.style.background=\''.$bgcolor.'\'"
								 bgcolor="'.$bgcolor.'" onclick="order_select(\''.$dishid.'\',\'personal_list_form\');">'
									 .$disname.
						 '</td>';
	 $tmp .= '<td bgcolor="'.$bgcolor.'" style="text-align:right;"><b>'.$dishprice.' </b></td>';
 //}

	$tmp .= '</tr>';
//.'<a href="#" onclick="JavaScript:order_select(\''.$dishid.'\',\'personal_list_form\'); return false;">'.$disname.'</a>'.
	$tmp .= '';

	$i++;

	}

	$tmp .= '
	</table>
	</form>
	';

	$tpl -> assign ('toplist',$tmp);
	return 0;
}

//LISTA A 2 COLONNE
function personal_list_show2cols(){
	global $tpl;

	$_SESSION['order_added']=0;

	//condizione per la lista asporto_list
	if(table_is_takeaway($_SESSION['sourceid'])){
		$query = "SELECT * FROM `#prefix#dishes` WHERE `asporto_list` = '1' AND `visible` = '1'ORDER BY asporto_list_order, name ASC";
		}
		else
		$query = "SELECT * FROM `#prefix#dishes` WHERE `personal_list` = '1' AND `visible` = '1'ORDER BY personal_list_order, name ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) {
	$tmp .= '<br><br><div align=center><B>Nessun Piatto </B><br>è presente nella Lista Personalizzata</div>';
	$tpl -> assign ('toplist2cols',$tmp);
	return 0;
	}

	$chk[1]="";
	$chk[2]="";
	$chk[3]="";
	$chk[4]="";

	$tmp .= '
		<form action="orders.php" method="POST" name="personal_list_form">
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
		<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">

		<table id="tabellalistapersonale2col" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<tr><td colspan=4 height="40px" nowrap>';

		if(table_is_takeaway($_SESSION['sourceid'])){
			$tmp .= '
							<INPUT TYPE="HIDDEN" NAME="data[priority]" VALUE="1">
							';
		}else {
			$tmp .= '
							Priorità:
							<select name="data[priority]" size="1" class="button">
							<option value="1" selected> 1 </option>
							<option value="2"> 2 </option>
							<option value="3"> 3 </option>
							<option value="4"> 4 </option>
							</select>
							';
		}

	$qtydata['nolabel']=1;

	if(table_is_takeaway($_SESSION['sourceid'])){
		$tmp .= '
						<script>
							function cancella(){
								var x = document.getElementById("quantita_moltiplicata").value="";
							}
						</script>

						 x1<input type="radio" checked name="data[quantity]" value="1" class="radio" onClick="cancella()">
						 x2<input type="radio" name="data[quantity]" value="2" class="radio" onClick="cancella()">
						 x3<input type="radio" name="data[quantity]" value="3" class="radio" onClick="cancella()">
						 x4<input type="radio" name="data[quantity]" value="4" class="radio" onClick="cancella()">
						 N°<input type="number" name="data[quantita_moltiplicata]" value="" min="1" max="200" step="1" class="radio" id="quantita_moltiplicata">
						';
	} else {
		$tmp .= ' ';
		$tmp .= 'Quantità: '.quantity_list($qtydata).'';
		$tmp .= '</td></tr>';
	}


	$tmp .= '</td></tr>';

	$a=0;
	while ($arr = mysql_fetch_array ($res)) {
	$a++;
	$category = $arr['category'];
	$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$category);

	$dishid = $arr['id'];
	$disname = $arr['name'];
	$dishprice = $arr['price'];
	//$bgcolor = 'Yellow';

	if($a%2) { $tmp .= '<tr>'; }

		if(table_is_takeaway($_SESSION['sourceid'])) {
			$tmp .= '	<td style="width:48%; height:40px; cursor: pointer; text-align:center; border: solid; border-width: 1px;"
										onMouseOver="this.style.background=\'#f1f1f1\'"
										onMouseOut="this.style.background=\''.$bgcolor.'\'"
										bgcolor="'.$bgcolor.'" onclick="order_select(\''.$dishid.'\',\'personal_list_form\');">
											'.$disname.'</br><b>'.$dishprice.'</b>
								</td>
								<td></td>';
		} else {
			$tmp .= '	<td style="width:48%; height:40px; cursor: pointer"
										onMouseOver="this.style.background=\'#f1f1f1\'"
										onMouseOut="this.style.background=\''.$bgcolor.'\'"
										bgcolor="'.$bgcolor.'" onclick="order_select(\''.$dishid.'\',\'personal_list_form\'); return false;">'
											.$disname.
							' </td>
								<td></td>';
		}

	//.'<a href="#" onclick="JavaScript:order_select(\''.$dishid.'\',\'personal_list_form\'); return false;"></a>'

	if(($a+1)%2) {$tmp .= '</tr>';}
	$tmp .= '';
	$i++;

	}

	$tmp .= '
	</table>
	</form>
	';

	$tpl -> assign ('toplist2cols',$tmp);
	return 0;
}

?>
