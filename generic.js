function doit(tID, isOver)
{
  var theRow = document.getElementById(tID)

 theRow.style.backgroundColor = (isOver) ? '#0000ff' : '#ffffff';
}

<!-- Distributed by Hypergurl http://www.hypergurl.com -->

function changeto(highlightcolor){
source=event.srcElement
if (source.tagName=="TD"||source.tagName=="TABLE")
return
while(source.tagName!="TR")
source=source.parentElement
if (source.style.backgroundColor!=highlightcolor&&source.id!="ignore")
source.style.backgroundColor=highlightcolor
}

function changeback(originalcolor){
if (event.fromElement.contains(event.toElement)||source.contains(event.toElement)||source.id=="ignore")
return
if (event.toElement!=source)
source.style.backgroundColor=originalcolor
}

/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   interger  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 3.3 ... Opera changes colors set via HTML to rgb(r,g,b) format so fix it
    if (currentColor.indexOf("rgb") >= 0)
    {
        var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,
                                     currentColor.indexOf(')'));
        var rgbValues = rgbStr.split(",");
        currentColor = "#";
        var hexChars = "0123456789ABCDEF";
        for (var i = 0; i < 3; i++)
        {
            var v = rgbValues[i].valueOf();
            currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
        }
    }

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
            // Garvin: deactivated onclick marking of the checkbox because it's also executed
            // when an action (like edit/delete) on a single item is performed. Then the checkbox
            // would get deactived, even though we need it activated. Maybe there is a way
            // to detect if the row was clicked, and not an item therein...
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
            // document.getElementById('id_rows_to_delete' + theRowNum).checked = false;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function

function redir(url) {
  //aumenta il tempo di rendirizzamento
  //setTimeout(function(){document.location.href=url},100);

  if (url && url.toLowerCase().indexOf(".phpmhr_tab_id=") !== -1) {
    url = url.replace(/\.phpmhr_tab_id=/ig, ".php?mhr_tab_id=");
  }
  
  // Aggiungi mhr_tab_id se disponibile (per celle della tabella, ecc.)
  if (window.sessionStorage) {
    var tabId = sessionStorage.getItem("mhr_tab_id");
    if (tabId && url) {
      // Controlla se l'URL ha già query parameters
      var sep = (url.indexOf("?") === -1) ? "?" : "&";
      // Evita di aggiungere il parametro due volte
      if (url.indexOf("mhr_tab_id=") === -1) {
        url += sep + "mhr_tab_id=" + encodeURIComponent(tabId);
      }
    }
  }
  
  document.location.href=url;
  myFunction();
	return false;
}

 function click(e) {
 if (document.all) {
 if (event.button == 2) {
 //var messagel="PC's Rule ha! :)\n"
 //alert(navigator.appName+" \nver: "+navigator.appVersion);
  return false;
   }
 }
 if (document.layers) {
 if (e.which == 3) {
 //var messagel="PC's Rule ha! :)\n"
 //alert(navigator.appName+" \nver: "+navigator.appVersion);
 return false;
    }
   }
 }

if (navigator.appName!="Microsoft Pocket Internet Explorer") {
 if (document.layers) {
 document.captureEvents(Event.MOUSEDOWN);
 }
 document.onmousedown=click;
}

function type_insert_check(form_name,elem_name,id){
	doc_form=eval("document."+form_name+'.'+elem_name);
	doc_form[id].checked=true;
}

function order_select($dishid,form_name){
	frm=eval("document."+form_name);
	frm.dishid.value=$dishid;
	frm.submit();
	return(false);
}

function color_table(color){
	document.edit_form_table.idcolor.value=color;
	document.getElementById("idcolor").style.backgroundColor=color;
	tabcolor.tbodies[0].trows[0].cells[0].innerText=color;
	//tabcolor.tbodies[0].trows[0].cells[0].style.backgroundColor=color;
	tdcolor.style.backgroundColor=color;
	return(false);
}

function color_select(color){
	document.edit_form_category.htmlcolor.value=color;
	document.getElementById("idcolor").style.backgroundColor=color;
	tabcolor.tbodies[0].trows[0].cells[0].innerText=color;
	//tabcolor.tbodies[0].trows[0].cells[0].style.backgroundColor=color;
	tdcolor.style.backgroundColor=color;
	return(false);
}

function mod_set($letter){
	document.form1.letter.value=$letter;
	document.form1.last.value=0;
	document.form1.submit();
	return(false);
}

function discount_switch(){

	if(document.form_discount.discount_type[0].checked==true){
		document.form_discount.percent.disabled=true;
		document.form_discount.amount.disabled=true;
	} else if (document.form_discount.discount_type[1].checked==true){
		document.form_discount.percent.disabled=false;
		document.form_discount.amount.disabled=true;
	} else if (document.form_discount.discount_type[2].checked==true){
		document.form_discount.percent.disabled=true;
		document.form_discount.amount.disabled=false;
	}

	return(false);
}

function pagamento_carte_switch(){

	if(document.form_type.tipo_corrispettivo[0].checked==true){
		document.form_type.pagato_carte_di_credito.disabled=false;
	} else if (document.form_type.tipo_corrispettivo[1].checked==true){
		document.form_type.pagato_carte_di_credito.disabled=true;
	} else if (document.form_type.tipo_corrispettivo[2].checked==true){
		document.form_type.pagato_carte_di_credito.disabled=true;
	} else if (document.form_type.tipo_corrispettivo[3].checked==true){
		document.form_type.pagato_carte_di_credito.disabled=true;
	} else if (document.form_type.tipo_corrispettivo[4].checked==true){
		document.form_type.pagato_carte_di_credito.disabled=true;
	}

	// ogni cambio di tipo corrispettivo azzera l'importo carta
	if (document.form_type && document.form_type.pagato_carte_di_credito) {
		document.form_type.pagato_carte_di_credito.value = '';
	}

	var wrapBtn = document.getElementById('wrap_btn_pos_totale');
	if (wrapBtn) {
		if (document.form_type.tipo_corrispettivo[1].checked==true){
			wrapBtn.style.display='';
		} else {
			wrapBtn.style.display='none';
		}
	}

	// mostra il pulsante "+ CARTA" solo con CONTANTI, altrimenti nasconde tutto il blocco carta
	var spanCarta = document.getElementById('wrap_importo_carta');
	var btnCarta  = document.getElementById('btn_mostra_importo_carta');

	if (document.form_type.tipo_corrispettivo[0].checked==true) {
		// CONTANTI: mostra il pulsante (se non è già stato cliccato)
		if (btnCarta) {
			btnCarta.style.display='';
		}
		// non tocco spanCarta: se l'utente ha già aperto l'importo, rimane visibile
	} else {
		// altri tipi pagamento: nasconde sia pulsante che importo carta
		if (btnCarta) {
			btnCarta.style.display='none';
		}
		if (spanCarta) {
			spanCarta.style.display='none';
		}
	}

	return(false);
}

function mostra_importo_carta(){
	var span = document.getElementById('wrap_importo_carta');
	var btn  = document.getElementById('btn_mostra_importo_carta');
	var btnPos = document.getElementById('btn_pos_carta');

	if(span){
		span.style.display='';
	}
	if(btn){
		btn.style.display='none';
	}

	if(document.form_type && document.form_type.pagato_carte_di_credito){
		var input = document.form_type.pagato_carte_di_credito;
		input.disabled=false;
		try{
			input.focus();
			if(input.select){
				input.select();
			}
		}catch(e){}

		// mostra il bottone POS solo quando c'è un importo valido (>0)
		if (btnPos && !input._posCartaListenerAdded) {
			input._posCartaListenerAdded = true;
			input.addEventListener('input', function(){
				var rawVal = (this.value || '').replace(',', '.').trim();
				var amountNum = parseFloat(rawVal);
				if (!isNaN(amountNum) && amountNum > 0) {
					btnPos.style.display = '';
				} else {
					btnPos.style.display = 'none';
				}
			});
		}
	}

	return false;
}

function invia_pos_totale(el){
	var url = el.getAttribute('data-pos-url');
	var amount = el.getAttribute('data-pos-amount');

	if(!url || !amount){
		alert('Impossibile inviare al POS: dati mancanti.');
		return false;
	}

	if(parseFloat(amount) <= 0){
		alert('Importo zero: non è possibile inviare al POS.');
		return false;
	}

	try{
		if(window.fetch){
			fetch(url, { method: 'GET', cache: 'no-store' })
				.then(function(){
					alert('Comando inviato al POS.\nImporto: ' + amount + ' €');
				})
				.catch(function(){
					alert('Errore durante l\'invio al POS.');
				});
		} else {
			var img = new Image();
			img.onload = img.onerror = function(){
				alert('Comando inviato al POS.\nImporto: ' + amount + ' €');
			};
			img.src = url;
		}
	} catch(e){
		alert('Errore durante l\'invio al POS.');
	}

	return false;
}

function invia_pos_carta(){
	var input = document.form_type && document.form_type.pagato_carte_di_credito
		? document.form_type.pagato_carte_di_credito
		: null;
	var btn   = document.getElementById('btn_pos_carta');

	if (!input || !btn) {
		alert('Impossibile inviare al POS: campo importo non trovato.');
		return false;
	}

	var rawVal = (input.value || '').replace(',', '.').trim();
	var amountNum = parseFloat(rawVal);

	if (isNaN(amountNum) || amountNum <= 0) {
		alert('Inserisci un importo carta valido (maggiore di zero).');
		input.focus();
		return false;
	}

	// arrotonda a 2 decimali
	amountNum = Math.round(amountNum * 100) / 100;
	var amount = amountNum.toFixed(2);

	var baseUrl = btn.getAttribute('data-pos-base-url') || '../POS/ingenico.php?from=waiter';

	var sep = (baseUrl.indexOf('?') === -1) ? '?' : '&';
	var url = baseUrl + sep + 'amount=' + encodeURIComponent(amount);

	try{
		if(window.fetch){
			fetch(url, { method: 'GET', cache: 'no-store' })
				.then(function(){
					alert('Comando inviato al POS.\nImporto carta: ' + amount + ' €');
				})
				.catch(function(){
					alert('Errore durante l\'invio al POS.');
				});
		} else {
			var img = new Image();
			img.onload = img.onerror = function(){
				alert('Comando inviato al POS.\nImporto carta: ' + amount + ' €');
			};
			img.src = url;
		}
	} catch(e){
		alert('Errore durante l\'invio al POS.');
	}

	return false;
}

function payment_activation(){
	//alert("Funzione BEGIN");
	//list1=eval("document.form1.payment_data_date_day")

	document.form1.payment_data_date_day.disabled=!document.form1.payment_data_date_day.disabled
	document.form1.payment_data_date_month.disabled=!document.form1.payment_data_date_month.disabled
	document.form1.payment_data_date_year.disabled=!document.form1.payment_data_date_year.disabled
	document.form1.payment_data_type[0].disabled=!document.form1.payment_data_type[0].disabled
	document.form1.payment_data_type[1].disabled=!document.form1.payment_data_type[1].disabled
	document.form1.payment_data_type[2].disabled=!document.form1.payment_data_type[2].disabled
	document.form1.payment_data_account_id.disabled=!document.form1.payment_data_account_id.disabled

	if(document.form1.payment_data_type[0].disabled==true){
		document.form1.payment_data_type[0].checked=false;
		document.form1.payment_data_type[1].checked=false;
		document.form1.payment_data_type[2].checked=false;
	} else {
		document.form1.payment_data_type[0].checked=true;
		document.form1.payment_data_type[1].checked=false;
		document.form1.payment_data_type[2].checked=false;
	}
}

function invia(aformtosend,alist1,alist2){
	formtosend=eval("document."+aformtosend);
	list1=eval("document."+aformtosend+"."+alist1);
	list2=eval("document."+aformtosend+"."+alist2);

	list1length=list1.length;
	list2length=list2.length;

	for(i=0;i<list1length;i++){
		list1[i].selected=true;
	}
	for(i=0;i<list2length;i++){
		list2[i].selected=true;
	}
	//alert(list2.length);
	formtosend.submit();
}

function quantity(form,elem,operation,massimo){
	elemento=eval("document."+form+"."+elem);
	//(int) elemento.value=(int) elemento.value + 1;
	//(int) elemento.text=(int) elemento.text + 1;
//	document.form1.elements[1].value="22";

	//alert(elemento.value + " " + massimo);

	if(operation=="1" && elemento.value < massimo){
			elemento.value++;
	} else if (operation=="-1" && elemento.value > 0) {
			elemento.value--;
	}
			//	elemento.value++;

}

function check_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");

	fromlistlength=fromlist.length;

	what = eval("document."+form+".all_checker.checked");

	for(i=0;i<fromlistlength;i++){
		fromlist[i].checked=what;
	}
}

function check_elem_in_list(form,elem,value) {
	fromlist=eval("document."+form+".elements['"+elem+"']");

	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		if(fromlist[i].value==value) fromlist[i].checked=!fromlist[i].checked;
	}
}

function check_prio(form,elem) {
	fromlist=eval("document."+form+".elements['data[priority]']");
	fromlist[elem].checked=true;
}

function check_ingredqty(ingredid,id) {
	elem="data[ingred_qty]["+ingredid+"]";
	fromlist=eval("document.form1.elements['"+elem+"']");
	fromlist[id].checked=true;
}

function check_elem(form,elem,id) {
	elem=elem+"["+id+"]";
	fromlist=eval("document."+form+".elements['"+elem+"']");
	fromlist.checked=!fromlist.checked;
}

function allarme() {
	alert('poppo');
}

function move(form,from,to){
	fromlist=eval("document."+form+".elements['"+from+"']");
	tolist=eval("document."+form+".elements['"+to+"']");

	fromlistlength=fromlist.length;
	tolistlength=tolist.length;

	for(i=0;i<fromlistlength;i++){

		if(fromlist[i].selected==true){
			tolist.length=tolistlength+1;
			tolistlength=tolist.length;

			last2=tolistlength-1;
			tolist[last2].value=fromlist[i].value;
			tolist[last2].text=fromlist[i].text;
			tolist[last2].selected=fromlist[i].selected;


			for(i=i;i<fromlistlength-1;i++){

				j=i+1;
				fromlist[i].value=fromlist[j].value;
				fromlist[i].text=fromlist[j].text;
				fromlist[i].selected=fromlist[j].selected;
			}
			fromlist.length=fromlistlength-1;

		i=-1;
		fromlistlength=fromlist.length;
		tolistlength=tolist.length;
		}
	}
}

<!--
var tl=new Array(
"My Handy Restaurant is a free software created to help restaurant workers in their job",
"",
"Developed by Fabio 'Kilyerd' De Pascale",
"Created by Fabio 'Kilyerd' De Pascale and Ivan 'Ivanoez' Anochin",
"",
"Kindly supported by:",
"Ristorante Arsenale (Forlì - Italy)- http://www.ristorantearsenale.it",
"Alt-F4 (Italy) - http://www.alt-f4.it",
"Aviano Inn (Aviano - Italy)",
"",
"Developers:",
"Fabio 'Kilyerd' De Pascale - Main developer",
"Rogelio Trivio Gonzalez - Optimization",
"",
"Translated by:",
"Fabio 'Kilyerd' De Pascale - Italian and English",
"Ivan 'Ivanoez' Anochin - Russian",
"Pablo Hugo 'Pabloha' Acevedo - Spanish (Argentinian)",
"Fadjar Tandabawana - Indonesian",
"Dorian Mladin - Romanian",
"",
"Thanks to:",
"Stefania, my girlfriend, for her love",
"Ivan, for his enthusiasm and the howtos",
"the forum writers, for their suggestions and bugs reporting",
"the people at Ristorante Arsenale, in particular Nando and Maurizio, for their support and their tasty meals!",
"Christian, for his surprising desserts",
"EliBus from Alt-F4, for the webserver and for believing in the project",
"Fadjar, for his suggestions and testing",
"Pabloha, for his hard work in doing the first translation"
 );

var speed=50;
var index=0; text_pos=0;
var str_length=tl[0].length;
var contents, row;

function type_text()
{
  contents='';
  row=Math.max(0,index-20);
  //row=0;
  while(row<index)
    contents += tl[row++] + '\r\n';
  document.forms[0].elements[0].value = contents + tl[index].substring(0,text_pos) + "_";
  if(text_pos++==str_length)
  {
    text_pos=0;
    index++;
    if(index!=tl.length)
    {
      str_length=tl[index].length;
      setTimeout("type_text()",500);
    }
  } else
    setTimeout("type_text()",speed);

}

function change_class (obj, classnam) {
	obj.className = classnam;
}

function select_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");

	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=true;
	}
}

function deselect_all(form,elem) {
	fromlist=eval("document."+form+".elements['"+elem+"']");

	fromlistlength=fromlist.length;

	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=false;
	}
}

function select_one(form,elem,idx) {
	fromlist=eval("document."+form+".elements['"+elem+"']");
	fromlistlength=fromlist.length;
	for(i=0;i<fromlistlength;i++){
		fromlist[i].selected=false;
	}
	fromlist[idx].selected=true;
}

function password_form(){
	p0=eval("document.edit_form_user.elements[password_action]");
	p1=eval("document.edit_form_user.elements['data[password1]']");
	p2=eval("document.edit_form_user.elements['data[password2]']");
	p1.disabled=p0.checked;
	p2.disabled=p0.checked;
}

//Pagina blu in attesa di risposta del server
function myFunction() {
  var x = document.getElementById("myDIV");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}
//Mostra e nasconte il layer Prenotazioni
function FunctionLayerPrenotazioni() {
	var x = document.getElementById('divPRENOTAZIONI');
	if (x.style.display === 'block') {
    x.style.display = 'none';
  } else {
    x.style.display = 'block';
  }
}

//Mostra e nasconte il layer Prenotazioni
function FunctionLayerDivPrenotazioniIphone() {
	var x = document.getElementById('div-prenotazioni-iphone');
	if (x.style.display === 'block') {
    x.style.display = 'none';
  } else {
    x.style.display = 'block';
  }
}

// Funzioni per la SIDEBAR apri e chiude la sidebar
function openNav() {
  var x = document.getElementById('mySidebar');
	if (x.style.width === "250px") {
    document.getElementById("mySidebar").style.width = "0";
    document.getElementById("main").style.marginLeft= "0";
  } else {
    document.getElementById("mySidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
  }
  }
/*
function openNav() {
  document.getElementById("mySidebar").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
}
*/

function closeNav() {
  document.getElementById("mySidebar").style.width = "0";
  document.getElementById("main").style.marginLeft= "0";
}

//funzione in javascript per la verifica del codice lotteria
function verifica_codice_lotteria() {

var CodiceLotteria = document.getElementById("codice_lotteria");
CodiceLotteria.value=CodiceLotteria.value.toUpperCase();
/* ATTENZIONE BLOCCA I DISPOSITIVI MOBILI
var verifica_codice = CodiceLotteria.value;
var AlfaNum = /^[a-zA-Z0-9]+$/;
var lunghezza = verifica_codice.length;

if ((verifica_codice!="") && (AlfaNum.test(verifica_codice)==false)) {
  document.form_type.codice_lotteria.focus();
  alert(" ATTENZIONE !!! \n SONO PRESENTI \n CARATTERI NON VALIDI ");
  return false;
  throw 'caratteri non validi';
}

if ( (lunghezza > 0 )	&& (lunghezza < 8 ) )	{
  document.form_type.codice_lotteria.focus();
  alert(" ATTENZIONE !!! \n LUNGHEZZA MINIMA \n 8 CARATTERI ");
    return false;
    throw 'lunghezza minima 8 caratteri';
}
*/
//fine funzione

}

//funzione ricerca in TUTTI i tavoli
function RicercaNeiTavoli() {
  				var input, filter, table, tr, td, i, txtValue;
  				input = document.getElementById("ricerca_tabella_tavoli");
  				filter = input.value.toUpperCase();
  				table = document.getElementById("tabella_tutti_i_tavoli");
  				tr = table.getElementsByTagName("tr");

  				for (i = 0; i < tr.length; i++) {
						var x = table.rows[i].cells.length;
							for (j = 0; j < x; j++) {
		               td = tr[i].getElementsByTagName("td")[j];
    								    if (td) {
      									     txtValue = td.textContent || td.innerText;
      									         if (txtValue.toUpperCase().indexOf(filter) > -1) {
        										         td.style.display = "";
      										       } else {
        										         td.style.display = "none";
      										       }
    								     }
							}
  				}
}

//funzione ricerca nei tavoli SOSPESI
function RicercaNeiTavoliSospesi() {
  				var input, filter, table, tr, td, i, txtValue;
  				input = document.getElementById("ricerca_tabella_tavoli");
  				filter = input.value.toUpperCase();
  				table = document.getElementById("tabella_tutti_i_sospesi");
  				tr = table.getElementsByTagName("tr");

  				for (i = 0; i < tr.length; i++) {
						var x = table.rows[i].cells.length;
							for (j = 0; j < x; j++) {
		               td = tr[i].getElementsByTagName("td")[j];
    								    if (td) {
      									     txtValue = td.textContent || td.innerText;
      									         if (txtValue.toUpperCase().indexOf(filter) > -1) {
        										         td.style.display = "";
      										       } else {
        										         td.style.display = "none";
      										       }
    								     }
							}
  				}
}

function RicercaPrenotatiFunction() {
  // Declare variables
  var input, filter, table, tr, td, i;
  input = document.getElementById("reservationListInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("reservationTable");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    if (!tr[i].classList.contains('header')) {
      td = tr[i].getElementsByTagName("td"),
      match = false;
      for (j = 0; j < td.length; j++) {
        if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
          match = true;
          break;
        }
      }
      if (!match) {
        tr[i].style.display = "none";
      } else {
        tr[i].style.display = "";
      }
    }
  }
}

//mostra oppure nascondi elemento
function MostraNascondi() {
  var x = document.getElementById("ricerca_tabella_tavoli");
  if (x.style.display === "block") {
    x.style.display = "none";
    window.location.href="#top";
    x.blur();
  } else {
    x.style.marginTop = "25px";
    x.style.display = "block";
    window.location.href="#Tuttiitavoli";
    x.focus();
  }
}

//countdown per mostrare il tempo di refresh della Pagina
function countdown(time_for_countdown){
	var maxTicks = time_for_countdown;
    var tickCount = 0;

    var tick = function()
    {
        if (tickCount >= maxTicks)
        {
            // Stops the interval.
            clearInterval(myInterval);
            return;
        }

        /* The particular code you want to excute on each tick */
       document.getElementById("timer").innerHTML = (maxTicks - tickCount);
       var alertcolor = (maxTicks - tickCount);
       var top1 = document.getElementById("top1");
        if (alertcolor == 10){
          document.getElementById("timer").style.color = "red";
          document.getElementById("timer").style.backgroundColor = "yellow";
          if (top1) {
            top1.style.color = "red";
            top1.style.backgroundColor = "yellow";
          }
        }
        if (alertcolor == 5){
          document.getElementById("timer").style.color = "white";
          document.getElementById("timer").style.backgroundColor = "red";
          if (top1) {
            top1.style.color = "white";
            top1.style.backgroundColor = "red";
          }
        }

        tickCount++;
    };

    // Start calling tick function every 1 second.
    var myInterval = setInterval(tick, 1000);
}

const formatPhoneNum = (inputField) => {
    const nums = inputField.value.split(' ').join("");
    const countryCode = '1';
    const digits = nums[0] === countryCode ? 1 : 0;

    // get character position of the cursor:
    let cursorPosition = inputField.selectionStart;

    // add dashes (format 1-xxx-xxx-xxxx or xxx-xxx-xxxx):
    if (nums.length > digits+10) {
        inputField.value = `${digits === 1 ? nums.slice(0, digits) + ' ' : ""}` + nums.slice(digits,digits+3) + ' ' + nums.slice(digits+3,digits+6) + ' ' + nums.slice(digits+6,digits+10);
    }
    else if (nums.length > digits+6) {
        inputField.value = `${digits === 1 ? nums.slice(0, digits) + ' ' : ""}` + nums.slice(digits,digits+3) + ' ' + nums.slice(digits+3,digits+6) + ' ' + nums.slice(digits+6,nums.length);
    }
    else if (nums.length > digits+5) {
        inputField.value = `${digits === 1 ? nums.slice(0, digits) + ' ' : ""}` + nums.slice(digits,digits+3) + ' ' + nums.slice(digits+3,nums.length);
    }
    else if (nums.length > digits+3) {
        inputField.value = `${digits === 1 ? nums.slice(0, digits) + ' ' : ""}` + nums.slice(digits, digits+3) + ' ' + nums.slice(digits+3, nums.length);
    }
    else if (nums.length > 1 && digits === 1) {
        inputField.value = nums.slice(0,digits) + ' ' + nums.slice(digits, nums.length);
    }

    // reseting the input value automatically puts the cursor at the end, which is annoying,
    // so reset the cursor back to where it was before, taking into account any dashes that we added...
    // if the character 1 space behind the cursor is a dash, then move the cursor up one character:
    if (inputField.value.slice(cursorPosition-1, cursorPosition) === ' ') {
        cursorPosition++;
    }
    
    inputField.selectionStart = cursorPosition;
    inputField.selectionEnd = cursorPosition;
}

// INIZIO Barra countdown colorata

document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.progress-container');
    if (!container) return;

    const secondsAttr = container.getAttribute('data-duration');
    const seconds = parseFloat(secondsAttr);
    if (isNaN(seconds) || seconds <= 0) return;

    const bar = document.getElementById('progress-bar');
    let timeLeft = seconds;

    const interval = setInterval(() => {
        timeLeft -= 0.1;
        const percentage = (timeLeft / seconds) * 100;

        if (timeLeft > 0) {
            bar.style.width = percentage + "%";

            if (timeLeft <= 10) {
                bar.style.backgroundColor = "#ea4335"; // Rosso ultimi 10s
            } else if (timeLeft <= 30) {
                bar.style.backgroundColor = "#fbbc04"; // Giallo tra 10s e 30s
            }
        } else {
            bar.style.width = "0%";
            clearInterval(interval);
            document.querySelector('.progress-container').style.display = 'none';
        }
    }, 100);
});

// FINE Barra countdown colorata

// Protezione dal doppio invio dei form
(function () {

    // 1. Submit button (<input type="submit"> o Enter): disabilita i pulsanti del form per 5 secondi
    document.addEventListener("submit", function (e) {
        var form = e.target;
        if (form._mhr_submitting) {
            e.preventDefault();
            return;
        }
        form._mhr_submitting = true;
        var btns = form.querySelectorAll("[type=submit]");
        for (var i = 0; i < btns.length; i++) btns[i].disabled = true;
        setTimeout(function () {
            form._mhr_submitting = false;
            var btns = form.querySelectorAll("[type=submit]");
            for (var i = 0; i < btns.length; i++) btns[i].disabled = false;
        }, 5000);
    }, true);

    // 2. Link/pulsanti con onclick che chiamano .submit() (es. pulsante conferma ordine cameriere)
    //    Il submit event non si attiva per submit programmatici, quindi si intercetta il click
    document.addEventListener("click", function (e) {
        var el = e.target;
        while (el && el.tagName) {
            var oc = el.getAttribute && el.getAttribute("onclick");
            if (oc && oc.indexOf(".submit()") !== -1) {
                if (el._mhr_submitting) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                (function (capturedEl) {
                    capturedEl._mhr_submitting = true;
                    setTimeout(function () { capturedEl._mhr_submitting = false; }, 3000);
                })(el);
                break;
            }
            el = el.parentNode;
        }
    }, true);

})();

