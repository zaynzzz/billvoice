<?php
include 'functions.php';

if (isset($_GET['invoice'])) {
    $invoiceId = $_GET['invoice'];
    
    // Update status to "paid"
    if (updateInvoiceStatus($invoiceId, 'paid')) {
        header("Location: index.php?status=success");
    } else {
        header("Location: index.php?status=error");
    }
}
?>
