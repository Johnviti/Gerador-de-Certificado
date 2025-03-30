<?php
$servername = "localhost";
$username = "unidas90_Leandro";
$password = "Le@ndro2101";
$dbname = "unidas90_comissoescertificados";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
