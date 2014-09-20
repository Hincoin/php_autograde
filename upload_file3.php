<?php session_start(); require('Config.php'); ?>
<?php

$allowedExts = array("java");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if (in_array($extension, $allowedExts)) {
  if ($_FILES["file"]["error"] > 0) {
    //echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
  } else {
 
    if (file_exists("upload/" . session_id().$_FILES["file"]["name"])) {
      echo $_FILES["file"]["name"].session_id() . " already exists. ";
    } else {
    
      if($_POST['assignments'] === '0')
      {
      		?>
      		<h2><strong>You did not choose an assignment! Please  <a href="MainScreen.php">Go back</a> and pick one.</strong></h2>
      		<?php
      		exit();
      }
      $problems_array = unserialize($_SESSION['logins']['problems']);
      if(array_key_exists($_POST['assignments'],$problems_array))
      {
      	
      		?>
      		<h2><strong>You have already solved this problem!  <a href="MainScreen.php">Go back</a> and solve another!</strong></h2>
      		<?php
      		exit();
      }
     	
      $client = fsockopen($proc_server_ip,$proc_server_port,$errno,$errstr,$proc_server_connection_timeout);
     
      $bytes_written = fwrite($client,$_POST["assignments"].PHP_EOL.$_FILES['file']["name"].PHP_EOL.$_SESSION['username'].PHP_EOL);
      fflush($client);
     
?>
			<br /><br />
			<?php 
      $buf = fgets($client,100);
      $diag = "";
      $decode = substr($buf,0,strlen($buf)-strlen(PHP_EOL));
    //  echo $buf;
  	 if($decode === "2" || $decode === "3")
      {
      	$diag = fgets($client,200);
      }
      if($decode !== "1") // if it wasn't a compiliation error
      {
      	unlink($upload_dir.$_SESSION['username'].'/'. pathinfo($_FILES['file']['name'],PATHINFO_FILENAME).'.class');
     	}
      ?>
      <font size="7"><u><strong>Result: </strong></u></font>
      
      <?php
      
      if($decode === "1")
      {
      	?>
      	<font size="7" color="red"><strong>Compilation Error</strong></font>
      	<h2><strong>Compilation was not succesful, please verify you are writing legal Java.</strong></h2>
      	<?php
      }
      else if($decode === "2")
      {
      	?>
      	<font size="7" color="red"><strong>Runtime Error</strong></font>
      	<h2><strong> Your code encountered a runtime error. Make sure your array indices are in place and nothing is null, recheck your code.</strong></h2>
      	<br />
      	<h2><?php echo $diag; ?></h2>
      	<br /><br />
      	
      	<?php
      }
      else if($decode == "3")
      {
      	?>
      	<font size="7" color="red"><strong>Security Error</strong></font>
      	<br />
      	<h2><strong> Please behave yourself</strong></h2>
      	<br />
      	<h2><?php echo $diag; ?></h2>
      	<h2>Reading,Writing,Modifying, or Executing Files is <font color="red" font=7>PROHIBITED</font></h2><br />
      	
      	<h2>Mrs. Slutsky will be notified and you will receive a failing grade on this assignment.</h2>
   			<?php
   			$problems_array[$_POST['assignments']] = 2;
      	$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);

				$database_store = serialize($problems_array);
				$stmt = $mysqli->prepare('UPDATE `students` SET `problems` = ? WHERE `username` = ?');
				
				$stmt->bind_param('ss',$database_store,$_SESSION['username']);
				
				$stmt->execute();
				$_SESSION['logins']['problems'] = $database_store;
				?>
      	<?php
    	}
    	else if($decode == "4")
    	{
    		
        copy($upload_dir.$_SESSION['username'].'/'.$_FILES["file"]["name"],$upload_dir.$_SESSION['username'].'/ACCEPTED_CODE/'.time().$_FILES['file']['name']);

    		?>
      	<font size="7" color="green"><strong>Accepted</strong></font>
      	<h2><strong>Congratulations! Your code printed the expected output!</strong></h2>
      	<?php
    	
      	$problems_array[$_POST['assignments']] = 1;

      	$mysqli = new mysqli($db_ip,$db_user,$db_pass,$db_name);

				$database_store = serialize($problems_array);
				$stmt = $mysqli->prepare('UPDATE `students` SET `problems_solved` = `problems_solved`+1,`problems` = ? WHERE `username` = ?');
				
				$stmt->bind_param('ss',$database_store,$_SESSION['username']);
				
				$stmt->execute();
				$_SESSION['logins']['problems'] = $database_store;
				
			
				
      	
    	}
    	else if($decode == "5")
    	{
    		?>
      	<font size="7" color="red"><strong>Wrong Answer</strong></font>
      	<h2><strong>Your code compiled and ran without issue, but produced the wrong results. Please verify your code logic.</strong></h2>
      	<?php
    	}
      else
      {
      	?>
      	<font size="7" color="orange"><strong>Time Limit Exceeded</strong></font>
      	<h2><strong>Your code either took too long to finish or entered an infinite loop.</strong> </h2>
      	
      	<?php
      }
			?>
      <?php
            unlink(realpath($upload_dir.$_SESSION['username'].'/'.$_FILES["file"]["name"]));
        ?>
			<br /><br />
		
			<h3>File: <?php echo $_FILES['file']['name']; ?> submitted.</h3>
		
			<br />
			<h3>Problem Attempted: <?php echo $_POST['assignments']; ?></h3>
			<?php
    }
    
  }
} else {
  echo "Invalid filetype. Only upload .java files. Please go back and try again.";
}

?>
