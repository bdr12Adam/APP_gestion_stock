<?php
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
];

$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';
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
          <a href="../vue/commande.php" class="<?= $current_page == 'commande' || $current_page == 'modifier_commande' ? 'active' : '' ?>">
            <i class="bx bx-list-ul"></i>
            <span class="links_name">Commandes</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-pie-chart-alt-2"></i>
            <span class="links_name">Analyses</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-coin-stack"></i>
            <span class="links_name">Stock</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-book-alt"></i>
            <span class="links_name">Toutes les commandes</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-user"></i>
            <span class="links_name">Utilisateur</span>
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bx bx-cog"></i>
            <span class="links_name">Configuration</span>
          </a>
        </li>
        <li class="log_out">
          <a href="#">
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
        <div class="profile-details">
          <!--<img src="images/profile.jpg" alt="">-->
          <span class="admin_name">Komche</span>
          <i class="bx bx-chevron-down"></i>
        </div>
      </nav>

    <script>
      let sidebar = document.querySelector(".sidebar");
      let sidebarBtn = document.querySelector(".sidebarBtn");
      sidebarBtn.onclick = function() {
        sidebar.classList.toggle("active");
        if(sidebar.classList.contains("active")){
          sidebarBtn.classList.replace("bx-menu" ,"bx-menu-alt-right");
        }else
          sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
      }
    </script>