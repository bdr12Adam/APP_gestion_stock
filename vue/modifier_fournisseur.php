<?php
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

$message = "";
$typeMessage = "";

/* ===============================
   RÉCUPÉRATION DU FOURNISSEUR
================================ */

$id_fournisseur = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_fournisseur <= 0) {
    header("Location: fournisseur.php");
    exit;
}

// Récupération des données du fournisseur
$stmt = $conn->prepare("SELECT * FROM fournisseur WHERE id = ?");
$stmt->bind_param("i", $id_fournisseur);
$stmt->execute();
$result = $stmt->get_result();
$fournisseur = $result->fetch_assoc();
$stmt->close();

if (!$fournisseur) {
    header("Location: fournisseur.php");
    exit;
}

/* ===============================
   TRAITEMENT MODIFICATION
================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['nom']) ||
        empty($_POST['prenom']) ||
        empty($_POST['telephone']) ||
        empty($_POST['adresse'])
    ) {
        $message = "❌ Tous les champs sont obligatoires.";
        $typeMessage = "danger";
    } else {

        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $telephone = trim($_POST['telephone']);
        $adresse = trim($_POST['adresse']);

        $stmt = $conn->prepare("
            UPDATE fournisseur 
            SET nom = ?, prenom = ?, telephone = ?, adresse = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param("ssssi", $nom, $prenom, $telephone, $adresse, $id_fournisseur);

        if ($stmt->execute()) {
            $message = "✅ Fournisseur modifié avec succès.";
            $typeMessage = "success";
            
            // Recharger les données mises à jour
            $stmt2 = $conn->prepare("SELECT * FROM fournisseur WHERE id = ?");
            $stmt2->bind_param("i", $id_fournisseur);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $fournisseur = $result2->fetch_assoc();
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
    max-width: 700px;
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
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
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
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
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
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    transform: translateY(-2px);
}

.input-style::placeholder {
    color: #a0aec0;
}

textarea.input-style {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
}

.info-box {
    background: #d1fae5;
    border-left: 4px solid #10b981;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    color: #065f46;
    font-weight: 600;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-box .icon {
    font-size: 18px;
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
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.btn-primary:active {
    transform: translateY(-1px);
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

.btn-secondary:active {
    transform: translateY(-1px);
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

/* Responsive */
@media (max-width: 768px) {
    .form-container {
        margin: 20px 15px;
        padding: 25px 20px;
    }
    
    .form-container h4 {
        font-size: 20px;
    }
    
    .btn-actions {
        flex-direction: column;
        gap: 12px;
    }
    
    .btn-primary, .btn-secondary {
        padding: 12px 0;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 20px 15px;
    }
    
    .input-style {
        padding: 10px 14px;
        font-size: 13px;
    }
}
</style>

<section class="home-section">
<div class="home-content">

<div class="form-container">
<h4>✏️ Modifier un Fournisseur</h4>

<?php if ($message): ?>
<div class="alert alert-<?= $typeMessage ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<form method="post">

    <label>Nom *</label>
    <input type="text" 
           name="nom" 
           class="input-style" 
           value="<?= htmlspecialchars($fournisseur['nom']) ?>"
           placeholder="Entrez le nom"
           required>

    <label>Prénom *</label>
    <input type="text" 
           name="prenom" 
           class="input-style" 
           value="<?= htmlspecialchars($fournisseur['prenom']) ?>"
           placeholder="Entrez le prénom"
           required>

    <label>Téléphone *</label>
    <input type="tel" 
           name="telephone" 
           class="input-style" 
           value="<?= htmlspecialchars($fournisseur['telephone']) ?>"
           placeholder="Ex: 0612345678"
           required>

    <label>Adresse *</label>
    <textarea name="adresse" 
              class="input-style" 
              placeholder="Entrez l'adresse complète"
              required><?= htmlspecialchars($fournisseur['adresse']) ?></textarea>

    <div class="info-box">
        <span class="icon">ℹ️</span>
        <span>Tous les champs marqués d'un astérisque (*) sont obligatoires</span>
    </div>

    <div class="btn-actions">
        <button type="submit" class="btn-primary">
            💾 Enregistrer les modifications
        </button>
        <a href="fournisseur.php" class="btn-secondary">
            ❌ Annuler
        </a>
    </div>

</form>

</div>

</div>
</section>

<?php include "pied.php"; ?>