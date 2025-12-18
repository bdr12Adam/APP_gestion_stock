<?php  
include "entete.php";
include "../model/connexion.php";
include_once "../model/functions.php";

// Récupérer toutes les ventes pour le reçu
$ventes = getVentes();
$totalGeneral = 0;
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .download-btn i {
            font-size: 22px;
        }

        /* Zone du reçu */
        #receipt {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            animation: slideInUp 0.6s ease-out;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
            max-width: 800px;
            margin: 0 auto;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* En-tête du reçu */
        .receipt-header {
            text-align: center;
            border-bottom: 5px solid #1e90ff;
            padding-bottom: 30px;
            margin-bottom: 35px;
            position: relative;
        }

        .receipt-header::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 5px;
            background: #0b5ed7;
        }

        .receipt-header h1 {
            font-size: 48px;
            font-weight: 900;
            color: #1e90ff;
            margin-bottom: 8px;
            letter-spacing: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .receipt-header .subtitle {
            font-size: 18px;
            color: #2d3748;
            font-weight: 700;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .pdf-mode {
    max-width: 100% !important;
    padding: 20px !important;
}

.pdf-mode .receipt-header h1 {
    font-size: 32px !important;
}

.pdf-mode table {
    font-size: 12px !important;
}

.pdf-mode th,
.pdf-mode td {
    padding: 8px 6px !important;
}

.pdf-mode .receipt-info {
    grid-template-columns: repeat(2, 1fr) !important;
}

.pdf-mode .receipt-total .amount {
    font-size: 28px !important;
}

/* Désactiver effets lourds */
.pdf-mode * {
    box-shadow: none !important;
    text-shadow: none !important;
}

        .receipt-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 35px;
            padding: 25px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 15px;
            border: 3px solid #1e90ff;
            box-shadow: 0 4px 15px rgba(30, 144, 255, 0.2);
        }

        .receipt-info div {
            font-size: 15px;
            color: #1565c0;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #1e90ff;
        }

        .receipt-info strong {
            color: #0d47a1;
            font-weight: 800;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .receipt-info span {
            font-size: 16px;
            font-weight: 700;
            color: #1e90ff;
        }

        /* Tableau */
        .receipt-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 35px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            border: 3px solid #1e90ff;
        }

        .receipt-table thead {
            background: linear-gradient(135deg, #1e90ff 0%, #0b5ed7 100%);
            color: white;
        }
        .receipt-table {
    page-break-inside: avoid;
}

.receipt-table tr {
    page-break-inside: avoid;
}

        .receipt-table th {
            padding: 18px 12px;
            text-align: center;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 3px solid #0a4da3;
        }

        .receipt-table td {
            padding: 16px 12px;
            text-align: center;
            font-size: 15px;
            color: #2d3748;
            border-bottom: 2px solid #e3f2fd;
            font-weight: 600;
        }

        .receipt-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .receipt-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .receipt-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Total */
        .receipt-total {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 15px;
            border: 4px solid #1e90ff;
            margin-top: 25px;
            box-shadow: 0 8px 25px rgba(30, 144, 255, 0.3);
        }

        .receipt-total h3 {
            font-size: 22px;
            color: #0d47a1;
            font-weight: 900;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .receipt-total .amount {
            font-size: 42px;
            color: #1e90ff;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            background: white;
            border-radius: 10px;
            display: inline-block;
            border: 3px solid #1e90ff;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 45px;
            padding-top: 25px;
            border-top: 3px dashed #1e90ff;
            color: #546e7a;
            font-size: 14px;
        }

        .receipt-footer p {
            margin: 8px 0;
            line-height: 1.8;
        }

        .receipt-footer p:first-child {
            font-size: 16px;
            color: #1e90ff;
            font-weight: 800;
        }

        .badge-prix {
            background: linear-gradient(135deg, #1e90ff 0%, #0b5ed7 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 800;
            font-size: 14px;
            display: inline-block;
            box-shadow: 0 3px 10px rgba(30, 144, 255, 0.3);
        }

        @media print {
            .download-btn {
                display: none;
            }
            .home-section {
                margin: 0;
                padding: 0;
            }
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            font-size: 18px;
        }

        .no-data i {
            font-size: 64px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<section class="home-section">
    <div class="home-content">
        <div style="max-width: 950px; margin: 0 auto; padding: 20px;">
            
            <!-- Bouton de téléchargement -->
            <button class="download-btn" onclick="downloadPDF()">
                <i class='bx bxs-download'></i>
                Télécharger en PDF
            </button>

            <!-- Zone du reçu -->
            <div id="receipt">
                
                <!-- En-tête -->
                <div class="receipt-header">
                    <h1>S-TOCK</h1>
                    <div class="subtitle">📋 REÇU DE VENTE</div>
                </div>

                <!-- Informations du reçu -->
                <div class="receipt-info">
                    <div>
                        <strong>📅 Date d'émission</strong>
                        <span><?= date('d/m/Y') ?></span>
                    </div>
                    <div>
                        <strong>🕐 Heure</strong>
                        <span><?= date('H:i:s') ?></span>
                    </div>
                    <div>
                        <strong>🔢 N° Reçu</strong>
                        <span><?= strtoupper(uniqid('REC-')) ?></span>
                    </div>
                </div>

                <?php if (count($ventes) > 0): ?>
                    
                    <!-- Tableau des ventes -->
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Article</th>
                                <th>Client</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Prix Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($ventes as $vente): 
                                $totalGeneral += $vente['prix'];
                            ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($vente['nom_article'] ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($vente['nom'] ?? 'N/A') ?> <?= htmlspecialchars($vente['prenom'] ?? '') ?></td>
                                    <td><?= $vente['quantite'] ?></td>
                                    <td><?= number_format($vente['prix'] / $vente['quantite'], 2) ?> DH</td>
                                    <td><span class="badge-prix"><?= number_format($vente['prix'], 2) ?> DH</span></td>
                                    <td><?= date('d/m/Y', strtotime($vente['date_vente'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Total Général -->
                    <div class="receipt-total">
                        <h3>💰 TOTAL GÉNÉRAL</h3>
                        <div class="amount"><?= number_format($totalGeneral, 2) ?> DH</div>
                    </div>

                <?php else: ?>
                    <div class="no-data">
                        <i class='bx bx-receipt'></i>
                        <p>Aucune vente enregistrée pour le moment.</p>
                    </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="receipt-footer">
                    <p><strong>Merci pour votre confiance !</strong></p>
                    <p>S-TOCK - Système de Gestion de Stock</p>
                    <p>📞 Contact: +212 XXX-XXXXXX | 📧 Email: contact@s-tock.ma</p>
                </div>

            </div>

        </div>
    </div>
</section>

<script>
function downloadPDF() {
    const element = document.getElementById('receipt');

    // Activer mode PDF
    element.classList.add('pdf-mode');

    const opt = {
        margin: 10,
        filename: 'Recue_S-TOCK_<?= date("Y-m-d_H-i") ?>.pdf',
        image: { type: 'jpeg', quality: 0.95 },
        html2canvas: {
            scale: 2,              // ⚠ PAS PLUS
            useCORS: true,
            backgroundColor: '#ffffff'
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        // Désactiver mode PDF après génération
        element.classList.remove('pdf-mode');
    });
}
</script>


<?php include "pied.php"; ?>

</body>
</html>