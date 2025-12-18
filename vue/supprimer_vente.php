<?php
include "../model/connexion.php";
include "../model/functions.php";

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    supprimerVente($id);
}

header("Location: ../vue/vente.php");
exit;