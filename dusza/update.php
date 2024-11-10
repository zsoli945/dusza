<?php
    session_start();
    if (!isset($_SESSION['csapat_id'])) {
        die("Be kell jelentkezned a módosításhoz.");
    }
    $csapat_id = $_SESSION['csapat_id'];
    require 'db_connection.php';
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }
    $csapattag1_nev = $conn->real_escape_string($_POST['csapattag1_nev']);
    $csapattag1_evfolyam = $conn->real_escape_string($_POST['csapattag1_evfolyam']);
    $csapattag2_nev = $conn->real_escape_string($_POST['csapattag2_nev']);
    $csapattag2_evfolyam = $conn->real_escape_string($_POST['csapattag2_evfolyam']);
    $csapattag3_nev = $conn->real_escape_string($_POST['csapattag3_nev']);
    $csapattag3_evfolyam = $conn->real_escape_string($_POST['csapattag3_evfolyam']);
    $pot_tag_nev = $conn->real_escape_string($_POST['pot_tag_nev']);
    $pot_tag_evfolyam = $conn->real_escape_string($_POST['pot_tag_evfolyam']);
    $felkeszito_tanarok = $conn->real_escape_string($_POST['felkeszito_tanarok']);
    $kategoria = $conn->real_escape_string($_POST['kategoria']);
    $programnyelv = $conn->real_escape_string($_POST['programnyelv']);
    $sql = "UPDATE csapatok SET 
                csapattag1_nev = '$csapattag1_nev', 
                csapattag1_evfolyam = '$csapattag1_evfolyam', 
                csapattag2_nev = '$csapattag2_nev', 
                csapattag2_evfolyam = '$csapattag2_evfolyam', 
                csapattag3_nev = '$csapattag3_nev', 
                csapattag3_evfolyam = '$csapattag3_evfolyam', 
                pot_tag_nev = '$pot_tag_nev', 
                pot_tag_evfolyam = '$pot_tag_evfolyam', 
                felkeszito_tanarok = '$felkeszito_tanarok', 
                kategoria = '$kategoria', 
                programnyelv = '$programnyelv' 
            WHERE id = $csapat_id";
    if ($conn->query($sql) === TRUE) {
        echo "Sikeres módosítás!";
    } else {
        echo "Hiba történt: " . $conn->error;
    }
    $conn->close();
?>
