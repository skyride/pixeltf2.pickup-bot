<?php
	
if(!function_exists("addserver"))
{
	function addserver($ip, $port, $rcon, $message)
	{
		global $servers;
			
		$counter = count($servers) + 1;
			
		$servers[$counter] = new server;
		$servers[$counter]->init($ip, $port, $rcon);
		$servers[$counter]->message = $message;

		//Return Server ID
		return $counter;
	}
}
	
	//Pixel Pickup Servers Config
	addserver("85.236.101.81", "37015", "cocobeans", "server provided by Multiplay.co.uk");
	addserver("46.105.121.112", "27125", "cocobeans", "server provided by Multiplay.co.uk");
	addserver("85.236.100.36", "37215", "cocobeans", "server provided by Multiplay.co.uk");
	addserver("85.236.100.36", "37315", "cocobeans", "server provided by Multiplay.co.uk");

?>