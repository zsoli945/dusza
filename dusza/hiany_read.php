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
$sql = "SELECT id, hiany FROM hianyok WHERE csapat_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $csapat_id);
$stmt->execute();
$stmt->bind_result($hiany_id, $hiany);
$hianyok = [];
while ($stmt->fetch()) {
    $hianyok[] = ['id' => $hiany_id, 'hiany' => $hiany];
}
$stmt->close();
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
    <title>Hiányok</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="team.php">Csapat Adatok</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="#">
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
    <div class="container mt-5">
        <?php if (!empty($hianyok)): ?>
            <div class="alert alert-danger">
                <h4>Aktuális hiányok</h4>
                <?php foreach ($hianyok as $hiany): ?>
                    <div class="alert alert-warning" id="hiany-<?php echo $hiany['id']; ?>">
                        <p><?php echo htmlspecialchars($hiany['hiany']); ?></p>
                        <button type="button" class="btn btn-success delete-btn" data-id="<?php echo $hiany['id']; ?>">Javítva</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Nincsenek hiányok.
            </div>
        <?php endif; ?>
    </div>
    <script>
        $(document).ready(function() {
            $('.delete-btn').click(function() {
                var hiany_id = $(this).data('id');
                var $alertBox = $('#hiany-' + hiany_id);
                $.ajax({
                    url: 'delete_hiany.php',
                    type: 'POST',
                    data: { id: hiany_id },
                    success: function(response) {
                        if (response === 'success') {
                            $alertBox.fadeOut();
                        } else {
                            alert('Hiba');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
