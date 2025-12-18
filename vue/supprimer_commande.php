<?php
include "../model/connexion.php";
session_start();

/* ===============================
   SUPPRESSION DE LA COMMANDE
================================ */

$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_commande <= 0) {
    $_SESSION['message'] = "❌ ID commande invalide.";
    $_SESSION['typeMessage'] = "danger";
    header("Location: ../vue/commande.php");
    exit;
}

// Vérifier si la commande existe et récupérer les infos
$stmt = $conn->prepare("
    SELECT c.*, a.nom_article 
    FROM commande c
    LEFT JOIN article a ON c.id_article = a.id
    WHERE c.id = ?
");
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "❌ Commande introuvable.";
    $_SESSION['typeMessage'] = "danger";
    $stmt->close();
    header("Location: ../vue/commande.php");
    exit;
}

$commande = $result->fetch_assoc();
$nom_article = $commande['nom_article'];
$stmt->close();

// Suppression de la commande
$stmt_delete = $conn->prepare("DELETE FROM commande WHERE id = ?");
$stmt_delete->bind_param("i", $id_commande);

if ($stmt_delete->execute()) {
    $_SESSION['message'] = "✅ Commande de « $nom_article » supprimée avec succès.";
    $_SESSION['typeMessage'] = "success";
} else {
    $_SESSION['message'] = "❌ Erreur lors de la suppression : " . $conn->error;
    $_SESSION['typeMessage'] = "danger";
}

$stmt_delete->close();
$conn->close();

header("Location: ../vue/commande.php");
exit;
?>