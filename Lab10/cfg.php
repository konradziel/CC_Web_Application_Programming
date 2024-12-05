<?php
/**
 * Konfiguracja aplikacji
 * Podstawowe ustawienia połączenia z bazą danych i dane administratora
 */

// Ustawienia bazy danych
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza = 'moja_strona';

// Dane administratora
if (!defined('ADMIN_LOGIN')) {
    define('ADMIN_LOGIN', 'admin');
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'password123');
}

// Inicjalizacja sesji z podstawowymi zabezpieczeniami
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Połączenie z bazą danych
$conn = new mysqli($dbhost, $dbuser, $dbpass, $baza);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<b>Błąd połączenia z bazą danych: </b>' . htmlspecialchars($conn->connect_error));
}
?>