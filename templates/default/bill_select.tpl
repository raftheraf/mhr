<!-- Template default bill_select.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		<center>
		<table width="500">
			<tr>
				<td>{navbar}</td>
			</tr>
			<tr>
				<td align="center">{people_number}</td>
			</tr>
			<tr>
				<td align="center"><b>{messages}</b></td>
			</tr>
		</table>

		<table width="900" border="1" cellspacing="5" cellpadding="5">

		<tr>
		<td width="375" valign="top">
		<div align="center">
		{orders}
		<br>
		{method}
		{discount}
		</div>
		</td>

		<td width="375" valign="top">
		<div align="center">

		{type}

		</div>
		</td>
		</tr>

		</table>

		{logout}

		{generating_time}
		</center>

	</body>
</html>
