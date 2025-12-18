<?php
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

$message = "";
$typeMessage = "";

/* ===============================
   RÉCUPÉRATION DE LA COMMANDE
================================ */

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_commande <= 0) {
    header("Location: ../vue/commande.php");
    exit;
}

// Récupération des données de la commande
$stmt = $conn->prepare("SELECT * FROM commande WHERE id = ?");
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();
$commande = $result->fetch_assoc();
$stmt->close();

if (!$commande) {
    header("Location: ../vue/commande.php");
    exit;
}

// Récupération des articles disponibles
$articles = [];
$sql_articles = "SELECT id, nom_article, prix_unitaire, quantite FROM article WHERE quantite > 0 OR id = ? ORDER BY nom_article ASC";
$stmt_articles = $conn->prepare($sql_articles);
$stmt_articles->bind_param("i", $commande['id_article']);
$stmt_articles->execute();
$result_articles = $stmt_articles->get_result();
while ($row = $result_articles->fetch_assoc()) {
    $articles[] = $row;
}
$stmt_articles->close();

// Récupération des fournisseurs
$fournisseurs = [];
$sql_fournisseurs = "SELECT id, nom, prenom FROM fournisseur ORDER BY nom ASC";
$result_fournisseurs = $conn->query($sql_fournisseurs);
while ($row = $result_fournisseurs->fetch_assoc()) {
    $fournisseurs[] = $row;
}

/* ===============================
   TRAITEMENT MODIFICATION
================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['id_article']) ||
        empty($_POST['id_fournisseur']) ||
        empty($_POST['quantite']) ||
        empty($_POST['prix'])
    ) {
        $message = "❌ Tous les champs sont obligatoires.";
        $typeMessage = "danger";
    } else {

        $id_article = intval($_POST['id_article']);
        $id_fournisseur = intval($_POST['id_fournisseur']);
        $quantite = intval($_POST['quantite']);
        $prix = floatval($_POST['prix']);

        $stmt = $conn->prepare("
            UPDATE commande 
            SET id_article = ?, id_fournisseur = ?, quantite = ?, prix = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param("iiidi", $id_article, $id_fournisseur, $quantite, $prix, $id_commande);

        if ($stmt->execute()) {
            $message = "✅ Commande modifiée avec succès.";
            $typeMessage = "success";
            
            // Recharger les données mises à jour
            $stmt2 = $conn->prepare("SELECT * FROM commande WHERE id = ?");
            $stmt2->bind_param("i", $id_commande);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $commande = $result2->fetch_assoc();
            $stmt2->close();
        } else {
            $message = "❌ Erreur lors de la modification : " . $conn->error;
            $typeMessage = "danger";
        }
        
        $stmt->close();
    }
}
?>

<style>
.form-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 35px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #8b5cf6 0%, #6d28d9 100%);
}

.form-container h4 {
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 25px;
    text-align: center;
    position: relative;
    padding-bottom: 12px;
}

.form-container h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #8b5cf6 0%, #6d28d9 100%);
    border-radius: 2px;
}

.form-container label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
    margin-top: 18px;
}

.form-container label:first-of-type {
    margin-top: 0;
}

.input-style {
    width: 100%;
    padding: 12px 16px;
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

select.input-style {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 45px;
}

.info-prix {
    background: #ede9fe;
    border-left: 4px solid #8b5cf6;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    color: #5b21b6;
    font-weight: 600;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary, .btn-secondary {
    flex: 1;
    padding: 13px 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    text-decoration: none;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(139, 92, 246, 0.45);
    background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%);
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    box-shadow: 0 4px 15px rgba(107, 114, 128, 0.35);
}

.btn-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(107, 114, 128, 0.45);
    background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
}

.alert {
    padding: 14px 18px;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 25px;
    animation: slideInDown 0.4s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
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

@media (max-width: 768px) {
    .form-container {
        margin: 20px 15px;
        padding: 25px 20px;
    }
    
    .btn-actions {
        flex-direction: column;
    }
}
</style>

<section class="home-section">
<div class="home-content">

<div class="form-container">
<h4>✏️ Modifier une Commande</h4>

<?php if ($message): ?>
<div class="alert alert-<?= $typeMessage ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<form method="post" id="modifCommandeForm">

    <label>Article *</label>
    <select name="id_article" id="id_article" class="input-style" required onchange="updatePrixArticle()">
        <?php foreach ($articles as $art): ?>
            <option value="<?= $art['id'] ?>"
                data-prix="<?= $art['prix_unitaire'] ?>"
                <?= $art['id'] == $commande['id_article'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($art['nom_article']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Fournisseur *</label>
    <select name="id_fournisseur" class="input-style" required>
        <?php foreach ($fournisseurs as $f): ?>
            <option value="<?= $f['id'] ?>" <?= $f['id'] == $commande['id_fournisseur'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($f['nom']) ?> <?= htmlspecialchars($f['prenom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Quantité *</label>
    <input type="number" name="quantite" id="quantite"
           class="input-style"
           min="1"
           value="<?= $commande['quantite'] ?>"
           oninput="updatePrixTotal()" required>

    <label>Prix Unitaire *</label>
    <input type="number" step="0.01" name="prix" id="prix"
           class="input-style"
           value="<?= $commande['prix'] ?>"
           oninput="updatePrixTotal()" required>

    <div class="info-prix" id="info_prix">
        💰 Prix Total : <span id="prix_total_display">0.00</span> DH
    </div>

    <div class="btn-actions">
        <button type="submit" class="btn-primary">
            💾 Enregistrer les modifications
        </button>
        <a href="../vue/commande.php" class="btn-secondary">
            ❌ Annuler
        </a>
    </div>

</form>

</div>

</div>
</section>

<script>
function updatePrixArticle() {
    const select = document.getElementById('id_article');
    const prixInput = document.getElementById('prix');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const prix = parseFloat(option.dataset.prix);
        prixInput.value = prix.toFixed(2);
        updatePrixTotal();
    }
}

function updatePrixTotal() {
    const quantite = parseFloat(document.getElementById('quantite').value) || 0;
    const prix = parseFloat(document.getElementById('prix').value) || 0;
    const total = quantite * prix;
    
    document.getElementById('prix_total_display').textContent = total.toFixed(2);
}

window.onload = updatePrixTotal;
</script>

<?php include "pied.php"; ?>