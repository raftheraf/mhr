<?php
function format_date_ita($ts) {
	$giorni = array('domenica','lunedì','martedì','mercoledì','giovedì','venerdì','sabato');
	$mesi   = array(1=>'gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre');
	return $giorni[date('w',$ts)] . ' ' . date('j',$ts) . ' ' . $mesi[(int)date('n',$ts)] . ' ' . date('Y',$ts);
}

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
	$output .= '<div style="
		background:#ffffff;
		border:1px solid #c8e6c9;
		border-radius:14px;
		box-shadow:0 4px 12px rgba(0,0,0,0.15);
		padding:28px 32px;
		max-width:300px;
		margin:0 auto;
		font-family:Arial,sans-serif;
	">
		<div style="text-align:center; margin-bottom:18px; color:#333; font-size:15px;">
			'.format_date_ita(time()).'<br>
			<span style="font-size:28px; font-weight:bold; color:#1b5e20;">'.date("G:i",time()).'</span>
		</div>
		<div style="margin-bottom:14px;">
			<label style="display:block; font-size:14px; color:#555; margin-bottom:5px; font-weight:bold;">'.ucfirst(phr('WHO_ARE_YOU')).'</label>
			'.$user->html_select(SHOW_WAITER_CASHIER).'
		</div>
		<div style="margin-bottom:20px;">
			<label style="display:block; font-size:14px; color:#555; margin-bottom:5px; font-weight:bold;">Password</label>
			<input type="password" name="password" style="
				width:100%;
				font-size:22px;
				padding:8px 10px;
				border:2px solid #a5d6a7;
				border-radius:8px;
				box-sizing:border-box;
				text-align:center;
			">
		</div>
		<div style="text-align:center;">
			<input type="submit" value="ACCEDI" style="
				background:#2e7d32;
				color:#ffffff;
				font-size:24px;
				font-weight:bold;
				padding:14px 48px;
				border-radius:10px;
				border:3px solid #1b5e20;
				box-shadow:0 4px 8px rgba(0,0,0,0.25);
				cursor:pointer;
				width:100%;
			">
		</div>
	</div>
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
			<h4>'.format_date_ita(time()).' - <b>'.date("G:i",time()).'</b></h4>
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
			<h4>'.format_date_ita(time()).' - <b>'.date("G:i",time()).'</b></h4>
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
		// Utente collegato ma senza privilegi: mostra solo il messaggio, NON disconnettere
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
