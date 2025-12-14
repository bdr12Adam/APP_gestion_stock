<?php
session_start();
include "../model/connexion.php";

if (
    empty($_POST["nom_article"]) ||
    empty($_POST["categorie"]) ||
    empty($_POST["quantite"]) ||
    empty($_POST["prix_unitaire"]) ||
    empty($_POST["date_fabrication"]) ||
    empty($_POST["date_expiration"])
) {
    $_SESSION['message']['text'] = "Tous les champs sont obligatoires.";
    $_SESSION['message']['type'] = "danger";
    header("Location: ../vue/article.php");
    exit;
}

// Récupération des données
$nom = $_POST["nom_article"];
$categorie = $_POST["categorie"];
$quantite = $_POST["quantite"];
$prix = $_POST["prix_unitaire"];
$date_fab = str_replace("T", " ", $_POST["date_fabrication"]) . ":00";
$date_exp = str_replace("T", " ", $_POST["date_expiration"]) . ":00";

$stmt = $conn->prepare("
    INSERT INTO article 
    (nom_article, categorie, quantite, prix_unitaire, date_fabrication, date_expiration) 
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("ssidds", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp);

if ($stmt->execute()) {
    $_SESSION['message']['text'] = "Article ajouté avec succès !";
    $_SESSION['message']['type'] = "success";
} else {
    $_SESSION['message']['text'] = "Erreur SQL : " . $conn->error;
    $_SESSION['message']['type'] = "danger";
}

header("Location: ../vue/article.php");
exit;
?>