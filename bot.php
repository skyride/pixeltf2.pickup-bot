<?php
ini_set("memory_limit", "64M");
//Pixel Gaming Pickup Bot

//Declares
$messages = array();
$skill = 0;
$pickupstatus = false;
$lastantiflood = 0;
$messageflood = array();
$classflood = array();
$statusinterp = 0;
$startstatus = false;
$chanjoin = 0;

//Main includes
include("config.php");
include("servers.php");
include("functions.php");
include("init.php");
include("fun.php");
include("maps.php");

//IRC Class
include_once('SmartIRC.php');

//IRC Bot Class Files
include_once('MainBot.php');
include_once('TopicBot.php');

echo "\n\n\n\n\nDSFSDFSFDSFSDFDSFDSFDSF\n\n\n\n\n\n";

//Called when the bot loads
function init()
{
	global $irc;
	global $PickupBot;
	global $pickupchannel;
	global $pickup;
	global $users;
	global $messages;
	global $bans;

	//Tell the channel that the bot in initialising
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Bot initialising...");
	$irc->message(SMARTIRC_TYPE_QUERY, "Q", "settopic ".$pickupchannel." \x02\x035Pixel Pickup Pickup Channel\x03 :: Bot Initialising...");

	//Get the message commands in
	$messages = mysql_pu_getmessages();

	//Get the user list and add it to the users object
	$userlist = mysql_pu_userlist();
	$counter = 0;
	while($counter < count($userlist))
	{
		$users->add_user_clean($userlist[$counter]["qauth"], $userlist[$counter]["nick"], 0, $userlist[$counter]["admin"], $userlist[$counter]["skill_scout"], $userlist[$counter]["skill_demoman"], $userlist[$counter]["skill_soldier"], $userlist[$counter]["skill_medic"]);
		$counter++;
	}
	
	//Get the bans into memory
	$banlist = mysql_pu_getbans();
	
	$counter = 0;
	while($counter < count($bans))
	{
		$bans->add_ban_clean($banlist[$counter]["qauth"], $banlist[$counter]["reason"], $banlist[$counter]["expires"], $banlist[$counter]["admin"]);
		$counter++;
	}

	$irc->registerActionhandler(SMARTIRC_TYPE_NICKCHANGE, '', &$PickupBot, 'nick_change');
	$irc->registerActionhandler(SMARTIRC_TYPE_UNKNOWN, '', &$PickupBot, 'whois_queue');
	$irc->registerActionhandler(SMARTIRC_TYPE_PART, '', &$PickupBot, 'leave');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUIT, '', &$PickupBot, 'leave');
	$irc->registerActionhandler(SMARTIRC_TYPE_KICK, '', &$PickupBot, 'PlayerKicked');
	$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot, 'join');
	$irc->registerActionhandler(SMARTIRC_TYPE_NAME, '', &$PickupBot, 'init_names');
	$irc->registerActionhandler(SMARTIRC_TYPE_WHOIS, '', &$PickupBot, 'whois_invite');
    $irc->registerActionhandler(SMARTIRC_TYPE_NOTICE, '', &$PickupBot, 'qconfirm');
}

//Add command hooks
function init_hooks()
{
	global $irc;
	global $PickupBot;

	//Hook Events
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'KillBot');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'Rehash');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'reloadmaps');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'info');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '', &$PickupBot, 'user_active');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'invite_query');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'report');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'senddelay');
    $irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'waitbot');
}

//Enable Bot
function EnablePickup()
{
	//Bring to scope
	global $irc;
	global $PickupBot;
	global $pickupchannel;
	global $pickup;
	global $pickupstatus;

	//Hook events
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'ChannelSay');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'OtherPickupStart');
	$pickupstatus = true;

	//Send Enable Message
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Pickup bot is now enabled");
        SetTopic();
}

function DisablePickup($reason = null)
{
	//Bring to scope
	global $irc;
	global $PickupBot;
	global $pickupchannel;
	global $pickup;
	global $pickupstatus;
    global $TopicBot;

	//Unhook events
	$irc->unregisterActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'ChannelSay');
	$irc->unregisterActionhandler(SMARTIRC_TYPE_QUERY, '', &$PickupBot, 'OtherPickupStart');
	$pickup->wipe();
	$pickupstatus = false;

	//Send Disable Message
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Pickup bot is now disabled");

	if($reason != null)
	{
		$TopicBot->SetTopic("\x02\x035Pixel Pickup Pickup Channel\x03 :: Bot Offline ::\x02 ".$reason);
	} else
	{
		$TopicBot->SetTopic("\x02\x035Pixel Pickup Pickup Channel\x03 :: Bot Offline");
	}
}

function PausePickup($reason = null)
{
	//Bring to scope
	global $irc;
	global $PickupBot;
	global $pickupchannel;
	global $pickup;
	global $pickupstatus;
        global $TopicBot;

	//Unhook events
	$irc->unregisterActionhandler(SMARTIRC_TYPE_CHANNEL, '!', &$PickupBot, 'ChannelSay');
	$pickupstatus = false;

	//Send Disable Message
	$irc->message(SMARTIRC_TYPE_CHANNEL, $pickupchannel, "\x02\x0302,00Pickup bot is now paused");

	if($reason != null)
	{
		$TopicBot->SetTopic("\x02\x035Pixel Pickup Pickup Channel\x03 :: Bot Paused ::\x02 ".$reason);
	} else
	{
		$TopicBot->SetTopic("\x02\x035Pixel Pickup Pickup Channel\x03 :: Bot Paused");
	}
}

$PickupBot = &new PickupBot;

//Create bot class
$irc = &new Net_SmartIRC();

//Set info
$irc->setUseSockets(true);

//Enable debugging
$irc->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);

//Connect Bots
	$irc->connect('multiplay.uk.quakenet.org', 6667);
	$irc->login($ircinfo->nick, $ircinfo->realname, 0,'PixelTF2Bot');
	$irc->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH '.$ircinfo->qauth_user.' '.$ircinfo->qauth_pass);

	//Start init cascade
	init();

$irc->listen();

$irc->disconnect();
?>
