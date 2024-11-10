<?php
session_start();
if (!isset($_SESSION['csapat_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connection.php';
require 'toast.php';
$csapat_id = $_SESSION['csapat_id'];
$stmt = $conn->prepare("SELECT type FROM csapatok WHERE id = ?");
$stmt->bind_param("i", $csapat_id);
$stmt->execute();
$stmt->bind_result($type);
$stmt->fetch();
$stmt->close();

if ($type !== 1) {
    header("Location: team.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['new_school_name']) && !empty(trim($_POST['new_school_name']))) {
        $new_school_name = trim($_POST['new_school_name']);
        $new_school_username = trim($_POST['new_school_username']);
        $new_school_password = password_hash(trim($_POST['new_school_password']), PASSWORD_DEFAULT);
        $new_school_address = trim($_POST['new_school_address']);
        $new_school_contact = trim($_POST['new_school_contact']);
        $new_school_contact_mail = trim($_POST['new_school_contact_mail']);

        $stmt = $conn->prepare("INSERT INTO iskolak (name, username, password, address, contact, contact_mail) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $new_school_name, $new_school_username, $new_school_password, $new_school_address, $new_school_contact, $new_school_contact_mail);
        if ($stmt->execute()) {
            echo "Az iskola sikeresen hozzáadva!";
        } else {
            echo "Hiba történt az iskola hozzáadása közben: " . $stmt->error;
        }
        $stmt->close();
    }

    if (isset($_POST['delete_school_id'])) {
        $delete_school_id = intval($_POST['delete_school_id']);
        $stmt = $conn->prepare("DELETE FROM iskolak WHERE id = ?");
        $stmt->bind_param('i', $delete_school_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['edit_school_id'])) {
        $edit_school_id = intval($_POST['edit_school_id']);
        $new_name = $_POST['edit_name'];
        $new_username = $_POST['edit_username'];
        $new_address = $_POST['edit_address'];
        $new_contact = $_POST['edit_contact'];
        $new_contact_mail = $_POST['edit_contact_mail'];
        if (!empty(trim($_POST['edit_password']))) {
            $new_password = password_hash(trim($_POST['edit_password']), PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE iskolak SET name = ?, username = ?, password = ?, address = ?, contact = ?, contact_mail = ? WHERE id = ?");
            $stmt->bind_param('ssssssi', $new_name, $new_username, $new_password, $new_address, $new_contact, $new_contact_mail, $edit_school_id);
        } else {
            $stmt = $conn->prepare("UPDATE iskolak SET name = ?, username = ?, address = ?, contact = ?, contact_mail = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $new_name, $new_username, $new_address, $new_contact, $new_contact_mail, $edit_school_id);
        }
        if ($stmt->execute()) {
            echo "Az iskola adatai sikeresen módosítva!";
            displayToast("Az iskola adatai sikeresen módosítva!");
            $stmt->close();
            
            $stmt = $conn->prepare("UPDATE csapatok SET iskola_nev = ? WHERE iskola_nev = ?");
            $stmt->bind_param('ss', $new_name, $_POST['old_name']);
            $stmt->execute();
        } else {
            echo "Hiba történt az iskola adatai módosítása közben: " . $stmt->error;
        }
        $stmt->close();
    }
}

$schools_result = $conn->query("SELECT id, name, username, address, contact, contact_mail FROM iskolak");
$edit_school = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM iskolak WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $edit_school_result = $stmt->get_result();
    $edit_school = $edit_school_result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Iskolák</title>
</head>
<body>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Verseny Adatok</a></li>
            <li class="nav-item"><a class="nav-link" href="dashboard-users.php">Versenyző Adatok</a></li>
            <li class="nav-item active"><a class="nav-link" href="dashboard-schools.php">Iskola Adatok</a></li>
            <li class="nav-item"><a class="nav-link" href="stats.php">Statisztikák</a></li>
            <li class="nav-item ml-2">
                <form action="logout.php" method="POST" class="form-inline">
                    <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                </form>
            </li>
        </ul>
    </nav>
    <div class="container">
        <br><br>
        <h2>Iskola Hozzáadása</h2>
        <hr>
        <form method="post" action="">
            <div class="form-group">
                <label for="new_school_name">Iskola neve:</label>
                <input type="text" class="form-control" name="new_school_name" placeholder="Iskola neve" required>
            </div>
            <div class="form-group">
                <label for="new_school_username">Felhasználónév:</label>
                <input type="text" class="form-control" name="new_school_username" placeholder="Felhasználónév" required>
            </div>
            <div class="form-group">
                <label for="new_school_password">Jelszó:</label>
                <input type="password" class="form-control" name="new_school_password" placeholder="Jelszó" required>
            </div>
            <div class="form-group">
                <label for="new_school_address">Cím:</label>
                <input type="text" class="form-control" name="new_school_address" placeholder="Cím" required>
            </div>
            <div class="form-group">
                <label for="new_school_contact">Kapcsolattartó:</label>
                <input type="text" class="form-control" name="new_school_contact" placeholder="Kapcsolattartó" required>
            </div>
            <div class="form-group">
                <label for="new_school_contact_mail">Kapcsolattartó e-mail:</label>
                <input type="email" class="form-control" name="new_school_contact_mail" placeholder="E-mail" required>
            </div>
            <button class="btn btn-success" type="submit">Iskola hozzáadása</button>
        </form>
        <br><br>
        <h2>Iskolák listája:</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Iskola neve</th>
                    <th>Felhasználónév</th>
                    <th>Cím</th>
                    <th>Kapcsolattartó</th>
                    <th>E-mail</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schools_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_mail']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="delete_school_id" value="<?php echo $row['id']; ?>">
                            <button class="btn btn-danger" type="submit">Törlés</button>
                        </form>
                        <form method="post">
                            <a href="dashboard-schools.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-warning mt-2">Módosítás</a>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($edit_school): ?>
        <h2>Adatok Módosítása</h2>
        <form method="POST" action="">
            <input type="hidden" name="edit_school_id" value="<?php echo $edit_school['id']; ?>">
            <input type="hidden" name="old_name" value="<?php echo $edit_school['name']; ?>">
            <div class="form-group">
                <label for="edit_name">Iskola neve:</label>
                <input type="text" class="form-control" name="edit_name" value="<?php echo htmlspecialchars($edit_school['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_username">Felhasználónév:</label>
                <input type="text" class="form-control" name="edit_username" value="<?php echo htmlspecialchars($edit_school['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_password">Új jelszó (ha módosítani szeretnéd):</label>
                <input type="password" class="form-control" name="edit_password" placeholder="Új jelszó (opcionális)">
            </div>
            <div class="form-group">
                <label for="edit_address">Cím:</label>
                <input type="text" class="form-control" name="edit_address" value="<?php echo htmlspecialchars($edit_school['address']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_contact">Kapcsolattartó:</label>
                <input type="text" class="form-control" name="edit_contact" value="<?php echo htmlspecialchars($edit_school['contact']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_contact_mail">Kapcsolattartó e-mail:</label>
                <input type="email" class="form-control" name="edit_contact_mail" value="<?php echo htmlspecialchars($edit_school['contact_mail']); ?>" required>
            </div>
            <button class="btn btn-success" type="submit">Módosítás</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
