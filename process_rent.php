<?php
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data dari form POST
    $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
    $gps_package_id = isset($_POST['gps_package_id']) ? (int)$_POST['gps_package_id'] : null;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $duration_months = isset($_POST['duration_months']) ? (int)$_POST['duration_months'] : null;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

    // Validasi data
    if (!$customer_id || !$gps_package_id || !$product_id || !$start_date || !$duration_months) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    try {
        // Panggil fungsi createRental untuk menyimpan data
        createRental($customer_id, $gps_package_id, $product_id, $start_date, $duration_months, $notes);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create rental: ' . $e->getMessage()]);
    }
}
