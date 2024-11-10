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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $team_id = $_POST['team_id'];
    $file = $_FILES['document'];
    if ($file['type'] == 'application/pdf') {
        $upload_dir = 'uploaded_docs/';
        $file_name = basename($file['name']);
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $update_sql = "UPDATE csapatok SET allapot = 2 WHERE id = ?";
            displayToast("A csapat sikeresen visszaigazolva!"); 
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param('i', $team_id);
            $stmt->execute();
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
        }
    }
}
$sql = "SELECT csapatok.id, csapatok.csapat_nev, csapatok.csapattag1_nev, csapatok.csapattag1_evfolyam, 
               csapatok.csapattag2_nev, csapatok.csapattag2_evfolyam, csapatok.csapattag3_nev, csapatok.csapattag3_evfolyam, 
               csapatok.allapot 
        FROM csapatok 
        INNER JOIN iskolak ON csapatok.iskola_nev = iskolak.name 
        WHERE iskolak.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $school_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <title>Csapatok Listája</title>
</head>
<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="school.php">Iskola Adatok</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="#">Csapatok</a>
        </li>
        <li class="nav-item ml-2">
            <form action="logout.php" method="POST" class="form-inline">
                <button type="submit" class="btn btn-danger">Kijelentkezés</button>
            </form>
        </li>
      </ul>
</nav>
<div class="container mt-5">
    <h1>Csapatok Listája</h1>
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Csapat Név</th>
                    <th>Csapattag 1</th>
                    <th>Évfolyam 1</th>
                    <th>Csapattag 2</th>
                    <th>Évfolyam 2</th>
                    <th>Csapattag 3</th>
                    <th>Évfolyam 3</th>
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['csapat_nev']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag1_nev']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag1_evfolyam']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag2_nev']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag2_evfolyam']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag3_nev']); ?></td>
                        <td><?php echo htmlspecialchars($row['csapattag3_evfolyam']); ?></td>
                        <td>
                            <?php if ($row['allapot'] != 2): ?>
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="team_id" value="<?php echo $row['id']; ?>">
                                    <input type="file" class="form-control-file border" name="document" accept="application/pdf" required>
                                    <button type="submit" class="btn btn-success mt-2">Igazolás</button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-success">Visszaigazolva</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Nincs csapat az iskolához tartozóan.</div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
