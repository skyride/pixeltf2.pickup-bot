<?php
$arguements = $_SERVER['argv'];

$keylock = $arguements[2];
$length = $arguements[1];

//Wait bot
include_once("SmartIRC.php");

function writepid()
{
	$PIDFile = "wait.pid";
	$fh = fopen($PIDFile, 'w') or die("can't open file");
	$stringData = getmypid();
	fwrite($fh, $stringData);
	fclose($fh);
}

class PickupBot
{
	function wait(&$irc, &$data)
	{
	    global $keylock;
	    global $length;
		$irc->message(SMARTIRC_TYPE_QUERY, "PixelTF2Bot", $keylock);
		echo "\n\n".$length."\n\n";
		echo "\n\n".$keylock."\n\n";
		$irc->message(SMARTIRC_TYPE_QUERY, $irc->_nick, "cocobeans");
		
		echo "\n\n\nWAIT BOT NATURAL END\n\n\n";
		
		sleep($length);
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

writepid();

//Connect Bot
	$irc->connect('multiplay.uk.quakenet.org', 6667);
	$irc->login('PixelWaitBot', 'PixelGaming.eu Pickup Bot #2', 0,'PixelWaitBot');
        $irc->join('#pixeltf2.bots', 'dreamworks');

$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot, 'wait');
$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, 'cocobeans', &$PickupBot, 'kill');

$irc->listen();


$irc->disconnect();

?>
