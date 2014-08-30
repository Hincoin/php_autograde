<?php
session_start();
require('Config.php');
if(isset($_SESSION['pd']))
{
	$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);
	$stmt  = $mysqli->prepare("UPDATE `students` SET `problems`='' WHERE `period`=?");

	$stmt->bind_param('s',$_SESSION['pd']);
	$stmt->execute();
	$stmt = $mysqli->prepare("SELECT * FROM `students` WHERE `period`=?");
	$stmt->bind_param('s',$_SESSION['pd']);
	$stmt->execute();
	$results = $stmt->get_result()->fetch_all();
	foreach ($results as $row)
	{
		echo($row[0]);
		$files = glob($upload_dir.$row[0].'\\ACCEPTED_CODE\\*');
		//echo($upload_dir.$row[0].'\\ACCEPTED_CODE');
		//echo('\n');
		foreach($files as $file)
		{
			echo($file);
			//echo('\n');
			if(is_file($file))
			{
				unlink($file);
			}	
		}
	}



}
?>