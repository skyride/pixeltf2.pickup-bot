<?php
//Pixel Pickup Bot
//!message bot
//This bots sole purpose is to print !messages and !skill to IRC

include("SmartIRC.php");
include("config.php");
include("servers.php");
include("maps.php");

function mysql_pu_lastpickup()
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `pickups` ORDER BY id DESC LIMIT 1;";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	$result = mysql_query($sql, $link);
	mysql_close($link);
	
	//Get results
	$returnvalue = mysql_fetch_assoc($result);
	return $returnvalue;
}

class MessageFlood
{
	var $flood;
	var	$nickflood;
	
	function CheckFlood($message)
	{
		$message = strtolower($message);
		
		if((time() - $flood[$message]) < 20)
		{
			$returnval = false;
		} else
		{
			$returnval = true;
			$flood[$message] = time();
		}
		
		return $returnval;
	}
	
	function SkillFlood($nick)
	{
		$nick = strtolower($nick);
		
		if((time() - $nickflood[$nick]) < 30)
		{
			$returnval = false;
		} else
		{
			$returnval = true;
			$nickflood[$nick] = time();
		}
		
		return $returnval;
	}
}

$messageflood = new MessageFlood;

class MessageBot
{
	function Maps(&$irc, &$data)
	{
		if(strtolower(substr($data->message, 0, 5)) == "!maps")
		{
			global $maps;
			
			$maplist = $maps->getmapslist();
			
			$message = "\x02\x0302,00Maps:\x02 ";
			
			$counter = 0;
			$count = count($maplist);
			while($counter < $count)
			{
				$message .= $maplist[$counter] . ", ";
				$counter++;
			}
			
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
		}
	}

	function SayMessage(&$irc, &$data)
	{
		//Bring in Global Vars
		global $mysql;
		global $messageflood;
		
		//Process the message
		$message = explode(" ", strtolower($data->message));
		$message = $message[0];
		
		if($message != "")
		{
			if(substr($message, 0, 1) == "!")
			{
				$message = substr($message, 1);
				
				//Check messageflood
				if($messageflood->CheckFlood($message))
				{				
					//Query MySQL for messages
					$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
					mysql_select_db($mysql->db);
					
					$sql = "SELECT * FROM `messages` WHERE `command` = '".mysql_real_escape_string($message)."' LIMIT 1;";
					
					$result = mysql_query($sql, $ln);
					
					//Check if any results were returned
					if(mysql_num_rows($result) > 0)
					{
						//Send output to IRC
						$outputmessage = mysql_fetch_assoc($result);
						$output = "\x0302,00!" . ucfirst($message) . ": " . $outputmessage['message'];
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
					}
				}
			}
		}
	}
	
	function SkillMessage(&$irc, &$data)
	{
		//Bring in Global Vars
		global $mysql;
		global $messageflood;
		
		//Process the message
		$message = explode(" ", $data->message);
		
		//Check if it is the !skill command
		if(strtolower($message[0]) == "!skill")
		{
			$nick = $message[1];
			
			if($nick == "")
			{
				$nick = $data->nick;
			}
			
			$nick = strtolower($nick);
			
			//Check Message flood
			if($messageflood->SkillFlood($nick))
			{
				//Query MySQL for this player
				$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
				mysql_select_db($mysql->db);
					
				$sql = "SELECT * FROM `users` WHERE `nick` = '".mysql_real_escape_string($nick)."' LIMIT 1;";
				
				$result = mysql_query($sql, $ln);
				
				//Check if any results were returned
				if(mysql_num_rows($result) > 0)
				{
					//Send output to IRC
					$info = mysql_fetch_assoc($result);
					$output = "\x02\x0302,00Skill:\x02 " . $info['nick'] . " || Scout: " . $info['skill_scout'] . " / Demo: " . $info['skill_demoman'] . " / Soldier: " . $info['skill_soldier'] . " / Medic: " . $info['skill_medic'];
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
				}
			}
		}
	}
	
	function Last24(&$irc, &$data)
	{
		//Bring in global Vars
		global $mysql;
		global $messageflood;
		
		//Check input
		$input = $data->message;
		
		//Check message flood
		if(strtolower(substr($input, 0, 5)) == "!last")
		{
			if(strlen($input) > 5)
			{
				$input = str_replace(" ", "", $input);
				$length = substr($input, 5, (strlen($input)) - 5);
				if($length != "")
				{					
					if($messageflood->CheckFlood($input))
					{
						//Query MySQL for most recent Pickups
						$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
						mysql_select_db($mysql->db);
							
						$time = time() - (60 * 60 * $length);
							
						$sql = "SELECT id FROM `pickups` WHERE `time` > '".$time."';";
							
						$result = mysql_query($sql, $ln);
							
						//Send Results to IRC
						$played = mysql_num_rows($result);
						$output = "\x0302,00There have been ".$played." pickups played in the last ".$length."hrs";
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
					}
				}
			}
		}
	}
	
	/*function Last24(&$irc, &$data)
	{
		//Bring in global Vars
		global $mysql;
		global $messageflood;
		
		//Check input
		$input = $data->message;
		
		//Check message flood
		if(strtolower(substr($input, 0, 7)) == "!last24")
		{
			if($messageflood->CheckFlood("last24"))
			{
				//Query MySQL for most recent Pickups
				$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
				mysql_select_db($mysql->db);
				
				$time = time() - (60 * 60 * 24);
				
				$sql = "SELECT id FROM `pickups` WHERE `time` > '".$time."';";
				
				$result = mysql_query($sql, $ln);
				
				//Send Results to IRC
				$played = mysql_num_rows($result);
				$output = "\x0302,00There have been ".$played." pickups played in the last 24hrs";
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
			}
		}
	}*/
	
	function Last7(&$irc, &$data)
	{
		//Bring in global Vars
		global $mysql;
		global $messageflood;
		
		//Check input
		$input = $data->message;
		
		//Check message flood
		if(strtolower(substr($input, 0, 7)) == "!lastweek")
		{
			if($messageflood->CheckFlood("lastweek"))
			{
				//Query MySQL for most recent Pickups
				$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
				mysql_select_db($mysql->db);
				
				$time = time() - (60 * 60 * 24 * 7);
				
				$sql = "SELECT id FROM `pickups` WHERE `time` > '".$time."';";
				
				$result = mysql_query($sql, $ln);
				
				//Send Results to IRC
				$played = mysql_num_rows($result);
				$output = "\x0302,00There have been ".$played." pickups played in the last 7 days";
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
			}
		}
	}

	function LastPU(&$irc, &$data)
	{
		global $mysql;
		global $messageflood;
	
		if(strtolower(substr($data->message, 0, 5)) == "!last")
		{
			if(strlen($data->message) <= 5)
			{
				if(strtolower(substr($data->message, 0, 6)) != "!last7")
				{
					if(strtolower(substr($data->message, 0, 7)) != "!last24")
					{
						//anti-flood
						if($messageflood->CheckFlood("last"))
						{
							$lastantiflood = time();
							$lastpickup = mysql_pu_lastpickup();

							//Calculate time since
							$timepassed = time() - $lastpickup["time"];
							switch($timepassed)
							{
								//Less than a minute
								case ($timepassed <= 60):
									$time = "Less than a minute ago";
									break;

								//Less than an hour
								case ($timepassed <= 3600):
									$minutes = floor($timepassed / 60);
									$time = $minutes . " minutes ago";
									break;

								//Less than a day
								case ($timepassed <= 86400):
									$hours = floor($timepassed / 3600);
									$minutes = floor(($timepassed - ($hours * 3600)) / 60);
									$time = $hours . " hours and " . $minutes . " minutes ago";
									break;

								//Less than 2 days
								case ($timepassed <= 172800):
									$hours = floor(($timepassed / 3600) - 24);
									$minutes = floor($timepassed / 60 - (($hours + 24) * 60));
									$time = "1 day, " . $hours . " hours and " . $minutes . " minutes ago";
									break;

								//Greater than 2 days
								case ($timepassed > 172800):
									$days = floor($timepassed / 86400);
									$hours = floor(($timepassed / 3600) - ($days * 24));
									$minutes = floor(($timepassed / 60) - (($days * 1440) + ($hours * 60)));
									$time = $days . " days, " . $hours . " hours and " . $minutes . " minutes ago";
									break;
							}

								//Display the message in channel
								$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Last pickup began ".$time." on Server ".$lastpickup["server"]);
						}
					}
				}
			}
		}
	}
	
	function Servers(&$irc, &$data)
	{
		$input = explode(" ", $data->message);
		
		if(strtolower(substr($input[0], 0, 7)) == "!server")
		{
			global $servers;
			
			$count = count($servers);
			
			print_r($servers);
			
			if($input[1] <= $count)
			{
				echo "lol1";
				
				$input[1] = intval($input[1]);
				
				if($input[1] != 0)
				{
					echo "lol3";
					
					$server = $input[1];
					
					$info = $servers[$server]->get_info();
					
					if($info == false)
					{
						$servermessage = "\x02\x0304,00DOWN\x02\x0302,00";
					} else
					{
						$servermessage = $info['totalplayers']."/".$info['maxplayers'];
					}
						
					$message = "\x0302,00\x02Server ".$server.":\x02 (".$servermessage."):\x03  connect ".$servers[$server]->host.":".$servers[$server]->port." ; password pixelpickup";
						
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
				}
			}
		}
	}
	
	function Rehash(&$irc, &$data)
	{
		if(strtolower(substr($data->message, 0, 7)) == "!rehash")
		{
			//Reload Server Config
			global $servers;
			
			$servers = array();
			include("serverlist.php");
			
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02\x0302Server Configuration File Successfully Reloaded: ".count($servers)." now in rotation");
		}
	}
	
	function ReloadMaps(&$irc, &$data)
	{
		if(strtolower(substr($data->message, 0, 11)) == "!reloadmaps")
		{
			//Reload Server Config
			global $maps;
			
			reloadmapsss();
			
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02\x0302Map List Successfully Reloaded: ".$maps->getmapcount()." now in rotation");
		}
	}
}

//Create IRC Instance
$irc = &new Net_SmartIRC();

//Create MessageBot Class
$MessageBot = &new MessageBot;

//Set info
$irc->setUseSockets(true);

//Enable debugging
$irc->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);

//Connect Bots
	$irc->connect('multiplay.uk.quakenet.org', 6667);
	$irc->login("PixelMSGBot", $ircinfo->realname, 0,'PixelMSGBot');
	$irc->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH '.$ircinfo->qauth_user.' '.$ircinfo->qauth_pass);
	
	$irc->join("#pixeltf2.pickup");
	$irc->join("#pixeltf2.admin", "poxelgay");
	$irc->join("#pixeltf2.bots", "dreamworks");
	$irc->join("#etf2l.sc2");
	
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'SayMessage');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'SkillMessage');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'Last24');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'Last7');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'LastPU');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'Servers');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'Maps');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'ReloadMaps');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$MessageBot, 'Rehash');
	
	$irc->listen();

?>
