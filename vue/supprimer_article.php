<?php
include "../model/connexion.php";
session_start();

/* ===============================
   SUPPRESSION SÉCURISÉE D'ARTICLE
   Vérifie les contraintes avant suppression
================================ */

$id_article = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_article <= 0) {
    $_SESSION['message'] = "❌ ID article invalide.";
    $_SESSION['typeMessage'] = "danger";
    header("Location: article.php");
    exit;
}

// Vérifier si l'article existe
$stmt = $conn->prepare("SELECT nom_article FROM article WHERE id = ?");
$stmt->bind_param("i", $id_article);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "❌ Article introuvable.";
    $_SESSION['typeMessage'] = "danger";
    $stmt->close();
    header("Location: article.php");
    exit;
}

$article = $result->fetch_assoc();
$nom_article = $article['nom_article'];
$stmt->close();

// Vérifier si l'article est utilisé dans des ventes
$stmt_check = $conn->prepare("SELECT COUNT(*) as nb_ventes FROM vente WHERE id_article = ?");
$stmt_check->bind_param("i", $id_article);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$check = $result_check->fetch_assoc();
$nb_ventes = $check['nb_ventes'];
$stmt_check->close();

if ($nb_ventes > 0) {
    // L'article est utilisé dans des ventes, on ne peut pas le supprimer
    $_SESSION['message'] = "❌ Impossible de supprimer l'article « $nom_article ». Il est utilisé dans $nb_ventes vente(s).";
    $_SESSION['typeMessage'] = "danger";
    header("Location: article.php");
    exit;
}

// L'article n'est pas utilisé, on peut le supprimer
$stmt_delete = $conn->prepare("DELETE FROM article WHERE id = ?");
$stmt_delete->bind_param("i", $id_article);

if ($stmt_delete->execute()) {
    $_SESSION['message'] = "✅ Article « $nom_article » supprimé avec succès.";
    $_SESSION['typeMessage'] = "success";
} else {
    $_SESSION['message'] = "❌ Erreur lors de la suppression : " . $conn->error;
    $_SESSION['typeMessage'] = "danger";
}

$stmt_delete->close();
$conn->close();

header("Location: article.php");
exit;
?>