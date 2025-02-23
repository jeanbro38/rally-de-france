<?php 
require_once 'src/db.php'; 
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallye De France</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/main.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        body { margin: 0; padding: 0; }
        .container { display: flex; height: 100vh; padding-top: 56px; }
        #map { flex: 1; height: calc(100vh - 56px); }
        .search-container { flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa; border-left: 2px solid #ddd; }
        .card { margin-bottom: 10px; }
        .photo { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center">
            <span class="d-none d-lg-block">Rallye De France</span>
        </a>
    </div>

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <?= isset($_SESSION['user_fullname']) ? htmlspecialchars($_SESSION['user_fullname']) : '' ?>
                    </a>
                    <a href="index.php?logout=true" class="btn btn-outline-danger">Se Deconnecter</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Se Connecter</a>
                    <a href="register.php" class="btn btn-secondary">S'inscrire</a>
                <?php endif; ?>
            </div>
        </ul>
    </nav>
</header>

<div class="container">
    <div id="map"></div>

    <div class="search-container">
        <h2>Chercher Un Rallye</h2>
        <div class="input-group mb-3">
            <input type="text" id="search" class="form-control" placeholder="Rallye Paris...">
            <button class="btn btn-primary" id="search-button">Rechercher</button>
        </div>
        <div id="results"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([48.866667, 2.333333], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let markers = [];

    $(document).ready(function() {
    
    $.ajax({
        url: "search.php",
        method: "POST",
        data: {},  
        dataType: "json",
        success: function(data) {
            loadRalliesOnMap(data);
        }
    });

    $("#search-button").on("click", function() {
        let query = $("#search").val().trim();
        if (query.length > 0) {
            $.ajax({
                url: "search.php",
                method: "POST",
                data: { query: query },
                dataType: "json",
                success: function(data) {
                    loadRalliesWithResults(data);
                }
            });
        } else {
            $("#results").html("<p>Entrez un terme de recherche.</p>");
        }
    });

    $(document).on("click", ".zoom-btn", function() {
        let lat = $(this).data("lat");
        let lng = $(this).data("lng");
        map.setView([lat, lng], 14);
    });
});


function loadRalliesOnMap(data) {
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    if (data.length === 0) {
        return; 
    }

    let bounds = new L.LatLngBounds();
    data.forEach(rally => {
        let marker = L.marker([rally.latitude, rally.longitude]).addTo(map)
            .bindPopup(`<b>${rally.title}</b><br>${rally.location}`);

        markers.push(marker);
        bounds.extend([rally.latitude, rally.longitude]);
    });

    if (data.length > 1) {
        map.fitBounds(bounds);
    } else if (data.length === 1) {
        map.setView([data[0].latitude, data[0].longitude], 14);
    }
}

function loadRalliesWithResults(data) {
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
    $("#results").html("");

    if (data.length === 0) {
        $("#results").html("<p>Aucun résultat trouvé.</p>");
        return;
    }

    let bounds = new L.LatLngBounds();
    data.forEach(rally => {
        let marker = L.marker([rally.latitude, rally.longitude]).addTo(map)
            .bindPopup(`<b>${rally.title}</b><br>${rally.location}`)
            .openPopup();

        markers.push(marker);
        bounds.extend([rally.latitude, rally.longitude]);

        let photoHtml = rally.photos ? `<img src="assets/img/rally/${rally.photos}" class="photo" alt="Rally Photo">` : "";

        $("#results").append(`
            <div class="card">
                <div class="card-body">
                    ${photoHtml}
                    <h5 class="card-title">${rally.title}</h5>
                    <p><strong>Lieu:</strong> ${rally.location}</p>
                    <p><strong>Description:</strong> ${rally.description}</p>
                    <p><strong>Dress Code:</strong> ${rally.dress_code}</p>
                    <p><strong>Tarif:</strong> ${rally.entry_fee} €</p>
                    <p><strong>Musique:</strong> ${rally.music_type}</p>
                    <button class="btn btn-outline-primary zoom-btn" data-lat="${rally.latitude}" data-lng="${rally.longitude}">Zoom</button>
                </div>
            </div>
        `);
    });

    if (data.length > 1) {
        map.fitBounds(bounds);
    } else if (data.length === 1) {
        map.setView([data[0].latitude, data[0].longitude], 14);
    }
}


</script>

</body>
</html>
