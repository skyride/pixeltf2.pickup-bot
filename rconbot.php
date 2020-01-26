<?php

include("servers.php");
include("SmartIRC.php");

function whitespace($input, $length)
{
	$output = substr($input, 0, $length);
	$counter = 0;
	$count = $length - strlen($output);
	while($counter < $count)
	{
		$output .= " ";
		$counter++;
	}
	
	//Return Value
	return $output;
}

function make_time($time)
{
	//Check if less than a minute
	if($time < 60)
	{
		$output = $time . "secs";
	} else
	{
		$output = floor($time / 60) . "mins";
	}
	
	//Return output
	return $output;
}

class PickupBot2
{
	function rcon(&$irc, &$data)
	{
		global $servers;

		//Check if the rcon command was sent
		$command = explode(" ", $data->message);
		if($command[0] == "!rcon")
		{
			$server = $command[1];
			array_shift($command);
			array_shift($command);
			$command = implode(" ", $command);

			if($server != "")
			{
				if($server == ("1" || "2" || "3" || "4" || "5"))
				{
					//Send Rcon command
					$output = $servers[$server]->rcon($command);

					//Give output
					$output = explode("\n", $output);
					$counter = 0;
					$count = count($output);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "command sent: ".$command);
					while($counter < $count)
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output[$counter]);
						$counter++;
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!rcon: You didn't enter a correct server number");
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!rcon <server number> <command>");
			}
		} else
		{
			//$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "))");
		}
	}
	
	function info(&$irc, &$data)
	{
		global $servers;
		
		//Check if the srvstats command was sent
		$command = explode(" ", $data->message);
		if($command[0] == "!srvstats")
		{
			$server = $command[1];
			if($server != "")
			{
				if(is_numeric($server))
				{
					//Get info
					$info = $servers[$server]->get_info();
					
					//IRC Message
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Name: ".$info['name']);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "IP: ".$info['ipport']);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Players: ".$info['totalplayers']."/".$info['maxplayers']);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Map: ".$info['map']);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Game Version: ".$info['gameversion']);
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!srvstats <server number>");
			}
		}
	}
	
	function players(&$irc, &$data)
	{
		global $servers;
		
		//Check if the srvstats command was sent
		$command = explode(" ", $data->message);
		if($command[0] == "!srvplayers")
		{
			$server = $command[1];
			if($server != "")
			{
				if(is_numeric($server))
				{
					//Get info
					$info = $servers[$server]->get_players();
					
					//Header
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Number of Players: ".$info['activeplayers']);
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Name:                        Kills:  Time on Server:");
					
					//Print Players
					$counter = 0;
					$count = $info['activeplayers'];
					while($counter < $count)
					{
						//Build message
						$output = whitespace($info['players'][$counter]['name'], 29);
						$output .= whitespace($info['players'][$counter]['kills'], 8);
						$output .= make_time($info['players'][$counter]['onlinetime']);
						
						//Send to IRC
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
						$counter++;
					}
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!srvplayers <server number>");
			}
		}
	}
	
	function kick(&$irc, &$data)
	{
		print_r($data);
		print_r($irc);
	}
}

$PickupBot2 = &new PickupBot2;

$PickupBot2->players = $players;
$PickupBot2->serverid = $serverid;

//Create bot class
$irc = &new Net_SmartIRC();

//Set info
$irc->setUseSockets(true);

//Enable debugging
$irc->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);

//Connect Bot
	$irc->connect('multiplay.uk.quakenet.org', 6667);
	$irc->login('RconBot', 'PixelGaming.eu Pickup Bot #2', 0,'TestThing');
	$irc->join('#pixeltf2.admin', 'poxelgay');

//$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot2, 'send');
//$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, 'cocobeans', &$PickupBot2, 'kill');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '', &$PickupBot2, 'rcon');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '', &$PickupBot2, 'info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '', &$PickupBot2, 'players');
$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '', &$PickupBot2, 'kick');

$irc->listen();


$irc->disconnect();

?>
