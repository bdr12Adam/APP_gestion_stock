
<?php
include "../model/connexion.php";
include "../model/functions.php";

$id = $_GET['id'] ?? null;

if ($id) {
    supprimerClient($id);
}

header("Location: ../vue/client.php");
exit;
