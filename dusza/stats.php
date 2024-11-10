<?php
session_start();
if (!isset($_SESSION['csapat_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connection.php';
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
$query_prog_lang = "
    SELECT n.name AS programnyelv, COUNT(*) AS count 
    FROM csapatok c
    INNER JOIN nyelvek n ON c.programnyelv = n.id
    GROUP BY n.name
";
$query_category = "
    SELECT k.name AS kategoria, COUNT(*) AS count 
    FROM csapatok c
    INNER JOIN kategoriak k ON c.kategoria = k.id
    GROUP BY k.name
";
$query_schools = "
    SELECT csapatok.iskola_nev, COUNT(*) AS count 
    FROM csapatok 
    GROUP BY csapatok.iskola_nev
";

$result_prog_lang = mysqli_query($conn, $query_prog_lang);
$result_category = mysqli_query($conn, $query_category);
$result_schools = mysqli_query($conn, $query_schools);

$prog_lang_data = [];
$category_data = [];
$school_data = [];

while ($row = mysqli_fetch_assoc($result_prog_lang)) {
    $prog_lang_data[] = ['label' => $row['programnyelv'], 'count' => $row['count']];
}
while ($row = mysqli_fetch_assoc($result_category)) {
    $category_data[] = ['label' => $row['kategoria'], 'count' => $row['count']];
}
while ($row = mysqli_fetch_assoc($result_schools)) {
    $school_data[] = ['label' => $row['iskola_nev'], 'count' => $row['count']];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <title>Statisztikák</title>
</head>
<body>
    <nav class="navbar navbar-expand-sm bg-light navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Verseny Adatok</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="dashboard-users.php">Versenyző Adatok</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="dashboard-schools.php">Iskola Adatok</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="#">Statisztikák</a>
            </li>
            <li class="nav-item ml-2">
                <form action="logout.php" method="POST" class="form-inline">
                    <button type="submit" class="btn btn-danger">Kijelentkezés</button>
                </form>
            </li>
        </ul>
    </nav>
    <div class="container mt-5">
        <div class="chart-container">
            <div class="chart-item">
                <h3>Programnyelv Eloszlása</h3>
                <hr>
                <canvas id="programLangChart"></canvas>
            </div>
            <div class="chart-item">
                <h3>Kategória Eloszlása</h3>
                <hr>
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="chart-item">
                <h3>Iskolák Eloszlása</h3>
                <hr>
                <canvas id="schoolChart"></canvas>
            </div>
        </div>
    </div>
    <script>
        const progLangData = <?php echo json_encode($prog_lang_data); ?>;
        const categoryData = <?php echo json_encode($category_data); ?>;
        const schoolData = <?php echo json_encode($school_data); ?>;
        const programLangLabels = progLangData.map(item => item.label);
        const programLangCounts = progLangData.map(item => item.count);
        const programLangChart = new Chart(document.getElementById('programLangChart'), {
            type: 'pie',
            data: {
                labels: programLangLabels,
                datasets: [{
                    label: 'Programnyelvek',
                    data: programLangCounts,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' db';
                            }
                        }
                    }
                }
            }
        });
        const categoryLabels = categoryData.map(item => item.label);
        const categoryCounts = categoryData.map(item => item.count);
        
        const categoryChart = new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Kategóriák',
                    data: categoryCounts,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'], 
                    borderColor: '#388E3C',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' db';
                            }
                        }
                    }
                }
            }
        });
        const schoolLabels = schoolData.map(item => item.label);
        const schoolCounts = schoolData.map(item => item.count);
        
        const schoolChart = new Chart(document.getElementById('schoolChart'), {
            type: 'bar',
            data: {
                labels: schoolLabels,
                datasets: [{
                    label: 'Iskolák Eloszlása',
                    data: schoolCounts,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'], 
                    borderColor: '#388E3C',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' db';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
