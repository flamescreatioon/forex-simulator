<?php

$servername = "82.197.82.121";
$username = "u877800740_forex";
$password = "Forexapp@2025";
$dbname = "u877800740_forex";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";

?>