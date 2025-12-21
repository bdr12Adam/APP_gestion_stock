<?php
include "entete.php";
include "../model/connexion.php";

$message = "";
$typeMessage = "";

// ----- TRAITEMENT FORMULAIRE AJOUT/MODIFICATION -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'ajouter') {
        if (
            empty($_POST["nom"]) ||
            empty($_POST["prenom"]) ||
            empty($_POST["email"]) ||
            empty($_POST["mot_de_passe"]) ||
            empty($_POST["role"])
        ) {
            $message = "Tous les champs sont obligatoires.";
            $typeMessage = "danger";
        } else {
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $email = $_POST["email"];
            $telephone = $_POST["telephone"] ?? "";
            $mot_de_passe = password_hash($_POST["mot_de_passe"], PASSWORD_DEFAULT);
            $role = $_POST["role"];
            $statut = $_POST["statut"] ?? "actif";

            // Vérifier si l'email existe déjà
            $check = $conn->prepare("SELECT id FROM utilisateur WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                $message = "Cet email est déjà utilisé.";
                $typeMessage = "danger";
            } else {
                $req = $conn->prepare("
                    INSERT INTO utilisateur 
                    (nom, prenom, email, telephone, mot_de_passe, role, statut, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $req->bind_param("sssssss", $nom, $prenom, $email, $telephone, $mot_de_passe, $role, $statut);

                if ($req->execute()) {
                    $message = "Utilisateur ajouté avec succès !";
                    $typeMessage = "success";
                } else {
                    $message = "Erreur : " . $conn->error;
                    $typeMessage = "danger";
                }
                $req->close();
            }
            $check->close();
        }
    }
}

// ----- SUPPRESSION -----
if (isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    $req = $conn->prepare("DELETE FROM utilisateur WHERE id = ?");
    $req->bind_param("i", $id);
    if ($req->execute()) {
        $message = "Utilisateur supprimé avec succès !";
        $typeMessage = "success";
    } else {
        $message = "Erreur lors de la suppression.";
        $typeMessage = "danger";
    }
    $req->close();
}

// ----- CHANGEMENT DE STATUT -----
if (isset($_GET['toggle_statut'])) {
    $id = intval($_GET['toggle_statut']);
    $req = $conn->prepare("UPDATE utilisateur SET statut = IF(statut = 'actif', 'inactif', 'actif') WHERE id = ?");
    $req->bind_param("i", $id);
    if ($req->execute()) {
        $message = "Statut modifié avec succès !";
        $typeMessage = "success";
    }
    $req->close();
}

// ----- RÉCUPÉRATION DES UTILISATEURS -----
$utilisateurs = $conn->query("
    SELECT * FROM utilisateur 
    ORDER BY date_creation DESC
");

// ----- STATISTIQUES -----
$total_users = $conn->query("SELECT COUNT(*) as total FROM utilisateur")->fetch_assoc()['total'] ?? 0;
$users_actifs = $conn->query("SELECT COUNT(*) as total FROM utilisateur WHERE statut = 'actif'")->fetch_assoc()['total'] ?? 0;
$users_inactifs = $conn->query("SELECT COUNT(*) as total FROM utilisateur WHERE statut = 'inactif'")->fetch_assoc()['total'] ?? 0;
$admins = $conn->query("SELECT COUNT(*) as total FROM utilisateur WHERE role = 'admin'")->fetch_assoc()['total'] ?? 0;

// ----- RÉCUPÉRATION POUR MODIFICATION -----
$user_modif = null;
if (isset($_GET['modifier'])) {
    $id = intval($_GET['modifier']);
    $req = $conn->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $req->bind_param("i", $id);
    $req->execute();
    $result = $req->get_result();
    $user_modif = $result->fetch_assoc();
    $req->close();
}
?>

<style>
.users-container {
    padding: 20px;
    max-width: 1600px;
    margin: 0 auto;
}

/* HEADER */
.users-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    padding-top: 100px;
    border-radius: 20px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.users-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.users-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

/* CARTES STATISTIQUES */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card:nth-child(1)::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.stat-card:nth-child(2)::before { background: linear-gradient(90deg, #10b981, #059669); }
.stat-card:nth-child(3)::before { background: linear-gradient(90deg, #ef4444, #b91c1c); }
.stat-card:nth-child(4)::before { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #ef4444, #b91c1c); }
.stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }

.stat-info h3 {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #1e293b;
}

/* GRILLE PRINCIPALE */
.main-grid {
    display: grid;
    grid-template-columns: 450px 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

/* FORMULAIRE */
.form-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.form-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 25px;
    text-align: center;
}

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

.form-input, .form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    color: #2d3748;
    background-color: #f7fafc;
    transition: all 0.3s ease;
}

.form-input:focus, .form-select:focus {
    outline: none;
    background-color: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-submit {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* TABLEAU */
.table-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.table-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.search-box {
    position: relative;
}

.search-input {
    padding: 10px 40px 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    font-size: 14px;
    width: 300px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.users-table-wrapper {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.users-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.users-table th {
    padding: 15px 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #cbd5e1;
}

.users-table td {
    padding: 16px 12px;
    font-size: 14px;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
}

.users-table tbody tr {
    transition: all 0.2s ease;
}

.users-table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 16px;
    margin-right: 10px;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.badge-admin {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: white;
}

.badge-user {
    background: #dbeafe;
    color: #1e40af;
}

.badge-actif {
    background: #d1fae5;
    color: #065f46;
}

.badge-inactif {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-toggle {
    background: #f59e0b;
    color: white;
}

.btn-toggle:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media (max-width: 1200px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
    
    .form-card {
        position: relative;
        top: 0;
    }
}

@media (max-width: 768px) {
    .users-container {
        padding: 15px;
    }
    
    .users-header {
        padding: 20px;
        padding-top: 80px;
    }
    
    .users-header h1 {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .search-input {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="users-container">
    
    <!-- HEADER -->
    <div class="users-header">
        <h1><i class='bx bx-user'></i> Gestion des Utilisateurs</h1>
        <p>👥 Administration des comptes et des accès - <?= date('d/m/Y à H:i') ?></p>
    </div>

    <!-- STATISTIQUES -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Total Utilisateurs</h3>
                    <div class="stat-value"><?= $total_users ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-user'></i></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Actifs</h3>
                    <div class="stat-value"><?= $users_actifs ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-user-check'></i></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Inactifs</h3>
                    <div class="stat-value"><?= $users_inactifs ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-user-x'></i></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Administrateurs</h3>
                    <div class="stat-value"><?= $admins ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-shield'></i></div>
            </div>
        </div>
    </div>

    <!-- GRILLE PRINCIPALE -->
    <div class="main-grid">
        
        <!-- FORMULAIRE -->
        <div class="form-card">
            <div class="form-title">
                <?= isset($_GET['modifier']) ? '✏️ Modifier Utilisateur' : '➕ Ajouter Utilisateur' ?>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?= $typeMessage ?>">
                <?= $message ?>
            </div>
            <?php endif; ?>

            <form action="" method="post">
                <input type="hidden" name="action" value="ajouter">
                
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-input" required 
                           value="<?= $user_modif['nom'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-input" required
                           value="<?= $user_modif['prenom'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" required
                           value="<?= $user_modif['email'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-input"
                           value="<?= $user_modif['telephone'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="mot_de_passe" class="form-input" 
                           <?= !isset($_GET['modifier']) ? 'required' : '' ?>>
                    <?php if (isset($_GET['modifier'])): ?>
                        <small style="color: #64748b;">Laissez vide pour ne pas modifier</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <option value="admin" <?= isset($user_modif) && $user_modif['role'] == 'admin' ? 'selected' : '' ?>>Administrateur</option>
                        <option value="user" <?= isset($user_modif) && $user_modif['role'] == 'user' ? 'selected' : '' ?>>Utilisateur</option>
                        <option value="manager" <?= isset($user_modif) && $user_modif['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Statut *</label>
                    <select name="statut" class="form-select" required>
                        <option value="actif" <?= isset($user_modif) && $user_modif['statut'] == 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= isset($user_modif) && $user_modif['statut'] == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    <?= isset($_GET['modifier']) ? '💾 Mettre à jour' : '➕ Ajouter' ?>
                </button>

                <?php if (isset($_GET['modifier'])): ?>
                    <a href="utilisateur.php" style="display:block;text-align:center;margin-top:10px;color:#667eea;text-decoration:none;font-weight:600;">
                        ← Annuler
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLEAU -->
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">📋 Liste des Utilisateurs</div>
                <div class="search-box">
                    <input type="text" class="search-input" id="searchTable" placeholder="🔍 Rechercher...">
                    <i class='bx bx-search search-icon'></i>
                </div>
            </div>

            <div class="users-table-wrapper">
                <table class="users-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($utilisateurs && $utilisateurs->num_rows > 0): ?>
                            <?php while ($user = $utilisateurs->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['telephone'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] == 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= $user['role'] == 'admin' ? '🛡️ Admin' : '👤 ' . ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $user['statut'] == 'actif' ? 'badge-actif' : 'badge-inactif' ?>">
                                        <?= $user['statut'] == 'actif' ? '✅ Actif' : '❌ Inactif' ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['date_creation'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?toggle_statut=<?= $user['id'] ?>" class="btn-action btn-toggle" title="Changer statut">
                                            <i class='bx bx-refresh'></i>
                                        </a>
                                        <a href="?modifier=<?= $user['id'] ?>" class="btn-action btn-edit" title="Modifier">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <a href="?supprimer=<?= $user['id'] ?>" class="btn-action btn-delete" title="Supprimer"
                                           onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <div style="font-size: 48px; margin-bottom: 10px;">👥</div>
                                    Aucun utilisateur trouvé
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</section>

<script>
// RECHERCHE TABLEAU
document.getElementById('searchTable').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include "pied.php"; ?>