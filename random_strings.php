<?php

$constant = 'abc';
for($i = 1; $i <= 8; $i++)
{
	$string_val = 'Period'.$i;
	$crypt_val = crypt($string_val,$constant);
	//echo strlen($crypt_val);
	echo ($string_val . ":  " . substr($crypt_val,-6));
	?><br /><br /><?php
}
?><br /><br /><?php
?>