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
        empty($_POST["id_article"]) ||
        empty($_POST["id_fournisseur"]) ||
        empty($_POST["quantite"]) ||
        empty($_POST["prix"])
    ) {
        $message = "Tous les champs sont obligatoires.";
        $typeMessage = "danger";

    } else {

        $id_article = intval($_POST["id_article"]);
        $id_fournisseur = intval($_POST["id_fournisseur"]);
        $quantite = intval($_POST["quantite"]);
        $prix = floatval($_POST["prix"]);
        $date_commande = date('Y-m-d H:i:s');

        // Vérifier que l'article existe et récupérer le stock actuel
        $stmt_check = $conn->prepare("SELECT nom_article, quantite FROM article WHERE id = ?");
        $stmt_check->bind_param("i", $id_article);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows == 0) {
            $message = "❌ Article introuvable.";
            $typeMessage = "danger";
        } else {
            $article = $result_check->fetch_assoc();
            $stock_actuel = $article['quantite'];
            $nom_article = $article['nom_article'];
            
            // Vérifier si le stock est suffisant
            if ($quantite > $stock_actuel) {
                $message = "❌ Stock insuffisant pour « $nom_article » ! Stock disponible : $stock_actuel";
                $typeMessage = "danger";
            } else {
                // Commencer la transaction
                $conn->begin_transaction();
                
                try {
                    // Insérer la commande
                    $req = $conn->prepare("
                        INSERT INTO commande 
                        (id_article, id_fournisseur, quantite, prix, date_commande)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $req->bind_param("iiids", $id_article, $id_fournisseur, $quantite, $prix, $date_commande);
                    $req->execute();
                    $req->close();
                    
                    // Déduire la quantité du stock
                    $nouveau_stock = $stock_actuel - $quantite;
                    $stmt_update = $conn->prepare("UPDATE article SET quantite = ? WHERE id = ?");
                    $stmt_update->bind_param("ii", $nouveau_stock, $id_article);
                    $stmt_update->execute();
                    $stmt_update->close();
                    
                    // Valider la transaction
                    $conn->commit();
                    
                    $message = "✅ Commande ajoutée avec succès ! Stock restant : $nouveau_stock";
                    $typeMessage = "success";
                    
                } catch (Exception $e) {
                    // Annuler en cas d'erreur
                    $conn->rollback();
                    $message = "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
                    $typeMessage = "danger";
                }
            }
        }
        $stmt_check->close();
    }
}

// ----- RÉCUPÉRATION DES COMMANDES -----
$sql = "SELECT c.*, 
        a.nom_article, 
        f.nom as fournisseur_nom, 
        f.prenom as fournisseur_prenom 
        FROM commande c
        LEFT JOIN article a ON c.id_article = a.id
        LEFT JOIN fournisseur f ON c.id_fournisseur = f.id
        ORDER BY c.id DESC";
$result = $conn->query($sql);
$commandes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $commandes[] = $row;
    }
}

// ----- RÉCUPÉRATION DES ARTICLES DISPONIBLES -----
$articles = [];
$sql_articles = "SELECT id, nom_article, prix_unitaire, quantite FROM article WHERE quantite > 0 ORDER BY nom_article ASC";
$result_articles = $conn->query($sql_articles);
if ($result_articles && $result_articles->num_rows > 0) {
    while ($row = $result_articles->fetch_assoc()) {
        $articles[] = $row;
    }
}

// ----- RÉCUPÉRATION DES FOURNISSEURS -----
$fournisseurs = [];
$sql_fournisseurs = "SELECT id, nom, prenom FROM fournisseur ORDER BY nom ASC";
$result_fournisseurs = $conn->query($sql_fournisseurs);
if ($result_fournisseurs && $result_fournisseurs->num_rows > 0) {
    while ($row = $result_fournisseurs->fetch_assoc()) {
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
    background: linear-gradient(90deg, #8b5cf6 0%, #6d28d9 100%);
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
    background: linear-gradient(90deg, #8b5cf6 0%, #6d28d9 100%);
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
    border-color: #8b5cf6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    transform: translateY(-2px);
}

.input-style::placeholder {
    color: #a0aec0;
    font-size: 14px;
}

/* Select personnalisé */
select.input-style {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 45px;
}

/* Info Prix */
.info-prix {
    background: #ede9fe;
    border-left: 4px solid #8b5cf6;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 13px;
    color: #5b21b6;
    font-weight: 600;
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Bouton */
.form-box .btn-primary {
    width: 100%;
    padding: 12px 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 8px;
}

.form-box .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(139, 92, 246, 0.45);
    background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%);
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
    min-width: 900px;
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
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
    }
    50% {
        box-shadow: 0 4px 25px rgba(139, 92, 246, 0.55);
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

            <!-- FORMULAIRE COMMANDE -->
            <div class="form-box">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-4 text-center fw-bold">📦 Ajouter une Commande</h4>

                        <form action="" method="post" id="formCommande">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Article *</label>
                                <select class="form-select input-style" name="id_article" id="id_article" required onchange="updatePrixArticle()">
                                    <option value="">-- Choisir un article --</option>
                                    <?php foreach ($articles as $art): ?>
                                        <option value="<?= $art['id'] ?>" 
                                                data-prix="<?= $art['prix_unitaire'] ?>" 
                                                data-stock="<?= $art['quantite'] ?>">
                                            <?= htmlspecialchars($art['nom_article']) ?> (Stock: <?= $art['quantite'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Fournisseur *</label>
                                <select class="form-select input-style" name="id_fournisseur" required>
                                    <option value="">-- Choisir un fournisseur --</option>
                                    <?php foreach ($fournisseurs as $f): ?>
                                        <option value="<?= $f['id'] ?>">
                                            <?= htmlspecialchars($f['nom']) ?> <?= htmlspecialchars($f['prenom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantité *</label>
                                <input type="number" class="form-control input-style" name="quantite" id="quantite" min="1" max="" required onchange="updatePrixTotal()">
                                <small id="stock_info" style="color: #6b7280; font-size: 12px; margin-top: 5px; display: block;"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Prix Unitaire *</label>
                                <input type="number" step="0.01" class="form-control input-style" name="prix" id="prix" required onchange="updatePrixTotal()">
                            </div>

                            <div class="info-prix" id="info_prix">
                                💰 Prix Total : <span id="prix_total">0.00</span> DH
                            </div>

                            <button class="btn btn-primary w-100">💾 Enregistrer</button>

                            <?php if ($message): ?>
                                <div class="alert alert-<?= $typeMessage ?> mt-3 text-center fw-bold">
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>

                        </form>

                    </div>
                </div>
            </div>

            <!-- TABLEAU DES COMMANDES -->
            <div class="table-box">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">📋 Liste des Commandes</h4>
                        

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
                                        <th>Article</th>
                                        <th>Fournisseur</th>
                                        <th>Quantité</th>
                                        <th>Prix Unit.</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (count($commandes) > 0): ?>
                                        <?php 
                                        $numero = 1;
                                        foreach ($commandes as $cmd): 
                                        $total = $cmd['quantite'] * $cmd['prix'];
                                        ?>
                                        <tr>
                                            <td><?= $numero++ ?></td>
                                            <td><?= htmlspecialchars($cmd['nom_article']) ?></td>
                                            <td><?= htmlspecialchars($cmd['fournisseur_nom']) ?> <?= htmlspecialchars($cmd['fournisseur_prenom']) ?></td>
                                            <td><?= $cmd['quantite'] ?></td>
                                            <td><?= number_format($cmd['prix'], 2) ?> DH</td>
                                            <td><?= number_format($total, 2) ?> DH</td>
                                            <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                                            <td>
                                                <a href="../vue/modifier_commande.php?id=<?= $cmd['id'] ?>" 
                                                   class="btn-action btn-edit" title="Modifier">
                                                   ✏️ Modifier
                                                </a>

                                                <a href="../vue/supprimer_commande.php?id=<?= $cmd['id'] ?>" 
                                                   class="btn-action btn-delete" title="Supprimer"
                                                   onclick="return confirm('Voulez-vous vraiment supprimer cette commande ?');">
                                                   🗑️ Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Aucune commande enregistrée</td>
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

<script>
// Mettre à jour le prix unitaire quand on sélectionne un article
function updatePrixArticle() {
    const select = document.getElementById('id_article');
    const prixInput = document.getElementById('prix');
    const quantiteInput = document.getElementById('quantite');
    const stockInfo = document.getElementById('stock_info');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const prix = parseFloat(option.dataset.prix);
        const stock = parseInt(option.dataset.stock);
        
        prixInput.value = prix.toFixed(2);
        quantiteInput.max = stock;
        quantiteInput.value = '';
        
        // Afficher l'info du stock
        if (stock > 0) {
            stockInfo.textContent = `📦 Stock disponible : ${stock} unité(s)`;
            stockInfo.style.color = '#10b981';
            quantiteInput.disabled = false;
        } else {
            stockInfo.textContent = '⚠️ Stock épuisé';
            stockInfo.style.color = '#ef4444';
            quantiteInput.disabled = true;
            quantiteInput.value = '';
        }
        
        updatePrixTotal();
    } else {
        prixInput.value = '';
        quantiteInput.max = '';
        quantiteInput.value = '';
        stockInfo.textContent = '';
        document.getElementById('prix_total').textContent = '0.00';
    }
}

// Calculer le prix total et vérifier le stock
function updatePrixTotal() {
    const select = document.getElementById('id_article');
    const quantiteInput = document.getElementById('quantite');
    const quantite = parseFloat(quantiteInput.value) || 0;
    const prix = parseFloat(document.getElementById('prix').value) || 0;
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const stock = parseInt(option.dataset.stock);
        
        // Limiter la quantité au stock disponible
        if (quantite > stock) {
            quantiteInput.value = stock;
            alert(`⚠️ Quantité limitée au stock disponible : ${stock}`);
        }
    }
    
    const total = (parseFloat(quantiteInput.value) || 0) * prix;
    document.getElementById('prix_total').textContent = total.toFixed(2);
}

// Validation avant soumission
document.getElementById('formCommande').addEventListener('submit', function(e) {
    const select = document.getElementById('id_article');
    const quantite = parseInt(document.getElementById('quantite').value);
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const stock = parseInt(option.dataset.stock);
        
        if (quantite > stock) {
            e.preventDefault();
            alert(`❌ Stock insuffisant ! Stock disponible : ${stock}`);
            return false;
        }
        
        if (quantite <= 0) {
            e.preventDefault();
            alert('❌ La quantité doit être supérieure à 0');
            return false;
        }
    }
});
</script>

<?php include "pied.php"; ?>