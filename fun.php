<?php

//Pixel Gaming TF2 Bot

//Russian Roulette
class rr
{
	public $clip;
	public $trigger;
	public $lastuser;
	public $lastusercount;
	
	function init()
	{
		//Create the clip
		$this->clip = array();
		
		//Load bullet to random 
		$deadshot = rand(1, 8);
		$this->clip[$deadshot] = "dead";
		
		//Select start point
		$this->trigger = rand(1, 8);
		
		//Reset last user
		$this->lastuser = "";
		$this->lastusercount = 0;
	}
	
	function fire()
	{
		//If the trigger was on the deadshot
		if($this->clip[$this->trigger] == "dead")
		{
			//Return true to kill the player
			$returnvalue = true;
			//Reset the game
			$this->init();
		} else
		{
			//Turn the clip
			$returnvalue = false;
			$this->trigger++;
			
			//Reset to barrel position 1 if it needs so
			if($this->trigger > 8)
			{
				$this->trigger = 1;
			}
		}
		
		//Return output
		return $returnvalue;
	}
	
	function spin()
	{
		//Spin the clip
		$this->trigger = rand(1, 8);
	}
}


$rr = new rr;
$rr->init();
?>
