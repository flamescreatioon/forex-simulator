<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "volatility";

try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("Could not connect to the database $dbname :" . $e->getMessage());
}