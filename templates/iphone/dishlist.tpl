<!-- Template iphone dishlist.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		{head}
		<link rel="stylesheet" href="../css/div-prenotazioni-iphone.css" type="text/css">
	</head>

	<body>
		{scripts}
		{progress-bar}

		<div id="div-prenotazioni-iphone">
			<a name="inizio_prenotati"></a>
			<center><br><br><br><br><br><hr>
				<h2>Ricerca articoli per lettera</h2>
				{letters}<br><br><br><hr><br>
			</center>
		</div>


		<div class="top-navbar">
			{navbar}
			<center>
				{people_number}
			</center>
		</div>

		<div class="last_order">
			<center>
			<b>{messages}</b>
			{last_order}
			</center>
		</div>

		<center>

		<table width="100%">
			<tr>
				<td valign=top width="80">{categories}</td>
				<td valign=top width="100%">
					{formstart}
					{priority}
					<table>
						<tr>
							<td valign="middle">
								{quantity}
							</td>
							<td valign="middle">
								{back_to_cat}
							</td>
						</tr>
					</table>
					{dishes_list}
					{formend}
				</td>
			</tr>
		</table>
		{letters}
		<br>
		{logout}
		<br>
		{generating_time}
		</center>

	</body>
</html>
