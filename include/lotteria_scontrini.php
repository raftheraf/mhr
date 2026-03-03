<?php

function verifica_codice_lotteria($codice_lotteria){
  $numero_caratteri=strlen($codice_lotteria);

    if( $numero_caratteri !=8 ) {
    $risultato="sbagliato";
    }elseif (!preg_match("/^[A-Za-z0-9' ]+$/", $codice_lotteria)) {
    $risultato="sbagliato";
    }
    else {
    $risultato="esatto";
    }
  return $risultato;
}

?>
