<?php

//Pixel Gaming Pickup Bot

function pminfo($playerlist, $serverid)
{
    global $servers;
    global $irc;
	  $players = explode(" ", $playerlist);
	
	//Now PM Players
	//PM the medics
    $irc->message(SMARTIRC_TYPE_QUERY, $players[0], "A Pickup has just started with you in it. You will be playing \x02\x0309Medic\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[1], "A Pickup has just started with you in it. You will be playing \x02\x0309Medic\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);

            //PM the soldiers
    $irc->message(SMARTIRC_TYPE_QUERY, $players[2], "A Pickup has just started with you in it. You will be playing \x02\x0309Soldier\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[3], "A Pickup has just started with you in it. You will be playing \x02\x0309Soldier\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[4], "A Pickup has just started with you in it. You will be playing \x02\x0309Soldier\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[5], "A Pickup has just started with you in it. You will be playing \x02\x0309Soldier\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);

            //PM The Scouts
    $irc->message(SMARTIRC_TYPE_QUERY, $players[6], "A Pickup has just started with you in it. You will be playing \x02\x0309Scout\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[7], "A Pickup has just started with you in it. You will be playing \x02\x0309Scout\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[8], "A Pickup has just started with you in it. You will be playing \x02\x0309Scout\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[9], "A Pickup has just started with you in it. You will be playing \x02\x0309Scout\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);

            //PM the Demos
    $irc->message(SMARTIRC_TYPE_QUERY, $players[10], "A Pickup has just started with you in it. You will be playing \x02\x0309Demoman\x03\x02 on \x02\x0312Team BLU\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
    $irc->message(SMARTIRC_TYPE_QUERY, $players[11], "A Pickup has just started with you in it. You will be playing \x02\x0309Demoman\x03\x02 on \x02\x0304Team RED\x03\x02. \x02Server ".$serverid.":\x02 ".$servers[$serverid]->connect);
}

function stvwait($serverid, $playersblu, $playersred, $pickupid, $winningmap)
{
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
         // we are the parent
            echo "\nNew STVWAIT INSTANCE STARTED!\n";
    } else {
         // we are the child
            include("stvwait.php");
    }
}

function rand_str($length = 12, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars{rand(0, $chars_length)};
   
    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string))
    {
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};
       
        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
   
    // Return the string
    return $string;
}

/*function find_map($userinput)
{
	global $pickupchannel;
	global $irc;

	//Set default return
	$returnvalue = "";

	switch($userinput)
	{
		case "":
			$returnvalue = "";
			break;

		case "bad": case "badl": case "badla": case "badlands": case "cp_badlands": case "cp_bad": case "faglands": case "lands": case "blands": case "b": case "dl": case "bl":
			$returnvalue = "cp_badlands";
			break;

		case "koth_bad": case "k_badl": case "koth_badla": case "koth_badlands": case "kothlands": case "kothbl":
			$returnvalue = "koth_badlands";
			break;

		case "gra": case "gran": case "granary": case "grana": case "cp_gran": case "cp_granary": case "y":
			$returnvalue = "cp_granary";
			break;

		case "fre": Case "free": case "freight": case "cp_fr": case "cp_fre": case "cp_freight":
			$returnvalue = "cp_freight_final1";
			break;

		case "obs": case "obscure": case "obsc": case "cp_obs": case "cp_obscure": case "cure": case "cp_lawlstickies": case "cp_obscure_final":
			$returnvalue = "cp_obscure_final";
			break;

		case "warm": case "warmfront": case "war": case "wfront": case "front": case "cp_war": case "cp_warmfront":
			$returnvalue = "cp_warmfront";
			break;

		case "cp_freight_final1": case "freight_final": case "freight_final1": case "newfre": case "newfreight": case "newfree": case "final1":
			$returnvalue = "cp_freight_final1";
			break;

		case "gravel": case "grav": case "gravelpit": case "gpit": case "cp_grav": case "cp_gravel": case "cp_gravelpit":
			$returnvalue = "cp_gravelpit";
			break;

		case "gor": case "gorge": case "cp_gorge": case "cp_gor":
			$returnvalue = "cp_gorge";
			break;

		case "gul": case "gull": case "gully": case "gullywash": case "cp_gul": case "cp_gull": case "cp_gully": case "cp_gullywash":
			$returnvalue = "cp_gullywash_pro";
			break;

		case "foll": case "fol": case "follower": case "cp_fol": case "cp_foll": case "cp_follower":
			$returnvalue = "cp_follower";
			break;

		case "waste": case "was": case "cp_waste": case "cp_was": case "waste_v2": case "cp_waste_v2":
			$returnvalue = "cp_waste_v2";
			break;
			
		case "well": case "cp_well": case "foster": case "cp_stalemate":
			$returnvalue = "cp_well";
			break;

		case "via": case "viad": case "viaduct": case "duct": case "koth_via": case "koth": case "koth_viaduct": case "pro_viaduct":
			$returnvalue = "koth_pro_viaduct_rc3";
			break;

		case "yuk": case "yukon": case "cp_yukon": case "yu": case "cp_yukon_final":
			$returnvalue = "cp_yukon_final";
			break;
			
		case "cp_bazillion_rc4": case "baz": case "bazz": case "bazillion": case "cp_bazillion": case "bazilion": case "arnoldsnewmap":
			$returnvalue = "cp_bazillion_rc4";
			break;
			
		case "cp_snakewater_rc3": case "snakewater": case "snake": case "sna": case "snakey": case "snakerc2":
			$returnvalue = "cp_snakewater_rc4";
			break;
			
		case "tur": case "turd": case "turdbine": case "turbine": case "fuckinggayctfthing": case "ctf_turbine": case "bine": case "ine": case "turb": case "turbinepro":
			$returnvalue = "ctf_turbine_pro_rc1";
			break;
			
		case "antiquity": case "anti": case "ant": case "cp_antiquity_b5": case "aztec": case "lookslikeitsfuckingantiquelolgeddit": 
			$returnvalue = "cp_antiquity_b5";
			break;
			
		case "grack": case "gp_grack_b10": case "cp_grack": case "crack": 
			$returnvalue = "cp_grack_b10";
			break;
			
		case "ash": case "ashville": case "ville": case "koth_ashville":
			$returnvalue = "koth_ashville_rc1";
			break;
		
		case "lane": case "prolane": case "cp_prolane_rc3": case "prolane_rc3":
			$returnvalue = "cp_prolane_rc3";
			break;
			
		case "progran": case "cp_granary_pro": case "pro_granny": case "granny":
			$returnvalue = "cp_granary_pro";
			break;
			
		case "obsureremake": case "obscure_remake": case "obs_remake":
			$returnvalue = "cp_obscure_remake_b2";
			break;

		//Randomly pick
		case "granlands":
			$output = rand(1, 100);
			if($output > 50)
			{
				$returnvalue = "cp_badlands";
			} else
			{
				$returnvalue = "cp_granary";
			}
			break;

		default:
			$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Unknown map: \"".$userinput."\"");
			break;
	}

	return $returnvalue;
}*/

function find_map($userinput)
{
	global $irc;
	global $maps;
	
	//Test output
	$maps->testoutput();
	
	if($userinput != "")
	{	
		//Find the map
		$res = $maps->getmap($userinput);
		
		if($res == "")
		{
			$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Unknown map: \"".$userinput."\"");
		}
	} else
	{
		$res = $userinput;
	}
	
	return $res;
}

function is_odd($number) 
{
	return $number & 1; // 0 = even, 1 = odd
}

function NewUser($qauth, $nick)
{
    global $users;
    global $irc;

    //Add user to the bot
    $users->add_user($qauth, $nick, "PixelTF2Bot");

    //And PM them with a welcome message
    $irc->message(SMARTIRC_TYPE_QUERY, $nick, "Hi, Welcome to #pixeltf2.open, the new pickup channel for players of all skill levels!");
    $irc->message(SMARTIRC_TYPE_QUERY, $nick, "This is just a welcome message from the bot, for more information about rules, gameplay, and how to use the bot, check out this link: http://pickup.pixelgaming.eu/forum/viewtopic.php?f=11&t=20");
    $irc->message(SMARTIRC_TYPE_QUERY, $nick, "Good luck and have fun, just remember to always be fair to other players");
}

function add_player($qauth, $nick, $class, $skill, $map)
{
	global $irc;
	global $pickup;
	global $pickupchannel;
        global $messageflood;

	//Get the actual map
	$map = find_map($map);

        //Check if the person is inside the wait period
        if($messageflood['addwait'][$qauth] < time())
        {
            //Check if the player isn't already added as that class
            if($pickup->is_added($qauth) == $class)
            {
                if($messageflood['alreadyadded'][$qauth] < time())
                {
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$nick.", you are already added as that class");
                    $messageflood['alreadyadded'][$qauth] = time() + 45;
                }
            } else
            {
                    $counter3 = 0;
                    while($counter3 < 12)
                    {
                            if($pickup->player[$counter3]->qauth == $qauth)
                            {
                                    if($map == "")
                                    {
                                            $map = $pickup->player[$counter3]->map;
                                    }
                            }
                            $counter3++;
                    }

                    //Check if player is already added as another class so we can remove them
                    if($pickup->is_added($qauth) !== false)
                    {
                            $counter3 = 0;
                            while($counter3 < 12)
                            {
                                    if($pickup->player[$counter3]->qauth == $qauth)
                                    {
                                            if($map == "")
                                            {
                                                    $map = $pickup->player[$counter3]->map;
                                            }
                                    }
                                    $counter3++;
                            }

                            $pickup->rm_player($qauth);
                    }

                    //Add the player
                    $pickup->add_player($qauth, $nick, $class, $skill);

                    //Perform map vote if specified
                    if($map != "")
                    {
                            $pickup->mapvote($qauth, $map);
                    }

                    //Set add wait time
                    $messageflood['addwait'][$qauth] = time() + 60;
                    $messageflood['addwait2'][$qauth] = 0;

                    //Update channel topic
                    SetTopic();
            }
      } else
      {
          if($messageflood['addwait2'][$qauth] == 0)
          {
                $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$nick.": You cannot re-add or change class less than 60 seconds after you have added");
                $messageflood['addwait2'][$qauth] = 1;
          }
      }
}

function rm_player($qauth)
{
	global $irc;
	global $pickup;
	global $pickupchannel;

	//Check if they are added						
	if($pickup->is_added($qauth) !== false)
	{
		$pickup->rm_player($qauth);
		//Update channel topic
                SetTopic();
	}
}

////////////////////////////////////////
//MySQL functions  /////////////////////
////////////////////////////////////////

function mysql_escape($input)
{
	//Escape characters that could be used for SQL injection
	$input = str_replace("\\", "\\\\", $input);
	$input = str_replace(";", "\\;", $input);
	$input = str_replace("'", "\\'", $input);
	$input = str_replace("\"", "\\\"", $input);
	$input = str_replace("`", "\\`", $input);
	return $input;
}

function mysql_pu_addreport($reporter, $reported, $reason)
{
	global $mysql;
	
	$reporter = mysql_escape($reporter);
	$reported = mysql_escape($reported);
	$reason = mysql_escape($reason);
	
	//Build MySQL query
	$sql = "INSERT INTO `reports` (`id`, `reporter`, `reported`, `reason`, `time`) VALUES (NULL, '".$reporter."', '".$reported."', '".$reason."', UNIX_TIMESTAMP());";
	
	//Execute Query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_skill($qauth, $class)
{
	global $mysql;
	$output = 3;
	
	//Build MySQL query
	$sql = "SELECT * FROM `users` WHERE `qauth` = '".mysql_escape($qauth)."'";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_select_db($mysql->db);
	$result = mysql_query($sql, $link);
	mysql_close($link);
	
	//Get results
	$result = mysql_fetch_assoc($result);
	
	//Parse return val
	switch($class)
	{
		case "scout":
			$output = $result['skill_scout'];
			break;
			
		case "demo":
			$output = $result['skill_demoman'];
			break;
			
		case "soldier":
			$output = $result['skill_soldier'];
			break;
		
		case "medic":
			$output = $result['skill_medic'];
			break;
	}
	
	//Return the output
	return $output;
}

function mysql_pu_lastpickup()
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`pickups` ORDER BY id DESC LIMIT 1;";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);
	
	//Get results
	$returnvalue = mysql_fetch_assoc($result);
	return $returnvalue;
}

function mysql_pu_adduser($qauth, $nick, $inviter, $skill_scout="5" , $skill_demoman="5" , $skill_soldier="5", $skill_medic="5")
{
	global $mysql;

	//Build MySQL query
	$sql = "INSERT INTO `".mysql_escape($mysql->db)."`.`users` (`id`, `qauth`, `nick`, `banned`, `admin`, `skill_scout`, `skill_demoman`, `skill_soldier`, `skill_medic`, `invitedby`, `banreason`, `invitedate`, `banexpire`, `played`) VALUES (NULL, '".mysql_escape($qauth)."', '".mysql_escape($nick)."', '0', '0', '".$skill_scout."', '".$skill_demoman."', '".$skill_soldier."', '".$skill_medic."', '".mysql_escape($inviter)."', '', '".time()."', '', '0');";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

//Revoke a users invite
function mysql_pu_deluser($qauth)
{
	global $mysql;
	
	//Build MySQL quert
	$sql = "DELETE FROM `users` WHERE qauth = '".mysql_escape($qauth)."'";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_addpickup($players, $classes, $map, $server)
{
	global $mysql;

	//Build MySQL query
	$sql = "INSERT INTO `".mysql_escape($mysql->db)."`.`pickups` (`id`, `players`, `classes`, `map`, `server`, `time`) VALUES (NULL, '".mysql_escape($players)."', '".mysql_escape($classes)."', '".mysql_escape($map)."', '".mysql_escape($server)."', UNIX_TIMESTAMP());";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

//Check if a user exists in the database
function mysql_pu_userexists($qauth)
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT `qauth` FROM `".mysql_escape($mysql->db)."`.`users` WHERE qauth='".mysql_escape($qauth)."'";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);

	//Examine
	$result = mysql_fetch_assoc($result);
	if($result["qauth"] == $qauth)
	{
		$returnvalue = true;
	} else
	{
		$returnvalue = false;
	}

	//Return the value
	return $returnvalue;
}

//Get info on a user
function mysql_pu_userinfo($qauth)
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`users` WHERE qauth='".mysql_escape($auth)."'";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);

	//Take to array and return value
	$result = mysql_fetch_assoc($result);

	return $result;
}

function mysql_pu_addmessage($command, $message)
{
	global $mysql;

	//Build MySQL query
	$sql = "INSERT INTO `".mysql_escape($mysql->db)."`.`messages` (`command`, `message`) VALUES ('".mysql_escape($command)."', '".mysql_escape($message)."');";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_updatemessage($command, $message)
{
	global $mysql;

	//Build SQL query
	$sql = "UPDATE `".mysql_escape($mysql->db)."`.`messages` SET message='".mysql_escape($message)."' WHERE command='".mysql_escape($command)."';";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_getmessages()
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`messages`";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);

	//take the query to array and return it
	while(($returnvalue[] = mysql_fetch_assoc($result)) || array_pop($returnvalue));

	return $returnvalue;
}

function mysql_pu_banuser($qauth, $expires, $admin, $length, $reason)
{
	global $mysql;
	
	//Build MySQL query
	$sql = "INSERT INTO `".mysql_escape($mysql->db)."`.`bans` (`id`, `qauth`, `reason`, `expires`, `admin`, `length`, `date`) VALUES (NULL, '".mysql_escape($qauth)."', '".mysql_escape($reason)."', '".mysql_escape($expires)."', '".mysql_escape($admin)."', '".mysql_escape($length)."', UNIX_TIMESTAMP());";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_unbanuser($qauth)
{
	global $mysql;
	
	//Build the MySQL query
	$sql = "UPDATE `".mysql_escape($mysql->db)."`.`bans` SET `expires` = UNIX_TIMESTAMP() WHERE `bans`.`qauth` = \"".mysql_escape($qauth)."\"";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_user_bans($qauth)
{
	global $mysql;
	
	//Build the SQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`bans` WHERE `bans`.`qauth` = \"".mysql_escape($qauth)."\"";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);

	//Get results from query
	while(($returnvalue[] = mysql_fetch_assoc($result)) || array_pop($returnvalue));
	
	//Return the results
	return $returnvalue;
}

function mysql_pu_addwarn($qauth, $reason, $admin)
{
	global $mysql;
	//Build MySQL query
	$sql = "INSERT INTO `".mysql_escape($mysql->db)."`.`warns` (`id`, `qauth`, `reason`, `admin`, `date`) VALUES (NULL, '".mysql_escape($qauth)."', '".mysql_escape($reason)."', '".mysql_escape($admin)."', UNIX_TIMESTAMP());";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);	
	$warnid = mysql_insert_id($link); // <3
	mysql_close($link);
	return $warnid; // returns the ID of the last entry so it may be removed through irc !delwarn ID
}

function mysql_pu_getwarns($qauth)
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`warns` WHERE `warns`.`qauth` = '".mysql_escape($qauth)."'";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);
	
	//Get results from query
	while(($returnvalue[] = mysql_fetch_assoc($result)) || array_pop($returnvalue));	
	//Return the results
	return $returnvalue;
}

/*
function mysql_pu_getwarnlist($qauth) // obsolete, impleted right in MainBot, messy though and should possibly be here but i faild so it's there y'know
{
	global $mysql;
	
	// build SQL query to get the warn values
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`warns` WHERE `warns`.`qauth` = '".mysql_escape($qauth)."'";
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	
	while(($output[] = mysql_fetch_array($result)) || array_pop($output));

	mysql_close($link);
	return $output; // return, since there are possibly multiple lines and we cannot process them here
}
*/

function mysql_pu_updateskills($qauth, $scoutskill, $demoskill, $soldierskill, $medicskill)
{
	
	global $mysql;
	
	//Build MySQL query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$sql = "UPDATE `".mysql_escape($mysql->db)."`.`users` 
	SET skill_scout = '".mysql_real_escape_string($scoutskill)."', skill_demoman = '".mysql_real_escape_string($demoskill)."', skill_soldier = '".mysql_real_escape_string($soldierskill)."', skill_medic = '".mysql_real_escape_string($medicskill)."'
	WHERE qauth = '".mysql_escape($qauth)."'";

	//Execute the query
	mysql_query($sql, $link);	
	mysql_close($link);
	
}

function mysql_pu_delwarn($id)
{
		global $mysql;

	//Build MySQL query
	$sql = "DELETE FROM `".mysql_escape($mysql->db)."`.`warns` 
	WHERE `id` = '".mysql_escape($id)."' LIMIT 1";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);

}

function mysql_pu_getbans($qauth)
{
	global $mysql;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`warns` WHERE `bans`.`qauth` = '".mysql_escape($qauth)."'";
	
	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);
	
	//Get results from query
	while(($returnvalue[] = mysql_fetch_assoc($result)) || array_pop($returnvalue));
	
	//Return the results
	return $returnvalue;
}

function mysql_pu_delmessage($command)
{
	global $mysql;

	//Build MySQL query
	$sql = "DELETE FROM `".mysql_escape($mysql->db)."`.`messages` WHERE `messages`.`command` = '".mysql_escape($command)."' LIMIT 1";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_userlist()
{
	global $mysql;
	global $users;

	//Build MySQL query
	$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`users`";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	$result = mysql_query($sql, $link);
	mysql_close($link);

	//take the query to array and return it
	while(($returnvalue[] = mysql_fetch_assoc($result)) || array_pop($returnvalue));

	return $returnvalue;
}

function mysql_pu_nickupdate($qauth, $new_nick)
{
	global $mysql;
	global $users;

	//Build SQL query
	$sql = "UPDATE `".mysql_escape($mysql->db)."`.`users` SET `nick` = '".mysql_escape($new_nick)."' WHERE `users`.`qauth` = '".mysql_escape($qauth)."' LIMIT 1;";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

function mysql_pu_banupdate($nick)
{
	global $mysql;
	global $users;

	//Build SQL query
	$sql = "UPDATE `".mysql_escape($mysql->db)."`.`users` SET `banned` = '1' AND SET `banreason` = '".mysql_escape($banreason)."' WHERE `users`.`nick` = '".mysql_escape($nick)."' LIMIT 1;";

	//Execute the query
	$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
	mysql_query($sql, $link);
	mysql_close($link);
}

//Initiated when 12 players are added
function start_pickup_s1()
{
    global $irc;
    global $users;
    global $pickup;
    global $afk;
    global $pickupchannel;
    global $waitbot;

    if($pickup->player_count() == 12)
    {
        //Clear the afk list
        $afk->clear();

            //Get a list of users
            $userlist = array();
            $counter = 0;
            while($counter < 12)
            {
                    $userlist[$counter] = $pickup->player[$counter]->qauth;
                    $counter++;
            }

            print_r($userlist);

            //Check if any users are afk
            $counter1 = 0;
            while($counter1 < 12)
            {
                    $timetest = time() - $users->last_active($userlist[$counter1]);
                    echo "\n".$timetest."\n";
                    if($timetest > 600)
                    {
                            $afk->add_player($userlist[$counter1]);
                    }
                    $counter1++;
            }

            if(count($afk->afklist) > 0) // more than 1 afk?
            {
                    $count2 = count($afk->afklist); // gief exact count of afk players
                    $counter2 = 0; // random shit counter init to 0

                    //Generate the afk message
                    $afkmessage = "\x02\x0302,00The following players are currently marked as afk: \x02\x035,00";
                    while($counter2 < $count2) // as long as there are moar afk than the counter is big
                    {
                            $afkmessage = $afkmessage . $users->qauthtonick($afk->afklist[$counter2])." ";
                            $counter2++; 
                    }

                    //Send the message to channel
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, $afkmessage);
                    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00These players have 60 seconds to type !ready before they are removed from the pickup.");

                    $waitbot->StartWait(60, 1);
            } else
            {
                    start_pickup_s2(); // if none afk, start stage 2
            }
     }
}

//Initiated when wait bot replies
function start_pickup_s2()
{
	global $pickup;
	global $users;
	global $pickupchannel;
	global $waitbot;
	global $irc;

	//Highlight the players
	$warning = "\x02\x0302,00Pickup Starting: \x02";

	$counter = 0;
	while($counter < 12)
	{
		$warning .= $users->qauthtonick($pickup->player[$counter]->qauth)." ";
		$counter++;
	}

	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, $warning);
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Type !del if you can't play");

	//Start 20sec waitbot
	$waitbot->StartWait("15", 2);
}

function start_pickup_s3()
{
	global $pickup;
	global $users;
	global $pickupchannel;
	global $pickup;
	global $waitbot;
	global $servers;
	global $irc;
    global $ircinfo;

	//FUCK IT
	//LETS JUST CHOOSE ALL PLAYERS RANDOMLY

	//Generate player list
	$playerlist['scout'][0] = $pickup->classid_qauth("scout", 0);
	$playerlist['scout'][1] = $pickup->classid_qauth("scout", 1);
	$playerlist['scout'][2] = $pickup->classid_qauth("scout", 2);
	$playerlist['scout'][3] = $pickup->classid_qauth("scout", 3);

	$playerlist['soldier'][0] = $pickup->classid_qauth("soldier", 0);
	$playerlist['soldier'][1] = $pickup->classid_qauth("soldier", 1);
	$playerlist['soldier'][2] = $pickup->classid_qauth("soldier", 2);
	$playerlist['soldier'][3] = $pickup->classid_qauth("soldier", 3);

	$playerlist['demoman'][0] = $pickup->classid_qauth("demoman", 0);
	$playerlist['demoman'][1] = $pickup->classid_qauth("demoman", 1);

	$playerlist['medic'][0] = $pickup->classid_qauth("medic", 0);
	$playerlist['medic'][1] = $pickup->classid_qauth("medic", 1);

	print_r($pickup->player);
	print_r($playerlist);

	//Choose the teams
	$teams = choose_teams_random($playerlist);
	// $teams = choose_teams_balanced_draft($playerlist);
	$blu = $teams['blu'];
	$red = $teams['red'];

	//WOOT WE FINALLY FINISHED ALL THE FUCKING BOLLOCKS
	//HONESTLY, IF YOU'RE READING THIS, WAEBI, DOTFLOAT, ETC, GO FUCK YOURSELF :DDD*/

	//Now find the winning map
	$pickup->build_votes();
	$counter = 0;
	$count = count($pickup->votes);
	$map['votes'] = 0;
	$map['map'] = "";

	while($counter < $count)
	{
		if($map['votes'] < $pickup->votes[$counter]["votes"])
		{
			$map['votes'] = $pickup->votes[$counter]["votes"];
			$map['map'] = $pickup->votes[$counter]["map"];
		}
		$counter++;
	}

	if($map['map'] == "")
	{
		$mapmessage = "Nobody voted for a map, so you get play something interesting: ";
		$winningmap = "cp_bazillion_rc2";
	} else
	{
		$mapmessage = "";
		$winningmap = $map['map'];
	}

	//I'D LIKE TO THANK NO ONE AS EVERYONES ADVICE WAS COMPLETELY USELESS IN EVERY POSSIBLE WAY
	//FUCK YOU ALL
	//LOVE, ADAM
	//AT LEAST WITH MY HELP YOUR SPELLING WOULD HAVE BEEN BETTER


	//Find an empty server and change the map
	$counter = 1;
	$count = count($servers);
	while($counter <= $count)
	{
		//Check if server is on
		if($servers[$counter]->on == true)
		{
			//Check if server is empty
			if($servers[$counter]->is_empty() == true)
			{
						$serverid = $counter;
						$servers[$serverid]->changelevel($winningmap);
						$counter = $count + 1;
			}
		}
		$counter++;
	}

        //Set channel +m to prevent spam
        $irc->mode($pickupchannel, "+m", SMARTIRC_CRITICAL);

        if($serverid == false)
        {
            $irc->message(SMARTIRC_TYPE_CHANNEL, $ircinfo->pickupchan, "\x02\x0302,00Sorry, no empty servers could be found, please try again later");
        } else
        {
			//Throttle send speed so the bot doesn't get kicked for excess flood
			$irc->setSenddelay(2100);

            //Send Pickup info to channel
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Pickup is now starting!");
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0312,00BLU: \x02Medic(".$users->qauthtonick($blu['medic']).") Soldiers(".$users->qauthtonick($blu['soldier'][0]).", ".$users->qauthtonick($blu['soldier'][1])." ) Demo(".$users->qauthtonick($blu['demoman'])." ) Scouts(".$users->qauthtonick($blu['scout'][0]).", ".$users->qauthtonick($blu['scout'][1])." )");
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0304,00RED: \x02Medic(".$users->qauthtonick($red['medic']).") Soldiers(".$users->qauthtonick($red['soldier'][0]).", ".$users->qauthtonick($red['soldier'][1])." ) Demo(".$users->qauthtonick($red['demoman'])." ) Scouts(".$users->qauthtonick($red['scout'][0]).", ".$users->qauthtonick($red['scout'][1])." )");
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Map:\x02 ". $mapmessage. $winningmap);
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Server ".$serverid.":\x02 \x03".$servers[$serverid]->connect);
			$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$servers[$serverid]->message);
            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00ALL PLAYERS MUST JOIN MUMBLE! Type !mumble for the mumble server info");
            $irc->Message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Please wait while the bot PM's all players the connect info");

            //Inform other bots about this channels pickup starting
                $counter = 0;
                while($counter < 12)
                {
                        $players[$counter] = $pickup->player[$counter]->qauth;
                        $classes[$counter] = $pickup->player[$counter]->class;
                        $counter++;
                }

                $players = implode(" ", $players);
                $classes = implode(" ", $classes);

                //Now PM the other bots
                $irc->message(SMARTIRC_TYPE_QUERY, "PixelTF2Bot2", "PLAYERKILL brixton ".$ircinfo->pickupchan." ".$players);

            //Now PM all players with connect info
                $playerlist = $users->qauthtonick($blu['medic'])." ";
                $playerlist .= $users->qauthtonick($red['medic'])." ";
                $playerlist .= $users->qauthtonick($blu['soldier'][0])." ";
                $playerlist .= $users->qauthtonick($blu['soldier'][1])." ";
                $playerlist .= $users->qauthtonick($red['soldier'][0])." ";
                $playerlist .= $users->qauthtonick($red['soldier'][1])." ";
                $playerlist .= $users->qauthtonick($red['scout'][0])." ";
                $playerlist .= $users->qauthtonick($red['scout'][1])." ";
                $playerlist .= $users->qauthtonick($blu['scout'][0])." ";
                $playerlist .= $users->qauthtonick($blu['scout'][1])." ";
                $playerlist .= $users->qauthtonick($blu['demoman'])." ";
                $playerlist .= $users->qauthtonick($red['demoman']);

            pminfo($playerlist, $serverid);
            //system("screen -A -m -d -S pminfo php pminfo.php '".$playerlist."' ".$serverid);

            //Record info about this pickup to MySQL
            mysql_pu_addpickup($players, $classes, $winningmap, $serverid);

            //Get Pickup ID
            $thispickup = mysql_pu_lastpickup();
            $pickupid = $thispickup["id"];

            //Generate the players message to be sent to the channel
            $playersred = "RED: Medic(".$users->qauthtonick($red['medic']).") Soldiers(".$users->qauthtonick($red['soldier'][0]).", ".$users->qauthtonick($red['soldier'][1])." ) Demo(".$users->qauthtonick($red['demoman'])." ) Scouts(".$users->qauthtonick($red['scout'][0]).", ".$users->qauthtonick($red['scout'][1])." )";
            $playersblu = "BLU: Medic(".$users->qauthtonick($blu['medic']).") Soldiers(".$users->qauthtonick($blu['soldier'][0]).", ".$users->qauthtonick($blu['soldier'][1])." ) Demo(".$users->qauthtonick($blu['demoman'])." ) Scouts(".$users->qauthtonick($blu['scout'][0]).", ".$users->qauthtonick($blu['scout'][1])." )";

            //Start the 45min STV kill bot
            stvwait($serverid, $playersblu, $playersred, $pickupid, $winningmap);
            //system("screen -A -m -d -S STVServer-".$serverid." php stvwait.php ".$serverid. " ".$pickupid." '".$playersred."' '".$playersblu."'");
        }

        //Reset the channel
        $pickup->init();
        SetTopic();
		
		//Reset send delay
		$irc->message(SMARTIRC_TYPE_QUERY, $ircinfo->nick, "brixton SENDDELAYRESET");
		
	$irc->mode($pickupchannel, "-m");
}

function rupmessage()
{
	global $afk;
	global $irc;
	global $users;
	global $pickupchannel;
	
	//Send message to channel
	$message = "\x0302,00The following players need to type !ready or will be removed from this pickup: \x0304,00";
	
	//Put list on message
	$counter = 0;
	$count = count($afk->afklist);
	while($counter < $count)
	{
		$message = $message . $users->qauthtonick($afk->afklist[$counter]) . " ";
		$counter++;
	}
	
	//Send message to the channel
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, $message);
}

function choose_teams_random($playerlist)
{
	//Choose medics
	$medicn = rand(1, 100);
	if($medicn > 50)
	{
		$red['medic'] = $playerlist['medic'][0];
		$blu['medic'] = $playerlist['medic'][1];
	} else
	{
		$red['medic'] = $playerlist['medic'][1];
		$blu['medic'] = $playerlist['medic'][0];
	}

	//Choose demos
	$demon = rand(1,100);
	if($demon > 50)
	{
		$red['demoman'] = $playerlist['demoman'][0];
		$blu['demoman'] = $playerlist['demoman'][1];
	} else
	{
		$red['demoman'] = $playerlist['demoman'][1];
		$blu['demoman'] = $playerlist['demoman'][0];
	}

	//Choose scouts
		//Shuffle the scout array
		$scouts = $playerlist['scout'];
		shuffle($scouts);

		//Assign to teams
		$red['scout'][0] = $scouts[0];
		$red['scout'][1] = $scouts[1];
		$blu['scout'][0] = $scouts[2];
		$blu['scout'][1] = $scouts[3];

	//Choose Soldiers
		//Shuffle the soldier array
		$soldiers = $playerlist['soldier'];
		shuffle($soldiers);

		//Assign to teams
		$red['soldier'][0] = $soldiers[0];
		$red['soldier'][1] = $soldiers[1];
		$blu['soldier'][0] = $soldiers[2];
		$blu['soldier'][1] = $soldiers[3];

	//Get final value to return
	$returnvalue['red'] = $red;
	$returnvalue['blu'] = $blu;

	//And return the value
	return $returnvalue;
}

/*function choose_teams_balanced($playerlist)
{
	//Bring in users class for skill
	global $users;

	//Split players into class arrays
	$counter = 0;	while($counter < 4)	{	$scouts[]['qauth'] = $playerlist['scout'];			$counter++;	}
	$counter = 0;	while($counter < 4)	{	$soldiers[]['qauth'] = $playerlist['soldier'];	$counter++;	}
	$counter = 0;	while($counter < 2)	{	$demos[]['qauth'] = $playerlist['demoman'];			$counter++;	}
	$counter = 0;	while($counter < 2)	{	$medics[]['qauth'] = $playerlist['medic'];			$counter++;	}
	
	
	//Get skill ratings for all players' respective classes
	$scouts[0]['skill'] = $users->get_skill($scouts[0]['qauth'], "scout");
	$scouts[1]['skill'] = $users->get_skill($scouts[1]['qauth'], "scout");
	$scouts[2]['skill'] = $users->get_skill($scouts[2]['qauth'], "scout");
	$scouts[3]['skill'] = $users->get_skill($scouts[3]['qauth'], "scout");
	
	$demos[0]['skill'] = $users->get_skill($demos[0]['qauth'], "demoman");
	$demos[1]['skill'] = $users->get_skill($demos[1]['qauth'], "demoman");
	
	$soldiers[0]['skill'] = $users->get_skill($soldiers[0]['qauth'], "soldier");
	$soldiers[1]['skill'] = $users->get_skill($soldiers[1]['qauth'], "soldier");
	$soldiers[2]['skill'] = $users->get_skill($soldiers[2]['qauth'], "soldier");
	$soldiers[3]['skill'] = $users->get_skill($soldiers[3]['qauth'], "soldier");
	
	$medics[0]['skill'] = $users->get_skill($medics[0]['qauth'], "medic");
	$medics[1]['skill'] = $users->get_skill($medics[1]['qauth'], "medic");
		
		
	// insert code here
	
	
	
[01:34:46] <link_> Problem:
[01:34:46] <link_> Divide n integers into two even groups s.t. the difference of their respective sums is minimized. 
[01:34:47] <link_> Preliminaries:
[01:34:47] <link_> Let n_i denote the integer of order i and S equal Sum(n,i)/2. Then the S'
[01:34:56] <link_> that is the problem we're facing
[01:35:46] <link_> lets say we have a total skill S and a medic skills m1 and m2
[01:58:03] <link_> Lets say you assign each player a number then the best way of picking a team is whatever yields the highest skill total within the half of the total skill limit. Lets say we have n players to choose from at start. Then the highst skill is max(n's skill + the skill yielded by picking from the other n-1, just the skill yielded by picking from the other n-1). We have to take into account how many we have picked
[01:58:03] <link_>  as well. For instance if we're picking from 3 and we've only picked 2 we return -1 to signal an error to show that it is not a viable solution. You make a large table with (persons picked, skill, number to pick from) and build that from just one player to many through that max formulation.
[01:58:25] <link_> and then you backtrack in it to find the solution
[02:02:06] <link_> oh, and you need to return -1 when the skill exceeds the limit as well
		
		
		// 
		
[02:23:14] <link_> how about this:
[02:23:15] <link_> Problem:
[02:23:16] <link_> Divide n integers into two even groups s.t. the difference of their respective sums is minimized. 
[02:23:16] <link_> Preliminaries:
[02:23:16] <link_> Let n_i denote the integer of order i. For all i,j,k calculate S(i,j,k) = max(n_i + S(i-1,j-n_i, k-1), S(i-1,j, k)). I denotes a choice from integers in the range n_1:n_i, j is how much more can be added to the sum before it reaches it's upper limit, k is the number of integers remaining to be picked. S(i,j,0) = 0. S(i,j,k) = -1 if 
[02:23:18] <link_> * i<k
[02:23:20] <link_> * j <0
[02:23:23] <link_> * k < 0


		http://en.wikipedia.org/wiki/Knapsack_problem
		http://en.wikipedia.org/wiki/Linear_programming
		
		
		1 medic
		1 demo
		2 scouts
		2 sollies
		=~
		the same
		
		
		// move classes around before this, then assign


	$red['medic'] = $medics[0];
	$blu['medic'] = $medics[1];
	
	$red['soldier'][0] = $soldiers[0];
	$red['soldier'][1] = $soldiers[1];
	
	$blu['soldier'][0] = $soldiers[2];
	$blu['soldier'][1] = $soldiers[3];

	$red['demoman'] = $demomen[0];
	$blu['demoman'] = $demomen[1]



	
	//Get final value to return
	$returnvalue['red'] = $red;
	$returnvalue['blu'] = $blu;

	//And return the value
	return $returnvalue;
	
}*/

function choose_teams_balanced_draft($playerlist)
{
//Bring in users class for skill
global $users;
global $irc;

//Split players into class arrays
/*
$counter = 0;while($counter < 4){$scouts[] = $playerlist['scout'];$scouts[]['qauth'] = $playerlist['scout'];$counter++;}
$counter = 0;while($counter < 4){$soldiers[] = $playerlist['soldier'];$soldiers[]['qauth'] = $playerlist['soldier'];$counter++;}
$counter = 0;while($counter < 2){$demos[] = $playerlist['demoman'];$demos[]['qauth'] = $playerlist['demoman'];$counter++;}
$counter = 0;while($counter < 2){$medics[] = $playerlist['medic'];$medics[]['qauth'] = $playerlist['medic'];$counter++;}
*/
/*
$counter = 0;while($counter < 4){$scouts[]['qauth'] = $playerlist['scout'];$counter++;}
$counter = 0;while($counter < 4){$soldiers[]['qauth'] = $playerlist['soldier'];$counter++;}
$counter = 0;while($counter < 4){$demos[]['qauth'] = $playerlist['demoman'];$counter++;}
$counter = 0;while($counter < 4){$medics[]['qauth'] = $playerlist['medic'];$counter++;}
*/

$medics = $playerlist['medic'];
$demos = $playerlist['demoman'];
$soldiers = $playerlist['soldier'];
$scouts = $playerlist['scout'];

//Split players into class arrays
$counter = 0;while($counter < 4){$scouts[$counter]['qauth'] = $playerlist['scout'];$counter++;}
$counter = 0;while($counter < 4){$soldiers[$counter]['qauth'] = $playerlist['soldier'];$counter++;}
$counter = 0;while($counter < 2){$demos[$counter]['qauth'] = $playerlist['demoman'];$counter++;}
$counter = 0;while($counter < 2){$medics[$counter]['qauth'] = $playerlist['medic'];$counter++;}


//Get skill ratings for all players' respective classes
$scouts[0]['skill'] = $users->get_skill($scouts[0]['qauth'], "scout");
$scouts[1]['skill'] = $users->get_skill($scouts[1]['qauth'], "scout");
$scouts[2]['skill'] = $users->get_skill($scouts[2]['qauth'], "scout");
$scouts[3]['skill'] = $users->get_skill($scouts[3]['qauth'], "scout");

$demos[0]['skill'] = $users->get_skill($demos[0]['qauth'], "demoman");
$demos[1]['skill'] = $users->get_skill($demos[1]['qauth'], "demoman");

$soldiers[0]['skill'] = $users->get_skill($soldiers[0]['qauth'], "soldier");
$soldiers[1]['skill'] = $users->get_skill($soldiers[1]['qauth'], "soldier");
$soldiers[2]['skill'] = $users->get_skill($soldiers[2]['qauth'], "soldier");
$soldiers[3]['skill'] = $users->get_skill($soldiers[3]['qauth'], "soldier");

$medics[0]['skill'] = $users->get_skill($medics[0]['qauth'], "medic");
$medics[1]['skill'] = $users->get_skill($medics[1]['qauth'], "medic");

	$skillshit = fopen('skillshit.txt', 'w+');
	fwrite($skillshit, $scouts[0]."\n");

// insert balancing code here

	if($medics[0]['skill'] >= $medics[1]['skill'])
	{ 
		// find best medic
		$medicBest = $medics[1]; // the best medic is medics[1] since his skill is "lower" than medics[0]
		$medicWorst = $medics[0];
	} 
	else 
	{
		$medicBest = $medics[0]; // ^ except the other way around
		$medicWorst = $medics[1];
	}
		
	if($demos[0]['skill'] >= $demos[1]['skill'])
	{ 
		// find best demo
		$demoBest = $demos[1]; // the best demo is demos[1] since his skill is "lower" than demos[0]
		$demoWorst = $demos[0];
	} 
	else 
	{
		$demoBest = $demos[0]; // ^ except the other way around
		$demoWorst = $demos[1];
	}	
		
// $medicWorst = max($medics[0]['skill'], $medics[1]['skill']);
// $medicBest = min($medics[0]['skill'], $medics[1]['skill']);

	$medicn = rand(1, 100); // randomly assigns each of the medics to a team
		if($medicn > 50)
		{
			$red['medic'] = $medicBest;
			$blu['medic'] = $medicWorst;
			
		$redskill = ${$medicBest}['skill'];
		$bluskill = ${$medicWorst}['skill'];

		} 
		else 
		{
			$red['medic'] = $medicWorst;
			$blu['medic'] = $medicBest;
		
		$redskill = ${$medicWorst}['skill'];
		$bluskill = ${$medicBest}['skill'];	
		}

	if($redskill >= $bluskill)
	{
			$red['demoman'] = $demoBest;
			$blu['demoman'] = $demoWorst;
		
		$redskill = $redskill + ${$demoBest}['skill'];
		$bluskill = $bluskill + ${$demoWorst}['skill'];
	} 
	else 
	{
			$red['demoman'] = $demoWorst;
			$blu['demoman'] = $demoBest;
			
		$redskill = $redskill + ${$demoWorst}['skill'];
		$bluskill = $bluskill + ${$demoBest}['skill'];
	}
	
	
	// the following code is ugly as fuck and probably not the most efficient, but I couldn't come up with another way of doing this so.. here we are.
	
	$findbestScout = min($scouts[0]['skill'], $scouts[1]['skill'], $scouts[2]['skill'], $scouts[3]['skill']);
		
		$i=0;
		$tmpScoutList = array();
		
		while($i <= 3){
			if($findbestScout == $scouts[$i]['skill']){
				if(isset($bestScout))
				{ // just in case there were two scouts with the same skill
					$tmpScoutList[] = $bestScout;
					$bestScout = $scouts[$i];
				}
				$bestScout = $scouts[$i]; // picks the best scout based on his "lower" skill
				$bestScoutNr = $i; // probably not gonna be needed
			} 
			else 
			{
				$tmpScoutList[] = $scouts[$i]; // save the fat kids for later
			}
			$i++;
		}
		
	$findsecondScout = min($tmpScoutList[0]['skill'], $tmpScoutList[1]['skill'], $tmpScoutList[2]['skill']); // fetching the second best scout
		$x=0;
		$tmpScoutLeft = array();
		
	
		while($x <= 2){
			if($findsecondScout == $tmpScoutList[$x]['skill'])
			{
				if(isset($secondScout))
				{ // just in case there were two scouts with the same skill
					$tmpScoutLeft[] = $secondScout;
					$secondScout = $tmpScoutList[$x];
				}
				
				$secondScout = $tmpScoutList[$x]; // picks the second best scout
				$secondScoutNr = $x; // probably not gonna be needed
			} 
			else 
			{
				$tmpScoutLeft[] = $tmpScoutList[$x];
			}
			$x++;
		}
		
		if($redskill >= $bluskill){
			$red['scout'][0] = $bestScout;
			$blu['scout'][0] = $secondScout;
			
			$redskill = $redskill + $bestScout['skill'];
			$bluskill = $bluskill + $secondScout['skill'];
		} 
		else 
		{
			$red['scout'][0] = $secondScout;
			$blu['scout'][0] = $bestScout;
			
			$redskill = $redskill + $secondScout['skill'];
			$bluskill = $bluskill + $bestScout['skill'];
		}
		
	$findthirdScout = min($tmpScoutLeft[0]['skill'], $tmpScoutLeft[1]['skill']);
		$y=0;
		$lastScout = array();
		
		if($findthirdScout == $tmpScoutLeft[0]['skill']){
			$thirdScout = $tmpScoutLeft[0]['skill'];
			$fourthScout = $tmpScoutLeft[1]['skill'];
		} 
		else 
		{
			$thirdScout = $tmpScoutLeft[1]['skill'];
			$fourthScout = $tmpScoutLeft[0]['skill'];
		}
			
		
		if($redskill >= $bluskill){
			$red['scout'][1] = $thirdScout;
			$blu['scout'][1] = $fourthScout;
			
			$redskill = $redskill + $thirdScout['skill'];
			$bluskill = $bluskill + $fourthScout['skill'];
			
		} 
		else 
		{
			$red['scout'][1] = $fourthScout;
			$blu['scout'][1] = $thirdScout;
			
			$redskill = $redskill + $fourthScout['skill'];
			$bluskill = $bluskill + $thirdScout['skill'];
			
		}
		
		$findbestSoldier = min($soldiers[0]['skill'], $soldiers[1]['skill'], $soldiers[2]['skill'], $soldiers[3]['skill']);
		
		$i=0;
		$tmpSoldierList = array();
		
		while($i <= 3)
		{
			if($findbestSoldier == $soldiers[$i]['skill'])
			{
				if(isset($bestSoldier))
				{ // just in case there were two soldiers with the same skill
					$tmpSoldierList[] = $bestSoldier;
					$bestSoldier = $soldiers[$i];
				}
				
				$bestSoldier = $soldiers[$i]; // picks the best soldier based on his "lower" skill
				$bestSoldierNr = $i; // probably not gonna be needed
			} 
			else 
			{
				$tmpSoldierList[] = $soldiers[$i]; // save the fat kids for later
			}
			$i++;
		}
		
	$findsecondSoldier = min($tmpSoldierList[0]['skill'], $tmpSoldierList[1]['skill'], $tmpSoldierList[2]['skill']); // fetching the second best soldier
		$x=0;
		$tmpSoldierLeft = array();
		
	
		while($x <= 2){
			if($findsecondSoldier == $tmpSoldierList[$x]['skill'])
			{
				if(isset($secondSoldier))
				{ // just in case there were two soldiers with the same skill
					$tmpSoldierLeft[] = $secondSoldier;
					$secondSoldier = $tmpSoldierList[$x];
				}
				
				$secondSoldier = $tmpSoldierList[$x]; // picks the second best soldier
				$secondSoldierNr = $x; // probably not gonna be needed
			} 
			else 
			{
				$tmpSoldierLeft[] = $tmpSoldierList[$x];
			}
			$x++;
		}
		
		if($redskill >= $bluskill)
		{
			$red['soldier'][0] = $bestSoldier;
			$blu['soldier'][0] = $secondSoldier;
			
			$redskill = $redskill + $bestSoldier['skill'];
			$bluskill = $bluskill + $secondSoldier['skill'];
		} 
		else 
		{
			$red['soldier'][0] = $secondSoldier;
			$blu['soldier'][0] = $bestSoldier;
			
			$redskill = $redskill + $secondSoldier['skill'];
			$bluskill = $bluskill + $bestSoldier['skill'];
		}
		
	$findthirdSoldier = min($tmpSoldierLeft[0]['skill'], $tmpSoldierLeft[1]['skill']);
		$y=0;
		$lastSoldier = array();
		
		if($findthirdSoldier == $tmpSoldierLeft[0]['skill'])
		{
			$thirdSoldier = $tmpSoldierLeft[0]['skill'];
			$fourthSoldier = $tmpSoldierLeft[1]['skill'];
		} 
		else 
		{
			$thirdSoldier = $tmpSoldierLeft[1]['skill'];
			$fourthSoldier = $tmpSoldierLeft[0]['skill'];
		}
			
		
		if($redskill >= $bluskill){
			$red['soldier'][1] = $thirdSoldier;
			$blu['soldier'][1] = $fourthSoldier;
			
			$redskill = $redskill + $thirdSoldier['skill'];
			$bluskill = $bluskill + $fourthSoldier['skill'];
			
		} 
		else 
		{
			$red['soldier'][1] = $fourthSoldier;
			$blu['soldier'][1] = $thirdSoldier;
			
			$redskill = $redskill + $fourthSoldier['skill'];
			$bluskill = $bluskill + $thirdSoldier['skill'];
			
		}
		
// lolp
		print($bluskill);
		print($redskill);

		$skillshit = fopen('skillshit.txt', 'w+');
		fwrite($skillshit, $bluskill."\n");
		fwrite($skillshit, $redskill);
		fclose($skillshit);
		
// and then return the list like shown in the "random" section
// ....


$returnvalue['red'] = $red;
$returnvalue['blu'] = $blu;
return $returnvalue;

}

function TopicName($qauth)
{
    global $users;
    if($qauth == "")
    {
        $returnvalue = "x";
    } else
    {
        $returnvalue = $users->qauthtonick($qauth);
    }

    //Return final value
    return $returnvalue;
}

function SetTopic()
{
    global $statusinterp;
    global $pickup;
    global $irc;
    global $pickupchannel;
    global $TopicBot;
    global $eventstack;

    //Check the eventstack
    if($eventstack->CheckEvent() == true)
    {
        //Check interp setting
        if($statusinterp == 0)
        {
            $TopicBot->SetTopic($pickup->title());
            $statusinterp = 1;
        } else
        {
            $TopicBot->SetTopic("\x02\x02".$pickup->title());
            $statusinterp = 0;
        }
    }
}

?>
