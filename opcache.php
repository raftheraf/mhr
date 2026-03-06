<?php
header('Content-Type: text/plain; charset=utf-8');

if (!function_exists('opcache_reset')) {
    echo "OPcache non disponibile (estensione non caricata).\n";
    exit(1);
}

if (function_exists('opcache_get_status') && opcache_get_status(false) === false) {
    echo "OPcache non attiva (nessuna cache in uso).\n";
    exit(1);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'status';

if ($action === 'reset') {
    if (opcache_reset()) {
        echo "Cache OPcache resettata.\n";
    } else {
        echo "Reset fallito.\n";
    }
    exit(0);
}

// action = status (default): mostra se OPcache sta cachando
if ($action === 'status' || $action === '') {
    $status = opcache_get_status(false);
    if (!$status) {
        echo "OPcache non attiva.\n";
        exit(1);
    }
    echo "OPcache attivo.\n";
    echo "  Script in cache: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "  Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 1) . "%\n";
    echo "  Hit: " . $status['opcache_statistics']['hits'] . " | Miss: " . $status['opcache_statistics']['misses'] . "\n";
    echo "\nPer resettare la cache: opcache.php?action=reset\n";
}
