<?php
function access_connect_form_waiter ($url='') {
	$output = '';
	if(empty($url) && isset($_REQUEST['url']) && !empty($_REQUEST['url'])) $url=$_REQUEST['url'];

	$user = new user();
	$output .= '
	<div align="center">
	<form action="'.ROOTDIR.'/waiter/connect.php" method="post" name="connect_form_waiter">
	<input type="hidden" name="command" value="connect">
	';
	if(!empty($url))
		$output .= '<input type="hidden" name="url" value="'.$url.'">'."\n";
	$output .= '<table>
		<tr><td>
			<center>
			<h4>
			'.date("j/n/Y",time()).'<br>
			<b>'.date("G:i",time()).'</b>
			</h4>
			'.ucfirst(phr('WHO_ARE_YOU')).'<br>
	'.$user->html_select(SHOW_WAITER_ONLY).'

			</center>
		</td></tr>
		<tr><td>
			<center>
			Password:<br>
			<input type="password" name="password" size="9" class="input">
			</center>
		</td></tr>
		<tr><td>
			<center>
			<INPUT TYPE="SUBMIT" value="ACCEDI" class="button_big">
			</center>
		</td></tr>
	</table>
	</form>
	</div>
	';

	if(isset($_SESSION['userid']) && $_SESSION['userid'])
		$output = 'Sei già connesso.<br>
		<a href="'.ROOTDIR.'/waiter/connect.php?command=disconnect">..::PRIMA DISCONNETTITI::..</a>';


	return $output;
}

function access_connect_form_pos ($url='') {
	$output = '';
	if(empty($url) && isset($_REQUEST['url']) && !empty($_REQUEST['url'])) $url=$_REQUEST['url'];

	$user = new user();
	$output .= '
	<div align="center">
	<form action="'.ROOTDIR.'/pos/connect.php" method="post" name="connect_form_waiter">
	<input type="hidden" name="command" value="connect">
	';
	if(!empty($url))
		$output .= '<input type="hidden" name="url" value="'.$url.'">'."\n";
	$output .= '<table>
		<tr><td>
			<center>
			<h4>
			'.date("j/n/Y",time()).'<br>
			<b>'.date("G:i",time()).'</b>
			</h4>
			'.ucfirst(phr('WHO_ARE_YOU')).'<br>
	'.$user->html_select(SHOW_CASHIER_ONLY).'

			</center>
		</td></tr>
		<tr><td>
			<center>
			Password:<br>
			<input type="password" name="password" size="9" class="input">
			</center>
		</td></tr>
		<tr><td>
			<center>
			<INPUT TYPE="SUBMIT" value="ACCEDI" class="button_big">
			</center>
		</td></tr>
	</table>
	</form>
	</div>
	';

	if($_SESSION['userid'])
		$output = 'Sei già connesso.<br>
		<a href="'.ROOTDIR.'/waiter/connect.php?command=disconnect">..::PRIMA DISCONNETTITI::..</a>';


	return $output;
}


function access_connect_form ($url='') {
	$output = '';
	if(empty($url) && isset($_REQUEST['url']) && !empty($_REQUEST['url'])) $url=$_REQUEST['url'];

	$user = new user();
	$output .= '
	<div align="center">
	<form action="'.ROOTDIR.'/admin/connect.php" method="post" name="connect_form">
	<input type="hidden" name="command" value="connect">
	';
	if(!empty($url))
		$output .= '<input type="hidden" name="url" value="'.$url.'">'."\n";
	$output .= '<table>
		<tr><td>
			<center>
			<h4>
			'.date("j/n/Y",time()).'<br>
			<b>'.date("G:i",time()).'</b>
			</h4>
			Chi sei?<br>
	'.$user->html_select(SHOW_ALL_USERS).'
			</center>
		</td></tr>
		<tr><td>
			<center>
			Password:<br>
			<input type="password" name="password">
			</center>
		</td></tr>
		<tr><td>
			<center>
			<INPUT TYPE="SUBMIT" value="Invia">
			</center>
		</td></tr>
	</table>
	</form>
	</div>
	';

	if(isset($_SESSION['userid']) && $_SESSION['userid'])
		$output = 'You are connected.<br>
		<a href="'.ROOTDIR.'/admin/connect.php?command=disconnect">Disconnect first.</a>';

	return $output;
}

function access_denied_waiter () {
	global $tpl;
	$tpl -> set_waiter_template_file ('question');

	$tmp = '<b>'.ucfirst(phr('ACCESS_DENIED'))."</b><br>\n";
	$tmp='<font color="Red">'.$tmp.'</font>';
	$tpl -> append ('messages',$tmp);

	$tmp = navbar_empty('javascript:history.go(-1);');
	$tpl -> assign ('navbar',$tmp);

	return 0;
}

function access_denied_admin () {
	$url = $_SERVER['REQUEST_URI'];
	$link = ROOTDIR.'/admin/connect.php?command=disconnect';
	$link .= '&url='.urlencode($url);

	$tmp = '<b>'.ucfirst(phr('ACCESS_DENIED')).'</b><br>
	'.ucfirst(phr('ACCESS_DENIED_EXPLAIN')).'<br>';

	if(!isset($_SESSION['userid']) || !$_SESSION['userid']) {
		// Nessun utente collegato: mostra il form di connessione
		$tmp .= access_connect_form($url);
	} else {
		// Utente collegato ma senza privilegi: disconnette e propone nuova connessione
		$user = new user ($_SESSION['userid']);
		$user->disconnect();
		$tmp .= '
	<a href="'.$link.'">Connettiti</a>';
	}
	return $tmp;
}

function access_denied_template () {
	global $tpl;

	$link = ''.ROOTDIR.'/admin/connect.php?command=disconnect';
	$link .= '&url='.urlencode($_SERVER['REQUEST_URI']);

	$tmp = '<b>'.ucfirst(phr('ACCESS_DENIED')).'</b><br>
	'.ucfirst(phr('ACCESS_DENIED_EXPLAIN')).'<br>
	<a href="'.$link.'">Connettiti</a>';

	$tmp='<font color="Red">'.$tmp.'</font>';
	$tpl -> append ('messages',$tmp);

	return 0;
}

function access_allowed ($level) {
	$query="SELECT `value` FROM `#prefix#system` WHERE `name`='upgrade_last_key'";
	$res = common_query($query,__FILE__,__LINE__);
	if(!$res) return true;
	$arr=mysql_fetch_array($res);
	// system version is before user zones -> disable access control
	if($arr['value']<4) return true;

	$user = new user();
	// no user found, auth ok anyway (to allow recovering from lost passwords)
	if(!$user -> count_users()) return true;

	// not authenticated
	if(!isset($_SESSION['userid'])) return false;

	$user = new user($_SESSION['userid']);

	// disabled user, deny
	if($user->data['disabled']) return false;

	//doesn't have the flag
	if(!$user->level[$level]) return false;

	// the flag is waiter or cashier or any other that doesn't need a password, ok
	//if($level==USER_BIT_WAITER || $level==USER_BIT_CASHIER || $level==USER_BIT_MONEY) return true;

	// other flags need password
	if (isset($_SESSION['passworded']) && $_SESSION['passworded']) return true;

	// password not set
	return false;
}
?>
