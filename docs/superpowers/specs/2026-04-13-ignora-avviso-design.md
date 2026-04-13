# Ignora Avviso — Pulsante di chiusura popup diavoletto

**Data:** 2026-04-13  
**Stato:** Approvato

## Obiettivo

Aggiungere un pulsante "Ignora Avviso" in fondo al Layer 1 del popup diavoletto (`mostraListaDiavoletti`). Al click il popup si chiude. Al prossimo refresh automatico il popup riappare se i diavoletti sono ancora presenti.

## Modifica

**File:** `javascript/diavoletto_alert.js` — funzione `mostraListaDiavoletti(lista)`

Aggiungere dopo il loop dei pulsanti tavolo:

```javascript
var btnIgnora = document.createElement('button');
btnIgnora.style.cssText = [
    'display:block',
    'width:100%',
    'padding:12px',
    'margin-top:15px',
    'background:#555555',
    'color:white',
    'border:none',
    'border-radius:5px',
    'font-size:16px',
    'cursor:pointer',
    'min-height:44px'
].join(';');
btnIgnora.textContent = 'Ignora Avviso';
btnIgnora.onclick = function () {
    var ov = document.getElementById('diavoletto-overlay');
    ov.parentNode.removeChild(ov);
};
box.appendChild(btnIgnora);
```

## Comportamento

- Appare solo nel Layer 1 (lista tavoli), non nel Layer 2 (conferma)
- Click → rimuove l'overlay dal DOM
- Al prossimo refresh (~1s) il popup riappare se i diavoletti sono ancora attivi
- Nessun sessionStorage, nessuna persistenza
