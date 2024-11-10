<?php
    session_start();
    if (!isset($_SESSION['csapat_id'])) {
        header("Location: login.php");
        exit();
    }
    $csapat_id = $_SESSION['csapat_id'];
    require 'db_connection.php';
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }
    $stmt = $conn->prepare("SELECT csapattag1_nev, csapattag1_evfolyam, csapattag2_nev, csapattag2_evfolyam, csapattag3_nev, csapattag3_evfolyam, pot_tag_nev, pot_tag_evfolyam, felkeszito_tanarok, kategoria, programnyelv FROM csapatok WHERE id = ?");
    $stmt->bind_param('i', $csapat_id);
    $stmt->execute();
    $stmt->bind_result($csapattag1_nev, $csapattag1_evfolyam, $csapattag2_nev, $csapattag2_evfolyam, $csapattag3_nev, $csapattag3_evfolyam, $pot_tag_nev, $pot_tag_evfolyam, $felkeszito_tanarok, $kategoria, $programnyelv);
    $stmt->fetch();
    $stmt->close();
    $nyelvek_query = "SELECT id, name FROM nyelvek";
    $nyelvek_result = $conn->query($nyelvek_query);
    $kategoriak_query = "SELECT id, name FROM kategoriak";
    $kategoriak_result = $conn->query($kategoriak_query);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    }
    $sql = "SELECT COUNT(*) FROM hianyok WHERE csapat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $csapat_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $hasNotification = $count > 0;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adatok módosítása</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Csapat Adatok</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="hiany_read.php">
            Hiánypótlás
            <?php if ($hasNotification): ?>
                    <span class="notification-dot"></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item ml-2">
            <form action="logout.php" method="POST" class="form-inline">
                <button type="submit" class="btn btn-danger">Kijelentkezés</button>
            </form>
        </li>
      </ul>
    </nav>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <form method="POST" action="">
            <label>Csapattag1 név: <input class="form-control" type="text" name="csapattag1_nev" value="<?php echo htmlspecialchars($csapattag1_nev); ?>" required></label><br>
            <label>Csapattag1 évfolyam: <input class="form-control" type="number" name="csapattag1_evfolyam" value="<?php echo htmlspecialchars($csapattag1_evfolyam); ?>" required></label><br>
            <label>Csapattag2 név: <input class="form-control" type="text" name="csapattag2_nev" value="<?php echo htmlspecialchars($csapattag2_nev); ?>" required></label><br>
            <label>Csapattag2 évfolyam: <input class="form-control" type="number" name="csapattag2_evfolyam" value="<?php echo htmlspecialchars($csapattag2_evfolyam); ?>" required></label><br>
            <label>Csapattag3 név: <input class="form-control" type="text" name="csapattag3_nev" value="<?php echo htmlspecialchars($csapattag3_nev); ?>" required></label><br>
            <label>Csapattag3 évfolyam: <input class="form-control" type="number" name="csapattag3_evfolyam" value="<?php echo htmlspecialchars($csapattag3_evfolyam); ?>" required></label><br>
            <label>Pót tag név: <input class="form-control" type="text" name="pot_tag_nev" value="<?php echo htmlspecialchars($pot_tag_nev); ?>"></label><br>
            <label>Pót tag évfolyam: <input class="form-control" type="number" name="pot_tag_evfolyam" value="<?php echo htmlspecialchars($pot_tag_evfolyam); ?>"></label><br>
            <label>Felkészítő tanár/tanárok: <input class="form-control" type="text" name="felkeszito_tanarok" value="<?php echo htmlspecialchars($felkeszito_tanarok); ?>" required></label><br>
            <label>Kategória:
            <select class="form-control" name="kategoria" required>
                    <?php
                    while ($kategoriak = $kategoriak_result->fetch_assoc()) {
                        $selected = $kategoriak['id'] == $kategoria ? 'selected' : '';
                        echo "<option value='{$kategoriak['id']}' $selected>{$kategoriak['name']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Választott programnyelv:
                <select class="form-control" name="programnyelv" required>
                    <?php
                    while ($nyelv = $nyelvek_result->fetch_assoc()) {
                        $selected = $nyelv['id'] == $programnyelv ? 'selected' : '';
                        echo "<option value='{$nyelv['id']}' $selected>{$nyelv['name']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <button class="btn btn-warning" type="submit">Módosítás</button>
        </form>
    </div>
</body>
</html>
