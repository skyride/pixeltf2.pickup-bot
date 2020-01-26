<?php

class PickupBot2
{
    var $players;
    var $serverid;
    
	function send(&$irc, &$data)
	{
	    $players = $this->players;
            $serverid = $this->serverid;
            global $servers;
            
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

            //Send the self-kill command
            $irc->message(SMARTIRC_TYPE_QUERY, $irc->_nick, "cocobeans");
        }
        
	function kill(&$irc, &$data)
	{
		$irc->disconnect();
		die();
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
	$irc->login('PixelTF2Bot2', 'PixelGaming.eu Pickup Bot #2', 0,'PixelTF2Bot2');
	$irc->join('#pixeltf2.bots', 'dreamworks');

$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '', &$PickupBot, 'send');
$irc->registerActionhandler(SMARTIRC_TYPE_QUERY, 'cocobeans', &$PickupBot, 'kill');

$irc->listen();


$irc->disconnect();

?>
