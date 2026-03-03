# QA Single Tab - My Handy Restaurant

Data: 2026-03-02
Scope: modulo waiter + POS

## Precondizioni
- Browser moderno con JavaScript attivo.
- Utente valido per login waiter/POS.
- Sessione pulita (consigliato: nuova finestra in incognito).

## Caso 1 - Accesso normale (scheda singola)
**Passi**
1. Aprire `waiter/connect.php`.
2. Eseguire login.
3. Navigare tra `tables.php`, `orders.php`, `booking.php`.

**Atteso**
- Nessun redirect a pagina errore.
- Operatività normale.

## Caso 2 - Apertura seconda scheda (duplicata)
**Passi**
1. Con sessione già attiva in scheda A, aprire stessa URL in scheda B.
2. In scheda B tentare navigazione/ricarica.

**Atteso**
- Scheda B viene reindirizzata a `multi_tab_error.php`.
- Scheda A resta pienamente operativa.

## Caso 3 - Chiusura scheda principale e recupero
**Passi**
1. Tenere aperte A (attiva) e B (in errore duplicato).
2. Chiudere scheda A.
3. Attendere 2-5 secondi.
4. In B cliccare "Ricarica".

**Atteso**
- B recupera il lock e torna operativa.
- Nessun blocco permanente.

## Caso 4 - Logout rilascia lock
**Passi**
1. Login in scheda A.
2. Eseguire logout da `disconnect.php`.
3. Aprire subito nuova scheda C e rifare login.

**Atteso**
- C entra senza redirect a errore duplicato.

## Caso 5 - Flusso POS
**Passi**
1. Ripetere i casi 1-4 su `pos/connect.php`.

**Atteso**
- Stesso comportamento del modulo waiter.

## Caso 6 - Heartbeat/timeout lock (resilienza)
**Passi**
1. Login in A.
2. Terminare bruscamente processo/tab (chiusura forzata browser o crash simulato).
3. Riaprire app in nuova scheda dopo alcuni secondi.

**Atteso**
- Nuova scheda diventa operativa grazie a release/TTL.

## Esito test
- [ ] Caso 1 OK
- [ ] Caso 2 OK
- [ ] Caso 3 OK
- [ ] Caso 4 OK
- [ ] Caso 5 OK
- [ ] Caso 6 OK

Note:
- In caso di anomalia, annotare browser, ora, URL e passi precisi.
