<?php  
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$typeMessage = "";

// Récupérer les messages de session (après suppression/modification)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $typeMessage = $_SESSION['typeMessage'];
    unset($_SESSION['message']);
    unset($_SESSION['typeMessage']);
}

// ----- TRAITEMENT FORMULAIRE -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST["nom"]) ||
        empty($_POST["prenom"]) ||
        empty($_POST["telephone"]) ||
        empty($_POST["adresse"])
    ) {
        $message = "Tous les champs sont obligatoires.";
        $typeMessage = "danger";

    } else {

        $nom = $_POST["nom"];
        $prenom = $_POST["prenom"];
        $telephone = $_POST["telephone"];
        $adresse = $_POST["adresse"];

        $req = $conn->prepare("
            INSERT INTO fournisseur 
            (nom, prenom, telephone, adresse)
            VALUES (?, ?, ?, ?)
        ");

        $req->bind_param("ssss", $nom, $prenom, $telephone, $adresse);

        if ($req->execute()) {
            $message = "Fournisseur ajouté avec succès !";
            $typeMessage = "success";
        } else {
            $message = "Erreur : " . $conn->error;
            $typeMessage = "danger";
        }

        $req->close();
    }
}

// ----- RÉCUPÉRATION DES FOURNISSEURS -----
$sql = "SELECT * FROM fournisseur ORDER BY id DESC";
$result = $conn->query($sql);
$fournisseurs = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fournisseurs[] = $row;
    }
}

?>

<!-- **************  CSS FINAL ************** -->
<style>
/* ==========================
  LAYOUT FLEX CONTAINER
   ========================== */

.flex-container {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    margin-top: 20px;
    padding: 0 15px;
}

/* ==========================
   FORMULAIRE MODERNE
   ========================== */

.form-box {
    flex: 0 0 450px;
    max-width: 450px;
}

.form-box .card {
    background: #ffffff;
    padding: 25px 30px;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    border: none;
    position: relative;
    overflow: hidden;
    animation: slideInLeft 0.6s ease-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Barre décorative gradient */
.form-box .card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

/* Titre du formulaire */
.form-box h4 {
    font-size: 20px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 18px;
    text-align: center;
    position: relative;
    padding-bottom: 10px;
}

.form-box h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 2px;
}

/* Labels */
.form-box label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 6px;
}

/* Inputs et selects */
.input-style {
    width: 100%;
    padding: 11px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 9px;
    font-size: 14px;
    color: #2d3748;
    background-color: #f7fafc;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}

.input-style:hover {
    background-color: #ffffff;
    border-color: #cbd5e0;
}

.input-style:focus {
    background-color: #ffffff;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    transform: translateY(-2px);
}

.input-style::placeholder {
    color: #a0aec0;
    font-size: 14px;
}

/* Bouton */
.form-box .btn-primary {
    width: 100%;
    padding: 12px 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 8px;
}

.form-box .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.form-box .btn-primary:active {
    transform: translateY(-1px);
}

/* Alert messages */
.alert {
    padding: 10px 14px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    margin-top: 12px;
    animation: slideInUp 0.4s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

/* ==========================
   TABLEAU MODERNE OPTIMISÉ
   ========================== */

.table-box {
    flex: 2;
    min-width: 0;
    width: 100%;
}

.table-box .card {
    background: #ffffff;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    border: none;
    animation: slideInRight 0.6s ease-out;
    overflow: hidden;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Titre du tableau */
.table-box h4 {
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid #e2e8f0;
}

/* Conteneur responsive du tableau */
.table-responsive {
    border-radius: 12px;
    overflow-x: auto;
    overflow-y: visible;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Style du tableau */
.table {
    margin-bottom: 0;
    border-collapse: collapse;
    width: 100%;
    min-width: 700px;
}

/* Header du tableau */
.table-dark {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%) !important;
}

.table-dark th {
    padding: 15px 12px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
    border: none;
    text-align: center;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Corps du tableau */
.table tbody td {
    padding: 14px 12px;
    font-size: 14px;
    color: #4a5568;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
    text-align: center;
}

/* Colonne Actions */
.table tbody td:last-child {
    min-width: 180px;
    white-space: nowrap;
}

/* Lignes alternées */
.table-striped tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
}

.table-striped tbody tr:nth-child(even) {
    background-color: #ffffff;
}

/* Hover effet sur les lignes */
.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #e9ecef !important;
    transform: scale(1.002);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

/* ==========================
   BOUTONS D'ACTION
   ========================== */

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    min-width: 80px;
    margin: 0 4px;
}

.btn-edit {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* ==========================
   RESPONSIVE DESIGN
   ========================== */

@media (max-width: 1200px) {
    .flex-container {
        flex-direction: column;
        gap: 25px;
    }
    
    .form-box {
        flex: 1;
        max-width: 100%;
        width: 100%;
    }
    
    .table-box {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .flex-container {
        padding: 0 10px;
        gap: 20px;
    }
    
    .form-box .card,
    .table-box .card {
        padding: 20px 15px;
    }
    
    .btn-action {
        padding: 6px 10px;
        font-size: 12px;
        min-width: 70px;
    }
}

/* Animation sur le bouton */
@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
    }
    50% {
        box-shadow: 0 4px 25px rgba(16, 185, 129, 0.55);
    }
}

.form-box .btn-primary {
    animation: pulse 2s infinite;
}

.form-box .btn-primary:hover {
    animation: none;
}

/* Effet fade-in sur les lignes du tableau */
.table tbody tr {
    animation: fadeIn 0.5s ease-out backwards;
}

.table tbody tr:nth-child(1) { animation-delay: 0.1s; }
.table tbody tr:nth-child(2) { animation-delay: 0.15s; }
.table tbody tr:nth-child(3) { animation-delay: 0.2s; }
.table tbody tr:nth-child(4) { animation-delay: 0.25s; }
.table tbody tr:nth-child(5) { animation-delay: 0.3s; }

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
</style>

<!-- **************  CONTENU ************** -->
<section class="home-section">
    <div class="home-content">

        <div class="flex-container">

            <!-- FORMULAIRE FOURNISSEUR -->
            <div class="form-box">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-4 text-center fw-bold">👤 Ajouter un Fournisseur</h4>

                        <form action="" method="post">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom</label>
                                <input type="text" class="form-control input-style" name="nom" placeholder="Entrez le nom" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Prénom</label>
                                <input type="text" class="form-control input-style" name="prenom" placeholder="Entrez le prénom" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Téléphone</label>
                                <input type="tel" class="form-control input-style" name="telephone" placeholder="Ex: 0612345678" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Adresse</label>
                                <textarea class="form-control input-style" name="adresse" rows="3" placeholder="Entrez l'adresse complète" required></textarea>
                            </div>

                            <button class="btn btn-primary w-100">Enregistrer</button>

                            <?php if ($message): ?>
                                <div class="alert alert-<?= $typeMessage ?> mt-3 text-center fw-bold">
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>

                        </form>

                    </div>
                </div>
            </div>

            <!-- TABLEAU DES FOURNISSEURS -->
            <div class="table-box">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">📋 Liste des Fournisseurs</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($fournisseurs) > 0): ?>
                                        <?php 
                                        $numero = 1; // Compteur pour numérotation séquentielle
                                        foreach ($fournisseurs as $four): 
                                        ?>
                                        <tr>
                                            <td><?= $numero++ ?></td>
                                            <td><?= htmlspecialchars($four['nom']) ?></td>
                                            <td><?= htmlspecialchars($four['prenom']) ?></td>
                                            <td><?= htmlspecialchars($four['telephone']) ?></td>
                                            <td><?= htmlspecialchars($four['adresse']) ?></td>
                                            <td>
                                                <a href="../vue/modifier_fournisseur.php?id=<?= $four['id'] ?>" 
                                                   class="btn-action btn-edit" title="Modifier">
                                                   ✏️ Modifier
                                                </a>

                                                <a href="../vue/supprimer_fournisseur.php?id=<?= $four['id'] ?>" 
                                                   class="btn-action btn-delete" title="Supprimer"
                                                   onclick="return confirm('Voulez-vous vraiment supprimer ce fournisseur ?');">
                                                   🗑️ Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Aucun fournisseur enregistré</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
<?php include "pied.php"; ?>