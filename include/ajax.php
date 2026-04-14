<?php

function dish_list_ajax ($start_data) {
	global $tpl;

	$_SESSION['order_added']=0;

	$tpl -> set_waiter_template_file ('ajax_dishlist');

//	$tmp = navbar_empty();
//	if(printing_orders_to_print($_SESSION['sourceid'])) $tmp = navbar_with_printer();
//	else $tmp = navbar_empty();
//	$tpl -> assign ('navbar',$tmp);


	if(/* get_conf(__FILE__,__LINE__,'creation_back_to_category') && */
	get_conf(__FILE__,__LINE__,"show_summary") &&
	isset($_SESSION['go_back_to_cat']) &&
	$_SESSION['go_back_to_cat']) {
		$tbl = new table ($_SESSION['sourceid']);
		if($last_mod = order_get_last_modified()) {
			$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
			$tbl -> list_orders ('last_order',$last_mod,$mods);
		}
	}

	if(isset($start_data['category'])){
		$tmp = dishlist_form_start_ajax($back_to_cat);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
//		$tmp = dishlist_back_to_cat();
//		$tpl -> assign ('back_to_cat',$tmp);
		$tmp = priority_radio_ajax($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list_ajax($start_data);
		$tpl -> assign ('quantity',$tmp);

		$tmp = dishes_list_cat_2cols_ajax ($start_data);
		$tpl -> assign ('dishes_list_2cols',$tmp);

//		$tmp = dishes_list_cat ($start_data);
//		$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_cat ();
		//$tpl -> append ('scripts',$tmp);

//		$tmp = categories_list_2cols();
//		$tpl -> assign ('categories2cols',$tmp);

//		$tmp = categories_list();
//		$tpl -> assign ('categories',$tmp);

//		$tmp = letters_list();
//		$tpl -> assign ('letters',$tmp);

	} elseif (isset($start_data['letter'])){
		$tmp = dishlist_form_start_ajax(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		if(!isset($start_data['priority']))$start_data['priority']=get_conf(__FILE__,__LINE__,"default_priority");
		$tmp = priority_radio_ajax($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list_ajax($start_data);
		$tpl -> assign ('quantity',$tmp);

//		$tmp = dishes_list_letter ($start_data);
//		$tpl -> assign ('dishes_list_2cols',$tmp);
//		$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_letters ();
		//$tpl -> append ('scripts',$tmp);

		//RTR
		$tmp = categories_list_2cols_ajax();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	} elseif (isset($start_data['search'])){
		$tmp = dishlist_form_start_ajax(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio_ajax($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list_ajax($start_data);
		$tpl -> assign ('quantity',$tmp);

	//	$tmp = dishes_list_search ($start_data);
	//	$tpl -> assign ('dishes_list_2cols',$tmp);
	//	$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_letters ();
		//$tpl -> append ('scripts',$tmp);

		//RTR
		$tmp = categories_list_2cols_ajax();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	} elseif (isset($start_data['idsystem'])){
		$tmp = dishlist_form_start_ajax(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio_ajax($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list_ajax($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = input_dish_id ($start_data);

		$tpl -> assign ('dishes_list_2cols',$tmp);

		$tpl -> assign ('dishes_list',$tmp);

		//RTR

		$tmp = categories_list_2cols_ajax();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);


		//rtr

	} else {
		$tmp = categories_list_2cols_ajax($start_data);
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list($start_data);
		$tpl -> assign ('categories',$tmp);

		$tmp = ucfirst(phr('ERROR_NO_CATEGORY_SELECTED'))."<br>\n";
		$tpl -> append ('messages',$tmp);

		//RTR

		$tmp = categories_list_2cols_ajax();
		$tpl -> assign ('categories2cols',$tmp);


		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);


		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	}
	return 0;
}


function categories_list_2cols_ajax($data=''){
	$output = '
<table id="tabellalistacategorie2colsajax" bgcolor="'.COLOR_TABLE_GENERAL.'">
';

	$query="SELECT * FROM `#prefix#categories` WHERE `deleted`='0' AND `visible`='1' ORDER BY ordine ASC";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$i=0;
	while ($arr = mysql_fetch_array ($res)) {
		$i++;
		$catid=$arr['id'];
		$cat = new category ($catid);
		$name=ucfirst($cat->name($_SESSION['language']));

		$backcommand="order_create1";
		$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$catid);

//Ajax modifiche
		$link = 'orders.php?command=dish_list_ajax&amp;data[category]='.$catid;
//		$link = 'orders.php?command=dish_list&amp;data[category]='.$catid;

		if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
		if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];

		if($i%2) {
			$output .= '
	<tr>';
		}

//Ajax modifiche
		$output .= '
		<td height="40px" bgcolor="'.$bgcolor.'" onclick="loadDish(\''.$link.'\');return(false);">';
//		$output .= '
//		<td height="40px" bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">';

		$output .= '

		<strong>'.$name.'</strong>

		</td>';

		if(($i+1)%2) {
			$output .= '
	</tr>';
		}

		/*
		$output .= '
		<td>
		&nbsp;
		</td>';
		*/
	}
	$output .= '
	</tbody>
</table>';

return $output;
}

function orders_list_ajax() {
	global $tpl;
	// RTR command
	// use session to decide wether to show the orders list or not true = show ; false=don't show
	// TODO: add get_conf here
	if(!isset($_SESSION['show_orders_list'])) $_SESSION['show_orders_list']=true;
	$show_orders=$_SESSION['show_orders_list'];

	unset($_SESSION['select_all']);

	$_SESSION['go_back_to_cat']=0;

	$user = new user($_SESSION['userid']);

	//in origine il codice era questo
	//if(table_is_closed($_SESSION['sourceid']) && !$user->level[USER_BIT_CASHIER])

	if(table_is_closed($_SESSION['sourceid']))
		{
		table_closed_interface();
		return 0;
		}

	$_SESSION['order_added']=0;
	$tpl -> set_waiter_template_file ('orders');
	 customer_data_form();

	if(table_is_takeaway ($_SESSION['sourceid'])) {
		$tpl -> set_waiter_template_file ('orders_takeaway');
		takeaway_form();
	}

	$table = new table ($_SESSION['sourceid']);
	$table->fetch_data(true);
	if($cust_id=$table->data['customer']) {
		$cust = new customer ($cust_id);
		$tmp = '';
		$tmp .= ' <a href="orders.php?command=customer_search"><img src="'.IMAGE_FIND.'" alt="CAMBIA CLIENTE" border=0 align="absmiddle" width="32px" height="32px"></a> ';
		$tmp .= '<b>'.$cust->data['surname'].' '.$cust->data['name'].'</b>';
		$tmp .= ' <a href="orders.php?command=set_customer&amp;data[customer]=0&amp;data[takeaway_surname]="" "><img src="'.IMAGE_LITTLE_TRASH.'" alt="CANCELLA" border=0 align="absmiddle" width="35px" height="35px"></a>';
		$tmp .= '<br><br>';
	} else {
		$tmp = '
		<FORM ACTION="orders.php?command=customer_search" METHOD=POST>
		<INPUT TYPE="submit" value="Inserisci cliente" class="button_big">
		</form>
		';
	}
	$tpl -> append ('commands',$tmp);

	if (!orders_service_fee_exists () && get_conf(__FILE__,__LINE__,'service_fee_use')) {
		$tmp = '
		<FORM ACTION="orders.php?command=create&amp;dishid='.SERVICE_ID.'" METHOD=POST>
		<INPUT TYPE="submit" value="Inserisci coperto" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}

	$associated_waiter = table_is_associated ();
	if (get_conf(__FILE__,__LINE__,"disassociation_allow")
		&& $associated_waiter && ($associated_waiter == $_SESSION ['userid'] || ($user->level[USER_BIT_CASHIER] && $user->level[USER_BIT_MONEY]) )
		) {
		$tmp = '
		<FORM ACTION="orders.php?command=dissociate" METHOD=POST>
		<INPUT TYPE="submit" value="Dissocia Tavolo" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}
	if ( $user->level[USER_BIT_CASHIER] OR access_allowed(USER_BIT_CONFIG) ) {
		$tmp = '
		<FORM ACTION="orders.php?command=ask_move" METHOD=POST>
		<INPUT TYPE="submit" value="SPOSTA TAVOLO" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}
	if (access_allowed(USER_BIT_CONFIG)) {
	$tmp = '
		<FORM ACTION="orders.php?command=ristampa_comanda" METHOD=POST>
		<INPUT TYPE="submit" value="RISTAMPA COMANDA" class="button_big">
		</form>
		';
		$tpl -> append ('commands',$tmp);
	}

	/* serve solo nquando il tavolo è chiuso da togliere
	if ($user->level[USER_BIT_CASHIER] && table_is_closed($_SESSION['sourceid'])) {
		$tmp = '
				<FORM ACTION="orders.php?command=reopen_confirm" METHOD=POST>
				<INPUT TYPE="submit" value=" RIAPRI TAVOLO " class="button_big">
				</form>
				';
		$tpl -> append ('commands',$tmp);
	}
	*/
	if ($_SESSION['show_orders_list']==false) $desc=ucfirst(phr('SHOW_ORDERS'));
	else $desc=ucfirst(phr('HIDE_ORDERS'));
	$tmp = '
			<FORM ACTION="orders.php?command=set_show_orders" METHOD=POST>
			<INPUT TYPE="submit" value="Vedi/Copri Ordine" class="button_big">
			</form>
			';
	$tmp .= '
			<FORM ACTION="orders.php?command=set_show_toplist" METHOD=POST>
			<INPUT TYPE="submit" value="TopList / Pers.List" class="button_big">
			</form>
			';

//RTR START attiva disattiva coperti
/*
	if ($user->level[USER_BIT_CASHIER])
	{

	$query=" SELECT value FROM `#prefix#conf` WHERE name='service_fee_use' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);

	$value=$arr['value'];
	$sourceid=$_SESSION['sourceid'];

			if($value==1)
			{
			$tmp .= '
				<form method="post" action="coperto_in_use.php">
				<INPUT TYPE="HIDDEN" NAME="sourceid" VALUE="'.$sourceid.'">
				<hr><i>Attiva/Disattiva la voce i Coperti in tutti i tavoli: </i><br>
				<b>COPERTO:</b>
				<select name="coperto">
				<option value="1" selected>ON</option>
				<option value="0" >OFF</option>
				</select>
				<input type="submit" value="CAMBIA">
				</form>
				';
			}
			else {
			$tmp .= '
				<form method="post" action="coperto_in_use.php">
				<INPUT TYPE="HIDDEN" NAME="sourceid" VALUE="'.$sourceid.'">
				<hr><i>Attiva/Disattiva la voce i Coperti in tutti i tavoli: </i><br>
				<b>COPERTO:</b>
				<select name="coperto">
				<option value="1" >ON</option>
				<option value="0" selected>OFF</option>
				</select>
				<input type="submit" value="CAMBIA">
				</form>
				';
			}
	$tmp .= '<hr>';
	}
*/
//RTR END

	$tpl -> append ('commands',$tmp);

	$tmp = categories_list_2cols_ajax();
	$tpl -> assign ('categories2cols',$tmp);

//	$tmp = categories_list();
//	$tpl -> assign ('categories',$tmp);


	$tmp = letters_list();
	$tpl -> assign ('letters',$tmp);

	if(CONF_FAST_ORDER){
		$tmp = order_fast_dishid_form ();
		$tpl -> assign ('fast_order_id',$tmp);
	} else {
	//eliminato il javascrip con che riconosce la pressione dei tasti
	//	$tmp = keys_orders ();
	//	$tpl -> append ('scripts',$tmp);
	}

	// use session to decide wether to show the orders list or not
	//se attivo la linea sotto visualizza per prima la toplist al primo accesso
	//if(!isset($_SESSION['show_toplist'])) $_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
	if($_SESSION['show_toplist']) {

		$tmp = toplist_show2cols();
		$tmp = toplist_show();

	}  elseif(get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		//$tmp = '<a href="orders.php?command=set_show_toplist">:: Mostra la TopList ::</a><br>';
		$tpl -> assign ('toplist2cols',$tmp);
		$tpl -> assign ('toplist',$tmp);
	}

	if(!$_SESSION['show_toplist']) {
		$tmp = personal_list_show2cols_ajax();
	}

	$tmp = command_bar_table_horizontal();
	$tpl -> assign ('horizontal_navbar',$tmp);

	$tmp = command_bar_table_vertical();
	$tpl -> assign ('vertical_navbar',$tmp);

	if($show_orders) {
		$table -> list_orders ();
	}

	if (get_conf(__FILE__,__LINE__,"show_summary")) {
		$query="SELECT * FROM `#prefix#orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$arr = mysql_fetch_array($res);
		$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
		$table -> list_orders ('last_order',$arr['id'],$mods);
	}
	return 0;
}

function personal_list_show2cols_ajax(){
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
		<INPUT TYPE="HIDDEN" NAME="command" VALUE="create" id="command">
		<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">

		<table id="tabellalistapersonale2col" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<tr><td colspan=4 height="40px" nowrap>
		<input type="hidden" id="dishpriority" value="1">
		';

		if(table_is_takeaway($_SESSION['sourceid'])){
			$tmp .= '
							<INPUT TYPE="HIDDEN" NAME="data[priority]" VALUE="1">
							';
		}else {
			$tmp .= '
							Priorità:
							<select name="data[priority]" size="1" class="button" onchange="setDishSelectedPriority(this.selectedIndex)">
							<option value="1" selected> 1 </option>
							<option value="2"					> 2 </option>
							<option value="3" 				> 3 </option>
							<option value="4" 				> 4 </option>
							</select>
							';
		}

	$qtydata['nolabel']=1;
	$tmp .= '<input type="hidden" id="dishquantity" value="1">';
	if(table_is_takeaway($_SESSION['sourceid'])){
		$tmp .= 'x1<input type="radio" name="data[quantity]" value="1" class="radio" checked >
						 x2<input type="radio" name="data[quantity]" value="2" class="radio"				 onclick="setDishSelectedQuantity(1)">
						 x3<input type="radio" name="data[quantity]" value="3" class="radio"				 onclick="setDishSelectedQuantity(2)">
						 x4<input type="radio" name="data[quantity]" value="4" class="radio"				 onclick="setDishSelectedQuantity(3)">
						 N°<input type="number" id="quantita_moltiplicata" name="data[quantita_moltiplicata]" value="" min="1" max="200" step="1" class="radio">
						';
	} else {
		$tmp .= ' ';
		$tmp .= 'Quantità: '.quantity_list_ajax($qtydata).'';
		$tmp .= '';
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
										bgcolor="'.$bgcolor.'" onclick="dishOrder(\''.$dishid.'\',\'personal_list_form\'); return false;">
											<b>'.$dishprice.'</b>
								</td>
								<td></td>';
		} else {
			$tmp .= '	<td style="width:48%; height:40px; cursor: pointer"
										onMouseOver="this.style.background=\'#f1f1f1\'"
										onMouseOut="this.style.background=\''.$bgcolor.'\'"
										bgcolor="'.$bgcolor.'" onclick="dishOrder(\''.$dishid.'\',\'personal_list_form\'); return false;">'
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

function lista_ordini_ajax() {
	global $tpl;
	// RTR command
	// use session to decide wether to show the orders list or not true = show ; false=don't show
	// TODO: add get_conf here
	if(!isset($_SESSION['show_orders_list'])) $_SESSION['show_orders_list']=true;
	$show_orders=$_SESSION['show_orders_list'];

	unset($_SESSION['select_all']);

	$_SESSION['go_back_to_cat']=0;

	$user = new user($_SESSION['userid']);

	//in origine il codice era questo
	//if(table_is_closed($_SESSION['sourceid']) && !$user->level[USER_BIT_CASHIER])

	if(table_is_closed($_SESSION['sourceid']))
		{
		table_closed_interface();
		return 0;
		}

	$_SESSION['order_added']=0;
	$tpl -> set_waiter_template_file ('ajax_orders');
	 customer_data_form();

	if(table_is_takeaway ($_SESSION['sourceid'])) {
		$tpl -> set_waiter_template_file ('ajax_orders_takeaway');
		takeaway_form();
	}

	$table = new table ($_SESSION['sourceid']);
	$table->fetch_data(true);

	$associated_waiter = table_is_associated ();


		$table -> list_orders ();

	if (get_conf(__FILE__,__LINE__,"show_summary")) {
		$query="SELECT * FROM `#prefix#orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$arr = mysql_fetch_array($res);
		$mods=get_conf(__FILE__,__LINE__,"show_mods_in_summary");
		$table -> list_orders ('last_order',$arr['id'],$mods);
	}
	return 0;
}


function dishes_list_cat_2cols_ajax($data){
	$output = '';

	$cat = new category($data['category']);
	//RTG: always exists???
	//if(!$cat->exists()) $data['category']=-1;

	if ($data['category']<=0) {
		if(get_conf(__FILE__,__LINE__,"invisible_show"))
			$query="SELECT #prefix#dishes.*, #prefix#dishes#lang#.table_name
			FROM `#prefix#dishes`
			JOIN `#prefix#dishes#lang#`
			WHERE #prefix#dishes.id=#prefix#dishes#lang#.table_id
			AND #prefix#dishes.deleted='0'
			ORDER BY category ASC, table_name ASC";
		else
			$query="SELECT #prefix#dishes.*, #prefix#dishes#lang#.table_name
			FROM `#prefix#dishes`
			JOIN `#prefix#dishes#lang#`
			WHERE `visible`='1'
			AND #prefix#dishes.id=#prefix#dishes#lang#.table_id
			AND #prefix#dishes.deleted='0'
			ORDER BY category ASC, table_name ASC";
	} else {
		if(get_conf(__FILE__,__LINE__,"invisible_show")) $query="SELECT #prefix#dishes.*, #prefix#dishes#lang#.table_name FROM `#prefix#dishes` JOIN `#prefix#dishes#lang#` WHERE category='".$data['category']."' AND #prefix#dishes.id=#prefix#dishes#lang#.table_id ORDER BY table_name ASC";
		else $query="SELECT #prefix#dishes.*, #prefix#dishes#lang#.table_name
			FROM `#prefix#dishes`
			JOIN `#prefix#dishes#lang#`
			WHERE category='".$data['category']."'
			AND `visible`='1'
			AND #prefix#dishes.id=#prefix#dishes#lang#.table_id
			AND #prefix#dishes.deleted='0'
			ORDER BY table_name ASC";

		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$data['category']);
		$dishcat=$data['category'];
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$output .= '
	<table bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">

	';

	// ascii letter A
	//$i=65;
	//unset($GLOBALS['key_binds_letters']);
	$a=0;
	while ($arr = mysql_fetch_array ($res)) {
		$a++;
		$dishid = $arr['id'];
		//RTG: no more queries, we have the data
		//$dishobj = new dish ($arr['id']);
		//$dishname = $dishobj -> name ($_SESSION['language']);
		$dishname = $arr['table_name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
			$dishname = $arr['name'];

		$dishprice = $arr['price'];

		if($data['category']<=0) {
			$dishcat = $arr['category'];
			debug_msg(__FILE__,__LINE__,"dishcat: $dishcat");
			$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories',"htmlcolor",$dishcat);
			debug_msg(__FILE__,__LINE__,"class: $class");
		}

		if ($dishcat>0){
			// letters array follows
			//if($i<91) {
			//	$GLOBALS['key_binds_letters'][$i]=$dishid;
			//	$letter = chr($i);
			//	$i++;
			//} else
			//$letter='';


			if($a%2) {
			$output .= '
				<tr style="height:35px">';
			}

			$output .= '
					<td style="font-size: 8px" bgcolor="'.$class.'">'.$arr['id'].'</td>
					<td style="cursor: pointer" bgcolor="'.$class.'"
						onMouseOver = "this.style.background=\'#f1f1f1\'"
						onMouseOut = "this.style.background=\''.$class.'\'"
						onclick="dishOrder('.$dishid.',\'order_form\'); return false;">'
					.$dishname.'</td>
				<td align="right" bgcolor="'.$class.'">'.$dishprice.'</td>
				<td> </td>
			';

		if(($a+1)%2) {
			$output .= '
			</tr>';
			}
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function priority_radio_ajax ($data) {
	// code modded from get to post. !!CHECK THIS!!

	if(table_is_takeaway($_SESSION['sourceid'])) {
		$output = '
	<input type="hidden" name="data[priority]" value="1" id="data[priority]">';
		return $output;
	}

	// if((!isset($data['priority']) || !$data['priority']) && table_is_takeaway($_SESSION['sourceid'])) $data['priority']=1;

	if((!isset($data['priority']) || !$data['priority']) && $data['category']) {
		$cat = new category ($data['category']);
		if ($cat->data['priority']) $data['priority']=$cat->data['priority'];
		elseif ($tmp=get_conf(__FILE__,__LINE__,"default_priority")) $data['priority']=$tmp;
	}

	for ($i=1;$i<4;$i++) $chk[$i]="";
	if(isset($data['priority'])) $chk[$data['priority']]="checked";
	// RTR dishlist priority
	// alternativa per link premendo qualunque punto della cella
	// <td align="center" onClick="check_prio(\'order_form\',0);return false;" bgcolor="'.COLOR_ORDER_PRIORITY_1.'">
	$output = '
	<input type="hidden" id="dishpriority" value="'.$data['priority'].'">
	<!-- function priority_radio_ajax -->

	<table width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<tr>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_1.'">1
				<input type="radio" '.$chk[1].' name="data[priority]" value="1" class="radio" onclick="setDishSelectedPriority(0)">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_2.'">2
				<input type="radio" '.$chk[2].' name="data[priority]" value="2" class="radio" onclick="setDishSelectedPriority(1)">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_3.'">3
				<input type="radio" '.$chk[3].' name="data[priority]" value="3" class="radio" onclick="setDishSelectedPriority(2)">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_4.'">4
				<input type="radio" '.$chk[4].' name="data[priority]" value="4" class="radio" onclick="setDishSelectedPriority(3)">
			</td>
		</tr>
	</table>
	';
	return $output;
}

function quantity_list_ajax($data=array()) {
	// code modded from get to post. !!CHECK THIS!!
	$tmp = '<!--function quantity_list_ajax-->';

	$default_quantity=get_conf(__FILE__,__LINE__,"default_quantity");
	$selected_qty = $default_quantity;

	if (isset($data['quantity']) && $data['quantity']>0) $selected_qty = $data['quantity'];

	if (!isset($data['nolabel']) || !$data['nolabel']) $tmp .= 'Q.tà:';
	$tmp .= '

		<select name="data[quantity]" size="1" class="button" onchange="setDishSelectedQuantity(this.selectedIndex)">';
			for ($i=1; $i<=MAX_QUANTITY; $i++) {
				if ($i==$selected_qty) $selected = ' selected';
				else $selected = '';

		$tmp .= '
		<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
	}
	$tmp .= '
	</select>';
	return $tmp;
}


function dishlist_form_start_ajax($back_to_cat=false) {
	$output = '
	<form action="orders.php" method="POST" name="order_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create" id="command">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">
	<input type="hidden" name="from_category" value="1">
	<input type="HIDDEN" value="1" id="dishquantity">
	';

	return $output;
}

?>
