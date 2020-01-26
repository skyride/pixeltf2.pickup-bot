<?php
//Pixel Gaming Pickup Bot
//Pickup Server List

//TF2 Server Class

include_once("class_PQ.php");

class server
{
	public $server;
	public $host;
    public $port;
	public $rconpass;
	public $connect;
	public $message;
	public $on;
	
    public function init($host, $port, $rconpass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->rconpass = $rconpass;
        
        //Create connect command
		$this->connect = "connect ".$host.":".$port." ; password pixelpickup";
		
		//Mark Server on
		$this->on = true;
    }
	
    public function is_empty()
    {
        $returnvalue = false;

        //Create query object
        $pq = PQ::create($conf);

        //Get info
        $info = $pq->query_info($this->host.":".$this->port);

        //Check player count
        if($info['totalplayers'] > 2)
        {
            $returnvalue = false;
        } else
        {
            $returnvalue = true;
        }

        return $returnvalue;
    }
	
	public function get_info()
	{
        //Create query object
        $pq = PQ::create($conf);

        //Get info
        $info = $pq->query_info($this->host.":".$this->port);

		//Return Info
        return $info;
	}
	
	public function get_players()
	{
        //Create query object
        $pq = PQ::create($conf);

        //Get info
        $info = $pq->query_players($this->host.":".$this->port);

		//Return Info
        return $info;
	}
    
    public function changelevel($map)
    {
    	$this->rcon("changelevel ".$map);
    }
    
    public function rcon($command)
    {
        //Create query object
        $pq = PQ::create($conf);

        //Get info
        $output = $pq->query_rcon2($command, $this->rconpass, $this->host.":".$this->port);
		echo $output;
		return $output;
    }
};

//Add Server Function
/*function addserver($ip, $port, $rcon, $message)
{
	global $servers;
		
	$counter = count($servers) + 1;
		
	$servers[$counter] = new server;
	$servers[$counter]->init($ip, $port, $rcon);
	$servers[$counter]->message = $message;

	//Return Server ID
	return $counter;
}*/


//Run Servers Config
include("serverlist.php");
?>