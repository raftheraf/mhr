<!-- Template BAR edit.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		{progress-bar}
		<center>
		<table width="500px"><tr><td align="center">
		{navbar}
		{people_number}
		{messages}
		<br><br>
		{form_start}


		<FIELDSET>
		<LEGEND><b> {dishname} </b></LEGEND>
		<br>
		{substitute}
		<br>
		{print_info}
		<br>
		<table width="100%" cellpadding="10" cellspacing="10">
			<tr>
				<td align="center" valign="top">{quantity}</td>
				<td align="center" valign="top">{priority}</td>
			</tr>
			<tr>
				<td colspan="2"><hr></td>
			</tr>

			<tr>
				<td colspan="2">{notaordine}</td>
			</tr>

			<tr>
				<td colspan="2">{suspend}</td>
			</tr>

			<tr>
				<td colspan="2">{extra_care}</td>
			</tr>
			</table>

			</FIELDSET>

		{form_end}
		{logout}
		{generating_time}

		</td></tr></table>
		</center>
	</body>
</html>
