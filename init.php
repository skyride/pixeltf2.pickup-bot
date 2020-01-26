<?php
//Pixel Gaming Pickup Bot
//Bot Init Script

class player
{
	public $qauth;
	public $nick;
	public $class;
	public $map;
	public $afk;
	public $lastactive;
	public $skill;
}

class pickup
{
	public $player;
	public $votes;

	public function init()
	{
		$this->player = array(
			0 => new player,
			1 => new player,
			2 => new player,
			3 => new player,
			4 => new player,
			5 => new player,
			6 => new player,
			7 => new player,
			8 => new player,
			9 => new player,
			10 => new player,
			11 => new player );
	}

	public function wipe()
	{
		$counter = 0;
		while($counter < 12)
		{
			unset($this->player[$counter]);
			$counter++;
		}
		$this->init();
	}

	public function add_player($qauth, $nick, $class, $skill)
	{
		//Reorder the array		
		//$this->_order();

		//Find an empty slot
		$counter = 0;
		while($counter < 12)
		{
			if($this->player[$counter]->nick == "")
			{
				$this->player[$counter]->qauth = $qauth;
				$this->player[$counter]->nick = $nick;
				$this->player[$counter]->class = $class;
				$this->player[$counter]->map = $map;
				$this->player[$counter]->afk = 0;
				$this->player[$counter]->skill = $skill;
				$counter = 12;
			}
			$counter++;
		}
	}

	public function rm_player($qauth)
	{
		//Find the player
		$counter = 0;
		while($counter < 12)
		{
			if($this->player[$counter]->qauth == $qauth)
			{
				$this->player[$counter]->qauth = "";
				$this->player[$counter]->nick = "";
				$this->player[$counter]->class = "";
				$this->player[$counter]->map = "";
				$this->player[$counter]->afk = "";
				$counter = 12;
			}
			$counter++;
		}
		//Reorder to remove space left by removal
		//$this->_order();
	}

	public function nick_change($old_nick, $new_nick)
	{
		//Find player
		$counter = 0;
		while($counter < 12)
		{
			if($this->player[$counter]->nick == $old_nick)
			{
				$this->player[$counter]->nick = $new_nick;
				$counter = 12;
			}
			$counter++;
		}
	}

	public function count_class($inclass)
	{
		$classcount = 0;
		$counter = 0;

		while($counter < 12)
		{
			if($this->player[$counter]->class == $inclass)
			{
				$classcount++;
			}
			$counter++;
		}

		//Return count number
		return $classcount;
	}

	public function classid_nick($class, $id)
	{
		//Default Values
		$returnvalue = "";
		$counter = 0;
		$classcounter = -1;
		while($counter < 12)
		{
			if($this->player[$counter]->class == $class)
			{
				$classcounter++;
				if($classcounter == $id)
				{
					$returnvalue = $this->player[$counter]->nick;
					$counter = 12;
				}
			}

			$counter++;
		}

		//Return value
		return $returnvalue;
	}

	public function classid_qauth($class, $id)
	{
		//Default Values
		$returnvalue = "";
		$counter = 0;
		$classcounter = -1;
		while($counter < 12)
		{
			if($this->player[$counter]->class == $class)
			{
				$classcounter++;
				if($classcounter == $id)
				{
					$returnvalue = $this->player[$counter]->qauth;
					$counter = 12;
				}
			}

			$counter++;
		}

		//Return value
		return $returnvalue;
	}

	public function is_voted($map)
	{
		//Set default return value
		$return = false;

		$counter2 = 0;
		while($counter2 < count($this->votes))
		{
			if($this->votes[$counter2]["map"] == $map)
			{
				//Yes the map has been voted for
				$return = $counter2;
			}
			$counter2++;
		}

		//Return the value
		return $return;
	}

	//Build vote array
	public function build_votes()
	{
		//Clear array
		$this->votes = array();

		$counter = 0;
		$mapcounter = 0;

		while($counter < 12)
		{
			//If the player has voted
			if($this->player[$counter]->map != "")
			{
				
				if($this->is_voted($this->player[$counter]->map) === false)
				{
					$this->votes[$mapcounter]["map"] = $this->player[$counter]->map;
					$this->votes[$mapcounter]["votes"] = 1;
					$mapid = $mapcounter;
					$mapcounter++;
				} else
				{
					$mapid = $this->is_voted($this->player[$counter]->map);
					$this->votes[$mapid]["votes"] = $this->votes[$mapid]["votes"] + 1;
				}

				//Add an extra vote if the person is medic
				if($this->player[$counter]->class == "medic")
				{
					$this->votes[$mapid]["votes"] = $this->votes[$mapid]["votes"] + 1;
				}
			}
			$counter++;
		}
	}

	//Vote for a map
	public function mapvote($qauth, $map)
	{
		//Find the user
		$counter = 0;

                //Set default return value
                $returnvalue = false;

		while($counter < 12)
		{
			if($this->player[$counter]->qauth == $qauth)
			{
                            if($this->player[$counter]->map == $map)
                            {
                                $returnvalue = true;
                            }

                            $this->player[$counter]->map = $map;
			}
			$counter++;
		}

                //Return the final value
                return $returnvalue;
	}

	//Find players of certain classes

	//Produce the channel title
	public function title()
	{
		//Prefix of the topic title
		//$title_prefix = "\x02\x035PixelGaming.eu INVITE\x03 ::\x02 ";

		//Add Player Counts
		$StandardCount = $this->count_class("demoman") + $this->count_class("soldier") + $this->count_class("scout");
		$MedicCount = $this->count_class("medic");
		$title = $title_prefix . "\x035\x02(\x02".$StandardCount."+".$MedicCount."\x02)\x03 ::\x02 ";

		//Add Class Nick
		$classnicks = "\x02Medic(\x02\x0310".TopicName($this->classid_qauth("medic", 0)).", ".TopicName($this->classid_qauth("medic", 1))."\x03\x02) "; //Medic
		$classnicks = $classnicks . "Demo(\x02\x0310".TopicName($this->classid_qauth("demoman", 0)).", ".TopicName($this->classid_qauth("demoman", 1))."\x03\x02) "; //Demo
		$classnicks = $classnicks . "Soldier(\x02\x0310".TopicName($this->classid_qauth("soldier", 0)).", ".TopicName($this->classid_qauth("soldier", 1)).", ".TopicName($this->classid_qauth("soldier", 2)).", ".TopicName($this->classid_qauth("soldier", 3))."\x03\x02) "; //Soldier
		$classnicks = $classnicks . "Scout(\x02\x0310".TopicName($this->classid_qauth("scout", 0)).", ".TopicName($this->classid_qauth("scout", 1)).", ".TopicName($this->classid_qauth("scout", 2)).", ".TopicName($this->classid_qauth("scout", 3))."\x03\x02) "; //Scout
		$title = $title . $classnicks;

		//Generate fresh votes list
		$this->build_votes();
		$counter2 = 0;
		$mapvotes = ":: Maps(\x02";
		while($counter2 < count($this->votes))
		{
			$mapvotes = $mapvotes . $this->votes[$counter2]["map"]." x".$this->votes[$counter2]["votes"].", ";
			$counter2++;
		}
		$mapvotes = $mapvotes . "\x02)\x02";

		//Add map votes to title
		$title = $title . $mapvotes;

		//Return the channel title
		return $title;
	}
	
	//Return the number of players added
	public function add_count()
	{
		$players = $this->count_class("demoman") + $this->count_class("soldier") + $this->count_class("scout") + $this->count_class("medic");
		return $players;
	}

	//Check if a player is already added
	public function is_added($qauth)
	{
		$counter = 0;
		$returnvalue = false;

		while($counter < 12)
		{
			if($this->player[$counter]->qauth == $qauth)
			{
				$returnvalue = $this->player[$counter]->class;
				$counter = 12;
			}
			$counter++;
		}

		//Return final value
		return $returnvalue;
	}

	//Count the number of players added
	public function player_count()
	{
		$counter = 0;
		$addcount = 0;

		while($counter < 12)
		{
			if($this->player[$counter]->qauth != "")
			{
				$addcount++;
			}
			$counter++;
		}

		//Return the player count
		return $addcount;
	}

	public function skill($qauth)
	{
		$counter = 0;
		$returnvalue = false;

		while($counter < 12)
		{
			if($this->player[$counter]->qauth == $qauth)
			{
				$returnvalue = $this->player[$counter]->skill;
				$counter = 12;
			}
			$counter++;
		}

		//Return final value
		return $returnvalue;
	}
}

class user
{
	public $qauth;
	public $nick;
	public $lastactive;
	public $banned;
	public $inchannel;
	public $admin;
	public $skill_scout;
	public $skill_demoman;
	public $skill_soldier;
	public $skill_medic;
	public $banreason;
}

class users
{
	public $users;

	public function init()
	{
		$user = array();
	}

	//Clean add user for creating initial list (doesn't add to MySQL)
	public function add_user_clean($qauth, $nick, $inchannel, $admin, $skill_scout, $skill_demoman, $skill_soldier, $skill_medic)
	{
		$item = count($this->users);
		$this->users[$item] = new user;

		$this->users[$item]->nick = $nick;
		$this->users[$item]->qauth = $qauth;
		$this->users[$item]->lastactive = time();
		$this->users[$item]->banned = 0;
		$this->users[$item]->inchannel = $inchannel;
		$this->users[$item]->admin = $admin;
		$this->users[$item]->skill_scout = $skill_scout;
		$this->users[$item]->skill_demoman = $skill_demoman;
		$this->users[$item]->skill_soldier = $skill_soldier;
		$this->users[$item]->skill_medic = $skill_medic;
		$this->users[$item]->banreason = $banreason;
	}

	//User add including MySQL
	public function add_user($qauth, $nick, $invitedby, $inchannel = 1, $admin = 0)
	{
		$item = count($this->users);
		$this->users[$item] = new user;

		$this->users[$item]->nick = $nick;
		$this->users[$item]->qauth = $qauth;
		$this->users[$item]->lastactive = time();
		$this->users[$item]->banned = 0;
		$this->users[$item]->inchannel = $inchannel;
		$this->users[$item]->admin = $admin;
		$this->users[$item]->skill_scout = "3";
		$this->users[$item]->skill_demoman = "3";
		$this->users[$item]->skill_soldier = "3";
		$this->users[$item]->skill_medic = "3";
		$this->users[$item]->banreason = "";

		//Add the user to MySQL
		mysql_pu_adduser($qauth, $nick, $invitedby);
	}
	
	public function del_user($qauth)
	{
		//Find the user
		$counter = 0;
		$count = count($this->users);
		$new = array();
		
		//Create new array without the user
		while($counter < $count)
		{
			if($this->users[$counter]->qauth != $qauth)
			{
				$new[] = $this->users[$counter];
			}
			$counter++;
		}
		
		//Set new array in object
		$this->users = $new;
		
		//Delete user from MySQL
		mysql_pu_deluser($qauth);
	}

	public function nick_change($old_nick, $new_nick)
	{
		$counter = 0;
		$count = count($this->users);
        $qauth = $this->nicktoqauth($old_nick);

		//Find the nick's array position
		while($counter < $count)
		{
			if($this->users[$counter]->nick == $old_nick)
			{
				$this->users[$counter]->nick = $new_nick;
				mysql_pu_nickupdate($qauth, $new_nick);
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	public function nick_change_join($qauth, $new_nick)
	{
		$counter = 0;
		$count = count($this->users);

		//Find the nick's array position
		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$this->users[$counter]->nick = $new_nick;
				mysql_pu_nickupdate($qauth, $new_nick);
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	public function user_join($qauth, $nick)
	{
		//Check if the bot already knows them
		$counter = 0;
		$position = null;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$position = $counter;
				$counter = $count + 1;
			}
			$counter++;
		}

		//Now add the user if they do not exist
		if($position == null)
		{
			add_user($qauth, $nick);
		} else
		{
			//Else simply update their nick and inchannel status
			$this->users[$counter]->nick = $nick;
			$this->users[$counter]->inchannel = 1;
		}
	}

	public function user_leave($nick)
	{
		//Check if the bot knows them
		$counter = 0;
		$position = null;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->nick == $nick)
			{
				$position = $counter;
				$counter = $count + 1;
			}
			$counter++;
		}

		//Set their inchannel status to 0 if they were found
		if($position != null)
		{
			$this->users[$counter]->inchannel = 0;
		}
	}

	public function user_give_admin($qauth)
	{
		//Find the user in the bot
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$this->users[$counter]->admin = 1;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	public function is_admin($nick)
	{
		//Find the user in the bot
		$returnvalue = false;
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->nick == $nick)
			{
				if($this->users[$counter]->admin == 0)	{	$returnvalue = false;	}
				if($this->users[$counter]->admin == 1)	{	$returnvalue = true;	}
				if($this->users[$counter]->admin == 2)	{	$returnvalue = true;	}
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	public function is_su_admin($nick)
	{
		//Find the user in the bot
		$returnvalue = false;
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->nick == $nick)
			{
				if($this->users[$counter]->admin == 0)	{	$returnvalue = false;	}
				if($this->users[$counter]->admin == 1)	{	$returnvalue = false;	}
				if($this->users[$counter]->admin == 2)	{	$returnvalue = true;	}
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	public function nicktoqauth($nick)
	{
		//Find the user in the bot
		$returnvalue = false;
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if(strtolower($this->users[$counter]->nick) == strtolower($nick))
			{
				$returnvalue = $this->users[$counter]->qauth;
				$counter = $count + 1;
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	public function qauthtonick($qauth)
	{
		//Find the user in the bot
		$returnvalue = false;
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if(strtolower($this->users[$counter]->qauth) == strtolower($qauth))
			{
				$returnvalue = $this->users[$counter]->nick;
				$counter = $count + 1;
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	public function is_added($qauth)
	{
		//Find the user
		$returnvalue = false;
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$returnvalue = true;
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	public function mark_inchannel($qauth)
	{
		//Find the user
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$this->users[$counter]->inchannel = 1;
			}
			$counter++;
		}
	}

	public function mark_outchannel($qauth)
	{
		//Find the user
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$this->users[$counter]->inchannel = 0;
			}
			$counter++;
		}
	}

	public function is_banned($qauth)
	{
		global $bans;
	
		//Find the user
		$counter = 0;
		$returnvalue = false;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				if($this->users[$counter]->banned == 1)
				{
					$returnvalue = true;
				}
			}
			$counter++;
		}

		//Now also check the bans file
		$bansinfo = $bans->info($qauth);
		if($bansinfo["qauth"] == $qauth)
		{
			$returnvalue = true;
		}

		//Return value
		return $returnvalue;
	}

	public function mark_banned($nick)
	{
		global $irc;
		global $pickupchannel;

		//Find the user
		$counter = 0;
		$returnvalue = false;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->nick == $nick)
			{
				$this->users[$counter]->banned = 1;
				mysql_pu_banupdate($nick);
				$irc->message(SMARTIRC_TYPE_QUERY, $pickupchannel, "TEMPBAN ".$pickupchannel." *!*.".$nick.".users.quakenet.org 3m You have been banned by a channel admin");
			}
			$counter++;
		}
	}

	public function banreason($qauth)
	{
		$returnvalue = "";
		$counter = 0;
		$count = count($this->users);

		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$returnvalue = $this->users[$counter]->banreason;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	public function user_active($nick)
	{
		//Find the user
		$counter = 0;
		$count = count($this->users);
		while($counter < $count)
		{
			if($this->users[$counter]->nick == $nick)
			{
				$this->users[$counter]->lastactive = time();
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	public function last_active($qauth)
	{
		//Find the user
		$counter = 0;
		$returnvalue = false;
		$count = count($this->users);
		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$returnvalue = $this->users[$counter]->lastactive;
				$counter = $count + 1;
			}
			$counter++;
		}

		//Return the value
		return $returnvalue;
	}

	//Find a players skill rating for a given class
	public function get_skill($qauth, $class)
	{
		//Query MySQL
		$output = mysql_skill($qauth, $class);
		return $output;
	}

	public function mark_afk($qauth)
	{
		//Find the user
		$counter = 0;
		$count = count($this->users);
		while($counter < $count)
		{
			if($this->users[$counter]->qauth == $qauth)
			{
				$this->users[$counter]->lastactive = 0;
			}
			$counter++;
		}
	}
}

class queueitem
{
	public $command;
	public $map;
	public $class;
}

class queue
{
	public $items;

	public function init()
	{
		$this->items = array();
	}

	//Add an item to the queue
	public function add_item($nick, $command, $map = "", $class = "")
	{
		//Create the entry if the user does not exist
		if(isset($this->items[$nick]) == false)
		{
			$this->items[$nick];
		}

		//get item count
		$itemid = count($this->items[$nick]);

		//Add item
		$this->items[$nick][$itemid] = new queueitem;
		$this->items[$nick][$itemid]->command = $command;
		$this->items[$nick][$itemid]->map = $map;
		$this->items[$nick][$itemid]->class = $class;
	}

	public function get_item($nick)
	{
		$returnvalue = false;

		//Check if the user exists
		if(isset($this->items[$nick]) == true)
		{
			//If there is actually an entry
			if(count($this->items[$nick]))
			{
				$returnvalue = array();
				$returnvalue["nick"] = $nick;
				$returnvalue["command"] = $this->items[$nick][0]->command;
				$returnvalue["map"] = $this->items[$nick][0]->map;
				$returnvalue["class"] = $this->items[$nick][0]->class;
			}
		}

		//Return the value
		return $returnvalue;
	}

	public function clear_item($nick)
	{
		//Check if the user exists
		if(isset($this->items[$nick]) == true)
		{
			//Check if they have a command set
			if(count($this->items[$nick]) > 0)
			{
				//Remove it using array shift
				array_shift($this->items[$nick][0]);
			}
		}
	}
}

class invite
{
	public $nick;
	public $qauth;
	public $invitedby;
	public $starttime;
	public $stage;
}

class invites
{
	public $invites;

	public function init()
	{
		$this->invites = array();
	}

	//Re-order the invite array
	public function _order($count)
	{
		$counter = 0;
		$count = count($this->invites);

		while($counter < $count)
		{
			//Check if the slot is used
			if($this->invites[$counter]->nick != "")
			{
				$newarray[] = $this->invites[$counter];
			}
			$counter++;
		}
		
		//Make the new array the actual array
		$this->invites = $newarray;
	}

	//Stage 0 - Get info into an invite
	public function startinvite($nick, $invitedby)
	{
		$count = count($this->invites);
		$this->invites[$count] = new invite;

		$this->invites[$count]->nick = $nick;
		$this->invites[$count]->starttime = time();
		$this->invites[$count]->invitedby = $invitedby;
		$this->invites[$count]->stage = 0;
	}

	//Get the start of whois 311 code
	public function stage1($nick)
	{
		//Get ID of invite
		$count = count($this->invites);
		$counter = 0;

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				$this->invites[$counter]->stage = 1;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	//Stage 2 - Get the qauth of the invitee
	public function stage2($nick, $qauth, $realnick)
	{
		//Get ID of invite
		$count = count($this->invites);
		$counter = 0;

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				$this->invites[$counter]->qauth = $qauth;
				$this->invites[$counter]->nick = $realnick;
				$this->invites[$counter]->stage = 2;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	//Stage 3 - Get the /END OF WHOIS 318 code
	public function stage3($nick)
	{
		//Get ID of invite
		$count = count($this->invites);
		$counter = 0;

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				$this->invites[$counter]->stage = 3;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	//Stage 4 - Recieve the users invite accept
	public function stage4($nick)
	{
		//Get ID of invite
		$count = count($this->invites);
		$counter = 0;

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				$this->invites[$counter]->stage = 4;
				$counter = $count + 1;
			}
			$counter++;
		}
	}

	//Info to add the user to MySQL
	public function info($nick)
	{
		$returnvalue = false;
		$counter = 0;
		$count = count($this->invites);

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				$returnvalue = $this->invites[$counter];
			}
			$counter++;
		}

		//Return the result
		return $returnvalue;
	}

	//Remove invite
	public function rm_invite($nick)
	{
		//Get ID of invite
		$count = count($this->invites);
		$counter = 0;
		$id = false;

		while($counter < $count)
		{
			if($nick == $this->invites[$counter]->nick)
			{
				unset($this->invites[$counter]);
				$this->_order();
				$counter = $count + 1;
			}
			$counter++;
		}
	}
}

class ban
{
	public $qauth;
	public $reason;
	public $expires;
	public $admin;
}

class bans
{
	public $bans;

	//Init func
	public function init()
	{
		$this->bans = array();
	}

	//Add a ban
	public function add_ban($qauth, $length, $expires, $admin, $reason)
	{
		//Add the new ban to memory
		$this->bans[$qauth] = new ban;
		
		$this->bans[$qauth]->qauth = $qauth;
		$this->bans[$qauth]->reason = $reason;
		$this->bans[$qauth]->expires = $expires;
		$this->bans[$qauth]->admin = $admin;
		
		//Add the ban to MySQL
		mysql_pu_banuser($qauth, $expires, $admin, $length, $reason);
	}
	
	//Clean bad add for bot initialisation
	public function add_ban_clean($qauth, $reason, $expires, $admin)
	{
		//Add the new ban to memory
		$this->bans[$qauth] = new ban;
		
		$this->bans[$qauth]->qauth = $qauth;
		$this->bans[$qauth]->reason = $reason;
		$this->bans[$qauth]->expires = $expires;
		$this->bans[$qauth]->admin = $admin;
	}
	
	public function rm_ban($qauth)
	{
		//Remove the ban from memory
		unset($this->bans[$qauth]);
		
		//Zero the expire time in MySQL
		mysql_pu_unbanuser($qauth);
	}
	
	public function info($qauth)
	{
		//Return the info on this ban
		$returnvalue["qauth"] = $this->bans[$qauth]->qauth;
		$returnvalue["reason"] = $this->bans[$qauth]->reason;
		$returnvalue["expires"] = $this->bans[$qauth]->expires;
		$returnvalue["admin"] = $this->bans[$qauth]->admin;
		
		return $returnvalue;
	}
}

class waitbot
{
	public $status;
	public $length;
	public $key;
	public $starttime;
	public $type;
	
	function init()
	{
		$this->status = false;
		$this->length = 0;
	}
	
	//Start the wait bot
	function StartWait($length, $type)
	{
		//Kill existing bot if running
		if($this->status == true)
		{
			//Get PID
			$file = "wait.pid";
			$fh = fopen($file, 'r'); 
			$pid = fread($fh, filesize($file)); 
			fclose($handle); 

			//Kill the process
			system("kill ".$pid);
		}
		
		//Generate key
		$keylock = rand_str(12);
		
		//Start new wait bot
		$botlength = $length - 3;
		system('screen -A -L -m -d -S PixelWaitBot php wait.php '.$botlength.' '.$keylock);
		
		//Mark data to object
		$this->status = true;
		$this->length = $length;
		$this->key = $keylock;
		$this->starttime = time();
		$this->type = $type;
	}
	
	//Mark the bot as no longer waiting
	function EndWait()
	{
		$this->key = "";
		$this->status = false;
	}
	
	//Kill the running bot
	function KillBot()
	{
		//Get PID
		$file = "wait.pid";
		$fh = fopen($file, 'r'); 
		$pid = fread($fh, filesize($file)); 
		fclose($handle); 

		//Kill the process
		system("kill ".$pid);
		
		//Set status
		$this->key = "";
		$this->status = false;
	}
}

//afk object
class afk
{
	public $afklist;
	
	function init()
	{
		$this->afklist = array();
	}
	
	function add_player($qauth)
	{
		$this->afklist[] = $qauth;
	}
	
	function rm_player($qauth)
	{
		//REORDER THE FUCKING ARRAY PROPERLY
		$original = $this->afklist;
		
		$counter = 0;
		$count = count($this->afklist);
		$new = array();
		
		while($counter < $count)
		{
			//If its not the one being removed
			if($original[$counter] != $qauth)
			{
				$new[] = $original[$counter];
			}
			$counter++;
		}
		
		//Make proper the new array
		$this->afklist = $new;
	}
	
	function is_afk($qauth)
	{
		$returnvalue = false;
		$counter = 0;
		$count = count($this->afklist);
		while($counter < $count)
		{
			if($this->afklist[$counter] == $qauth)
			{
				$returnvalue = true;
				$counter = $count + 1;
			}
			$counter++;
		}
		
		//Return the value
		return $returnvalue;
	}
	
	//Clear existing afkers
	function clear()
	{
		$this->afklist = array();
	}
}

class eventstack
{
    var $eventstack;

    function init()
    {
        $inittime = (time() - 5);

        $this->eventstack[0] = $inittime;
        $this->eventstack[1] = $inittime;
        $this->eventstack[2] = $inittime;
    }

    function NewEvent()
    {
        //Shift off first item
        array_shift($this->eventstack);

        //And add new item
        $this->eventstack[2] = time();
    }

    function CheckEvent()
    {
        //Default return value
        $returnvalue = true;

        //Check if a topic change should occur
        $ttime = ($this->eventstack[0] + $this->eventstack[1] + $this->eventstack[2]);
        $ttime = ($ttime / 3);
        $eventtime = $ttime - time();
        
        if($eventtime > -6)
        {
            $returnvalue = false;
        }

        //And register new event
        $this->NewEvent();

        return $returnvalue;
    }
}

class skillstack
{
	private $times;

	function trigger($qauth)
	{
		if((time() - $times[$qauth]) < 30)
		{
			$return = false;
		} else
		{
			$return = true;
		}
		
		//Update
		$times[$qauth] = time();
		
		//Return output
		return $return;
	}
	
	function init()
	{
		$times = array();
	}
}

$skillstack = new skillstack;
$skillstack->init();

$eventstack = new eventstack;
$eventstack->init();

$users = new users;
$users->init();

$pickup = new pickup;
$pickup->init();

$queue = new queue;
$queue->init();

$invites = new invites;
$invites->init();

$bans = new bans;
$bans->init();

$waitbot = new waitbot;
$waitbot->init();

$afk = new afk;
$afk->init();
?>
