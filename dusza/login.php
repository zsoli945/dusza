<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $felhasznalonev = $_POST['felhasznalonev'];
    $jelszo = $_POST['jelszo'];
    require 'db_connection.php';
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }
    $stmt = $conn->prepare("SELECT id, jelszo, type FROM csapatok WHERE felhasznalonev = ?");
    $stmt->bind_param('s', $felhasznalonev);
    $stmt->execute();
    $stmt->bind_result($csapat_id, $hashed_jelszo, $type);
    
    if ($stmt->fetch()) {
        if (password_verify($jelszo, $hashed_jelszo)) {
            $_SESSION['csapat_id'] = $csapat_id;
            echo "Sikeres bejelentkezés!";

            if ($type == 1) {
                header("Location: dashboard.php");
            } else {
                header("Location: team.php");
            }
            exit();
        } else {
            echo "Hibás jelszó!";
        }
    } else {
        $stmt_school = $conn->prepare("SELECT id, password FROM iskolak WHERE username = ?");
        $stmt_school->bind_param('s', $felhasznalonev);
        $stmt_school->execute();
        $stmt_school->bind_result($school_id, $hashed_password);

        if ($stmt_school->fetch()) {
            if (password_verify($jelszo, $hashed_password)) {
                $_SESSION['school_id'] = $school_id;  
                echo "Sikeres bejelentkezés!";
                header("Location: school.php");
                exit();
            } else {
                echo "Hibás jelszó!";
            }
        } else {
            echo "Nincs ilyen felhasználó!";
        }
        $stmt_school->close();
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <form method="POST" action="#" class="w-100" style="max-width: 400px;">
            <div class="form-group">
                <label for="felhasznalonev">Felhasználónév:</label>
                <input class="form-control w-100" type="text" placeholder="Felhasználónév" name="felhasznalonev" id="felhasznalonev" required>
            </div>
            <div class="form-group">
                <label for="jelszo">Jelszó:</label>
                <input class="form-control w-100" type="password" placeholder="Jelszó" name="jelszo" id="jelszo" required>
            </div>
            <button class="btn btn-primary btn-block w-100" type="submit">Bejelentkezés</button>
            <p class="text-center mt-3">A csapatotok még nem <a href="registration.php">regisztrált?</a></p>
        </form>
    </div>
</body>
</html>

