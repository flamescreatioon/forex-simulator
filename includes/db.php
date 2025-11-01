<?php

$servername = "82.197.82.121";
$username = "u877800740_forex";
$password = "Forexapp@2025";
$dbname = "u877800740_forex";

try{
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    // Don't die - let the app run in session-only mode
    // This allows the forex app to work even when DB connection limit is exceeded
    $pdo = null;
    // You can uncomment this to see the error in logs:
    // error_log("Database connection failed: " . $e->getMessage());
}