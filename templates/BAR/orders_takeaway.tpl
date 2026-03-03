<!-- Template BAR orders_takeaway.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
  {head}
</head>

<body>
  {scripts}
  {progress-bar}
  <center>
    <table class="tabella_ordini_cassieri" width="1200">
      <!-- Prima riga -->
      <tr>
        <td width=300px align="center" valign="middle"><div class="titolo_tavolo">{people_number}</div></td>
        <td width=700px align="center" valign="middle">{horizontal_navbar}</td>
        <td width=500px align="center" valign="middle"><div class="tabella_ordini_messaggi">{messages}</div></td>
      </tr>

      <!-- Seconda riga -->
      <tr>
        <!-- prima cella -->
        <td valign="top" bgcolor="#FFCC99">
          <center>
            {takeaway}
            {commands}
          </center>
        </td>
        <!-- seconda cella -->
        <td valign="top" bgcolor="#FFCC99">
          <table width="100%">
            <tr>
              <td colspan="2" valign="top" align="center">
                {fast_order_id}</td>
            </tr>
            <tr>
              <td valign="top" width="40%">
                {categories2cols}
              </td>
              <td id="lista_piatti" valign="top" width="60%">
                {toplist2cols}
              </td>
            </tr>
          </table>
          {letters}
        </td>
        <!-- terza cella -->
        <td valign="top" bgcolor="#FFCC99">
          <table width="100%" align="center" cellspacing="0" cellpadding="3">
            <tr>
              <td height="77px" valign="top">{last_order}
              </td>
            <tr>
              <td id="lista_ordini">{orders_list}</td>
            </tr>
          </table>
        </td>

      </tr>
    </table>
    <br>
    {logout}
    <br>
    {generating_time}
  </center>
</body>
</html>
