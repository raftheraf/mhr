<?php
function keys_orders () {
	$links = order_last_modified_links ();
	$output = '
<script language="JavaScript1.2">
<!--
	if(!document.all) {
		window.captureEvents(Event.KEYUP);
	} else {
		document.onkeypress = keypressHandler;
	}
	function keypressHandler(e) {
		var e;
		if(document.all) { //it\'s IE
			e = window.event.keyCode;
		} else {
			e = e.which;
		}
		
		// key bindings
		if(e == 36) { /* home key */
			top.location=\''.$links[-1].'\';
		}
		if (e == 37 && 9) { /* left arrow */';
		if($links[10])
			$output .= '
			top.location=\''.$links[11].'\';';
		$output .= '}
		if (e == 38) { /* up arrow (unusable) */
		}
		if (e == 39) { /* right arrow */';
		if($links[11])
			$output .= '
			top.location=\''.$links[10].'\';';
		$output .= '}
		if (e == 40) { /* down arrow (unusable) */
		}';
		
		
		// letters
		$dishes_letters = letters_list_creator ();
		for ($i=65;$i<=90;$i++) {
			$letter = chr($i);
			if(in_array($letter, $dishes_letters, false)) {
				$link = 'orders.php?command=dish_list&data[letter]='.$letter;
				$output .= '
		if (e == '.$i.') top.location=\''.$link.'\';';
			}
		}
		
		// numbers
		for ($i=0;$i<10;$i++) {
			if(empty($links[$i])) continue;
			$key=$i+48;
			$output .= '
		if (e == '.$key.') top.location=\''.$links[$i].'\';';
		}
		
		$output .= '
		if (e == 10 || e == 13) { /* enter */
		}
		if (e == 27) { /* escape */
		}
		if (e == 108) { /* plus (unusable) */
		}
		if (e == 109) { /* minus (unusable) */
		}
		// key bindings end
	}
	window.onkeyup = keypressHandler;
//-->
</script>';
	return $output;
}

function keys_dishlist_cat () {
	$links = order_last_modified_links ();
	$output = '
<script language="JavaScript1.2">
<!--
	if(!document.all) {
		window.captureEvents(Event.KEYUP);
	} else {
		document.onkeypress = keypressHandler;
	}
	function keypressHandler(e) {
		var e;
		if(document.all) { //it\'s IE
			e = window.event.keyCode;
		} else {
			e = e.which;
		}
		
		// key bindings
		if(e == 36) { /* home key */
			top.location=\''.$links[-1].'\';
		}
		if (e == 37 && 9) { /* left arrow */';
		if($links[0])
			$output .= '
			top.location=\''.$links[1].'\';';
		$output .= '}
		if (e == 38) { /* up arrow (unusable) */
		}
		if (e == 39) { /* right arrow */';
		if($links[1])
			$output .= '
			top.location=\''.$links[0].'\';';
		$output .= '}
		if (e == 40) { /* down arrow (unusable) */
		}';
		
		
		// letters
		for ($i=65;$i<=90;$i++) {
			$letter = chr($i);
			if(isset($GLOBALS['key_binds_letters'][$i])) {
				$output .= '
		if (e == '.$i.' || e == '.($i + 32).') order_select('.$GLOBALS['key_binds_letters'][$i].',\'order_form\');';
			}
		}
		
		// numbers
			$output .= '
		if (e == 49) check_prio(\'order_form\',0);
		if (e == 50) check_prio(\'order_form\',1);
		if (e == 51) check_prio(\'order_form\',2);
		if (e == 52) check_prio(\'order_form\',3);
		';
		
		
		$output .= '
		if (e == 108) { /* plus (unusable) */
		}
		if (e == 109) { /* minus (unusable) */
		}
		// key bindings end
	}
	window.onkeyup = keypressHandler;
//-->
</script>';
	return $output;
}

function keys_dishlist_letters () {
	$links = order_last_modified_links ();
	$output = '
<script language="JavaScript1.2">
<!--
	if(!document.all) {
		window.captureEvents(Event.KEYUP);
	} else {
		document.onkeypress = keypressHandler;
	}
	function keypressHandler(e) {
		var e;
		if(document.all) { //it\'s IE
			e = window.event.keyCode;
		} else {
			e = e.which;
		}
		
		/*alert(e);*/
		
		// key bindings
		if(e == 36) { /* home key */
			top.location=\''.$links[-1].'\';
		}
		if (e == 37 && 9) { /* left arrow */';
		if($links[0])
			$output .= '
			top.location=\''.$links[1].'\';';
		$output .= '}
		if (e == 38) { /* up arrow (unusable) */
		}
		if (e == 39) { /* right arrow */';
		if($links[1])
			$output .= '
			top.location=\''.$links[0].'\';';
		$output .= '}
		if (e == 40) { /* down arrow (unusable) */
		}';
		
		
		// letters
		for ($i=65;$i<=90;$i++) {
			$letter = chr($i);
			if(isset($GLOBALS['key_binds_letters'][$i])) {
				$output .= '
		if (e == '.$i.' || e == '.($i + 32).') order_select('.$GLOBALS['key_binds_letters'][$i].',\'order_form\');';
			}
		}
		
		// numbers
			$output .= '
		if (e == 49) check_prio(\'order_form\',0);
		if (e == 50) check_prio(\'order_form\',1);
		if (e == 51) check_prio(\'order_form\',2);';
		
		
		$output .= '
		if (e == 108) { /* plus (unusable) */
		}
		if (e == 109) { /* minus (unusable) */
		}
		// key bindings end
	}
	window.onkeyup = keypressHandler;
//-->
</script>';
	return $output;
}


?>