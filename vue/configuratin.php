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

/* HEADER */
.config-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    padding-top: 100px;
    border-radius: 20px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.config-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.config-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

/* TABS */
.tabs-container {
    background: white;
    border-radius: 20px;
    padding: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 25px;
    background: transparent;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    background: #f8fafc;
    color: #667eea;
}

.tab-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.tab-btn i {
    font-size: 18px;
}

/* CONTENT */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* CARDS */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.settings-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title i {
    font-size: 24px;
    color: #667eea;
}

/* FORM */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    color: #2d3748;
    background-color: #f7fafc;
    transition: all 0.3s ease;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    background-color: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-input:disabled {
    background-color: #e2e8f0;
    cursor: not-allowed;
}

.toggle-switch {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.toggle-switch:hover {
    background: #f1f5f9;
}

.toggle-label {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.toggle-description {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
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
    background-color: #cbd5e1;
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* BUTTONS */
.btn-save {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
}

.btn-danger:hover {
    box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
}

.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

/* STATS */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.stat-item {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #667eea;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}

/* INFO BOX */
.info-box {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
    margin-bottom: 20px;
}

.info-box h4 {
    color: #1e40af;
    margin-bottom: 10px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-box p {
    color: #1e3a8a;
    font-size: 14px;
    margin: 5px 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .config-container {
        padding: 15px;
    }
    
    .config-header {
        padding: 20px;
        padding-top: 80px;
    }
    
    .config-header h1 {
        font-size: 24px;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs-container {
        overflow-x: auto;
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