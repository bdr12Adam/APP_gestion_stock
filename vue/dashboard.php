<?php
include "entete.php";
include "../model/connexion.php";

// ========== STATISTIQUES ==========
$sql_commandes = "SELECT COUNT(*) as total FROM commande";
$total_commandes = $conn->query($sql_commandes)->fetch_assoc()['total'];

$sql_ventes = "SELECT COUNT(*) as total FROM vente";
$total_ventes = $conn->query($sql_ventes)->fetch_assoc()['total'];

$sql_revenu = "SELECT SUM(prix) as total FROM vente";
$revenu_total = $conn->query($sql_revenu)->fetch_assoc()['total'] ?? 0;

$sql_cout = "SELECT SUM(quantite * prix) as total FROM commande";
$cout_total = $conn->query($sql_cout)->fetch_assoc()['total'] ?? 0;

$profit = $revenu_total - $cout_total;

$sql_articles = "SELECT COUNT(*) as total FROM article";
$total_articles = $conn->query($sql_articles)->fetch_assoc()['total'];

$sql_clients = "SELECT COUNT(*) as total FROM client";
$total_clients = $conn->query($sql_clients)->fetch_assoc()['total'];

$sql_fournisseurs = "SELECT COUNT(*) as total FROM fournisseur";
$total_fournisseurs = $conn->query($sql_fournisseurs)->fetch_assoc()['total'];

$sql_valeur_stock = "SELECT SUM(quantite * prix_unitaire) as valeur FROM article";
$valeur_stock = $conn->query($sql_valeur_stock)->fetch_assoc()['valeur'] ?? 0;

// ========== VENTES RÉCENTES ==========
$sql_ventes_recentes = "
    SELECT v.*, a.nom_article, c.nom as client_nom, c.prenom as client_prenom
    FROM vente v
    LEFT JOIN article a ON v.id_article = a.id
    LEFT JOIN client c ON v.id_client = c.id
    ORDER BY v.date_vente DESC
    LIMIT 6
";
$ventes_recentes = $conn->query($sql_ventes_recentes);

// ========== TOP PRODUITS ==========
$sql_top_articles = "
    SELECT a.nom_article, SUM(v.quantite) as total_vendu, SUM(v.prix) as ca
    FROM vente v
    LEFT JOIN article a ON v.id_article = a.id
    GROUP BY v.id_article
    ORDER BY total_vendu DESC
    LIMIT 5
";
$top_articles = $conn->query($sql_top_articles);

// ========== STOCK FAIBLE ==========
$sql_stock_faible = "
    SELECT nom_article, quantite
    FROM article
    WHERE quantite < 10 AND quantite > 0
    ORDER BY quantite ASC
    LIMIT 5
";
$stock_faible = $conn->query($sql_stock_faible);
?>

<style>
/* ============================================
   STYLE GLOBAL
============================================ */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.home-content {
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

/* ============================================
   CARTES STATISTIQUES
============================================ */
.stat-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.stat-emoji {
    font-size: 50px;
    margin-bottom: 15px;
    display: block;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #2d3748;
    margin: 10px 0;
}

.stat-label {
    font-size: 14px;
    color: #718096;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Couleurs des cartes */
.stat-card:nth-child(1) { border-top: 5px solid #667eea; }
.stat-card:nth-child(2) { border-top: 5px solid #10b981; }
.stat-card:nth-child(3) { border-top: 5px solid #f59e0b; }
.stat-card:nth-child(4) { border-top: 5px solid #ef4444; }
.stat-card:nth-child(5) { border-top: 5px solid #8b5cf6; }
.stat-card:nth-child(6) { border-top: 5px solid #3b82f6; }
.stat-card:nth-child(7) { border-top: 5px solid #ec4899; }
.stat-card:nth-child(8) { border-top: 5px solid #06b6d4; }

/* ============================================
   SECTIONS PRINCIPALES
============================================ */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.dashboard-section {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    animation: fadeIn 0.6s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-title {
    font-size: 20px;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 3px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-emoji {
    font-size: 28px;
}

/* ============================================
   LISTE DES VENTES
============================================ */
.vente-item {
    padding: 15px;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border-radius: 15px;
    border-left: 4px solid #667eea;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.vente-item:hover {
    transform: translateX(10px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
}

.vente-info {
    flex: 1;
}

.vente-client {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
}

.vente-produit {
    font-size: 13px;
    color: #718096;
}

.vente-prix {
    font-weight: bold;
    color: #667eea;
    font-size: 16px;
}

/* ============================================
   TOP PRODUITS
============================================ */
.top-item {
    padding: 15px;
    margin-bottom: 10px;
    background: #f9fafb;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.top-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: linear-gradient(180deg, #667eea, #764ba2);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.top-item:hover::before {
    transform: scaleY(1);
}

.top-item:hover {
    background: #fff;
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.top-product {
    font-weight: 600;
    color: #2d3748;
}

.top-ca {
    font-weight: bold;
    color: #10b981;
}

.top-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    margin-left: 10px;
}

/* ============================================
   ALERTE STOCK
============================================ */
.alert-stock {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    padding: 20px;
    border-radius: 20px;
    margin-bottom: 25px;
    border-left: 5px solid #ef4444;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.alert-title {
    font-size: 18px;
    font-weight: bold;
    color: #991b1b;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-item {
    background: white;
    padding: 12px 15px;
    margin-bottom: 8px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.alert-item:hover {
    transform: translateX(5px);
}

.alert-product {
    font-weight: 600;
    color: #dc2626;
}

.alert-badge {
    background: #ef4444;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

/* ============================================
   BOUTONS
============================================ */
.btn-view-all {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-view-all:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

/* ============================================
   ÉTAT VIDE
============================================ */
.empty-state {
    text-align: center;
    padding: 40px;
    color: #9ca3af;
}

.empty-emoji {
    font-size: 80px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-text {
    font-size: 16px;
    font-weight: 600;
}

/* ============================================
   RESPONSIVE
============================================ */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stat-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-emoji {
        font-size: 40px;
    }
    
    .stat-number {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .stat-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="home-content">
    
    <!-- 📊 CARTES STATISTIQUES -->
    <div class="stat-cards">
        <div class="stat-card">
            <span class="stat-emoji">📦</span>
            <div class="stat-number"><?= number_format($total_commandes) ?></div>
            <div class="stat-label">Commandes</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">🛒</span>
            <div class="stat-number"><?= number_format($total_ventes) ?></div>
            <div class="stat-label">Ventes</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">💰</span>
            <div class="stat-number"><?= number_format($profit, 0) ?> DH</div>
            <div class="stat-label">Profit</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">💵</span>
            <div class="stat-number"><?= number_format($revenu_total, 0) ?> DH</div>
            <div class="stat-label">Revenu</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">📦</span>
            <div class="stat-number"><?= number_format($total_articles) ?></div>
            <div class="stat-label">Articles</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">👥</span>
            <div class="stat-number"><?= number_format($total_clients) ?></div>
            <div class="stat-label">Clients</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">🏭</span>
            <div class="stat-number"><?= number_format($total_fournisseurs) ?></div>
            <div class="stat-label">Fournisseurs</div>
        </div>
        
        <div class="stat-card">
            <span class="stat-emoji">🏪</span>
            <div class="stat-number"><?= number_format($valeur_stock, 0) ?> DH</div>
            <div class="stat-label">Valeur Stock</div>
        </div>
    </div>

    <!-- ⚠️ ALERTE STOCK FAIBLE -->
    <?php if ($stock_faible && $stock_faible->num_rows > 0): ?>
    <div class="alert-stock">
        <div class="alert-title">
            <span>⚠️</span>
            <span>Attention ! Stock Faible</span>
        </div>
        <?php while ($item = $stock_faible->fetch_assoc()): ?>
        <div class="alert-item">
            <div>
                <div class="alert-product">📉 <?= htmlspecialchars($item['nom_article']) ?></div>
            </div>
            <span class="alert-badge">Reste <?= $item['quantite'] ?></span>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <!-- GRILLE PRINCIPALE -->
    <div class="dashboard-grid">
        
        <!-- 🛒 VENTES RÉCENTES -->
        <div class="dashboard-section">
            <div class="section-title">
                <span class="section-emoji">🛍️</span>
                <span>Ventes Récentes</span>
            </div>
            
            <?php if ($ventes_recentes && $ventes_recentes->num_rows > 0): ?>
                <?php while ($vente = $ventes_recentes->fetch_assoc()): ?>
                <div class="vente-item">
                    <div class="vente-info">
                        <div class="vente-client">
                            👤 <?= htmlspecialchars($vente['client_nom']) ?> <?= htmlspecialchars($vente['client_prenom']) ?>
                        </div>
                        <div class="vente-produit">
                            📦 <?= htmlspecialchars($vente['nom_article']) ?> (×<?= $vente['quantite'] ?>)
                        </div>
                    </div>
                    <div class="vente-prix">
                        <?= number_format($vente['prix'], 2) ?> DH
                    </div>
                </div>
                <?php endwhile; ?>
                
                <center>
                    <a href="../vue/vente.php" class="btn-view-all">📋 Voir Toutes les Ventes</a>
                </center>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-emoji">🛒</div>
                    <div class="empty-text">Aucune vente pour le moment</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 🏆 TOP PRODUITS -->
        <div class="dashboard-section">
            <div class="section-title">
                <span class="section-emoji">🏆</span>
                <span>Top Produits</span>
            </div>
            
            <?php if ($top_articles && $top_articles->num_rows > 0): ?>
                <?php 
                $rank = 1;
                $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                while ($article = $top_articles->fetch_assoc()): 
                ?>
                <div class="top-item">
                    <div>
                        <span style="font-size: 24px; margin-right: 10px;"><?= $medals[$rank - 1] ?></span>
                        <span class="top-product"><?= htmlspecialchars($article['nom_article']) ?></span>
                        <span class="top-badge">×<?= $article['total_vendu'] ?></span>
                    </div>
                    <div class="top-ca">
                        <?= number_format($article['ca'], 0) ?> DH
                    </div>
                </div>
                <?php 
                $rank++;
                endwhile; 
                ?>
                
                <center>
                    <a href="article.php" class="btn-view-all">📦 Gérer les Articles</a>
                </center>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-emoji">📦</div>
                    <div class="empty-text">Aucune donnée disponible</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</section>

<?php
include "pied.php";
$conn->close();
?>