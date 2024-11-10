<?php
require 'db_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csapat_id']) && isset($_POST['hiany'])) {
    $csapat_id = intval($_POST['csapat_id']);
    $hiany = $conn->real_escape_string(trim($_POST['hiany']));
    $sql = "INSERT INTO hianyok (csapat_id, hiany) VALUES ($csapat_id, '$hiany')";
    if ($conn->query($sql) === TRUE) {
        echo "Hiányjelentés sikeresen mentve!";
    } else {
        echo "Hiba történt: " . $conn->error;
    }
}
?>
