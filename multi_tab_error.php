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
$msg .= '<button id="btn-force-claim" onclick="mhrForceClaim()" style="font-size:1.2em;padding:12px 28px;cursor:pointer;background:#c0392b;color:#fff;border:none;border-radius:6px;">Prendi il controllo</button>';
$msg .= '<p id="force-claim-msg" style="margin-top:12px;color:#c0392b;display:none;"></p>';
$msg .= '<br><br>';
$msg .= '<h1><a href="index.php">Torna al login</a></h1>';
$msg .= '</div>';
$msg .= '<script>
function mhrForceClaim() {
    var btn = document.getElementById("btn-force-claim");
    var msgEl = document.getElementById("force-claim-msg");

    // Leggi tabId da sessionStorage
    var tabId = sessionStorage.getItem("mhr_tab_id");
    if (!tabId) {
        // Genera un nuovo tabId se non esiste
        tabId = "tab_" + Math.random().toString(36).substr(2, 9) + "_" + Date.now();
        sessionStorage.setItem("mhr_tab_id", tabId);
    }

    // Sovrascrivi il lock localStorage per notificare le altre schede
    function getPHPSESSID() {
        var match = document.cookie.match(/(?:^|;\s*)PHPSESSID=([^;]+)/);
        return match ? match[1] : "";
    }
    var sessid = getPHPSESSID();
    if (sessid) {
        var lockKey = "mhr_single_tab_lock_" + sessid;
        localStorage.setItem(lockKey, JSON.stringify({tab_id: tabId, ts: Date.now()}));
    }

    btn.disabled = true;
    msgEl.style.display = "block";
    msgEl.textContent = "Acquisizione in corso...";

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "tab_guard.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.ok && res.allowed) {
                msgEl.style.color = "#27ae60";
                msgEl.textContent = "Controllo acquisito. Reindirizzamento...";
                setTimeout(function () { window.location.href = "waiter/index.php"; }, 800);
            } else {
                msgEl.textContent = "Impossibile acquisire il controllo. Riprova.";
                btn.disabled = false;
            }
        } catch (e) {
            msgEl.textContent = "Errore di comunicazione. Riprova.";
            btn.disabled = false;
        }
    };
    xhr.onerror = function () {
        msgEl.textContent = "Errore di rete. Riprova.";
        btn.disabled = false;
    };
    xhr.send("action=force_claim&tab_id=" + encodeURIComponent(tabId));
}
</script>';

$msg .= common_bottom();
echo $msg;

?>