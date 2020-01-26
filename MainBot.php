<?php

//Pixel Gaming Main Pickup Bot
class PickupBot
{
	//Reload maps
	function reloadmaps(&$irc, &$data)
	{
		global $maps;
		global $users;
		
		if($users->is_su_admin($data->nick))
		{
			if(strtolower(substr($data->message, 0, 11)) == "!reloadmaps")
			{
				//Reload Server Config
				
				$maps->init();
				reloadmapsss();
				
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x02\x0302Map List Successfully Reloaded: ".$maps->getmapcount()." now in rotation");
			}
		}
	}

	//Rehash
	function Rehash(&$irc, &$data)
	{
		global $users;
		
		if($users->is_su_admin($data->nick))
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
	}
	
	function senddelay(&$irc, &$data)
	{
		if($data->message == "brixton SENDDELAYRESET")
		{
			$irc->setSenddelay(250);
		}
	}

	//The query report function
	function report(&$irc, &$data)
	{
		global $adminchannel;
		global $users;
		$input = explode(" ", $data->message);

		//Report command - used by normal players to report another player to the admins
		if(strtolower(substr($data->message, 0, 7)) == "!report")
		{
			//If a nick was entered
			$input = explode(" ", $data->message);
			if($input[1] != "")
			{
				//If the user is in the channel
				if($users->is_added($users->nicktoqauth($input[1])) == true)
				{
					$reasoncheck = $input[0] . " " . $input[1];
					$reason = str_replace($reasoncheck, "", $data->message);
					if($reason != "")
					{
						//Tell user the report is successful
						$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x0302,00Report: The user has been successfully reported to the admins");
						//Tell the admins in the admin channel
						$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02\x0304Report:\x02\x03 ".$data->nick." reported ".$input[1]." (via PM) for:".$reason);
						
						//Record in MySQL
						mysql_pu_addreport($data->nick, $input[1], $reason);
					} else
					{
						$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x0302,00Report: Please enter a reason when attempting to report another player");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x0302,00Report: The person you reported is not invited to this pickup, please double-check you entered the name correctly");
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "\x0302,00Report: !report <nick> <reason>");
			}
		}
	}

	//The waitbot reply
	function waitbot(&$irc, &$data)
	{
		global $afk;
		global $pickupchannel;
		global $pickup;
		global $waitbot;
		global $users;

		if($data->message == $waitbot->key)
		{
			//Mark waitbot as dead
			$waitbot->EndWait();

			//If waitbot is type 2 (15 sec !del period)
			if($waitbot->type == 2)
			{
				if($pickup->add_count() == 12)
				{
					start_pickup_s3();
				} else
                                {
                                    $waitbot->killbot();
                                    $afk->clear();
                                }
			}

			//If waitbot is type 1 (60 sec !ready period)
			if($waitbot->type == 1)
			{
                            if($pickup->add_count() == 12)
                            {
				//Check if anyone is still afk
				if(count($afk->afklist) > 0)
				{
					//if so, remove the afk players
					$counter = 0;
					$count = count($afk->afklist);
					$removelist = "";
					while($counter < $count)
					{
						$pickup->rm_player($afk->afklist[$counter]);
						$removelist = $removelist . $users->qauthtonick($afk->afklist[$counter]) . " ";
						$counter++;
					}

					$irc->message(SMARTIRC_TYPE_QUERY, "Q", $pickup->title());
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00The following users have been removed from the pickup:");
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$removelist);
				} else
				{
					//Run pickup warn
					start_pickup_s2();
				}
                            } else
                            {
                                $waitbot->killbot();
                                $afk->clear();
                            }
			}
		}
	}

	//A user has typed something
	function user_active(&$irc, &$data)
	{
		global $users;
		global $pickupchannel;
        global $pickup;

		//Remind the user they are added
		if((time() - $users->last_active($users->nicktoqauth($data->nick))) > 360)
		{
		    if($pickup->is_added($users->nicktoqauth($data->nick)))
		    {
			    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": Remember you are added to this pickup. If you can't play type !del");
			}
		}

		$users->user_active($data->nick);
	}

	//Invite check
	function invite_query(&$irc, &$data)
	{
		global $invites;
		global $users;
		global $pickupchannel;

		$info = $invites->info($data->nick);

		//If the invite is at this stage
		if($info->stage == 3)
		{
			if(strtolower($data->message) == "!accept")
			{
				//Add user
				$users->add_user($info->qauth, $info->nick, $info->invitedby);

				//Unban the player
				$irc->message(SMARTIRC_TYPE_QUERY, "Q", "unban ".$pickupchannel." *!*@".$users->nicktoqauth($data->nick).".users.quakenet.org");

				//Inform user via query
				$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "Congratulations! You have been successfully added to the invite list!");
				$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "To join the channel, type /join #pixeltf2.pickup");

				//And delete the invite from queue
				$invites->rm_invite($data->nick);
			}
		}
	}

	//whois print
	function whois_invite(&$irc, &$data)
	{
		global $invites;
		global $irc;
		global $pickupchannel;
		global $users;

		if($data->rawmessageex[1] == "311")
		{
			$info = $invites->info($data->rawmessageex[3]);

			if($data->rawmessageex[3] == $info->nick)
			{
				$invites->stage1($data->rawmessageex[3]);
			}
		}

		if($data->rawmessageex[1] == "318")
		{
			$info = $invites->info($data->rawmessageex[3]);

			if($data->rawmessageex[3] == $info->nick)
			{
				//Check if stage 2 was executed
				if($info->stage == 1)
				{
					//Tell channel the user is not authed with Q
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Invite: The user \"".$info->nick."\" is either not authed with Q or the wrong nick was entered");
					//And delete the invite
					$invites->rm_invite($data->rawmessageex[3]);
				} else
				{
					//Confirm stage 3
					$invites->stage3($data->rawmessageex[3]);
					//And send the user a query
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00An invite has been sent to: ".$info->nick);
					$irc->message(SMARTIRC_TYPE_QUERY, $info->nick, "Hello, you have been invited to the TF2 pickup channel #pixeltf2.pickup by ".$users->qauthtonick($info->invitedby));
					$irc->message(SMARTIRC_TYPE_QUERY, $info->nick, "Before you can play here, you must agree to these rules: http://pickup.pixelgaming.eu/rules/");
					$irc->message(SMARTIRC_TYPE_QUERY, $info->nick, "If you wish to accept these rules, simply type !accept in this chat window, otherwise, type !decline");
					print_r($invites->invites);
				}
			}
		}
	}

	//Check user list on join
	function init_names(&$irc, &$data)
	{
		//Bring variables into scope
		global $users;
		global $PickupBot;
		global $queue;
                global $pickupchannel;

		$userlist = array();

		//Unregister name list hook
		$irc->unregisterActionhandler(SMARTIRC_TYPE_NAME, '', &$PickupBot, 'init_names');

		//Issue whois on names
		$counter = 0;
		$size = count($data->messageex);
		while($counter < $size)
		{
			$input = str_replace("@", "", $data->messageex[$counter]);
			$input = str_replace("+", "", $input);
			array_push($userlist, $input);
			$counter++;
		}

		//Add check commands to the Queue
		$counter = 0;
		while($counter < $size)
		{
			if($userlist[$counter] !== "Q")
			{
				$queue->add_item($userlist[$counter], "check");
				$irc->whois($userlist[$counter]);
			}
			$counter++;
		}

		$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Bot initialised!");
		$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "NOW WITH A NEW VANILLA FLAVOUR");

		init_hooks();
		DisablePickup();
	}

	function join(&$irc, &$data)
	{
		//Bring variables into scope
		global $pickupchannel;
        global $adminchannel;
		global $pickup;
		global $queue;
        global $chanjoin;
		global $ircinfo;

                //Check if this is the bot itself joining
                if($chanjoin < 3)
                {
                    //Check if this is the pickup channel
                    if($data->channel == $ircinfo->pickupchan)
                    {
                        //Join the bot and admin channel
                        $irc->join($ircinfo->adminchan, $ircinfo->adminchan_pass);
                        $irc->join($ircinfo->botchan, $ircinfo->botchan_pass);

                        //Names the pickup channel
                        $irc->names($ircinfo->pickupchan);
                    }

                    //Increment the channel join counter
                    $chanjoin++;
                } else
                {
                    //Get data to variables
                    $nick = $data->nick;

                    //Add user check to the queue
                    $queue->add_item($nick, "check");

                    //Issue whois
                    $irc->whois($nick);
                }
	}

	function OtherPickupStart(&$irc, &$data)
        {
            global $playerkillkey;
            global $pickupstatus;
            global $pickup;
            global $ircinfo;

            //Get Constituent Messages
            $input = explode(" ", $data->message);

            //Check key
            if($input[0] == "PLAYERKILL")
            {
                if($input[1] != $playerkillkey)
                {
                    $irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "lol bro, they see me trollin, they hatin'");
                } else
                {
                    //Check if a a pickup is active
                    if($pickupstatus == true)
                    {
                        //Get player list into an array (as QAUTHs)
                        $playerlist[0] = $input[3];
                        $playerlist[1] = $input[4];
                        $playerlist[2] = $input[5];
                        $playerlist[3] = $input[6];
                        $playerlist[4] = $input[7];
                        $playerlist[5] = $input[8];
                        $playerlist[6] = $input[9];
                        $playerlist[7] = $input[10];
                        $playerlist[8] = $input[11];
                        $playerlist[9] = $input[12];
                        $playerlist[10] = $input[13];
                        $playerlist[11] = $input[14];

                        //Now check if any of those players are added currently in our channel's pickup
                        $counter = 0;
                        $pcount = 0;
                        $playersadded = "";
                        while($counter < 12)
                        {
                            if($pickup->is_added($playerlist[$counter]) != false)
                            {
                                $playersadded .= $playerlist[$counter] . " ";
                                $pcount++;
                            }
                            $counter++;
                        }

                        //Remove any added to this pickup
                        $plist = explode(" ", $playersadded);
                        if($pcount > 0)
                        {
                            //Remove the players
                            $count = count($plist);
                            $counter = 0;
                            while($counter < $count)
                            {
                                $pickup->rm_player($plist[$counter]);
                                $counter++;
                            }

                            //Inform the channel
                            $irc->message(SMARTIRC_TYPE_CHANNEL, $ircinfo->pickupchan, "\x0302,00A pickup has started in the channel ".$input[2]." and the following players have been removed:");
                            $irc->message(SMARTIRC_TYPE_CHANNEL, $ircinfo->pickupchan, "\x0302,00".$playersadded);
                            SetTopic();
                        }
                    }
                }
            }
        }
	
	function nick_change(&$irc, &$data)
	{
		//Bring variables into scope
		global $pickup;
		global $users;
                global $pickupstatus;

		$users->nick_change($data->nick, $data->message);
		$pickup->nick_change($data->nick, $data->message);

		//Update topic
                if($pickup->is_added($users->nicktoqauth($data->nick)) == true)
                {
                    if($pickupstatus === true)
                    {
                        SetTopic();
                    }
                }
	}

	function kick(&$irc, &$data)
	{
		global $pickup;
		global $users;
		global $irc;
		global $pickupchannel;
		global $pickup;

		//Get data
		$nick = $data->rawmessageex[3];
		$qauth = $users->nicktoqauth($nick);

		//Remove from pickup
		if($pickup->is_added($qauth) == true)
		{
			$pickup->rm_player($qauth);
		}

		//Mark the user out the channel
		$users->mark_outchannel($qauth);

		//Update the topic
		if($pickupstatus === true)
		{
                    SetTopic();
		}
	}

	function leave(&$irc, &$data)
	{
		//Bring variables intro scope
		global $pickup;
		global $users;
		global $irc;
		global $pickupchannel;
		global $pickup;
		global $pickupstatus;

		//Get data
		$nick = $data->nick;
		$qauth = $users->nicktoqauth($nick);

		//Remove from pickup
		if($pickup->is_added($qauth) == true)
		{
			$pickup->rm_player($qauth);
                        SetTopic();
		}

		//Mark the user out the channel
		$users->mark_outchannel($qauth);
	}

	function quit(&$irc, &$data)
	{
		//Bring variables intro scope
		global $pickup;
		global $users;
		global $irc;
		global $pickupchannel;
		global $pickup;
		global $pickupstatus;

		//Get data
		$nick = $data->nick;
		$qauth = $users->nicktoqauth($nick);

		//Remove from pickup
		if($pickup->is_added($qauth))
		{
			$pickup->rm_player($qauth);
                        SetTopic();
		}

		//Mark the user out the channel
		$users->mark_outchannel($qauth);
	}

	function whois_queue(&$irc, &$data)
	{
		//Bring variables into scope
		global $pickupchannel;
		global $pickup;
		global $queue;
		global $users;
		global $invites;
		global $bans;

		//print_r($data);
		//print_r($queue);

		//Check for commands on queue
		if($data->rawmessageex[1] == "330")
		{
				if($queue->get_item($data->rawmessageex[3]) != false)
				{
					//Shift the command off the queue
					$queue_item = $queue->get_item($data->rawmessageex[3]);
					$qauth = $data->rawmessageex[4];

					//Take Queue info to variables
					$map = $queue_item["map"];
					$nick = $queue_item["nick"];
					$class = $queue_item["class"];

					//Queue command switch
					switch($queue_item["command"])
					{
						case "check":
							//Check if the user is on the invite list
							if($users->is_added($qauth) == true)
							{
								if($users->is_banned($qauth) == false)
								{
									//Update nick on users
									$users->nick_change_join($qauth, $nick);
									$users->mark_inchannel($qauth);
								} else
								{
									$irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$qauth.".users.quakenet.org 1m You have been banned from this channel: ".$bans->info($qauth)->reason);
								}
							} else
							{
								//Temp ban the user
								//$irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$qauth.".users.quakenet.org 1m This is an invite-only pickup.");
								NewUser($qauth, $nick);
							}
							break;
					}
				}

				//Invite queue check
				$info = $invites->info($data->rawmessageex[3]);

				if($info->nick == $data->rawmessageex[3])
				{
					if($users->is_added($data->rawmessageex[4]))
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00The user \"".$info->nick."\" is already added to this pickup channel");
					} else
					{
						//Add qauth as stage2
						$invites->stage2($info->nick, $data->rawmessageex[4], $data->rawmessageex[3]);
					}
				}
		}

		//No Such Nick reply from WHOIS
		if($data->rawmessageex[1] == "401")
		{
			$info = $invites->info($data->rawmessageex[3]);
			if($info->nick == $data->rawmessageex[3])
			{
				//Inform the channel that the user does not exist
				$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00No user with the nick \"".$info->nick."\" exists on quakenet. Please check again.");
				//And delete the invite
				$invites->rm_invite($data->rawmessageex[3]);
			}
		}
	}

	function whois_check(&$irc, &$data)
	{
		//Bring variables into scope
		global $pickupchannel;
		global $pickup;
		global $queue;
		global $users;

		//Check for commands on queue
		if($data->rawmessageex[1] == "330")
		{
			if($queue[$data->rawmessageex[3]][0]["nick"] == $data->rawmessageex[3])
			{
				//Get qauth and nick
				$nick = $data->rawmessageex[3];
				$qauth = $data->rawmessageex[4];

				//Check the queue
				if($users->is_banned($qauth))
				{
					if($queue[$data->rawmessageex[3]][0]["command"] == "check")
					{
						//Check if the user is in the database
						if($users->is_added($qauth) == false)
						{
							$irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$qauth.".users.quakenet.org 1m This is an invite-only pickup.");
							//$irc->kick($pickupchannel, $nick, "This is an invite-only pickup.");
							//Remove command
							array_shift($queue[$nick]);
						} else
						{
							if($users->is_banned($qauth) === false)
							{
								$users->nick_change_join($qauth, $nick);
								$users->mark_inchannel($qauth);
								array_shift($queue[$nick]);
							} else
							{
								$irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$qauth.".users.quakenet.org 1m This is an invite-only pickup.");
								array_shift($queue[$nick]);
							}
						}
					}
				}
			}
		}
	}

	function ChannelSay(&$irc, &$data)
	{
		//Get the command from the input
		$input = strtolower($data->message);
		$input = str_replace("!", "", $input);
		$command = explode(" ", $input, 2);

		global $pickupchannel;
		global $pickup;
		global $queue;
		global $users;
		global $waitbot;
		global $afk;
		global $classflood;
                global $startstatus;

		$nick = $data->nick;

		switch($command[0])
		{
			//Admin remove user from pickup command
			case "adel":
				if($users->is_admin($data->nick))
				{
					//Get entered nick
					$user = explode(" ", $data->message);
					$user = $user[1];
					
					if($user != "")
					{
						$userq = $users->nicktoqauth($user);
						if($userq != false)
						{
							rm_player($userq);

							//Kill the pickup start process if this person was 12th
							if($waitbot->status == true)
							{
								if($waitbot->type > 0)
								{
									$waitbot->KillBot();
                                    global $afk;
								}
                                $afk->clear();
							}
						} else
						{
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->message, "\x0302,00".$data->nick.": The user you entered could not be found");
						}
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->message, "\x0302,00!adel: Admin Delete Command: !adel <nick>");
					}
				}
			break;
		
			//Add as Scout
			case "sc": case "sco": case "scout": case "scoot":
				if($pickup->count_class("scout") < 4)
				{
					$nick = $data->nick;
					$class = "scout";
					$map = $command[1];
					$qauth = $users->nicktoqauth($nick);

					//Check if this player has not hit flood

					//Perform a whois check if qauth is false
					if($qauth == false)
					{
						$queue->add_item($data->nick, "check");
						$irc->whois($data->nick);
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You have 2 logins with your Q account in this channel, if you definitely wish to add, type the command again");
					} else
					{

						//Add the player
						add_player($qauth, $nick, $class, 2 ,$map);

						//Check if the pickup is now full
						if($pickup->player_count() == 12)
						{
							start_pickup_s1();
						}
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$data->nick.", All of the scout spots are already filled. :(");
					//Check if the pickup is now full
                    /*if($pickup->player_count() == 12)
					{
						start_pickup_s1();
					}*/
				}
				break;

			//Add as Soldier
			case "sol": case "soldier": case "sold": case "soulja": case "soli": case "solli": case "solly":
				if($pickup->count_class("soldier") < 4)
				{
					$nick = $data->nick;
					$class = "soldier";
					$map = $command[1];
					$qauth = $users->nicktoqauth($nick);
					//Perform a whois check if qauth is false
					if($qauth == false)
					{
						$queue->add_item($data->nick, "check");
						$irc->whois($data->nick);
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You have 2 logins with your Q account in this channel, try to add again");
					} else
					{
						//Add the player
						add_player($qauth, $nick, $class, 2, $map);

						//Check if the pickup is now full
						if($pickup->player_count() == 12)
						{
							start_pickup_s1();
						}
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$data->nick.", All of the soldier spots are already filled. :(");
				}
				break;

			//Add as Demoman
			case "de": case "dem": case "demo": case "demoman": case "negro":
				if($pickup->count_class("demoman") < 2)
				{
					$nick = $data->nick;
					$class = "demoman";
					$map = $command[1];
					$qauth = $users->nicktoqauth($nick);

					//Perform a whois check if qauth is false
					if($qauth == false)
					{
						$queue->add_item($data->nick, "check");
						$irc->whois($data->nick);
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You have 2 logins with your Q account in this channel, try to add again");
					} else
					{
						//Add the player
						add_player($qauth, $nick, $class, 2, $map);

						//Check if the pickup is now full
						if($pickup->player_count() == 12)
						{
							start_pickup_s1();
						}
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$data->nick.", All of the demo spots are already filled. :(");
				}
				break;

			//Add as Medic
			case "med": case "medic": case "madic":
				if($pickup->count_class("medic") < 2)
				{
					$nick = $data->nick;
					$class = "medic";
					$map = $command[1];
					$qauth = $users->nicktoqauth($nick);

					//Perform a whois check if qauth is false
					if($qauth == false)
					{
						$queue->add_item($data->nick, "check");
						$irc->whois($data->nick);
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You have 2 logins with your Q account in this channel, try to add again");
					} else
					{
						//Add the player
						add_player($qauth, $nick, $class, 2, $map);

						//Check if the pickup is now full
						if($pickup->player_count() == 12)
						{
							start_pickup_s1();
						}
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$data->nick.", All of the medic spots are already filled. :(");
				}
				break;

			//Delete from the pickup
			case "del": case "delete": case "rem": case "remove":
				$nick = $data->nick;
				$qauth = $users->nicktoqauth($nick);
				rm_player($qauth);

				//Kill the pickup start process if this person was 12th
				if($waitbot->status == true)
				{
					if($waitbot->type > 0)
					{
						$waitbot->KillBot();
                        global $afk;
					}
				}
				
				$afk->clear();
				break;

			//Map Vote
			case "map": case "vote":
				$nick = $data->nick;
				$qauth = $users->nicktoqauth($nick);
				$map = $command[1];

                                //Check if the user was added
                                if($pickup->is_added($qauth) == true)
                                {
                                    //Change topic if this map was not already voted for
                                    if($pickup->mapvote($qauth, find_map($map)) != true)
                                    {
                                        SetTopic();
                                    }
                                }

				break;

			case "rdy": case "ready":
				if($waitbot->status == true)
				{
					if($waitbot->type == 1)
					{
						//Check if the user was marked as afk
						if($afk->is_afk($users->nicktoqauth($data->nick)))
						{
							$afk->rm_player($users->nicktoqauth($data->nick));
						}

						//Check if everyone has now readied up
						if(count($afk->afklist) == 0)
						{
							//Run straight to stage 2
							start_pickup_s2();
						} else
						{
							rupmessage();
						}
					}
				}
				break;

			case "afk":
				if($waitbot->status == false)
				{
					//Mark the user afk
					$users->mark_afk($users->nicktoqauth($data->nick));
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You have been marked as afk, simply type something in the channel to be marked as back.");
				} else
				{
				    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": You cannot mark yourself as afk during this time");
				}
		}
	}

	function info(&$irc, &$data)
	{
		//Get the command from the input
		$command = explode(" ", $data->message);
		$command[0] = strtolower($command[0]);
		$command[0] = str_replace("!", "", $command[0]);

		global $pickupchannel;
		global $pickup;
		global $queue;
		global $skill;
		global $messages;
		global $users;
		global $invites;
		global $messageflood;
		global $skillstack;

		$nick = $data->nick;

		//Check for set message command
		if($command[0] == "setmessage")
		{
			//Check if issuer was an admin
			if($users->is_admin($nick))
			{
				$new_command = strtolower($command[1]);
				$preload = "!setmessage ".$command[1]." ";
				$new_message = str_replace($preload, "", $data->message);

				if(isset($command[1]) || ($command[1] != ""))
				{
					if(isset($command[2]) || ($command[2] != ""))
					{
						//Check if it exists
						$counter1 = 0;
						$exists = false;
						while($counter1 < count($messages))
						{
							if($messages[$counter1]["command"] == $new_command)
							{
								$exists = $counter1;
								$counter1 = count($messages) + 1;
							}
							$counter1++;
						}

						if($exists === false)
						{
							$position = count($messages);
							$messages[$position]["command"] = $new_command;
							$messages[$position]["message"] = $new_message;
							//Add to MySQL
							mysql_pu_addmessage($new_command, $new_message);
							//Inform the channel
							$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00New message successfully added: !".$new_command);
						} else
						{
							$messages[$exists]["message"] = $new_message;
							//Update on MySQL
							mysql_pu_updatemessage($new_command, $new_message);
							//Inform the channel
							$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Existing message successfully updated: !".$new_command);
						}

						//Change input so the new command will be displayed
						$command[0] = $new_command;
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00No Message was entered");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00No command was entered");
				}
			}
		}

		//Check for delete message command
		if($command[0] == "delmessage")
		{
			//Check if issuer was an admin
			if($users->is_admin($nick))
			{
				$old_command = strtolower($command[1]);

				//Check if it exists
				$counter1 = 0;
				$exists = false;
				while($counter1 < count($messages))
				{
					if($messages[$counter1]["command"] == $old_command)
					{
						$exists = $counter1;
						$counter1 = count($messages) + 1;
					}
					$counter1++;
				}

				if($exists === false)
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00No such message exists: !".$old_command);
				} else
				{
					//Remove from the messages list
					$messages[$exists]["command"] = "";
					$messages[$exists]["message"] = "";

					//Check if the array should be re-oredered
					$counter2 = $exists + 1;
					$messages_size = count($messages);
					if($counter2 < $messages_size)
					{
						$counter3 = $exists;
						while($counter2 < $messages_size)
						{
							$messages[$counter3]["command"] = $messages[$counter2]["command"];
							$messages[$counter3]["message"] = $messages[$counter2]["message"];
							$messages[$counter2]["command"] = "";
							$messages[$counter2]["message"] = "";

							//Unset $counter2 element if it is the end of the array
							$unset = $counter2 + 1;
							if(($unset) == $messages_size)
							{
								unset($messages[$counter2]);
							}

							//Increment both counters
							$counter2++;
							$counter3++;
						}
					} else
					{
						//Unset current position
						unset($messages[$exists]);
					}
					//Remove in MySQL
					mysql_pu_delmessage($old_command);

					//Inform the channel
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Successfully deleted message: !".$old_command);
				}
			}
		}
		
		////////////////////////////////////////////////
		//This functionality has now been replaced by MessageBot (messagebot.php)
		
		//!skill command to let players see their skill rating in the bot
		/*if(strtolower($command[0]) == "skill")
		{
			if($command[1] == "")
			{
				$user = $nick;
				$user = $users->nicktoqauth($nick);
			} else
			{
				if($users->nicktoqauth($command[1]) != false)
				{
					$user = $users->nicktoqauth($command[1]);
				}
			}
			
			//Get the players skill as each class
			$scout = $users->Get_Skill($user, "scout");
			$demo = $users->get_skill($user, "demo");
			$soldier = $users->get_skill($user, "soldier");
			$medic = $users->get_skill($user, "medic");
			
			//Create output message
			$output = "\x0302,00\x02Skill:\x02 " . $users->qauthtonick($user) . " || Scout: ".$scout." / Demo: ".$demo." / Soldier: ".$soldier." / Medic: ".$medic;
			
			//Check spam class
			if($skillstack->trigger($user) == true)
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
			}
		}*/
		
		if(strtolower($command[0]) == "matches")
		{
			//Look up match info from web server
			$stats = file_get_contents("http://pickup.pixelgaming.eu/dynamic.php?job=stats");
			$stats = explode(":", $stats);
			$stats = $stats[0];
			
			//Generate output
			$output = "\x0302,00" . $stats . " matches have been played since 8th February 2011";
			
			//Check messageflood
			if((time() - $messageflood['matches']) > 20)
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
			}
			
			$messageflood['matches'] = time();
		}
		
		if(strtolower($command[0]) == "players")
		{
			//Look up match info from web server
			$stats = file_get_contents("http://pickup.pixelgaming.eu/dynamic.php?job=stats");
			$stats = explode(":", $stats);
			$stats = $stats[1];
			
			//Generate output
			$output = "\x0302,00There are currently " . $stats . " players invited to this channel";
			
			//Check messageflood
			if((time() - $messageflood['players']) > 20)
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $output);
			}
			
			$messageflood['players'] = time();
		}

		//Change command name for some commonly used commands
		switch($command[0])
		{
			case "mum": case "voice": case "vent": case "ventrilo": case "ventrillo":
				$command[0] = "mumble";
				break;

			case "req": case "reqs": case "requis": case "requi": case "requirements":
				$command[0] = "requirements";
				break;

			case "rule": case "rul": case "ruls":
				$command[0] = "rules";
				break;
		}

		//Check message array and display if it matches
		/*$counter = 0;
		while($counter < count($messages))
		{
			if($messages[$counter]["command"] == $command[0])
			{
				//Check messages anti flood
				if((time() - $messageflood[$command[0]]) > 15)
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00".ucfirst($command[0]).": ".$messages[$counter]["message"]);
					$messageflood[$command[0]] = time();
				}
			}
			$counter++;
		}*/
		////////////////////////////////////////////////
		//This functionality has now been replaced by MessageBot (messagebot.php)
	}

	function PlayerKicked(&$irc, &$data)
	{
		global $users;
		global $pickup;
		
		$kickeduser = $data->message;
		$by = $data->nick;
		
		//Remove from the Pickup if added
		if($pickup->is_added($users->nicktoqauth($kickeduser)) != false)
		{
			$pickup->rm_player($users->nicktoqauth($kickeduser));
			SetTopic();
		}
	}
	
	function KillBot(&$irc, &$data)
	{
		global $users;
		global $pickupstatus;
		global $pickup;
		global $pickupchannel;
		global $queue;
		global $invites;
		global $bans;
		global $adminchannel;
		global $rr;
        global $messageflood;
		global $servers;

		if($users->is_su_admin($data->nick))
		{
			if(substr($data->message, 0, 5) == "!kill")
			{
				$input = str_replace("!kill ", "", $data->message);
				if($input != "!kill")
				{
					DisablePickup($input);
				} else
				{
					DisablePickup();
				}
			}

			if(substr($data->message, 0, 7) == "!enable")
			{
				EnablePickup();
			}
			
			//Disable a server
			if(substr($data->message, 0, 8) == "!markoff")
			{
				$input = explode(" ", $data->message);
				if($input[1] != "")
				{
					//Mark the server off
					$servers[$input[1]]->on = false;
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Server ".$input[1]." successfully marked as off");
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!markoff <server id>");
				}
			}
			
			//Enable a server
			if(substr($data->message, 0, 7) == "!markon")
			{
				$input = explode(" ", $data->message);
				if($input[1] != "")
				{
					//Mark the server on
					$servers[$input[1]]->on = true;
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Server ".$input[1]." successfully marked as on");
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!markon <server id>");
				}
			}
			
			//Server state
			if(substr($data->message, 0, 9) == "!srvstate")
			{
				$input = explode(" ", $data->message);
				if($input[1] != "")
				{
					//Display state
					$state = $servers[$input[1]]->on;
					if($state == true)
					{
						$state = "on";
					} else
					{
						$state = "off";
					}
						
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Server ".$input[1]." is currently marked as: ".$state);
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!srvstate <server id>");
				}
			}

			if(substr($data->message, 0, 6) == "!pause")
			{
				$input = str_replace("!pause ", "", $data->message);
				if($input != "!pause")
				{
					PausePickup($input);
				} else
				{
					PausePickup();
				}
			}
			
			if(substr($data->message, 0, 9) == "!truekill")
			{
				//Kill the bots process
				die();
			}
		}

		if($users->is_admin($data->nick))
		{
			if($data->message == "!clearinvites")
			{
				$invites->invites = array();
				$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x03\x0302,00All invites have now been cleared");
			}
		}

		//status command
		if($pickupstatus === true)
		{
			if(strtolower(substr($data->message, 0, 7)) == "!status")
			{
                            //Check anti-flood
                            if($messageflood["status"] < time())
                            {
                                SetTopic();
                                $messageflood["status"] = time() + 30;
                            }
			}
		}

		//Invite command
		if(strtolower(substr($data->message, 0, 7)) == "!invite")
		{
                    //Check if the user is an admin
                    if($users->is_admin($data->nick))
                    {
			$input = explode(" ", $data->message);

			if($input[1] == "")
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Invite: To invite a user type the command \"!invite\", followed by the persons IRC nick. i.e. !invite Adam`. NOTE: The person must be authed with Q to be invited to the pickup");
			} else
			{
				//Check if the user is already added
				$qauth = $users->nicktoqauth($input[1]);
				if($qauth == false)
				{
					//Check if the person is already invited
					if($invites->info($input[1]) == false)
					{
						//Start the invite process
						$invites->startinvite($input[1], $users->nicktoqauth($data->nick));
						$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "PASTE THE FUCKING ETF2L PROFILE ID IN HERE YOU LAZY MONG: http://skyride.pixelgaming.eu/rateplayer.php");
						//Issue a whois on the user
						$irc->whois($input[1]);
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": This user has already been sent an invite");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00".$data->nick.": That person is already in the channel you dumb fuck ".$data->nick." :P");
				}
			}
                    } else
                    {
                        //Check the anti-flood
                        if($messageflood["invite"][$data->nick] < time())
                        {
                            $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Sorry ".$data->nick.", but only admins can invite people now. Use !requestinvite to ask an admin to invite this person");
                            $messageflood["invite"][$data->nick] = time() + 30;
                        }
                    }
		}

    //The Request invite command
    if(strtolower(substr($data->message, 0, 14) == "!requestinvite"))
    {
                    //Check the anti-flood
                    if($messageflood["requestinvite"][$data->nick] < time())
                    {
                        $input = explode(" ", $data->message);
                        //If no nick was entered
                        if($input[1] == "")
                        {
                            $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Request Invite: !requestinvite <IRC Nick> <ETF2L Profile Link>");
                        } else
                        {
                            //If no ETF2L profile was entered
                            if($input[2] == "")
                            {
                                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Request Invite: ".$data->nick.", you need to enter a link to the users ETF2L profile");
                                $messageflood["requestinvite"][$data->nick] = time() + 10;
                            } else
                            {
                                $messageflood["requestinvite"][$data->nick] = time() + 30;
                                //Send message to admin channel and confirm in main channel
                                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Request Invite: ".$data->nick.", An invite request has now been sent to the admins regarding this player");
                                $irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02\x0304Invite Request:\x02\x03 ".$data->nick." has request the player ".$input[1]." to be invited. ETF2L: ".$input[2]);
                            }
                        }
                    }
    }

		//Revoke Invite
		if(strtolower(substr($data->message, 0, 7)) == "!revoke")
		{
			if($users->is_admin($data->nick) === true)
			{
				//Check if a nick/qauth was entered
				$input = explode(" ", $data->message);
				if($input[1] == "")
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!revoke <nick/#qauth>");
				} else
				{
					//Check if it was a nick or a qauth
					if(substr($input[1], 0, 1) == "#")
					{
						//A qauth was entered
						
						//Check if qauth is on the invite list
						if($users->qauthtonick($input[1]) == false)
						{
							//Not found
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!revoke: The qauth entered could not be found");
						}
						{
							//Found
							$qauth = $users->qauthtonick(substr($input[1], 1));
							$qauth = $users->nicktoqauth($qauth);
							
							//Delete user from the invite list
							$users->del_user($qauth);
							
							//Kick user from the channel
							$irc->kick($pickupchannel, $users->qauthtonick($qauth));
							
							//Send message to channel telling that the user has been kicked
							$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Notice: The invite for \"".$users->qauthtonick($qauth)."\" http://www.youtube.com/watch?v=SiXNUaSjXRY&feature=player_detailpage#t=7s");
							
							//Send message to the admin kicking them
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00".$data->nick.": The invite for \"".$users->qauthtonick($qauth)."\" has been successfully revoked");

							//Get revoke reason
							array_shift($input);
							array_shift($input);
							
							//Send a short PM to the person who has been kicked
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "Sorry to say, but your invite to #pixeltf2.pickup has been revoked");
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "There are a number of possible reasons for this. If you'd like to discuss the issue, you can speak to \"".$data->nick."\" who is responsible for revoking your invite");
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "Reason: ".$input);
						}
					} else
					{
						//A nick was entered
						
						//Check if this user is on the invite list
						if($users->nicktoqauth($input[1]) == false)
						{
							//Not found
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00!revoke: The user entered could not be found");
						} else
						{
							$qauth = $users->nicktoqauth($qauth);
							
							//Delete user from the invite list
							$users->del_user($qauth);
							
							//Kick user from the channel
							$irc->kick($pickupchannel, $users->qauthtonick($qauth));
							
							//Send message to channel telling that the user has been kicked
							$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x0302,00Notice: The invite for \"".$users->qauthtonick($qauth)."\" http://www.youtube.com/watch?v=SiXNUaSjXRY&feature=player_detailpage#t=7s");
							
							//Send message to the admin kicking them
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00".$data->nick.": The invite for \"".$users->qauthtonick($qauth)."\" has been successfully revoked");

							//Get revoke reason
							array_shift($input);
							array_shift($input);
							
							//Send a short PM to the person who has been kicked
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "Sorry to say, but your invite to #pixeltf2.pickup has been revoked");
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "There are a number of possible reasons for this. If you'd like to discuss the issue, you can speak to \"".$data->nick."\" who is responsible for revoking your invite");
							$irc->message(SMARTIRC_TYPE_QUERY, $users->qauthtonick($qauth), "Reason: ".$input);
						}
					}
				}
			}
		}

		//Ban command
		if(strtolower(substr($data->message, 0, 4)) == "!ban")
		{
			if($users->is_admin($data->nick) === true)
			{
				$input = explode(" ", $data->message);
				//If a nick was entered
				if($input[1] == "")
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Ban: Type !ban <nick> <duration: m/h/d/w> <reason>");
				} else
				{
					//Check if player exists
					if($users->is_added($users->nicktoqauth($input[1])))
					{
						//If a duration was entered
						if(preg_match("/[0-9]{1,4}[mhdwMHDW]{1}/", $input[2]) == false)
						{
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Ban: You did not enter a duration");
						} else
						{
							//If a reason was entered
							$ReasonCheck = $input[0]." ".$input[1]." ".$input[2];
							$Reason = str_replace($ReasonCheck, "", $data->message);
							if($Reason != "")
							{
								//Get duration number and character
								preg_replace("/[0-9]{1,4}/", $input[2], $value);
								preg_replace("/[mhdwMHDW]{1}/", $input[2], $key);
								$key = strtolower($key);

								//Calculate ban expire time
								switch($key)
								{
									//Minutes
									case "m":
										$bantime = $value[0] * 60;
										break;

									//Hours
									case "h":
										$bantime = $value[0] * 60 * 60;
										break;

									//Days
									case "d":
										$bantime = $value[0] * 60 * 60 * 24;
										break;

									//Weeks
									case "w":
										$bantime = $value[0] * 60 * 60 * 24 * 7;
										break;
								}

								//Now add this value to the current time
								$expiretime = time() + $bantime;

								//Perform the ban
								$bans->add_ban($users->nicktoqauth($input[1]), $input[2], $expiretime, $users->nicktoqauth($data->nick), $reason);
								//Kick them from the channel
								$irc->kick($pickupchannel, $input[1]);

                                                                //PM Q to handle dem baaans
                                                                $irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$users->nicktoqauth($input[1]).".users.quakenet.org ".$input[2]." ".$Reason);

								//PM the unlucky fucker so they know what has happened
								$irc->message(SMARTIRC_TYPE_QUERY, $input[1], "You have been banned from the channel #pixeltf2.pickup for the following reason: ".$Reason);
								$irc->message(SMARTIRC_TYPE_QUERY, $input[1], "This ban will expire on: ".date("H:i - d/m/Y", $expiretime));

								//Tell the channel
								$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Ban: The user ".$input[1]." has been successfully banned");
							} else
							{
								$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Ban: You did not enter a ban reason");
							}
						}
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Ban: The user '".$input[1]."' was not found in our records");
					}
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "lol ".$data->nick." you cheeky fuck xD");
			}
		}

		//Unban command
		if(strtolower(substr($data->message, 0, 6)) == "!unban")
		{
			$input = explode(" ", $data->message);
			//If the issuer is an admin
			if($users->is_admin($data->nick))
			{
				//If a qauth was entered
				if($input[1] != "")
				{
					//If the person being unbanned is actually on the users list
					if($users->is_added($input[1]))
					{
						//If the user is actually banned
						if(count(mysql_pu_user_bans($input[1])) > 0)
						{
							//Perform the unban
							$bans->rm_ban($input[1]);

               //Undo the Q ban
               $irc->message(SMARTIRC_TYPE_QUERY, "Q", "UNBAN ".$pickupchannel." *!*@".$input[1].".users.quakenet.org");

							//let the channel know
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Unban: The user '".$input[1]."' has been successfully unbanned");
						} else
						{
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Unban: The user you entered is currently not banned.");
						}
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Unban: The user entered could not be found. Remember you need to enter a users QAuth when using the !unban command.");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Unban: !unban <qauth>");
				}
			}
		}

		//Nick Change Command
		if(strtolower(substr($data->message, 0, 11)) == "!nickchange")
		{
			if($users->is_su_admin($data->nick))
			{
				$input = explode(" ", $data->message);
				$newnick = $input[1];
				$irc->changeNick($newnick);
				//Change nick in MySQL
				$users->nick_change_join("PixelTF2Bot", $newnick);
			}
		}
		
		//Warn command
		if(strtolower(substr($data->message, 0, 5)) == "!warn")
		{
			$input = explode(" ", $data->message);
			//If the issuer is an admin
			if($users->is_admin($data->nick))
			{
				//If a user was actually entered
				if($input[1] != "")
				{
					//If the user actually exists
					if($users->nicktoqauth($input[1]) != false)
					{
						//Get the reason
						$reason = $input[0] . " " . $input[1];
						$reason = str_replace($reason, "", $data->message);

						//If the reason is valid
						if($reason != "")
						{
							//Add the warn to MySQL
							$warnid = mysql_pu_addwarn($users->nicktoqauth($input[1]), $reason, $users->nicktoqauth($data->nick));

							//Find out how many warns this user now has
							$warns = count(mysql_pu_getwarns($users->nicktoqauth($input[1])));
							//Tell the channel
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: ".$data->nick.": The warning has been successfully registered. This user now has ".$warns." warnings.");

							//Tell the admin channel
							$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02\x0304Warn:\x03\x02 ".$data->nick." has warned ".$input[1]." for:".$reason);
							$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "The last warning has the ID ".$warnid);

						} else
						{
							$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: Please enter a reason for the warning");
						}
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: The user you entered could not be found in the database");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: !warn <nick> <reason>");
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00lol ".$data->nick.", umad m8? Use !report to tell an admin (works both in the channel and in a query to the bot).");
			}
		}

		//Remove Warn command
		if(strtolower(substr($data->message, 0, 8)) == "!delwarn")
		{
			$id = explode(" ", $data->message);
			//If the issuer is an admin
			if($users->is_admin($data->nick))
			{
				//If an ID was actually entered
				if($id[1] != "")	
				{
					//Remove the warn from MySQL
					mysql_pu_delwarn($id[1]); // RIGHT SO IT ACTUALLY WORKS NOW
					//Tell the admin channel
					$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02\x0304Deleted Warning:\x03\x02 ".$id[1]);
				} 
				else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: Please enter a warning ID");
				}
			}
			else
			{ // do fuck all if it aint a badmin
			} 
		}
		
		//List current Warns command
		if(strtolower(substr($data->message, 0, 10)) == "!listwarns")
		{
			// define mysql shit so it can be used in this function
			global $mysql;
			$input = explode(" ", $data->message);
			//If the issuer is an admin
			if($users->is_admin($data->nick))
			{
				//If a user was actually entered
				if($input[1] != "")
				{
					//If the user actually exists
					if($users->nicktoqauth($input[1]) != false)			
					{
						//Find out how many warns this user now has
						$warns = count(mysql_pu_getwarns($users->nicktoqauth($input[1])));
						if($warns != 0)
						{
							// tell the admin channel the current number of warns
							$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x0302,00Listwarns: This user currently has ".$warns." warnings:");
							
							// shit query, same as getwarns() but wat.
							$sql = "SELECT * FROM `".mysql_escape($mysql->db)."`.`warns` WHERE `warns`.`qauth` = '".mysql_escape($users->nicktoqauth($input[1]))."'";
	
							//Execute the query
							$link = mysql_connect($mysql->host, $mysql->user, $mysql->pass);
							$result = mysql_query($sql, $link);

							// loop through dem rows yo
							while($warnlist = mysql_fetch_assoc($result)) 
							{
								// before: $irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02ID\x02: ".$warnlist['id']." - \x02Reason\x02: ".$warnlist['reason']." - \x02Admin\x02: ".$warnlist['admin'] );
								$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02ID\x02: ".$warnlist['id']." - \x02Reason\x02: ".$warnlist['reason']." - \x02Time\x02: ".date('jS M Y H:i:s',$warnlist['date']) );

							}
							// dno if that shit is needed yo, but surely it did not work last time i tried without or tried it differently 
							// so imma just let this be here yo.
							mysql_free_result($result);
							mysql_close($link);
							unset($sql);
							unset($result);
							unset($warnlist);
						}
						else
						{
							//Tell the admin channel there are no warnings
							$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x0302,00Listwarns: This user currently has no warnings.");
						}
					} 
					else
					{
						// tell the admin channel WELL FUCKS SAKE WHY DO I EVEN COMMENT IT, IT'S CLEAR HERE
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Listwarns: The user you entered could not be found in the database");
					}
				} 
				else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Listwarns: The user you entered could not be found in the database");
				}
			}
				else
				{ 
					// do fuck all, we want an oligarchy
				}
		}
		
		//Update skill command
		if(strtolower(substr($data->message, 0, 9)) == "!setskill")
		{
			$input = explode(" ", $data->message);
			//If the issuer is an admin
			if($users->is_admin($data->nick))
			{
				//If an nick was actually entered
				if($input[1] != "")	
				{
					//If the user actually exists
					if($users->nicktoqauth($input[1]) != false)
					{
						$qauth=$users->nicktoqauth($input[1]);
						//Update the skill level
						mysql_pu_updateskills($qauth, $input[2], $input[3], $input[4], $input[5]); // FUCKING MASSIVE WOO
						//Tell the admin channel
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "The skill levels for ".$input[1]." have been successfully updated.");
					} 
					else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Setskill: The entered user does not exist.");
					}
				}
				else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Warn: Please enter a name and the skill levels in the format <name> <scout> <demo> <soldier> <medic>.");
				}
			}
			else
			{ 
				// do fuck all if it aint a badmin
			} 
		}
		
		//Report command - used by normal players to report another player to the admins
		if(strtolower(substr($data->message, 0, 7)) == "!report")
		{
			//If a nick was entered
			$input = explode(" ", $data->message);
			if($input[1] != "")
			{
				//If the user is in the channel
				if($users->is_added($users->nicktoqauth($input[1])) == true)
				{
					$reasoncheck = $input[0] . " " . $input[1];
					$reason = str_replace($reasoncheck, "", $data->message);
					if($reason != "")
					{
						//Tell user the report is successful
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Report: The user has been successfully reported to the admins");
						//Tell the admins in the admin channel
						$irc->message(SMARTIRC_TYPE_CHANNEL, $adminchannel, "\x02\x0304Report:\x02\x03 ".$data->nick." reported ".$input[1]." for:".$reason);
					} else
					{
						$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Report: Please enter a reason when attempting to report another player");
					}
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Report: The person you reported is not invited to this pickup, please double-check you entered the name correctly");
				}
			} else
			{
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0302,00Report: !report <nick> <reason>");
			}
		}

		//Fake start command
		if(strtolower(substr($data->message, 0, 9)) == "!testfill")
		{
			//If is Super Admin
			if($users->is_su_admin($data->nick))
			{
				//Fill unused slots
				$count = 12;
				$counter = 1;
				while($counter < $count)
				{
					$users->user_active($counter);
					$counter++;
				}

				$pickup->add_player("1", "1", "scout", "3");
				$pickup->add_player("2", "2", "scout", "3");
				$pickup->add_player("3", "3", "scout", "3");
				$pickup->add_player("4", "4", "scout", "3");

				$pickup->add_player("5", "5", "medic", "3");
				$pickup->add_player("6", "6", "medic", "3");

				$pickup->add_player("7", "7", "soldier", "3");
				$pickup->add_player("8", "8", "soldier", "3");
				$pickup->add_player("9", "9", "soldier", "3");
				$pickup->add_player("10", "10", "soldier", "3");

				$pickup->add_player("11", "11", "demoman", "3");

                                SetTopic();

				start_pickup_s1();
			}
		}

		//Last command - display when the last pickup took place => Moved to messagebot.php
		/*if(strtolower(substr($data->message, 0, 6)) == "!last ")
		{
				global $lastantiflood;
				//anti-flood
				if((time() - $lastantiflood) > 15)
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
		}*/

		//Russian Roulette stuff
		//Fire command
		if(strtolower(substr($data->message, 0, 3)) == "!rr")
		{
			//If they last pulled the trigger
			if($rr->lastuser != $users->nicktoqauth($data->nick))
			{
				//If they were shot
				if($rr->fire() == true)
				{
					//1 min Q ban the user
					$irc->message(SMARTIRC_TYPE_QUERY, "Q", "TEMPBAN ".$pickupchannel." *!*@".$users->nicktoqauth($data->nick).".users.quakenet.org 1m You died...");
					$irc->message(SMARTIRC_TYPE_QUERY, $data->nick, "You died... See you in a minute ;)");
					$irc->message(SMARTIRC_TYPE_QUERY, $data->channel, "So, whos next? >:)");

					//Reset the game
					$rr->init();
				} else
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0312You got lucky this time ".$data->nick."...");
					//Set last user
					$rr->lastuser = $users->nicktoqauth($data->nick);
					$rr->lastusercount = 0;
				}
			} else
			{
				if($rr->lastusercount == 0)
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0312You might be keen, but you can't try your luck twice in a row ;)");
					$rr->lastusercount = 1;
				}
			}
		}

		//Spin command
		if(strtolower(substr($data->message, 0, 5)) == "!spin")
		{
			//If they last used the gun, they can't spin it
			if($rr->lastuser != $users->nicktoqauth($data->nick))
			{
				//Spin the barrel
				$rr->spin();
				$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0312".$data->nick." spun the barrel!");
			} else
			{
				if($rr->lastusercount == 0)
				{
					$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "\x0312You might be keen, but you can't try your luck twice in a row ;)");
					$rr->lastusercount = 1;
				}
			}
		}

    //Promote command
     if(strtolower(substr($data->message, 0, 8)) == "!promote")
     {
       //If pickups are running
                    if($pickupstatus == true)
                    {
                        //If pickup isn't full
                        if($pickup->player_count() < 12)
                        {
                            //If the anti-flood is not in effect
                            if($messageflood["promote"] < time())
                            {
                                //Set anti-flood time
                                $messageflood["promote"] = time() + 30;

                                //The pickup isn't, so lets check whats needed!
                                //Check if the pickup is completely empty
                                if($pickup->player_count() == 0)
                                {
                                    $irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Promote:\x02 ADD UP GUYS, PICKUP IS EMPTY! FML!");
                                } else
                                {
                                    //Build classes message based on free slots
                                    $message = "\x0302,00This pickup still needs";

                                    //Demos
                                    switch($pickup->count_class("demoman"))
                                    {
                                        case 2:
                                            $demos = "";
                                            break;

                                        case 1:
                                            $demos = "1 demo";
                                            break;

                                        case 0:
                                            $demos = "2 demos";
                                            break;
                                    }

                                    //Soldiers
                                    switch($pickup->count_class("soldier"))
                                    {
                                        case 4:
                                            $soldiers = "";
                                            break;

                                        case 3:
                                            $soldiers = "1 Soldier";
                                            break;

                                        case 2:
                                            $soldiers = "2 Soldiers";
                                            break;

                                        case 1:
                                            $soldiers = "3 Soldiers";
                                            break;

                                        case 0:
                                            $soldiers = "4 Soldiers";
                                    }

                                    //Scouts
                                    switch($pickup->count_class("scout"))
                                    {
                                        case 4:
                                            $scouts = "";
                                            break;

                                        case 3:
                                            $scouts = "1 Scout";
                                            break;

                                        case 2:
                                            $scouts = "2 Scouts";
                                            break;

                                        case 1:
                                            $scouts = "3 Scouts";
                                            break;

                                        case 0:
                                            $scouts = "4 Scouts";
                                            break;
                                    }

                                    //Medics
                                    switch($pickup->count_class("medic"))
                                    {
                                        case 2:
                                            $medics = "";
                                            break;

                                        case 1:
                                            $medics = "1 Medic";
                                            break;

                                        case 0:
                                            $medics = "2 Medics";
                                            break;
                                    }

                                    //Find number of classes for the message
                                    $stacksize = 0;
                                    if($demos != "")    {   $stacksize++;   }
                                    if($soldiers != "") {   $stacksize++;   }
                                    if($scouts != "")   {   $stacksize++;   }
                                    if($medics != "")   {   $stacksize++;   }

                                    //Build message based on stacksize
                                    switch($stacksize)
                                    {
                                        case 0:
                                            $message = "... >.>";
                                            break;

                                        case 1:
                                            if($demos != "")
                                            {
                                                $message .= " " . $demos;
                                            }

                                            if($soldiers != "")
                                            {
                                               $message .= " " . $soldiers;
                                            }

                                            if($scouts != "")
                                            {
                                                $message .= " " . $scouts;
                                            }

                                            if($medics != "")
                                            {
                                                $message .= " " . $medics;
                                            }
                                            break;

                                        case 2:
                                            if($demos != "")
                                            {
                                                $message .= " " . $demos . " and ";
                                                if($soldiers != "") {   $message .= $soldiers;  }
                                                if($scouts != "")   {   $message .= $scouts;    }
                                                if($medics != "")   {   $message .= $medics;    }
                                            }

                                            if($soldiers != "")
                                            {
                                                $message .= " " . $soldiers . " and ";
                                                if($scouts != "")   {   $message .= $scouts;    }
                                                if($medics != "")   {   $message .= $medics;    }
                                            }

                                            if($scouts != "")
                                            {
                                                $message .= " " . $scouts . " and " . $medics;
                                            }
                                            break;

                                        case 3:
                                            switch("")
                                            {
                                                case $demos:
                                                    $message .= " " . $soldiers . ", " . $scouts . " and " . $medics;
                                                    break;

                                                case $soldiers:
                                                    $message .= " " . $demos . ", " . $scouts . " and " . $medics;
                                                    break;

                                                case $scouts:
                                                    $message .= " " . $demos . ", " . $soldiers . " and " . $medics;

                                                case $medics:
                                                    $message .= " " . $demos . ", " . $soldiers . " and " . $scouts;
                                            }
                                            
                                        case 4:
                                            $message .= " " . $demos . ", " . $soldiers . ", " . $scouts . " and " . $medics;
                                            break;
                                    }

                                //Now send the message on IRC
                                $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
                                
                                }
                            }                            
                        }
                    }
     }
	}

    //Q confirm
    //Confirms if the bot is authed with Q
    function qconfirm(&$irc, &$data)
    {
			global $pickupchannel;
            if($data->message == "Remember: NO-ONE from QuakeNet will ever ask for your password.  NEVER send your password to ANYONE except Q@CServe.quakenet.org.")
            {
                echo "\n\n\n\nSHIIIT\n\n\n\n";
                $irc->join($pickupchannel);
            }
    }
}

?>
