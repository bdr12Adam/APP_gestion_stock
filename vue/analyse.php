<?php  
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

// ----- RÉCUPÉRATION DES DONNÉES -----
$total_articles = $conn->query("SELECT COUNT(*) as total FROM article")->fetch_assoc()['total'] ?? 0;
$total_quantite = $conn->query("SELECT SUM(quantite) as total FROM article")->fetch_assoc()['total'] ?? 0;
$valeur_stock = $conn->query("SELECT SUM(quantite * prix_unitaire) as total FROM article")->fetch_assoc()['total'] ?? 0;
$categories_count = $conn->query("SELECT COUNT(DISTINCT categorie) as total FROM article")->fetch_assoc()['total'] ?? 0;

$categories_result = $conn->query("SELECT categorie, COUNT(*) as nombre, SUM(quantite) as total_quantite FROM article WHERE categorie IS NOT NULL AND categorie != '' GROUP BY categorie");
$categories_data = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories_data[] = $row;
    }
}

$valeur_categorie_result = $conn->query("SELECT categorie, SUM(quantite * prix_unitaire) as valeur_totale FROM article WHERE categorie IS NOT NULL AND categorie != '' GROUP BY categorie ORDER BY valeur_totale DESC");
$valeur_categorie = [];
if ($valeur_categorie_result) {
    while ($row = $valeur_categorie_result->fetch_assoc()) {
        $valeur_categorie[] = $row;
    }
}

// Évolution des ventes (7 derniers jours)
$ventes_evolution = $conn->query("SELECT DATE(date_vente) as date, COUNT(*) as nombre, SUM(prix_total) as montant FROM vente WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_vente) ORDER BY date ASC");
$evolution_data = [];
if ($ventes_evolution) {
    while ($row = $ventes_evolution->fetch_assoc()) {
        $evolution_data[] = $row;
    }
}

$top_articles = $conn->query("SELECT nom_article, prix_unitaire, quantite, (prix_unitaire * quantite) as valeur_totale FROM article ORDER BY valeur_totale DESC LIMIT 5");
$top_articles_data = [];
if ($top_articles) {
    while ($row = $top_articles->fetch_assoc()) {
        $top_articles_data[] = $row;
    }
}

$stock_faible = $conn->query("SELECT nom_article, quantite, categorie FROM article WHERE quantite < 10 ORDER BY quantite ASC LIMIT 5");
$stock_faible_data = [];
if ($stock_faible) {
    while ($row = $stock_faible->fetch_assoc()) {
        $stock_faible_data[] = $row;
    }
}
?>

<style>
.analytics-container { padding: 20px; max-width: 1600px; margin: 0 auto; }

.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    padding-top: 100px;
    border-radius: 20px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.page-header h1 { font-size: 32px; font-weight: 800; margin: 0 0 10px 0; }
.page-header p { margin: 0; font-size: 16px; opacity: 0.95; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); }

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }
.stat-card.green::before { background: linear-gradient(90deg, #10b981, #059669); }
.stat-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }
.stat-card.orange::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

.stat-header { display: flex; justify-content: space-between; align-items: flex-start; }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.stat-card.blue .stat-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.stat-card.green .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card.purple .stat-icon { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.stat-card.orange .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }

.stat-info h3 { font-size: 14px; color: #64748b; font-weight: 600; margin: 0 0 8px 0; text-transform: uppercase; }
.stat-info .stat-value { font-size: 32px; font-weight: 800; color: #1e293b; }

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
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.chart-title { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
.chart-subtitle { font-size: 13px; color: #64748b; margin-bottom: 20px; }
.chart-container { height: 350px; position: relative; }

.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 25px;
}

.table-card {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.table-card h3 { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }

.analysis-table { width: 100%; border-collapse: collapse; }
.analysis-table th {
    padding: 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    background: #f8fafc;
    border-bottom: 2px solid #cbd5e1;
}
.analysis-table td {
    padding: 14px 12px;
    font-size: 14px;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
}
.analysis-table tbody tr:hover { background-color: #f8fafc; }

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }

@media (max-width: 1200px) {
    .charts-grid, .tables-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .page-header h1 { font-size: 24px; }
}
</style>

<div class="analytics-container">

    <div class="page-header">
        <h1><i class='bx bx-line-chart'></i> Tableau  Analytique</h1>
        <p>📅 Analyses et statistiques - <?= date('d/m/Y à H:i') ?></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Total Articles</h3>
                    <div class="stat-value"><?= number_format($total_articles) ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-box'></i></div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Quantité Totale</h3>
                    <div class="stat-value"><?= number_format($total_quantite) ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-package'></i></div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Valeur du Stock</h3>
                    <div class="stat-value"><?= number_format($valeur_stock, 0) ?> DH</div>
                </div>
                <div class="stat-icon"><i class='bx bx-dollar-circle'></i></div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-header">
                <div class="stat-info">
                    <h3>Catégories</h3>
                    <div class="stat-value"><?= $categories_count ?></div>
                </div>
                <div class="stat-icon"><i class='bx bx-category'></i></div>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-pie-chart-alt-2'></i> Répartition par Catégorie</div>
            <div class="chart-subtitle">Distribution des articles par catégorie</div>
            <div class="chart-container"><canvas id="categoryPieChart"></canvas></div>
        </div>

        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-bar-chart-alt-2'></i> Valeur par Catégorie</div>
            <div class="chart-subtitle">Valeur totale en stock (DH)</div>
            <div class="chart-container"><canvas id="valueBarChart"></canvas></div>
        </div>

        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-line-chart'></i> Évolution des Ventes (7 derniers jours)</div>
            <div class="chart-subtitle">Nombre de ventes réalisées par jour</div>
            <div class="chart-container"><canvas id="evolutionLineChart"></canvas></div>
        </div>

        <div class="chart-card">
            <div class="chart-title"><i class='bx bx-doughnut-chart'></i> Quantité par Catégorie</div>
            <div class="chart-subtitle">Distribution des quantités en stock</div>
            <div class="chart-container"><canvas id="quantityDonutChart"></canvas></div>
        </div>
    </div>

    <div class="tables-grid">
        <div class="table-card">
            <h3><i class='bx bx-trophy' style="color: #f59e0b;"></i> Top 5 Articles (Valeur)</h3>
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prix Unit.</th>
                        <th>Qté</th>
                        <th>Valeur Totale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($top_articles_data) > 0): ?>
                        <?php foreach ($top_articles_data as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['nom_article']) ?></td>
                            <td><?= number_format($article['prix_unitaire'], 2) ?> DH</td>
                            <td><span class="badge badge-success"><?= $article['quantite'] ?></span></td>
                            <td><strong style="color: #8b5cf6;"><?= number_format($article['valeur_totale'], 2) ?> DH</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center;">Aucune donnée disponible</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-card">
            <h3><i class='bx bx-error-circle' style="color: #ef4444;"></i> Alertes Stock Faible</h3>
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Nom Article</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($stock_faible_data) > 0): ?>
                        <?php foreach ($stock_faible_data as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['nom_article']) ?></td>
                            <td><?= htmlspecialchars($article['categorie']) ?></td>
                            <td><strong style="color: #ef4444;"><?= $article['quantite'] ?></strong></td>
                            <td>
                                <?php if ($article['quantite'] == 0): ?>
                                    <span class="badge badge-danger">Rupture</span>
                                <?php elseif ($article['quantite'] < 5): ?>
                                    <span class="badge badge-danger">Critique</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Faible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #10b981;">✓ Tous les stocks sont satisfaisants</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Page chargée');
    
    // Attendre que Chart.js soit disponible
    const waitForChart = setInterval(function() {
        if (typeof Chart !== 'undefined') {
            clearInterval(waitForChart);
            console.log('✅ Chart.js chargé');
            initCharts();
        }
    }, 100);
});

function initCharts() {
    const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#f59e0b', '#10b981', '#3b82f6'];
    
    const categoriesData = <?= json_encode($categories_data) ?>;
    const valeurCategorieData = <?= json_encode($valeur_categorie) ?>;
    const evolutionData = <?= json_encode($evolution_data) ?>;
    
    console.log('📊 Données:', {
        categories: categoriesData,
        valeurs: valeurCategorieData,
        evolution: evolutionData
    });

    // 1. Camembert
    if (categoriesData && categoriesData.length > 0) {
        console.log('📈 Création camembert...');
        new Chart(document.getElementById('categoryPieChart'), {
            type: 'pie',
            data: {
                labels: categoriesData.map(c => c.categorie),
                datasets: [{
                    data: categoriesData.map(c => parseInt(c.nombre)),
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

    // 2. Barres
    if (valeurCategorieData && valeurCategorieData.length > 0) {
        console.log('📊 Création barres...');
        new Chart(document.getElementById('valueBarChart'), {
            type: 'bar',
            data: {
                labels: valeurCategorieData.map(c => c.categorie),
                datasets: [{
                    label: 'Valeur (DH)',
                    data: valeurCategorieData.map(c => parseFloat(c.valeur_totale)),
                    backgroundColor: colors,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // 3. Ligne - Évolution des ventes
    console.log('📈 Création ligne...');
    const last7Days = [];
    const dataMap = {};
    
    if (evolutionData) {
        evolutionData.forEach(item => {
            dataMap[item.date] = parseInt(item.nombre);
        });
    }
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        const dayLabel = date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
        last7Days.push({ label: dayLabel, value: dataMap[dateStr] || 0 });
    }

    new Chart(document.getElementById('evolutionLineChart'), {
        type: 'line',
        data: {
            labels: last7Days.map(d => d.label),
            datasets: [{
                label: 'Ventes',
                data: last7Days.map(d => d.value),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            return ` ${context.parsed.y} vente(s)`;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value + ' ventes';
                        }
                    }
                }
            }
        }
    });

    // 4. Donut
    if (categoriesData && categoriesData.length > 0) {
        console.log('🍩 Création donut...');
        new Chart(document.getElementById('quantityDonutChart'), {
            type: 'doughnut',
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
    
    console.log('✅ Tous les graphiques créés !');
}
</script>

<?php include "pied.php"; ?>