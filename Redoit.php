
<?php session_start() ?>
<?php

if(isset($_SESSION['logins']))
{
	unset($_SESSION['logins'][session_id()]);
	if(isset($_SESSION['id']))
		unset($_SESSION['id']);
	session_unset();
	session_destroy();
	if (ini_get("session.use_cookies")) 
	{
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
	}
	header('Location: SampleForm.php');
	
}






?>