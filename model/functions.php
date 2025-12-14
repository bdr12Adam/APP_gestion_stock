<?php
include_once 'connexion.php'; // connexion à la base

/**
 * Récupérer tous les articles triés par ID croissant
 * @return array Liste des articles
 */
function getArticles() {
    global $conn;

    $sql = "SELECT * FROM article ORDER BY id ASC";
    $result = $conn->query($sql);

    $articles = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }

    return $articles;
}

/**
 * Récupérer un article par son ID
 * @param int $id - ID de l'article
 * @return array|null Article ou null si non trouvé
 */
function getArticleById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM article WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $article = $result->fetch_assoc();

    $stmt->close();

    return $article;
}

/**
 * Ajouter un nouvel article
 * @param string $nom - Nom de l'article
 * @param string $categorie - Catégorie
 * @param int $quantite - Quantité
 * @param float $prix - Prix unitaire
 * @param string $date_fab - Date de fabrication
 * @param string $date_exp - Date d'expiration
 * @return bool Succès ou échec
 */
function ajouterArticle($nom, $categorie, $quantite, $prix, $date_fab, $date_exp) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO article 
        (nom_article, categorie, quantite, prix_unitaire, date_fabrication, date_expiration)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssidss", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp);
    $result = $stmt->execute();

    $stmt->close();

    return $result;
}

/**
 * Modifier un article existant
 * @param int $id - ID de l'article
 * @param string $nom - Nom de l'article
 * @param string $categorie - Catégorie
 * @param int $quantite - Quantité
 * @param float $prix - Prix unitaire
 * @param string $date_fab - Date de fabrication
 * @param string $date_exp - Date d'expiration
 * @return bool Succès ou échec
 */
function modifierArticle($id, $nom, $categorie, $quantite, $prix, $date_fab, $date_exp) {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE article 
        SET nom_article = ?, 
            categorie = ?, 
            quantite = ?, 
            prix_unitaire = ?, 
            date_fabrication = ?, 
            date_expiration = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssidssi", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp, $id);
    $result = $stmt->execute();

    $stmt->close();

    return $result;
}

/**
 * Supprimer un article
 * @param int $id - ID de l'article à supprimer
 * @return bool Succès ou échec
 */
function supprimerArticle($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM article WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    $stmt->close();

    // Réorganiser les IDs après suppression
    reorganiserIDs();

    return $result;
}

/**
 * Réorganiser les IDs pour qu'ils soient séquentiels (1, 2, 3, ...)
 * À utiliser après une suppression
 */
function reorganiserIDs() {
    global $conn;

    // Désactiver temporairement les contraintes de clé étrangère si nécessaire
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Réorganiser les IDs en ordre croissant
    $conn->query("SET @count = 0");
    $conn->query("UPDATE article SET id = @count:= @count + 1 ORDER BY id ASC");

    // Réinitialiser l'auto-increment au prochain ID disponible
    $result = $conn->query("SELECT MAX(id) as max_id FROM article");
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] ? $row['max_id'] + 1 : 1;
    
    $conn->query("ALTER TABLE article AUTO_INCREMENT = $next_id");

    // Réactiver les contraintes
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}

/**
 * Compter le nombre total d'articles
 * @return int Nombre d'articles
 */
function compterArticles() {
    global $conn;

    $result = $conn->query("SELECT COUNT(*) as total FROM article");
    $row = $result->fetch_assoc();

    return $row['total'];
}

/**
 * Rechercher des articles par nom ou catégorie
 * @param string $recherche - Terme de recherche
 * @return array Liste des articles trouvés
 */
function rechercherArticles($recherche) {
    global $conn;

    $recherche = "%$recherche%";
    $stmt = $conn->prepare("
        SELECT * FROM article 
        WHERE nom_article LIKE ? OR categorie LIKE ?
        ORDER BY id ASC
    ");

    $stmt->bind_param("ss", $recherche, $recherche);
    $stmt->execute();

    $result = $stmt->get_result();
    $articles = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }

    $stmt->close();

    return $articles;
}

/**
 * Récupérer les articles par catégorie
 * @param string $categorie - Catégorie à filtrer
 * @return array Liste des articles
 */
function getArticlesByCategorie($categorie) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM article WHERE categorie = ? ORDER BY id ASC");
    $stmt->bind_param("s", $categorie);
    $stmt->execute();

    $result = $stmt->get_result();
    $articles = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }

    $stmt->close();

    return $articles;
}

/**
 * Vérifier si un article existe
 * @param int $id - ID de l'article
 * @return bool True si existe, false sinon
 */
function articleExiste($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM article WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;

    $stmt->close();

    return $existe;
}

/**
 * Récupérer les statistiques des articles
 * @return array Statistiques (total, par catégorie, stock faible)
 */
function getStatistiquesArticles() {
    global $conn;

    $stats = [];

    // Total articles
    $stats['total'] = compterArticles();

    // Par catégorie
    $result = $conn->query("
        SELECT categorie, COUNT(*) as nombre, SUM(quantite) as total_quantite 
        FROM article 
        GROUP BY categorie
    ");
    
    $stats['par_categorie'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['par_categorie'][] = $row;
    }

    // Articles avec stock faible (< 10)
    $result = $conn->query("
        SELECT COUNT(*) as nombre 
        FROM article 
        WHERE quantite < 10
    ");
    $row = $result->fetch_assoc();
    $stats['stock_faible'] = $row['nombre'];

    // Valeur totale du stock
    $result = $conn->query("
        SELECT SUM(quantite * prix_unitaire) as valeur_totale 
        FROM article
    ");
    $row = $result->fetch_assoc();
    $stats['valeur_totale'] = $row['valeur_totale'] ?? 0;

    return $stats;
}

/**
 * Récupérer les articles avec stock faible
 * @param int $seuil - Seuil de quantité (par défaut 10)
 * @return array Liste des articles avec stock faible
 */
function getArticlesStockFaible($seuil = 10) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT * FROM article 
        WHERE quantite < ? 
        ORDER BY quantite ASC
    ");
    
    $stmt->bind_param("i", $seuil);
    $stmt->execute();

    $result = $stmt->get_result();
    $articles = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }

    $stmt->close();

    return $articles;
}

/**
 * Récupérer les articles expirés ou bientôt expirés
 * @param int $jours - Nombre de jours avant expiration (par défaut 30)
 * @return array Liste des articles
 */
function getArticlesExpiration($jours = 30) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT * FROM article 
        WHERE date_expiration <= DATE_ADD(NOW(), INTERVAL ? DAY)
        ORDER BY date_expiration ASC
    ");
    
    $stmt->bind_param("i", $jours);
    $stmt->execute();

    $result = $stmt->get_result();
    $articles = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
    }

    $stmt->close();

    return $articles;
}
function getClients() {
    global $conn;
    $req = $conn->query("SELECT * FROM client ORDER BY id ASC");
    return $req->fetch_all(MYSQLI_ASSOC);
}

function getClientById($id) {
    global $conn;

    if (!$id) {
        return null;
    }

    $req = $conn->prepare("SELECT * FROM client WHERE id = ?");
    $req->bind_param("i", $id);
    $req->execute();

    return $req->get_result()->fetch_assoc();
}

function modifierClient($id, $nom, $prenom, $telephone, $adresse) {
    global $conn;
    $req = $conn->prepare("
        UPDATE client 
        SET nom=?, prenom=?, telephone=?, adresse=?
        WHERE id=?
    ");
    $req->bind_param("ssssi", $nom, $prenom, $telephone, $adresse, $id);
    return $req->execute();
}
function supprimerClient($id) {
    global $conn;
    $req = $conn->prepare("DELETE FROM client WHERE id = ?");
    $req->bind_param("i", $id);
    return $req->execute();
}
?>