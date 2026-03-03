<?php
if (opcache_reset()){
  echo ("Chache resettata");
  opcache_reset();
}
  else {
    echo ("chache non attiva");
  }


?>
