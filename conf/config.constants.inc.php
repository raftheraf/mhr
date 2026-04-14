<?php
if (!defined('CONF_DEBUG')) {
define('CONF_DEBUG',                                    0);	     // In production set to 0, prints all debug info in the relative file and some debug data on screen
define('CONF_DEBUG_PRINT_GENERATING_TIME',              0);	     // In production set to 0, allows printing of the generating time
define('CONF_DEBUG_REPORT_NOTICES',                     0);	     // In production set to 0, uses E_ALL php error reporting
define('CONF_DEBUG_LANG_DISABLED',                      0);	     // disables the language functions (both db and xml)
define('CONF_DEBUG_PRINT_GENERATING_TIME_ONLY_IF_HIGH', 0);	     // stampa tempo generazione pagnina solo se maggiore del valore sotto
define('CONF_DEBUG_PRINT_GENERATING_TIME_TRESHOLD',     0.25);   // tempo massimo di generazione pagina in secondi 0.25 (valore standard)
define('CONF_DEBUG_DONT_DELETE',                        0);	     // if active the order will never be deleted
define('CONF_DEBUG_DONT_SET_PRINTED',                   0);	     // if the flag is active, the flag printed in orders table won't be set
define('CONF_DEBUG_DONT_PRINT',                         0);	     // if active, printing won't work
define('CONF_DEBUG_PRINT_MARKUP',                       0);	     // if active all the unused markup will not be deleted before printing
define('CONF_DEBUG_PRINT_TICKET_DEST',                  0);	     // if active the print destination will be printed inserere {destination} nei .tpl
define('CONF_DEBUG_PRINT_DISPLAY_MSG',                  0);	     // Visualizza i testi inviati alla stampante
define('CONF_DEBUG_DISPLAY_MYSQL_QUERIES',              0);	     // displays the number of common_query() calls
define('CONF_DEBUG_DISABLE_FUNCTION_INSERT',            0);	     // enables the use of functions besides of numbers (1+1 instead of 2)
define('CONF_DEBUG_PRINT_PAGE_SIZE',                    0);	     // Stampa la dimenzione della pagina generata in kb (immagini escluse)


define('CONF_SHOW_DEFAULT_ON_MISSING', 1);	    // if a lang value in the db is empty, writes the corresponding value in the default language instead of the lang code
define('CONF_TOPLIST_HIDE_PRIORITY',   1);	    // sets if priority button should be displayed in the toplist box
define('CONF_TOPLIST_HIDE_QUANTITY',   1);	    // sets if quantity button should be displayed in the toplist box
define('CONF_TOPLIST_SAVED_NUMBER',    100);		// quantity of orders to be saved for toplist statistics
define('CONF_ALLOW_EASY_DELETE',       true);	 	// if true shows the little trash icon when an order has quantity 1
define('CONF_XML_TRANSLATIONS',        false);	// if true uses the xml language files instead of the database. It is recomended to leave this funciton off unless you know what you are doing
define('CONF_PRINT_BARCODES',          false);	// if true prints the barcode with the order ID for each order
																								// it requires a barcode-ready printer to work and is as of today a useless feature

//Parametri per una connessione a Javapos mai testati
//it requires a JavaPOS fiscal printer and fabioPOS to be installed to work
define('CONF_PRINT_JAVAPOS_RECEIPT', false);	      // if true prints the receipt also to a JavaPOS printer, through the fabioPOS
define('CONF_PRINT_JAVAPOS_ADDRESS', '127.0.0.1');	// hostname or IP address of the fabioPOS server
define('CONF_PRINT_JAVAPOS_PORT',    9999);	       // port number of the fabioPOS server


define('CONF_PRINT_TICKETS_ONE_PAGE_PER_TABLE', true);	 // if true prints all the priorities on one ticket per table
define('CONF_PRINT_TICKET_ID',                  true);	 // if true prints the ticket ID at the end of each ticket
define('CONF_MINUTES_BEFORE_PRINTING_TAKEAWAY', 5);	    // number of minutes to be wait to have the takeaway time printed on tickets
define('CONF_PRINT_ONLY_HIGH_PRIORITY_NUMBER',  false);	// if true prints the ticket ID at the end of each ticket

																										// Mette un Pallino davanti al piatto stmapato
define('CONF_COLOUR_PRINTED',				true);			// if on the user can see the elapsed time from the printing of the order ticket as a linear color.
define('CONF_COLOUR_PRINTED_COLOUR',		'red');	// possible values: red, green, blue, magenta, yellow, cyan, grey. default: yellow
define('CONF_COLOUR_PRINTED_MAX_TIME',       60);	  // after how much time in mins should the max colour be reached
define('CONF_TIME_SINCE_PRINTED',            1);	  // if on the elapsed time since printing will be written aside the dish name in the orders list
define('CONF_ENCRYPT_PASSWORD',              false);// if true the passwords will be encrypted with the best available method, otherwise a MD5 checksum will be prepared
																										// the checksum is a bit less secure, but ensures that the password will be the same on every machine,
																										// otherwise changing the OS or upgrading it could cause all the passwords to be unusable (recreate the users is the only solution)

define('CONF_DISPLAY_MYSQL_ERRORS',          true);	  // if on the mysql errors will be displayed to the users and logged to file, otherwise they will be only logged to errors file
define('CONF_SQL_RESUME_ENABLED',            false);  // if on the sql upgrades and restores will be stopped and resumed to allow progress display (HIGHLY EXPERIMENTAL!!!)
define('CONF_SHOW_SUMMARY_ON_LIST',          true);	  // if on a summary of the data about the ingredients/dishes will be displayed in the tables in admin section
																										  // (slows the page generation by a factor of about 4)
define('CONF_SHOW_PERCENT_INSERTED_ON_LIST', true);	  // if on the percent of inserted ingredient quantities will be displayed in the table in admin section
																										  //(slows the page generation by a factor of about 4)
define('CONF_UNIT_MASS',                     'g');	  // measure unit for weigths
define('CONF_UNIT_VOLUME',                   'l');	  // measure unit for volumes
define('CONF_FORCE_UPGRADE',                 false);	// if true forces upgrading, otherwise only displays suggestion with ink in messages
define('CONF_STOCK_QUANTITY_ALARM',          10);	    // treshold for low quantity in stock messages
define('CONF_FAST_ORDER',                    true);	  // enables the fast order form in the orders page (also disables the keyboad shurtcuts on the orders form)

/*
Cache system for db queries
0: disable the db query caching system (low performance)
1: cache on page (cache is reset on page reload)
2: cache on session (cache is reset on user connection) (high performance, but updates from other users cannot be seen until disconnection!)
3: cache data on page and lang on session (suggested)
*/
define('CONF_CACHE_TYPE',3);

/************************************************************************************
* YOU SHOULDN'T MODIFY ANYTHING BELOW THIS LINE!
* (unless you really know what you're doing)
*************************************************************************************/
define('ERROR_FILE',ROOTDIR.'/error.log');
define('DEBUG_FILE',ROOTDIR.'/debug.log');

define('MIN_SEARCH_LENGTH',		0);

define('SERVICE_ID',         -1);
define('MOD_ID',             -2);
define('DISCOUNT_ID',        -3);
define('ROMANA_QUOTA_ID',    -4);

define('LANG_TABLES_NUMBER', 	3);			// The number of tables added per language to the db
define('LANG_FILES_NUMBER',  	1);			// The number of files added per language to the lang dir

define('AUTOSELECT_FIRST',   	0);			// if 1: selects the first item in mods' quantity to be modified
																			// else selects the last item in mods' quantity to be modified



define('CONF_TRANSLATE_ALWAYS_CHECK_TABLES',0); // if yes checks for translation problems in the tables every time the translators page is loaded (heavy CPU load).
																								// otherwise prints a message inviting them to do that

define('REFRESH_TIME',0.2);


define('MAX_QUANTITY',         200); // max displayed quanitty in quantity <select > boxes.
																		 // This is NOT the maximux allowed quantity, so don't use this for security matters.

define('USER_BIT_WAITER',      0);
define('USER_BIT_CASHIER',     1);
define('USER_BIT_STOCK',       2);
define('USER_BIT_CONTACTS',    3);
define('USER_BIT_MENU',        4);
define('USER_BIT_USERS',       5);
define('USER_BIT_ACCOUNTING',  6);
define('USER_BIT_TRANSLATION', 7);
define('USER_BIT_CONFIG',      8);
define('USER_BIT_MONEY',       9);

define('USER_BIT_LAST',        9);

define('SHOW_ALL_USERS',           0);
define('SHOW_WAITER_ONLY',         1);
define('SHOW_CASHIER_ONLY',        2);
define('SHOW_ADMIN_ONLY',          3);
define('SHOW_WAITER_CASHIER',      4);

define('ERROR_LEVEL_USER',     0);
define('ERROR_LEVEL_DEBUG',    1);
define('ERROR_LEVEL_ERROR',    2);

define('TABLE_INGREDIENTS',    1);
define('TABLE_DISHES',         2);
define('TABLE_CATEGORIES',     3);
define('TABLE_TABLES',         4);
define('TABLE_USERS',          5);
define('TABLE_AUTOCALC',       6);
define('TABLE_VAT_RATES',      7);
define('TABLE_PRINTERS',       8);
define('TABLE_STOCK_OBJECTS',  9);
define('TABLE_STOCK_DISHES',   10);

define('LICENSE_FILE',ROOTDIR.'/docs/LICENSE');
$halttime=2;

// installer: mysql dump files locations
$location['common']['complete']  = 'myhandyrestaurant_common_complete.sql';
$location['account']['complete'] = 'myhandyrestaurant_account_complete.sql';
$location['common']['struct']    = 'myhandyrestaurant_common_struct.sql';
$location['account']['struct']   = 'myhandyrestaurant_account_struct.sql';


define('TYPE_NONE',             0);
define('TYPE_DISH',             1);
define('TYPE_INGREDIENT',       2);

define('INGRED_TYPE_INCLUDED',  1);
define('INGRED_TYPE_AVAILABLE', 2);


define('UNIT_TYPE_NONE',        0);
define('UNIT_TYPE_MASS',        1);
define('UNIT_TYPE_VOLUME',      2);
define('UNIT_TYPE_MONEY',       3);

$allowed_not_upgraded  = array('upgrade.php','connect.php','export_db.php');

global $convertion_constants;
$convertion_constants = array (
	// weight US
	'oz-kg'=>0.02834952313,
	'lb-kg'=>0.45359237,
	// weight IS
	'mg-kg'=>0.000001,
	'cg-kg'=>0.00001,
	'dg-kg'=>0.0001,
	'g-kg'=>0.001,
	'dag-kg'=>0.001,
	'hg-kg'=>0.1,
	// volume US
	'gal-l'=>3.785411784,
	'floz-l'=>0.02957352956,
	// volume IS
	'ml-l'=>0.001,
	'cl-l'=>0.01,
	'dl-l'=>0.1,
	'hl-l'=>100.0,
);

global $unit_types_volume;
$unit_types_volume = array ('gal','floz','ml','cl','dl','l','hl');
global $unit_types_mass;
$unit_types_mass = array ('oz','lb','mg','cg','dg','g','dag','hg','kg');

//define('CONF_HTTP_ROOT_DIR','http://192.168.0.50/handyrestaurant/demo/');
define('CONF_HTTP_ROOT_DIR',ROOTDIR.'/');

define('CONF_JS_URL',         CONF_HTTP_ROOT_DIR."generic.js");
define('CONF_CSS_URL',        CONF_HTTP_ROOT_DIR."styles.css");

define('CONF_JS_URL_CONFIG',  "./generic.js");
define('CONF_CSS_URL_CONFIG', "./styles.css");

//CONF_HTTP_ROOT_DIR='http://'.$_SERVER['SERVER_NAME'].'/handyrestaurant/';
// images used

define('IMAGE_CUSTOMER_KNOWN', CONF_HTTP_ROOT_DIR."images/personal.png");
define('IMAGE_MENU',           CONF_HTTP_ROOT_DIR."images/gohome.png");
define('IMAGE_NO',             CONF_HTTP_ROOT_DIR."images/agt_action_fail.png");
define('IMAGE_OK',             CONF_HTTP_ROOT_DIR."images/agt_action_success.png");
define('IMAGE_PRINT',          CONF_HTTP_ROOT_DIR."images/print.png");
define('IMAGE_RICERCA_VELOCE', CONF_HTTP_ROOT_DIR."images/ricerca_veloce.png");
//RTR
define('IMAGE_PRINT_FAST',     CONF_HTTP_ROOT_DIR."images/print_fast.png");
define('IMAGE_SOURCE',         CONF_HTTP_ROOT_DIR."images/source.png");
define('IMAGE_TRASH',          CONF_HTTP_ROOT_DIR."images/trash.png");
define('IMAGE_LITTLE_TRASH',   CONF_HTTP_ROOT_DIR."images/little_trash.png");
define('IMAGE_YES',            CONF_HTTP_ROOT_DIR."images/agt_action_success.png");
define('IMAGE_BACK',           CONF_HTTP_ROOT_DIR."images/back.jpg");
define('IMAGE_CLOSE',          CONF_HTTP_ROOT_DIR."images/newclose.png");
define('IMAGE_MINUS',          CONF_HTTP_ROOT_DIR."images/down.png");
define('IMAGE_PLUS',           CONF_HTTP_ROOT_DIR."images/up.png");
define('IMAGE_FIND',           CONF_HTTP_ROOT_DIR."images/find.png");
define('IMAGE_NEW',            CONF_HTTP_ROOT_DIR."images/new.png");
define('IMAGE_TOPLIST',        CONF_HTTP_ROOT_DIR."images/top_list.gif");
define('IMAGE_EDITDISH',       CONF_HTTP_ROOT_DIR."images/edit_dish.jpg");
define('IMAGE_BLANK',       	 CONF_HTTP_ROOT_DIR."images/cm_fill.gif");


// all the colors used in background and tables

define('COLOR_TABLE_ULTIMA_OPERAZIONE',  '#c00003');
define('COLOR_TABLE_GENERAL',            '#ffcc99');
define('COLOR_TABLE_TOTAL',              '#ffeebb');
define('COLOR_HIGHLIGHT',                '#dddddd');
define('COLOR_BACK_OK',                  '#6ffa7d');
define('COLOR_BACK_ERROR',               '#ff0d11');
define('COLOR_ORDER_PRINTED',            '#ffffff');
define('COLOR_ORDER_TO_PRINT',           '#6ffa7d');
define('COLOR_ORDER_SUSPENDED',          '#ff9966');
define('COLOR_ORDER_EXTRACARE',          '#2206db');
define('COLOR_ERROR',                    '#ff9966');
define('COLOR_OK',                       '#6ffa7d');

//Colori dei tavoli
define('COLOR_TABLE_FREE',               '#ffffff'); //
define('COLOR_TABLE_MINE',               '#6ffa7d'); //
define('COLOR_TABLE_OTHER',              '#aaffaf'); //
define('COLOR_TABLE_CLOSED_OPENABLE',    '#cd5c5c'); //
define('COLOR_TABLE_NOT_OPENABLE',       '#ffa07a'); //tavolo Pagato (pagato ma è ancora occupato)
define('COLOR_TABLE_GENERIC_NOT_PRICED', '#8890ff'); //
define('COLORE_TAVOLO_DA_ASSOCIARE',     '#ffd700'); //
define('COLOR_TABLE_SCONTRINATO',        '#f08080'); //tavolo chiuso con scontrino emesso

define('COLOR_ORDER_PRIORITY_PRINTED', '#ffffff');
define('COLOR_ORDER_PRIORITY_1',       '#e4e4e4');
define('COLOR_ORDER_PRIORITY_2',       '#00ffff');
define('COLOR_ORDER_PRIORITY_3',       '#ef4e6e');
define('COLOR_ORDER_PRIORITY_4',       '#ff00ff');

define('MGMT_COLOR_BACKGROUND', 		'#feefac');
$mgmt_color_background=					"#feefac";
define('MGMT_COLOR_TABLEBG',    		'#ffca68');
$mgmt_color_tablebg=					"#ffca68";
define('MGMT_COLOR_CELLBG0',    		'#ffe9b7');
$mgmt_color_cellbg0=					"#ffe9b7";
define('MGMT_COLOR_CELLBG1',    		'#faff97');
$mgmt_color_cellbg1=					"#faff97";

//RTR
define('TEMPO_MASSIMO_ORDINI',              '300');	    //Dopo quanto tempo in sec compare ICONA_ORDINE_DA_STAMPARE
																												//sul tavolo. (default 5 minuti 60*5=300sec)
																												//ordine da stampare. 20 minuti = 20min x 60sec = 1200sec
define('ICONA_ORDINE_DA_STAMPARE',          '🔔');			//Emoticon oppure link all'immagine che appare sul tavolo quando un ordine
																												//che non è stato stampato oltre il tempo massimo stabilito da TEMPO_MASSIMO_ORDINI


define('TEMPO_MASSIMO_TAVOLO_FERMO', '1800');	   //Default 30 minuti (30*60=1800) Dopo quanto tempo in sec coppare una icona per indicare che
																								//se il tavolo è fermo da troppo tempo, compare un diavoletto.
define('ICONA_TAVOLO_TROPPO_TEMPO_FERMO',  '😈');			//Icona o link ad immagine che appare sul tavolo fermo da troppo tempo

//Paramtri per la configurazione della Print-F
define ('PATH_MULTIDRIVER', 'C:\Multidriver\MULTIDRIVER_APP.exe'); //percorso sul server dove è installato il driver es. "C:\Multidriver\MULTIDRIVER_APP.exe"
define ('PATH_SCONTRINO_INP', '../PATH_IN/scontrino.inp'); //percorso relativo dove l'eseguibile del driver trova il file scontrino.inp ad esempio "../PATH_IN/scontrino.inp"
define ('PATH_PAPER_OUT','../PATH_OUT/Paper.out'); //percorso relativo dove l'eseguibile del driver trova il file Papar.out ad esempio "../PATH_OUT/Paper.out"
define ('PATH_SCONTRINO_OUT','../PATH_OUT/scontrino.out'); //percorso relativo dove l'eseguibile del driver trova il file scontrino.out ad esempio "../PATH_OUT/scontrino.out"

//Percorso della stampante in rete
define ('ADDRESS_PRINTF', '192.168.1.10:23'); // da inserire nel file del templates/default/prints/receipt.tpl
define ('ABILITA_LOTTERIA_SCONTRINI', true); // se true abilita la funzione per l'invio al registratore del codice lotteria

//Messaggio WhatsApp
define ('WHATSAPP_MSG', '
il tuo tavolo è ora disponibile, ti aspettiamo.%0A
Attendiamo tua pronta CONFERMA.%0A
%0A
Avvisaci se non puoi più arrivare.%0A
%0A
Ristorante Biscione');

}
?>
