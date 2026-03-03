<?php
function toplist_delete_firsts (){
	// cut out all the possible error inserted (dishid=0)
	$query = "DELETE FROM `#prefix#last_orders` WHERE `dishid`='0'";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$query = "SELECT * FROM `#prefix#last_orders` ORDER BY `id`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	$num=mysql_num_rows($res);
	while($num>=CONF_TOPLIST_SAVED_NUMBER){
		$arr=mysql_fetch_array($res);
		$query = "DELETE FROM `#prefix#last_orders` WHERE `id`=".$arr['id']." LIMIT 1";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;

		$query = "SELECT * FROM `#prefix#last_orders` ORDER BY `id`";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
		$num=mysql_num_rows($res);
	}
	return 0;
}

function toplist_show(){
	global $tpl;

	$_SESSION['order_added']=0;

	$query = "SELECT * FROM `#prefix#last_orders`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return 1;

	while($arr=mysql_fetch_array($res)){
		$dishid=$arr['dishid'];
		if($dishid==MOD_ID || $dishid==SERVICE_ID) continue;

		/*
		$query = "SELECT * FROM `#prefix#dishes` WHERE `id`='$dishid'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return ERR_MYSQL;
		if(!mysql_num_rows($res2)) continue;
		*/

		if(!isset($toplist[$dishid])) $toplist[$dishid]=0;
		$toplist[$dishid]++;
	}

	if(!is_array($toplist)) return 0;

	arsort($toplist);
	reset ($toplist);

	$chk[1]="";
	$chk[2]="";
	$chk[3]="";
	$chk[4]="";

	$tmp = '
	<form action="orders.php" method="POST" name="toplist_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">';

	if(CONF_TOPLIST_HIDE_QUANTITY) {
		$tmp .= '<INPUT TYPE="HIDDEN" NAME="data[quantity]" VALUE="1">';
	}
	if(CONF_TOPLIST_HIDE_PRIORITY) {
		$tmp .= '<INPUT TYPE="HIDDEN" NAME="data[priority]" VALUE="1">';
	}

	$tmp .= '<table id="tabellatoplist" width="100%" bgcolor="'.COLOR_TABLE_GENERAL.'">';
	$tmp .= '<tr><td colspan=2 nowrap><center><b>TOP LIST</b><br>';

	$i = 0;
	while ($i < get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		if(list ($key, $value) = each($toplist)){
			$category=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','category',$key);
			$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$category);

			if(table_is_takeaway($_SESSION['sourceid'])) {
				$tmp .= '<input type="hidden" name="data[priority]" value=1>';
			} elseif(!$i && !CONF_TOPLIST_HIDE_PRIORITY) {

			$tmp .= '
			P.tà:
			<select name="data[priority]" size="1" class="button">
			<option value="1" selected> 1 </option>
			<option value="2"> 2 </option>
			<option value="3"> 3 </option>
			<option value="4"> 4 </option>
			</select>
			';
			}

			if (!$i && !CONF_TOPLIST_HIDE_QUANTITY) {
				$qtydata['nolabel']=1;
				$tmp .= 'Q.tà:'.quantity_list($qtydata).'';
				}
			$tmp .= '</center></td></tr>';


			$dishobj = new dish ($key);
			$dishname = $dishobj -> name ($_SESSION['language']);

			$tmp .= '
							<tr bgcolor="'.$bgcolor.'">
								<td align="right" width="10" height="40px">'.$value.'</td>
								<td onclick="order_select(\''.$key.'\',\'toplist_form\');">'.$dishname.'</td>
							</tr>';
			}
		$i++;
	}
	$tmp .= '
	</tr>
	</table>
	</form>';

	$tpl -> assign ('toplist',$tmp);

	return 0;
}

function toplist_show2cols(){
	global $tpl;

	$_SESSION['order_added']=0;

	$query = "SELECT * FROM `#prefix#last_orders`";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	if(!mysql_num_rows($res)) return 1;

	while($arr=mysql_fetch_array($res)){
		$dishid=$arr['dishid'];
		if($dishid==MOD_ID || $dishid==SERVICE_ID) continue;

		/*
		$query = "SELECT * FROM `#prefix#dishes` WHERE `id`='$dishid'";
		$res2=common_query($query,__FILE__,__LINE__);
		if(!$res2) return ERR_MYSQL;
		if(!mysql_num_rows($res2)) continue;
		*/

		if(!isset($toplist[$dishid])) $toplist[$dishid]=0;
		$toplist[$dishid]++;
	}

	if(!is_array($toplist)) return 0;

	arsort($toplist);
	reset ($toplist);

	$chk[1]="";
	$chk[2]="";
	$chk[3]="";
	$chk[4]="";

	$tmp = '
	<form action="orders.php" method="POST" name="toplist_form">
	<INPUT TYPE="HIDDEN" NAME="command" VALUE="create">
	<INPUT TYPE="HIDDEN" NAME="dishid" VALUE="0">';

	if(CONF_TOPLIST_HIDE_QUANTITY) {
		$tmp .= '<INPUT TYPE="HIDDEN" NAME="data[quantity]" VALUE="1">';
	}
	if(CONF_TOPLIST_HIDE_PRIORITY) {
		 $tmp .= '<INPUT TYPE="HIDDEN" NAME="data[priority]" VALUE="1">';
	}

	$tmp .= '	<table id="tabellatoplist2cols" width="100%" cellspacing="2" bgcolor="'.COLOR_TABLE_GENERAL.'">';
	$tmp .= '<tr><td colspan="4" nowrap><center><b>TOP LIST</b><br>';

	if(table_is_takeaway($_SESSION['sourceid'])) {
		$tmp .= '<input type="hidden" name="data[priority]" value=1>';
		} elseif(!CONF_TOPLIST_HIDE_PRIORITY) {
				$tmp .= 'Priorità:
								<select name="data[priority]" size="1" class="button">
								<option value="1" selected> 1 </option>
								<option value="2"> 2 </option>
								<option value="3"> 3 </option>
								<option value="4"> 4 </option>
								</select>';
		}

		if (!CONF_TOPLIST_HIDE_QUANTITY) {
			$qtydata['nolabel']=1;
				$tmp .= 'Quantità: '.quantity_list($qtydata).' ';
		}
	$tmp .= '</center></td></tr>';

	$i = 0;
	$a = 0;
	while ($i < get_conf(__FILE__,__LINE__,"top_list_show_top")) {
		$a++;
		if(list ($key, $value) = each($toplist)){
			$category=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'dishes','category',$key);
			$bgcolor=get_db_data(__FILE__,__LINE__,$_SESSION['common_db'],'categories','htmlcolor',$category);
			$dishobj = new dish ($key);
			$dishname = $dishobj -> name ($_SESSION['language']);

			if($a%2) {	$tmp .= '<tr>';	}

			$tmp .= '
							<td bgcolor="'.$bgcolor.'" align="right" width="10" height="40px">'.$value.'</td>
							<td bgcolor="'.$bgcolor.'" onclick="order_select(\''.$key.'\',\'toplist_form\');">'.$dishname.'</td>
			';

			if(($a+1)%2) {	$tmp .= '</tr>';		}
			$tmp .= '';
			}
		$i++;
		$tmp .= '';
	}
	$tmp .= '
					</table>
				</form>';

	$tpl -> assign ('toplist2cols',$tmp);

	return 0;
}

function toplist_insert ($dishid,$quantity){
	if(!$dishid) return 0;
	if($dishid==MOD_ID || $dishid==SERVICE_ID) return 0;

	for($i=0; $i<$quantity;$i++) {
		toplist_delete_firsts ();
		$query = "INSERT INTO `#prefix#last_orders` (`dishid`) VALUES ('".$dishid."')";
		$res=common_query($query,__FILE__,__LINE__);
		if(!$res) return ERR_MYSQL;
	}

	return 0;
}

function toplist_delete ($dishid,$quantity=1){
	if(!$dishid) return 0;

	$query = "DELETE FROM `#prefix#last_orders` WHERE `dishid`='".$dishid."' LIMIT $quantity";
	$res=common_query($query,__FILE__,__LINE__);
	if(!$res) return ERR_MYSQL;

	return 0;
}

function toplist_update($dishid,$old,$new) {
	$err = 0;
	$quantity_diff = $new - $old;
	if($quantity_diff > 0) $err = toplist_insert ($dishid,abs($quantity_diff));
	if($quantity_diff < 0) $err = toplist_delete ($dishid,abs($quantity_diff));
	return $err;
}
?>
