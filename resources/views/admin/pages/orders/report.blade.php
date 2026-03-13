<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Fişi</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            width: 120px;
            font-weight: bold;
        }

        .info-value {
            display: table-cell;
        }

        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead {
            background-color: #f0f0f0;
        }

        table th {
            padding: 8px;
            text-align: left;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 11px;
        }

        table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 11px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 10px;
        }

        .variants {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .variant-item {
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="container" id="report-content">
        <!-- İçerik JavaScript ile doldurulacak -->
    </div>

    @if (isset($data))
        <script>
            window.reportDataFromPHP = @json($data);
        </script>
    @endif

    <script>
        function generateReportHTML(data) {
            const order = data.order;
            const settings = data.settings;
            const customer = data.customer;
            const items = data.items;
            const calc = data.calculations;

            let html = `
                <div class="header">
                    <h1>Sipariş Fişi</h1>
                </div>

                <div class="info-section">
                    <h2>Gönderen</h2>
                    <div class="info-row">
                        <span class="info-label">Ünvan:</span>
                        <span class="info-value">${settings.title || '—'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adres:</span>
                        <span class="info-value">${settings.address || '—'}</span>
                    </div>
                </div>

                <div class="info-section">
                    <h2>Alıcı</h2>
                    <div class="info-row">
                        <span class="info-label">Ad Soyad:</span>
                        <span class="info-value">${customer.name}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Adres:</span>
                        <span class="info-value">${customer.address}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Telefon:</span>
                        <span class="info-value">${customer.phone}</span>
                    </div>
                </div>
            `;

            return html;
        }

        function generatePDF(orderNumber) {
            const {
                jsPDF
            } = window.jspdf;
            const element = document.getElementById('report-content');

            html2canvas(element, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgWidth = 210;
                const pageHeight = 297;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save(`siparis-${orderNumber}.pdf`);
            }).catch(error => {
                console.error('PDF oluşturma hatası:', error);
                alert('PDF oluşturulurken bir hata oluştu.');
            });
        }

        // Sayfa yüklendiğinde veriyi al ve PDF oluştur
        document.addEventListener('DOMContentLoaded', function() {
            // Eğer data window üzerinden geliyorsa
            if (window.reportData) {
                document.getElementById('report-content').innerHTML = generateReportHTML(window.reportData);
                setTimeout(() => {
                    generatePDF(window.reportData.order.order_number);
                }, 500);
            }
            // Eğer data PHP'den geliyorsa
            else if (window.reportDataFromPHP) {
                document.getElementById('report-content').innerHTML = generateReportHTML(window.reportDataFromPHP);
                setTimeout(() => {
                    generatePDF(window.reportDataFromPHP.order.order_number);
                }, 500);
            }
            // AJAX ile veriyi al
            else {
                const urlParams = new URLSearchParams(window.location.search);
                const orderId = urlParams.get('order_id') || window.location.pathname.split('/').pop();

                if (orderId) {
                    fetch(`/admin/orders/${orderId}/report-pdf`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => {
                                    throw new Error(
                                        `HTTP ${response.status}: ${text.substring(0, 200)}`);
                                });
                            }
                            return response.json();
                        })
                        .then(response => {
                            if (response.code === 1 && response.data) {
                                document.getElementById('report-content').innerHTML = generateReportHTML(
                                    response.data);
                                setTimeout(() => {
                                    generatePDF(response.data.order.order_number);
                                }, 500);
                            } else {
                                document.getElementById('report-content').innerHTML =
                                    '<p>Veri alınırken bir hata oluştu: ' + (response.msg ||
                                        'Bilinmeyen hata') + '</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Veri alınamadı:', error);
                            document.getElementById('report-content').innerHTML =
                                '<p>Veri yüklenirken bir hata oluştu: ' + error.message + '</p>';
                        });
                } else {
                    document.getElementById('report-content').innerHTML =
                        '<p>Sipariş ID bulunamadı.</p>';
                }
            }
        });
    </script>
</body>

</html>
