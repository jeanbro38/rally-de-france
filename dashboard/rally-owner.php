<?php
require_once 'src/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = ''; 

$UUIDrally = $_GET['id'] ?? ''; 

$stmt = $pdo->prepare("SELECT * FROM dance_rallies WHERE UUIDrally = BINARY ?");
$stmt->execute([$UUIDrally]);

$rally = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rally) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => "No rally found for UUID: " . htmlspecialchars($UUIDrally)];
    header('Location: dashboard.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($UUIDrally)) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $postcode = trim($_POST['postcode'] ?? '');
    $dress_code = $_POST['dress_code'] ?? '';
    $entry_fee = $_POST['entry_fee'] ?? null;
    $music_type = $_POST['music_type'] ?? '';

    $lat = null;
    $lon = null;
    if (!empty($postcode)) {
        $url = "https://nominatim.openstreetmap.org/search?format=json&country=France&postalcode=" . urlencode($postcode) . "&city=" . urlencode($location) . "";
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

    if ($lat && $lon) {
        $stmt = $pdo->prepare("UPDATE dance_rallies 
            SET title=?, description=?, location=?, latitude=?, longitude=?, dress_code=?, entry_fee=?, music_type=? 
            WHERE UUIDrally = BINARY ?");

        $success = $stmt->execute([$title, $description, $location, $lat, $lon, $dress_code, $entry_fee, $music_type, $UUIDrally]);

        if ($success) {
            $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Mise à jour réussie !"];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Échec de la mise à jour merci de contacter le support en precisant cette erreur: " . implode(" | ", $stmt->errorInfo())];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Impossible d'obtenir la latitude et la longitude pour ce code postal."];
    }

    header("Location: rally.php?id=" . urlencode($UUIDrally));
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Gestion de <?= htmlspecialchars($rally['title'] ?? 'Rallye') ?> - Rallye de France</title>
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
      <h1><?= htmlspecialchars($rally['title'] ?? 'Rallye') ?></h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Accueil</a></li>
          <li class="breadcrumb-item">Mes Rallyes</li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($rally['title'] ?? 'Rallye') ?></li>
        </ol>
      </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Gestion de Mon Rallye</h5>
                        <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                                <?= $_SESSION['message']['text'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php unset($_SESSION['message']); ?>
                                <?php endif; ?>
                        <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-home" type="button" role="tab" aria-controls="home" aria-selected="true">Compte Inscrit à Mon Rally</button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Demande d'inscription</button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100" id="contact-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Modifier les infos de mon Rally</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                            <div class="tab-pane fade show active" id="bordered-justified-home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Compte Enregistrer dans ce Rally</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Nom</th>
                                                    <th scope="col">Adresse</th>
                                                    <th scope="col">Adresse Mail</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">1</th>
                                                    <td>Brandon Jacob</td>
                                                    <td>Designer</td>
                                                    <td>28</td>
                                                    <td>2016-05-25</td>
                                                </tr>
                  
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Compte en Attente d'inscription</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Nom</th>
                                                    <th scope="col">Adresse</th>
                                                    <th scope="col">Adresse Mail</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">1</th>
                                                    <td>Brandon Jacob</td>
                                                    <td>Designer</td>
                                                    <td>28</td>
                                                    <td>2016-05-25</td>
                                                </tr>
                  
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="bordered-justified-contact" role="tabpanel" aria-labelledby="contact-tab">
                                <h5 class="card-title">Modifier les informations du Rallye</h5>
                                
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
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
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