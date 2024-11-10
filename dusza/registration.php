<?php
    require 'db_connection.php';
    $result_deadline = $conn->query("SELECT date FROM hatarido LIMIT 1");
    $deadline = $result_deadline->fetch_assoc();
    $registration_deadline = strtotime($deadline['date']);
    $current_date = time();
    if ($current_date >= $registration_deadline) {
        echo "";
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = [];

            $required_fields = ['felhasznalonev', 'jelszo', 'csapat_nev', 'iskola_nev', 'iskola_email', 'csapattag1_nev', 'csapattag1_evfolyam', 'csapattag2_nev', 'csapattag2_evfolyam', 'csapattag3_nev', 'csapattag3_evfolyam', 'felkeszito_tanarok', 'kategoria', 'programnyelv'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $errors[] = "A(z) $field mező kitöltése kötelező.";
                }
            }
            if (empty($errors)) {
                $felhasznalonev = $conn->real_escape_string($_POST['felhasznalonev']);
                $jelszo = password_hash($_POST['jelszo'], PASSWORD_BCRYPT);
                $csapat_nev = $conn->real_escape_string($_POST['csapat_nev']);
                $iskola_nev = $conn->real_escape_string($_POST['iskola_nev']);
                $iskola_email = $conn->real_escape_string($_POST['iskola_email']);
                $csapattag1_nev = $conn->real_escape_string($_POST['csapattag1_nev']);
                $csapattag1_evfolyam = (int)$_POST['csapattag1_evfolyam'];
                $csapattag2_nev = $conn->real_escape_string($_POST['csapattag2_nev']);
                $csapattag2_evfolyam = (int)$_POST['csapattag2_evfolyam'];
                $csapattag3_nev = $conn->real_escape_string($_POST['csapattag3_nev']);
                $csapattag3_evfolyam = (int)$_POST['csapattag3_evfolyam'];
                $pot_tag_nev = !empty($_POST['pot_tag_nev']) ? $conn->real_escape_string($_POST['pot_tag_nev']) : NULL;
                $pot_tag_evfolyam = !empty($_POST['pot_tag_evfolyam']) ? (int)$_POST['pot_tag_evfolyam'] : NULL;
                $felkeszito_tanarok = $conn->real_escape_string($_POST['felkeszito_tanarok']);
                $kategoria_id = (int)$_POST['kategoria'];
                $programnyelv_id = (int)$_POST['programnyelv'];
                $sql_check_iskola = "SELECT id FROM iskolak WHERE name = '$iskola_nev'";
                $result_check_iskola = $conn->query($sql_check_iskola);

                if ($result_check_iskola->num_rows == 0) {
                    $sql_isakola = "INSERT INTO iskolak (name, contact_mail) VALUES ('$iskola_nev', '$iskola_email')";
                    if ($conn->query($sql_isakola) === TRUE) {
                        $sql_csapat = "INSERT INTO csapatok (felhasznalonev, jelszo, csapat_nev, iskola_nev, csapattag1_nev, csapattag1_evfolyam, csapattag2_nev, csapattag2_evfolyam, csapattag3_nev, csapattag3_evfolyam, pot_tag_nev, pot_tag_evfolyam, felkeszito_tanarok, kategoria, programnyelv) 
                        VALUES ('$felhasznalonev', '$jelszo', '$csapat_nev', '$iskola_nev', '$csapattag1_nev', $csapattag1_evfolyam, '$csapattag2_nev', $csapattag2_evfolyam, '$csapattag3_nev', $csapattag3_evfolyam, " . ($pot_tag_nev ? "'$pot_tag_nev'" : "NULL") . ", " . ($pot_tag_evfolyam ? $pot_tag_evfolyam : "NULL") . ", '$felkeszito_tanarok', $kategoria_id, $programnyelv_id)";

                        if ($conn->query($sql_csapat) === TRUE) {
                            echo "Sikeres regisztráció!";
                            header("Location: login.php");
                            exit();
                        } else {
                            echo "Hiba a csapat regisztrálása közben: " . $conn->error;
                        }
                    } else {
                        echo "Hiba az iskola adatainak mentésekor: " . $conn->error;
                    }
                } else {
                    $sql_csapat = "INSERT INTO csapatok (felhasznalonev, jelszo, csapat_nev, iskola_nev, csapattag1_nev, csapattag1_evfolyam, csapattag2_nev, csapattag2_evfolyam, csapattag3_nev, csapattag3_evfolyam, pot_tag_nev, pot_tag_evfolyam, felkeszito_tanarok, kategoria, programnyelv) 
                    VALUES ('$felhasznalonev', '$jelszo', '$csapat_nev', '$iskola_nev', '$csapattag1_nev', $csapattag1_evfolyam, '$csapattag2_nev', $csapattag2_evfolyam, '$csapattag3_nev', $csapattag3_evfolyam, " . ($pot_tag_nev ? "'$pot_tag_nev'" : "NULL") . ", " . ($pot_tag_evfolyam ? $pot_tag_evfolyam : "NULL") . ", '$felkeszito_tanarok', $kategoria_id, $programnyelv_id)";

                    if ($conn->query($sql_csapat) === TRUE) {
                        echo "Sikeres regisztráció!";
                        header("Location: login.php");
                        exit();
                    } else {
                        echo "Hiba: " . $conn->error;
                    }
                }
            } else {
                foreach ($errors as $error) {
                    echo "<p>$error</p>";
                }
            }
        }
    }
    $result_kategoriak = $conn->query("SELECT id, name FROM kategoriak");
    $result_nyelvek = $conn->query("SELECT id, name FROM nyelvek");
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <script>
        function clearForm() {
            document.querySelectorAll('input').forEach(input => input.value = ''); 
            document.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        }
    </script>
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <form class="w-100 mt-2" style="max-width: 400px;" method="POST" action="">
        <?php 
            if ($current_date >= $registration_deadline) {
                echo "<h2>Lejárt a regisztrációs határidő!</h2>
                <p class=\"text-center mt-3\"><a href=\"login.php\">Jelentkezzetek be!</a></p>";
            } else {
        ?>
        <div class="form-group">
            <label>Felhasználónév:</label> <input type="text" class="form-control w-100" name="felhasznalonev" required>
        </div>
        <div class="form-group">
            <label>Jelszó:</label> <input type="password" class="form-control w-100" name="jelszo" required>
        </div>
        <div class="form-group">
            <label>Csapat neve:</label> <input type="text" class="form-control w-100" name="csapat_nev" required>
        </div>
        <div class="form-group">
            <label>Iskola neve:</label> <input type="text" class="form-control w-100" name="iskola_nev" required>
        </div>
        <div class="form-group">
            <label>Iskola kapcsolattartó email:</label> <input type="email" class="form-control w-100" name="iskola_email" required>
        </div>
        <div class="form-group">
            <label>Csapattag1 név:</label> <input type="text" class="form-control w-100" name="csapattag1_nev" required>
        </div>
        <div class="form-group">
            <label>Csapattag1 évfolyam:</label> <input type="number" class="form-control w-100" name="csapattag1_evfolyam" required>
        </div>
        <div class="form-group">
            <label>Csapattag2 név:</label> <input type="text" class="form-control w-100" name="csapattag2_nev" required>
        </div>
        <div class="form-group">
            <label>Csapattag2 évfolyam:</label> <input type="number" class="form-control w-100" name="csapattag2_evfolyam" required>
        </div>
        <div class="form-group">
            <label>Csapattag3 név:</label> <input type="text" class="form-control w-100" name="csapattag3_nev" required>
        </div>
        <div class="form-group">
            <label>Csapattag3 évfolyam:</label> <input type="number" class="form-control w-100" name="csapattag3_evfolyam" required>
        </div>
        <div class="form-group">
            <label>Pót tag név:</label> <input type="text" class="form-control w-100" name="pot_tag_nev">
        </div>
        <div class="form-group">
            <label>Pót tag évfolyam:</label> <input type="number" class="form-control w-100" name="pot_tag_evfolyam">
        </div>
        <div class="form-group">
            <label>Felkészítő tanár/tanárok:</label> <input type="text" class="form-control w-100" name="felkeszito_tanarok" required>
        </div>
        <div class="form-group">
            <label>Kategória:</label>
                <select name="kategoria" class="form-control w-100" required>
                    <?php while ($row = $result_kategoriak->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            <br>
        </div>
        <div class="form-group">
            <label>Választott programnyelv:</label>
                <select name="programnyelv" class="form-control w-100" required>
                    <?php while ($row = $result_nyelvek->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            <br>
        </div>
        <button type="submit" class="btn btn-primary btn-block w-100">Regisztráció</button>
        <button type="button" class="btn btn-secondary btn-block w-100 mt-2" onclick="clearForm()">Törlés</button>
        <p class="text-center mt-3">Van fiókotok? <a href="login.php">Jelentkezzetek be!</a></p>
    </form>
    <?php
        }
    ?>
</body>
</html>
<?php $conn->close(); ?>
