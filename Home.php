<?php
include('header.php');
include('functions.php');

$all_invoices = getAllInvoices();  // Ambil semua invoice dari database
$display_invoices = array_slice($all_invoices, 0, 6);  // Batasi hanya 6 invoice yang ditampilkan
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Dashboard</title>
    <style>
     body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
        color: #343a40;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .stat-card {
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Tambahkan bayangan */
        border-radius: 12px; /* Rounded corners */
        overflow: hidden; /* Agar sudut border terlihat rapi */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
    }

    .stat-card:hover {
        transform: translateY(-5px); /* Sedikit efek hover untuk interaksi */
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15); /* Bayangan lebih dalam saat dihover */
    }

    .card-header {
        padding: 15px;
        color: white;
        font-weight: bold;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-header-due {
        background-color: #dc3545; /* Merah untuk overdue */
    }

    .card-header-warning {
        background-color: #ff851b; /* Oranye untuk warning */
    }

    .card-header-upcoming {
        background-color: #ffc107; /* Kuning untuk upcoming */
        color: black;
    }

    .card-header-paid {
        background-color: #28a745; /* Hijau untuk paid */
    }

    .card-body {
        padding: 20px; /* Tambahkan padding untuk card body */
    }

    .card-title {
        font-size: 18px;
        margin-bottom: 10px;
        color: #343a40;
    }

    .card-text {
        margin-bottom: 8px;
        color: #555;
        font-size: 16px;
    }
    h1 {
        font-size: 36px;
        font-weight: 600;
        color: #3a3f51;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    h2 {
        font-size: 28px;
        font-weight: 500;
        color: #2a2d3e;
        margin-bottom: 15px;
        text-transform: uppercase;
        border-bottom: 2px solid #e5e5e5; /* Garis pembatas di bawah judul */
        padding-bottom: 5px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px; /* Tambahkan padding untuk lebih banyak ruang */
    }

    .header {
        text-align: center;
        margin-bottom: 30px; /* Tambahkan lebih banyak ruang antara header dan konten */
    }

    .view-more {
        text-align: center;
        margin-top: 20px;
    }

    .view-more a {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    .view-more a:hover {
        background-color: #0056b3;
    }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="header">
        <h1><span>Invoice</span> Dashboard</h1>
        <h2><span>All</span> Invoices</h2>
    </div>
    <div class="row">
        <?php if (count($display_invoices) > 0): ?>
            <?php foreach ($display_invoices as $invoice): ?>
                <?php
                // Tentukan status berdasarkan due date dan status
                $invoice_status = '';
                $card_class = '';

                $today = date('Y-m-d'); // Tanggal hari ini
                $due_date = $invoice['invoice_due_date'];
                $days_until_due = (strtotime($due_date) - strtotime($today)) / (60 * 60 * 24); // Hitung selisih hari

                if ($invoice['status'] == 'paid') {
                    $invoice_status = 'Paid';
                    $card_class = 'card-header-paid';
                } elseif ($due_date < $today && $invoice['status'] == 'open') {
                    $invoice_status = 'Unpaid (Overdue)';
                    $card_class = 'card-header-due';
                } elseif ($due_date == $today && $invoice['status'] == 'open') {
                    $invoice_status = 'Due Today';
                    $card_class = 'card-header-due';
                } elseif ($days_until_due <= 7 && $days_until_due > 0 && $invoice['status'] == 'open') {
                    // Jika due date dalam 7 hari dari sekarang, anggap sebagai "Warning"
                    $invoice_status = 'Warning: Due in ' . $days_until_due . ' days';
                    $card_class = 'card-header-warning';
                } elseif ($days_until_due > 7 && $invoice['status'] == 'open') {
                    $invoice_status = 'Upcoming';
                    $card_class = 'card-header-upcoming';
                }

                // Ambil produk dari tabel invoice_items (asumsi ada fungsi untuk ini)
                $invoice_items = getInvoiceItems($invoice['invoice']);
                $product_names = [];
                foreach ($invoice_items as $item) {
                    $product_names[] = $item['product_name']; // Ambil nama produk dari item invoice
                }
                $product_list = implode(', ', $product_names); // Gabungkan nama produk dengan koma
                ?>
                <!-- Wrap seluruh card dengan tag <a> untuk membuat card bisa diklik -->
                <div class="col-md-4">
                    <a href="invoice_detail.php?invoice=<?= $invoice['invoice']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="card stat-card">
                            <div class="card-header <?= $card_class; ?>">
                                <strong>Status: <?= $invoice_status; ?></strong><br>
                                <small>Due Date: <?= $invoice['invoice_due_date']; ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Customer: <?= $invoice['custom_email']; ?></h5>
                                <p class="card-text">Invoice: <?= $invoice['invoice']; ?></p>
                                <p class="card-text">Total: Rp. <?= number_format($invoice['total'], 0, ',', '.'); ?></p>
                                <p class="card-text">Products: <?= $product_list; ?></p> <!-- Tampilkan nama produk -->
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No invoices found.</p>
        <?php endif; ?>
    </div>

    <!-- Tampilkan tombol "View More" jika invoice lebih dari 6 -->
    <?php if (count($all_invoices) > 6): ?>
        <div class="view-more">
            <a href="all_invoices.php">View More Invoices</a>
        </div>
    <?php endif; ?>
</div>


    <!-- Muat Bootstrap JS dari lokal -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include('footer.php');
?>
