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

    // Supprimer les ventes du client
    $stmt1 = $conn->prepare("DELETE FROM vente WHERE id_client = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    // Supprimer le client
    $stmt2 = $conn->prepare("DELETE FROM client WHERE id = ?");
    $stmt2->bind_param("i", $id);
    return $stmt2->execute();
}
 // ============================================
// FONCTIONS POUR LA GESTION DES VENTES
// VERSION CORRIGÉE
// ============================================

/**
 * Récupérer toutes les ventes avec les détails des articles et clients
 * @return array Liste des ventes
 */
function getVentes() {
    global $conn;

    // Requête simple qui récupère directement nom et prenom
    $sql = "
        SELECT 
            v.id,
            v.id_article,
            v.id_client,
            v.quantite,
            v.prix,
            v.date_vente,
            a.nom_article,
            a.categorie,
            a.prix_unitaire,
            c.nom,
            c.prenom,
            c.telephone,
            c.adresse
        FROM vente v
        LEFT JOIN article a ON v.id_article = a.id
        LEFT JOIN client c ON v.id_client = c.id
        ORDER BY  v.id ASC
    ";
    
    $result = $conn->query($sql);
    $ventes = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ventes[] = $row;
        }
    }

    return $ventes;
}

/**
 * Récupérer une vente par son ID
 * @param int $id - ID de la vente
 * @return array|null Vente ou null si non trouvée
 */
function getVenteById($id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            v.*,
            a.nom_article,
            a.categorie,
            c.nom,
            c.prenom
        FROM vente v
        LEFT JOIN article a ON v.id_article = a.id
        LEFT JOIN client c ON v.id_client = c.id
        WHERE v.id = ?
    ");
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $vente = $result->fetch_assoc();

    $stmt->close();

    return $vente;
}

/**
 * Ajouter une nouvelle vente
 * @param int $id_article - ID de l'article
 * @param int $id_client - ID du client
 * @param int $quantite - Quantité vendue
 * @return array Résultat avec succès et message
 */
function ajouterVente($id_article, $id_client, $quantite) {
    global $conn;

    // Vérifier si l'article existe
    $article = getArticleById($id_article);
    
    if (!$article) {
        return [
            'success' => false,
            'message' => '❌ Erreur : L\'article sélectionné n\'existe pas.'
        ];
    }

    // Vérifier le stock disponible
    if ($article['quantite'] < $quantite) {
        return [
            'success' => false,
            'message' => '❌ Stock insuffisant ! Quantité disponible : ' . $article['quantite']
        ];
    }

    // Calculer le prix total
    $prix = $article['prix_unitaire'] * $quantite;
    $date_vente = date('Y-m-d H:i:s');

    // Insérer la vente
    $stmt = $conn->prepare("
        INSERT INTO vente 
        (id_article, id_client, quantite, prix, date_vente)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Erreur de préparation : ' . $conn->error
        ];
    }

    $stmt->bind_param("iiids", $id_article, $id_client, $quantite, $prix, $date_vente);
    
    if ($stmt->execute()) {
        // Mettre à jour le stock de l'article
        $nouvelle_quantite = $article['quantite'] - $quantite;
        $stmt_update = $conn->prepare("UPDATE article SET quantite = ? WHERE id = ?");
        
        if ($stmt_update) {
            $stmt_update->bind_param("ii", $nouvelle_quantite, $id_article);
            $stmt_update->execute();
            $stmt_update->close();
        }

        $stmt->close();

        return [
            'success' => true,
            'message' => '✅ Vente enregistrée avec succès ! Prix total : ' . number_format($prix, 2) . ' DH',
            'prix_total' => $prix
        ];
    } else {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement : ' . $conn->error
        ];
    }
}

/**
 * Supprimer une vente et restaurer le stock
 * @param int $id - ID de la vente à supprimer
 * @return array Résultat avec succès et message
 */
function supprimerVente($id) {
    global $conn;

    // Récupérer la vente avant de la supprimer
    $vente = getVenteById($id);
    
    if (!$vente) {
        return [
            'success' => false,
            'message' => '❌ Vente introuvable.'
        ];
    }

    // Restaurer le stock de l'article
    $stmt_restore = $conn->prepare("
        UPDATE article 
        SET quantite = quantite + ? 
        WHERE id = ?
    ");
    
    if ($stmt_restore) {
        $stmt_restore->bind_param("ii", $vente['quantite'], $vente['id_article']);
        $stmt_restore->execute();
        $stmt_restore->close();
    }

    // Supprimer la vente
    $stmt = $conn->prepare("DELETE FROM vente WHERE id = ?");
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Erreur de préparation : ' . $conn->error
        ];
    }
    
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        return [
            'success' => true,
            'message' => '✅ Vente supprimée et stock restauré avec succès.'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Erreur lors de la suppression.'
        ];
    }
}

/**
 * Récupérer les articles disponibles (stock > 0)
 * @return array Liste des articles disponibles
 */
function getArticlesDisponibles() {
    global $conn;

    $sql = "SELECT * FROM article WHERE quantite > 0 ORDER BY nom_article ASC";
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
 * Récupérer les statistiques des ventes
 * @return array Statistiques (total ventes, revenus, articles vendus)
 */
function getStatistiquesVentes() {
    global $conn;

    $stats = [];

    // Nombre total de ventes
    $result = $conn->query("SELECT COUNT(*) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['nombre_ventes'] = $row['total'] ?? 0;
    }

    // Revenu total
    $result = $conn->query("SELECT SUM(prix) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['revenu_total'] = $row['total'] ?? 0;
    }

    // Quantité totale vendue
    $result = $conn->query("SELECT SUM(quantite) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['quantite_vendue'] = $row['total'] ?? 0;
    }

    // Article le plus vendu
    $result = $conn->query("
        SELECT a.nom_article, SUM(v.quantite) as total_vendu
        FROM vente v
        JOIN article a ON v.id_article = a.id
        GROUP BY v.id_article
        ORDER BY total_vendu DESC
        LIMIT 1
    ");
    if ($result) {
        $stats['article_plus_vendu'] = $result->fetch_assoc();
    }

    // Ventes du jour
    $result = $conn->query("
        SELECT COUNT(*) as nombre, SUM(prix) as total
        FROM vente
        WHERE DATE(date_vente) = CURDATE()
    ");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['ventes_aujourdhui'] = [
            'nombre' => $row['nombre'] ?? 0,
            'total' => $row['total'] ?? 0
        ];
    }

    // Ventes du mois
    $result = $conn->query("
        SELECT COUNT(*) as nombre, SUM(prix) as total
        FROM vente
        WHERE MONTH(date_vente) = MONTH(CURDATE()) 
        AND YEAR(date_vente) = YEAR(CURDATE())
    ");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['ventes_mois'] = [
            'nombre' => $row['nombre'] ?? 0,
            'total' => $row['total'] ?? 0
        ];
    }

    return $stats;
}

/**
 * Récupérer les ventes d'un client spécifique
 * @param int $id_client - ID du client
 * @return array Liste des ventes du client
 */
function getVentesByClient($id_client) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            v.*,
            a.nom_article,
            a.categorie
        FROM vente v
        LEFT JOIN article a ON v.id_article = a.id
        WHERE v.id_client = ?
        ORDER BY v.date_vente DESC
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $id_client);
    $stmt->execute();

    $result = $stmt->get_result();
    $ventes = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ventes[] = $row;
        }
    }

    $stmt->close();

    return $ventes;
}

/**
 * Récupérer les ventes d'un article spécifique
 * @param int $id_article - ID de l'article
 * @return array Liste des ventes de l'article
 */
function getVentesByArticle($id_article) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            v.*,
            c.nom,
            c.prenom
        FROM vente v
        LEFT JOIN client c ON v.id_client = c.id
        WHERE v.id_article = ?
        ORDER BY v.date_vente DESC
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $id_article);
    $stmt->execute();

    $result = $stmt->get_result();
    $ventes = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ventes[] = $row;
        }
    }

    $stmt->close();

    return $ventes;
}

/**
 * Récupérer les ventes par période
 * @param string $date_debut - Date de début (Y-m-d)
 * @param string $date_fin - Date de fin (Y-m-d)
 * @return array Liste des ventes
 */
function getVentesByPeriode($date_debut, $date_fin) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            v.*,
            a.nom_article,
            c.nom,
            c.prenom
        FROM vente v
        LEFT JOIN article a ON v.id_article = a.id
        LEFT JOIN client c ON v.id_client = c.id
        WHERE DATE(v.date_vente) BETWEEN ? AND ?
        ORDER BY v.date_vente DESC
    ");
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("ss", $date_debut, $date_fin);
    $stmt->execute();

    $result = $stmt->get_result();
    $ventes = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ventes[] = $row;
        }
    }

    $stmt->close();

    return $ventes;
}

/**
 * Vérifier si une vente existe
 * @param int $id - ID de la vente
 * @return bool True si existe, false sinon
 */
function venteExiste($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM vente WHERE id = ?");
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $existe = $result->num_rows > 0;

    $stmt->close();

    return $existe;
}
?>