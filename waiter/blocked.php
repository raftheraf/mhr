<?php
// Pagina mostrata alle schede duplicate della sezione camerieri
// Non include generic.js per evitare ulteriori controlli/redirect
define('ROOTDIR','..');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>Accesso bloccato</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1, maximum-scale=1, user-scalable=no" />
  <style type="text/css">
    body {
      font-family: Arial, Helvetica, sans-serif;
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 60px auto;
      background: #ffffff;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      padding: 24px 28px;
      text-align: center;
    }
    h1 {
      margin-top: 0;
      color: #c0392b;
      font-size: 22px;
    }
    p {
      font-size: 15px;
      line-height: 1.5;
      color: #333333;
    }
    .btn {
      display: inline-block;
      margin-top: 18px;
      padding: 10px 18px;
      background-color: #3498db;
      color: #ffffff;
      text-decoration: none;
      border-radius: 4px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Accesso bloccato</h1>
    <p>
      La sezione camerieri è già aperta in un&apos;altra finestra o scheda di questo browser.<br />
      Per evitare errori e conflitti sui dati, questa finestra è stata bloccata.
    </p>
    <p>
      Usa la prima finestra aperta per continuare a lavorare.<br />
      Puoi chiudere tranquillamente questa scheda.
    </p>
    <a href="javascript:window.close();" class="btn">Chiudi questa scheda</a>
  </div>
</body>
</html>

