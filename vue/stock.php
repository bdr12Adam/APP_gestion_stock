<?php
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

// ========== STATISTIQUES GLOBALES ==========
$total_articles = $conn->query("SELECT COUNT(*) as total FROM article")->fetch_assoc()['total'] ?? 0;
$total_quantite = $conn->query("SELECT SUM(quantite) as total FROM article")->fetch_assoc()['total'] ?? 0;
$valeur_stock = $conn->query("SELECT SUM(quantite * prix_unitaire) as total FROM article")->fetch_assoc()['total'] ?? 0;

// Stock critique (< 5)
$stock_critique = $conn->query("SELECT COUNT(*) as total FROM article WHERE quantite < 5")->fetch_assoc()['total'] ?? 0;

// Stock faible (5-10)
$stock_faible = $conn->query("SELECT COUNT(*) as total FROM article WHERE quantite >= 5 AND quantite < 10")->fetch_assoc()['total'] ?? 0;

// Stock OK (> 10)
$stock_ok = $conn->query("SELECT COUNT(*) as total FROM article WHERE quantite >= 10")->fetch_assoc()['total'] ?? 0;

// ========== DONNÉES POUR GRAPHIQUES ==========

// Quantités par produit (Top 10)
$stock_par_produit = $conn->query("
    SELECT nom_article, quantite, prix_unitaire, (quantite * prix_unitaire) as valeur_totale, categorie
    FROM article 
    ORDER BY quantite DESC 
    LIMIT 10
");
$produits_data = [];
if ($stock_par_produit) {
    while ($row = $stock_par_produit->fetch_assoc()) {
        $produits_data[] = $row;
    }
}

// Répartition du stock par catégorie
$stock_par_categorie = $conn->query("
    SELECT categorie, SUM(quantite) as total_quantite, SUM(quantite * prix_unitaire) as valeur
    FROM article 
    WHERE categorie IS NOT NULL AND categorie != ''
    GROUP BY categorie
    ORDER BY total_quantite DESC
");
$categories_stock = [];
if ($stock_par_categorie) {
    while ($row = $stock_par_categorie->fetch_assoc()) {
        $categories_stock[] = $row;
    }
}

// Produits critiques à réapprovisionner
$produits_critiques = $conn->query("
    SELECT nom_article, quantite, categorie, prix_unitaire
    FROM article 
    WHERE quantite < 10
    ORDER BY quantite ASC
    LIMIT 10
");
$critiques_data = [];
if ($produits_critiques) {
    while ($row = $produits_critiques->fetch_assoc()) {
        $critiques_data[] = $row;
    }
}

// Tous les articles pour le tableau
$tous_articles = $conn->query("
    SELECT id, nom_article, categorie, quantite, prix_unitaire, 
           (quantite * prix_unitaire) as valeur_totale,
           date_fabrication, date_expiration
    FROM article 
    ORDER BY quantite ASC
");
$articles_tableau = [];
if ($tous_articles) {
    while ($row = $tous_articles->fetch_assoc()) {
        $articles_tableau[] = $row;
    }
}

// Calculer le pourcentage de santé du stock
$pourcentage_sante = 0;
if ($total_articles > 0) {
    $pourcentage_sante = round(($stock_ok / $total_articles) * 100);
}
?>

<style>
.stock-container {
    padding: 20px;
    max-width: 1800px;
    margin: 0 auto;
}

/* HEADER */
.stock-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    padding-top: 100px;
    border-radius: 20px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.stock-header h1 {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stock-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

/* CARTES KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out backwards;
}

.kpi-card:nth-child(1) { animation-delay: 0.1s; }
.kpi-card:nth-child(2) { animation-delay: 0.2s; }
.kpi-card:nth-child(3) { animation-delay: 0.3s; }
.kpi-card:nth-child(4) { animation-delay: 0.4s; }

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

.kpi-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.kpi-card.blue::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.kpi-card.green::before { background: linear-gradient(90deg, #10b981, #059669); }
.kpi-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }
.kpi-card.red::before { background: linear-gradient(90deg, #ef4444, #b91c1c); }

.kpi-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.kpi-card.blue .kpi-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.kpi-card.green .kpi-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.kpi-card.purple .kpi-icon {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    color: white;
}

.kpi-card.red .kpi-icon {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: white;
}

.kpi-info h3 {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 32px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
}

/* JAUGE DE SANTÉ */
.gauge-container {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    text-align: center;
}

.gauge-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
}

.gauge-wrapper {
    position: relative;
    width: 250px;
    height: 150px;
    margin: 0 auto 20px;
}

.gauge-value {
    font-size: 48px;
    font-weight: 800;
    margin: 20px 0;
}

.gauge-label {
    font-size: 16px;
    color: #64748b;
    font-weight: 600;
}

/* GRILLE GRAPHIQUES */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.chart-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 20px;
}

.chart-container {
    height: 350px;
    position: relative;
}

/* LISTE CRITIQUE */
.critical-list {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border-left: 5px solid #ef4444;
}

.critical-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.critical-item {
    background: #fef2f2;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.critical-item:hover {
    background: #fee2e2;
    transform: translateX(8px);
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}

.item-category {
    font-size: 13px;
    color: #64748b;
}

.item-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: white;
}

.badge-danger {
    background: #ef4444;
}

.badge-warning {
    background: #f59e0b;
}

.badge-success {
    background: #10b981;
}

/* TABLEAU */
.table-container {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow-x: auto;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.search-box {
    position: relative;
}

.search-input {
    padding: 10px 40px 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    font-size: 14px;
    width: 300px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
}

.stock-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1000px;
}

.stock-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.stock-table th {
    padding: 15px 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #cbd5e1;
    cursor: pointer;
    user-select: none;
}

.stock-table th:hover {
    background: #e2e8f0;
}

.stock-table td {
    padding: 14px 12px;
    font-size: 14px;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
}

.stock-table tbody tr {
    transition: all 0.2s ease;
}

.stock-table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.status-danger { background: #ef4444; }
.status-warning { background: #f59e0b; }
.status-success { background: #10b981; }

/* RESPONSIVE */
@media (max-width: 1400px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stock-container {
        padding: 15px;
    }
    
    .stock-header {
        padding: 20px;
        padding-top: 80px;
    }
    
    .stock-header h1 {
        font-size: 24px;
    }
    
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .search-input {
        width: 100%;
    }
}
</style>

<div class="stock-container">
    
    <!-- HEADER -->
    <div class="stock-header">
        <h1><i class='bx bx-package'></i> Gestion de Stock</h1>
        <p>📦 Suivi et analyse de votre inventaire - <?= date('d/m/Y à H:i') ?></p>
    </div>

    <!-- KPI CARDS -->
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-header">
                <div class="kpi-info">
                    <h3>Total Articles</h3>
                    <div class="kpi-value"><?= number_format($total_articles) ?></div>
                </div>
                <div class="kpi-icon"><i class='bx bx-box'></i></div>
            </div>
        </div>

        <div class="kpi-card green">
            <div class="kpi-header">
                <div class="kpi-info">
                    <h3>Quantité Totale</h3>
                    <div class="kpi-value"><?= number_format($total_quantite) ?></div>
                </div>
                <div class="kpi-icon"><i class='bx bx-package'></i></div>
            </div>
        </div>

        <div class="kpi-card purple">
            <div class="kpi-header">
                <div class="kpi-info">
                    <h3>Valeur Stock</h3>
                    <div class="kpi-value"><?= number_format($valeur_stock, 0) ?> DH</div>
                </div>
                <div class="kpi-icon"><i class='bx bx-dollar-circle'></i></div>
            </div>
        </div>

        <div class="kpi-card red">
            <div class="kpi-header">
                <div class="kpi-info">
                    <h3>Stock Critique</h3>
                    <div class="kpi-value"><?= number_format($stock_critique) ?></div>
                </div>
                <div class="kpi-icon"><i class='bx bx-error'></i></div>
            </div>
        </div>
    </div>

    <!-- JAUGE DE SANTÉ -->
    <div class="gauge-container">
        <div class="gauge-title">🎯 Santé du Stock</div>
        <div class="gauge-wrapper">
            <canvas id="stockGauge"></canvas>
        </div>
        <div class="gauge-value" style="color: <?= $pourcentage_sante >= 70 ? '#10b981' : ($pourcentage_sante >= 40 ? '#f59e0b' : '#ef4444') ?>">
            <?= $pourcentage_sante ?>%
        </div>
        <div class="gauge-label">
            <?php if ($pourcentage_sante >= 70): ?>
                ✅ Excellent état
            <?php elseif ($pourcentage_sante >= 40): ?>
                ⚠️ Attention requise
            <?php else: ?>
                🚨 Action urgente
            <?php endif; ?>
        </div>
        <div style="margin-top: 20px; font-size: 14px; color: #64748b;">
            🟢 Stock OK: <?= $stock_ok ?> | 🟡 Stock Faible: <?= $stock_faible ?> | 🔴 Stock Critique: <?= $stock_critique ?>
        </div>
    </div>

    <!-- GRAPHIQUES -->
    <div class="charts-grid">
        <!-- BAR CHART - Stock par produit -->
        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-bar-chart-alt-2'></i> Stock par Produit</div>
            <div class="chart-subtitle">Top 10 des produits en stock</div>
            <div class="chart-container"><canvas id="stockBarChart"></canvas></div>
        </div>

        <!-- PIE CHART - Répartition par catégorie -->
        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-pie-chart-alt-2'></i> Répartition par Catégorie</div>
            <div class="chart-subtitle">Distribution du stock par catégorie</div>
            <div class="chart-container"><canvas id="categoryPieChart"></canvas></div>
        </div>
    </div>

    <!-- PRODUITS CRITIQUES -->
    <?php if (count($critiques_data) > 0): ?>
    <div class="critical-list">
        <div class="critical-title">
            <span>🚨</span>
            <span>Produits à Réapprovisionner</span>
        </div>
        <?php foreach ($critiques_data as $item): ?>
        <div class="critical-item">
            <div class="item-info">
                <div class="item-name">
                    <span class="status-indicator <?= $item['quantite'] == 0 ? 'status-danger' : ($item['quantite'] < 5 ? 'status-danger' : 'status-warning') ?>"></span>
                    <?= htmlspecialchars($item['nom_article']) ?>
                </div>
                <div class="item-category">📁 <?= htmlspecialchars($item['categorie']) ?> | 💰 <?= number_format($item['prix_unitaire'], 2) ?> DH</div>
            </div>
            <span class="item-badge <?= $item['quantite'] == 0 ? 'badge-danger' : ($item['quantite'] < 5 ? 'badge-danger' : 'badge-warning') ?>">
                <?php if ($item['quantite'] == 0): ?>
                    ÉPUISÉ
                <?php elseif ($item['quantite'] < 5): ?>
                    CRITIQUE (<?= $item['quantite'] ?>)
                <?php else: ?>
                    FAIBLE (<?= $item['quantite'] ?>)
                <?php endif; ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- TABLEAU DÉTAILLÉ -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-title">📋 Inventaire Détaillé</div>
            <div class="search-box">
                <input type="text" class="search-input" id="searchTable" placeholder="🔍 Rechercher un article...">
                <i class='bx bx-search search-icon'></i>
            </div>
        </div>
        
        <table class="stock-table" id="stockTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">ID ↕</th>
                    <th onclick="sortTable(1)">Article ↕</th>
                    <th onclick="sortTable(2)">Catégorie ↕</th>
                    <th onclick="sortTable(3)">Quantité ↕</th>
                    <th onclick="sortTable(4)">Prix Unit. ↕</th>
                    <th onclick="sortTable(5)">Valeur Tot. ↕</th>
                    <th onclick="sortTable(6)">État</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles_tableau as $article): ?>
                <tr>
                    <td><?= $article['id'] ?></td>
                    <td><strong><?= htmlspecialchars($article['nom_article']) ?></strong></td>
                    <td><?= htmlspecialchars($article['categorie']) ?></td>
                    <td><strong><?= $article['quantite'] ?></strong></td>
                    <td><?= number_format($article['prix_unitaire'], 2) ?> DH</td>
                    <td><?= number_format($article['valeur_totale'], 2) ?> DH</td>
                    <td>
                        <?php if ($article['quantite'] == 0): ?>
                            <span class="item-badge badge-danger">ÉPUISÉ</span>
                        <?php elseif ($article['quantite'] < 5): ?>
                            <span class="item-badge badge-danger">CRITIQUE</span>
                        <?php elseif ($article['quantite'] < 10): ?>
                            <span class="item-badge badge-warning">FAIBLE</span>
                        <?php else: ?>
                            <span class="item-badge badge-success">OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const waitForChart = setInterval(function() {
        if (typeof Chart !== 'undefined') {
            clearInterval(waitForChart);
            initCharts();
        }
    }, 100);
});

function initCharts() {
    const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#ec4899'];
    
    const produitsData = <?= json_encode($produits_data) ?>;
    const categoriesData = <?= json_encode($categories_stock) ?>;
    const pourcentageSante = <?= $pourcentage_sante ?>;
    
    // 1. JAUGE
    new Chart(document.getElementById('stockGauge'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [pourcentageSante, 100 - pourcentageSante],
                backgroundColor: [
                    pourcentageSante >= 70 ? '#10b981' : (pourcentageSante >= 40 ? '#f59e0b' : '#ef4444'),
                    '#e2e8f0'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            circumference: 180,
            rotation: 270,
            cutout: '75%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
    
    // 2. BAR CHART
    if (produitsData && produitsData.length > 0) {
        new Chart(document.getElementById('stockBarChart'), {
            type: 'bar',
            data: {
                labels: produitsData.map(p => p.nom_article),
                datasets: [{
                    label: 'Quantité',
                    data: produitsData.map(p => parseInt(p.quantite)),
                    backgroundColor: colors,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
    
    // 3. PIE CHART
    if (categoriesData && categoriesData.length > 0) {
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoriesData.map(c => c.categorie),
                datasets: [{
                    data: categoriesData.map(c => parseInt(c.total_quantite)),
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, font: { size: 13, weight: '600' } }
                    }
                }
            }
        });
    }
}

// RECHERCHE TABLEAU
document.getElementById('searchTable').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#stockTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// TRI TABLEAU
let sortDirection = {};
function sortTable(columnIndex) {
    const table = document.getElementById('stockTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    sortDirection[columnIndex] = !sortDirection[columnIndex];
    const direction = sortDirection[columnIndex] ? 1 : -1;
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        const aNum = parseFloat(aText.replace(/[^0-9.-]/g, ''));
        const bNum = parseFloat(bText.replace(/[^0-9.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return direction * (aNum - bNum);
        }
        
        return direction * aText.localeCompare(bText);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}
</script>

<?php include "pied.php"; ?>