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
    $programnyelvek = $conn->query("SELECT id, name FROM nyelvek");
    $kategoriak = $conn->query("SELECT id, name FROM kategoriak");

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id']) && isset($_POST['felhasznalonev'])) {
        $edit_id = intval($_POST['edit_id']);
        $felhasznalonev = isset($_POST['felhasznalonev']) ? $conn->real_escape_string(trim($_POST['felhasznalonev'])) : '';
        $csapat_nev = isset($_POST['csapat_nev']) ? $conn->real_escape_string(trim($_POST['csapat_nev'])) : '';
        $iskola_nev = isset($_POST['iskola_nev']) ? $conn->real_escape_string(trim($_POST['iskola_nev'])) : '';
        $csapattag1_nev = isset($_POST['csapattag1_nev']) ? $conn->real_escape_string(trim($_POST['csapattag1_nev'])) : '';
        $csapattag1_evfolyam = isset($_POST['csapattag1_evfolyam']) && is_numeric($_POST['csapattag1_evfolyam']) ? $_POST['csapattag1_evfolyam'] : 0;
        $csapattag2_nev = isset($_POST['csapattag2_nev']) ? $conn->real_escape_string(trim($_POST['csapattag2_nev'])) : '';
        $csapattag2_evfolyam = isset($_POST['csapattag2_evfolyam']) && is_numeric($_POST['csapattag2_evfolyam']) ? $_POST['csapattag2_evfolyam'] : 0;
        $csapattag3_nev = isset($_POST['csapattag3_nev']) ? $conn->real_escape_string(trim($_POST['csapattag3_nev'])) : '';
        $csapattag3_evfolyam = isset($_POST['csapattag3_evfolyam']) && is_numeric($_POST['csapattag3_evfolyam']) ? $_POST['csapattag3_evfolyam'] : 0;
        $pot_tag_nev = isset($_POST['pot_tag_nev']) ? $conn->real_escape_string(trim($_POST['pot_tag_nev'])) : '';
        $pot_tag_evfolyam = isset($_POST['pot_tag_evfolyam']) && is_numeric($_POST['pot_tag_evfolyam']) ? $_POST['pot_tag_evfolyam'] : 0;
        $felkeszito_tanarok = isset($_POST['felkeszito_tanarok']) ? $conn->real_escape_string(trim($_POST['felkeszito_tanarok'])) : '';
        $programnyelv = isset($_POST['programnyelv']) && is_numeric($_POST['programnyelv']) ? (int)$_POST['programnyelv'] : 0;
        $kategoria = isset($_POST['kategoria']) && is_numeric($_POST['kategoria']) ? (int)$_POST['kategoria'] : 0;
        $type = isset($_POST['type']) ? $conn->real_escape_string(trim($_POST['type'])) : 0;

        $sql = "UPDATE csapatok SET 
        felhasznalonev = '$felhasznalonev',
        csapat_nev = '$csapat_nev',
        iskola_nev = '$iskola_nev',
        csapattag1_nev = '$csapattag1_nev',
        csapattag1_evfolyam = $csapattag1_evfolyam,
        csapattag2_nev = '$csapattag2_nev',
        csapattag2_evfolyam = $csapattag2_evfolyam,
        csapattag3_nev = '$csapattag3_nev',
        csapattag3_evfolyam = $csapattag3_evfolyam,
        pot_tag_nev = '$pot_tag_nev',
        pot_tag_evfolyam = $pot_tag_evfolyam,
        felkeszito_tanarok = '$felkeszito_tanarok',
        programnyelv = $programnyelv,
        kategoria = $kategoria,
        type = '$type'
            WHERE id = $edit_id";
    if ($conn->query($sql) === TRUE) {
        echo "Adatok sikeresen frissítve!";
    } else {
        echo "Hiba történt: " . $conn->error;
    }
}
$result = $conn->query("
    SELECT csapatok.*, kategoriak.name AS kategoria_nev, nyelvek.name AS programnyelv_nev, allapotok.name AS allapot_nev
    FROM csapatok
    LEFT JOIN kategoriak ON csapatok.kategoria = kategoriak.id
    LEFT JOIN nyelvek ON csapatok.programnyelv = nyelvek.id
    LEFT JOIN allapotok ON csapatok.allapot = allapotok.id
");
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_id'])) {
    $approve_id = intval($_POST['approve_id']);
    $sql = "UPDATE csapatok SET allapot = 3 WHERE id = $approve_id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Csapat állapota sikeresen frissítve!";
    } else {
        echo "Hiba történt a frissítés során: " . $conn->error;
    }
}
//csv
if (isset($_POST['export_csv'])) {
    $result = $conn->query("
        SELECT csapatok.*, kategoriak.name AS kategoria_nev, nyelvek.name AS programnyelv_nev
        FROM csapatok
        LEFT JOIN kategoriak ON csapatok.kategoria = kategoriak.id
        LEFT JOIN nyelvek ON csapatok.programnyelv = nyelvek.id
    ");
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="versenyzo_adatok.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Felhasználónév', 'Csapat név', 'Iskola név', 'Csapattagok', 'Programnyelv', 'Kategória']);
    while ($row = $result->fetch_assoc()) {
        $csapattagok = [
            $row['csapattag1_nev'],
            $row['csapattag2_nev'],
            $row['csapattag3_nev']
        ];
        fputcsv($output, [
            $row['id'],
            $row['felhasznalonev'],
            $row['csapat_nev'],
            $row['iskola_nev'],
            implode(", ", $csapattagok),
            $row['programnyelv_nev'],
            $row['kategoria_nev']
        ]);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Versenyzők Adatai</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-sm bg-light navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" href="dashboard.php">Verseny Adatok</a>
    </li>
    <li class="nav-item active">
      <a class="nav-link" href="#">Versenyző Adatok</a>
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
<div class="modal fade" id="hianyModal" tabindex="-1" role="dialog" aria-labelledby="hianyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hianyModalLabel">Hiányjelentés</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="hianyForm" method="POST">
                    <input type="hidden" name="csapat_id" id="csapat_id">
                    <div class="form-group">
                        <label for="hiany">Hiány megjegyzés:</label>
                        <textarea class="form-control" name="hiany" id="hiany" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Küldés</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="container mt-4">
  <h2>Versenyzők Adatai</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Felhasználónév</th>
                <th>Csapat név</th>
                <th>Iskola név</th>
                <th>Csapattagok</th>
                <th>Programnyelv</th>
                <th>Kategória</th>
                <th>Műveletek</th>
                <th>Állapot</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['felhasznalonev']); ?></td>
                <td><?php echo htmlspecialchars($row['csapat_nev']); ?></td>
                <td><?php echo htmlspecialchars($row['iskola_nev']); ?></td>
                <td>
                    <?php echo htmlspecialchars($row['csapattag1_nev']); ?>, 
                    <?php echo htmlspecialchars($row['csapattag2_nev']); ?>, 
                    <?php echo htmlspecialchars($row['csapattag3_nev']); ?>
                </td>
                <td><?php echo htmlspecialchars($row['programnyelv_nev']); ?></td>
                <td><?php echo htmlspecialchars($row['kategoria_nev']); ?></td>
                <td>
                    <form action="" onsubmit="return approveTeam(<?php echo $row['id']; ?>);">
                        <button class="btn btn-success mt-2" type="button" onclick="approveTeam(<?php echo $row['id']; ?>)">Jóváhagyás</button>
                    </form>
                    <form class="mt-2" method="post" action="">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-warning" type="submit">Módosítás</button>
                    </form>
                    <form class="mt-2" method="post" action="">
                        <button class="btn btn-danger" type="button" onclick="openHianyModal(<?php echo $row['id']; ?>)">Hiányjelentés</button>
                    </form>
                </td>
                <td id="allapot_<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['allapot_nev']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php if (isset($_POST['edit_id']) && !isset($_POST['felhasznalonev'])): ?>
    <h3>Adatok módosítása</h3>
    <?php 
    $edit_id = $_POST['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM csapatok WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    ?>
 <form method="POST" action="">
        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
        <div class="form-group">
            <label for="felhasznalonev">Felhasználónév:</label>
            <input type="text" class="form-control" id="felhasznalonev" name="felhasznalonev" value="<?php echo htmlspecialchars($row['felhasznalonev']); ?>" required>
        </div>
        <div class="form-group">
            <label for="csapat_nev">Csapat neve:</label>
            <input type="text" class="form-control" id="csapat_nev" name="csapat_nev" value="<?php echo htmlspecialchars($row['csapat_nev']); ?>" required>
        </div>
        <div class="form-group">
            <label for="iskola_nev">Iskola neve:</label>
            <input type="text" class="form-control" id="iskola_nev" name="iskola_nev" value="<?php echo htmlspecialchars($row['iskola_nev']); ?>" required>
        </div>
    <div class="form-group">
        <label for="csapattag1_nev">Csapattag 1 neve:</label>
        <input type="text" class="form-control" name="csapattag1_nev" value="<?php echo htmlspecialchars($row['csapattag1_nev']); ?>" required>
    </div>
    <div class="form-group">
        <label for="csapattag1_evfolyam">Csapattag 1 évfolyam:</label>
        <input type="text" class="form-control" name="csapattag1_evfolyam" value="<?php echo htmlspecialchars($row['csapattag1_evfolyam']); ?>" required>
    </div>
    <div class="form-group">
        <label for="csapattag2_nev">Csapattag 2 neve:</label>
        <input type="text" class="form-control" name="csapattag2_nev" value="<?php echo htmlspecialchars($row['csapattag2_nev']); ?>" required>
    </div>
    <div class="form-group">
        <label for="csapattag2_evfolyam">Csapattag 2 évfolyam:</label>
        <input type="text" class="form-control" name="csapattag2_evfolyam" value="<?php echo htmlspecialchars($row['csapattag2_evfolyam']); ?>" required>
    </div>
    <div class="form-group">
        <label for="csapattag3_nev">Csapattag 3 neve:</label>
        <input type="text" class="form-control" name="csapattag3_nev" value="<?php echo htmlspecialchars($row['csapattag3_nev']); ?>" required>
    </div>
    <div class="form-group">
        <label for="csapattag3_evfolyam">Csapattag 3 évfolyam:</label>
        <input type="text" class="form-control" name="csapattag3_evfolyam" value="<?php echo htmlspecialchars($row['csapattag3_evfolyam']); ?>" required>
    </div>
    <div class="form-group">
        <label for="pot_tag_nev">Póttag neve:</label>
        <input type="text" class="form-control" name="pot_tag_nev" value="<?php echo htmlspecialchars($row['pot_tag_nev']); ?>">
    </div>
    <div class="form-group">
        <label for="pot_tag_evfolyam">Póttag évfolyam:</label>
        <input type="text" class="form-control" name="pot_tag_evfolyam" value="<?php echo htmlspecialchars($row['pot_tag_evfolyam']); ?>">
    </div>
    <div class="form-group">
        <label for="felkeszito_tanarok">Felkészítő tanárok:</label>
        <input type="text" class="form-control" name="felkeszito_tanarok" value="<?php echo htmlspecialchars($row['felkeszito_tanarok']); ?>" required>
    </div>
    <div class="form-group">
    <label for="programnyelv">Programnyelv:</label>
    <select class="form-control" name="programnyelv">
        <?php 
        mysqli_data_seek($programnyelvek, 0);
        while ($nyelv = $programnyelvek->fetch_assoc()): ?>
            <option value="<?php echo $nyelv['id']; ?>" 
                <?php echo ($row['programnyelv'] == $nyelv['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($nyelv['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
<div class="form-group">
    <label for="kategoria">Kategória:</label>
    <select class="form-control" name="kategoria">
        <?php 
        mysqli_data_seek($kategoriak, 0);
        while ($kategoria = $kategoriak->fetch_assoc()): ?>
            <option value="<?php echo $kategoria['id']; ?>" 
                <?php echo ($row['kategoria'] == $kategoria['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($kategoria['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
    <div class="form-group">
        <label for="type">Típus:</label>
        <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($row['type']); ?>" required>
    </div>
    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
    <button type="submit" class="btn btn-success mb-2">Módosítás</button>
</form>
    <?php endif; ?>
    <form method="post" action="">
    <button class="btn btn-primary" type="submit" name="export_csv">Export CSV</button>
</form>
</div>
<script>//ajax az állapot jóváhagyásához
function approveTeam(teamId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "approve_team.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('allapot_' + teamId).innerHTML = xhr.responseText;
        }
    };
    xhr.send("approve_id=" + teamId);
    return false;
}
</script>
<script>//hianybejelentés
    function openHianyModal(csapat_id) {
        document.getElementById('csapat_id').value = csapat_id;
        $('#hianyModal').modal('show');
    }
    $('#hianyForm').on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();
        $.post('hiany_save.php', formData, function(response) {
            $('#hianyModal').modal('hide');
            alert(response);
        });
    });
</script>
</body>
</html>
