<?php
$host = 'db_server';
$user = 'example_user';
$pass = 'example_password';
$db = 'example_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>