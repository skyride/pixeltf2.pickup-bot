<?php

//Pixel Gaming Pickup Bot
//Topic Changing bot

//Create TopicBot Reply Thread

class TopicBot
{
    function SetTopic($topic)
    {
        global $irc2;
        global $irc;
        global $pickupchannel;

        $irc->message(SMARTIRC_TYPE_QUERY, "Q", "SETTOPIC ".$pickupchannel." ".$topic);
        echo "\n\n\ntest\n\n\n";
    }
}

$TopicBot = &new TopicBot;
/*///////////////////////////////////////////////////////
// Create new IRC instance
        $TopicBot = &new TopicBot;
        $irc2 = &new Net_SmartIRC();
        $irc2->setUseSockets(true);
        $irc2->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);

//Connect to quakenet and auth
        $irc2->connect('multiplay.uk.quakenet.org', 6667);
	$irc2->login('PixelTopicBot1', 'PixelGaming.eu Pickup Bot - Topic Bot', 0,'PixelTopicBot1');
	$irc2->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH PixelTF2Bot DFs53mJd3o');
        $irc2->join('#pixeltf2.bots', 'dreamworks');
        
$irc2_pid = pcntl_fork();
switch($irc2_pid) {
        case -1:
            echo "Could not fork!\n";
            exit;
            break;

        case 0:


        $irc2->listen();
        break;

}*/
?>
