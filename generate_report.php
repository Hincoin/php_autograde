

<?php
session_start();

require('Config.php');
require("moss.php"); 
global $files_to_users;
global $plagairise;
$files_to_users = array();
$plagairise = array();
function moveToAccFolder($user,$first,$last,$w_root,$w_up)
{
	
	//echo($upload_dir);
	$accepted_files = glob($w_up.$user."/ACCEPTED_CODE/*");
	foreach($accepted_files as $acc)
	{
		//echo($acc);
		$GLOBALS['files_to_users'][$user.pathinfo($acc,PATHINFO_FILENAME).".java"] = $first . " " . $last;
		
		copy($acc,$w_root."ACC/".$user.pathinfo($acc,PATHINFO_FILENAME).".java");

	}
}
function run_moss($f1,$f2,$plagairise,$files_to_users,$prob_name)
{
	$moss = new MOSS("488240259");
   echo "COMPARING: " . $f1 . "    to     " . $f2;
	$moss->setLanguage('java');
	//$moss->addByWildcard($w_root."ACC/*.java");
	$moss->addFile($f1);
	$moss->addFile($f2);
	$val = $moss->send();
	$html = @get_headers(substr($val,0,strlen($val)-1) . '/match0.html');
	
	if(strpos($html[0],'200') !== false)
	{
		
		$owner1 = $files_to_users[pathinfo($f1,PATHINFO_FILENAME).".java"];
		$owner2 = $files_to_users[pathinfo($f2,PATHINFO_FILENAME).".java"];
		array_push($GLOBALS['plagairise'][$owner1.$prob_name],$owner2);	
	}
	

	// check if html contains $val."/match0.html"
	// if it does, plagairism is detected... get the user in which
	// plagairism was detected and get the amount of lines copied.

	// a few lines copied will not be penalized heavily. as some code
	// has a basis that every student does the same, such as opening a file,
	// File I/O and such.



	//$moss->setCommentString("This is a test");

}
?>

<html>
<head>
<a href="finalize_report.php" onclick="return confirm('Are you sure you want to finalize this report?')">Finalize Report</a>
<style>
table,th,td
{
border:1px solid black
border-collapse:collapse;
}
th,td
{
padding:5px;
}
</style>
</head>
<body>
<?php
/*if(!(isset($_SESSION['loginauth'])))
{
	die;
}
*/
if(!(isset($_POST['pd'])))
{
	header('Location: get_report.php');
	
}
?>
<table style="width:300px">
<tr>
  <th>First Name</th>
  <th>Last Name</th>		
  <th>ID</th>
  
  <?php
  $problems = array();
  
  foreach(glob($old_assignments_dir . '*.*') as $prob)
  {
  	if(!($prob === "OLD_ASSIGNMENTS_FOLDER"))
  	{
	  	?>
	  	<th><?php echo pathinfo($prob,PATHINFO_FILENAME); ?></th>
	 	 	<?php
	 	 	array_push($problems,pathinfo($prob,PATHINFO_FILENAME));
	 	 	
 	  }
  }
  
 
  ?>
  
</tr>

<?php
$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);
$stmt  = $mysqli->prepare('SELECT * FROM `students` WHERE `period`=?');
$stmt->bind_param('s',$_POST['pd']);
$stmt->execute();
$results = $stmt->get_result()->fetch_all();
echo(count($results));
$_SESSION['pd'] = $_POST['pd'];
foreach($results as $row)
{

	$plagairise[$row[2] . " " . $row[3]] = array();
	moveToAccFolder($row[0],$row[2],$row[3],$web_root,$upload_dir);

}

$prob_arr_1 = array();
$prob_arr_2 = array();
$prob_str_1 = "";
$prob_str_2 = "";
$usr_1_str = "";
foreach($results as $row)
{
	
	foreach($problems as $prob)
	{
		$prob_str_1 = $row[5];
		if(strlen($prob_str_1) !== 0 )
		{
			$prob_arr_1 = unserialize($prob_str_1);
		}
		
		else
			continue;
			
		if(array_key_exists($prob,$prob_arr_1))
		{
			
			
			?><br /><br /><?php
			$u1_table = unserialize($row[8]);
			$usr_1_str = $web_root."ACC/".$row[0].$u1_table[$prob];
			foreach($results as $row2)
			{
				if($row[0] === $row2[0]) continue; // don't do plagairism on same person
				$prob_str_2 = $row2[5];
				if(strlen($prob_str_2) !== 0)
				{
					$prob_arr_2 = unserialize($prob_str_2);
				}
				
				else 
					continue;
					
				if(array_key_exists($prob,$prob_arr_2))
				{
						$u2_table = unserialize($row2[8]);
						echo "PROB = " . $prob;
						try
						{
						run_moss($usr_1_str,$web_root."ACC/".$row2[0].$u2_table[$prob],$plagairise,$files_to_users,$prob);
						}catch(Exception $e)
						{
							echo $e->getMessage();
							exit();
						}
				}
			}
		}
	}
	
}

echo "OUT";
foreach($results as $row)
{
	echo "IN";
	$problems_array = unserialize($row[5]);
	if(strlen($row[5]) == 0)
		$problems_array = array();
	?>
	<tr>
		
	<td> <?php echo $row[2]; ?>    </td>
	<td> <?php echo $row[3]; ?>    </td>
	<td> <?php echo $row[6]; ?>    </td>
	
	<?php
	foreach($problems as $prob)
	{
		
		if(array_key_exists($prob,$problems_array))
		{
			
			
			if($problems_array[$prob] === 1)
			{
				?>
				
				<td>ACCEPTED</td>
				<?php
			}

			else
			{
				?>
				<td>SECURITTY VIOLATION</td>
				<?php
			}
			
	
			if(count($plagairise[$row[0].$prob]) !== 0)
			{
	
				 					?>
                		<td><font color="red">YES</font> : <?php foreach($plagairise[$row[2] . " " . $row[3]] as $plag){echo ($plag . " ");}?></td>


                	<?php

			}
			

			else
      {
          		?><td>AUTHENTIC</td>
        			 <?php
     	}


	


                
		
	
	  }
		else
		{
			?>
			<td>NULL</td>
			<?php
		}
	
	}
	
	?>
	</tr>
	<?php
	
	
}

?>
</tr>
</body>
</html>
