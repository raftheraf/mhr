<?php

// Questa riga deve essere la primissima cosa, prima di qualsiasi "echo"
header('Content-Type: text/html; charset=utf-8');

echo "<br><br>";

echo "<div align=\"center\">";

	echo "Versione PHP: " . PHP_VERSION . "<br>";
	echo "Architettura: " . (PHP_INT_SIZE === 8 ? 'x64' : 'x86') . "<br>";
	echo "Thread Safety: " . (ZEND_THREAD_SAFE ? 'Sì (TS)' : 'No (NTS)') . "<p>";

	if (extension_loaded('printer')) {
		echo "<b>✅ SUCCESSO:</b> L'estensione printer è attiva!";
	} else {
		echo "<b>❌ ERRORE:</b> L'estensione non è caricata.";
	}
echo "</div>";
echo "<br><br>";

phpinfo();
