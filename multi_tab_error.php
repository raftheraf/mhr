<?php
session_start();

define('ROOTDIR','.');
require_once(ROOTDIR.'/includes.php');

$title = 'Accesso bloccato';
$msg = common_header($title);

$msg .= '<div style="max-width:720px;margin:40px auto;padding:20px;text-align:center;">';
$msg .= '<h1>Sessione già aperta in un\'altra scheda</h1>';
$msg .= '<p>Questa finestra è stata bloccata per evitare sovrascritture o perdita di dati.</p>';
$msg .= '<p>Chiudi l\'altra scheda oppure attendi alcuni secondi, poi ricarica questa pagina.</p>';

$msg .= '<a href="waiter/connect.php"><h1>Torna al login</h1></a>';
$msg .= '</div>';

$msg .= common_bottom();
echo $msg;

?>