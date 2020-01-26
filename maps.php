<?php

class maps
{
	var $maps;
	var $shortcodes;
	
	//init
	public function init()
	{
		$this->maps = array();
		$this->shortcodes = array();
	}

	//Adds a new map to the list
	public function addmap($mapname)
	{
		//Check if map already exists
		$counter = 0;
		$count = count($this->maps);
		$iftrue = false;
		while($counter < $count)
		{
			if($mapname == $this->maps[$counter])
			{
				$iftrue = true;
				$counter = $count + 1;
				$returnval = false;
			}
			
			$counter++;
		}
		
		//Add map
		if($iftrue == false)
		{
			$counttt = count($this->maps);
			$this->maps[$counttt] = $mapname;
			$this->shortcodes[$counttt] = "";
			$returnval = true;
		}
		
		return $returnval;
	}
	
	//Adds a new shortcode
	public function addshort($mapname, $short)
	{
		$returnval = false;
		
		//Find map
		$counter = 0;
		$count = count($this->maps);
		$map = false;
		while($counter < $count)
		{
			if($mapname == $this->maps[$counter])
			{
				$map = $counter;
			}
		
			$counter++;
		}
		
		//If map wasn't found
		if($map === false)
		{
			$returnval = false;
		} else
		{
			//If it was
			$this->shortcodes[$map] .= " " . $short;
			$returnval = true;
		}
		
		return $returnval;
	}
	
	//Finds a map based on input
	public function getmap($input)
	{
		//Default return
		$returnval = "";
	
		//Check actual map names first
		$counter = 0;
		$count = count($this->maps);
		while($counter < $count)
		{
			if($input == $this->maps[$counter])
			{
				$returnval = $this->maps[$counter];
				$counter = $count;
			}

			$counter++;
		}
		
		//Check shortcodes if we still don't know
		if($returnval == "")
		{
			$counter1 = 0;
			$count1 = count($this->shortcodes);
			while($counter1 < $count1)
			{
				$temp = explode(" ", $this->shortcodes[$counter1]);
				
				$counter2 = 0;
				$count2 = count($temp);
				while($counter2 < $count2)
				{
					if($input == $temp[$counter2])
					{
						$returnval = $this->maps[$counter1];
						$counter2 = $count2;
						$counter1 = $count1;
					}
				
					$counter2++;
				}
			
				$counter1++;
			}
		}
		
		//If we still don't know... oh well
		return $returnval;
	}
	
	//Get Map Count
	public function getmapcount()
	{
		return count($this->maps);
	}
	
	//Test output
	public function testoutput()
	{
		print_r($this->maps);
		print_r($this->shortcodes);
	}
	
	//Get maps
	public function getmapslist()
	{
		return $this->maps;
	}
}

function reloadmapsss()
{
	global $maps;
	print_r($maps);
	
	//file_put_contents("globals.txt", var_export($GLOBALS, true));
	//file_put_contents("maps.txt", var_export($maps, true));
	
	$maps->init();
	include("maplist.php");
}

//Instantiate the maps list
global $maps;
$maps = new maps;
$maps->init();

//Include the maps list
include("maplist.php");
?>