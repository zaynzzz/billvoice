<?php
include('header.php');
include('functions.php');

// Mendapatkan daftar customer dan GPS package dari database
$customers = getAllCustomers();
$gps_packages = getAllGpsPackages();
$products = getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent GPS</title>
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        h1 {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        label {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rent GPS</h1>
        </div>
        <form id="rentalForm" method="POST" action="process_rent.php">
            <!-- Select Customer -->
            <h3>Select Customer</h3>
            <div class="mb-3">
                <label for="customer">Customer</label>
                <select name="customer_id" required>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id']; ?>"><?= $customer['name']; ?> - <?= $customer['email']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Select GPS Package -->
            <h3>Select GPS Package</h3>
            <div class="mb-3">
                <label for="gps_package">GPS Package</label>
                <select name="gps_package_id" required>
                    <?php foreach ($gps_packages as $gps_package): ?>
                        <option value="<?= $gps_package['package_id']; ?>"><?= $gps_package['package_name']; ?> - Rp. <?= number_format($gps_package['package_price'], 0, ',', '.'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Select Product -->
            <h3>Select Product</h3>
            <div class="mb-3">
                <label for="product_id">Product</label>
                <select name="product_id" required>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['product_id']; ?>"><?= $product['product_name']; ?> - <?= $product['imei']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rental Details -->
            <h3>Rental Details</h3>
            <div class="mb-3">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="mb-3">
                <label for="duration_months">Duration (Months)</label>
                <input type="number" name="duration_months" required min="1" value="12">
            </div>

            <!-- Notes Field -->
            <div class="mb-3">
                <label for="notes">Notes (Optional)</label>
                <textarea name="notes" rows="4" placeholder="Enter any additional notes here"></textarea>
            </div>

            <button type="submit">Create Rental</button>
        </form>

    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
</body>
</html>


<?php
include('footer.php');
?>
