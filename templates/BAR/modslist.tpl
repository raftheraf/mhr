<!-- Template BAR modslist.tpl -->
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
		</table>

		<br>

		{form_start}
		{mod_quantity}
		{mod_letters}<br><br>
		{add_list}
		{delete_list}
		{form_end}

		<br><br>
		{logout}<br>
		{generating_time}
		</center>
	</body>
</html>
