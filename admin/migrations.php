<?php
$inizio = microtime();
session_start();

define('ROOTDIR', '..');
require_once(ROOTDIR . '/includes.php');
require('./admin_start.php');

// --- Funzioni helper ---

function migrations_dir() {
    return dirname(__FILE__) . '/../upgrade';
}

function migration_list() {
    $dir = migrations_dir();
    $files = array();
    if (!is_dir($dir)) return $files;
    $handle = opendir($dir);
    if (!$handle) return $files;
    while (false !== ($file = readdir($handle))) {
        if (is_file($dir . '/' . $file) && preg_match('/\.sql$/i', $file)) {
            $files[] = $file;
        }
    }
    closedir($handle);
    sort($files);
    return $files;
}

function migration_is_applied($filename) {
    $key = addslashes('migration_' . basename($filename));
    $query = "SELECT * FROM `#prefix#system` WHERE `name`='" . $key . "' LIMIT 1";
    $res = common_query($query, __FILE__, __LINE__);
    if (!$res) return false;
    $arr = mysql_fetch_array($res);
    return ($arr && $arr['value'] == '1');
}

function migration_mark_applied($filename) {
    $key = addslashes('migration_' . basename($filename));
    $check = common_query("SELECT `id` FROM `#prefix#system` WHERE `name`='" . $key . "' LIMIT 1", __FILE__, __LINE__);
    if ($check && mysql_num_rows($check) > 0) {
        $query = "UPDATE `#prefix#system` SET `value`='1' WHERE `name`='" . $key . "'";
    } else {
        $query = "INSERT INTO `#prefix#system` (`name`, `value`) VALUES ('" . $key . "', '1')";
    }
    return common_query($query, __FILE__, __LINE__);
}

function migration_get_description($filename) {
    $dir = migrations_dir();
    $path = $dir . '/' . basename($filename);
    if (!is_file($path)) return '';
    $fp = fopen($path, 'r');
    if (!$fp) return '';
    $desc = '';
    while (!feof($fp)) {
        $line = trim(fgets($fp, 1024));
        if ($line === '') continue;
        if (substr($line, 0, 2) === '--') {
            $clean = trim(ltrim($line, '-'));
            if ($clean !== '' && !preg_match('/^MHR Migration/i', $clean)) {
                $desc = $clean;
                break;
            }
        } else {
            break;
        }
    }
    fclose($fp);
    return $desc;
}

function run_migration($filename, $simulate) {
    $dir = migrations_dir();
    $path = $dir . '/' . basename($filename);
    if (!is_file($path)) return array('File non trovato: ' . htmlentities($filename));

    $sql = file_get_contents($path);
    $lines = explode("\n", $sql);

    $db_type = 'common';
    $query   = '';
    $errors  = array();
    $log     = array();

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // direttiva database_type
        if (preg_match('/#[^:]*database_type[^:]*:\s*(\w+)/i', $trimmed, $m)) {
            $db_type = strtolower(trim($m[1]));
            continue;
        }

        // commenti e righe vuote
        if ($trimmed === '' || $trimmed[0] === '#' || substr($trimmed, 0, 2) === '--') {
            continue;
        }

        $query .= ' ' . $trimmed;

        // fine statement
        if (substr($trimmed, -1) === ';') {
            $query = trim(rtrim(trim($query), ';'));

            if ($simulate) {
                $log[] = '<code>' . htmlentities($query) . '</code>';
            } else {
                if ($db_type === 'account') {
                    $res = accounting_query($query, __FILE__, __LINE__, true);
                } else {
                    $res = common_query($query, __FILE__, __LINE__, true);
                }
                if (!$res) {
                    $errors[] = mysql_error() . '<br><small>' . htmlentities(substr($query, 0, 200)) . '</small>';
                } else {
                    $log[] = 'OK: <code>' . htmlentities(substr($query, 0, 120)) . '</code>';
                }
            }
            $query = '';
        }
    }

    return array('errors' => $errors, 'log' => $log, 'simulate' => $simulate);
}

// --- Controllo accesso ---
$access_ok = (access_allowed(USER_BIT_CONFIG) || access_allowed(USER_BIT_MENU));
if (!$access_ok) {
    $command = 'access_denied';
}

// --- Gestione comandi ---
$message = '';
$run_result = null;
$run_file = '';

if ($command === 'access_denied') {
    $tpl->set_admin_template_file('standard');
    $tmp = head_line('Migrazioni DB');
    $tpl->assign('head', $tmp);
    $tpl->assign('title', 'Migrazioni DB');
    $tpl->assign('content', access_denied_admin());
    $tmp = generating_time($inizio);
    $tpl->assign('generating_time', $tmp);
    if ($err = $tpl->parse()) die('error parsing template');
    $tpl->clean();
    header('Content-type: text/html; charset=' . phr('CHARSET'));
    echo $tpl->getOutput();
    exit;
}

if ($command === 'run' && isset($start_data['file'])) {
    $run_file = basename($start_data['file']);
    $simulate = !empty($start_data['simulate']);
    $result = run_migration($run_file, $simulate);

    if (empty($result['errors'])) {
        if (!$simulate) {
            migration_mark_applied($run_file);
            $message = '<span style="color:green"><b>Migrazione applicata con successo:</b> ' . htmlentities($run_file) . '</span>';
        } else {
            $message = '<span style="color:orange"><b>Simulazione completata (nessuna modifica al DB).</b></span>';
        }
    } else {
        $message = '<span style="color:red"><b>Errori durante la migrazione:</b><br>' . implode('<br>', $result['errors']) . '</span>';
    }
    $run_result = $result;
}

// --- Output HTML ---
$tpl->set_admin_template_file('standard');
$tmp = head_line('Migrazioni DB');
$tpl->assign('head', $tmp);
$tpl->assign('title', 'Migrazioni DB');

ob_start();

echo '<style>
.mig-table { border-collapse: collapse; width: 100%; font-size: 13px; }
.mig-table th, .mig-table td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
.mig-table th { background: #e0e0e0; }
.mig-applied { color: green; font-weight: bold; }
.mig-pending { color: #cc7700; font-weight: bold; }
.mig-log { background: #f5f5f5; border: 1px solid #ccc; padding: 8px; margin-top: 10px; font-size: 12px; max-height: 200px; overflow-y: auto; }
</style>';

if ($message) {
    echo '<p>' . $message . '</p>';
}

if ($run_result && (!empty($run_result['log']) || !empty($run_result['errors']))) {
    echo '<div class="mig-log">';
    if ($run_result['simulate']) echo '<b>SIMULAZIONE — query che verrebbero eseguite:</b><br>';
    foreach ($run_result['log'] as $l) echo $l . '<br>';
    foreach ($run_result['errors'] as $e) echo '<span style="color:red">' . $e . '</span><br>';
    echo '</div>';
}

$files = migration_list();

if (empty($files)) {
    echo '<p>Nessun file .sql trovato in <code>upgrade/</code>.</p>';
} else {
    echo '<table class="mig-table">
    <tr>
        <th>File</th>
        <th>Descrizione</th>
        <th>Stato</th>
        <th>Azioni</th>
    </tr>';

    foreach ($files as $file) {
        $applied = migration_is_applied($file);
        $desc    = migration_get_description($file);
        $status  = $applied
            ? '<span class="mig-applied">&#10003; Applicata</span>'
            : '<span class="mig-pending">&#9679; Pendente</span>';

        echo '<tr>';
        echo '<td><code>' . htmlentities($file) . '</code></td>';
        echo '<td>' . htmlentities($desc) . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>';

        if (!$applied) {
            echo '<form method="post" action="migrations.php" style="display:inline;">
                <input type="hidden" name="command" value="run">
                <input type="hidden" name="data[file]" value="' . htmlentities($file) . '">
                <input type="hidden" name="data[simulate]" value="0">
                <input type="submit" value="Esegui">
            </form> ';
            echo '<form method="post" action="migrations.php" style="display:inline;">
                <input type="hidden" name="command" value="run">
                <input type="hidden" name="data[file]" value="' . htmlentities($file) . '">
                <input type="hidden" name="data[simulate]" value="1">
                <input type="submit" value="Simula" style="background:#eea;">
            </form>';
        } else {
            echo '<form method="post" action="migrations.php" style="display:inline;">
                <input type="hidden" name="command" value="run">
                <input type="hidden" name="data[file]" value="' . htmlentities($file) . '">
                <input type="hidden" name="data[simulate]" value="0">
                <input type="submit" value="Riesegui" style="background:#fcc;">
            </form>';
        }

        echo '</td></tr>';
    }

    echo '</table>';
}

$content = ob_get_clean();
$tpl->assign('content', $content);

$tmp = generating_time($inizio);
$tpl->assign('generating_time', $tmp);

if ($err = $tpl->parse()) die('error parsing template');
$tpl->clean();

header('Content-Language: ' . $_SESSION['language']);
header('Content-type: text/html; charset=' . phr('CHARSET'));

echo $tpl->getOutput();
?>
