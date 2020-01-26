<?php
//Wait bot
include_once("SmartIRC.php");
include_once("config.php");

class MedicCheck
{
	var $players;
	
	function AddMedicGame($qauth)
	{
		$id = $this->GetID($qauth);
		
		if($id == false)
		{
			$id = count($this->players);
			$this->players[$id]['qauth'] = $qauth;
			$this->players[$id]['games'] = 1;
		} else
		{
			$this->players[$id]['games']++;
		}
	}
	
	function GetID($qauth)
	{
		$counter = 0;
		$count = count($this->players);
		$returnval = false;
		
		while($counter < $count)
		{
			if($this->players[$counter]['qauth'] == $qauth)
			{
				$returnval = $counter;
			}
			$counter++;
		}
		
		return $returnval;
	}
	
	function ReturnOrdered()
	{
		//Create new ordered array of all medics
		$old = $this->players;
		$new = array();
		
		$counter1 = 0;
		$count1 = count($old);
		while($counter1 < $count1)
		{
			//Find next largest game count
			$counter2 = 0;
			$count2 = $count1;
			$largestsofar = (0 - 1);
			while($counter2 < $count2)
			{
				if($old[$counter2]['games'] >= $old[$largestsofar]['games'])
				{
					$largestsofar = $counter2;
				}
				
				$counter2++;
			}
			
			//Insert this largest one into the array
			$new[$counter1] = $old[$largestsofar];
			//Set this value in the old array to be lower
			$old[$largestsofar]['games'] = (0 - 2);
			
			$counter1++;
		}
		
		//Return this final value
		return $new;
	}
}

function mysql_pu_delmotw()
{
	global $mysql;
	
	$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	$sql = "TRUNCATE `motw`";
	
	mysql_query($sql, $ln);
}

function mysql_pu_addmotw($medics)
{
	global $mysql;
	
	$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	
	//Start loop
	$counter = 0;
	$count = 5;
	while($counter < $count)
	{
		$sql = "INSERT INTO `motw` (`id`, `qauth`, `played`) VALUES ('".($counter+1)."', '".mysql_real_escape_string($medics[$counter]['qauth'])."', '".mysql_real_escape_string($medics[$counter]['games'])."');";
		mysql_query($sql, $ln);
		$counter++;
	}
}

//Get current Medics of the week from MySQL
function mysql_pu_motw()
{
	global $mysql;
	
	$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	
	$sql = "SELECT * FROM `motw`";
	
	$result = mysql_query($sql, $ln);
	
	while($list[] = mysql_fetch_assoc($result));
	array_pop($list);
	
	return $list;
}

function mysql_pu_getnick($qauth)
{
	global $mysql;
	
	$returnval = false;
	
	$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	
	$sql = "SELECT * FROM `users` WHERE `qauth` ='".mysql_real_escape_string($qauth)."' LIMIT 1;";
	
	$result = mysql_query($sql, $ln);
	
	if(mysql_num_rows($result) > 0)
	{
		$returnval = mysql_fetch_assoc($result);
		$returnval = $returnval['nick'];
	}
	
	return $returnval;
}

function AnnounceMOTW()
{
	//Bring in global Vars
	global $irc;
	global $mysql;
	
	//$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", "test!");
	
	//Get full pickup list
	$ln = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	$time = (time() - (60 * 60 * 24 * 7));
	$sql = "SELECT * FROM `pickups` WHERE time > '".$time."';";
	$result = mysql_query($sql, $ln);
	
	//Get results into an array
	while($puresult[] = mysql_fetch_assoc($result));
	array_pop($puresult);
	
	
	//Create new MedicCheck instance
	$MedicCheck = new MedicCheck;
	
	//Now check the 2 medics for every pickup
	$counter = 0;
	$count = count($puresult);
	while($counter < $count)
	{
		//Reset med ID array
		$meds = array();
		
		//Find meds in each pickup
			$classes = explode(" ", $puresult[$counter]['classes']);
			$players = explode(" ", $puresult[$counter]['players']);
			
			$counter2 = 0;
			$count2 = 12;
			while($counter2 < $count2)
			{
				if($classes[$counter2] == "medic")
				{
					$meds[] = $counter2;
				}
				$counter2++;
			}
		
		//Run GameAdd on both medics
		$MedicCheck->AddMedicGame($players[$meds[0]]);
		$MedicCheck->AddMedicGame($players[$meds[1]]);
		
		//echo "\n".$players[$meds[0]]." ".$players[$meds[1]];
		
		$counter++;
	}	
	
	//Now Get the order array for the medic list
	$list = $MedicCheck->ReturnOrdered();
	$medics[0] = $list[0];
	$medics[1] = $list[1];
	$medics[2] = $list[2];
	$medics[3] = $list[3];
	$medics[4] = $list[4];
	
	//Get Previous medics from MySQL
	$prev = mysql_pu_motw();
	
	//Set channel +m and pause the bot
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.admin", "!pause Announcing Medics of the Week!");
	$irc->mode("#pixeltf2.pickup", "+m");
	
	//Remove previous medics of the week
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", "\x0302,00Hey lads (and CGChris you dickhead), lets announce the Medics of the week!");
	mysql_pu_delmotw();
	
	//Remove their +v
	$counter = 0;
	$count = 5;
	$names = "";
	while($counter < $count)
	{
		$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$prev[$counter]['qauth']." -v");
		$names .= mysql_pu_getnick($qauth) . " ";
		$counter++;
	}
	$irc->mode($channel, '-vvvvv '.$names, $priority);
	
	//Fifth
	$nick = mysql_pu_getnick($medics[4]['qauth']);
	$output = "\x0302,00In Fifth place with ".$medics[4]['games']." pickups played: \x02".$nick."\x02!";
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", $output);
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$medics[4]['qauth']." +v");
	
	//Fourth
	$nick = mysql_pu_getnick($medics[3]['qauth']);
	$output = "\x0302,00In Fourth place with ".$medics[3]['games']." pickups played: \x02".$nick."\x02!";
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", $output);
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$medics[3]['qauth']." +v");
	
	//Third
	$nick = mysql_pu_getnick($medics[2]['qauth']);
	$output = "\x0302,00In Third place with ".$medics[2]['games']." pickups played: \x02".$nick."\x02!";
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", $output);
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$medics[2]['qauth']." +v");
	
	//Second
	$nick = mysql_pu_getnick($medics[1]['qauth']);
	$output = "\x0302,00In Second place with ".$medics[1]['games']." pickups played: \x02".$nick."\x02!";
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", $output);
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$medics[1]['qauth']." +v");
	
	//First
	$nick = mysql_pu_getnick($medics[0]['qauth']);
	$output = "\x0302,00And in first place with ".$medics[0]['games']." pickups played: \x02".$nick."\x02!";
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.pickup", $output);
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "CHANLEV #pixeltf2.pickup #".$medics[0]['qauth']." +v");
	
	//Delete Previous Medics
	mysql_pu_delmotw();
	
	//Add them to MySQL
	mysql_pu_addmotw($medics);
	
	//Remove channel +m and re-enable bot
	$irc->message(SMARTIRC_TYPE_CHANNEL, "#pixeltf2.admin", "!enable");
	$irc->mode("#pixeltf2.pickup", "-m");
	
	//Now kill yourself
	$irc->message(SMARTIRC_TYPE_QUERY, $irc->_nick, "cocobeans");
}

class PickupBot
{
	function wait(&$irc, &$data)
	{
		print_r($data);
		echo $irc->_nick;
		
		//Check if its us in question
		if($data->nick == $irc->_nick)
		{
			//Check if its the pickup chan
			if($data->channel == "#pixeltf2.pickup")
			{
				//Deregister handler
				$irc->unregisterActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot, 'wait');
				
				//Call
				AnnounceMOTW();
				//$prev = mysql_pu_motw();
				//print_r($prev);
			}
		}
	}

	function kill(&$irc, &$data)
	{
		$irc->disconnect();
		echo "\n\n\nWAITBOT EXTERNALLY KILLED\n\n\n";
		die();
	}
}

$PickupBot = &new PickupBot;

//Create bot class
$irc = &new Net_SmartIRC();

//Set info
$irc->setUseSockets(true);

//Enable debugging
$irc->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);

//Connect Bot
	$irc->connect('multiplay.uk.quakenet.org', 6667);
	$irc->login('MedicOfTheWeek', 'PixelGaming.eu Pickup Bot #2', 0,'MedicOfTheWeek');
	
	//Auth
	$irc->message(SMARTIRC_TYPE_QUERY, "Q@CServe.quakenet.org", "AUTH MedicOfTheWeek captain42");
	
    $irc->join('#pixeltf2.bots', 'dreamworks');
	$irc->join('#pixeltf2.admin', 'poxelgay');
	$irc->join('#pixeltf2.pickup');

$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot, 'wait');
$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, 'cocobeans', &$PickupBot, 'kill');

$irc->listen();


$irc->disconnect();

?>
