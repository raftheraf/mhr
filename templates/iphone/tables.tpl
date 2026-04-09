<!-- Template iphone.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	{head}
	<!-- Collegamenti per il funzionemanto della bottom bar-->
	<link rel="stylesheet" href="../css/top-navbar.css" type="text/css">

	<!-- Collegamenti per il funzionemanto della left-sidebar-->
	<link rel="stylesheet" href="../css/left_sidebar.css" type="text/css">
	<script type="text/javascript" language="JavaScript" src="../javascript/left_sidebar.js"></script>

	<!-- Collegamenti per il funzionemanto della div-prenotazioni-iphone -->
	<link rel="stylesheet" href="../css/div-prenotazioni-iphone.css" type="text/css">

</head>

<body>
	{scripts}
	<a id="top"></a>
	<div class="top-navbar">
		<a id="top1" href="tables.php" class="active">{countdown} Aggiorna</a>
		<a id="top2" href="#inizio_prenotati" onclick="FunctionLayerDivPrenotazioniIphone()">Prenotazioni</a>
		<a id="top3" href="javascript:void(0)" class="openbtn" onclick="openNav()">MENU</a>
		<a id="top4" class="openbtn" onclick="MostraNascondi();"><img src="../images/find_top_bar.png" /></a>

	</div>
	<div id="left_sidebar" class="left_sidebar">
		<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">
			<img src="../images/button_close.png" style="width: 50px; height: 50px;" alt="Chiudi la SideBar">
		</a>
		<br>
		<a href="tables.php">Tavoli</a>
		<br>
		<a href="../admin/">Amministrazione</a>
		<br>
<p>
			{logout}
		</p>
	</div>

	<div class="main">
		<center>
			{messages}
			{navbar}

			{tables}
			<br>
				{riepilogo}
			{generating_time}<br>
		</center>
	</div>
	<div id="div-prenotazioni-iphone">
		<div style="height: 38px"><a name="inizio_prenotati"> </a></div>
		<center>
			{prenotazioni}
		</center>
	</div>

</body>

</html>
