<?php
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

$message = "";
$typeMessage = "";

/* ===============================
   RÉCUPÉRATION DE LA VENTE
================================ */

$id_vente = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_vente <= 0) {
    header("Location: vente.php");
    exit;
}

$vente = getVenteById($id_vente);
if (!$vente) {
    header("Location: vente.php");
    exit;
}

$article_original = getArticleById($vente['id_article']);
$quantite_originale = $vente['quantite'];

$articles = getArticlesDisponibles();
$clients  = getClients();

/* ===============================
   TRAITEMENT MODIFICATION
================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['id_article']) ||
        empty($_POST['id_client']) ||
        empty($_POST['quantite'])
    ) {
        $message = "❌ Tous les champs sont obligatoires.";
        $typeMessage = "danger";
    } else {

        $id_article_nouveau = intval($_POST['id_article']);
        $id_client_nouveau  = intval($_POST['id_client']);
        $quantite_nouvelle  = intval($_POST['quantite']);

        $article_nouveau = getArticleById($id_article_nouveau);

        if (!$article_nouveau) {
            $message = "❌ Article introuvable.";
            $typeMessage = "danger";
        } else {

            $conn->begin_transaction();

            try {

                /* ===== CAS 1 : MÊME ARTICLE ===== */
                if ($id_article_nouveau == $vente['id_article']) {

                    $difference = $quantite_nouvelle - $quantite_originale;
                    $nouveau_stock = $article_original['quantite'] - $difference;

                    if ($nouveau_stock < 0) {
                        throw new Exception("❌ Stock insuffisant.");
                    }

                    $stmt = $conn->prepare("UPDATE article SET quantite=? WHERE id=?");
                    $stmt->bind_param("ii", $nouveau_stock, $id_article_nouveau);
                    $stmt->execute();
                    $stmt->close();
                }

                /* ===== CAS 2 : ARTICLE DIFFÉRENT ===== */
                else {

                    // Restaurer ancien article
                    $stock_ancien = $article_original['quantite'] + $quantite_originale;
                    $stmt1 = $conn->prepare("UPDATE article SET quantite=? WHERE id=?");
                    $stmt1->bind_param("ii", $stock_ancien, $vente['id_article']);
                    $stmt1->execute();
                    $stmt1->close();

                    // Déduire nouveau article
                    if ($article_nouveau['quantite'] < $quantite_nouvelle) {
                        throw new Exception("❌ Stock insuffisant.");
                    }

                    $stock_nouveau = $article_nouveau['quantite'] - $quantite_nouvelle;
                    $stmt2 = $conn->prepare("UPDATE article SET quantite=? WHERE id=?");
                    $stmt2->bind_param("ii", $stock_nouveau, $id_article_nouveau);
                    $stmt2->execute();
                    $stmt2->close();
                }

                /* ===== MISE À JOUR VENTE ===== */
                $prix_nouveau = $article_nouveau['prix_unitaire'] * $quantite_nouvelle;

                $stmt3 = $conn->prepare("
                    UPDATE vente 
                    SET id_article=?, id_client=?, quantite=?, prix=?
                    WHERE id=?
                ");
                $stmt3->bind_param(
                    "iiidi",
                    $id_article_nouveau,
                    $id_client_nouveau,
                    $quantite_nouvelle,
                    $prix_nouveau,
                    $id_vente
                );
                $stmt3->execute();
                $stmt3->close();

                $conn->commit();

                $message = "✅ Vente modifiée avec succès.";
                $typeMessage = "success";

                // Recharger données
                $vente = getVenteById($id_vente);
                $article_original = getArticleById($vente['id_article']);
                $quantite_originale = $vente['quantite'];

            } catch (Exception $e) {
                $conn->rollback();
                $message = $e->getMessage();
                $typeMessage = "danger";
            }
        }
    }
}

/* ===============================
   LISTE ARTICLES + CLIENTS
================================ */

// Ajouter l'article actuel s'il n'est plus dispo
$existe = false;
foreach ($articles as $a) {
    if ($a['id'] == $vente['id_article']) {
        $existe = true;
        break;
    }
}
if (!$existe && $article_original) {
    $articles[] = $article_original;
}
?>

<style>
.form-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.form-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
}

.form-container h4 {
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 25px;
    text-align: center;
    position: relative;
    padding-bottom: 10px;
}

.form-container h4::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    border-radius: 2px;
}

.form-container label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 8px;
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
    border-color: #f59e0b;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
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
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
    font-weight: 600;
    margin-top: 15px;
}

.btn-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.btn-primary, .btn-secondary {
    flex: 1;
    padding: 12px 0;
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
}

.btn-primary {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.45);
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
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
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
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
</style>

<section class="home-section">
<div class="home-content">

<div class="form-container">
<h4>✏️ Modifier une Vente</h4>

<?php if ($message): ?>
<div class="alert alert-<?= $typeMessage ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<form method="post" id="modifVenteForm">

<label>Article *</label>
<select name="id_article" id="id_article" class="input-style" onchange="updatePrix()" required>
    <?php foreach ($articles as $art): ?>
        <option value="<?= $art['id'] ?>"
            data-prix="<?= $art['prix_unitaire'] ?>"
            data-stock="<?= $art['quantite'] + ($art['id'] == $vente['id_article'] ? $quantite_originale : 0) ?>"
            <?= $art['id'] == $vente['id_article'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($art['nom_article']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Client *</label>
<select name="id_client" class="input-style" required>
    <?php foreach ($clients as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $c['id'] == $vente['id_client'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nom']) ?> <?= htmlspecialchars($c['prenom']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Quantité *</label>
<input type="number" name="quantite" id="quantite"
       class="input-style"
       min="1"
       value="<?= $vente['quantite'] ?>"
       oninput="updatePrix()" required>

<div class="info-prix" id="info_prix">
    💰 Nouveau Prix Total :
    <span id="prix_total_display">0.00</span> DH
</div>

<div class="btn-actions">
    <button class="btn-primary">💾 Enregistrer</button>
    <a href="vente.php" class="btn-secondary">❌ Annuler</a>
</div>

</form>
</div>

</div>
</section>

<script>
function updatePrix() {
    const a = id_article.options[id_article.selectedIndex];
    const q = parseInt(quantite.value || 0);
    const p = parseFloat(a.dataset.prix);
    const s = parseInt(a.dataset.stock);

    if (q > s) quantite.value = s;
    prix_total_display.innerText = (p * quantite.value).toFixed(2);
}
window.onload = updatePrix;
</script>

<?php include "pied.php"; ?>
