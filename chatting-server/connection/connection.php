<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
$apiKey="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";
$model="gpt-4o-mini";
$connection = new mysqli ("localhost", "root","","chatting-app");

if ($connection -> connect_error){
    die ("connection error:" . $connection-> connect_error);
}

?>