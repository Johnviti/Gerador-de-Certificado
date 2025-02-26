<?php
$servername = "localhost";
$username = "unidas90_admin";
$password = "4dm1n@2025";
$dbname = "unidas90_certificados";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
