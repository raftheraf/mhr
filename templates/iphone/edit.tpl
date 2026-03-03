<!-- Template iphone edit.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>

	<body>
		{scripts}
		{progress-bar}

		<div class="top-navbar">
			{navbar}
			<center>
				{people_number}
			</center>
		</div>

		<center>
		{messages}
		{form_start}

		<div class="font20px"><b>
		{dishname}</b><br><br>
		</div>
		{print_info}
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="middle" align="center">{quantity}</td>
				<td valign="middle" align="center">{priority}</td>
			</tr>
			<tr>
				<td colspan="2">{notaordine}<br></td>
			</tr>
			<tr>
				<td colspan="2">{suspend}<br><br></td>
			</tr>
			<tr>
				<td colspan="2">{extra_care}<br></td>
			</tr>
			</table>


		{form_end}
		<hr>
		{logout}
		{generating_time}
		</center>
	</body>
</html>
