# Design — Scambia Tavolo (Swap Table)

**Data**: 2026-04-13
**Approccio**: A — scambio diretto via doppio UPDATE SQL
**Approccio C** (admin con doppia select) pianificato come fase successiva separata.

---

## Obiettivo

Permettere a cassiere/admin di scambiare di posto due tavoli occupati o sospesi:
tutti gli ordini e i dati operativi (cliente, cameriere, telefono, ecc.) passano
dall'uno all'altro, mentre l'identità fisica del tavolo (nome, colore, ordernum)
rimane invariata.

---

## Flusso UX

```
[ordini tavolo A]
  → pulsante "SCAMBIA TAVOLO"
    → POST orders.php?command=ask_swap
      → griglia tavoli occupati/sospesi (escluso il tavolo corrente)
        → click su tavolo B
          → GET orders.php?command=swap&data[id]=B
            → esecuzione scambio
              → ritorno a orders_list (tavolo A ora con dati di B)
```

Il pulsante è visibile solo a cassiere (`USER_BIT_CASHIER`) e admin (`USER_BIT_CONFIG`),
identico al controllo già presente su "SPOSTA TAVOLO".

---

## Dati scambiati

### Campi che NON si scambiano (identità fissa del tavolo fisico)

`id`, `name`, `ordernum`, `takeaway`, `sospeso`, `utente_abilitato`, `tablehtmlcolor`

### Campi che SI scambiano (stato operativo)

`userid`, `toclose`, `discount`, `scontrinato`, `paid`, `catprinted`,
`catprinted_time`, `last_access_userid`, `last_access_time`, `takeaway_surname`,
`prefix_telefono`, `telefono`, `ora_prenotazione`, `takeaway_time`, `customer`,
`nota_tavolo`

### Ordini

`mhr_orders.sourceid` viene riassegnato: A→B e B→A.

---

## Sequenza SQL di `swap($destination)`

```
1. SELECT * FROM sources WHERE id = A          → $arr_a
2. SELECT * FROM sources WHERE id = B          → $arr_b
3. UPDATE orders SET sourceid = B WHERE sourceid = A
4. UPDATE orders SET sourceid = A WHERE sourceid = B
5. UPDATE sources SET [stato da $arr_b] WHERE id = A
6. UPDATE sources SET [stato da $arr_a] WHERE id = B
```

I passi 3 e 4 non collidono: dopo il passo 3 non esistono più ordini con
`sourceid = A`, quindi il passo 4 opera su un insieme distinto.

Campi esclusi dal SET: `id`, `name`, `ordernum`, `takeaway`, `sospeso`,
`utente_abilitato`, `tablehtmlcolor`, `last_access_time`.

---

## Griglia di selezione `swap_list_tables($cols)`

Come `move_list_tables()` con le seguenti differenze:

| Aspetto | move_list_tables | swap_list_tables |
|---|---|---|
| Tavoli mostrati | liberi (`userid=0`, `sospeso=0`) | occupati (`userid!=0`) + sospesi (`sospeso=1`) |
| Tavolo corrente | non applicabile | escluso |
| Link cella | `command=move&data[id]=X` | `command=swap&data[id]=X` |
| Titolo sezione 1 | "ELENCO DEI TAVOLI LIBERI" | "Tavoli occupati" |
| Titolo sezione 2 | "Tavoli sospesi" | "Tavoli sospesi" |

---

## File modificati

| File | Modifica |
|---|---|
| `include/tables_admin.php` | Aggiunge `swap_list_tables()`, `swap_list_cell()`, `swap()` nella classe `table` |
| `include/orders_waiter.php` | Aggiunge pulsante "SCAMBIA TAVOLO" accanto a "SPOSTA TAVOLO" |
| `waiter/orders.php` | Aggiunge `case 'ask_swap'` e `case 'swap'` |

Nessun template nuovo. Nessuna modifica al DB.

---

## Vincoli tecnici

- PHP 5.5 — niente `??`, `fn=>`, spread operator
- Usare `isset($x) ? $x : default` al posto di `$x ?? default`
- Usare `mysql_*` come nel resto del codice
- Compatibilità con il sistema single-tab (`mhr_tab_id` già gestito da `redir()`)
