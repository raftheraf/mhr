<!-- Template BAR bill_select.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		{progress-bar}
		<center>
		<table width="500px">
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

		<table width="900px" border="1" cellspacing="5" cellpadding="5">

		<tr>
		<td width="375px" valign="top">
		<div align="center">
		{orders}
		<br>
		{discount}
		<br>
		{method}
		</div>
		</td>

		<td width="375px" valign="top">
		<div align="center">

		{type}

		</div>
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
