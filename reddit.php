<?php
//Pixel Pickup Bot
//!message bot
//This bots sole purpose is to print !messages and !skill to IRC

include("SmartIRC.php");
include("config.php");

class loop
{
	var $count = 0;
	
	function check()
	{
		if($counter < 8)
		{
			$this->count++;
			return false;
		} else
		{
			$this->count = 0;
			return true;
		}
	}
}

$loop = new loop;

class MessageBot
{
	function SayMessage(&$irc, &$data)
	{
		global $loop;
		
		if($loop->check() == true)
		{
			$irc->message(SMARTIRC_TYPE_CHANNEL, $data->message, "Congratulations!");
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
	$irc->connect('irc.esper.net', 6667);
	$irc->login("Congratulations", $ircinfo->realname, 0,'Congratulations');	
	$irc->join("#starcraft");
	
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '', &$MessageBot, 'SayMessage');
	
	$irc->listen();

?>