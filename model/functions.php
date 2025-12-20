<?php
include_once 'connexion.php';

// ============================================
// FONCTIONS POUR LA GESTION DES ARTICLES
// ============================================

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

function ajouterArticle($nom, $categorie, $quantite, $prix, $date_fab, $date_exp) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO article (nom_article, categorie, quantite, prix_unitaire, date_fabrication, date_expiration) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidss", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function modifierArticle($id, $nom, $categorie, $quantite, $prix, $date_fab, $date_exp) {
    global $conn;
    $stmt = $conn->prepare("UPDATE article SET nom_article = ?, categorie = ?, quantite = ?, prix_unitaire = ?, date_fabrication = ?, date_expiration = ? WHERE id = ?");
    $stmt->bind_param("ssidssi", $nom, $categorie, $quantite, $prix, $date_fab, $date_exp, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function supprimerArticle($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM article WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    reorganiserIDs();
    return $result;
}

function reorganiserIDs() {
    global $conn;
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("SET @count = 0");
    $conn->query("UPDATE article SET id = @count:= @count + 1 ORDER BY id ASC");
    $result = $conn->query("SELECT MAX(id) as max_id FROM article");
    $row = $result->fetch_assoc();
    $next_id = $row['max_id'] ? $row['max_id'] + 1 : 1;
    $conn->query("ALTER TABLE article AUTO_INCREMENT = $next_id");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
}

function compterArticles() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM article");
    $row = $result->fetch_assoc();
    return $row['total'];
}

function rechercherArticles($recherche) {
    global $conn;
    $recherche = "%$recherche%";
    $stmt = $conn->prepare("SELECT * FROM article WHERE nom_article LIKE ? OR categorie LIKE ? ORDER BY id ASC");
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

function getStatistiquesArticles() {
    global $conn;
    $stats = [];
    $stats['total'] = compterArticles();
    
    $result = $conn->query("SELECT categorie, COUNT(*) as nombre, SUM(quantite) as total_quantite FROM article GROUP BY categorie");
    $stats['par_categorie'] = [];
    while ($row = $result->fetch_assoc()) {
        $stats['par_categorie'][] = $row;
    }
    
    $result = $conn->query("SELECT COUNT(*) as nombre FROM article WHERE quantite < 10");
    $row = $result->fetch_assoc();
    $stats['stock_faible'] = $row['nombre'];
    
    $result = $conn->query("SELECT SUM(quantite * prix_unitaire) as valeur_totale FROM article");
    $row = $result->fetch_assoc();
    $stats['valeur_totale'] = $row['valeur_totale'] ?? 0;
    
    return $stats;
}

function getArticlesStockFaible($seuil = 10) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM article WHERE quantite < ? ORDER BY quantite ASC");
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

function getArticlesExpiration($jours = 30) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM article WHERE date_expiration <= DATE_ADD(NOW(), INTERVAL ? DAY) ORDER BY date_expiration ASC");
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

// ============================================
// FONCTIONS POUR LA GESTION DES CLIENTS
// ============================================

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

function ajouterClient($nom, $prenom, $telephone, $adresse) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO client (nom, prenom, telephone, adresse) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("ssss", $nom, $prenom, $telephone, $adresse);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function modifierClient($id, $nom, $prenom, $telephone, $adresse) {
    global $conn;
    $req = $conn->prepare("UPDATE client SET nom=?, prenom=?, telephone=?, adresse=? WHERE id=?");
    $req->bind_param("ssssi", $nom, $prenom, $telephone, $adresse, $id);
    return $req->execute();
}

function supprimerClient($id) {
    global $conn;
    $stmt1 = $conn->prepare("DELETE FROM vente WHERE id_client = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();
    $stmt2 = $conn->prepare("DELETE FROM client WHERE id = ?");
    $stmt2->bind_param("i", $id);
    return $stmt2->execute();
}

// ============================================
// FONCTIONS POUR LA GESTION DES VENTES
// ============================================

function getVentes() {
    global $conn;
    
    // Vérifier la connexion
    if (!$conn) {
        die("ERREUR: Connexion à la base de données non disponible dans getVentes()");
    }
    
    // Vérifier que la table vente existe
    $check_table = $conn->query("SHOW TABLES LIKE 'vente'");
    if (!$check_table || $check_table->num_rows == 0) {
        die("ERREUR: La table 'vente' n'existe pas dans la base de données. Veuillez créer la table vente.");
    }
    
    $sql = "SELECT v.id, v.id_article, v.id_client, v.quantite, v.prix_total as prix, v.date_vente, a.nom_article, a.categorie, a.prix_unitaire, c.nom, c.prenom, c.telephone, c.adresse FROM vente v LEFT JOIN article a ON v.id_article = a.id LEFT JOIN client c ON v.id_client = c.id ORDER BY v.id ASC";
    
    $result = $conn->query($sql);
    
    // Si erreur SQL, afficher les détails
    if (!$result) {
        die("ERREUR SQL dans getVentes(): " . $conn->error . "<br><br>Requête: " . $sql);
    }
    
    $ventes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ventes[] = $row;
        }
    }
    
    return $ventes;
}

function getVenteById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT v.*, a.nom_article, a.categorie, c.nom, c.prenom FROM vente v LEFT JOIN article a ON v.id_article = a.id LEFT JOIN client c ON v.id_client = c.id WHERE v.id = ?");
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

function ajouterVente($id_article, $id_client, $quantite) {
    global $conn;
    $article = getArticleById($id_article);
    
    if (!$article) {
        return ['success' => false, 'message' => '❌ Erreur : L\'article sélectionné n\'existe pas.'];
    }
    
    if ($article['quantite'] < $quantite) {
        return ['success' => false, 'message' => '❌ Stock insuffisant ! Quantité disponible : ' . $article['quantite']];
    }
    
    $prix = $article['prix_unitaire'] * $quantite;
    $date_vente = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO vente (id_article, id_client, quantite, prix_total, date_vente) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        return ['success' => false, 'message' => 'Erreur de préparation : ' . $conn->error];
    }
    
    $stmt->bind_param("iiids", $id_article, $id_client, $quantite, $prix, $date_vente);
    
    if ($stmt->execute()) {
        $nouvelle_quantite = $article['quantite'] - $quantite;
        $stmt_update = $conn->prepare("UPDATE article SET quantite = ? WHERE id = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("ii", $nouvelle_quantite, $id_article);
            $stmt_update->execute();
            $stmt_update->close();
        }
        $stmt->close();
        return ['success' => true, 'message' => '✅ Vente enregistrée avec succès ! Prix total : ' . number_format($prix, 2) . ' DH', 'prix_total' => $prix];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement : ' . $conn->error];
    }
}

function supprimerVente($id) {
    global $conn;
    $vente = getVenteById($id);
    
    if (!$vente) {
        return ['success' => false, 'message' => '❌ Vente introuvable.'];
    }
    
    $stmt_restore = $conn->prepare("UPDATE article SET quantite = quantite + ? WHERE id = ?");
    if ($stmt_restore) {
        $stmt_restore->bind_param("ii", $vente['quantite'], $vente['id_article']);
        $stmt_restore->execute();
        $stmt_restore->close();
    }
    
    $stmt = $conn->prepare("DELETE FROM vente WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Erreur de préparation : ' . $conn->error];
    }
    
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        return ['success' => true, 'message' => '✅ Vente supprimée et stock restauré avec succès.'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de la suppression.'];
    }
}

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

function getStatistiquesVentes() {
    global $conn;
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['nombre_ventes'] = $row['total'] ?? 0;
    }
    
    $result = $conn->query("SELECT SUM(prix) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['revenu_total'] = $row['total'] ?? 0;
    }
    
    $result = $conn->query("SELECT SUM(quantite) as total FROM vente");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['quantite_vendue'] = $row['total'] ?? 0;
    }
    
    $result = $conn->query("SELECT a.nom_article, SUM(v.quantite) as total_vendu FROM vente v JOIN article a ON v.id_article = a.id GROUP BY v.id_article ORDER BY total_vendu DESC LIMIT 1");
    if ($result) {
        $stats['article_plus_vendu'] = $result->fetch_assoc();
    }
    
    $result = $conn->query("SELECT COUNT(*) as nombre, SUM(prix) as total FROM vente WHERE DATE(date_vente) = CURDATE()");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['ventes_aujourdhui'] = ['nombre' => $row['nombre'] ?? 0, 'total' => $row['total'] ?? 0];
    }
    
    $result = $conn->query("SELECT COUNT(*) as nombre, SUM(prix) as total FROM vente WHERE MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente) = YEAR(CURDATE())");
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['ventes_mois'] = ['nombre' => $row['nombre'] ?? 0, 'total' => $row['total'] ?? 0];
    }
    
    return $stats;
}

function getVentesByClient($id_client) {
    global $conn;
    $stmt = $conn->prepare("SELECT v.*, a.nom_article, a.categorie FROM vente v LEFT JOIN article a ON v.id_article = a.id WHERE v.id_client = ? ORDER BY v.date_vente DESC");
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

function getVentesByArticle($id_article) {
    global $conn;
    $stmt = $conn->prepare("SELECT v.*, c.nom, c.prenom FROM vente v LEFT JOIN client c ON v.id_client = c.id WHERE v.id_article = ? ORDER BY v.date_vente DESC");
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

function getVentesByPeriode($date_debut, $date_fin) {
    global $conn;
    $stmt = $conn->prepare("SELECT v.*, a.nom_article, c.nom, c.prenom FROM vente v LEFT JOIN article a ON v.id_article = a.id LEFT JOIN client c ON v.id_client = c.id WHERE DATE(v.date_vente) BETWEEN ? AND ? ORDER BY v.date_vente DESC");
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