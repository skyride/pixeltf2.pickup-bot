<?php

//MySQL settings
class mysql_server
{
	public $host;
	public $user;
	public $pass;
	public $db;
}

class ircinfo
{
	public $qauth_user;
	public $qauth_pass;
	public $nick;
	public $realname;
	public $pickupchan;
	public $adminchan;
	public $botchan;
	public $adminchan_pass;
	public $botchan_pass;
}

$ircinfo = new ircinfo;
$ircinfo->qauth_user = "PixelTF2Bot";
$ircinfo->qauth_pass = "Nope you're not getting to see this";
$ircinfo->nick = "PixelTF2Bot";
$ircinfo->realname = "PixelGaming.eu Pickup Bot #1";
$ircinfo->pickupchan = "#pixeltf2.pickup";
$ircinfo->adminchan = "#pixeltf2.admin";
$ircinfo->adminchan_pass = "Nope you're not getting to see this";
$ircinfo->botchan = "#pixeltf2.bots";
$ircinfo->botchan_pass = "Nope you're not getting to see this";

//For legacy, needs to be replaced by above class in the main codebase
$pickupchannel = $ircinfo->pickupchan;
$adminchannel = $ircinfo->adminchan;

$mysql = new mysql_server;

$mysql->host = 'localhost';
$mysql->user = 'pickupbot';
$mysql->pass = "Nope you're not getting to see this";
$mysql->db = 'pickup2';

$playerkillkey = "Nope you're not getting to see this";