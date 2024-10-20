<?php
include('header.php');
include('functions.php');

$invoice_id = $_GET['invoice']; // Ambil invoice ID dari URL
$invoice = getInvoiceById($invoice_id); // Mengambil detail invoice dan customer menggunakan fungsi getInvoiceById()

// Jika invoice tidak ditemukan, tampilkan pesan error
if (!$invoice) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice Not Found</title>
        <style>
            .container {
                text-align: center;
                padding: 50px;
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            }
            h1 {
                font-size: 36px;
                color: #dc3545;
                margin-bottom: 20px;
            }
            p {
                font-size: 18px;
                color: #555;
                margin-bottom: 20px;
            }
            a:hover {
                background-color: #007bff;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Invoice Not Found</h1>
            <p>The invoice you are looking for does not exist or may have been removed.</p>
            <a href="all_invoices.php">Back to Invoices</a>
        </div>
    </body>
    </html>
    ';
    include('footer.php');
    exit;
}

// Mendapatkan item-item dari invoice
$invoice_items = getInvoiceItems($invoice_id);

// Tanggal hari ini menggunakan PHP
$today = date('Y-m-d');
$today_month_day = date('m-d', strtotime($today));
$due_date = $invoice['invoice_due_date'];
$due_date_month_day = date('m-d', strtotime($due_date));
$days_until_due = (strtotime($due_date) - strtotime($today)) / (60 * 60 * 24); // Hitung selisih hari

// Menentukan status
$status = 'Closed'; // Default to closed

if ($days_until_due < 0) {
    $status = 'Closed'; // Invoice is closed if the due date has passed
} elseif ($today_month_day === $due_date_month_day) {
    $status = 'Due Today'; // Due today
} elseif ($days_until_due > 0 && $days_until_due <= 7) {
    $status = "Warning: Due in " . $days_until_due . " day" . ($days_until_due > 1 ? "s" : ""); // Warning for 7 days or less
} else {
    $status = 'Upcoming'; // More than 7 days until due
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Detail</title>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 26px;
            color: #333;
            font-weight: 600;
        }

        .invoice-detail {
            display: grid;
            grid-template-columns: 150px 10px auto;
            gap: 10px 20px;
            align-items: center;
            margin-bottom: 30px;
        }

        .invoice-detail .detail-item {
            font-size: 16px;
            color: #555;
        }

        .invoice-detail strong {
            font-size: 18px;
            color: #333;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
            display: inline-block;
        }

        .status-paid {
            background-color: #28a745;
            color: white;
        }

        .status-unpaid {
            background-color: #dc3545;
            color: white;
        }

        .status-warning {
            background-color: #ff851b;
            color: white;
        }

        .status-upcoming {
            background-color: #ffc107;
            color: black;
        }

        .phone-number a {
            color: #25d366;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
        }

        .phone-number a:hover {
            text-decoration: underline;
        }

        .phone-number i {
            margin-right: 8px;
            font-size: 22px;
            color: #25d366;
        }

        .notes {
            grid-column: 1 / -1;
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .notes label {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .notes span {
            font-size: 16px;
            color: #555;
            display: block;
            margin-top: 5px;
        }

        .total {
            font-size: 22px;
            font-weight: 600;
            color: #28a745;
            text-align: right;
            margin-top: 30px;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice: <?= $invoice['invoice']; ?></h1>
        </div>

        <div class="invoice-detail">
            <strong>Customer</strong>
            <span>:</span>
            <span><?= $invoice['custom_email']; ?></span>

            <strong>Due Date</strong>
            <span>:</span>
            <span><?= $invoice['invoice_due_date']; ?></span>

            <strong>Total</strong>
            <span>:</span>
            <span>Rp. <?= number_format($invoice['total'], 0, ',', '.'); ?></span>

            <strong>Status</strong>
            <span>:</span>
            <span class="status-badge <?= strpos($status, 'Warning') !== false ? 'status-warning' : (strpos($status, 'Due') !== false ? 'status-unpaid' : 'status-upcoming') ?>">
                <?= $status; ?>
            </span>

            <strong>Phone Number</strong>
            <span>:</span>
            <span>
                <a href="https://wa.me/<?= $invoice['customer_phone']; ?>" target="_blank">
                    <i class="fab fa-whatsapp"></i> +<?= $invoice['customer_phone']; ?>
                </a>
            </span>

            <div class="notes">
                <label>Notes</label>
                <span><?= $invoice['notes']; ?></span>
            </div>
        </div>

        <!-- Section for Total -->
        <div class="total">
            Total Amount Due: Rp. <?= number_format($invoice['total'], 0, ',', '.'); ?>
        </div>
    </div>
</body>
</html>

<?php
include('footer.php');
?>
