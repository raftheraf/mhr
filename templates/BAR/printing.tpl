<!-- Template BAR printing.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		{progress-bar}
		<center>

		<table width="700">
			<tr>
				<td>{navbar}</td>
			</tr>
			<tr>
				<td align="center">{people_number}</td>
			</tr>
			<tr>
				<td align="center"><b>{messages}</b></td>
			</tr>
			<tr>
				<td>{content}</td>
			</tr>
		</table>

		{commands}

		<br>
		{logout}
		<br>
		{generating_time}
		</center>
	</body>
</html>
