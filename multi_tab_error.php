<?php
session_start();

define('ROOTDIR','.');
require_once(ROOTDIR.'/includes.php');

header('Content-Type: text/html; charset=utf-8');

$title = 'Accesso bloccato';
$msg = common_header($title);

$msg .= '<div style="max-width:720px;margin:40px auto;padding:20px;text-align:center;">';
$msg .= '<h1>Sessione già aperta in un\'altra scheda</h1>';
$msg .= '<p>Questa finestra è stata bloccata per evitare sovrascritture o perdita di dati.</p>';
$msg .= '<p>Chiudi l\'altra scheda oppure attendi alcuni secondi, poi ricarica questa pagina.</p>';
$msg .= '<br><br>';
$msg .= '<h1><a href="waiter/connect.php?command=none&amp;from_logout=1">Torna al login</a></h1>';
$msg .= '</div>';

$msg .= common_bottom();
echo $msg;

?>