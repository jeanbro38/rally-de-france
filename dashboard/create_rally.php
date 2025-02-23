<?php
require_once 'src/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UUIDrally = bin2hex(random_bytes(16)); // Génération d'un UUID unique
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $postcode = trim($_POST['postcode'] ?? '');
    $dress_code = $_POST['dress_code'] ?? '';
    $entry_fee = $_POST['entry_fee'] ?? null;
    $music_type = $_POST['music_type'] ?? '';
    $creator_uuid = $_SESSION['user_id'] ?? '';

    $lat = null;
    $lon = null;
    
    // Vérifier si un code postal est fourni
    if (!empty($postcode)) {
        $url = "https://nominatim.openstreetmap.org/search?format=json&country=France&postalcode=" . urlencode($postcode) . "&city=" . urlencode($location);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyRallyApp/1.5 (contact@eaglecloud.fr)');
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!empty($data)) {
            $lat = $data[0]['lat'] ?? null;
            $lon = $data[0]['lon'] ?? null;
        }
    }

    // Vérification des coordonnées avant insertion
    if ($lat && $lon) {
        $stmt = $pdo->prepare("INSERT INTO dance_rallies 
        (UUIDrally, title, description, location, latitude, longitude, dress_code, entry_fee, music_type, creator_uuid) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([$UUIDrally, $title, $description, $location, $lat, $lon, $dress_code, $entry_fee, $music_type, $creator_uuid]);

        if ($success) {
            $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Création réussie !"];
            header("Location: rally.php?id=" . urlencode($UUIDrally));
            exit;
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Échec de la mise à jour. Erreur: " . implode(" | ", $stmt->errorInfo())];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Impossible d'obtenir la latitude et la longitude pour ce code postal."];
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Création de Rally - Rallye de France</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head>

<body>

<?php include("header-dash.php"); ?>

<main id="main" class="main">

    <div class="pagetitle">
      <h1>Enregistrer un Rally dans la Base de données</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
          
          <li class="breadcrumb-item active">Enregistrer Un Rally dans la base de données</li>
        </ol>
      </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Création d'un rally</h5>
                        <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                                <?= $_SESSION['message']['text'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['message']); ?>
                                <?php endif; ?>
                                
                                <form method="post">
                            
                                    <input type="hidden" name="UUIDrally" value="<?= htmlspecialchars($rally['UUIDrally'] ?? '') ?>">
                
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Titre</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($rally['title'] ?? '') ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Description</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" name="description"><?= htmlspecialchars($rally['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Ville</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($rally['location'] ?? '') ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Code Postal</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" name="postcode" required>
                                        </div>
                                    </div>
                
                
                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label">Code vestimentaire</label>
                                        <div class="col-sm-10">
                                            <select class="form-select" aria-label="Default select example" name="dress_code">
                                                <option selected>Veuillez choisir une option</option>
                                                <option value="tenue de ville" <?= isset($rally['dress_code']) && $rally['dress_code'] == 'tenue de ville' ? 'selected' : '' ?>>Tenue de ville</option>
                                                <option value="tenue de soirée" <?= isset($rally['dress_code']) && $rally['dress_code'] == 'tenue de soirée' ? 'selected' : '' ?>>Tenue de soirée</option>
                                                <option value="smoking" <?= isset($rally['dress_code']) && $rally['dress_code'] == 'smoking' ? 'selected' : '' ?>>Smoking</option>
                                                <option value="robe longue" <?= isset($rally['dress_code']) && $rally['dress_code'] == 'robe longue' ? 'selected' : '' ?>>Robe longue</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="inputEntryfee" class="col-sm-2 col-form-label">Frais d'entrée (€):</label>
                                        <div class="col-sm-10">
                                            <input type="number" step="0.01" name="entry_fee" value="<?= htmlspecialchars($rally['entry_fee'] ?? '') ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="inputMusictype" class="col-sm-2 col-form-label">Type de musique :</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="music_type" value="<?= htmlspecialchars($rally['music_type'] ?? '') ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-2 col-form-label"></label>
                                        <div class="col-sm-10">
                                            <button type="submit" class="btn btn-primary">Créer</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>          
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("footer-dash.php"); ?>