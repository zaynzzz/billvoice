<?php
include 'functions.php';

if (isset($_GET['invoice'])) {
    $invoiceId = $_GET['invoice'];
    
    // Delete invoice
    if (deleteInvoice($invoiceId)) {
        header("Location: index.php?status=deleted");
    } else {
        header("Location: index.php?status=error");
    }
}
?>
