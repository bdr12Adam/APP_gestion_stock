<?php  
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

$message = "";
$typeMessage = "";

// ----- TRAITEMENT FORMULAIRE -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_article'])) {

    if (
        empty($_POST["nom_article"]) ||
        empty($_POST["categorie"]) ||
        empty($_POST["quantite"]) ||
        empty($_POST["prix_unitaire"]) ||
        empty($_POST["date_fabrication"]) ||
        empty($_POST["date_expiration"])
    ) {
        $message = "Tous les champs sont obligatoires.";
        $typeMessage = "danger";

    } else {

        $nom = $_POST["nom_article"];
        $categorie = $_POST["categorie"];
        $quantite = $_POST["quantite"];
        $prix = $_POST["prix_unitaire"];

        $date_fab = str_replace("T", " ", $_POST["date_fabrication"]) . ":00";
        $date_exp = str_replace("T", " ", $_POST["date_expiration"]) . ":00";

        $req = $conn->prepare("
            INSERT INTO article 
            (nom_article, categorie, quantite, prix_unitaire, date_fabrication, date_expiration)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $req->bind_param("ssidds", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp);

        if ($req->execute()) {
            $message = "Article ajouté avec succès !";
            $typeMessage = "success";
        } else {
            $message = "Erreur : " . $conn->error;
            $typeMessage = "danger";
        }

        $req->close();
    }
}

// ----- TRAITEMENT RECHERCHE -----
$searchTerm = "";
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
}

// ----- RÉCUPÉRATION DES ARTICLES (avec recherche) -----
if (!empty($searchTerm)) {
    // Recherche dans la base de données
    $search = "%{$searchTerm}%";
    $req = $conn->prepare("
        SELECT * FROM article 
        WHERE nom_article LIKE ? 
        OR categorie LIKE ? 
        OR CAST(id AS CHAR) LIKE ?
        ORDER BY id DESC
    ");
    $req->bind_param("sss", $search, $search, $search);
    $req->execute();
    $result = $req->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $req->close();
} else {
    // Récupération normale de tous les articles
    $articles = getArticles();
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
   BARRE DE RECHERCHE MODERNE
   ========================== */

.search-container {
    margin-bottom: 20px;
    animation: slideInDown 0.5s ease-out;
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

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 500px;
    margin: 0 auto;
}

.search-box input {
    width: 100%;
    padding: 14px 50px 14px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 50px;
    font-size: 15px;
    color: #2d3748;
    background-color: #f7fafc;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.search-box input:focus {
    background-color: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.search-box input::placeholder {
    color: #a0aec0;
}

.search-box .search-icon {
    position: absolute;
    right: 18px;
    font-size: 20px;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-box .search-icon:hover {
    color: #764ba2;
    transform: scale(1.15);
}

/* Bouton de réinitialisation */
.reset-search {
    position: absolute;
    right: 50px;
    font-size: 18px;
    color: #a0aec0;
    cursor: pointer;
    transition: all 0.3s ease;
    display: none;
}

.reset-search.active {
    display: block;
}

.reset-search:hover {
    color: #ef4444;
    transform: scale(1.2);
}

/* Badge de résultats */
.search-results-badge {
    text-align: center;
    margin-top: 15px;
    padding: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    animation: slideInUp 0.4s ease-out;
}

.search-results-badge .clear-link {
    color: white;
    text-decoration: underline;
    margin-left: 10px;
    cursor: pointer;
    font-weight: 700;
}

.search-results-badge .clear-link:hover {
    color: #ffd700;
}

/* Message "Aucun résultat" */
.no-results {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-top: 20px;
}

.no-results i {
    font-size: 60px;
    color: #cbd5e0;
    margin-bottom: 15px;
}

.no-results h5 {
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 10px;
}

.no-results p {
    color: #a0aec0;
    font-size: 14px;
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
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
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
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
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
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
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

/* Bouton */
.form-box .btn-primary {
    width: 100%;
    padding: 12px 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 8px;
}

.form-box .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.45);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
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
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Colonne Actions */
.table tbody td:last-child {
    min-width: 180px;
    max-width: 200px;
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

/* Highlight pour résultats de recherche */
.table tbody tr.highlight {
    background-color: #fff3cd !important;
    animation: highlightFade 2s ease-out;
}

@keyframes highlightFade {
    0% {
        background-color: #ffd700;
    }
    100% {
        background-color: #fff3cd;
    }
}

/* ==========================
   BOUTONS D'ACTION
   ========================== */

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

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
    .search-box {
        max-width: 100%;
    }
    
    .search-box input {
        padding: 12px 45px 12px 16px;
        font-size: 14px;
    }
    
    .flex-container {
        padding: 0 10px;
        gap: 20px;
    }
    
    .table-box h4 {
        font-size: 18px;
    }
}

@media (max-width: 576px) {
    .search-box input {
        padding: 10px 40px 10px 14px;
        font-size: 13px;
    }
    
    .search-box .search-icon {
        right: 12px;
        font-size: 18px;
    }
}
</style>

<!-- **************  CONTENU ************** -->
<section class="home-section">
    <div class="home-content">

        <div class="flex-container">

            <!-- FORMULAIRE -->
            <div class="form-box">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-4 text-center fw-bold">+ Ajouter un article</h4>

                        <form action="" method="post">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom de l'article</label>
                                <input type="text" class="form-control input-style" name="nom_article">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Catégorie</label>
                                <select class="form-select input-style" name="categorie">
                                    <option value="">-- Choisir --</option>
                                    <option value="Ordinateur">Ordinateur</option>
                                    <option value="Imprimante">Imprimante</option>
                                    <option value="Accessoire">Accessoire</option>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantité</label>
                                <input type="number" class="form-control input-style" name="quantite">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Prix Unitaire</label>
                                <input type="number" class="form-control input-style" name="prix_unitaire">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Date de fabrication</label>
                                <input type="datetime-local" class="form-control input-style" name="date_fabrication">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Date d'expiration</label>
                                <input type="datetime-local" class="form-control input-style" name="date_expiration">
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

            <!-- TABLEAU -->
            <div class="table-box">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">📊 Liste des articles</h4>

                        <!-- BARRE DE RECHERCHE -->
                        <div class="search-container">
                            <form action="" method="get" id="searchForm">
                                <div class="search-box">
                                    <input 
                                        type="text" 
                                        name="search" 
                                        id="searchInput"
                                        placeholder="🔍 Rechercher par nom, catégorie ou ID..." 
                                        value="<?= htmlspecialchars($searchTerm) ?>"
                                        autocomplete="off"
                                    />
                                    <i class='bx bx-x reset-search <?= !empty($searchTerm) ? "active" : "" ?>' 
                                       id="resetSearch" 
                                       title="Effacer"></i>
                                    <i class='bx bx-search search-icon' onclick="document.getElementById('searchForm').submit()"></i>
                                </div>
                            </form>

                            <?php if (!empty($searchTerm)): ?>
                            <div class="search-results-badge">
                                <?= count($articles) ?> résultat(s) trouvé(s) pour "<?= htmlspecialchars($searchTerm) ?>"
                                <a href="?" class="clear-link">Afficher tout</a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive">
                            <?php if (count($articles) > 0): ?>
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Quantité</th>
                                        <th>Prix</th>
                                        <th>Date fabrication</th>
                                        <th>Date expiration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($articles as $art): ?>
                                    <tr>
                                        <td><?= $art['id'] ?></td>
                                        <td><?= htmlspecialchars($art['nom_article']) ?></td>
                                        <td><?= htmlspecialchars($art['categorie']) ?></td>
                                        <td><?= $art['quantite'] ?></td>
                                        <td><?= number_format($art['prix_unitaire'], 2) ?> DH</td>
                                        <td><?= date('d/m/Y H:i', strtotime($art['date_fabrication'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($art['date_expiration'])) ?></td>
                                        <td>
                                            <a href="modifier_article.php?id=<?= $art['id'] ?>" 
                                               class="btn-action btn-edit" title="Modifier">
                                               ✏️
                                            </a>

                                            <a href="../vue/supprimer_article.php?id=<?= $art['id'] ?>" 
                                               class="btn-action btn-delete" title="Supprimer"
                                               onclick="return confirm('Voulez-vous vraiment supprimer cet article ?');">
                                               🗑️
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="no-results">
                                <i class='bx bx-search-alt'></i>
                                <h5>Aucun résultat trouvé</h5>
                                <p>Aucun article ne correspond à votre recherche "<?= htmlspecialchars($searchTerm) ?>"</p>
                                <a href="?" class="btn btn-primary mt-3">Afficher tous les articles</a>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- SCRIPT JAVASCRIPT POUR LA RECHERCHE -->
<script>
    // Recherche en temps réel (optionnel)
    const searchInput = document.getElementById('searchInput');
    const resetSearch = document.getElementById('resetSearch');
    const searchForm = document.getElementById('searchForm');

    // Soumission automatique après une pause de frappe
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        // Afficher/masquer le bouton reset
        if (this.value.length > 0) {
            resetSearch.classList.add('active');
        } else {
            resetSearch.classList.remove('active');
        }
        
        // Recherche automatique après 500ms d'inactivité
        searchTimeout = setTimeout(() => {
            if (this.value.length >= 2 || this.value.length === 0) {
                searchForm.submit();
            }
        }, 500);
    });

    // Bouton reset
    resetSearch.addEventListener('click', function() {
        searchInput.value = '';
        this.classList.remove('active');
        window.location.href = '?';
    });

    // Soumettre avec Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchForm.submit();
        }
    });
</script>

<?php include "pied.php"; ?>