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

      /* Google Font */
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    background: #f0f2ff;
}

/* ================= SIDEBAR ================= */
:root {
  --primary-color: #6366f1;
  --secondary-color: #8b5cf6;
  --accent-color: #ec4899;
  --dark-bg: #0f172a;
  --darker-bg: #020617;
  --light-text: #f1f5f9;
  --muted-text: #94a3b8;
  --hover-bg: rgba(99, 102, 241, 0.1);
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ================= SIDEBAR ================= */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 280px;
  background: var(--dark-bg);
  backdrop-filter: blur(10px);
  transition: var(--transition);
  overflow-x: hidden;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.3);
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: var(--darker-bg);
}

.sidebar::-webkit-scrollbar-thumb {
  background: var(--primary-color);
  border-radius: 10px;
}

.sidebar.active {
  width: 80px;
}

/* LOGO */
.sidebar .logo-details {
  height: 80px;
  display: flex;
  align-items: center;
  padding: 0 20px;
  background: var(--darker-bg);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
}

.sidebar .logo-details::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 20px;
  right: 20px;
  height: 3px;
  background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color), var(--primary-color));
  background-size: 200% 100%;
  opacity: 0.7;
  animation: gradientShift 3s ease infinite;
  box-shadow: 0 0 10px var(--primary-color);
}

@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.sidebar .logo-details i {
  min-width: 60px;
  font-size: 32px;
  color: var(--primary-color);
  text-align: center;
  transition: var(--transition);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { 
    transform: scale(1) rotate(0deg);
    filter: drop-shadow(0 0 5px var(--primary-color));
  }
  50% { 
    transform: scale(1.2) rotate(180deg);
    filter: drop-shadow(0 0 15px var(--secondary-color));
  }
}

.sidebar .logo-details .logo_name {
  font-size: 24px;
  color: var(--light-text);
  font-weight: 700;
  letter-spacing: 1px;
  white-space: nowrap;
  opacity: 1;
  transition: var(--transition);
  text-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
}

.sidebar.active .logo_name {
  opacity: 0;
  pointer-events: none;
}

/* NAVIGATION LINKS */
.sidebar .nav-links {
  margin-top: 30px;
  padding: 0 15px;
}

.sidebar .nav-links li {
  list-style: none;
  margin-bottom: 8px;
  position: relative;
}

.sidebar .nav-links li a {
  height: 52px;
  display: flex;
  align-items: center;
  text-decoration: none;
  border-radius: 12px;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.sidebar .nav-links li a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, var(--primary-color), var(--secondary-color), var(--accent-color));
  transform: scaleY(0);
  transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  border-radius: 0 4px 4px 0;
  box-shadow: 0 0 15px var(--primary-color);
}

.sidebar .nav-links li a::after {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  width: 0;
  height: 0;
  background: radial-gradient(circle, var(--primary-color) 0%, transparent 70%);
  border-radius: 50%;
  transform: translate(50%, -50%);
  transition: all 0.5s ease;
  opacity: 0;
}

.sidebar .nav-links li a:hover::before,
.sidebar .nav-links li a.active::before {
  transform: scaleY(1);
  animation: pulseBar 1.5s ease-in-out infinite;
}

.sidebar .nav-links li a:hover::after {
  width: 100px;
  height: 100px;
  opacity: 0.3;
}

@keyframes pulseBar {
  0%, 100% { box-shadow: 0 0 15px var(--primary-color); }
  50% { box-shadow: 0 0 25px var(--accent-color); }
}

.sidebar .nav-links li a:hover {
  background: linear-gradient(90deg, var(--hover-bg), rgba(139, 92, 246, 0.15));
  transform: translateX(8px) scale(1.02);
  box-shadow: -5px 0 20px rgba(99, 102, 241, 0.3);
}

.sidebar .nav-links li a.active {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(139, 92, 246, 0.25), rgba(236, 72, 153, 0.15));
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4), inset 0 0 20px rgba(99, 102, 241, 0.1);
  animation: activeGlow 2s ease-in-out infinite;
}

@keyframes activeGlow {
  0%, 100% { 
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4), inset 0 0 20px rgba(99, 102, 241, 0.1);
  }
  50% { 
    box-shadow: 0 8px 35px rgba(139, 92, 246, 0.6), inset 0 0 30px rgba(139, 92, 246, 0.2);
  }
}

.sidebar .nav-links li i {
  min-width: 60px;
  font-size: 22px;
  text-align: center;
  color: var(--muted-text);
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  filter: drop-shadow(0 0 0px transparent);
}

.sidebar .nav-links li a:hover i,
.sidebar .nav-links li a.active i {
  color: var(--primary-color);
  transform: scale(1.3) rotate(360deg);
  filter: drop-shadow(0 0 10px var(--primary-color));
  animation: iconBounce 0.6s ease;
}

@keyframes iconBounce {
  0%, 100% { transform: scale(1.3) rotate(360deg) translateY(0); }
  25% { transform: scale(1.4) rotate(370deg) translateY(-5px); }
  50% { transform: scale(1.35) rotate(360deg) translateY(-3px); }
  75% { transform: scale(1.32) rotate(355deg) translateY(-1px); }
}

.sidebar .nav-links li .links_name {
  color: var(--light-text);
  font-size: 15px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 1;
  transition: all 0.3s ease;
  text-shadow: 0 0 0px transparent;
}

.sidebar .nav-links li a:hover .links_name {
  letter-spacing: 1px;
  text-shadow: 0 0 10px rgba(99, 102, 241, 0.8);
  transform: translateX(3px);
}

.sidebar .nav-links li a.active .links_name {
  font-weight: 600;
  text-shadow: 0 0 15px rgba(99, 102, 241, 1);
}

.sidebar.active .nav-links li .links_name {
  opacity: 0;
  pointer-events: none;
}

/* TOOLTIP pour sidebar réduite */
.sidebar.active .nav-links li a .links_name {
  display: none;
}

.sidebar.active .nav-links li:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 90px;
  top: 50%;
  transform: translateY(-50%);
  background: var(--dark-bg);
  padding: 8px 16px;
  border-radius: 8px;
  color: var(--light-text);
  font-size: 14px;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  z-index: 1001;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  0% { 
    opacity: 0; 
    transform: translateY(-50%) translateX(-20px) scale(0.8);
  }
  50% {
    transform: translateY(-50%) translateX(5px) scale(1.05);
  }
  100% { 
    opacity: 1; 
    transform: translateY(-50%) translateX(0) scale(1);
  }
}

/* LOGOUT */
.sidebar .nav-links .log_out {
  position: absolute;
  bottom: 20px;
  width: calc(100% - 30px);
  margin: 0;
}

.sidebar .nav-links .log_out a {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.1));
  border: 2px solid rgba(239, 68, 68, 0.4);
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.sidebar .nav-links .log_out a:hover {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.25));
  border-color: rgba(239, 68, 68, 0.8);
  transform: translateX(8px) scale(1.03);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4), -5px 0 20px rgba(239, 68, 68, 0.3);
  animation: shakeLogout 0.5s ease;
}

@keyframes shakeLogout {
  0%, 100% { transform: translateX(8px) scale(1.03) rotate(0deg); }
  25% { transform: translateX(10px) scale(1.05) rotate(2deg); }
  75% { transform: translateX(6px) scale(1.03) rotate(-2deg); }
}

.sidebar .nav-links .log_out i {
  color: #ef4444;
  transition: all 0.3s ease;
  filter: drop-shadow(0 0 5px rgba(239, 68, 68, 0.5));
}

.sidebar .nav-links .log_out a:hover i {
  transform: scale(1.3) rotate(20deg);
  filter: drop-shadow(0 0 15px rgba(239, 68, 68, 1));
  animation: pulse 0.5s ease infinite;
}



/* ================= AJUSTEMENT HOME SECTION ================= */
.home-section {
  position: relative;
  min-height: 100vh;
  width: calc(100% - 280px);
  left: 280px;
  transition: var(--transition);
}

.sidebar.active ~ .home-section {
  width: calc(100% - 80px);
  left: 80px;
}

/* ================= NAVBAR ADJUSTMENT ================= */
.home-section nav {
  position: fixed;
  top: 0;
  left: 280px;
  width: calc(100% - 280px);
  transition: var(--transition);
}

.sidebar.active ~ .home-section nav {
  left: 80px;
  width: calc(100% - 80px);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 900px) {
  .sidebar {
    width: 80px;
  }
  
  .sidebar .logo_name,
  .sidebar .links_name {
    opacity: 0;
  }
  
  .home-section {
    width: calc(100% - 80px);
    left: 80px;
  }
  
  .home-section nav {
    left: 80px;
    width: calc(100% - 80px);
  }
}

.search-box {
  width: 450px;
  position: relative;
}

.search-box input {
  width: 100%;
  height: 48px;
  border-radius: 12px;
  border: 2px solid transparent;
  padding: 0 50px 0 20px;
  background: #f1f5f9;
  font-size: 15px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  color: #0f172a;
}

.search-box input:focus {
  outline: none;
  border-color: #6366f1;
  background: white;
  box-shadow: 0 8px 30px rgba(99, 102, 241, 0.3);
  transform: translateY(-2px);
}

.search-box input::placeholder {
  color: #94a3b8;
  transition: all 0.3s ease;
}

.search-box input:focus::placeholder {
  transform: translateX(5px);
  opacity: 0.7;
}

.search-box > i {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #6366f1;
  font-size: 22px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  pointer-events: none;
}

.search-box input:focus ~ i {
  transform: translateY(-50%) scale(1.3) rotate(90deg);
  filter: drop-shadow(0 0 8px #6366f1);
  animation: searchPulse 1.5s ease-in-out infinite;
}

@keyframes searchPulse {
  0%, 100% { 
    transform: translateY(-50%) scale(1.3) rotate(90deg);
  }
  50% { 
    transform: translateY(-50%) scale(1.4) rotate(90deg);
    filter: drop-shadow(0 0 15px #8b5cf6);
  }
}

/* SEARCH RESULTS DROPDOWN */
.search-results {
  position: absolute;
  top: 60px;
  left: 0;
  width: 100%;
  background: white;
  border-radius: 12px;
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
  max-height: 450px;
  overflow-y: auto;
  display: none;
  z-index: 1000;
  border: 2px solid #e2e8f0;
}

.search-results::-webkit-scrollbar {
  width: 8px;
}

.search-results::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 0 12px 12px 0;
}

.search-results::-webkit-scrollbar-thumb {
  background: #6366f1;
  border-radius: 10px;
}

.search-results.active {
  display: block;
  animation: slideDownSearch 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes slideDownSearch {
  0% {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  50% {
    transform: translateY(5px) scale(1.02);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* SEARCH RESULT ITEMS - ULTRA DYNAMIQUE */
.search-result-item {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  display: flex;
  align-items: center;
  gap: 15px;
  text-decoration: none;
  color: #0f172a;
  position: relative;
  overflow: hidden;
  background: white;
}

/* Barre latérale animée */
.search-result-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 5px;
  height: 100%;
  background: linear-gradient(180deg, #6366f1, #8b5cf6, #ec4899);
  transform: scaleY(0);
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  border-radius: 0 8px 8px 0;
  box-shadow: 0 0 20px rgba(99, 102, 241, 0);
}

/* Effet de brillance au hover */
.search-result-item::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.6s ease;
}

.search-result-item:hover::before {
  transform: scaleY(1);
  box-shadow: 0 0 20px rgba(99, 102, 241, 0.6);
  animation: pulseSearchBar 1.5s ease-in-out infinite;
}

.search-result-item:hover::after {
  left: 100%;
}

@keyframes pulseSearchBar {
  0%, 100% { 
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.6);
  }
  50% { 
    box-shadow: 0 0 30px rgba(236, 72, 153, 0.8);
  }
}

.search-result-item:hover {
  background: linear-gradient(90deg, rgba(99, 102, 241, 0.12), rgba(139, 92, 246, 0.08), transparent);
  padding-left: 32px;
  transform: translateX(5px) scale(1.01);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
}

.search-result-item:active {
  transform: translateX(3px) scale(0.98);
  transition: all 0.1s ease;
}

.search-result-item:last-child {
  border-bottom: none;
  border-radius: 0 0 12px 12px;
}

/* Icônes ultra-dynamiques */
.search-result-item i {
  color: #6366f1;
  font-size: 24px;
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.4));
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
  padding: 10px;
  border-radius: 12px;
}

.search-result-item:hover i {
  transform: scale(1.3) rotate(360deg) translateY(-3px);
  filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.8));
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
  animation: iconBounceSearch 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes iconBounceSearch {
  0%, 100% { 
    transform: scale(1.3) rotate(360deg) translateY(-3px);
  }
  25% { 
    transform: scale(1.45) rotate(370deg) translateY(-8px);
  }
  50% { 
    transform: scale(1.38) rotate(365deg) translateY(-5px);
  }
  75% { 
    transform: scale(1.33) rotate(358deg) translateY(-4px);
  }
}

/* Contenu avec animation */
.search-result-content {
  flex: 1;
  transition: all 0.3s ease;
}

.search-result-item:hover .search-result-content {
  transform: translateX(3px);
}

.search-result-title {
  font-weight: 600;
  font-size: 15px;
  color: #0f172a;
  margin-bottom: 4px;
  transition: all 0.3s ease;
  text-shadow: 0 0 0px transparent;
}

.search-result-item:hover .search-result-title {
  color: #6366f1;
  text-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
  letter-spacing: 0.3px;
}

.search-result-description {
  font-size: 13px;
  color: #64748b;
  transition: all 0.3s ease;
  opacity: 0.8;
}

.search-result-item:hover .search-result-description {
  color: #475569;
  opacity: 1;
}

/* Badges ultra-stylés avec animation */
.search-result-badge {
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.search-result-badge::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
}

.search-result-item:hover .search-result-badge {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.search-result-item:hover .search-result-badge::before {
  left: 100%;
}

.badge-page {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe, #93c5fd);
  color: #1e40af;
}

.badge-client {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0, #6ee7b7);
  color: #065f46;
}

.badge-article {
  background: linear-gradient(135deg, #fce7f3, #fbcfe8, #f9a8d4);
  color: #9f1239;
}

.badge-fournisseur {
  background: linear-gradient(135deg, #fef3c7, #fde68a, #fcd34d);
  color: #92400e;
}

/* NO RESULTS - Animation améliorée */
.no-results {
  padding: 40px 20px;
  text-align: center;
  color: #94a3b8;
  animation: fadeInScale 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes fadeInScale {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.no-results i {
  font-size: 64px;
  margin-bottom: 15px;
  opacity: 0.4;
  animation: floatingAdvanced 3s ease-in-out infinite;
  color: #6366f1;
  filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.3));
}

@keyframes floatingAdvanced {
  0%, 100% { 
    transform: translateY(0px) rotate(0deg);
    filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.3));
  }
  25% {
    transform: translateY(-15px) rotate(5deg);
  }
  50% { 
    transform: translateY(-20px) rotate(0deg);
    filter: drop-shadow(0 0 30px rgba(139, 92, 246, 0.5));
  }
  75% {
    transform: translateY(-15px) rotate(-5deg);
  }
}

.no-results p {
  font-size: 15px;
  font-weight: 600;
  color: #64748b;
}

.no-results strong {
  color: #6366f1;
  font-weight: 700;
}

/* SEARCH CATEGORY HEADER - Ultra stylé */
.search-category {
  padding: 12px 20px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  font-size: 12px;
  font-weight: 800;
  color: #6366f1;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  border-bottom: 3px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(10px);
  animation: slideInCategory 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes slideInCategory {
  0% {
    opacity: 0;
    transform: translateY(-10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
/* ===============================
   STYLE DE BASE
================================ */
.sidebar-button {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    cursor: pointer;
    transition: 
        background 0.3s ease,
        box-shadow 0.3s ease,
        transform 0.3s ease;
}

/* Hover du bouton */
.sidebar-button:hover {
    background: #f5f7ff;
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.2);
    transform: translateY(-2px);
}

/* ===============================
   ICON / BOUTON INTERNE
================================ */
.sidebarBtn {
    font-size: 20px;
    color: #4b5563;
    transition: 
        transform 0.4s ease,
        color 0.3s ease,
        filter 0.3s ease;
}

/* ===============================
   HOVER + ANIMATION
================================ */
.sidebar-button:hover .sidebarBtn {
    color: #4f46e5; /* Indigo */
    animation: rotatePulse 0.6s ease;
    filter: drop-shadow(0 0 8px rgba(79, 70, 229, 0.6));
}

/* ===============================
   KEYFRAMES
================================ */
@keyframes rotatePulse {
    0% {
        transform: rotate(0deg) scale(1);
    }
    50% {
        transform: rotate(180deg) scale(1.15);
    }
    100% {
        transform: rotate(360deg) scale(1);
    }
}


.search-category::before {
  content: '';
  position: absolute;
  bottom: -3px;
  left: 0;
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
  animation: categoryLineMove 2s ease-in-out infinite;
}

@keyframes categoryLineMove {
  0%, 100% {
    left: 0;
    width: 60px;
  }
  50% {
    left: calc(100% - 60px);
    width: 80px;
  }
}

/* LOADING STATE - Animation spectaculaire */
.search-loading {
  padding: 40px 20px;
  text-align: center;
  color: #6366f1;
}

.search-loading i {
  font-size: 48px;
  animation: spinAdvanced 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
  filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.6));
}

@keyframes spinAdvanced {
  0% { 
    transform: rotate(0deg) scale(1);
    filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.6));
  }
  50% {
    transform: rotate(180deg) scale(1.2);
    filter: drop-shadow(0 0 25px rgba(139, 92, 246, 0.8));
  }
  100% { 
    transform: rotate(360deg) scale(1);
    filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.6));
  }
}

.search-loading::after {
  content: 'Recherche en cours...';
  display: block;
  margin-top: 15px;
  font-size: 14px;
  font-weight: 600;
  color: #64748b;
  animation: pulse 1.5s ease-in-out infinite;
}

/* HIGHLIGHT MATCHED TEXT - Super visible */
.highlight {
  background: linear-gradient(135deg, #fef3c7, #fde68a, #fcd34d);
  padding: 3px 6px;
  border-radius: 4px;
  font-weight: 800;
  color: #92400e;
  box-shadow: 0 2px 8px rgba(252, 211, 77, 0.4);
  animation: highlightPulse 2s ease-in-out infinite;
  display: inline-block;
  text-shadow: 0 1px 2px rgba(146, 64, 14, 0.1);
}

@keyframes highlightPulse {
  0%, 100% {
    box-shadow: 0 2px 8px rgba(252, 211, 77, 0.4);
    transform: scale(1);
  }
  50% {
    box-shadow: 0 4px 15px rgba(252, 211, 77, 0.6);
    transform: scale(1.05);
  }
}

/* Animation d'entrée pour chaque résultat */
.search-result-item {
  animation: slideInResult 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) backwards;
}

.search-result-item:nth-child(1) { animation-delay: 0.05s; }
.search-result-item:nth-child(2) { animation-delay: 0.1s; }
.search-result-item:nth-child(3) { animation-delay: 0.15s; }
.search-result-item:nth-child(4) { animation-delay: 0.2s; }
.search-result-item:nth-child(5) { animation-delay: 0.25s; }
.search-result-item:nth-child(6) { animation-delay: 0.3s; }
.search-result-item:nth-child(7) { animation-delay: 0.35s; }
.search-result-item:nth-child(8) { animation-delay: 0.4s; }

@keyframes slideInResult {
  0% {
    opacity: 0;
    transform: translateX(-30px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .search-box {
    display: none;
  }
  
  .search-result-item {
    padding: 12px 15px;
  }
  
  .search-result-item i {
    font-size: 20px;
    padding: 8px;
  }
  
  .search-result-badge {
    padding: 4px 10px;
    font-size: 10px;
  }
}

/* PROFILE */
.profile-details {
    display: flex;
    align-items: center;
    background: #f1f3ff;
    padding: 5px 15px;
    border-radius: 8px;
}

.profile-details img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
}

.profile-details .admin_name {
    margin: 0 10px;
    font-weight: 500;
}

/* ================= CONTENT ================= */
.home-content {
    padding: 120px 25px 25px;
}

/* ================= STATS BOX ================= */
.overview-boxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.overview-boxes .box {
    background: #fff;
    padding: 20px;
    border-radius: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.35s linear;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.overview-boxes .box:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(102,126,234,0.35);
}

.box .number {
    font-size: 34px;
    font-weight: 600;
}

.box-topic {
    font-size: 18px;
}

.cart {
    width: 55px;
    height: 55px;
    border-radius: 14px;
    background: #e0e5ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #667eea;
}

/* ================= SALES ================= */
.sales-boxes {
    display: flex;
    gap: 20px;
    margin-top: 30px;
}

.sales-boxes .box {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transition: all 0.35s linear;
}

.sales-boxes .box:hover {
    transform: translateY(-6px);
}

.recent-sales {
    flex: 2;
}

.top-sales {
    flex: 1;
}

/* BUTTON */
.button a {
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 8px 16px;
    border-radius: 6px;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s linear;
}

.button a:hover {
    transform: scale(1.05);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 900px) {
    .sales-boxes {
        flex-direction: column;
    }

    .search-box {
        display: none;
    }
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
  <input type="text" id="searchInput" placeholder="Rechercher une page, un client, un article..." />
  <i class="bx bx-search"></i>
  <div class="search-results" id="searchResults"></div>
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

      const searchData = {
  pages: [
    { name: 'Dashboard', icon: 'bx-grid-alt', url: 'dashboard.php', description: 'Vue d\'ensemble de l\'application' },
    { name: 'Ventes', icon: 'bx-shopping-bag', url: '../vue/vente.php', description: 'Gérer toutes vos ventes' },
    { name: 'Clients', icon: 'bx-user', url: '../vue/client.php', description: 'Base de données clients' },
    { name: 'Articles', icon: 'bx-box', url: 'article.php', description: 'Catalogue de produits' },
    { name: 'Fournisseurs', icon: 'bx-basket', url: '../vue/fournisseur.php', description: 'Gestion des fournisseurs' },
    { name: 'Analyses', icon: 'bx-pie-chart-alt-2', url: '../vue/analyse.php', description: 'Statistiques et rapports' },
    { name: 'Stock', icon: 'bx-coin-stack', url: '../vue/stock.php', description: 'Inventaire et stocks' },
    { name: 'Utilisateurs', icon: 'bx-user', url: '../vue/gestion_utilisateur.php', description: 'Gestion des utilisateurs' },
    { name: 'Configuration', icon: 'bx-cog', url: '../vue/configuratin.php', description: 'Paramètres système' }
  ],
  // Vous pouvez ajouter des clients, articles, etc. ici
  clients: [
    { name: 'Samsung Electronics', type: 'client', url: '../vue/client.php?id=1' },
    { name: 'Apple Store Morocco', type: 'client', url: '../vue/client.php?id=2' }
  ],
  articles: [
    { name: 'iPhone 15 Pro', type: 'article', url: 'article.php?id=1' },
    { name: 'Samsung Galaxy S24', type: 'article', url: 'article.php?id=2' }
  ]
};

const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');

// Fonction pour highlight le texte correspondant
function highlightText(text, query) {
  const regex = new RegExp(`(${query})`, 'gi');
  return text.replace(regex, '<span class="highlight">$1</span>');
}

// Fonction de recherche
function performSearch(query) {
  query = query.toLowerCase().trim();
  
  if (query === '') {
    searchResults.classList.remove('active');
    return;
  }

  let results = {
    pages: [],
    clients: [],
    articles: []
  };

  // Recherche dans les pages
  searchData.pages.forEach(item => {
    if (item.name.toLowerCase().includes(query) || 
        item.description.toLowerCase().includes(query)) {
      results.pages.push({...item, type: 'page'});
    }
  });

  // Recherche dans les clients
  searchData.clients.forEach(item => {
    if (item.name.toLowerCase().includes(query)) {
      results.clients.push(item);
    }
  });

  // Recherche dans les articles
  searchData.articles.forEach(item => {
    if (item.name.toLowerCase().includes(query)) {
      results.articles.push(item);
    }
  });

  displayResults(results, query);
}

// Fonction pour afficher les résultats
function displayResults(results, query) {
  let html = '';
  let totalResults = results.pages.length + results.clients.length + results.articles.length;

  if (totalResults === 0) {
    html = `
      <div class="no-results">
        <i class='bx bx-search-alt'></i>
        <p>Aucun résultat pour "<strong>${query}</strong>"</p>
      </div>
    `;
  } else {
    // Pages
    if (results.pages.length > 0) {
      html += '<div class="search-category">📄 Pages</div>';
      results.pages.forEach(item => {
        html += `
          <a href="${item.url}" class="search-result-item">
            <i class="bx ${item.icon}"></i>
            <div class="search-result-content">
              <div class="search-result-title">${highlightText(item.name, query)}</div>
              <div class="search-result-description">${item.description}</div>
            </div>
            <span class="search-result-badge badge-page">Page</span>
          </a>
        `;
      });
    }

    // Clients
    if (results.clients.length > 0) {
      html += '<div class="search-category">👤 Clients</div>';
      results.clients.forEach(item => {
        html += `
          <a href="${item.url}" class="search-result-item">
            <i class="bx bx-user"></i>
            <div class="search-result-content">
              <div class="search-result-title">${highlightText(item.name, query)}</div>
            </div>
            <span class="search-result-badge badge-client">Client</span>
          </a>
        `;
      });
    }

    // Articles
    if (results.articles.length > 0) {
      html += '<div class="search-category">📦 Articles</div>';
      results.articles.forEach(item => {
        html += `
          <a href="${item.url}" class="search-result-item">
            <i class="bx bx-box"></i>
            <div class="search-result-content">
              <div class="search-result-title">${highlightText(item.name, query)}</div>
            </div>
            <span class="search-result-badge badge-article">Article</span>
          </a>
        `;
      });
    }
  }

  searchResults.innerHTML = html;
  searchResults.classList.add('active');
}

// Event listeners
searchInput.addEventListener('input', function(e) {
  performSearch(e.target.value);
});

// Navigation au clavier
searchInput.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    const firstResult = searchResults.querySelector('.search-result-item');
    if (firstResult && firstResult.href) {
      window.location.href = firstResult.href;
    }
  }
});

// Fermer la recherche en cliquant ailleurs
document.addEventListener('click', function(e) {
  if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
    searchResults.classList.remove('active');
  }
});

// ESC pour fermer
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    searchResults.classList.remove('active');
    searchInput.blur();
  }
});

// Focus avec raccourci clavier (Ctrl+K ou Cmd+K)
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    searchInput.focus();
  }
});
    </script>