<?php 
require_once __DIR__ . '/../src/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT UUIDrally, title FROM dance_rallies WHERE creator_uuid = ?");
$stmt->execute([$user_id]);
$user_rallies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT UUIDrally, title FROM dance_rallies WHERE registred_user = ?");
$stmt->execute([$user_id]);
$registred_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <!--<img src="assets/img/logo.png" alt="">-->
        <span class="d-none d-lg-block">Rally De France</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>  

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            
            <span class="d-none d-md-block dropdown-toggle ps-2"><?= isset($_SESSION['user_fullname']) ? htmlspecialchars($_SESSION['user_fullname']) : '' ?></span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">

            <li>
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>Mon Profil</span>
              </a>
            </li>
            
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="dashboard.php?logout=true">
                <i class="bi bi-box-arrow-right"></i>
                <span>Se Deconnecter</span>
              </a>
            </li>

          </ul>
        </li>

      </ul>
    </nav>

  </header>

  
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Tableau de bord</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Effectuer une recherche</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav1" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Mes Rally</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav1" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <?php foreach ($user_rallies as $list_rally): ?>
            <li>
              <a href="rally-owner.php?id=<?= $list_rally['UUIDrally'] ?>">
                <i class="bi bi-circle"></i><span><?= htmlspecialchars($list_rally['title']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
          <li>
            <a href="create_rally.php">
              <i class="bi bi-circle"></i><span>Ajouter un Rally dans la Base de Donnée</span>
            </a>
          </li>
          
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav2" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Rally dans lesquels je suis </span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav2" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <?php foreach ($registred_users as $list_rally): ?>
            <li>
              <a href="rally-registred.php?id=<?= $list_rally['UUIDrally'] ?>">
                <i class="bi bi-circle"></i><span><?= htmlspecialchars($list_rally['title']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
          <li>
            <a href="register-rally.php">
              <i class="bi bi-circle"></i><span>S'enregister dans un rally</span>
            </a>
          </li>
          
        </ul>
      </li>

      <li class="nav-heading">Autres</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="/contact.php">
          <i class="bi bi-envelope"></i>
          <span>Nous Contacter</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="dashboard.php?logout=true">
          <i class="bi bi-box-arrow-right"></i>
          <span>Se Déconnecter</span>
        </a>
      </li>




    </ul>

  </aside>