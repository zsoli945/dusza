<?php
session_start();
if (!isset($_SESSION['csapat_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connection.php';
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
if (isset($_POST['id'])) {
    $hiany_id = $_POST['id'];
    $delete_sql = "DELETE FROM hianyok WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $hiany_id);
    
    if ($delete_stmt->execute()) {
        echo 'ok';
    } else {
        echo 'no';
    }
    $delete_stmt->close();
}
?>
