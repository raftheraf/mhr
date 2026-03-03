<?php
function categories_printed($sourceid,$category) {
	$catprinted=array();
	$catprintedtext=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'sources',"catprinted",$sourceid);
	if($catprintedtext!=""){
		$catprinted = explode (" ", $catprintedtext);
	}

	// the priority has already been printed. return true
	if(in_array($category,$catprinted)) return true;

	return 0;
}

function categories_orders_present ($sourceid,$category) {
	$query = "	SELECT id
				FROM #prefix#orders
				WHERE sourceid ='".$sourceid."'
				AND priority =$category
				AND deleted = 0
				AND printed IS NOT NULL
				AND dishid != ".MOD_ID."
				AND dishid != ".SERVICE_ID."
				AND suspend = 0";
	$res = common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return mysql_num_rows($res);
}

function categories_list($data=''){
	$output = '
<table id="tabellalistacategorie" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'">
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
		$link = 'orders.php?command=dish_list&amp;data[category]='.$catid;
		if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
		if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];

		if($i%2) {
			$output .= '
		';
		}

		$output .= '
		<tr>
		<td height="40px" bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">
		<a href="'.$link.'">
		<strong>'.$name.'</strong>
		</a>
		</td>
		</tr>';

		if(($i+2)%2) {
			$output .= '
			';
		}

		/*
		$output .= '
		<td>
		&nbsp;
		</td>';
		*/
	}
	$output .= '
</table>';

	return $output;
}

function categories_list_2cols($data=''){
	$output = '
<table id="tabellalistacategorie2cols" bgcolor="'.COLOR_TABLE_GENERAL.'">
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

		$link = 'orders.php?command=dish_list&amp;data[category]='.$catid;

		if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
		if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];

		if($i%2) {
			$output .= '
	<tr>';
		}

		$output .= '
		<td height="40px" bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">';

		$output .= '
		<a href="'.$link.'">
		<strong>'.$name.'</strong>
		</a>
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

function letters_list_creator (){
	$invisible_show = get_conf(__FILE__,__LINE__,"invisible_show");
	if($invisible_show) {
		$query="SELECT `name`, `table_name` FROM `#prefix#dishes#lang#`
			JOIN #prefix#dishes
			WHERE #prefix#dishes#lang#.table_id=#prefix#dishes.id
			AND #prefix#dishes.deleted='0'";
	} else {
		$query="SELECT `name`, `table_name` FROM `#prefix#dishes#lang#`
			JOIN #prefix#dishes
			WHERE `visible`='1'
			AND #prefix#dishes#lang#.table_id=#prefix#dishes.id
			AND #prefix#dishes.deleted='0'";
	}

	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;
	$dishes_letters = array();
	while ($arr = mysql_fetch_array ($res)) {
		$name = trim($arr['table_name']);
		if ($name == null || strlen($name) == 0)
			$name = trim($arr['name']); //if no name in the fixed lang, use the main name
		array_push($dishes_letters, substr($name, 0, 1));
	}
	return $dishes_letters;
}

function letters_list ($data=''){
	$output = '
<table class="letters_list" bgcolor="'.COLOR_TABLE_GENERAL.'">
';

	$output .= '
	<tr>';

	// letters
	// total 32-95
	$offset = 32;

	$col=-1;
	$color = 0;

	$dishes_letters = letters_list_creator ();

	for ($i=17;$i<=(92-$offset);$i++) {

		$letter = chr($i + $offset);
		if($letter == "'") $letter = "\'";

		if($letter =='%' ) continue;

		$bgcolor=COLOR_TABLE_GENERAL;
		//RTG: if there is some dishes begginnig with this letter
		if(in_array($letter, $dishes_letters, false)) {
			$letter= htmlentities($letter);
			$link = 'orders.php?command=dish_list&amp;data[letter]='.$letter;

			if(isset($data['quantity']) && $data['quantity']) $link .= '&amp;data[quantity]='.$data['quantity'];
			if(isset($data['priority']) && $data['priority']) $link .= '&amp;data[priority]='.$data['priority'];

			$bgcolor = color ($color++);
			$output .= '
			<td  bgcolor="'.$bgcolor.'" onclick="redir(\''.$link.'\');return(false);">
			<a href="'.$link.'">
			<strong>'.$letter.'</strong>
			</a>
			</td>';
			$col++;
		} else {
			continue;
			$output .= '
			<td bgcolor="'.$bgcolor.'">
			&nbsp;
			</td>';
		}

		if((($col +1) % 8) == 0) {
			$color++;
			$output .= '
		</tr>
		<tr>';
		}
	}

	$output .= '
	</tr>';

	$output .= '
	</tbody>
</table>';

	return $output;
}

?>
