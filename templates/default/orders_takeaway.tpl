<!-- Template default orders_takeaway.tpl -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
</head>


<body>


{head} {scripts}
<center> {people_number} {messages}
<table>


  <tbody>


    <tr>


      <td rowspan="4" valign="top"> {takeaway} </td>


      <td>

      <table>


        <tbody>


          <tr>


            <td>{vertical_navbar}</td>


            <td valign="top">{categories}</td>


          </tr>



        </tbody>

      </table>


      </td>


      <td rowspan="4" align="left" valign="top">

      <table>


        <tbody>


          <tr>


            <td> {last_order} </td>


          </tr>


          <tr>


            <td> {orders_list} &nbsp; </td>


          </tr>



        </tbody>

      </table>


      </td>


    </tr>


    <tr>


      <td><br>
      <br>
{formstart}
					{priority}<br>

					{quantity}
					{back_to_cat}<br>
{dishes_list}

					<br>
{formend}<br>
      <br>
      <br>
{fast_order_id} </td>


    </tr>


    <tr>


      <td valign="top">{letters}</td>


    </tr>


    <tr>


      <td> {toplist} </td>


    </tr>


    <tr colspan="2">


      <td> {commands} </td>


    </tr>



  </tbody>
</table>


{logout} {generating_time} </center>


</body>
</html>
