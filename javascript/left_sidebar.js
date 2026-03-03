// Funzioni per la left-sidebar apri e chiude la sidebar
function openNav() {
  var x = document.getElementById('left_sidebar');
	if (x.style.width === "250px") {
    document.getElementById("left_sidebar").style.width = "0px";

  } else {
    document.getElementById("left_sidebar").style.width = "250px";

  }
  }
/*
function openNav() {
  document.getElementById("left-sidebar").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
}
*/

function closeNav() {
  document.getElementById("left_sidebar").style.width = "0px";
  
}
