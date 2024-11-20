<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

if (!defined('ADMIN_LOGIN')) {
    define('ADMIN_LOGIN', 'admin'); // Login administratora
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'password123'); // Hasło administratora
}

$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);

if ($conn->connect_error) {
    die('<b>Połączenie zostało przerwane: </b>' . $conn->connect_error);
}
?>