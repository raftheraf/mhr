<!-- Template BAR dishlist.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
	{head}
	</head>
	<body>
		{scripts}
		{progress-bar}
		<center>
		  <table width="900">
			<tr>
			  <td colspan="2" rowspan="2" align="left" valign=top>{vertical_navbar}</td>
			  <td width="344" valign=top>{people_number}</td>
		    </tr>
			<tr>
			  <td valign=top align="center"><b>{messages}</b></td>
		    </tr>
			<tr>
				<td width="251" valign=top>{categories2cols}</td>
				<td colspan="2" align="center" valign=top><table width="100%">
					<tr>
						  <td colspan="2" valign="middle">{last_order}</td>
				  </tr>
				<tr>
						  <td colspan="2" valign="middle">{formstart} </td>
					  </tr>
						<tr>
							<td width="66%" align="center" valign="middle">
							{priority}</td>
							<td width="34%" valign="middle">{quantity}
							{back_to_cat}</td>
						</tr>
						<tr>
							<td colspan="2" valign="top">
							{dishes_list_2cols}
						    {formend}</td>
						</tr>
					</table></td>
			</tr>
				<tr>
			  <td colspan="3" align="center" valign=top>{letters}</td>
		    </tr>
		  </table>
			<br><br>
		{logout}
		{generating_time}
		</center>
	</body>
</html>
