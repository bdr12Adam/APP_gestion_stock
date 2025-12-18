<?php
include "../model/connexion.php";
session_start();

/* ===============================
   SUPPRESSION DU FOURNISSEUR
================================ */

$id_fournisseur = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_fournisseur <= 0) {
    $_SESSION['message'] = "❌ ID fournisseur invalide.";
    $_SESSION['typeMessage'] = "danger";
    header("Location: ../vue/fournisseur.php");
    exit;
}

// Vérifier si le fournisseur existe
$stmt = $conn->prepare("SELECT id FROM fournisseur WHERE id = ?");
$stmt->bind_param("i", $id_fournisseur);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "❌ Fournisseur introuvable.";
    $_SESSION['typeMessage'] = "danger";
    $stmt->close();
    header("Location: fournisseur.php");
    exit;
}
$stmt->close();

// Suppression du fournisseur
$stmt_delete = $conn->prepare("DELETE FROM fournisseur WHERE id = ?");
$stmt_delete->bind_param("i", $id_fournisseur);

if ($stmt_delete->execute()) {
    $_SESSION['message'] = "✅ Fournisseur supprimé avec succès.";
    $_SESSION['typeMessage'] = "success";
} else {
    $_SESSION['message'] = "❌ Erreur lors de la suppression : " . $conn->error;
    $_SESSION['typeMessage'] = "danger";
}

$stmt_delete->close();
$conn->close();

header("Location: fournisseur.php");
exit;
?>