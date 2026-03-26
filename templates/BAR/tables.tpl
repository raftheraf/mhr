<!-- Template BAR tables.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "">
<html>

<head>
	{head}
</head>

<body>
	{scripts}
	

	<div id="mySidebar" class="sidebar">
		<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">
			<img src="../images/button_close.png" alt="Chiudi la SideBar" style="width:50px;height:50px;">
		</a>
		<br>
		<a href="booking.php">Prenotazioni</a>
		<br>
		<a href="../admin/">Amministrazione</a>
		<br>
		<p>Apri/Chiudi</p>
		{barra_apri_chiudi_coperti}
		<p>
			{logout}
		</p>
		<br><br><br>
	</div>

	<center>

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td align="center" valign="top" width="90px">

					<div id="main">
						<a href="javascript:void(0)" class="openbtn" onclick="openNav()">
							<img src="../images/button_sidebar.png" alt="Apri la SideBar" style="width:50px; height:50px;">
						</a>
						<br><br><br>
						<a href="javascript:void(0)" onclick="redir('tables.php')">{countdown}</a>
						<br><br><br>
						<a href="javascript:void(0)">
							<img src="../images/button_prenotazioni.png" style="width: 50px; height: 50px;" alt="Visualizza i prenotati" onclick="FunctionLayerPrenotazioni()">
						</a>

						<div id="divPRENOTAZIONI">
							{prenotazioni}
						</div>

						<div>
							<br /><br />
							<input type="text" id="ricerca_tabella_tavoli"
										onkeyup="	RicercaNeiTavoli();
													RicercaNeiTavoliSospesi()"								
										name="ricerca_tabella_tavoli"
										placeholder="Cerca..."
										onfocusout="
										javascript:document.getElementById('ricerca_tabella_tavoli').value=null;
										javascript:document.getElementById('ricerca_tabella_tavoli_sospesi').value=null" >
						</div>

						<div>
							<br /><br />
							<i>{giorno}</i><br/>
							<b style="font-size:24px;">{sono_le_ore}</b></br/></br/>
						</div>

					</div>
				</td>

				<td align="center" valign="top">
					<div style="width:50%">	{messages} 	{navbar}	</div>
					{tables}

					<br>
					{riepilogo}
					{generating_time}
				</td>

			</tr>
		</table>
		<br>
	</center>

</body>

</html>
