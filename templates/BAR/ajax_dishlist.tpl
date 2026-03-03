<!-- Template BAR ajax_dishlist.tpl -->
<html>
	<head>
	</head>
	<body>
		<center>

		{formstart}

		<table width="100%">
			<tr>
				<td>{priority}</td>
				<td>
					{quantity}
				</td>
			</tr>

			<tr>
				<td colspan="2" width="100%">
					{dishes_list_2cols}
				</td>
			</tr>
					</table>

			{formend}

			<br>
		{generating_time}
		</center>
	</body>
</html>
