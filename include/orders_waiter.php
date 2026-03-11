<?php
function order_last_modified_mods () {
if (!$deleted
				&& $arr['printed']==NULL
				&& $arr['dishid']!=MOD_ID
				&& $arr['dishid']!=SERVICE_ID) {
			$link = 'orders.php?command=listmods&amp;data[id]='.$arr['associated_id'];
			$output .= '
		<td bgcolor="'.$class.'" onclick="redir(\''.$link.'\');">
			<a href="'.$link.'">+ -</a>
		</td>';

		}
}

function order_last_modified_links () {
	$ret=array();

	$query="SELECT * FROM `#prefix#orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);

	if((!$arr['printed'] && $arr['dishid']!=MOD_ID) || $arr['dishid']==SERVICE_ID){
		$link = 'orders.php?command=ask_delete&data[id]='.$arr['id'];
		if($arr['suspend']) $link .= '&data[suspend]=1';
		if($arr['extra_care']) $link .= '&data[extra_care]=1';
		$ret[0]=$link;

		for($i=1;$i<10;$i++) {
			$link = 'orders.php?command=update&data[quantity]='.$i.'&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[$i]=$link;
		}

		$newquantity=$arr['quantity']+1;
		$link = 'orders.php?command=update&data[quantity]='.$newquantity.'&data[id]='.$arr['id'];
		if($arr['suspend']) $link .= '&data[suspend]=1';
		if($arr['extra_care']) $link .= '&data[extra_care]=1';
		$ret[10]=$link;

		if($arr['quantity']>1){
			$newquantity=$arr['quantity']-1;
			$link = 'orders.php?command=update&data[quantity]='.$newquantity.'&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[11]=$link;
		} elseif($arr['quantity']==1 && CONF_ALLOW_EASY_DELETE){
			$link = 'orders.php?command=ask_delete&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
			$ret[11]=$link;
		} else $ret[11]='';
	} else {
		for($i=1;$i<10;$i++) $ret[$i]='';
		$ret[10]='';
		$ret[11]='';
	}

	$ret[-1]='tables.php';

	return $ret;
}

function order_last_modified_decrease () {
	$query="SELECT * FROM `#prefix#orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array($res);

	if((!$arr['printed'] && $arr['dishid']!=MOD_ID) || $arr['dishid']==SERVICE_ID){
		if($arr['quantity']>1){
			$newquantity=$arr['quantity']-1;
			$link = 'orders.php?command=update&data[quantity]='.$newquantity.'&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
		} elseif($arr['quantity']==1 && CONF_ALLOW_EASY_DELETE){
			$newquantity=0;
			$link = 'orders.php?command=ask_delete&data[id]='.$arr['id'];
			if($arr['suspend']) $link .= '&data[suspend]=1';
			if($arr['extra_care']) $link .= '&data[extra_care]=1';
		}
		return $link;
	}
	return '';
}

function order_get_last_modified() {
	$query="SELECT `id` FROM `#prefix#orders`WHERE `sourceid`='".$_SESSION['sourceid']."' AND `id`=`associated_id` ORDER BY `timestamp` DESC LIMIT 1";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	$arr = mysql_fetch_array($res);
	return $arr['id'];
}

function dish_list ($start_data) {
	global $tpl;

	$_SESSION['order_added']=0;

	$tpl -> set_waiter_template_file ('dishlist');

	$tmp = navbar_empty();
	if(printing_orders_to_print($_SESSION['sourceid'])) $tmp = navbar_with_printer();
	else $tmp = navbar_empty();
	$tpl -> assign ('navbar',$tmp);


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
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = dishlist_back_to_cat();
		$tpl -> assign ('back_to_cat',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);

		$tmp = dishes_list_cat_2cols ($start_data);
		$tpl -> assign ('dishes_list_2cols',$tmp);

		$tmp = dishes_list_cat ($start_data);
		$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_cat ();
		//$tpl -> append ('scripts',$tmp);

		$tmp = categories_list_2cols();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

	} elseif (isset($start_data['letter'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		if(!isset($start_data['priority']))$start_data['priority']=get_conf(__FILE__,__LINE__,"default_priority");
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);

		$tmp = dishes_list_letter ($start_data);
		$tpl -> assign ('dishes_list_2cols',$tmp);
		$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_letters ();
		//$tpl -> append ('scripts',$tmp);

		//RTR
		$tmp = categories_list_2cols();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	} elseif (isset($start_data['search'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);

		$tmp = dishes_list_search ($start_data);
		$tpl -> assign ('dishes_list_2cols',$tmp);
		$tpl -> assign ('dishes_list',$tmp);

		//eliminato il javascript che attiva la selezione con la tastiera
		//$tmp = keys_dishlist_letters ();
		//$tpl -> append ('scripts',$tmp);

		//RTR
		$tmp = categories_list_2cols();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	} elseif (isset($start_data['idsystem'])){
		$tmp = dishlist_form_start(false);
		$tpl -> assign ('formstart',$tmp);
		$tmp = dishlist_form_end();
		$tpl -> assign ('formend',$tmp);
		$tmp = priority_radio($start_data);
		$tpl -> assign ('priority',$tmp);
		$tmp = quantity_list($start_data);
		$tpl -> assign ('quantity',$tmp);
		$tmp = input_dish_id ($start_data);

		$tpl -> assign ('dishes_list_2cols',$tmp);

		$tpl -> assign ('dishes_list',$tmp);

		//RTR

		$tmp = categories_list_2cols();
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);

		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);


		//rtr

	} else {
		$tmp = categories_list_2cols($start_data);
		$tpl -> assign ('categories2cols',$tmp);

		$tmp = categories_list($start_data);
		$tpl -> assign ('categories',$tmp);

		$tmp = ucfirst(phr('ERROR_NO_CATEGORY_SELECTED'))."<br>\n";
		$tpl -> append ('messages',$tmp);

		//RTR

		$tmp = categories_list_2cols();
		$tpl -> assign ('categories2cols',$tmp);


		$tmp = categories_list();
		$tpl -> assign ('categories',$tmp);


		$tmp = letters_list();
		$tpl -> assign ('letters',$tmp);

		//rtr

	}
	return 0;
}

function order_fast_dishid_form () {
	$data['nolabel']=1;
	$data['priority']=1;
	$tmp = dishlist_form_start(false);
	$tmp .= priority_radio($data);
	$tmp .= quantity_list($data);
	// input_dish_id non richiede dati specifici, passa array vuoto per evitare Notice
	$tmp .= input_dish_id(array());
	$tmp .= dishlist_form_end();
	return $tmp;
}

function order_price_modify($id) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);

	$query="SELECT * FROM `#prefix#orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr = mysql_fetch_array($res);
	if(!$arr) return ERR_ORDER_NOT_FOUND;

	$dishid=$arr['dishid'];
	$generic=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes',"generic",$dishid);
	$pricetot=$arr['price'];
	$quantity=$arr['quantity'];
	//$price=$pricetot/$quantity;
	$price=$pricetot;
	$price=sprintf("%01.2f",$price);

	$tmp='';
	if($generic) $tmp .= ucfirst(phr('GENERIC_PRICE_DESCRIPTION'))."<br>\n";
	$tmp .= ucfirst(phr('GENERIC_PRICE_INSTRUCTION')).' '.ucfirst(phr('UPDATE_PRICE')).'<br>
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="update">
	<input type="hidden" name="data[id]" value="'.$id.'">
	<input type="hidden" name="data[quantity]" value="'.$quantity.'"><br/><br/>
	<input type="text" size="8" maxlength="8" name="data[price]" value="'.$price.'" class="input"><br/><br/><br/>
	<input type="submit" value="'.ucfirst(phr('UPDATE_PRICE')).'" class="button_big">
	</form>
	<br>';
	$tpl -> assign ('question',$tmp);

	return 0;
}

function order_is_mod($id){
	/*
	Return codes:
	0. no valid record or it's not MOD_ID
	1. found SERVICE_ID
	2. found MOD_ID
	*/
	$query="SELECT * FROM `#prefix#orders` WHERE `id`=$id";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return 0;

	$arr=mysql_fetch_array($res);
	$dishid=$arr['dishid'];

	if($dishid==SERVICE_ID) return 1;
	if($dishid==MOD_ID) return 2;

	return 0;
}

function order_has_mods($id){
	$query="SELECT * FROM `#prefix#orders` WHERE `associated_id`='$id' AND `id`!='$id'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return mysql_num_rows($res);
}

function order_find_incrementable ($dishid,$priority){
	$query="SELECT * FROM `#prefix#orders`
	 WHERE `sourceid`='".$_SESSION['sourceid']."'
	 AND `dishid`='$dishid'";
	 $res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	while ($arr = mysql_fetch_array($res)) {
		if(!order_has_mods($arr['id'])
			&& $arr['priority']==$priority
			&& $arr['suspend']==0
			&& $arr['extra_care']==0
			&& $arr['printed']==NULL
			&& $arr['deleted']==0) return $arr['id'];
	}
	return 0;
}


function order_found_generic_not_priced($sourceid){
	$query="SELECT * FROM `#prefix#orders`
			JOIN `#prefix#dishes`
			WHERE #prefix#dishes.id=#prefix#orders.dishid
			AND #prefix#dishes.generic='1'
			AND #prefix#orders.sourceid = '.$sourceid.'
			AND #prefix#orders.price = '0'
			AND #prefix#orders.printed IS NOT NULL
			AND #prefix#orders.deleted='0'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	return mysql_num_rows($res);
}

function price_calc ($num,$correction=0) {
	if ($num<1) return 0;

// echo '$correction: '.$correction.'<br>';
	$autocalc = new autocalc ();
	$maxquantity = $autocalc -> max_quantity();
	// no value is set
	if($maxquantity==-1) return 0;

	$autocalc = array();

	$query="SELECT * FROM `#prefix#autocalc`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	while($arr = mysql_fetch_array($res)) {
		$autocalc [$arr['quantity']] = $arr['price'];
	}

	ksort($autocalc);

	$maxquantity = $maxquantity - $correction;
// echo '$maxquantity: '.$maxquantity.'<br>';

	//echo 'pre: '.var_dump_table($autocalc);
	foreach($autocalc as $key => $value) {
		$newindex=$key-$correction;
		if($newindex<1 && $key!=0)
			unset($autocalc[$key]);
		elseif($key!=0 && $newindex!=$key) {
			$autocalc[$newindex]=$autocalc[$key];
//echo '$autocalc['.$newindex.']: '.$autocalc[$newindex].'<br>';
			unset($autocalc[$key]);
		}
	}
// echo '$num: '.$num.'<br>';
// echo 'array_key_exists($num,$autocalc): '.array_key_exists($num,$autocalc).'<br>';
// echo '$autocalc: '.var_dump_table($autocalc);
	// quantity found, it is in the array
	if(array_key_exists($num,$autocalc)) {
		ksort($autocalc);

		$sum=0;
		foreach($autocalc as $key => $value) {
			if($key<=$num && $key>0)
			$sum += $value;
		}
// echo '$sum: '.$sum.'<br>';
		return $sum;
	}

	// quantity not found, we look for the highest quantiy available,
	// then add the remaining price (based on the 0 quantity record)

	$sum=0;
	foreach($autocalc as $key => $value) {
		if($key!=0)
		$sum += $value;
	}

	$sum += $autocalc [0] * ($num-$maxquantity);

	return $sum;
}

// RTR START
// Funzione Genera Menu Fisso
//__________________________________________________________
function orders_create ($dishid,$input_data=array()) {
	global $tpl;

	if(!isset($input_data['quantity'])) {
		$input_data['quantity']=1;
		echo '<br> quantità non inserita!';
	}
	$existing = 0;

	// tutti gli articoli con menu fisso attivo vengono assegnati a priorità 1 per poterli sommare tra loro
	if(controlla_menu_fisso($dishid)) $input_data['priority']=1;

	//Controlla se il piatto ha il flag salta_stampa=1 allora assegna come già stampato
	if(controlla_salta_stampa($dishid)) $input_data['printed']=date("Y-m-d H:i:s");

	if(isset($input_data['priority']) && $input_data['priority']) $existing = order_find_incrementable ($dishid,$input_data['priority']);
	if($existing) {
		// the order already exists, so updates the old one instead of creating a new identical
		$existing= (int) $existing;
		$ord = new order($existing);
		$data_old = $ord -> data;
		$data_old['quantity'] = $data_old['quantity'] + $input_data['quantity'];
		if($err = orders_update ($data_old)) return 0;

		return $ord -> id;
	}

	$ord = new order();
	$ord -> prepare_default_array ($dishid, $input_data);
	if($err = $ord -> create ()) return 0;

	// insert all the modules interfaces for order creation here
	toplist_insert ($dishid, $input_data['quantity']);

	//don't move stock from here!
	if(class_exists('stock_object') && isset($input_data['quantity'])) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($ord->id,$input_data['quantity']);
	}
	// set_stock_from_id($ord->id,$input_data['quantity']);

	// end interfaces

	$ord -> data['associated_id']=$ord->id;
	$ord -> data['quantity']=$input_data['quantity'];

  //salta_stampa
	//$ord -> data['printed']=$input_data['printed'];

	if(isset($input_data['priority']) && $input_data['priority']) $ord -> data['priority']=$input_data['priority'];

	if($dishid==SERVICE_ID) {
					$ord -> data['priority']=1;
				} else {
					// salta_stampa: aggiorna 'printed' solo se valorizzato in input
					if (isset($input_data['printed']) && $input_data['printed'] !== '') {
						$ord -> data['printed']=$input_data['printed'];
					}
				}

// tutti gli articoli con menu fisso attivo hanno priorità 1 non dipende dalla priorità scelta
	if(controlla_menu_fisso($dishid)) $ord -> data['priority']=1;

	if($err = $ord -> set()) return 0;

	return $ord->id;
}
//__________________________________________________________
// RTR END

function orders_ask_delete ($start_data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$ord = new order ((int) $start_data['id']);

	if ($ord -> data['dishid'] == SERVICE_ID) $dishname = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$dishname = $dish -> name ($_SESSION ['language']);
	}

	$tmp = '
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="delete">
	<input type="hidden" name="data[id]" value="'.$start_data['id'].'">
	'.ucfirst(phr('ASK_DELETE_CONFIRMATION')).'<br>
	<b>'.$dishname.'</b>

	</form>';
	$tpl -> assign ('question',$tmp);
}

function orders_ask_substitute ($start_data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('question');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$ord = new order ((int) $start_data['id']);

	if ($ord -> data['dishid'] == SERVICE_ID) $dishname = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$dishname = $dish -> name ($_SESSION ['language']);
	}

	$tmp = '
	<form action="orders.php" method="post" name="form1">
	<input type="hidden" name="command" value="substitute">
	<input type="hidden" name="data[id]" value="'.$start_data['id'].'">
	'.ucfirst(phr('SUBSTITUTE_ASK')).'<br>
	<b>'.$dishname.'</b>

	</form>';
	$tpl -> assign ('question',$tmp);
}

function orders_get_data ($start_data) {
	$id = (int) $start_data['id'];
	$ord = new order($id);

	$ret = $ord -> data;

	unset($ord);
	return $ret;
}

function orders_edit ($start_data,$fee_destroyer=false) {
	global $tpl;

	$tpl -> set_waiter_template_file ('edit');

	$ordid = (int) $start_data['id'];
	$ord = new order ($ordid);
	if (!$ord->exists ()) return ERR_ORDER_NOT_FOUND;

	if($fee_destroyer) {
	$tmp = navbar_form('form1','orders.php?command=delete&amp;data[silent]=1&amp;data[id]='.$start_data['id']);
	} else {
	$tmp = navbar_trash('form1','orders.php?command=list',$start_data);
	}
	$tpl -> assign ('navbar',$tmp);

	orders_edit_printed_info ($ord);
	orders_edit_form ($ord);

	//if($ord->data['dishid'] != SERVICE_ID) orders_edit_substitute ($ord);

	if($ord->data['dishid'] != SERVICE_ID && $ord->data['printed']) {
		orders_edit_dish_name ($ord);
		orders_edit_quantity_per_nota_ordine ($ord);
		nota_ordine ($ord);
		$tmp = navbar_trash('form1','orders.php?command=list',$start_data);
		$tpl -> assign ('navbar',$tmp);
		return 0;
	}

	orders_edit_quantity ($ord);
	orders_edit_dish_name ($ord);

	if($ord->data['dishid'] == SERVICE_ID) return 0;

	orders_edit_priority ($ord);
	orders_edit_extra_care ($ord);
	orders_edit_suspend ($ord);
	nota_ordine ($ord);
	return 0;
}

function orders_edit_printed_info ($ord) {
	global $tpl;

	if ($ord -> data['dishid'] == SERVICE_ID) return 0;
	if($ord->data['printed']==NULL) return 0;

	$print_time= substr($ord->data['printed'],-8,5);
	$tmp = ucphr('ORDER_PRINTED_AT').' '.$print_time;
	$tmp .= '<br>(<b>'.orders_print_elapsed_time ($ord,false).'</b> '.phr('ORDER_PRINTED_MINS_AGO').')';
	$tpl -> assign ('print_info',$tmp);

	return 0;
}

function orders_print_elapsed_time ($ord,$string=false) {
	if ($ord -> data['dishid'] == SERVICE_ID) return -1;
	if($ord->data['printed']==NULL) return -1;
	if($ord->data['deleted']) return -1;

	$elapsed_time=time() - strtotime($ord->data['printed']);
	$elapsed_time = round($elapsed_time/60,0);

	// number is requested, so we return minutes
	if(!$string) return $elapsed_time;

	// return string with associated description
	if ($elapsed_time>60) {
		$hrs=floor($elapsed_time/60);
		$mins=$elapsed_time-($hrs*60);
		$mins=sprintf("%02d",$mins);
		$elapsed_time=$hrs.'h:'.$mins.'min';
	} else {
		$elapsed_time.='min';
	}

	return $elapsed_time;
}

function orders_edit_dish_name ($ord) {
	global $tpl;

	if ($ord -> data['dishid'] == SERVICE_ID) $tmp = ucfirst(phr('SERVICE_FEE'));
	else {
		$dish = new dish ($ord -> data['dishid']);
		$tmp = $dish -> name ($_SESSION ['language']);
	}
	$tpl -> assign ('dishname',$tmp);

	return 0;
}


// WORKING Funzione nota_ordine nella tabella ordini

function nota_ordine ($ord) {
	global $tpl;
	$id_ordine = $ord -> data['id'];
	$query ="SELECT * FROM `#prefix#orders` WHERE `id`='".$id_ordine."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$arr = mysql_fetch_array ($res);

	$tmp = '';
	$data['nota_ordine'] = $arr['nota_ordine'];
	$tmp .= '';
	$tmp .= '<b>Nota piatto</b><br>
				<TEXTAREA NAME="data[nota_ordine]" COLS=38 ROWS=8>'.$data['nota_ordine'].'</TEXTAREA>
				<br>';
	$tpl -> append ('notaordine',$tmp);
return 0;
}

function orders_edit_suspend ($ord) {
	global $tpl;

	$checked='';
	if ($ord -> data['suspend']) $checked=" checked";
	$tmp = '<input type="checkbox" name="data[suspend]" value="1"'.$checked.' class="chekbox">';
	$tmp .= '<b>SOSPENDI DALLA STAMPA</b><br>';
	$tpl -> assign ('suspend',$tmp);

	return 0;
}

function orders_edit_extra_care ($ord) {
	global $tpl;

	$checked='';
	if ($ord -> data['extra_care']) $checked=" checked";

	$tmp = '<input type="checkbox" name="data[extra_care]" value="1"'.$checked.' class="chekbox">';
	$tmp .= '<b>ATTENZIONE PARTICOLARE</b><br>';

	$tpl -> assign ('extra_care',$tmp);
	return 0;
}

function orders_edit_substitute ($ord) {
	global $tpl;
	$tmp = '<A HREF="orders.php?command=ask_substitute&amp;data[id]='.$ord->data['id'].'"><b>SOSTITUISCI</b></a>';
	$tpl -> assign ('substitute',$tmp);
	return 0;
}

function orders_edit_quantity ($ord=0) {
	global $tpl;
	$tmp = '
	<table>
	<tr>
	<td><b>Quantità:</b><br>
	<select name="data[quantity]" class="button_big">';
	for ($i=0; $i<=MAX_QUANTITY; $i++) {
		if ($ord -> data['quantity'] == $i) $selected = 'selected';
		else $selected = '';

		$tmp .= '
		<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
	}
	$tmp .= '
	</select>
	</td>
	</tr>
	</table>';
	$tpl -> assign ('quantity',$tmp);
	return 0;
}

//consente di stampare una nota ordine anche con i piatti già stampati
// risolve il problema che non form non esiste la quantità e quindi la mette a zero annullando l'ordine
// non è molto elegante ma è una modifica veloce

function orders_edit_quantity_per_nota_ordine ($ord) {
	global $tpl;
	$id_ordine = $ord -> data['id'];
	$query =" SELECT * FROM `#prefix#orders` WHERE `id`= '".$id_ordine."' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$arr = mysql_fetch_array ($res);

	$data['quantity'] = $arr['quantity'];

	$tmp = '<input type="hidden" name="data[quantity]" value="'.$data['quantity'].'">';
	$tpl -> assign ('quantity',$tmp);
	return 0;
}

function orders_edit_priority ($ord) {
	global $tpl;

	$tmp = '';
// Vecchio codice
//$tmp = '
//	<table><tr><td valign="middle">P: </td><td>
//	<select name="data[priority]" size="4" class="button_big">';
//	for ($i=1; $i<=4; $i++) {
//		if ($ord -> data['priority'] == $i) $selected = ' selected';
//		else $selected = '';
//
//		$tmp .= '
//				<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
//	}
//		$tmp .= '
//				</select></td></tr></table>';

// RTR START WORKING
	$tmp .= '
	<b>Priorità:</b><br>
	<table width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<tr>
		';

	for ($i=1; $i<=4; $i++) {
	if ($ord -> data['priority'] == $i) $selected = 'checked';
	else $selected = '';

	//$color = array('', '#E4E4E4', '#00FFFF', '#FF0000', '#FF00FF');
	$color[0]= 0;
	$color[1]= COLOR_ORDER_PRIORITY_1;
	$color[2]= COLOR_ORDER_PRIORITY_2;
	$color[3]= COLOR_ORDER_PRIORITY_3;
	$color[4]= COLOR_ORDER_PRIORITY_4;

	$tmp .= '
			<td align="center" bgcolor="'.$color[$i].'"> '.$i.'
				<input type="radio" '.$selected.' name="data[priority]" value="'.$i.'" class="radio">
			</td>
			';
	}
	$tmp .= '
		</tr>
	</table>
	';
// RTR END

	$tpl -> assign ('priority',$tmp);
	return 0;
}

function orders_edit_form($ord) {
	global $tpl;

	$tmp = '
	<form action="orders.php" method="POST" name="form1">
	<input type="hidden" name="command" VALUE="update">
	<input type="hidden" name="data[id]" VALUE="'.$ord->id.'">';
	$tpl -> assign ('form_start',$tmp);

	$tmp = '
	</form>';
	$tpl -> assign ('form_end',$tmp);

	return 0;
}

function orders_update($start_data) {
	global $tpl;

	$id= (int) $start_data['id'];
	$ord = new order($id);

	if (!isset($start_data['suspend'])) $start_data['suspend'] = 0;
	if (!isset($start_data['extra_care'])) $start_data['extra_care'] = 0;

	if (isset($start_data['price'])) $start_data['price'] = eq_to_number ($start_data['price']);

	// forces extra_care = 1 for generic dishes
	$dishid=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"orders","dishid",$start_data['id']);
	$generic=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"dishes","generic",$dishid);
	if($generic && (!isset($start_data['price']) || $start_data['price']==0)) {
		$start_data['extra_care'] = '1';
	} elseif($generic && !empty($start_data['price'])) {
		$start_data['extra_care'] = '0';
	}

	// toplist update code
	if(!isset($start_data['quantity'])) $start_data['quantity']=0;

	// insert all the modules interfaces for order creation here
	toplist_update ($ord -> data['dishid'], $ord->data['quantity'], $start_data['quantity']);
	if(class_exists('stock_object')) {
		$stock = new stock_object;
		$stock -> silent = true;
		$stock -> remove_from_waiter($id,$start_data['quantity']);
	}
	// set_stock_from_id($id,$start_data['quantity']);
	// end interfaces

	// real update
	$ord->data=$start_data;
	$err = $ord -> set();


	unset($ord);
	return $err;
}

function orders_delete($start_data) {
	global $tpl;

	$id = (int) $start_data['id'];
	$ord = new order($id);

	if(!$ord -> data['deleted'] &&
		$ord -> data['printed'] &&
		$ord -> data['dishid'] != SERVICE_ID) {
		if($err = print_ticket($id,true)) return $err;
	}

	if (CONF_DEBUG_DONT_DELETE) return 0;

	// was as follows, but it's better to never delete an order if the table is still open
	// if (get_conf(__FILE__,__LINE__,"orders_show_deleted") && $ord -> data['dishid'] != SERVICE_ID) {
	if ($ord -> data['dishid'] != SERVICE_ID) {
		$start_data['deleted']=1;
		$start_data['paid']=1;
		$start_data['suspend']=0;
		$start_data['printed']='0000-00-00 00:00:00';
		$start_data['price']=0;
		$err = orders_update ($start_data);
	} else {
		// insert all the modules interfaces for order creation here
		toplist_delete($ord -> data['dishid'],$ord -> data['quantity']);
		if(class_exists('stock_object')) {
			$stock = new stock_object;
			$stock -> silent = true;
			$stock -> remove_from_waiter($id,0);
		}
		// set_stock_from_id($id,0);
		// end interfaces

		$err = $ord -> delete();
	}

	unset($ord);
	return $err;
}

function orders_find_mod_order($main, $ingredid) {
	$main = (int) $main;

	$query ="SELECT id FROM `#prefix#orders` WHERE #prefix#orders.associated_id = '".$main."' AND #prefix#orders.id != '".$main."' AND #prefix#orders.ingredid = '".$ingredid."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	$arr ['id']=0;
	$arr = mysql_fetch_array ($res);
	return $arr ['id'];
}

function orders_service_fee_exists(){
	$query="SELECT `id` FROM `#prefix#orders` WHERE `dishid` = '".SERVICE_ID."' AND `sourceid`='".$_SESSION['sourceid']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if (!mysql_num_rows($res)) return 0;

	$arr = mysql_fetch_array ($res);

	return $arr['id'];
}

function orders_service_fee_questions() {
	$id=orders_service_fee_exists ();
	$created=false;
	if(!$id) {
		//inserisce la quantità minima dei coperti
		//$data['quantity']=get_conf(__FILE__,__LINE__,"default_quantity");

/*		$data['quantity']='0';
		$id = orders_create (SERVICE_ID,$data);
		$created =true;
		$start_data['id']=$id;
		orders_edit ($start_data,$created);
*/

//inizio nuovo codice
	global $tpl;

	$tpl -> set_waiter_template_file ('edit');

	$tmp = navbar_form('form1','orders.php?command=list');
	$tpl -> assign ('navbar',$tmp);

	$id = SERVICE_ID;

	$tmp = '
	<form action="orders.php" method="POST" name="form1">
	<input type="hidden" name="command" VALUE="create">
	<input type="HIDDEN" name="dishid" value="'.SERVICE_ID.'">
	<input type="hidden" name="data[priority]" VALUE="1">
	';
	$tpl -> assign ('form_start',$tmp);

	$tmp = ucfirst(phr('SERVICE_FEE'));
	$tpl -> assign ('dishname',$tmp);

	$tmp =  '
					<script>
						function cancella(){
							var x = document.getElementById("quantita_moltiplicata").value="";
						}
					</script>

								Nessun Coperto <input type="radio" name="data[quantity]" value="Zero" class="radio" onClick="cancella()" checked /> <br /><br /><br />

								<table style="width: 90%; margin: 0 auto; border-spacing: 0 35px; text-align: right;">
								  <tr>
									<td>1 <input type="radio" name="data[quantity]" value="1" class="radio" onClick="cancella()" /></td>
									<td>2 <input type="radio" name="data[quantity]" value="2" class="radio" onClick="cancella()" /></td>
									<td>3 <input type="radio" name="data[quantity]" value="3" class="radio" onClick="cancella()" /></td>
									<td>4 <input type="radio" name="data[quantity]" value="4" class="radio" onClick="cancella()" /></td>
									<td>5 <input type="radio" name="data[quantity]" value="5" class="radio" onClick="cancella()" /></td>
								  </tr>
								  
								  <tr>
									<td>6 <input type="radio" name="data[quantity]" value="6" class="radio" onClick="cancella()" /></td>
									<td>7 <input type="radio" name="data[quantity]" value="7" class="radio" onClick="cancella()" /></td>
									<td>8 <input type="radio" name="data[quantity]" value="8" class="radio" onClick="cancella()" /></td>
									<td>9 <input type="radio" name="data[quantity]" value="9" class="radio" onClick="cancella()" /></td>
									<td>10 <input type="radio" name="data[quantity]" value="10" class="radio" onClick="cancella()" /></td>
								  </tr>
								  
								  <tr>
									<td>11 <input type="radio" name="data[quantity]" value="11" class="radio" onClick="cancella()" /></td>
									<td>12 <input type="radio" name="data[quantity]" value="12" class="radio" onClick="cancella()" /></td>
									<td>13 <input type="radio" name="data[quantity]" value="13" class="radio" onClick="cancella()" /></td>
									<td>14 <input type="radio" name="data[quantity]" value="14" class="radio" onClick="cancella()" /></td>
									<td>15 <input type="radio" name="data[quantity]" value="15" class="radio" onClick="cancella()" /></td>
								  </tr>
								</table>

								Inserisci numero: <input type="number" name="data[quantita_moltiplicata]" value="" min="16" max="200" step="1" class="radio" id="quantita_moltiplicata" style="font-size: 30px; padding: 10px; width: 90px; />
					';

	$tpl -> assign ('quantity',$tmp);

	$tmp = '
	</form>';
	$tpl -> assign ('form_end',$tmp);

	return 0;

//fine nuovo codice


	} else {
		orders_list();
	}
}

function orders_list() {
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
		&& $associated_waiter && ($associated_waiter == $_SESSION ['userid'] || $user->level[USER_BIT_CASHIER] )
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
	$tmp .= '
			<FORM ACTION="orders.php?command=unisci" METHOD=POST>
			<INPUT TYPE="submit" value="UNISCI TAVOLO" class="button_big">
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

	$tmp = categories_list_2cols();
	$tpl -> assign ('categories2cols',$tmp);

	$tmp = categories_list();
	$tpl -> assign ('categories',$tmp);


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
	if(!isset($_SESSION['show_toplist'])) {
		$_SESSION['show_toplist']=get_conf(__FILE__,__LINE__,"top_list_show_top");
	}
	if($_SESSION['show_toplist']) {

		$tmp = personal_list_show2cols();
		$tmp = personal_list_show();

	}  elseif(get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		//$tmp = '<a href="orders.php?command=set_show_toplist">:: Mostra la TopList ::</a><br>';
		$tpl -> assign ('toplist2cols',$tmp);
		$tpl -> assign ('toplist',$tmp);
	}

	if(!$_SESSION['show_toplist']) {
	
		$tmp = toplist_show2cols();
		$tmp = toplist_show();

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

function dishlist_form_start($back_to_cat=false) {
	$output = '
	<form action="orders.php" method="POST" name="order_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">
	<input type="hidden" name="from_category" value="1">';

	return $output;
}

function dishlist_form_end() {
	$output = '
	</form>
	';
	return $output;
}

function dishlist_back_to_cat() {
	if(get_conf(__FILE__,__LINE__,'creation_back_to_category')) $back_to_cat_chk=' checked';
	elseif($_SESSION['go_back_to_cat']) $back_to_cat_chk=' checked';
	else $back_to_cat_chk='';
	//RTR check box
	$output = '
	<INPUT TYPE="checkbox" NAME="back_to_cat" VALUE="1"'.$back_to_cat_chk.' class="chekbox"> Torna';
	return $output;
}

function priority_radio ($data) {
	// code modded from get to post. !!CHECK THIS!!

	if(table_is_takeaway($_SESSION['sourceid'])) {
		$output = '
	<input type="hidden" name="data[priority]" value=1>';
		return $output;
	}

	// if((!isset($data['priority']) || !$data['priority']) && table_is_takeaway($_SESSION['sourceid'])) $data['priority']=1;

	if((!isset($data['priority']) || !$data['priority']) && $data['category']) {
		$cat = new category ($data['category']);
		if ($cat->data['priority']) $data['priority']=$cat->data['priority'];
		elseif ($tmp=get_conf(__FILE__,__LINE__,"default_priority")) $data['priority']=$tmp;
	}

	for ($i=1;$i<=4;$i++) $chk[$i]="";
	if(isset($data['priority'])) $chk[$data['priority']]="checked";
	// RTR dishlist priority
	// alternativa per link premendo qualunque punto della cella
	// <td align="center" onClick="check_prio(\'order_form\',0);return false;" bgcolor="'.COLOR_ORDER_PRIORITY_1.'">
	$output = '
	<!-- function priority_radio -->
	<table width="100%" border="1" cellpadding="0" cellspacing="0" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<tr>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_1.'">1
				<input type="radio" '.$chk[1].' name="data[priority]" value=1 class="radio">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_2.'">2
				<input type="radio" '.$chk[2].' name="data[priority]" value=2 class="radio">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_3.'">3
				<input type="radio" '.$chk[3].' name="data[priority]" value=3 class="radio">
			</td>
			<td align="center" bgcolor="'.COLOR_ORDER_PRIORITY_4.'">4
				<input type="radio" '.$chk[4].' name="data[priority]" value=4 class="radio">
			</td>
		</tr>
	</table>
	';
	return $output;
}

function quantity_list($data=array()) {
	// code modded from get to post. !!CHECK THIS!!
	$tmp = '<!-- function quantity_list -->';

	$default_quantity=get_conf(__FILE__,__LINE__,"default_quantity");
	$selected_qty = $default_quantity;

	if (isset($data['quantity']) && $data['quantity']>0) $selected_qty = $data['quantity'];

	if (!isset($data['nolabel']) || !$data['nolabel']) $tmp .= 'Q.tà:';
	$tmp .= '
	<select name="data[quantity]" size="1" class="button">';
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

function dishes_list_cat($data){
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

	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">
	<tr>
	<th scope=col>ID</th>
	<th scope=col>Nome</th>
	<th scope=col>Euro</th>
	</tr>
	';

	// ascii letter A
	//$i=65;
	//unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
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
			//} else $letter='';

			$output .= '
			<tr style="height:35px">
				<td style="font-size: 8px" bgcolor="'.$class.'">'.$arr['id'].'</td>
				<td style="cursor: pointer" bgcolor="'.$class.'"
				onMouseOver = "this.style.background=\'#f1f1f1\'"
				onMouseOut  = "this.style.background=\''.$class.'\'"
				onclick     = "order_select('.$dishid.',\'order_form\'); return false;">
				'.$dishname.'</td>
				<td align="right" bgcolor="'.$class.'">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

//RTR

function dishes_list_cat_2cols($data){
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
						onclick="order_select('.$dishid.',\'order_form\'); return false;">'
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

//rtr



function input_dish_id($data){
	$output = '';

	$output .= ' Articolo:<input type="text" name="dishid" value="" size="9" maxlength="6" class="input">';

	return  $output;
}

function dishes_list_letter($data){
	$output = '';

	$letter = $data['letter'][0];
	if($letter=='\\') $letter=$data['letter'][0].$data['letter'][1];

	if(empty($letter)) return '';

	if(get_conf(__FILE__,__LINE__,"invisible_show")) {
		$query="SELECT #prefix#dishes.*,
		#prefix#dishes#lang#.table_name
		FROM `#prefix#dishes`
		JOIN `#prefix#dishes#lang#`
		WHERE (`table_name` LIKE '".$letter."%' OR `name` LIKE '".$letter."%')
		AND #prefix#dishes.deleted='0'
		AND #prefix#dishes.id=#prefix#dishes#lang#.table_id
		ORDER BY table_name ASC";
	} else {
		$query="SELECT #prefix#dishes.*,
		#prefix#dishes#lang#.table_name
		FROM `#prefix#dishes`
		JOIN `#prefix#dishes#lang#`
		WHERE (`table_name` LIKE '".$letter."%' OR `name` LIKE '".$letter."%')
		AND `visible`='1'
		AND #prefix#dishes.deleted='0'
		AND #prefix#dishes.id=#prefix#dishes#lang#.table_id
		ORDER BY table_name ASC";
	}
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">
	<tr>
	<th scope=col>ID</th>
	<th scope=col>Nome</th>
	<th scope=col>Euro</th>
	</tr>
	';

	// ascii letter A
	//$i=65;
	unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$arr['category']);
		$dishcat=$arr['category'];
		$dishid = $arr['id'];

		//RTG: no more queries, please
		// k: i'm sorry, but we have to do that, because of other charsets!
		$dishobj = new dish ($arr['id']);
		$dishname = $dishobj -> name ($_SESSION['language']);

		// $dishname = $arr['table_name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
			$dishname = $arr['name'];

		if(strtolower($dishname{0})!=strtolower($letter)) continue;
		$dishprice = $arr['price'];

		if ($dishcat>0){
			// letters array follows
			//if($i<91) {
			//	$GLOBALS['key_binds_letters'][$i]=$dishid;
			//	$local_letter = chr($i);
			//	$i++;
		  //	} else $local_letter='';

			$output .= '<tr style="height:35px">
			<td style="font-size: 8px" bgcolor="'.$class.'" align="center">'.$arr['id'].'</td>';
			$output .= '
			<td style="cursor: pointer" bgcolor="'.$class.'"
			onMouseOver = "this.style.background=\'#f1f1f1\'"
			onMouseOut  = "this.style.background=\''.$class.'\'"
				onclick="order_select('.$dishid.',\'order_form\'); return false;">'
				.$dishname.'</td>';
			$output .= '<td bgcolor="'.$class.'" align="right">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function dishes_list_search($data){
	$output = '';

	$search = strtolower(trim($data['search']));

	if(empty($search)) return '';

	$query="SELECT #prefix#dishes.*,
	#prefix#dishes#lang#.table_name
	FROM `#prefix#dishes`
	JOIN `#prefix#dishes#lang#` ON #prefix#dishes.id=#prefix#dishes#lang#.table_id
	WHERE (LCASE(`table_name`) LIKE '".$search."%'
		OR LCASE(`name`) LIKE '".$search."%'
		OR LCASE(`table_name`) LIKE '% ".$search."%'
		OR LCASE(`name`) LIKE '% ".$search."%'
		)";
	if(!get_conf(__FILE__,__LINE__,"invisible_show")) {
		$query .= "AND `visible`='1'";
	}
	$query .= "
	AND #prefix#dishes.deleted='0'
	ORDER BY table_name ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';

	$output .= '<table bgcolor="'.COLOR_TABLE_GENERAL.'" width="100%">
	<tr>
	<th scope=col>ID</th>
	<th scope=col>Nome</th>
	<th scope=col>Euro</th>
	</tr>
	';

	// ascii letter A
	$i=65;
	unset($GLOBALS['key_binds_letters']);

	while ($arr = mysql_fetch_array ($res)) {
		$class=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],"categories","htmlcolor",$arr['category']);
		$dishcat=$arr['category'];
		$dishid = $arr['id'];

		//RTG: no more queries, please
		// k: i'm sorry, but we have to do that, because of other charsets!
		$dishobj = new dish ($arr['id']);
		$dishname = $dishobj -> name ($_SESSION['language']);

		// $dishname = $arr['table_name'];
		if ($dishname == null || strlen(trim($dishname)) == 0)
			$dishname = $arr['name'];

		$dishprice = $arr['price'];

		if ($dishcat>0){
			// letters array follows
			//if($i<91) {
			//	$GLOBALS['key_binds_letters'][$i]=$dishid;
			//	$local_letter = chr($i);
			//	$i++;
		  //} else $local_letter='';

			$output .= '<tr style="height:35px">
			<td style="font-size: 8px" bgcolor="'.$class.'"align="center">'.$arr['id'].'</td>';

			$output .= '
			<td style="cursor: pointer" bgcolor="'.$class.'"
					onMouseOut="this.style.background=\''.$class.'\'"
					onMouseOver="this.style.background=\'#f1f1f1\'"
					onclick="order_select('.$dishid.',\'order_form\'); return false;">'
			.$dishname.'</td>';
			$output .= '<td bgcolor="'.$class.'" align="right">'.$dishprice.'</td>
			</tr>';
		}
	}
	$output .= '
	</table>';

	return  $output;
}

function order_priority_class($priority){
	$classpriority='#FFFFFF';
	switch($priority){
		case 1:
			if($_SESSION['catprinted'][1]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_1;
			}
			break;
		case 2:
			if($_SESSION['catprinted'][2]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_2;
			}
			break;
		case 3:
			if($_SESSION['catprinted'][3]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_3;
			}
			break;
			//RTR todo sistemare colore categoria
			case 4:
			if($_SESSION['catprinted'][4]){
				$classpriority=COLOR_ORDER_PRIORITY_PRINTED;
			} else {
				$classpriority=COLOR_ORDER_PRIORITY_4;
			}
			break;
	}
	return $classpriority;
}

function order_extra_msg($extra_care){
	if ($extra_care) {
		$extra_msg = ucfirst(phr('EXTRA_CARE_ABBR'));
	} else {
		$extra_msg = "";
	}
	return $extra_msg;
}

function order_extra_class($extra_care,$class){
	if ($extra_care) {
		$classextra=COLOR_ORDER_EXTRACARE;
	} else {
		$classextra=$class;
	}
	return $classextra;
}

function order_printed_class($printed,$suspended){
	if ($printed) {
		$class=COLOR_ORDER_PRINTED;
	} elseif ($printed==NULL && $suspended==0) {
		$class=COLOR_ORDER_TO_PRINT;
	} elseif ($suspended==1) {
		$class=COLOR_ORDER_SUSPENDED;
	}
	return $class;
}

function order_print_time_class($orderid){
	$orderid= (int)$orderid;
	$ord = new order ($orderid);
	$elapsed = orders_print_elapsed_time ($ord);
	if($elapsed<1) return '';

	$level=100/CONF_COLOUR_PRINTED_MAX_TIME*$elapsed;
	$level=round($level,0);
	if($level>255) $level=255;
	if($level<0) $level=0;
	$level=255-$level;
	$level = sprintf("%02x",$level);
	switch(strtolower(CONF_COLOUR_PRINTED_COLOUR)) {
		case 'red': 	$class='#'.'FF'.$level.$level; break;
		case 'green': 	$class='#'.$level.'FF'.$level; break;
		case 'blue': 	$class='#'.$level.$level.'FF'; break;
		case 'magenta': $class='#'.'FF'.$level.'FF'; break;
		case 'cyan': 	$class='#'.$level.'FF'.'FF'; break;
		case 'yellow': 	$class='#'.'FF'.'FF'.$level; break;
		case 'grey':	$class='#'.$level.$level.$level; break;
		default: 		$class='#'.'FF'.'FF'.$level; break;
	}
	return $class;
}

// Funzione Genera Menu Fisso
function controlla_menu_fisso ($dishid) {
	$query=" SELECT * FROM mhr_dishes WHERE id='".$dishid."' AND menufisso='1'";
	$res=common_query($query,__FILE__,__LINE__);
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return 0;
	return true;
}

function controlla_salta_stampa ($dishid) {
	$query=" SELECT * FROM mhr_dishes WHERE id='".$dishid."' AND salta_stampa='1'";
	$res=common_query($query,__FILE__,__LINE__);
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return 0;
	return true;
}

// Funzione controlla se ci sono ordini da stampare fermi da più tot minuti TEMPO_MASSIMO_ORDINI
function controlla_ordini_da_stampare($sourceid) {

	$intervallo=TEMPO_MASSIMO_ORDINI;

	$diff=TIME()-$intervallo;
	$now=date("Y-m-d H:i:s", $diff);

	$query="SELECT `printed` FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' AND `printed` IS NULL AND `timestamp`<'".$now."' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return 0;

	return true;
}

// Funzione controlla se ci sono ordini da stampare fermi da più tot minuti TEMPO_MASSIMO_ORDINI
function controlla_tempo_massimo_ordine_fermo($sourceid) {

	$intervallo=TEMPO_MASSIMO_TAVOLO_FERMO;

	$diff=TIME()-$intervallo;
	$now=date("Y-m-d H:i:s", $diff);

	$query="SELECT `printed`, `timestamp` FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' AND (`printed`>'".$now."' OR `timestamp`>'".$now."') ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return true;

	//esiste almenu un ordine stampato da meno di 20 minuti
	return true;
}

// Funzione controlla se ci sono ordini da stampare fermi da più tot minuti TEMPO_MASSIMO_ORDINI
function controlla_tempo_massimo_tavolo_fermo($sourceid) {

	$intervallo=TEMPO_MASSIMO_TAVOLO_FERMO;

	$diff=TIME()-$intervallo;
	$now=date("Y-m-d H:i:s", $diff);

	$query="SELECT * FROM `#prefix#sources` WHERE `id`='".$sourceid."' AND `catprinted_time`>'".$now."' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return true;

	//esiste almeno una categoria stampata da meno di 20 minuti
	return true;
}

// Funzione controlla se ci sono ordini nel tavolo
function ci_sono_ordini_nel_tavolo($sourceid) {

	$query=" SELECT `id` FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' AND `dishid`!='-1' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	$num_rows=mysql_num_rows($res);
	if(!$num_rows) return 0;
	if($num_rows==0) return true;

	//esiste almeno una categoria stampata da meno di 20 minuti
	return true;
}

// Funzione controlla se ci sono ordini nel tavolo
function presenza_ordini($sourceid) {

	$query=" SELECT `id` FROM `#prefix#orders` WHERE `sourceid`='".$sourceid."' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return '';
	$num_rows=mysql_num_rows($res);
	if($num_rows) return TRUE;
	if($num_rows==0) return FALSE;

	//esiste almeno unordine
	return 1;
}

//funzione segna tavolo unito


//Verifica se un tavolo è unito
// la funzione è errata non restituisce il valore a $arr ['unito'] errore 
/*
function verifica_se_tavolo_unito($sourceid) {
	$query=" SELECT `id`,`unito` FROM `#prefix#sources` WHERE `id`='".$sourceid."' ";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;
	$num_rows=mysql_num_rows($res);
	if ($arr ['unito']) { 
		return TRUE;
		} else {
		return FALSE;
	}
}
*/
?>
