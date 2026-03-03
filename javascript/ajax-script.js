function loadDish ( pageUrl ) {
    $.ajax( {
      type: "POST",
      url: pageUrl,
      data : '',
      success: function ( html ) {
        $( "#lista_piatti" ).html(html);
      }
    } );
}




function dishOrder ($dishid, form_name) {
var frm=eval("document."+form_name);
//frm.dishid.value=$dishid;
var dishid = $dishid;

var priority = $("#dishpriority").val(); //OK
//var priority = document.getElementById("dishpriority").value;

var from_category = $("input[name='from_category']").val();

var command = document.getElementById("command").value;// OHOHHO
//var command = $("#command").val();                                   //BIG PROBLEM

var quantity = $("#dishquantity").val(); //OK

var quantitamoltiplicata = "";
if (document.getElementById("quantita_moltiplicata")) {
var quantitamoltiplicata = document.getElementById("quantita_moltiplicata").value;
}

// orders.php?dishid=1049&data[priority]=4&from_category=2&command=create&data[quantity]=22

  $.ajax( {
  					type: "POST",
  					url: "orders.php",
  					data : "dishid=" + dishid + "&data[priority]=" + priority + "&from_category=" + from_category + "&command=" + command
                             + "&data[quantity]=" + quantity + "&data[quantita_moltiplicata]=" + quantitamoltiplicata,
  					success: function ( html ) {
  						$( "#lista_ordini" ).html(html);
  					}
  				} );
}


function setDishSelectedQuantity (quantity) {
  if(quantity == 0 )
    quantity = 1
  else
    quantity = quantity + 1;
  $("#dishquantity").val(quantity);
}

function setDishSelectedPriority (priority) {
  if(priority == 0 )
    priority = 1
  else
    priority = priority + 1;
  $("#dishpriority").val(priority);
}






//modifiche Ajax
//arrivati fino a qui
//===========================
  				function quickDishOrder (  ) {
  					var quickDishID = $("#quickdishid").val();
  					var priority = $("input[name='data[priority]']:checked").val();
  					var from_category = $("input[name='from_category']").val();
  					var command = $("input[name='command']").val();
  					var quantity = $("#dishquantity").val();

  					$.ajax( {
  						type: "POST",
  						url: "orders.php",
  						data : "dishid=" + quickDishID + "&data[priority]=" + priority + "&from_category=" + from_category + "&command=" + command + "&data[quantity]=" + quantity,
  						success: function ( html ) {
  							$( "#lista_ordini" ).html(html);
  						}
  					} );
  					$("#quickdishid").val("");
  					return false;
  				}

  				function modifyDishOrder ( formName ) {
  					 var formUrl = $("[name=" + formName + "]").serialize();
  					 $.ajax( {
  						type: "POST",
  						url: "orders.php",
  						data : formUrl,
  						success: function ( html ) {
  							$( "#lista_ordini" ).html(html);
  							$.modal.close();
  						}
  					} );
  					return false;
  				}

  				function separatedBills ( formName ) {
  					 var formUrl = $("[name=" + formName + "]").serialize();
  					 $.ajax( {
  						type: "POST",
  						url: "orders.php",
  						data : formUrl,
  						success: function ( html ) {
  							$.modal.close();
  							$(html).modal({
  								close: false,
  								position: ["15%",],
  								onClose: function (dialog) {$.modal.close();}
  							})
  						}
  					});
  					return false;
  				}

  				function applyDiscount ( formName ) {
  					var formUrl = $("[name=" + formName + "]").serialize();
  					var pageurl = "orders.php?" +  formUrl;
  					$.modal.close();
  					$.get(pageurl,
  						function(returned_data){
  							$(returned_data).modal({
  								close: false,
  								position: ["15%",],
  								onClose: function (dialog) {$.modal.close();}
  						})
  					});
  					return false;
  				}

  				function modifyDishQuantity(dataPost) {
  					$.ajax( {
  						type: "POST",
  						url: "orders.php",
  						data : dataPost,
  						success: function ( html ) {
  							$( "#lista_ordini" ).html(html);
  						}
  					} );
  				}

  				function generalDishModifier(urldata) {
  					$.ajax( {
  						type: "POST",
  						url: urldata,
  						success: function ( html ) {
  							$("#lista_ordini").html(html);
  							$.modal.close();
  						}
  					} );
  				}

  				function loadModal ( pageurl ) {
  					$.get(pageurl,
  						function(returned_data){
  							$(returned_data).modal({
  								close: false,
  								position: ["15%",],
  								onClose: function (dialog) {$.modal.close();}
  						})
  					});
  				}

  				function lookupCustomer(inputCustomer) {
  					if(inputCustomer.length == 0) {
  						$("#suggestions").hide();
  					} else {
  						$.post("orders.php?command=customer_search", {queryString: ""+inputCustomer+""}, function(data){
  							if(data.length >0) {
  								$("#suggestions").show();
  								$("#autoSuggestionsList").html(data);
  							}
  						});
  					}
  				}

  				function fillCustomer(thisValue, customer) {
  					//alert(thisValue);
  					//alert(customer);
  					$.ajax( {
  						type: "POST",
  						url: thisValue,
  						success: function ( html ) {
  							$("#inputCustomer").val(customer);
  							setTimeout("$('#suggestions').hide();", 200);
  						}
  					} );
  				}
