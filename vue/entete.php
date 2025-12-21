<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location:  ../vue/utilisateur.php");
    exit();
}

// Déterminer le titre de la page en fonction du fichier actuel
$current_page = basename($_SERVER['PHP_SELF'], '.php');

$page_titles = [
    'dashboard' => 'Dashboard',
    'vente' => 'Gestion des Ventes',
    'client' => 'Gestion des Clients',
    'article' => 'Gestion des Articles',
    'fournisseur' => 'Gestion des Fournisseurs',
    'commande' => 'Gestion des Commandes',
    'modifier_vente' => 'Modifier Vente',
    'modifier_client' => 'Modifier Client',
    'modifier_article' => 'Modifier Article',
    'modifier_fournisseur' => 'Modifier Fournisseur',
    'modifier_commande' => 'Modifier Commande',
    'analyse' => 'Analyses & Statistiques',
    'stock' => 'Gestion de Stock',
    'utilisateur' => 'Gestion des Utilisateurs',
];

$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';

// Récupérer les informations de l'utilisateur
$user_nom = $_SESSION['user_nom'] ?? 'Utilisateur';
$user_prenom = $_SESSION['user_prenom'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'user';
$user_initiale = strtoupper(substr($user_prenom, 0, 1));
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <meta charset="UTF-8" />
    <title>S-TOCK - <?= $page_title ?></title>
    <link rel="stylesheet" href="index.css" />
    <!-- Boxicons CDN Link -->
    <link
      href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css"
      rel="stylesheet"
    />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
      /* Style pour le lien actif */
      .nav-links li a.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
      }
      
      .nav-links li a.active i {
        color: #fff;
      }
      
      .nav-links li a.active .links_name {
        color: #fff;
      }

      /* Style amélioré pour le profil */
      .profile-details {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 15px;
        border-radius: 12px;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
      }

      .profile-details:hover {
        background: #e2e8f0;
      }

      .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
      }

      .profile-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
      }

      .admin_name {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
      }

      .admin_role {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
      }

      .role-admin {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        color: white;
      }

      .role-manager {
        background: #dbeafe;
        color: #1e40af;
      }

      .role-user {
        background: #d1fae5;
        color: #065f46;
      }

      /* Dropdown menu profil */
      .profile-dropdown {
        position: absolute;
        top: 70px;
        right: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 10px;
        min-width: 200px;
        display: none;
        z-index: 1000;
      }

      .profile-dropdown.active {
        display: block;
        animation: slideDown 0.3s ease;
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .dropdown-item {
        padding: 10px 15px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: #334155;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
      }

      .dropdown-item:hover {
        background: #f8fafc;
      }

      .dropdown-item i {
        font-size: 18px;
        color: #667eea;
      }

      .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
      }
    </style>
  </head>
  <body>
    <div class="sidebar">
      <div class="logo-details">
        <i class='bx bx-command'></i> 
        <span class="logo_name">S-TOCK</span>
      </div>
      <ul class="nav-links">
        <li>
          <a href="dashboard.php" class="<?= $current_page == 'dashboard' ? 'active' : '' ?>">
            <i class="bx bx-grid-alt"></i>
            <span class="links_name">Dashboard</span>
          </a>
        </li>
        <li>
          <a href="../vue/vente.php" class="<?= $current_page == 'vente' || $current_page == 'modifier_vente' ? 'active' : '' ?>">
            <i class='bx bx-shopping-bag'></i> 
            <span class="links_name">Vente</span>
          </a>
        </li>
        <li>
          <a href="../vue/client.php" class="<?= $current_page == 'client' || $current_page == 'modifier_client' ? 'active' : '' ?>">
            <i class="bx bx-user"></i>
            <span class="links_name">Client</span>
          </a>
        </li>
        <li>
          <a href="article.php" class="<?= $current_page == 'article' || $current_page == 'modifier_article' ? 'active' : '' ?>">
            <i class="bx bx-box"></i>
            <span class="links_name">Article</span>
          </a>
        </li>
        <li>
          <a href="../vue/fournisseur.php" class="<?= $current_page == 'fournisseur' || $current_page == 'modifier_fournisseur' ? 'active' : '' ?>">
            <i class='bx bx-basket'></i> 
            <span class="links_name">Fournisseur</span>
          </a>
        </li>
       
        <li>
          <a href="../vue/analyse.php" class="<?= $current_page == 'analyse' ? 'active' : '' ?>">
            <i class="bx bx-pie-chart-alt-2"></i>
            <span class="links_name">Analyses</span>
          </a>
        </li>
        <li>
          <a href="../vue/stock.php" class="<?= $current_page == 'stock' ? 'active' : '' ?>">
            <i class="bx bx-coin-stack"></i>
            <span class="links_name">Stock</span>
          </a>
        </li>
        
        <li>
          <a href="../vue/gestion_utilisateur.php" class="<?= $current_page == 'utilisateur' ? 'active' : '' ?>">
            <i class="bx bx-user"></i>
            <span class="links_name">Utilisateurs</span>
          </a>
        </li>
        <li>
          <a href="../vue/configuratin.php">
            <i class="bx bx-cog"></i>
            <span class="links_name">Configuration</span>
          </a>
        </li>
        <li class="log_out">
          <a href="../vue/logout.php">
            <i class="bx bx-log-out"></i>
            <span class="links_name">Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
    <section class="home-section">
      <nav>
        <div class="sidebar-button">
          <i class="bx bx-menu sidebarBtn"></i>
          <span class="dashboard"><?= $page_title ?></span>
        </div>
        <div class="search-box">
          <input type="text" placeholder="Recherche..." />
          <i class="bx bx-search"></i>
        </div>
        <div class="profile-details" id="profileToggle">
          <div class="profile-avatar"><?= $user_initiale ?></div>
          <div class="profile-info">
            <span class="admin_name"><?= htmlspecialchars($user_prenom . ' ' . $user_nom) ?></span>
            <span class="role-badge role-<?= $user_role ?>">
              <?= $user_role == 'admin' ? '🧑‍💻 Admin' : ($user_role == 'manager' ? '👔 Manager' : '👤 User') ?>
            </span>
          </div>
          <i class="bx bx-chevron-down"></i>
        </div>

        <!-- Dropdown Menu -->
        <div class="profile-dropdown" id="profileDropdown">
          <a href="../vue/utilisateur.php?modifier=<?= $_SESSION['user_id'] ?>" class="dropdown-item">
            <i class='bx bx-user'></i>
            <span>Mon Profil</span>
          </a>
          <a href="../vue/configuratin.php" class="dropdown-item">
            <i class='bx bx-cog'></i>
            <span>Paramètres</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="../vue/logout.php" class="dropdown-item">
            <i class='bx bx-log-out'></i>
            <span>Déconnexion</span>
          </a>
        </div>
      </nav>

    <script>
      // Toggle sidebar
      let sidebar = document.querySelector(".sidebar");
      let sidebarBtn = document.querySelector(".sidebarBtn");
      sidebarBtn.onclick = function() {
        sidebar.classList.toggle("active");
        if(sidebar.classList.contains("active")){
          sidebarBtn.classList.replace("bx-menu" ,"bx-menu-alt-right");
        }else
          sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
      }

      // Toggle profile dropdown
      const profileToggle = document.getElementById('profileToggle');
      const profileDropdown = document.getElementById('profileDropdown');

      profileToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        profileDropdown.classList.toggle('active');
      });

      // Fermer le dropdown quand on clique ailleurs
      document.addEventListener('click', function(e) {
        if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
          profileDropdown.classList.remove('active');
        }
      });
    </script>