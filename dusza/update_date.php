<?php
require 'db_connection.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deadline'])) {
    $deadline = $conn->real_escape_string($_POST['deadline']);
    $sql = "UPDATE hatarido SET date = '$deadline' WHERE editableindex = 1";
    if ($conn->query($sql) === TRUE) {
        echo "OK!";
    } else {
        echo "Hiba történt: " . $conn->error;
    }
}
?>
