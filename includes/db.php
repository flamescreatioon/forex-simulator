<?php

$servername = "82.197.82.121";
$username = "u877800740_forex";
$password = "Forexapp@2025";
$dbname = "u877800740_forex";

try{
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("Could not connect to the database $dbname :" . $e->getMessage());
}