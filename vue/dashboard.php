<?php
include "entete.php";
include "../model/connexion.php";

// ========== STATISTIQUES ==========
$sql_commandes = "SELECT COUNT(*) as total FROM commande";
$total_commandes = $conn->query($sql_commandes)->fetch_assoc()['total'];

$sql_ventes = "SELECT COUNT(*) as total FROM vente";
$total_ventes = $conn->query($sql_ventes)->fetch_assoc()['total'];

$sql_revenu = "SELECT SUM(prix_total) as total FROM vente";
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
    SELECT a.nom_article, SUM(v.quantite) as total_vendu, SUM(v.prix_total) as ca
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

// ========== RUPTURE DE STOCK ==========
$sql_rupture_stock = "
    SELECT nom_article, quantite
    FROM article
    WHERE quantite = 0
    ORDER BY nom_article ASC
    LIMIT 5
";
$rupture_stock = $conn->query($sql_rupture_stock);
?>

<style>
.dashboard-container {
    padding: 20px;
    max-width: 1800px;
    margin: 0 auto;
}

/* HEADER */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    padding-top: 100px;
    border-radius: 20px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.dashboard-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.dashboard-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

/* CARTES STATISTIQUES */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.15s; }
.stat-card:nth-child(3) { animation-delay: 0.2s; }
.stat-card:nth-child(4) { animation-delay: 0.25s; }
.stat-card:nth-child(5) { animation-delay: 0.3s; }
.stat-card:nth-child(6) { animation-delay: 0.35s; }
.stat-card:nth-child(7) { animation-delay: 0.4s; }
.stat-card:nth-child(8) { animation-delay: 0.45s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card:nth-child(1)::before { background: linear-gradient(90deg, #667eea, #764ba2); }
.stat-card:nth-child(2)::before { background: linear-gradient(90deg, #10b981, #059669); }
.stat-card:nth-child(3)::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
.stat-card:nth-child(4)::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.stat-card:nth-child(5)::before { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }
.stat-card:nth-child(6)::before { background: linear-gradient(90deg, #ec4899, #be185d); }
.stat-card:nth-child(7)::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }
.stat-card:nth-child(8)::before { background: linear-gradient(90deg, #ef4444, #b91c1c); }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    background: #f8fafc;
}

.stat-info h3 {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-info .stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
    margin: 0;
}

/* ALERTES */
.alerts-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.alert-box {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border-left: 5px solid;
    animation: slideInLeft 0.6s ease-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.alert-rupture {
    border-left-color: #ef4444;
    animation: shake 3s infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
    20%, 40%, 60%, 80% { transform: translateX(3px); }
}

.alert-faible {
    border-left-color: #f59e0b;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.alert-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: blink 1.5s infinite;
}

@keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.7; }
}

.alert-item {
    background: #f8fafc;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease-out backwards;
}

.alert-item:nth-child(1) { animation-delay: 0.1s; }
.alert-item:nth-child(2) { animation-delay: 0.2s; }
.alert-item:nth-child(3) { animation-delay: 0.3s; }
.alert-item:nth-child(4) { animation-delay: 0.4s; }
.alert-item:nth-child(5) { animation-delay: 0.5s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-item:hover {
    background: #f1f5f9;
    transform: translateX(8px) scale(1.02);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.alert-product {
    font-weight: 600;
    color: #334155;
}

.alert-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: white;
    animation: bounceScale 2s infinite;
}

@keyframes bounceScale {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.alert-rupture .alert-badge {
    background: #ef4444;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
}

.alert-faible .alert-badge {
    background: #f59e0b;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
}

/* SECTIONS PRINCIPALES */
.main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 25px;
}

.section-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-icon {
    font-size: 24px;
}

/* VENTES RÉCENTES */
.vente-item {
    background: #f8fafc;
    padding: 18px;
    margin-bottom: 12px;
    border-radius: 14px;
    border-left: 4px solid #667eea;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.vente-item:hover {
    background: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
    transform: translateX(8px);
}

.vente-info {
    flex: 1;
}

.vente-client {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    font-size: 15px;
}

.vente-produit {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.vente-prix {
    font-weight: 800;
    color: #667eea;
    font-size: 18px;
}

/* TOP PRODUITS */
.top-item {
    background: #f8fafc;
    padding: 18px;
    margin-bottom: 12px;
    border-radius: 14px;
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
    width: 4px;
    background: linear-gradient(180deg, #667eea, #764ba2);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.top-item:hover::before {
    transform: scaleY(1);
}

.top-item:hover {
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: scale(1.03);
}

.top-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.top-rank {
    font-size: 28px;
}

.top-product {
    font-weight: 700;
    color: #1e293b;
    font-size: 15px;
}

.top-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin-left: 10px;
}

.top-ca {
    font-weight: 800;
    color: #10b981;
    font-size: 16px;
}

/* BOUTONS */
.btn-primary {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 30px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-align: center;
    margin-top: 15px;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

/* ÉTAT VIDE */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-text {
    font-size: 16px;
    font-weight: 600;
}

/* RESPONSIVE */
@media (max-width: 1400px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 15px;
    }
    
    .dashboard-header {
        padding: 20px;
    }
    
    .dashboard-header h1 {
        font-size: 24px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .alerts-container {
        grid-template-columns: 1fr;
    }
    
    .stat-info .stat-value {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-container">
    
    <!-- HEADER -->
    <div class="dashboard-header">
        <h1><i class='bx bx-tachometer'></i> Tableau de Bord</h1>
        <p>📅 Vue d'ensemble de votre activité - <?= date('d/m/Y à H:i') ?></p>
    </div>

    <!-- CARTES STATISTIQUES -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Commandes</h3>
                    <div class="stat-value"><?= number_format($total_commandes) ?></div>
                </div>
                <div class="stat-icon">📦</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Ventes</h3>
                    <div class="stat-value"><?= number_format($total_ventes) ?></div>
                </div>
                <div class="stat-icon">🛒</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Profit</h3>
                    <div class="stat-value"><?= number_format($profit, 0) ?> DH</div>
                </div>
                <div class="stat-icon">💰</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Revenu Total</h3>
                    <div class="stat-value"><?= number_format($revenu_total, 0) ?> DH</div>
                </div>
                <div class="stat-icon">💵</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Articles</h3>
                    <div class="stat-value"><?= number_format($total_articles) ?></div>
                </div>
                <div class="stat-icon">📦</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Clients</h3>
                    <div class="stat-value"><?= number_format($total_clients) ?></div>
                </div>
                <div class="stat-icon">👥</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Fournisseurs</h3>
                    <div class="stat-value"><?= number_format($total_fournisseurs) ?></div>
                </div>
                <div class="stat-icon">🏭</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Valeur Stock</h3>
                    <div class="stat-value"><?= number_format($valeur_stock, 0) ?> DH</div>
                </div>
                <div class="stat-icon">🏪</div>
            </div>
        </div>
    </div>

    <!-- ALERTES -->
    <div class="alerts-container">
        <!-- RUPTURE DE STOCK -->
        <?php if ($rupture_stock && $rupture_stock->num_rows > 0): ?>
        <div class="alert-box alert-rupture">
            <div class="alert-title">
                <span>🚨</span>
                <span>URGENT ! Rupture de Stock</span>
            </div>
            <?php while ($item = $rupture_stock->fetch_assoc()): ?>
            <div class="alert-item">
                <div class="alert-product">❌ <?= htmlspecialchars($item['nom_article']) ?></div>
                <span class="alert-badge">ÉPUISÉ</span>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- STOCK FAIBLE -->
        <?php if ($stock_faible && $stock_faible->num_rows > 0): ?>
        <div class="alert-box alert-faible">
            <div class="alert-title">
                <span>⚠️</span>
                <span>Attention ! Stock Faible</span>
            </div>
            <?php while ($item = $stock_faible->fetch_assoc()): ?>
            <div class="alert-item">
                <div class="alert-product">📉 <?= htmlspecialchars($item['nom_article']) ?></div>
                <span class="alert-badge">Reste <?= $item['quantite'] ?></span>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- GRILLE PRINCIPALE -->
    <div class="main-grid">
        
        <!-- VENTES RÉCENTES -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-icon">🛍️</span>
                    <span>Ventes Récentes</span>
                </div>
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
                        <?= number_format($vente['prix_total'], 2) ?> DH
                    </div>
                </div>
                <?php endwhile; ?>
                
                <center>
                    <a href="../vue/vente.php" class="btn-primary">📋 Voir Toutes les Ventes</a>
                </center>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <div class="empty-text">Aucune vente pour le moment</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- TOP PRODUITS -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <span class="section-icon">🏆</span>
                    <span>Top Produits</span>
                </div>
            </div>
            
            <?php if ($top_articles && $top_articles->num_rows > 0): ?>
                <?php 
                $rank = 1;
                $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                while ($article = $top_articles->fetch_assoc()): 
                ?>
                <div class="top-item">
                    <div class="top-left">
                        <span class="top-rank"><?= $medals[$rank - 1] ?></span>
                        <div>
                            <span class="top-product"><?= htmlspecialchars($article['nom_article']) ?></span>
                            <span class="top-badge">×<?= $article['total_vendu'] ?></span>
                        </div>
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
                    <a href="article.php" class="btn-primary">📦 Gérer les Articles</a>
                </center>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📦</div>
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