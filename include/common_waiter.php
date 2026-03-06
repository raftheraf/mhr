<?php
/**
* My Handy Restaurant
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2004 Fabio De Pascale
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* @author		Fabio 'Kilyerd' De Pascale <public@fabiolinux.com>
* @package		MyHandyRestaurant
* @copyright		Copyright 2003-2005, Fabio De Pascale
*/

function find_accounting_db() {
	$found_accounting_db = false;
	$query = "SELECT * FROM `#prefix#accounting_dbs`";
	$res = common_query($query, __FILE__, __LINE__);
	if (!$res) return false;

	while ($arr = mysql_fetch_array($res)) {
		$db_escaped = mysql_real_escape_string($arr['db']);
		$tquery = "SHOW TABLES FROM `" . $db_escaped . "`";
		$tres = common_query($tquery, __FILE__, __LINE__);
		if ($tres && mysql_num_rows($tres) > 0) {
			$found_accounting_db = true;
			break;
		}
	}
	unset($res);
	unset($arr);

	return $found_accounting_db;
}

function common_allowed_ip($host) {
	// IP-based access control: only IPs in allowed_clients may proceed.
	// If the table is empty, any host is allowed.

	$query = "SELECT 1 FROM `#prefix#allowed_clients` LIMIT 1";
	$res = common_query($query, __FILE__, __LINE__);
	if (!$res) return false;
	if (mysql_num_rows($res) == 0) return true;

	$host = trim($host);
	if ($host === '') return false;
	$host_escaped = mysql_real_escape_string($host);
	$query = "SELECT 1 FROM `#prefix#allowed_clients` WHERE `host`='" . $host_escaped . "' LIMIT 1";
	$res = common_query($query, __FILE__, __LINE__);
	if (!$res) return false;

	return (mysql_num_rows($res) > 0);
}

// RTR START allowed_user_host
// funzione controllo accesso dalla tabella users con ip assegnato
// Restituisce true solo se esiste esattamente un utente con user_host = IP dato.
function allowed_user_host($user_host) {
	// Validazione: deve essere un IPv4 o IPv6 valido (riduce rischio se il parametro cambiasse in futuro)
	$user_host = trim($user_host);
	if ($user_host === '') return false;
	// Tabella vuota: nessun utente può fare login per IP
	$query = "SELECT 1 FROM `#prefix#users` LIMIT 1";
	$res = common_query($query, __FILE__, __LINE__);
	if (!$res) return false;
	if (mysql_num_rows($res) == 0) return false;
	// Escape per sicurezza (REMOTE_ADDR è di solito affidabile, ma non concatenare mai input utente)
	$user_host_escaped = mysql_real_escape_string($user_host);
	$query = "SELECT COUNT(*) AS n FROM `#prefix#users` WHERE `user_host`='" . $user_host_escaped . "'";
	$res = common_query($query, __FILE__, __LINE__);
	if (!$res) return false;
	$row = mysql_fetch_assoc($res);
	$count = (int) $row['n'];
	// Un solo utente con questo IP: login per IP consentito
	return ($count === 1);
}

function redirect($url, $tempo = FALSE) {
	if (!headers_sent() && $tempo === FALSE) {
		header('Location: ' . $url);
	} elseif (!headers_sent() && $tempo !== FALSE) {
		header('Refresh: ' . $tempo . ';' . $url);
	} else {
		if ($tempo === FALSE) {
			$tempo = 0;
		}
		echo '<meta http-equiv="refresh" content="' . $tempo . ';' . $url . '">';
	}
}

// RTR END

?>
