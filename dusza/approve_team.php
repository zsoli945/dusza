<?php
session_start();
require 'db_connection.php';
require 'toast.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_id'])) {
    $approve_id = intval($_POST['approve_id']);
    $sql = "UPDATE csapatok SET allapot = 3 WHERE id = $approve_id";
    if ($conn->query($sql) === TRUE) {
        echo "Szervezők Által Jóváhagyva";
    } else {
        echo "Hiba történt: " . $conn->error;
    }
}
?>
