<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$queue = 'TM88STUDIO'; // nome esatto della stampante Windows

echo "Prima di printer_open...<br>";
flush();

$h = printer_open($queue);   // <-- QUI probabilmente si blocca

echo "Dopo printer_open...<br>";
flush();

if (!$h) {
    die("ERRORE: printer_open() non riesce ad aprire '$queue'");
}

printer_set_option($h, PRINTER_MODE, "RAW");
$text = "Test MyHandyRestaurant\r\n\r\n";
$res = printer_write($h, $text);
printer_close($h);

echo "Fatto, risultato printer_write: " . var_export($res, true);