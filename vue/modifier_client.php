<?php
include "entete.php";
include "../model/connexion.php";
include "../model/functions.php";

$id = $_GET['id'] ?? null;
$client = getClientById($id);

if (!$client) {
    header("Location: client.php");
    exit;
}

$message = "";
$typeMessage = "";

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

        modifierClient(
            $id,
            $_POST["nom"],
            $_POST["prenom"],
            $_POST["telephone"],
            $_POST["adresse"]
        );

        header("Location: client.php");
        exit;
    }
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

                <h4>✏️ Modifier le client</h4>
                <div class="subtitle">Modifiez les informations du client ci-dessous</div>

                <div class="text-center">
                    <span class="article-id-badge">ID: <?= $client['id'] ?></span>
                </div>

                <?php if ($message): ?>
                    <div class="alert-edit alert-<?= $typeMessage ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group-edit">
                        <label>Nom</label>
                        <input type="text"
                               name="nom"
                               value="<?= htmlspecialchars($client['nom']) ?>"
                               class="input-style-edit">
                    </div>

                    <div class="form-group-edit">
                        <label>Prénom</label>
                        <input type="text"
                               name="prenom"
                               value="<?= htmlspecialchars($client['prenom']) ?>"
                               class="input-style-edit">
                    </div>

                    <div class="form-group-edit">
                        <label>Téléphone</label>
                        <input type="text"
                               name="telephone"
                               value="<?= htmlspecialchars($client['telephone']) ?>"
                               class="input-style-edit">
                    </div>

                    <div class="form-group-edit">
                        <label>Adresse</label>
                        <textarea name="adresse"
                                  class="input-style-edit"><?= htmlspecialchars($client['adresse']) ?></textarea>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-update">
                            💾 Mettre à jour
                        </button>
                        <a href="../vue/client.php" class="btn-cancel">
                            ❌ Annuler
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>
