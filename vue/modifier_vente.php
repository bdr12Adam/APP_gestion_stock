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

                // Vérifier si la colonne 'prix' existe, sinon utiliser 'prix_total'
                $stmt3 = $conn->prepare("
                    UPDATE vente 
                    SET id_article=?, id_client=?, quantite=?, prix_total=?
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
                
                if (!$stmt3->execute()) {
                    throw new Exception("❌ Erreur lors de la mise à jour : " . $stmt3->error);
                }
                $stmt3->close();

                $conn->commit();

                // ✅ REDIRECTION IMMÉDIATE APRÈS SUCCÈS
                ?>
                <script>
                    window.location.href = 'vente.php';
                </script>
                <?php
                exit;

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
/* ==========================
   FORMULAIRE DE MODIFICATION
   ========================== */

.edit-container {
    max-width: 700px;
    margin: 40px auto;
    padding: 0 20px;
}

.edit-card {
    background: #ffffff;
    padding: 40px 45px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: none;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Barre décorative gradient en haut */
.edit-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #7a32e5ff 0%, #7505e4ff 100%);
}

/* Titre */
.edit-card h4 {
    font-size: 26px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
    text-align: center;
    position: relative;
}

.edit-card .subtitle {
    text-align: center;
    color: #718096;
    font-size: 14px;
    margin-bottom: 30px;
    font-weight: 500;
}

/* Labels */
.edit-card label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 7px;
}

/* Inputs et selects */
.input-style-edit {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    color: #2d3748;
    background-color: #f7fafc;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}

.input-style-edit:hover {
    background-color: #ffffff;
    border-color: #cbd5e0;
}

.input-style-edit:focus {
    background-color: #ffffff;
    border-color: rgba(154, 55, 235, 1);
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    transform: translateY(-2px);
}

/* Select personnalisé */
select.input-style-edit {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 45px;
}

/* Groupes de formulaire */
.form-group-edit {
    margin-bottom: 20px;
}

/* Conteneur des boutons */
.button-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

/* Bouton Mettre à jour */
.btn-update {
    flex: 1;
    padding: 14px 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #ab58ffff 0%, #ac03e0ff 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.btn-update:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(171, 11, 245, 0.45);
    background: linear-gradient(135deg, #880ce8ff 0%, #780bf5ff 100%);
}

.btn-update:active {
    transform: translateY(-1px);
}

/* Bouton Annuler */
.btn-cancel {
    flex: 1;
    padding: 14px 0;
    font-size: 15px;
    font-weight: 700;
    color: #4a5568;
    background: #e2e8f0;
    border: 2px solid #cbd5e0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-cancel:hover {
    background: #cbd5e0;
    border-color: #a0aec0;
    transform: translateY(-2px);
    color: #2d3748;
}

/* Alert messages */
.alert-edit {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    margin-bottom: 20px;
    animation: slideInDown 0.4s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-edit.alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #dc2626;
}

.alert-edit.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

/* Badge ID */
.article-id-badge {
    display: inline-block;
    background: linear-gradient(135deg, #da0bf5ff 0%, #d836edff 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

/* Info Prix */
.info-prix {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
    padding: 14px 18px;
    border-radius: 10px;
    font-size: 15px;
    color: #92400e;
    font-weight: 700;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
    .edit-container {
        margin: 20px auto;
    }
    
    .edit-card {
        padding: 30px 25px;
        border-radius: 16px;
    }
    
    .edit-card h4 {
        font-size: 22px;
    }
    
    .button-group {
        flex-direction: column;
    }
    
    .btn-update,
    .btn-cancel {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .edit-card {
        padding: 25px 20px;
    }
    
    .input-style-edit {
        font-size: 14px;
        padding: 11px 14px;
    }
}
</style>

<section class="home-section">
    <div class="home-content">
        <div class="edit-container">
            <div class="edit-card">
                
                <h4>✏️ Modifier une vente</h4>
                <div class="subtitle">Modifiez les informations de la vente ci-dessous</div>
                
                <div class="text-center">
                    <span class="article-id-badge">ID: <?= $vente['id'] ?></span>
                </div>

                <?php if ($message): ?>
                    <div class="alert-edit alert-<?= $typeMessage ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="modifVenteForm">
                    
                    <div class="form-group-edit">
                        <label>Article *</label>
                        <select name="id_article" id="id_article" class="input-style-edit" onchange="updatePrix()" required>
                            <?php foreach ($articles as $art): ?>
                                <option value="<?= $art['id'] ?>"
                                    data-prix="<?= $art['prix_unitaire'] ?>"
                                    data-stock="<?= $art['quantite'] + ($art['id'] == $vente['id_article'] ? $quantite_originale : 0) ?>"
                                    <?= $art['id'] == $vente['id_article'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($art['nom_article']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-edit">
                        <label>Client *</label>
                        <select name="id_client" class="input-style-edit" required>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $vente['id_client'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nom']) ?> <?= htmlspecialchars($c['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-edit">
                        <label>Quantité *</label>
                        <input type="number" 
                               name="quantite" 
                               id="quantite"
                               class="input-style-edit"
                               min="1"
                               value="<?= $vente['quantite'] ?>"
                               oninput="updatePrix()" 
                               required>
                    </div>

                    <div class="info-prix" id="info_prix">
                        💰 Nouveau Prix Total : <span id="prix_total_display">0.00</span> DH
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-update">
                            💾 Enregistrer
                        </button>
                        <a href="vente.php" class="btn-cancel">
                            ❌ Annuler
                        </a>
                    </div>

                </form>

            </div>
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