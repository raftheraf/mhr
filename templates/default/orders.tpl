<!-- Template default orders.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
{head}
</head>
<body>
{scripts}
<center>
  <table width="900">
  <tr>
  <td valign="top">{horizontal_navbar}</td>
  <td valign="top"><table width="100%" border="0" bordercolor="black" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">{people_number}</td>
    </tr>
    <tr>
      <td align="center"><b>{messages}</b></td>
    </tr>
  </table></td>
</tr>
<tr>

		<td width="498" valign="top" bgcolor="#FFCC99">


					<table width="100%">
						<tr>
						<td colspan="2" valign="top">
						{fast_order_id}</td>
						</tr>
						<tr>
							<td width="21%" valign="top">
				        <p>{categories2cols}</p></td>
							<td valign="top">
							  <p>{toplist2cols}</p>
  <p>&nbsp;</p></td>
						</tr>
					</table>

    </td>
		<td width="390" valign="top" bgcolor="#FFCC99">

					<table width="100%" align="center" cellspacing="0" cellpadding="3">
					<tr>
						<td height="76px" valign="top">{last_order}
						</td>


					<tr>
						<td>{orders_list}</td>
					</tr>
					</table>

					<center>
					{customerdataform}
					{commands}
					</center>




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
