<!-- Template default modslist.tpl -->
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

		<br>

		{form_start}
		{mod_quantity}
		{mod_letters}<br>
		{add_list}
		{delete_list}
		{form_end}

		<br>
		{logout}<br>
		{generating_time}
		</center>
	</body>
</html>
