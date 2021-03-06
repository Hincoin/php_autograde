<?php 
session_start();
require('PairClass.php');
require('Config.php');
if(!isset($_SESSION['logins']))
{
	$_SESSION['logins'] = array();
}


?>
<!DOCTYPE html>
<html>
<body>

<?php


// check to make sure passwords are encrypted with blowfish
$blowfish_constant = $hash_constant;
$errno= 0;
$errstr='';


if($_POST["username"] && $_POST["password"])
{

	$username = $_POST["username"];
	$p_hash = crypt($_POST['password'],$blowfish_constant);
	
	$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);

	
	$stmt = $mysqli->prepare('SELECT * FROM ' . $db_table . ' WHERE BINARY Username = ? AND Password = ?');
	$stmt->bind_param('ss',$username,$p_hash);
	
	$stmt->execute();
	
	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		echo "Incorrect Username or Password.\nPlease try again\n";
		
	}
	else
	{
			// move to web UI
		//	$p = new Pair($username,$p_hash);
			$r1 = $result->fetch_assoc();
			if(strlen($r1['problems'])==0)
			{
				$dummy = array();
				$r1['problems'] = serialize($dummy);
			}
			
			
		  session_regenerate_id(true);

	  	$_SESSION['logins'] = $r1;
		//	array_push($_SESSION['logins'],$p);
			//setcookie("VERIFY_COOKIE",$username,0,'/'); 
		//	$_POST['UserAuth'] = $username;
			$_SESSION['username'] = $_POST["username"];
			
			header('Location: MainScreen.php');
	
	}
	
		
}
else
{  
	echo "Username or Password cannot be empty!".PHP_EOL;
	echo "Please go back and try again".PHP_EOL;


}






?>

</body>
</html>
