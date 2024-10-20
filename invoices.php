<?php
include 'functions.php';

$invoiceId = $_GET['invoice'];

// Fetch invoice details (you can create a function to fetch the invoice if needed)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Invoice #<?= $invoiceId ?></h1>
        <div class="card">
            <div class="card-header">
                Invoice Details
            </div>
            <div class="card-body">
                <h5 class="card-title">Thank you for renting GPS!</h5>
                <p class="card-text">Your invoice has been generated. Please pay before the due date.</p>
                <a href="#" class="btn btn-primary">Pay Now</a>
            </div>
        </div>
    </div>
</body>
</html>
