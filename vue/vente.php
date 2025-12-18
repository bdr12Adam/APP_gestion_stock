<?php  
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

$message = "";
$typeMessage = "";

// ----- TRAITEMENT FORMULAIRE DE VENTE -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST["id_article"]) ||
        empty($_POST["id_client"]) ||
        empty($_POST["quantite"])
    ) {
        $message = "Tous les champs sont obligatoires.";
        $typeMessage = "danger";
    } else {
        // Utiliser la fonction ajouterVente
        $resultat = ajouterVente(
            intval($_POST["id_article"]),
            intval($_POST["id_client"]),
            intval($_POST["quantite"])
        );

        $message = $resultat['message'];
        $typeMessage = $resultat['success'] ? 'success' : 'danger';
    }
}

// ----- RÉCUPÉRATION DES DONNÉES -----
$articles_dispo = getArticlesDisponibles();
$clients = getClients();
$ventes = getVentes();

?>

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

.form-box .card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

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

.form-box label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 6px;
}

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

select.input-style {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 45px;
}

.info-prix {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 13px;
    color: #047857;
    font-weight: 600;
    margin-top: 10px;
}

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
   TABLEAU MODERNE
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

.table-box h4 {
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid #e2e8f0;
}

.table-responsive {
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: 800px;
}

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
}

.table tbody td {
    padding: 14px 12px;
    font-size: 14px;
    color: #4a5568;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
    text-align: center;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
}

.table-striped tbody tr:nth-child(even) {
    background-color: #ffffff;
}

.table tbody tr:hover {
    background-color: #e9ecef !important;
    transform: scale(1.001);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.badge-prix {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
    display: inline-block;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    margin: 2px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    color: #fff;
    transition: all 0.25s ease;
}

/* Bouton Modifier */
.btn-edit {
    background-color: #0d6efd; /* bleu */
}

.btn-edit:hover {
    background-color: #0b5ed7;
    transform: scale(1.05);
}
td {
    text-align: center;
    vertical-align: middle;
}
/* Bouton Supprimer */
.btn-delete {
    background-color: #dc3545; /* rouge */
}

.btn-delete:hover {
    background-color: #bb2d3b;
    transform: scale(1.05);
}

/* Effet clic */
.btn-action:active {
    transform: scale(0.95);
}
/* ==========================
   RESPONSIVE
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
}

@media (max-width: 768px) {
    .flex-container {
        padding: 0 10px;
        gap: 20px;
    }
    
    .form-box .card,
    .table-box .card {
        padding: 20px 15px;
        border-radius: 16px;
    }
}
.receipt-link {
  list-style: none;
  margin: 25px 0;
  padding: 0;
}

.receipt-link a {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  background: #ffffff;
  color: #1e90ff;
  padding: 14px 24px;
  border-radius: 10px;
  border: 3px solid #1e90ff;
  transition: all 0.4s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  font-weight: 700;
  position: relative;
}

.receipt-link a::after {
  content: '→';
  position: absolute;
  right: 20px;
  opacity: 0;
  transition: all 0.3s ease;
  font-size: 20px;
  font-weight: bold;
}

.receipt-link a h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  transition: transform 0.3s ease;
}

.receipt-link a:hover {
  background: #1e90ff;
  color: #ffffff;
  border-color: #1e90ff;
  transform: translateX(8px);
  box-shadow: 0 8px 25px rgba(30, 144, 255, 0.3);
  padding-right: 50px;
}

.receipt-link a:hover::after {
  opacity: 1;
  right: 15px;
}

.receipt-link a:hover h5 {
  transform: translateX(-5px);
}

.receipt-link a:active {
  transform: translateX(6px) scale(0.97);
  box-shadow: 0 4px 15px rgba(30, 144, 255, 0.2);
}

.receipt-link a i {
  font-size: 20px;
  transition: transform 0.3s ease;
}

.receipt-link a:hover i {
  transform: rotate(360deg);
}

</style>

<!-- **************  CONTENU ************** -->
<section class="home-section">
    <div class="home-content">

        <div class="flex-container">

            <!-- FORMULAIRE DE VENTE -->
            <div class="form-box">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-4 text-center fw-bold">🛒 Nouvelle Vente</h4>

                        <form action="" method="post" id="venteForm">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Article disponible <span style="color:red;">*</span></label>
                                <select class="form-select input-style" name="id_article" id="id_article" required onchange="updatePrix()">
                                    <option value="">-- Sélectionner un article --</option>
                                    <?php if (count($articles_dispo) > 0): ?>
                                        <?php foreach ($articles_dispo as $art): ?>
                                            <option value="<?= $art['id'] ?>" 
                                                    data-prix="<?= $art['prix_unitaire'] ?>"
                                                    data-stock="<?= $art['quantite'] ?>">
                                                <?= htmlspecialchars($art['nom_article']) ?> - Stock: <?= $art['quantite'] ?> - Prix: <?= number_format($art['prix_unitaire'], 2) ?> DH
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Aucun article disponible</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Client <span style="color:red;">*</span></label>
                                <select class="form-select input-style" name="id_client" required>
                                    <option value="">-- Sélectionner un client --</option>
                                    <?php if (count($clients) > 0): ?>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client['id'] ?>">
                                                <?= htmlspecialchars($client['nom']) ?> 
                                                <?= htmlspecialchars($client['prenom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Aucun client enregistré</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantité à vendre <span style="color:red;">*</span></label>
                                <input type="number" class="form-control input-style" 
                                       name="quantite" id="quantite" 
                                       min="1" required onchange="updatePrix()" oninput="updatePrix()">
                                <small class="text-muted" id="stock_info"></small>
                            </div>

                            <div class="info-prix" id="info_prix" style="display: none;">
                                💰 Prix Total : <span id="prix_total_display">0.00</span> DH
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Enregistrer la vente</button>

                            <?php if ($message): ?>
                                <div class="alert alert-<?= $typeMessage ?> mt-3 text-center fw-bold">
                                    <?= $message ?>
                                </div>
                            <?php endif; ?>

                        </form>

                    </div>
                </div>
            </div>

            <!-- TABLEAU DES VENTES -->
     <div class="table-box">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="fw-bold mb-3">📋 Historique des Ventes</h4>
                  <li class="receipt-link">
  <a href="../vue/recue.php">
    <h5> 📁Accédez à votre reçu en cliquant ici</h5>
  </a>
</li>

            <link
      href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css"
      rel="stylesheet"
    />
            <div class="recue">
                <!-- Ici tu peux ajouter des messages ou filtres -->
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 180px;">Article</th>
                            <th style="width: 150px;">Client</th>
                            <th style="width: 100px;">Quantité</th>
                            <th style="width: 120px;">Prix Total</th>
                            <th style="width: 150px;">Date Vente</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($ventes) > 0): ?>
                            <?php $i = 1; // Compteur séquentiel ?>
                            <?php foreach ($ventes as $vente): ?>
                                <tr>
                                    <!-- ID séquentiel à l'affichage -->
                                    <td><?= $i++ ?></td>
                                    
                                    <td><?= htmlspecialchars($vente['nom_article'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($vente['nom'] ?? 'N/A') ?> <?= htmlspecialchars($vente['prenom'] ?? '') ?></td>
                                    <td><?= $vente['quantite'] ?></td>
                                    <td><span class="badge-prix"><?= number_format($vente['prix'], 2) ?> DH</span></td>
                                    <td><?= date('d/m/Y H:i', strtotime($vente['date_vente'])) ?></td>
                                    <td>
                                        <a href="../vue/modifier_vente.php?id=<?= $vente['id'] ?>" 
                                           class="btn-action btn-edit" title="Modifier">
                                           ✏️ Modifier
                                        </a>

                                        <a href="../vue/supprimer_vente.php?id=<?= $vente['id'] ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Supprimer cette vente ?');">
                                           🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune vente enregistrée</td>
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
function updatePrix() {
    const articleSelect = document.getElementById('id_article');
    const quantiteInput = document.getElementById('quantite');
    const infoPrix = document.getElementById('info_prix');
    const prixDisplay = document.getElementById('prix_total_display');
    const stockInfo = document.getElementById('stock_info');
    
    const selectedOption = articleSelect.options[articleSelect.selectedIndex];
    
    if (selectedOption.value && quantiteInput.value) {
        const prix = parseFloat(selectedOption.dataset.prix);
        const stock = parseInt(selectedOption.dataset.stock);
        const quantite = parseInt(quantiteInput.value);
        
        if (quantite > stock) {
            stockInfo.textContent = `⚠️ Stock insuffisant ! Maximum disponible : ${stock}`;
            stockInfo.style.color = '#dc3545';
            quantiteInput.value = stock;
        } else {
            stockInfo.textContent = `✅ Stock disponible : ${stock}`;
            stockInfo.style.color = '#10b981';
        }
        
        const total = prix * quantite;
        prixDisplay.textContent = total.toFixed(2);
        infoPrix.style.display = 'block';
    } else {
        infoPrix.style.display = 'none';
        stockInfo.textContent = '';
    }
}
</script>

<?php include "pied.php"; ?>