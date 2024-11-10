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
        if (isset($_POST['new_category']) && !empty(trim($_POST['new_category']))) {
            $new_category = trim($_POST['new_category']);
            $stmt = $conn->prepare("INSERT INTO kategoriak (name) VALUES (?)");
            $stmt->bind_param('s', $new_category);
            $stmt->execute();
            $stmt->close();
            displayToast("Új kategória hozzáadva: " . htmlspecialchars($new_category));
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        if (isset($_POST['delete_category_id'])) {
            $delete_category_id = intval($_POST['delete_category_id']);
            $stmt = $conn->prepare("SELECT COUNT(*) FROM csapatok WHERE kategoria = ?");
            $stmt->bind_param('i', $delete_category_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count == 0) {
                $stmt = $conn->prepare("DELETE FROM kategoriak WHERE id = ?");
                $stmt->bind_param('i', $delete_category_id);
                $stmt->execute();
                $stmt->close();
                echo "A kategória sikeresen törölve!";
                displayToast("A kategória sikeresen törölve!");
            } else {
                displayToast("Ez a kategória még használatban van, így nem törölhető!");
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        if (isset($_POST['new_language']) && !empty(trim($_POST['new_language']))) {
            $new_language = trim($_POST['new_language']);
            $stmt = $conn->prepare("INSERT INTO nyelvek (name) VALUES (?)");
            $stmt->bind_param('s', $new_language);
            $stmt->execute();
            $stmt->close();
            displayToast("Új nyelv hozzáadva: " . htmlspecialchars($new_language));
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        if (isset($_POST['delete_id'])) {
            $delete_id = intval($_POST['delete_id']);
            $stmt = $conn->prepare("SELECT COUNT(*) FROM csapatok WHERE programnyelv = ?");
            $stmt->bind_param('i', $delete_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count == 0) {
                $stmt = $conn->prepare("DELETE FROM nyelvek WHERE id = ?");
                $stmt->bind_param('i', $delete_id);
                $stmt->execute();
                $stmt->close();
                displayToast("A programnyelvnyelv sikeresen törölve!");
            } else {
                displayToast("Ez a nyelv használatban van egy csapat által, így nem törölhető!");
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    $kategoriak_result = $conn->query("SELECT id, name FROM kategoriak");
    $nyelvek_result = $conn->query("SELECT id, name FROM nyelvek");
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Verseny Adatok</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="dashboard-users.php">Versenyző Adatok</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="dashboard-schools.php">Iskola Adatok</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="stats.php">Statisztikák</a>
        </li>
        <li class="nav-item ml-2">
            <form action="logout.php" method="POST" class="form-inline">
                <button type="submit" class="btn btn-danger">Kijelentkezés</button>
            </form>
        </li>
      </ul>
    </nav>
    <div class="container">
        <br><br>
        <h2>Kategóriák listája</h2>
        <hr>
        <form method="post" action="">
            <div class="d-flex">
                <input class="form-control mr-2" type="text" name="new_category" placeholder="Új kategória neve" required>
                <button class="btn btn-success" type="submit">Hozzáadás</button>
            </div>
        </form>
        <table class="table table-striped mt-2" border="1">
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Műveletek</th>
            </tr>
            <?php while ($row = $kategoriak_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="delete_category_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-danger" type="submit">Törlés</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br><br>
        <h2>Nyelvek listája</h2>
        <hr>
        <form method="post" action="">
            <div class="d-flex">
                <input class="form-control mr-2" type="text" name="new_language" placeholder="Új nyelv neve" required>
                <button class="btn btn-success" type="submit">Hozzáadás</button>
            </div>
            </form>
        <table class="table table-striped mt-2" border="1">
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Műveletek</th>
            </tr>
            <?php while ($row = $nyelvek_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-danger" type="submit">Törlés</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br><br>
                <?php
                $sql = "SELECT date FROM hatarido WHERE editableindex = 1";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $original_date = $row['date'];
                } else {
                    $original_date = 'Nincs Dátum';
                }
                ?>
            </tbody>
        </table>
        <br><br>
        <h2>Határidő Módosítása</h2>
        <hr>
        <div class="form-row align-items-center">
            <div class="col-auto">
                <input type="text" id="deadline" class="form-control" placeholder="Dátum kiválasztása" />
            </div>
            <div class="col-auto">
                <p>Jelenlegi Határidő: <span id="original_date"><?php echo $original_date; ?></span></p>
            </div>
        </div>
        <br><br>
    <script>
        flatpickr("#deadline", {
            dateFormat: "Y-m-d", 
            onChange: function(selectedDates, dateStr, instance) {
                updateDeadline(dateStr);
            }
        });
        function displayToast(message) {
            $.ajax({
                url: 'toast.php',
                method: 'POST',
                data: { toast_message: message },
                success: function() {
                    $('#toastContainer').load('toast.php #myToast', function() {
                        $('#myToast').toast('show'); 
                    });
                }
            });
        }
        function updateDeadline(newDate) {
            $.ajax({
                url: 'update_date.php',
                type: 'POST',
                data: {
                    deadline: newDate
                },
                success: function(response) {
                    displayToast("A dátum módosítva lett!");
                    $('#original_date').text(newDate); 
                },
                error: function(xhr, status, error) {
                    alert('Hiba történt: ' + error);
                }
            });
        }
    </script>
    </div>
</div>
<?php
    $kategoriak_result->free();
    $nyelvek_result->free();
    $conn->close();
?>
</body>
</html>
