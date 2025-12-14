<?php
include "../model/connexion.php";
include "../model/functions.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    supprimerArticle($id);
}

header("Location: article.php");
exit;
