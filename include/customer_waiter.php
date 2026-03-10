<?php
class customer extends object {
	function customer($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'customers';
		$this->id=$id;
		$this -> fetch_data();
	}
}


function customer_search_page($data=array()) {
	global $tpl;

	$surname = isset($data['surname']) ? $data['surname'] : '';

	if(customer_recognize($surname)) return 0;

	$tpl -> set_waiter_template_file ('standard');

	// $tmp = navbar_form('form1','orders.php');
	$tmp = navbar_empty('orders.php');
	$tpl -> assign ('navbar',$tmp);

	$tmp = '';
	$tmp .= customer_search_form();
	$tmp .= customer_list($surname);
	$tmp .= '<br>
	<a href="orders.php?command=customer_insert_form" class="input">Inserisci Nuovo Cliente</a><br><br>
	';
	$tpl -> assign ('content',$tmp);

	return 0;
}

function customer_insert_page() {
	global $tpl;

	$tpl -> set_waiter_template_file ('standard');

	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);

	$tmp = '';
	$tmp .= customer_insert_form();
	$tpl -> assign ('content',$tmp);

	return 0;
}

function customer_edit_page($data) {
	global $tpl;

	$tpl -> set_waiter_template_file ('standard');

	$tmp = navbar_form('form1','orders.php');
	$tpl -> assign ('navbar',$tmp);

	$tmp = '';
	$tmp .= customer_edit_form($data);
	$tpl -> assign ('content',$tmp);

	return 0;
}

function customer_check_values($input_data){
	global $tpl;

	foreach ($input_data as $key => $value) $input_data[$key] =trim ($value);

	$msg="";
	if(empty($input_data['surname'])) {
		$msg=ucfirst(phr('CHECK_SURNAME')).'<br>';
		$err = ERR_TAKEAWAY_CHECK_SURNAME;
	}

	if($msg){
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return $err;
	}

	return $input_data;
}

function customer_list_table_head() {
	$msg = '
	<input class="form-control" type="text" id="reservationListInput" onkeyup="RicercaPrenotatiFunction()" placeholder="Ricerca nei clienti">
	<table class="booking" id="reservationTable" bgcolor="'.COLOR_TABLE_GENERAL.'">
		<colgroup>
			<col class="col1">
			<col class="col2 odd">
			<col class="col3">
			<col class="col4 odd">
			<col class="col5">
			<col class="col6 odd">
			<col class="col7">
		</colgroup>
		<thead>
			<tr class="header">
				<th align="center">'.ucfirst(phr('ID')).'</th>
				<th align="left">'.ucfirst(phr('SURNAME')).'</th>
				<th align="left">'.ucfirst(phr('NAME')).'</th>
				<th align="left">'.ucfirst(phr('PHONE')).'</th>
				<th align="left">'.ucfirst(phr('ADDRESS')).'</th>
				<th align="left">'.ucfirst(phr('EMAIL')).'</th>
				<th align="left"> Edit</th>
			</tr>
		</thead>
	<tbody>
	';
	return $msg;
}

function customer_list_table_bottom() {
	$msg = '</tbody>
	</table>
	';
	return $msg;
}

function customer_recognize ($term='') {
	global $tpl;

	$term=trim($term);
	$term=addslashes($term);
	if(empty($term)) return 0;

	$query = "SELECT * FROM `#prefix#customers`";
	$query .= " WHERE `surname` LIKE '%$term%'";
	$query .= " OR `name` LIKE '%$term%'";
	$query .= " OR `phone` LIKE '%$term%'";
	$query .= " OR `address` LIKE '%$term%'";
	$query .= " OR `email` LIKE '%$term%'";
	$query .= " OR `vat_account` LIKE '%$term%'";
	$query .= " ORDER BY `surname` ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return 0;

	if(mysql_num_rows ($res)==1) {
		$arr = mysql_fetch_array ($res);

		$data['takeaway_surname'] = $arr['surname'];
		$data['customer'] = $arr['id'];

		$err=takeaway_set_customer_data($_SESSION['sourceid'],$data);
		status_report ('TAKEAWAY_DATA',$err);

		orders_list();

		return 1;
	}

	return 0;
}

function customer_list($term='') {
	global $tpl;

	$term=trim($term);
	$term=addslashes($term);

	$query = "SELECT * FROM `#prefix#customers`";
	if(!empty($term)) {
		$query .= " WHERE `surname` LIKE '%$term%'";
		$query .= " OR `name` LIKE '%$term%'";
		$query .= " OR `phone` LIKE '%$term%'";
		$query .= " OR `address` LIKE '%$term%'";
		$query .= " OR `email` LIKE '%$term%'";
		$query .= " OR `vat_account` LIKE '%$term%'";
	}
	$query .= " ORDER BY `surname`, `name` ASC";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows ($res)) {
		$msg = ucphr('ERR_NO_CUSTOMER_FOUND');
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return '';
	}

	$msg = '';
	$msg .= customer_list_table_head();

	while ($arr = mysql_fetch_array ($res)) {
		$msg .= customer_list_row($arr);
	}
	$msg .= customer_list_table_bottom();

	return $msg;
}

function customer_list_row($arr) {
	$msg = '
	<tr>
		<td align="center">'.$arr['id'].'</td>
		<td><a href="orders.php?command=set_customer&amp;data[takeaway_surname]='.$arr['surname'].'&amp;data[customer]='.$arr['id'].'">'.$arr['surname'].'</a></td>
		<td>'.$arr['name'].'</a></td>
		<td>'.$arr['phone'].'</td>
		<td>'.$arr['address'].'</td>
		<td>'.$arr['email'].'</td>
		<td><a href="orders.php?command=customer_edit_form&amp;data[id]='.$arr['id'].'">'.ucfirst(phr('EDIT')).'</a></td>
	</tr>
	';

	return $msg;
}

function customer_search_form() {
	$msg = '
	<form action="orders.php" method="post" name="form_search">
		<input type="hidden" name="command" value="customer_search">
		';

/*
		$msg. = '
		<table>
		<tr>
		<td>
			<a href="orders.php?command=customer_insert_form">
			<img src="'.IMAGE_NEW.'" alt="'.ucfirst(phr('CUSTOMER_INSERT')).'" border=0 width="50" height="50">
			</a>
		</td>
		<td>
			<input name="data[surname]" type="text" value="'.$data['surname'].'" maxlength="255" size="10" class="input">
		</td>
		<td>
			<input type="image" src="'.IMAGE_FIND.'" alt="'.ucfirst(phr('SEARCH')).'" border=0 width="60" height="50">
		</td>
		</tr>
		</table>
		';
*/
		$msg .= '
	</form>
		';
	return $msg;
}

function customer_insert($input_data){
	$input_data=customer_check_values($input_data);
	if(!is_array($input_data)) return $input_data;

	// Now we'll build the correct INSERT query, based on the fields provided
	$query="INSERT INTO `#prefix#customers` (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="`".$key."`,";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=") VALUES (";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		$query.="'".addslashes($value)."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=")";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function customer_edit($input_data){
	$input_data=customer_check_values($input_data);
	if($input_data<0) return $input_data;

	// Now we'll build the correct UPDATE query, based on the fields provided
	$query="UPDATE `#prefix#customers` SET ";
	for (reset ($input_data); list ($key, $value) = each ($input_data); ) {
		//riga di codice originale genera problema con apostrofi
		//$query.="`".$key."`='".$value."',";
		$query.="`".$key."`='".addslashes($value)."',";
	}
	// strips the last comma that has been put
	$query = substr ($query, 0, strlen($query)-1);
	$query.=" WHERE `id`='".$input_data['id']."'";

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function customer_insert_form() {
	$msg = '
	<form action="orders.php" method="post" name="form1">
		<input type="hidden" name="command" value="customer_insert">
	';
	$msg .= customer_form_data(0);
	$msg .= '</form>
	';
	return $msg;
}

function customer_edit_form($data) {
	global $tpl;

	$query="SELECT * FROM `#prefix#customers` WHERE `id`='".$data['id']."'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows ($res)) {
		$msg = ucphr('ERR_NO_CUSTOMER_FOUND');
		$msg='<font color="Red">'.$msg.'</font>';
		$tpl -> append ('messages',$msg);
		return '';
	}

	$arr=mysql_fetch_array ($res);
	$msg = '
	<form action="orders.php" method="post" name="form1">
		<input type="hidden" name="command" value="customer_edit">
		<input type="hidden" name="data[id]" value="'.$arr['id'].'">
	';
	$msg .= customer_form_data($arr);
	$msg .= '</form>
	';
	return $msg;
}

function customer_form_data($data) {
	$msg = '
		<table>
		<tr>
		<td>
			'.ucfirst(phr('SURNAME')).':
		</td>
		<td>
			<input name="data[surname]" type="text" value="'.$data['surname'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('NAME')).':
		</td>
		<td>
			<input name="data[name]" type="text" value="'.$data['name'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ADDRESS')).':
		</td>
		<td>
			<input name="data[address]" type="text" value="'.$data['address'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('CITY')).':
		</td>
		<td>
			<input name="data[city]" type="text" value="'.$data['city'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('ZIP_CODE')).':
		</td>
		<td>
			<input name="data[zip]" type="text" value="'.$data['zip'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('VAT_ACCOUNT')).':
		</td>
		<td>
			<input name="data[vat_account]" type="text" value="'.$data['vat_account'].'" maxlength="11" size="11">
		</td>
		</tr>

		<tr>
		<td>
			Codice Fiscale:
		</td>
		<td>
			<input name="data[codice_fiscale]" type="text" value="'.$data['codice_fiscale'].'" maxlength="16" size="16">
		</td>
		</tr>

		<tr>
		<td>
			'.ucfirst(phr('PHONE')).':
		</td>
		<td>
			<input name="data[phone]" type="text" value="'.$data['phone'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			'.ucfirst(phr('MOBILE')).':
		</td>
		<td>
			<input name="data[mobile]" type="text" value="'.$data['mobile'].'" maxlength="255" size="10">
		</td>
		</tr>
		<tr>
		<td>
			<b>CODICE SDI: </b>
		</td>
		<td>
			<input name="data[email]" type="text" value="'.$data['email'].'" maxlength="255" size="10">
		</td>
		</tr>
		</table>
	';
	return $msg;
}

?>
