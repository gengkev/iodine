<html>
<head>
<title>TJHSST Intranet2: Login</title>
<style type='text/css' media='all'>@import "[<$I2_ROOT>]/www/styles/global.css";</style>
</head>
<body>
 <div id="login_box">
  <table BORDER="0" CELLPADDING="1" CELLSPACING="0" WIDTH="100%" HEIGHT="100%">
   <tr><td valign="center"><center>
    <h3>Intranet2 Login</h3>
    [<if $failed>]
    Your login as [<$uname>] failed.  Maybe your password is incorrect?
    [</if>]
    <form action='[<$I2_SELF>]' method='post'>
     Username: <input name='login_username' type='text' value='[<$uname>]'/><br />
     Password: <input name='login_password' type='password'/><br />
     <input type='submit' value='Submit'/>
    </form>
   </center></td></tr>
  </table>
 </div>
</body>
</html>
