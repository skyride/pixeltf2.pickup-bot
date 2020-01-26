<?php
global $servers;

function stv_start($serverid, $pickupid, $winningmap)
{
	global $servers;
	//Start the SourceTV record
	$servers[$serverid]->rcon("tv_record pixelpickup-invite-".$pickupid."-".date("Y-m-d--H-i"));
	$servers[$serverid]->rcon("exec etf2l");
	$servers[$serverid]->rcon("sv_pausable 0");
	if($winningmap == "ctf_turbine_pro_rc1")
	{
		$servers[$serverid]->rcon("mp_winlimit 2");
	}
	//$servers[$serverid]->rcon("hide_server 1");
}

function stv_stop($serverid)
{
	global $servers;
	
	//Send STV stop command
	$servers[$serverid]->rcon("tv_stoprecord");
	//$servers[$serverid]->rcon("hide_server 0");
}

function writepid($serverid)
{
	$PIDFile = "stvwait.".$serverid.".pid";
	$fh = fopen($PIDFile, 'w') or die("can't open file");
	$stringData = getmypid();
	fwrite($fh, $stringData);
	fclose($fh);
}

function readpid($serverid)
{
	$file = "stvwait.".$serverid.".pid";
	$fh = fopen($file, 'r'); 
	$pid = fread($fh, filesize($file)); 
	fclose($fh);
	
	return $pid;
}

//Write this PID for check
writepid($serverid);

//Start STV
sleep(30);
stv_start($serverid, $pickupid, $winningmap);

echo getmypid();

//Sleep for 45mins
echo "\n0 minutes have passed\n";
$counter = 1;

while($counter <= 3)
{
	sleep(60);
	echo $counter . " minutes have passed\n";
	$counter++;
}

	//Send players message to the channel
	$servers[$serverid]->rcon("say ".$playersred);
	$servers[$serverid]->rcon("say ".$playersblu);
	
	echo "Sent player list and provider message\n";
	
	//Send Provider message
	$servers[$serverid]->rcon("say ".$servers[$serverid]->message);

while($counter <= 45)
{
	sleep(60);
	echo $counter . " minutes have passed\n";
	$counter++;
}

//Now read back PID to check it has not changed
if(readpid($serverid) == getmypid())
{
	//Then send the STV record kill command
	stv_stop($serverid);
	echo "Demo recording successfully stopped";
} else
{
	echo "New pickup in progress therefore demo was not stopped";
}

//Kill this process
die();

?>
