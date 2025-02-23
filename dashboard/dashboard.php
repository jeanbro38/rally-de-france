<?php
require_once __DIR__ . '/../src/db.php';
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT UUIDrally, title FROM dance_rallies WHERE creator_uuid = ?");
    $stmt->execute([$user_id]);
    $user_rallies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Une erreur de base de données s'est produite.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Tableau de Bord - Rally de France</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="/assets/img/favicon.png" rel="icon">
    <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include("header-dash.php"); ?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Tableau de Bord</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                <li class="breadcrumb-item active">Tableau De Bord</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-xxl-4 col-xl-12">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Nombre de vue de vos rallyes <span>| Cette année</span></h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6 id="rally-views-count">0</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Statistique de recherche <span>/Cet année</span></h5>
                                <div id="reportsChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
            </div>
        </div>
    </section>
</main>
<?php include("footer-dash.php"); ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch('../src/dashboard-back.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Dashboard Data:", data);

                if (data.error) {
                    console.error("Error from server:", data.error);
                    return;
                }

                document.getElementById("rally-views-count").innerText = data.total_views;

                let seriesData = data.rallies.map(rally => ({
                    name: rally.title,
                    data: data.dates.map(year => rally.views_per_year[year] || 0)
                }));

                new ApexCharts(document.querySelector("#reportsChart"), {
                    series: seriesData,
                    chart: { height: 350, type: 'area', toolbar: { show: false } },
                    markers: { size: 4 },
                    colors: ['#4154f1', '#2eca6a', '#ff771d'],
                    fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.4, stops: [0, 90, 100] } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { type: 'category', categories: data.dates },
                    tooltip: { x: { format: 'yyyy-MM-dd' } }
                }).render();
            })
            .catch(error => console.error("Error fetching dashboard data:", error));
    });
</script>
</body>
</html>