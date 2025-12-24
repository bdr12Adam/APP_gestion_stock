<?php
include "entete.php";
include "../model/connexion.php";

$message = "";
$typeMessage = "";

// Vérifier si c'est un admin
$is_admin = ($_SESSION['user_role'] === 'admin');

// ----- RÉCUPÉRATION DES PARAMÈTRES ACTUELS -----
$parametres = [];
$result = $conn->query("SELECT * FROM parametre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $parametres[$row['cle']] = $row['valeur'];
    }
}

// Valeurs par défaut si pas de paramètres
$defaults = [
    'nom_entreprise' => 'S-TOCK',
    'email_entreprise' => 'contact@stock.com',
    'telephone_entreprise' => '+212 6XX XXX XXX',
    'adresse_entreprise' => 'Casablanca, Maroc',
    'devise' => 'DH',
    'tva' => '20',
    'stock_alerte' => '10',
    'stock_critique' => '5',
    'theme' => 'light',
    'langue' => 'fr',
    'notifications_email' => '1',
    'notifications_stock' => '1',
    'sauvegarde_auto' => '1',
];

foreach ($defaults as $key => $value) {
    if (!isset($parametres[$key])) {
        $parametres[$key] = $value;
    }
}

// ----- TRAITEMENT DU FORMULAIRE -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    
    if (isset($_POST['action'])) {
        
        // Mise à jour des paramètres généraux
        if ($_POST['action'] === 'update_general') {
            $settings = [
                'nom_entreprise' => $_POST['nom_entreprise'],
                'email_entreprise' => $_POST['email_entreprise'],
                'telephone_entreprise' => $_POST['telephone_entreprise'],
                'adresse_entreprise' => $_POST['adresse_entreprise'],
                'devise' => $_POST['devise'],
                'tva' => $_POST['tva'],
            ];
            
            foreach ($settings as $cle => $valeur) {
                $stmt = $conn->prepare("
                    INSERT INTO parametre (cle, valeur) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valeur = ?
                ");
                $stmt->bind_param("sss", $cle, $valeur, $valeur);
                $stmt->execute();
                $stmt->close();
            }
            
            $message = "Paramètres généraux mis à jour avec succès !";
            $typeMessage = "success";
        }
        
        // Mise à jour des alertes stock
        if ($_POST['action'] === 'update_stock') {
            $settings = [
                'stock_alerte' => $_POST['stock_alerte'],
                'stock_critique' => $_POST['stock_critique'],
            ];
            
            foreach ($settings as $cle => $valeur) {
                $stmt = $conn->prepare("
                    INSERT INTO parametre (cle, valeur) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valeur = ?
                ");
                $stmt->bind_param("sss", $cle, $valeur, $valeur);
                $stmt->execute();
                $stmt->close();
            }
            
            $message = "Paramètres de stock mis à jour avec succès !";
            $typeMessage = "success";
        }
        
        // Mise à jour des préférences
        if ($_POST['action'] === 'update_preferences') {
            $settings = [
                'theme' => $_POST['theme'],
                'langue' => $_POST['langue'],
                'notifications_email' => isset($_POST['notifications_email']) ? '1' : '0',
                'notifications_stock' => isset($_POST['notifications_stock']) ? '1' : '0',
                'sauvegarde_auto' => isset($_POST['sauvegarde_auto']) ? '1' : '0',
            ];
            
            foreach ($settings as $cle => $valeur) {
                $stmt = $conn->prepare("
                    INSERT INTO parametre (cle, valeur) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valeur = ?
                ");
                $stmt->bind_param("sss", $cle, $valeur, $valeur);
                $stmt->execute();
                $stmt->close();
            }
            
            $message = "Préférences mises à jour avec succès !";
            $typeMessage = "success";
        }
        
        // Recharger les paramètres
        $parametres = [];
        $result = $conn->query("SELECT * FROM parametre");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $parametres[$row['cle']] = $row['valeur'];
            }
        }
    }
}

// ----- STATISTIQUES SYSTÈME -----
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as total FROM utilisateur")->fetch_assoc()['total'] ?? 0,
    'total_articles' => $conn->query("SELECT COUNT(*) as total FROM article")->fetch_assoc()['total'] ?? 0,
    'total_ventes' => $conn->query("SELECT COUNT(*) as total FROM vente")->fetch_assoc()['total'] ?? 0,
    'total_clients' => $conn->query("SELECT COUNT(*) as total FROM client")->fetch_assoc()['total'] ?? 0,
];

// Version PHP et MySQL
$php_version = phpversion();
$mysql_version = $conn->server_info;
?>

<style>
.config-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

/* HEADER ULTRA-DYNAMIQUE */
.config-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    background-size: 200% 200%;
    animation: gradientFlow 8s ease infinite;
    padding: 30px;
    padding-top: 100px;
    border-radius: 24px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 15px 50px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.config-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: shimmer 3s infinite;
}

@keyframes gradientFlow {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 200%; }
}

.config-header h1 {
    font-size: 36px;
    font-weight: 900;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
    text-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    animation: slideInLeft 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.config-header h1 i {
    animation: rotateIcon 3s ease-in-out infinite;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
}

@keyframes rotateIcon {
    0%, 100% { transform: rotate(0deg) scale(1); }
    50% { transform: rotate(180deg) scale(1.1); }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.config-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
    animation: slideInRight 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.2s backwards;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* TABS ULTRA-STYLÉS */
.tabs-container {
    background: white;
    border-radius: 20px;
    padding: 12px;
    margin-bottom: 30px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.tabs-container::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
    background-size: 200% 100%;
    animation: gradientSlide 3s linear infinite;
}

@keyframes gradientSlide {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.tab-btn {
    padding: 14px 28px;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.tab-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s ease;
}

.tab-btn:hover {
    background: #f8fafc;
    color: #667eea;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
    border-color: #e2e8f0;
}

.tab-btn:hover::before {
    left: 100%;
}

.tab-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    animation: tabPulse 2s ease-in-out infinite;
}

@keyframes tabPulse {
    0%, 100% {
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    50% {
        box-shadow: 0 15px 40px rgba(118, 75, 162, 0.5);
    }
}

.tab-btn i {
    font-size: 20px;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.tab-btn:hover i {
    transform: scale(1.2) rotate(10deg);
}

.tab-btn.active i {
    transform: scale(1.2) rotate(360deg);
    filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.5));
}

/* CONTENT ANIMATION */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeInScale 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes fadeInScale {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(20px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* CARDS ULTRA-DYNAMIQUES */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.settings-card {
    background: white;
    padding: 35px;
    border-radius: 24px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
    overflow: hidden;
    border: 2px solid transparent;
}

.settings-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
    background-size: 200% 100%;
    animation: gradientSlide 3s linear infinite;
}

.settings-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 15px 45px rgba(102, 126, 234, 0.25);
    border-color: rgba(102, 126, 234, 0.3);
}

.card-title {
    font-size: 22px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid transparent;
    background: linear-gradient(to right, #f1f5f9, #e2e8f0);
    border-image: linear-gradient(90deg, #667eea, #764ba2) 1;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideInTitle 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes slideInTitle {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.card-title i {
    font-size: 28px;
    color: #667eea;
    animation: iconBounceCard 2s ease-in-out infinite;
    filter: drop-shadow(0 0 10px rgba(102, 126, 234, 0.4));
}

@keyframes iconBounceCard {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-5px) scale(1.1);
    }
}

/* FORM INPUTS DYNAMIQUES */
.form-group {
    margin-bottom: 22px;
    animation: fadeInUp 0.5s ease backwards;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.15s; }
.form-group:nth-child(3) { animation-delay: 0.2s; }
.form-group:nth-child(4) { animation-delay: 0.25s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    color: #2d3748;
    background-color: #f8fafc;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    background-color: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 5px rgba(102, 126, 234, 0.15), 0 8px 20px rgba(102, 126, 234, 0.2);
    transform: translateY(-2px) scale(1.01);
}

.form-input:disabled {
    background-color: #e2e8f0;
    cursor: not-allowed;
    opacity: 0.7;
}

/* TOGGLE SWITCHES ULTRA-STYLÉS */
.toggle-switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 14px;
    margin-bottom: 15px;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.toggle-switch::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.6s ease;
}

.toggle-switch:hover {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    transform: translateX(5px);
    border-color: rgba(102, 126, 234, 0.3);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
}

.toggle-switch:hover::before {
    left: 100%;
}

.toggle-label {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    transition: all 0.3s ease;
}

.toggle-switch:hover .toggle-label {
    color: #667eea;
}

.toggle-description {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 32px;
    flex-shrink: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-radius: 34px;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
}

.slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 24px;
    left: 4px;
    bottom: 4px;
    background: white;
    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

input:checked + .slider {
    background: linear-gradient(135deg, #667eea, #764ba2);
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.5), inset 0 2px 8px rgba(0, 0, 0, 0.1);
}

input:checked + .slider:before {
    transform: translateX(28px) scale(1.1);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

input:checked + .slider {
    animation: switchGlow 2s ease-in-out infinite;
}

@keyframes switchGlow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.5), inset 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    50% {
        box-shadow: 0 0 30px rgba(118, 75, 162, 0.7), inset 0 2px 8px rgba(0, 0, 0, 0.1);
    }
}

/* BUTTONS ULTRA-DYNAMIQUES */
.btn-save {
    padding: 14px 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-save::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.btn-save:hover::before {
    width: 300px;
    height: 300px;
}

.btn-save:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
}

.btn-save:active {
    transform: translateY(-2px) scale(0.98);
    transition: all 0.1s ease;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
}

.btn-danger:hover {
    box-shadow: 0 15px 40px rgba(239, 68, 68, 0.6);
}

/* ALERTS ANIMÉES */
.alert {
    padding: 16px 22px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 15px;
    font-weight: 700;
    animation: slideInAlert 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

@keyframes slideInAlert {
    0% {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border-left: 5px solid #10b981;
}

.alert-danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #991b1b;
    border-left: 5px solid #ef4444;
}

/* STATS DYNAMIQUES */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.stat-item {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    padding: 25px;
    border-radius: 16px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transform: scaleX(0);
    transition: transform 0.5s ease;
}

.stat-item:hover::before {
    transform: scaleX(1);
}

.stat-item:hover {
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
    border-color: rgba(102, 126, 234, 0.3);
}

.stat-value {
    font-size: 40px;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
    animation: countUp 1s ease;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* INFO BOX DYNAMIQUE */
.info-box {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 50%, #93c5fd 100%);
    background-size: 200% 200%;
    animation: gradientFlow 8s ease infinite;
    padding: 25px;
    border-radius: 16px;
    border-left: 5px solid #3b82f6;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
}

.info-box h4 {
    color: #1e40af;
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-box h4 i {
    animation: pulse 2s ease-in-out infinite;
}

.info-box p {
    color: #1e3a8a;
    font-size: 14px;
    margin: 8px 0;
    font-weight: 600;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .config-container {
        padding: 15px;
    }
    
    .config-header {
        padding: 20px;
        padding-top: 80px;
        border-radius: 16px;
    }
    
    .config-header h1 {
        font-size: 26px;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs-container {
        overflow-x: auto;
        padding: 8px;
    }
    
    .tab-btn {
        padding: 12px 20px;
        font-size: 14px;
    }
    
    .settings-card {
        padding: 25px;
    }
    
    .switch {
        width: 50px;
        height: 28px;
    }
    
    .slider:before {
        height: 20px;
        width: 20px;
    }
    
    input:checked + .slider:before {
        transform: translateX(22px) scale(1.1);
    }
}
</style>

<div class="config-container">
    
    <!-- HEADER -->
    <div class="config-header">
        <h1><i class='bx bx-cog'></i> Configuration</h1>
        <p>⚙️ Paramètres et préférences du système - <?= date('d/m/Y à H:i') ?></p>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $typeMessage ?>">
        <?= $message ?>
    </div>
    <?php endif; ?>

    <?php if (!$is_admin): ?>
    <div class="info-box">
        <h4><i class='bx bx-info-circle'></i> Accès Limité</h4>
        <p>Vous n'avez pas les permissions nécessaires pour modifier les paramètres système.</p>
        <p>Contactez un administrateur si vous avez besoin de faire des modifications.</p>
    </div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tabs-container">
        <button class="tab-btn active" onclick="switchTab('general')">
            <i class='bx bx-building'></i>
            <span>Général</span>
        </button>
        <button class="tab-btn" onclick="switchTab('stock')">
            <i class='bx bx-package'></i>
            <span>Stock</span>
        </button>
        <button class="tab-btn" onclick="switchTab('preferences')">
            <i class='bx bx-palette'></i>
            <span>Préférences</span>
        </button>
        <button class="tab-btn" onclick="switchTab('system')">
            <i class='bx bx-server'></i>
            <span>Système</span>
        </button>
    </div>

    <!-- TAB: GÉNÉRAL -->
    <div id="tab-general" class="tab-content active">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_general">
            
            <div class="settings-grid">
                <div class="settings-card">
                    <div class="card-title">
                        <i class='bx bx-building'></i>
                        <span>Informations Entreprise</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom de l'entreprise</label>
                        <input type="text" name="nom_entreprise" class="form-input" 
                               value="<?= htmlspecialchars($parametres['nom_entreprise']) ?>"
                               <?= !$is_admin ? 'disabled' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email_entreprise" class="form-input" 
                               value="<?= htmlspecialchars($parametres['email_entreprise']) ?>"
                               <?= !$is_admin ? 'disabled' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="telephone_entreprise" class="form-input" 
                               value="<?= htmlspecialchars($parametres['telephone_entreprise']) ?>"
                               <?= !$is_admin ? 'disabled' : '' ?>>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <textarea name="adresse_entreprise" class="form-textarea" 
                                  <?= !$is_admin ? 'disabled' : '' ?>><?= htmlspecialchars($parametres['adresse_entreprise']) ?></textarea>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <i class='bx bx-dollar-circle'></i>
                        <span>Paramètres Financiers</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Devise</label>
                        <select name="devise" class="form-select" <?= !$is_admin ? 'disabled' : '' ?>>
                            <option value="DH" <?= $parametres['devise'] == 'DH' ? 'selected' : '' ?>>Dirham (DH)</option>
                            <option value="EUR" <?= $parametres['devise'] == 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                            <option value="USD" <?= $parametres['devise'] == 'USD' ? 'selected' : '' ?>>Dollar ($)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">TVA (%)</label>
                        <input type="number" name="tva" class="form-input" 
                               value="<?= htmlspecialchars($parametres['tva']) ?>"
                               min="0" max="100" step="0.01"
                               <?= !$is_admin ? 'disabled' : '' ?>>
                    </div>

                    <?php if ($is_admin): ?>
                    <button type="submit" class="btn-save">
                        💾 Enregistrer les modifications
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB: STOCK -->
    <div id="tab-stock" class="tab-content">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_stock">
            
            <div class="settings-card" style="max-width: 600px;">
                <div class="card-title">
                    <i class='bx bx-error'></i>
                    <span>Alertes de Stock</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Seuil d'alerte (Stock Faible)</label>
                    <input type="number" name="stock_alerte" class="form-input" 
                           value="<?= htmlspecialchars($parametres['stock_alerte']) ?>"
                           min="1" <?= !$is_admin ? 'disabled' : '' ?>>
                    <small style="color: #64748b;">Alerte lorsque la quantité est inférieure à cette valeur</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Seuil critique (Stock Critique)</label>
                    <input type="number" name="stock_critique" class="form-input" 
                           value="<?= htmlspecialchars($parametres['stock_critique']) ?>"
                           min="1" <?= !$is_admin ? 'disabled' : '' ?>>
                    <small style="color: #64748b;">Alerte urgente lorsque la quantité est inférieure à cette valeur</small>
                </div>

                <?php if ($is_admin): ?>
                <button type="submit" class="btn-save">
                    💾 Enregistrer les modifications
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TAB: PRÉFÉRENCES -->
    <div id="tab-preferences" class="tab-content">
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_preferences">
            
            <div class="settings-grid">
                <div class="settings-card">
                    <div class="card-title">
                        <i class='bx bx-palette'></i>
                        <span>Apparence</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thème</label>
                        <select name="theme" class="form-select" <?= !$is_admin ? 'disabled' : '' ?>>
                            <option value="light" <?= $parametres['theme'] == 'light' ? 'selected' : '' ?>>Clair</option>
                            <option value="dark" <?= $parametres['theme'] == 'dark' ? 'selected' : '' ?>>Sombre</option>
                            <option value="auto" <?= $parametres['theme'] == 'auto' ? 'selected' : '' ?>>Automatique</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Langue</label>
                        <select name="langue" class="form-select" <?= !$is_admin ? 'disabled' : '' ?>>
                            <option value="fr" <?= $parametres['langue'] == 'fr' ? 'selected' : '' ?>>🇫🇷 Français</option>
                            <option value="ar" <?= $parametres['langue'] == 'ar' ? 'selected' : '' ?>>🇲🇦 العربية</option>
                            <option value="en" <?= $parametres['langue'] == 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                        </select>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-title">
                        <i class='bx bx-bell'></i>
                        <span>Notifications</span>
                    </div>
                    
                    <div class="toggle-switch">
                        <div>
                            <div class="toggle-label">Notifications par Email</div>
                            <div class="toggle-description">Recevoir des emails pour les événements importants</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="notifications_email" 
                                   <?= $parametres['notifications_email'] == '1' ? 'checked' : '' ?>
                                   <?= !$is_admin ? 'disabled' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="toggle-switch">
                        <div>
                            <div class="toggle-label">Alertes de Stock</div>
                            <div class="toggle-description">Recevoir des alertes pour les stocks faibles</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="notifications_stock" 
                                   <?= $parametres['notifications_stock'] == '1' ? 'checked' : '' ?>
                                   <?= !$is_admin ? 'disabled' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="toggle-switch">
                        <div>
                            <div class="toggle-label">Sauvegarde Automatique</div>
                            <div class="toggle-description">Sauvegarder automatiquement les données</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="sauvegarde_auto" 
                                   <?= $parametres['sauvegarde_auto'] == '1' ? 'checked' : '' ?>
                                   <?= !$is_admin ? 'disabled' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <?php if ($is_admin): ?>
                    <button type="submit" class="btn-save">
                        💾 Enregistrer les modifications
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB: SYSTÈME -->
    <div id="tab-system" class="tab-content">
        <div class="settings-card">
            <div class="card-title">
                <i class='bx bx-server'></i>
                <span>Informations Système</span>
            </div>
            
            <div class="info-box">
                <h4><i class='bx bx-info-circle'></i> Détails du Serveur</h4>
                <p><strong>Version PHP:</strong> <?= $php_version ?></p>
                <p><strong>Version MySQL:</strong> <?= $mysql_version ?></p>
                <p><strong>Système:</strong> <?= php_uname('s') . ' ' . php_uname('r') ?></p>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Utilisateurs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total_articles'] ?></div>
                    <div class="stat-label">Articles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total_ventes'] ?></div>
                    <div class="stat-label">Ventes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total_clients'] ?></div>
                    <div class="stat-label">Clients</div>
                </div>
            </div>

            <?php if ($is_admin): ?>
            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="button" class="btn-save" onclick="alert('Sauvegarde en cours...')">
                    <i class='bx bx-save'></i> Sauvegarder la BDD
                </button>
                <button type="button" class="btn-save btn-danger" onclick="if(confirm('Êtes-vous sûr ?')) alert('Cache vidé !')">
                    <i class='bx bx-trash'></i> Vider le Cache
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</section>

<script>
function switchTab(tabName) {
    // Cacher tous les contenus
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Désactiver tous les boutons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activer le contenu et le bouton sélectionnés
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.closest('.tab-btn').classList.add('active');
}
</script>

<?php include "pied.php"; ?>