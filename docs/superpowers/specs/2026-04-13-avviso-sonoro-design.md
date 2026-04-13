# Avviso Sonoro Popup Diavoletto

**Data:** 2026-04-13  
**Stato:** Approvato

## Obiettivo

Quando il popup diavoletto appare sulla pagina tavoli, parte un beep sonoro in loop (ogni secondo, 300ms di durata). Il suono si ferma quando il cameriere preme "Ignora Avviso" o "APRI" (conferma navigazione al tavolo).

## File modificato

`javascript/diavoletto_alert.js` — dentro l'IIFE esistente.

## Implementazione

### Variabili private (dentro IIFE)

```javascript
var _alertInterval = null;
var _audioCtx = null;
```

### `avviaBeep()`

```javascript
function avviaBeep() {
    if (_alertInterval) return;
    try {
        _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    } catch (e) { return; }
    _alertInterval = setInterval(function () {
        var osc = _audioCtx.createOscillator();
        osc.connect(_audioCtx.destination);
        osc.frequency.value = 880;
        osc.start();
        osc.stop(_audioCtx.currentTime + 0.3);
    }, 1000);
}
```

### `fermaBeep()`

```javascript
function fermaBeep() {
    if (_alertInterval) {
        clearInterval(_alertInterval);
        _alertInterval = null;
    }
    if (_audioCtx) {
        try { _audioCtx.close(); } catch (e) {}
        _audioCtx = null;
    }
}
```

### Punti di integrazione

- `mostraListaDiavoletti(lista)` — chiama `avviaBeep()` all'inizio
- `btnIgnora.onclick` — chiama `fermaBeep()` prima di rimuovere l'overlay
- `btnApri.onclick` in `mostraConferma()` — chiama `fermaBeep()` prima di `window.location.href`

## Vincoli

- Vanilla JS, nessun ES6
- `try/catch` su `AudioContext` per graceful degradation su browser senza supporto
- Primo caricamento su mobile: il browser può bloccare l'audio senza interazione utente precedente — comportamento accettabile
