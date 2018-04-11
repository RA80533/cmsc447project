<?php

header("Access-Control-Allow-Origin: *");
header("content-type: application/json");
echo file_get_contents($_REQUEST['us-country-zips'].".json");

?>