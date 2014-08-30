<?php
require('Config.php');
if(strlen($_POST['first']) == 0)
{
	?>
	<h2><center><strong>No first name entered. Please go back and enter your first name</strong></center></h2>
	<?php
	exit();
}
if(strlen($_POST['last']) == 0)
{
	?>
	<h2><center><strong>No last name entered. Please go back and enter your last name</strong></center></h2>
	<?php
	exit();
}
if(strlen($_POST['username']) == 0)
{
	?>
	<h2><center><strong>No username entered. Please go back and enter a username</strong></center></h2>
	<?php
	exit();
}
if(strlen($_POST['password']) == 0)
{
	?>
	<h2><center><strong>No password entered. Please go back and enter a password.</strong></center></h2>
	<?php
	exit();
}
if(!isset($_POST['period']))
{
	?>
	<h2><center><strong>No period number entered. Please go back and enter the period you will have AP Computer Science.</strong></center></h2>
	<?php
	exit();
	
}
if($_POST['period'] < 1 || $_POST['period'] > 8)
{
	?>
	<h2><center><strong>Invalid period number entered ( must be between 1 and 8). Please go back and enter the period you will have AP Computer Science.</strong></center></h2>
	<?php
	exit();
}
$password = crypt($_POST['password'],$hash_constant);
$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);
$stmt = $mysqli->prepare("SELECT * FROM `students` WHERE `Username`= ?");
	$stmt->bind_param('s',$_POST['username']);
	$stmt->execute();
	if($stmt->get_result()->num_rows > 0)
	{
		?>
		<h2>Username already taken. Please go back and choose another.</h2>
		<?php
		exit();
		
	}
if(strpos($_POST['username']) !== 0 
$stmt = $mysqli->prepare("INSERT INTO `students` (`Username`, `Password`, `First_Name`, `Last_Name`, `problems_solved`, `problems`, `student_id`, `period`) VALUES (?,?,?,?,0,'',?,?)");
$stmt->bind_param('ssssss',$_POST['username'],$password,$_POST['first'],$_POST['last'],$_POST['id'],$_POST['period']);
$val = $stmt->execute();	
if($val == 1)
{
	
	
	?>
	
	<h2>Thank you for subscribing. You may begin submitting homework assignments!</h2>
	<?php
}
else
{
	echo $mysqli->error;
	?><br /><br /><?php
	echo "Something happened...please report this incident to an administrator.";
	
}
$mysqli->close();

?>