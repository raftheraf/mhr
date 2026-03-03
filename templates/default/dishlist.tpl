<!-- Template default dishlist.tpl -->
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
			<tr>
				<td>{last_order}</td>
			</tr>
		</table>

		<table width="500">
			<tr>
				<td valign=top width="150">{categories}</td>
				<td align="center" valign=top width="350">
					{formstart}
					<table width="100%">
						<tr>
							<td valign="middle">
								{priority}
							</td>
						</tr>
						<tr>
							<td align="center" valign="middle">
								{quantity}
								{back_to_cat}
						</tr>
						<tr>
							<td valign="top">
								{dishes_list}
							</td>
						</tr>
					</table>
					{formend}


				</td>
			</tr>
		</table>

		<table width="500">
			<tr>
				<td align="center">
				{letters}
				</td>
			</tr>
		</table>

		{logout}
		{generating_time}
		</center>
	</body>
</html>
