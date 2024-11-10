<?php
$servername = '3.69.30.215';
$username = 'dusza';
$password = 'XkRjHWG8ZrpKQn#h';
$dbname = 'duszadb';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
?>
