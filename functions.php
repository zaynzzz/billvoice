<?php


include_once("includes/config.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Periksa apakah 'duration_months' ada dalam request
    if (!isset($_POST['duration_months'])) {
        echo json_encode(['status' => 'error', 'message' => 'Duration is required.']);
        exit;
    }

    $customer_id = $_POST['customer_id'];
    $gps_package_id = $_POST['gps_package_id'];
    $start_date = $_POST['start_date'];
    $duration_months = $_POST['duration_months'];
    $notes = $_POST['notes'];

    // Validasi bahwa duration harus integer
    if (!is_numeric($duration_months) || intval($duration_months) != $duration_months) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create rental: Duration must be an integer.']);
        exit;
    }

    $duration_months = intval($duration_months); // Konversi menjadi integer

    
}
// get invoice list
// function getInvoices() {

// 	// Connect to the database
// 	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// 	// output any connection error
// 	if ($mysqli->connect_error) {
// 		die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
// 	}

// 	// the query
//     $query = "SELECT * 
// 		FROM invoices i
// 		JOIN customers c
// 		ON c.invoice = i.invoice
// 		WHERE i.invoice = c.invoice
// 		ORDER BY i.invoice";

// 	// mysqli select query
// 	$results = $mysqli->query($query);

// 	// mysqli select query
// 	if($results) {

// 		print '<table class="table table-striped table-bordered" id="data-table" cellspacing="0"><thead><tr>

// 				<th><h4>Invoice</h4></th>
// 				<th><h4>Customer</h4></th>
// 				<th><h4>Issue Date</h4></th>
// 				<th><h4>Due Date</h4></th>
// 				<th><h4>Type</h4></th>
// 				<th><h4>Status</h4></th>
// 				<th><h4>Action</h4></th>

// 			  </tr></thead><tbody>';

// 		while($row = $results->fetch_assoc()) {

// 			print '
// 				<tr>
// 					<td>'.$row["invoice"].'</td>
// 					<td>'.$row["name"].'</td>
// 				    <td>'.$row["invoice_date"].'</td>
// 				    <td>'.$row["invoice_due_date"].'</td>
// 				    <td>'.$row["invoice_type"].'</td>
// 				';

// 				if($row['status'] == "open"){
// 					print '<td><span class="label label-info">'.$row['status'].'</span></td>';
// 				} elseif ($row['status'] == "paid"){
// 					print '<td><span class="label label-success">'.$row['status'].'</span></td>';
// 				}

// 				print '
// 				<td>
// 					<a href="invoice-edit.php?id=' . $row["invoice"] . '" class="btn btn-primary btn-xs">
// 						<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
// 					</a> 
// 					<a href="#" data-invoice-id="' . $row["invoice"] . '" data-email="' . $row["email"] . '" data-invoice-type="' . $row["invoice_type"] . '" data-custom-email="' . $row["custom_email"] . '" class="btn btn-success btn-xs email-invoice">
// 						<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
// 					</a> 
// 					<a href="/invoices/' . $row["invoice"] . '.pdf" class="btn btn-info btn-xs" target="_blank">
//     <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
// 	</a>

// 					<a data-invoice-id="' . $row["invoice"] . '" class="btn btn-danger btn-xs delete-invoice">
// 						<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
// 					</a>
// 				</td>
// 			';
			

// 		}

// 		print '</tr></tbody></table>';

// 	} else {

// 		echo "<p>There are no invoices to display.</p>";

// 	}

// 	// Frees the memory associated with a result
// 	$results->free();

// 	// close connection 
// 	$mysqli->close();

// }

function getInvoiceById($invoice_id) {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT invoices.*, store_customers.phone AS customer_phone
                           FROM invoices 
                           JOIN store_customers ON invoices.customer_id = store_customers.id 
                           WHERE invoices.invoice = :invoice_id");
    $stmt->bindParam(':invoice_id', $invoice_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getInvoiceItems($invoice_id) {
    $pdo = dbConnect();
    
    // Ambil data dari invoice_items, gps_packages, dan products
    $stmt = $pdo->prepare("
        SELECT 
            invoice_items.qty,
            invoice_items.price,
            gps_packages.package_name, 
            gps_packages.package_price, 
            products.product_name 
        FROM invoice_items
        LEFT JOIN gps_packages ON invoice_items.gps_package_id = gps_packages.package_id
        LEFT JOIN products ON invoice_items.product_id = products.product_id
        WHERE invoice_items.invoice = :invoice_id
    ");
    
    $stmt->bindParam(':invoice_id', $invoice_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function createRental($customer_id, $gps_package_id, $product_id, $start_date, $duration_months, $notes) {
    try {
        $pdo = dbConnect();

        // Buat invoice ID yang unik
        $invoice_id = uniqid();

        // Dapatkan harga GPS package dari tabel gps_packages
        $stmt = $pdo->prepare("SELECT package_price FROM gps_packages WHERE package_id = :gps_package_id");
        $stmt->bindParam(':gps_package_id', $gps_package_id);
        $stmt->execute();
        $gps_package = $stmt->fetch();

        if (!$gps_package) {
            throw new Exception("GPS package not found.");
        }

        // Dapatkan harga dan nama produk dari tabel products
        $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Product not found.");
        }

        // Set subtotal dari GPS package price
        $subtotal = $gps_package['package_price'];
        $total = $subtotal; // Bisa ditambahkan diskon atau pajak jika perlu

        // Masukkan data ke tabel invoices
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice, custom_email, invoice_date, invoice_due_date, subtotal, total, product_id, customer_id, start_date, duration_months, notes, status, gps_package_id) 
                               VALUES (:invoice, (SELECT email FROM store_customers WHERE id = :customer_id), :invoice_date, :invoice_due_date, :subtotal, :total, :product_id, :customer_id, :start_date, :duration_months, :notes, 'open', :gps_package_id)");
        $stmt->bindParam(':invoice', $invoice_id);
        $stmt->bindParam(':invoice_date', $start_date);
        $invoice_due_date = date('Y-m-d', strtotime("+$duration_months months", strtotime($start_date)));
        $stmt->bindParam(':invoice_due_date', $invoice_due_date);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':duration_months', $duration_months);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':gps_package_id', $gps_package_id);
        $stmt->execute();

        // Simpan item invoice di tabel invoice_items
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice, product, qty, price, subtotal, product_id, gps_package_id) 
                               VALUES (:invoice, :product, 1, :price, :subtotal, :product_id, :gps_package_id)");
        $stmt->bindParam(':invoice', $invoice_id);
        $stmt->bindParam(':product', $product['product_name']);  // Memasukkan nama produk dari tabel products
        $stmt->bindParam(':price', $subtotal);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':gps_package_id', $gps_package_id);
        $stmt->execute();

    } catch (Exception $e) {
        // Lemparkan error untuk ditangani di level yang lebih tinggi
        throw new Exception("Failed to create rental: " . $e->getMessage());
    }
}







// Database connection

function getAllInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT * FROM invoices ORDER BY invoice_due_date ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all GPS products
function getAllGPSProducts() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT * FROM gps_packages");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Function to get all customers
function getAllCustomers() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT * FROM store_customers");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// 1. Create new invoice
function createInvoice($customerData, $productData, $startDate, $durationMonths) {
    $pdo = dbConnect();

    // Insert into customers table
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, address_1, phone) VALUES (:name, :email, :address, :phone)");
    $stmt->bindParam(':name', $customerData['name']);
    $stmt->bindParam(':email', $customerData['email']);
    $stmt->bindParam(':address', $customerData['address']);
    $stmt->bindParam(':phone', $customerData['phone']);
    $stmt->execute();
    
    $customerId = $pdo->lastInsertId();

    // Insert invoices for each month
    for ($month = 0; $month < $durationMonths; $month++) {
        $invoice = uniqid();
        $invoiceDate = date('Y-m-d', strtotime("+$month month", strtotime($startDate)));
        $dueDate = date('Y-m-d', strtotime("+1 month", strtotime($invoiceDate)));
        $subtotal = $productData['package_price'];
        $total = $subtotal;

        // Insert into invoices table
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice, custom_email, invoice_date, invoice_due_date, subtotal, total, status) 
                               VALUES (:invoice, :email, :invoiceDate, :dueDate, :subtotal, :total, 'open')");
        $stmt->bindParam(':invoice', $invoice);
        $stmt->bindParam(':email', $customerData['email']);
        $stmt->bindParam(':invoiceDate', $invoiceDate);
        $stmt->bindParam(':dueDate', $dueDate);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':total', $total);
        $stmt->execute();

        // Insert into invoice_items table
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice, product, qty, price, subtotal) 
                               VALUES (:invoice, :product, 1, :price, :subtotal)");
        $stmt->bindParam(':invoice', $invoice);
        $stmt->bindParam(':product', $productData['package_name']);
        $stmt->bindParam(':price', $productData['package_price']);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->execute();
    }
}

// 2. Read invoices
function getInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.custom_email = c.email
                           ORDER BY i.invoice_due_date ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Update invoice status (mark as paid)
function updateInvoiceStatus($invoiceId, $newStatus) {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("UPDATE invoices SET status = :status WHERE invoice = :invoiceId");
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':invoiceId', $invoiceId);
    return $stmt->execute();
}

// 4. Delete invoice
function deleteInvoice($invoiceId) {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE invoice = :invoiceId");
    $stmt->bindParam(':invoiceId', $invoiceId);
    return $stmt->execute();
}
function createInvoiceWithMonthlyPayments($customerData, $productData, $startDate, $durationMonths) {
    $pdo = dbConnect();

    try {
        for ($month = 0; $month < $durationMonths; $month++) {
            // ID invoice yang unik
            $invoice = uniqid();

            // Menentukan tanggal invoice dan due date
            $invoiceDate = date('Y-m-d', strtotime("+$month month", strtotime($startDate)));
            $dueDate = date('Y-m-d', strtotime("+1 month", strtotime($invoiceDate)));

            // Memastikan nilai subtotal, shipping, discount, vat memiliki default jika kosong
            $subtotal = isset($productData['package_price']) ? $productData['package_price'] : 0;
            $shipping = 0;
            $discount = 0;
            $vat = 0;
            $total = $subtotal + $shipping - $discount + $vat;

            // Notes dan invoice type default kosong
            $notes = '';
            $invoice_type = '';
            $status = 'open';

            // Insert invoice baru untuk setiap bulan
            $stmt = $pdo->prepare("
                INSERT INTO invoices (
                    invoice, custom_email, invoice_date, invoice_due_date, subtotal, shipping, discount, vat, total, notes, invoice_type, status
                ) 
                VALUES (
                    :invoice, :email, :invoiceDate, :dueDate, :subtotal, :shipping, :discount, :vat, :total, :notes, :invoice_type, :status
                )
            ");

            // Bind parameters
            $stmt->bindParam(':invoice', $invoice);
            $stmt->bindParam(':email', $customerData['email']);
            $stmt->bindParam(':invoiceDate', $invoiceDate);
            $stmt->bindParam(':dueDate', $dueDate);
            $stmt->bindParam(':subtotal', $subtotal);
            $stmt->bindParam(':shipping', $shipping);
            $stmt->bindParam(':discount', $discount);
            $stmt->bindParam(':vat', $vat);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':invoice_type', $invoice_type);
            $stmt->bindParam(':status', $status);

            // Eksekusi query
            $stmt->execute();

            // Insert item penyewaan ke invoice_items
            $stmt = $pdo->prepare("
                INSERT INTO invoice_items (invoice, product, qty, price, subtotal) 
                VALUES (:invoice, :product, 1, :price, :subtotal)
            ");

            // Bind parameters untuk invoice items
            $stmt->bindParam(':invoice', $invoice);
            $stmt->bindParam(':product', $productData['package_name']);
            $stmt->bindParam(':price', $productData['package_price']);
            $stmt->bindParam(':subtotal', $subtotal);

            // Eksekusi query
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Tangkap error dan tampilkan pesan error
        echo "Error: " . $e->getMessage();
    }
}


function getDefaultValue($value, $default = 0) {
    return isset($value) ? $value : $default;
}

// Get invoices due within 7 days
function getDueSoonInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.custom_email = c.email
                           WHERE i.invoice_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                           AND i.status = 'open'
                           ORDER BY i.invoice_due_date ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function dbConnect() {
    $host = 'localhost';
    $db = 'invoice';
    $user = 'root';
    $pass = 'root';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Could not connect to the database $db :" . $e->getMessage());
    }
}

// Get invoices that are due date
function getDueDateInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.custom_email = c.email
                           WHERE i.invoice_due_date < CURDATE() 
                           AND i.status = 'open'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getUpcomingInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.custom_email = c.email
                           WHERE i.invoice_due_date >= CURDATE() 
                           AND i.status = 'open'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Function to get invoices that have expired (end of rental period)
function getExpiredRentals() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.custom_email = c.email
                           WHERE i.invoice_due_date <= CURDATE() 
                           AND i.status = 'paid'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get paid invoices
function getPaidInvoices() {
    $pdo = dbConnect();
    $stmt = $pdo->prepare("SELECT i.*, c.name 
                           FROM invoices i 
                           JOIN customers c ON i.invoice = c.invoice
                           WHERE i.status = 'paid'
                           ORDER BY i.invoice_due_date ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initial invoice number
function getInvoiceId() {
    global $mysqli; // Menggunakan variabel global

    $result = $mysqli->query("SELECT MAX(id) FROM invoices");
    $row = $result->fetch_row();
    $id = $row[0];

    // Mengubah string ke integer, jika tidak ada invoice maka id menjadi 1
    return intval($id) + 1;
}


// populate product dropdown for invoice creation
function popProductsList() {

	// Connect to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// the query
	$query = "SELECT * FROM products ORDER BY product_name ASC";

	// mysqli select query
	$results = $mysqli->query($query);

	if($results) {
		echo '<select class="form-control item-select">';
		while($row = $results->fetch_assoc()) {

		    print '<option value="'.$row['product_price'].'">'.$row["product_name"].' - '.$row["product_desc"].'</option>';
		}
		echo '</select>';

	} else {

		echo "<p>There are no products, please add a product.</p>";

	}

	// Frees the memory associated with a result
	$results->free();

	// close connection 
	$mysqli->close();

}

// populate product dropdown for invoice creation
function popCustomersList() {

	// Connect to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// the query
	$query = "SELECT * FROM store_customers ORDER BY name ASC";

	// mysqli select query
	$results = $mysqli->query($query);

	if($results) {

		print '<table class="table table-striped table-bordered" id="data-table"><thead><tr>

				<th><h4>Name</h4></th>
				<th><h4>Email</h4></th>
				<th><h4>Phone</h4></th>
				<th><h4>Action</h4></th>

			  </tr></thead><tbody>';

		while($row = $results->fetch_assoc()) {

		    print '
			    <tr>
					<td>'.$row["name"].'</td>
				    <td>'.$row["email"].'</td>
				    <td>'.$row["phone"].'</td>
				    <td><a href="#" class="btn btn-primary btn-xs customer-select" data-customer-name="'.$row['name'].'" data-customer-email="'.$row['email'].'" data-customer-phone="'.$row['phone'].'" data-customer-address-1="'.$row['address_1'].'" data-customer-address_2="'.$row['address_2'].'" data-customer-town="'.$row['town'].'" data-customer-county="'.$row['county'].'" data-customer-postcode="'.$row['postcode'].'" data-customer-name-ship="'.$row['name_ship'].'" data-customer-address-1-ship="'.$row['address_1_ship'].'" data-customer-address-2-ship="'.$row['address_2_ship'].'" data-customer-town-ship="'.$row['town_ship'].'" data-customer-county-ship="'.$row['county_ship'].'" data-customer-postcode-ship="'.$row['postcode_ship'].'">Select</a></td>
			    </tr>
		    ';
		}

		print '</tr></tbody></table>';

	} else {

		echo "<p>There are no customers to display.</p>";

	}

	// Frees the memory associated with a result
	$results->free();

	// close connection 
	$mysqli->close();


}
function getAllGpsPackages() {
    try {
        $pdo = dbConnect(); // Pastikan fungsi dbConnect() mengembalikan koneksi ke database
        $stmt = $pdo->prepare("SELECT * FROM gps_packages"); // Ambil semua data dari tabel gps_packages
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan data sebagai array asosiatif
    } catch (PDOException $e) {
        throw new Exception("Error fetching GPS packages: " . $e->getMessage());
    }
}
function getAllProducts() {
    try {
        $pdo = dbConnect(); // Pastikan fungsi dbConnect() mengembalikan koneksi ke database
        $stmt = $pdo->prepare("SELECT * FROM products"); // Ambil semua data dari tabel gps_packages
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengembalikan data sebagai array asosiatif
    } catch (PDOException $e) {
        throw new Exception("Error fetching Products : " . $e->getMessage());
    }
}


function getPackages() {
    // Connect to the database
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    
    // Output any connection error
    if ($mysqli->connect_error) {
        die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
    }

    // Fetch packages
    $query = "SELECT * FROM gps_packages"; // Adjust the table name as necessary
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Package Name</th><th>Description</th><th>Price</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['package_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['package_desc']) . '</td>';
            echo '<td>' . htmlspecialchars($row['package_price']) . '</td>';
            echo '<td>
                    <a href="package-edit.php?id=' . $row['package_id'] . '" class="btn btn-warning">Edit</a>
                    <button class="btn btn-danger delete-package-btn" data-id="' . $row['package_id'] . '" data-toggle="modal" data-target="#delete_package_modal">Delete</button>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No GPS packages found.</p>';
    }

    // Close the connection
    $mysqli->close();
}
// get products list
function getProducts() {

	// Connect to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// the query
	$query = "SELECT * FROM products ORDER BY product_name ASC";

	// mysqli select query
	$results = $mysqli->query($query);

	if($results) {

		print '<table class="table table-striped table-bordered" id="data-table"><thead><tr>

				<th><h4>Product</h4></th>
				<th><h4>Description</h4></th>
				<th><h4>Imei</h4></th>
				<th><h4>GPS Type</h4></th>
				<th><h4>Action</h4></th>

			  </tr></thead><tbody>';

		while($row = $results->fetch_assoc()) {

		    print '
			    <tr>
					<td>'.$row["product_name"].'</td>
				    <td>'.$row["product_desc"].'</td>
				    <td>'.$row["imei"].'</td>
				    <td>'.$row["gps_type"].'</td>
				    <td><a href="product-edit.php?id='.$row["product_id"].'" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a> <a data-product-id="'.$row['product_id'].'" class="btn btn-danger btn-xs delete-product"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></td>
			    </tr>
		    ';
		}

		print '</tr></tbody></table>';

	} else {

		echo "<p>There are no products to display.</p>";

	}

	// Frees the memory associated with a result
	$results->free();

	// close connection 
	$mysqli->close();
}

// get user list
function getUsers() {

	// Connect to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// the query
	$query = "SELECT * FROM users ORDER BY username ASC";

	// mysqli select query
	$results = $mysqli->query($query);

	if($results) {

		print '<table class="table table-striped table-bordered" id="data-table"><thead><tr>

				<th><h4>Name</h4></th>
				<th><h4>Username</h4></th>
				<th><h4>Email</h4></th>
				<th><h4>Phone</h4></th>
				<th><h4>Action</h4></th>

			  </tr></thead><tbody>';

		while($row = $results->fetch_assoc()) {

		    print '
			    <tr>
			    	<td>'.$row['name'].'</td>
					<td>'.$row["username"].'</td>
				    <td>'.$row["email"].'</td>
				    <td>'.$row["phone"].'</td>
				    <td><a href="user-edit.php?id='.$row["id"].'" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a> <a data-user-id="'.$row['id'].'" class="btn btn-danger btn-xs delete-user"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></td>
			    </tr>
		    ';
		}

		print '</tr></tbody></table>';

	} else {

		echo "<p>There are no users to display.</p>";

	}

	// Frees the memory associated with a result
	$results->free();

	// close connection 
	$mysqli->close();
}

// get user list
function getCustomers() {

	// Connect to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// the query
	$query = "SELECT * FROM store_customers ORDER BY name ASC";

	// mysqli select query
	$results = $mysqli->query($query);

	if($results) {

		print '<table class="table table-striped table-bordered" id="data-table"><thead><tr>

				<th><h4>Name</h4></th>
				<th><h4>Email</h4></th>
				<th><h4>Phone</h4></th>
				<th><h4>Action</h4></th>

			  </tr></thead><tbody>';

		while($row = $results->fetch_assoc()) {

		    print '
			    <tr>
					<td>'.$row["name"].'</td>
				    <td>'.$row["email"].'</td>
				    <td>'.$row["phone"].'</td>
				    <td><a href="customer-edit.php?id='.$row["id"].'" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a> <a data-customer-id="'.$row['id'].'" class="btn btn-danger btn-xs delete-customer"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></td>
			    </tr>
		    ';
		}

		print '</tr></tbody></table>';

	} else {

		echo "<p>There are no customers to display.</p>";

	}

	// Frees the memory associated with a result
	$results->free();

	// close connection 
	$mysqli->close();
}

?>

