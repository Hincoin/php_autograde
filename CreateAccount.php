<html>
<h1><center>Create Account</center></h1>
<br /><br />
<head>
    <title>Create Account</title>
    <style type="text/css">
    .container {
        width: 500px;
        clear: both;
    }
    .container input {
        width: 100%;
        clear: both;
    }

    </style>
</head>
<body>
<div class="container">
<form action="insert_sql.php" method="POST">
 <label>First Name</label>
 <input type="text" name="first"><br /> <br />
 <label>Last Name</label>
 <input type="text" name="last"><br />  <br />
 <label>Student ID</label>
 <input type="text" name="id"><br /> <br />
 <label>Username</label>
 <input type="text" name="username"><br /> <br />
 <label>Password</label>
 <input type="password" name="password"><br /> <br />
 <label>Period</label>
 <input type="number" name="period"><br />
 <br /><br />
 <input type="submit" value="Create Account">
</form>
</div>
</body>
</html>
	
