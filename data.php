<?php
session_start();
header('Content-Type: application/json');
require_once("includes/config.php");
include('functions.php');

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

// Get Total Customers
$totalCustomersQuery = "SELECT COUNT(*) as totalCustomers FROM customers";
$totalCustomersResult = $mysqli->query($totalCustomersQuery) or die($mysqli->error);
$totalCustomers = $totalCustomersResult->fetch_assoc()['totalCustomers'];

// Get Total Packages
$totalPackagesQuery = "SELECT COUNT(*) as totalPackages FROM gps_packages";
$totalPackagesResult = $mysqli->query($totalPackagesQuery) or die($mysqli->error);
$totalPackages = $totalPackagesResult->fetch_assoc()['totalPackages'];

// Get Total Invoices
$totalInvoicesQuery = "SELECT COUNT(*) as totalInvoices FROM invoices";
$totalInvoicesResult = $mysqli->query($totalInvoicesQuery) or die($mysqli->error);
$totalInvoices = $totalInvoicesResult->fetch_assoc()['totalInvoices'];

// Get Total Revenue
$totalRevenueQuery = "SELECT SUM(total) as totalRevenue FROM invoices";
$totalRevenueResult = $mysqli->query($totalRevenueQuery) or die($mysqli->error);
$totalRevenue = $totalRevenueResult->fetch_assoc()['totalRevenue'];

// Get Monthly Revenue
$monthlyRevenueQuery = "SELECT MONTH(invoice_date) as month, SUM(total) as revenue FROM invoices GROUP BY MONTH(invoice_date)";
$monthlyRevenueResult = $mysqli->query($monthlyRevenueQuery) or die($mysqli->error);
$monthlyRevenue = array_fill(0, 12, 0);
while ($row = $monthlyRevenueResult->fetch_assoc()) {
    $monthIndex = $row['month'] - 1;
    $monthlyRevenue[$monthIndex] = (int) $row['revenue'];
}

// Get Package Distribution
$packageDistributionQuery = "SELECT package_name as label, COUNT(*) as count FROM gps_packages GROUP BY package_name";
$packageDistributionResult = $mysqli->query($packageDistributionQuery) or die($mysqli->error);
$packageDistribution = [];
while ($row = $packageDistributionResult->fetch_assoc()) {
    $packageDistribution[] = [
        'label' => $row['label'],
        'count' => (int) $row['count']
    ];
}

$response = [
    'totalCustomers' => $totalCustomers,
    'totalPackages' => $totalPackages,
    'totalInvoices' => $totalInvoices,
    'totalRevenue' => $totalRevenue,
    'monthlyRevenue' => $monthlyRevenue,
    'packageDistribution' => $packageDistribution
];

$mysqli->close();
echo json_encode($response);
?>
