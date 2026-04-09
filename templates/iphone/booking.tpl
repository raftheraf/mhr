<!-- Template iphone booking.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
	{head}

	<!-- Collegamenti per il funzionemanto della bottom bar-->
	<link rel="stylesheet" href="../css/top-navbar.css" type="text/css">

	<!-- Collegamenti per il funzionemanto della left-sidebar-->
	<link rel="stylesheet" href="../css/left-sidebar.css" type="text/css">
	<script type="text/javascript" language="JavaScript" src="../javascript/left-sidebar.js"></script>

</head>

<body>
	{scripts}
	{progress-bar}

	<div class="top-navbar">
		<a href="tables.php">HOME</a>
		<a href="booking.php" class="active">PRENOTAZIONI</a>
		<a href="javascript:void(0)" class="openbtn" onclick="openNav()">MENU</a>
	</div>

	<div id="left-sidebar" class="left-sidebar">
		<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">
		<img  src="../images/button_close.png" style="width: 50px; height: 50px;" alt="Chiudi la SideBar">
		</a>
		<br>
		<a href="tables.php">Tavoli</a>
		<br>
		<a href="booking.php">Prenotazioni</a>
		<br>
		<a href="../admin/">Amministrazione</a>
		<br>
<p>
			{logout}
		</p>
	</div>

<div class="main">
		<center>
			<p>{messages}
				{navbar} </p>
			<div align="center">
				<table border="0" cellspacing="10" cellpadding="0">
					<tr>
						<td valign="top">
							<p>{prenotazioni} </p>
						</td>
					</tr>
				</table>
			</div>
				{generating_time}
		</center>
	</div>
	<br><br>
</body>

</html>
