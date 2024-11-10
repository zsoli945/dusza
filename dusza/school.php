<?php
session_start();
if (!isset($_SESSION['school_id'])) {
    header("Location: login.php");
    exit();
}
$school_id = $_SESSION['school_id'];
require 'db_connection.php';
require 'toast.php';
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_school_name = $_POST['new_school_name'];
    $new_school_username = $_POST['new_school_username'];
    $new_school_password = $_POST['new_school_password'];
    $new_school_address = $_POST['new_school_address'];
    $new_school_contact = $_POST['new_school_contact'];
    $new_school_contact_mail = $_POST['new_school_contact_mail'];
    if (!empty($new_school_password)) {
        $hashed_password = password_hash($new_school_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE iskolak 
                       SET name = ?, username = ?, password = ?, address = ?, contact = ?, contact_mail = ? 
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssssssi', $new_school_name, $new_school_username, $hashed_password, $new_school_address, $new_school_contact, $new_school_contact_mail, $school_id);
    } else {
        $update_sql = "UPDATE iskolak 
                       SET name = ?, username = ?, address = ?, contact = ?, contact_mail = ? 
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sssssi', $new_school_name, $new_school_username, $new_school_address, $new_school_contact, $new_school_contact_mail, $school_id);
    }
    if ($stmt->execute()) {
        $success_message = "Az iskola adatai sikeresen frissítve!";
        $update_teams_sql = "UPDATE csapatok SET iskola_nev = ? WHERE iskola_nev = ?";
        $stmt = $conn->prepare($update_teams_sql);
        $stmt->bind_param('ss', $new_school_name, $_POST['old_school_name']);
        $stmt->execute();
        
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
    } else {
        $error_message = "Hiba történt az adatok frissítése közben!";
    }
}
$sql = "SELECT name, username, address, contact, contact_mail FROM iskolak WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $school_id);
$stmt->execute();
$stmt->bind_result($school_name, $school_username, $school_address, $school_contact, $school_contact_mail);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <title>Iskola Adatok</title>
</head>
<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Iskola Adatok</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="school_teams.php">Csapatok</a>
        </li>
        <li class="nav-item ml-2">
            <form action="logout.php" method="POST" class="form-inline">
                <button type="submit" class="btn btn-danger">Kijelentkezés</button>
            </form>
        </li>
      </ul>
    </nav>
<div class="container mt-5">
        <h1>Adatok Módosítása</h1>
        <?php if (isset($success_message)): ?>
        <?php displayToast("Az iskola adatai sikeresen módosítva!"); ?>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="old_school_name" value="<?php echo htmlspecialchars($school_name); ?>">
            <div class="form-group">
                <label for="new_school_name">Iskola neve:</label>
                <input type="text" class="form-control" name="new_school_name" value="<?php echo htmlspecialchars($school_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_school_username">Felhasználónév:</label>
                <input type="text" class="form-control" name="new_school_username" value="<?php echo htmlspecialchars($school_username); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_school_password">Jelszó:</label>
                <input type="password" class="form-control" name="new_school_password" placeholder="Új jelszó (ha módosítani szeretnéd)">
            </div>
            <div class="form-group">
                <label for="new_school_address">Cím:</label>
                <input type="text" class="form-control" name="new_school_address" value="<?php echo htmlspecialchars($school_address); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_school_contact">Kapcsolattartó:</label>
                <input type="text" class="form-control" name="new_school_contact" value="<?php echo htmlspecialchars($school_contact); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_school_contact_mail">Kapcsolattartó e-mail:</label>
                <input type="email" class="form-control" name="new_school_contact_mail" value="<?php echo htmlspecialchars($school_contact_mail); ?>" required>
            </div>
            <button type="submit" class="btn btn-warning">Módosítás</button>
        </form>
    </div>
</body>
</html>
