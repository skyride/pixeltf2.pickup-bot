<?php
error_reporting(E_ALL);
include("servers.php");

$info = $servers[1]->get_players();

print_r($info);

echo $info;
?>