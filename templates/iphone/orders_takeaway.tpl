<!-- Template iphone orders.tpl -->
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
		<center>
			<br><br><br><br><hr>
			<b>Ricerca articoli per nome</b>
			{fast_order_id}<br><hr>
			<b>Ricerca articoli per lettera</b>
			{letters}<br><br><br><hr>
		</center>
	</div>

	<div class="top-navbar">
		{horizontal_navbar}
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
			{orders_list}
			<table width="100%">
				<tr>
					<td valign=top width="50%">
						{categories}
					</td>

					<td valign=top width="50%">
						{toplist}
					</td>
				</tr>
			</table>
			<br>
		{takeaway}
		{commands}
		{logout}
		{generating_time}
		<br><br>
		</center>

		</body>
</html>
