<?php

class Pair
{
	private  $a = "";
	private  $b = "";
	
	function __construct($a1, $b1)
	{
		 $this->a = $a1;
		 $this->b = $b1;
	}
	
	public function __toString()
	{
		return (string)$this->a . (string)$this->b;
	}
	
	
	public function get_a()
	{
		return $a;
	}
	
	public function get_b()
	{
		return $b;
		
	}
	
	
	
	
}