<?php

include("servers.php");

$servers[1]->host = "84.236.101.81";
var_export($info = $servers[1]->get_info());


?>