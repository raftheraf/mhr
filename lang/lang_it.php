<?php
/**
* My Handy Restaurant - Italian language file
*
* http://www.myhandyrestaurant.org
*
* My Handy Restaurant is a restaurant complete management tool.
* Visit {@link http://www.myhandyrestaurant.org} for more info.
* Copyright (C) 2003-2004 Fabio De Pascale
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* @author		Fabio 'Kilyerd' De Pascale <public@fabiolinux.com>
* @package		MyHandyRestaurant
* @copyright		Copyright 2003-2005, Fabio De Pascale
* @ignore
*/
/*
ucfirst(lang_get($_SESSION['language'],'ERROR_NO_INGREDIENT_SELECTED'))

lang_get($_SESSION['language'],'ERROR_NO_INGREDIENT_SELECTED')
*/
if (!defined('GLOBALMSG_CONFIG_FILE_NOT_WRITEABLE')) define('GLOBALMSG_CONFIG_FILE_NOT_WRITEABLE','Il file di configurazione (conf/config.inc.php) non è scrivibile. My Handy Restaurant non può funzionare senza quel file.<br>Controllare che il file esista e sia scrivibile o che la directory conf/ sia scrivibile.<br>Ricorda che il file o la directory devono essere scrivibile per l\'utente sotto il quale gira il web server.');
if (!defined('GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE')) define('GLOBALMSG_CONFIG_OUTPUT_FILES_NOT_WRITEABLE','i file di log degli errori o di debug non sono scrivibili.<br>Per funzionare correttamente, My handy Restaurant (l\'utente sotto il quale gira il web server) deve poter scrivere quei file.<br>Per favore, controllare che i file siano esistenti e scrivibili, o che la directory in cui dovrebber essere non sia protetta da scrittura, così che My handy Restaurant li possa creare.');
if (!defined('GLOBALMSG_CONFIG_SYSTEM')) define('GLOBALMSG_CONFIG_SYSTEM','<a href="../conf/index.php">Configura My handy Restaurant</a>');
if (!defined('GLOBALMSG_CONFIGURE_DATABASES')) define('GLOBALMSG_CONFIGURE_DATABASES','<a href="../admin/admin.php?class=accounting_database&amp;command=none"><br>Configura i database di My handy Restaurant</a>');
if (!defined('GLOBALMSG_DB_CONNECTION_ERROR')) define('GLOBALMSG_DB_CONNECTION_ERROR','Errore: Si è verificato un errore nella connessione al server del database: provare a controllare il file config.php e se il database sia attivo.');
if (!defined('GLOBALMSG_DB_NO_TABLES_ERROR')) define('GLOBALMSG_DB_NO_TABLES_ERROR','Errore: Non è presente alcuna tabella nel database, impossibile procedere.');
if (!defined('GLOBALMSG_NO_ACCOUNTING_DB_FOUND')) define('GLOBALMSG_NO_ACCOUNTING_DB_FOUND','Errore: non c\'è alcun database per la contaiblità;, impossbile procedere.<br>My Handy Restaurant ha bisogno di un database comune e almeno un database contabilità.');

if (!defined('GLOBALMSG_ACTION_IS_DEFINITIVE')) define('GLOBALMSG_ACTION_IS_DEFINITIVE','L\'azione è <b>definitiva</b>');

if (!defined('GLOBALMSG_FROM')) define('GLOBALMSG_FROM','da');
if (!defined('GLOBALMSG_FROM_TIME')) define('GLOBALMSG_FROM_TIME','dalle');
if (!defined('GLOBALMSG_FROM_DAY')) define('GLOBALMSG_FROM_DAY','dal');

if (!defined('GLOBALMSG_GO_BACK')) define('GLOBALMSG_GO_BACK','Torna indietro');

if (!defined('GLOBALMSG_INSERTING')) define('GLOBALMSG_INSERTING','Sto inserendo');
if (!defined('GLOBALMSG_ITEM')) define('GLOBALMSG_ITEM','Prodotto');
if (!defined('GLOBALMSG_INVOICE')) define('GLOBALMSG_INVOICE','Fattura');
if (!defined('GLOBALMSG_INVOICE_ASSOCIATED')) define('GLOBALMSG_INVOICE_ASSOCIATED','Fattura associata');
if (!defined('GLOBALMSG_INVOICE_PAID')) define('GLOBALMSG_INVOICE_PAID','Pagata');

if (!defined('GLOBALMSG_INDEX_WHO_ARE_YOU')) define('GLOBALMSG_INDEX_WHO_ARE_YOU','Chi sei?');
if (!defined('GLOBALMSG_INDEX_SUBMIT')) define('GLOBALMSG_INDEX_SUBMIT','Entra');

if (!defined('GLOBALMSG_NAME')) define('GLOBALMSG_NAME','Nome');
if (!defined('GLOBALMSG_NO')) define('GLOBALMSG_NO','No');
if (!defined('GLOBALMSG_NONE_FEMALE')) define('GLOBALMSG_NONE_FEMALE','Nessuna');
if (!defined('GLOBALMSG_NOTE')) define('GLOBALMSG_NOTE','Nota');
if (!defined('GLOBALMSG_NOTE_UPDATE')) define('GLOBALMSG_NOTE_UPDATE','Aggiorna la nota');

if (!defined('GLOBALMSG_ONLY')) define('GLOBALMSG_ONLY','solo');
if (!defined('GLOBALMSG_OF_DAY')) define('GLOBALMSG_OF_DAY','del');
if (!defined('GLOBALMSG_OR')) define('GLOBALMSG_OR','o');
if (!defined('GLOBALMSG_OTHER_FILE')) define('GLOBALMSG_OTHER_FILE','Scheda altro');
if (!defined('GLOBALMSG_OUTGOING_MANY')) define('GLOBALMSG_OUTGOING_MANY','uscenti');

if (!defined('GLOBALMSG_PAGE_TIME')) define('GLOBALMSG_PAGE_TIME','secondi per generare la pagina');
if (!defined('GLOBALMSG_PHONE')) define('GLOBALMSG_PHONE','Telefono');
if (!defined('GLOBALMSG_PLACE')) define('GLOBALMSG_PLACE','luogo');
if (!defined('GLOBALMSG_POS_CIRCUIT_FILE')) define('GLOBALMSG_POS_CIRCUIT_FILE','Scheda circuito POS');
if (!defined('GLOBALMSG_PRICE')) define('GLOBALMSG_PRICE','Prezzo');
if (!defined('MSG_PAPER_PRINT_REMOVE')) define('MSG_PAPER_PRINT_REMOVE','CANCELLATO');
if (!defined('MSG_PAPER_PRINT_TABLE')) define('MSG_PAPER_PRINT_TABLE','Tavolo');
if (!defined('MSG_PAPER_PRINT_PRIORITY')) define('MSG_PAPER_PRINT_PRIORITY','Priorita');
if (!defined('MSG_PAPER_PRINT_WAITER')) define('MSG_PAPER_PRINT_WAITER','Cameriere');
if (!defined('MSG_PAPER_PRINT_DISCOUNT')) define('MSG_PAPER_PRINT_DISCOUNT','Sconto');
if (!defined('MSG_PAPER_PRINT_TAXABLE')) define('MSG_PAPER_PRINT_TAXABLE','Imponibile');
if (!defined('MSG_PAPER_PRINT_TAX')) define('MSG_PAPER_PRINT_TAX','Imposta');
if (!defined('MSG_PAPER_PRINT_TAX_TOTAL')) define('MSG_PAPER_PRINT_TAX_TOTAL','Imposta totale');
if (!defined('MSG_PAPER_PRINT_CURRENCY')) define('MSG_PAPER_PRINT_CURRENCY','E');
if (!defined('MSG_PAPER_PRINT_TOTAL')) define('MSG_PAPER_PRINT_TOTAL','Totale');
if (!defined('MSG_PAPER_PRINT_BILL')) define('MSG_PAPER_PRINT_BILL','Ricevuta');
if (!defined('MSG_PAPER_PRINT_INVOICE')) define('MSG_PAPER_PRINT_INVOICE','Fattura');
if (!defined('MSG_PAPER_PRINT_RECEIPT')) define('MSG_PAPER_PRINT_RECEIPT','Scontrino');
if (!defined('MSG_PAPER_PRINT_NUMBER_ABBREVIATED')) define('MSG_PAPER_PRINT_NUMBER_ABBREVIATED','N.');
if (!defined('MSG_PAPER_PRINT_A_LOT')) define('MSG_PAPER_PRINT_A_LOT','ABB');
if (!defined('MSG_PAPER_PRINT_FEW')) define('MSG_PAPER_PRINT_FEW','POCO');
if (!defined('MSG_PAPER_PRINT_ATTENTION')) define('MSG_PAPER_PRINT_ATTENTION','ATTENZIONE');
if (!defined('MSG_PAPER_PRINT_WAIT')) define('MSG_PAPER_PRINT_WAIT','ASPETTARE');
if (!defined('MSG_PAPER_PRINT_GO')) define('MSG_PAPER_PRINT_GO','Partire');
if (!defined('MSG_PAPER_PRINT_GO_NOW')) define('MSG_PAPER_PRINT_GO_NOW','Partire Subito');
if (!defined('GLOBALMSG_PAPER_PRINT_TAKEAWAY')) define('GLOBALMSG_PAPER_PRINT_TAKEAWAY','Asporto');
if (!defined('GLOBALMSG_PERIOD')) define('GLOBALMSG_PERIOD','periodo');

if (!defined('GLOBALMSG_QUANTITY')) define('GLOBALMSG_QUANTITY','Quantita');

if (!defined('GLOBALMSG_RECEIPT_ID')) define('GLOBALMSG_RECEIPT_ID','Id');
if (!defined('GLOBALMSG_RECEIPT_ID_INTERNAL')) define('GLOBALMSG_RECEIPT_ID_INTERNAL','Id interno');
if (!defined('GLOBALMSG_RECEIPT_ANNULLED_RECEIPT')) define('GLOBALMSG_RECEIPT_ANNULLED_RECEIPT','La ricevuta è stata annullata');
if (!defined('GLOBALMSG_RECEIPT_ANNULLED_INVOICE')) define('GLOBALMSG_RECEIPT_ANNULLED_INVOICE','La fattura è stata annullata');
if (!defined('GLOBALMSG_RECEIPT_ANNULLED_BILL')) define('GLOBALMSG_RECEIPT_ANNULLED_BILL','Lo scontrino è stato annullato');
if (!defined('GLOBALMSG_RECEIPT_ANNULL_CONFIRM')) define('GLOBALMSG_RECEIPT_ANNULL_CONFIRM','Sei sicuro di voler cancellare le seguenti voci e tutte le voci di log associate?<br>L\'operazione è <b>irreversibile</b>.');

if (!defined('GLOBALMSG_RECORD_ANNULL')) define('GLOBALMSG_RECORD_ANNULL','Annulla la voce');
if (!defined('GLOBALMSG_RECORD_ANNULLED')) define('GLOBALMSG_RECORD_ANNULLED','Annullata');
if (!defined('GLOBALMSG_RECORD_ANNULLED_ABBREVIATED')) define('GLOBALMSG_RECORD_ANNULLED_ABBREVIATED','ANN');
if (!defined('GLOBALMSG_RECORD_NONE_SELECTED_ERROR')) define('GLOBALMSG_RECORD_NONE_SELECTED_ERROR','Non è stata selezionata alcuna voce');
if (!defined('GLOBALMSG_RECORD_NONE_FOUND_ERROR')) define('GLOBALMSG_RECORD_NONE_FOUND_ERROR','Non è stata trovata alcuna voce');
if (!defined('GLOBALMSG_RECORD_NONE_FOUND_PERIOD_ERROR')) define('GLOBALMSG_RECORD_NONE_FOUND_PERIOD_ERROR','Non è stata trovata alcuna voce nel periodo richiesto');
if (!defined('GLOBALMSG_RECORD_CHANGE_SEARCH')) define('GLOBALMSG_RECORD_CHANGE_SEARCH','Provare a cambiare ricerca o periodo');
if (!defined('GLOBALMSG_RECORD_DELETE_CONFIRM')) define('GLOBALMSG_RECORD_DELETE_CONFIRM','Sei sicuro di voler cancellare la seguente voce?');
if (!defined('GLOBALMSG_RECORDS_DELETE_CONFIRM')) define('GLOBALMSG_RECORDS_DELETE_CONFIRM','Sei sicuro di voler cancellare le seguenti voci?');
if (!defined('GLOBALMSG_RECORD_DELETE')) define('GLOBALMSG_RECORD_DELETE','Cancella la voce');
if (!defined('GLOBALMSG_RECORD_DELETE_SELECTED')) define('GLOBALMSG_RECORD_DELETE_SELECTED','Cancella le voci selezionate');
if (!defined('GLOBALMSG_RECORD_EDIT')) define('GLOBALMSG_RECORD_EDIT','Modifica la voce');
if (!defined('GLOBALMSG_RECORD_INSERT')) define('GLOBALMSG_RECORD_INSERT','Inserisci la voce');
if (!defined('GLOBALMSG_RECORD_OUTGOING')) define('GLOBALMSG_RECORD_OUTGOING','Uscente');
if (!defined('GLOBALMSG_RECORD_INCOMING')) define('GLOBALMSG_RECORD_INCOMING','Entrante');
if (!defined('GLOBALMSG_RECORD_INVOICE')) define('GLOBALMSG_RECORD_INVOICE','Fattura');
if (!defined('GLOBALMSG_RECORD_POS')) define('GLOBALMSG_RECORD_POS','POS');
if (!defined('GLOBALMSG_RECORD_BILL')) define('GLOBALMSG_RECORD_BILL','Scontrino');
if (!defined('GLOBALMSG_RECORD_CHEQUE')) define('GLOBALMSG_RECORD_CHEQUE','Assegno');
if (!defined('GLOBALMSG_RECORD_RECEIPT')) define('GLOBALMSG_RECORD_RECEIPT','Ricevuta');
if (!defined('GLOBALMSG_RECORD_DEPOSIT')) define('GLOBALMSG_RECORD_DEPOSIT','Versamento');
if (!defined('GLOBALMSG_RECORD_WIRE_TRANSFER')) define('GLOBALMSG_RECORD_WIRE_TRANSFER','Bonifico');
if (!defined('GLOBALMSG_RECORD_PAYMENT')) define('GLOBALMSG_RECORD_PAYMENT','Pagamento');
if (!defined('GLOBALMSG_RECORD_PAYMENT_DATE')) define('GLOBALMSG_RECORD_PAYMENT_DATE','Data del pagamento');
if (!defined('GLOBALMSG_RECORD_PAID')) define('GLOBALMSG_RECORD_PAID','Pagato');
if (!defined('GLOBALMSG_RECORD_THE_MANY')) define('GLOBALMSG_RECORD_THE_MANY','Le voci');
if (!defined('GLOBALMSG_RECORD_DELETE_OK_MANY')) define('GLOBALMSG_RECORD_DELETE_OK_MANY','sono state cancellate correttamente');
if (!defined('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY')) define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY','sono state cancellate correttamente dal log');
if (!defined('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY_2')) define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG_MANY_2','Le voci del log sono quindi state cancellate');
if (!defined('GLOBALMSG_RECORD_THE')) define('GLOBALMSG_RECORD_THE','La voce');
if (!defined('GLOBALMSG_RECORD_DELETE_OK')) define('GLOBALMSG_RECORD_DELETE_OK','è stata cancellata correttamente');
if (!defined('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG')) define('GLOBALMSG_RECORD_DELETE_OK_FROM_LOG','è stata cancellata correttamente dal log');
if (!defined('GLOBALMSG_RECORD_DELETE_NONE')) define('GLOBALMSG_RECORD_DELETE_NONE','Nessuna voce è stata selezionata');
if (!defined('GLOBALMSG_RECORD_ADD_OK')) define('GLOBALMSG_RECORD_ADD_OK','è stata aggiunta correttamente');
if (!defined('GLOBALMSG_RECORD_ADD_NONE')) define('GLOBALMSG_RECORD_ADD_NONE','Nessuna voce è stata aggiunta');
if (!defined('GLOBALMSG_RECORD_EDIT_OK')) define('GLOBALMSG_RECORD_EDIT_OK','è stata modificata correttamente');
if (!defined('GLOBALMSG_RECORD_EDIT_NONE')) define('GLOBALMSG_RECORD_EDIT_NONE','No voice has been updated');
if (!defined('GLOBALMSG_RECORD_EDIT_NOT_DONE')) define('GLOBALMSG_RECORD_EDIT_NOT_DONE','Nessuna voce è stata modificata');
if (!defined('GLOBALMSG_RECORD_TITLE_FOR')) define('GLOBALMSG_RECORD_TITLE_FOR','Voci per');
if (!defined('GLOBALMSG_RECORD_TITLE_FOR_NOT_IN_ADDRESSBOOK')) define('GLOBALMSG_RECORD_TITLE_FOR_NOT_IN_ADDRESSBOOK','Voci per i contatti non presenti in rubrica');
if (!defined('GLOBALMSG_RECORD_TITLE_FOR_TYPE')) define('GLOBALMSG_RECORD_TITLE_FOR_TYPE','Voci per i contatti del tipo');
if (!defined('GLOBALMSG_RECORD_TITLE_INCOME_TYPE')) define('GLOBALMSG_RECORD_TITLE_INCOME_TYPE','Incasso del tipo');
if (!defined('GLOBALMSG_RECORD_TITLE_INCOME')) define('GLOBALMSG_RECORD_TITLE_INCOME','Incasso');
if (!defined('GLOBALMSG_RECORD_TITLE_ALL')) define('GLOBALMSG_RECORD_TITLE_ALL','Tutte le voci');
if (!defined('GLOBALMSG_RECORD_PRINTABLE')) define('GLOBALMSG_RECORD_PRINTABLE','Versione stampabile (prova)');
if (!defined('GLOBALMSG_RECORD_TABLE_')) define('GLOBALMSG_RECORD_TABLE_','Versione stampabile (prova)');
if (!defined('GLOBALMSG_REPORT_ACCOUNT')) define('GLOBALMSG_REPORT_ACCOUNT','Conto');
if (!defined('GLOBALMSG_REPORT_GENERATE')) define('GLOBALMSG_REPORT_GENERATE','Genera report');
if (!defined('GLOBALMSG_REPORT_PERIOD')) define('GLOBALMSG_REPORT_PERIOD','Periodo report');

if (!defined('GLOBALMSG_STATS')) define('GLOBALMSG_STATS','Statistiche');
if (!defined('GLOBALMSG_STATS_DISHES_ORDERED')) define('GLOBALMSG_STATS_DISHES_ORDERED','Piatti ordinati');
if (!defined('GLOBALMSG_STATS_INGREDIENTS_ADDED')) define('GLOBALMSG_STATS_INGREDIENTS_ADDED','Ingredienti aggiunti');
if (!defined('GLOBALMSG_STATS_INGREDIENTS_REMOVED')) define('GLOBALMSG_STATS_INGREDIENTS_REMOVED','Ingredienti rimossi');
if (!defined('GLOBALMSG_STATS_MYSQL_TIME')) define('GLOBALMSG_STATS_MYSQL_TIME','secondi spesi per le query mySQL');
if (!defined('GLOBALMSG_STATS_RECORDS_SCANNED')) define('GLOBALMSG_STATS_RECORDS_SCANNED','voci esaminate');
if (!defined('GLOBALMSG_STATS_TOTAL_DEPTS')) define('GLOBALMSG_STATS_TOTAL_DEPTS','Totali settore');
if (!defined('GLOBALMSG_STATS_TOTAL_PERIOD')) define('GLOBALMSG_STATS_TOTAL_PERIOD','Totale periodo');
if (!defined('GLOBALMSG_STOCK_ADD_OK')) define('GLOBALMSG_STOCK_ADD_OK','Nuovo prodotto inserito correttamente');
if (!defined('GLOBALMSG_STOCK_ADD_ERROR')) define('GLOBALMSG_STOCK_ADD_ERROR','Si è verificato un errore durante l\'inserimento del nuovo prodotto');
if (!defined('GLOBALMSG_STOCK_ITEM_ADD')) define('GLOBALMSG_STOCK_ITEM_ADD','Aggiungi prodotto');
if (!defined('GLOBALMSG_STOCK_ITEM_NAME')) define('GLOBALMSG_STOCK_ITEM_NAME','Nome prodotto');
if (!defined('GLOBALMSG_STOCK_ITEM_INITIAL_QUANTITY')) define('GLOBALMSG_STOCK_ITEM_INITIAL_QUANTITY','Quantita iniziale');
if (!defined('GLOBALMSG_STOCK_MOVEMENTS')) define('GLOBALMSG_STOCK_MOVEMENTS','Movimenti magazzino');
if (!defined('GLOBALMSG_STOCK_MOVEMENT_INSERT')) define('GLOBALMSG_STOCK_MOVEMENT_INSERT','Inserisci un movimento magazzino');
if (!defined('GLOBALMSG_STOCK_MOVEMENT_INSERT_ERROR')) define('GLOBALMSG_STOCK_MOVEMENT_INSERT_ERROR','Errore insermento movimento magazzino');
if (!defined('GLOBALMSG_STOCK_MOVEMENT_NONE_ASSOCIATED_TO_INVOICE')) define('GLOBALMSG_STOCK_MOVEMENT_NONE_ASSOCIATED_TO_INVOICE','Nessun movimento di magazzino è associato alla fattura');
if (!defined('GLOBALMSG_STOCK_SEND_TO')) define('GLOBALMSG_STOCK_SEND_TO','Invia al magazzino');
if (!defined('GLOBALMSG_STOCK_SITUATION')) define('GLOBALMSG_STOCK_SITUATION','Stato magazzino');
if (!defined('GLOBALMSG_STOCK_DATA_UPDATE')) define('GLOBALMSG_STOCK_DATA_UPDATE','Aggiorna dati');
if (!defined('GLOBALMSG_STOCK_UPDATE_ERROR')) define('GLOBALMSG_STOCK_UPDATE_ERROR','Errore aggiornamento dati magazzino');
if (!defined('GLOBALMSG_STOCK_UPDATE_OK')) define('GLOBALMSG_STOCK_UPDATE_OK','Magazzino aggiornato correttamente');
if (!defined('GLOBALMSG_SUPPLIER_FILE')) define('GLOBALMSG_SUPPLIER_FILE','Scheda fornitore');

if (!defined('GLOBALMSG_TABLE')) define('GLOBALMSG_TABLE','Tavolo');
if (!defined('GLOBALMSG_TABLES')) define('GLOBALMSG_TABLES','Tavoli');
if (!defined('GLOBALMSG_TABLE_NONE_FOUND')) define('GLOBALMSG_TABLE_NONE_FOUND','Non è stato trovato nessun tavolo');
if (!defined('GLOBALMSG_TABLE_NONE_SELECTED')) define('GLOBALMSG_TABLE_NONE_SELECTED','Non è stato selezionato nessun tavolo');
if (!defined('GLOBALMSG_TABLE_THE')) define('GLOBALMSG_TABLE_THE','Il tavolo');
if (!defined('GLOBALMSG_TABLE_ID')) define('GLOBALMSG_TABLE_ID','Id (numero ordinamento)');
if (!defined('GLOBALMSG_TABLE_INSERT_NEW')) define('GLOBALMSG_TABLE_INSERT_NEW','Inserisci un nuovo tavolo');
if (!defined('GLOBALMSG_TABLE_INSERT')) define('GLOBALMSG_TABLE_INSERT','Inserisci tavolo');
if (!defined('GLOBALMSG_TABLE_UPDATE')) define('GLOBALMSG_TABLE_UPDATE','Modifica tavolo');
if (!defined('GLOBALMSG_TABLE_DELETE')) define('GLOBALMSG_TABLE_DELETE','Cancella tavolo');
if (!defined('GLOBALMSG_TABLE_NUMBER')) define('GLOBALMSG_TABLE_NUMBER','Numero o Nome (visualizzato)');
if (!defined('GLOBALMSG_TABLE_TABLE_ID')) define('GLOBALMSG_TABLE_TABLE_ID','Id');
if (!defined('GLOBALMSG_TABLE_TABLE_NUMBER')) define('GLOBALMSG_TABLE_TABLE_NUMBER','Numero/Nome');
if (!defined('GLOBALMSG_TABLE_TAKEAWAY')) define('GLOBALMSG_TABLE_TAKEAWAY','Asporto');
if (!defined('GLOBALMSG_TAXABLE')) define('GLOBALMSG_TAXABLE','imponibile');
if (!defined('GLOBALMSG_TAX')) define('GLOBALMSG_TAX','Tassa');
if (!defined('GLOBALMSG_TAX_NUMBER')) define('GLOBALMSG_TAX_NUMBER','Codice fiscale');
if (!defined('GLOBALMSG_TAX_MANY')) define('GLOBALMSG_TAX_MANY','Tasse');
if (!defined('GLOBALMSG_TAX_TO_PAY')) define('GLOBALMSG_TAX_TO_PAY','Per il periodo selezionato, le tasse da pagare sono');
if (!defined('GLOBALMSG_TAX_TO_PAY_INVOICE_EXCLUDED')) define('GLOBALMSG_TAX_TO_PAY_INVOICE_EXCLUDED','escluse le fatture non pagate');
if (!defined('GLOBALMSG_TAX_TO_PAY_INVOICE_INCLUDED')) define('GLOBALMSG_TAX_TO_PAY_INVOICE_INCLUDED','incluse le fatture non pagate');
if (!defined('GLOBALMSG_TIME')) define('GLOBALMSG_TIME','Ora');
if (!defined('GLOBALMSG_TYPE')) define('GLOBALMSG_TYPE','Tipo');
if (!defined('GLOBALMSG_TO')) define('GLOBALMSG_TO','a');
if (!defined('GLOBALMSG_TO_DAY')) define('GLOBALMSG_TO_DAY','al');
if (!defined('GLOBALMSG_TO_TIME')) define('GLOBALMSG_TO_TIME','alle');
if (!defined('GLOBALMSG_TOTAL')) define('GLOBALMSG_TOTAL','totale');

if (!defined('GLOBALMSG_VAT_ACCOUNT')) define('GLOBALMSG_VAT_ACCOUNT','Partita IVA');
if (!defined('GLOBALMSG_VAT_CALCULATION')) define('GLOBALMSG_VAT_CALCULATION','Calcolo IVA');

if (!defined('MSG_WAITER_NOT_CONNECTED_ERROR')) define('MSG_WAITER_NOT_CONNECTED_ERROR','Errore: Non sei connesso.');

if (!defined('GLOBALMSG_WAITER')) define('GLOBALMSG_WAITER','Cameriere');
if (!defined('GLOBALMSG_WAITERS')) define('GLOBALMSG_WAITERS','Camerieri');
if (!defined('GLOBALMSG_WAITER_NONE_FOUND')) define('GLOBALMSG_WAITER_NONE_FOUND','Non è stato trovato nessun cameriere');
if (!defined('GLOBALMSG_WAITER_NONE_SELECTED')) define('GLOBALMSG_WAITER_NONE_SELECTED','Non è stato selezionato nessun cameriere');
if (!defined('GLOBALMSG_WAITER_THE')) define('GLOBALMSG_WAITER_THE','Il cameriere');
if (!defined('GLOBALMSG_WAITER_NAME')) define('GLOBALMSG_WAITER_NAME','Nome');
if (!defined('GLOBALMSG_WAITER_LANGUAGE')) define('GLOBALMSG_WAITER_LANGUAGE','Lingua (Formato internaz. a 2 caratteri: ad es.: en, it, de, ...)');
if (!defined('GLOBALMSG_WAITER_CAN_OPEN_CLOSED_TABLES')) define('GLOBALMSG_WAITER_CAN_OPEN_CLOSED_TABLES','Puo aprire i tavoli chiusi (e modificare il prezzo dei piatti generici)');
if (!defined('GLOBALMSG_WAITER_INSERT_NEW')) define('GLOBALMSG_WAITER_INSERT_NEW','Inserisci un nuovo cameriere');
if (!defined('GLOBALMSG_WAITER_INSERT')) define('GLOBALMSG_WAITER_INSERT','Inserisci cameriere');
if (!defined('GLOBALMSG_WAITER_UPDATE')) define('GLOBALMSG_WAITER_UPDATE','Modifica cameriere');
if (!defined('GLOBALMSG_WAITER_DELETE')) define('GLOBALMSG_WAITER_DELETE','Cancella cameriere');
if (!defined('GLOBALMSG_WAITER_TABLE_NAME')) define('GLOBALMSG_WAITER_TABLE_NAME','Nome');
if (!defined('GLOBALMSG_WAITER_TABLE_LANGUAGE')) define('GLOBALMSG_WAITER_TABLE_LANGUAGE','Lingua');
if (!defined('GLOBALMSG_WAITER_TABLE_CAN_OPEN_CLOSED_TABLES')) define('GLOBALMSG_WAITER_TABLE_CAN_OPEN_CLOSED_TABLES','Apre tavoli chiusi');
if (!defined('GLOBALMSG_WEBSITE')) define('GLOBALMSG_WEBSITE','Sito web');

if (!defined('GLOBALMSG_YES')) define('GLOBALMSG_YES','Si');



$msg_admin_confirm_reset_orders="
<b>Vuoi davvero azzerare TUTTI gli ordini?</b><br>
Questa operazione &eacute; <b>irreversibile</b> e causerà
 la perdita di tutte le comande prese finora.";
$msg_admin_confirm_reset_sources="
<b>Vuoi davvero azzerare TUTTI i tavoli?</b><br>
Questa operazione è <b>irreversibile</b> e causerà
 <b>anche</b> la perdita di tutte le comande prese finora.";
$msg_admin_confirm_reset_access_times="
<b>Vuoi davvero resettare TUTTI i tempi di accesso?</b><br>
Questa operazione è <b>irreversibile</b> e causerà
 la momentanea interruzione del servizio di protezione tavoli.<br>
 L'uso di questa funzione è consigliato solo in caso di cambiamenti di
 orario dell'orologio di sistema.";
$msg_reset_orders="Azzera tutti gli ordini";
$msg_reset_access_times="Azzera tutti i tempi di accesso";
$msg_reset_sources="Azzera tutti i tavoli";
$but_reset_access_times="Azzera";
$but_reset_orders="Azzera";
$but_reset_sources="Azzera";
$msg_reset_access_times_ok="Tutti i tempi di accesso sono stati azzerati";
$msg_reset_orders_ok="Tutti gli ordini sono stati azzerati";
$msg_reset_sources_ok="Tutti i tavoli e gli ordini sono stati azzerati";
$msg_admin_confirmhalt="Vuoi spegnere il computer centrale?";
$msg_halt="Spegni il PC";
$but_halt="Spegni";

//Problemi con la variabile $halttime da un errore (mod_fcgid: stderr: PHP Notice:  Undefined variable: halttime) tolta la riga e sostituita
//$msg_halt_ok="Procedura di spegnimento avviata. Spegenere il pc tra circa ".$halttime." minuti";

$msg_halt_ok="Procedura di spegnimento avviata. Spegenere il pc tra pochi minuti";

?>
