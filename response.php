<?php


include_once('includes/config.php');
require_once('class.phpmailer.php');

// show PHP errors
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

$action = isset($_POST['action']) ? $_POST['action'] : "";
 

include_once('includes/config.php');  // Ensure this file contains your database connection details
require_once('class.phpmailer.php');

// show PHP errors
ini_set('display_errors', 1);

// Output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

// Check if action is 'email_invoice'
$action = isset($_POST['action']) ? $_POST['action'] : "";

 
if ($action == 'email_invoice') {
    // Capture data from the request
    $fileId = $_POST['id'];  // Invoice ID
    $emailId = $_POST['email'];  // Recipient email address

    // Fetch invoice data from your database
    $query = "SELECT * FROM invoices WHERE invoice = '$fileId'";
    $result = mysqli_query($mysqli, $query);
    $invoice = mysqli_fetch_assoc($result);

    // Fetch customer details
    $query_customer = "SELECT * FROM customers WHERE invoice = '$fileId'";
    $result_customer = mysqli_query($mysqli, $query_customer);
    $customer = mysqli_fetch_assoc($result_customer);

    // Fetch invoice items
    $query_items = "SELECT * FROM invoice_items WHERE invoice = '$fileId'";
    $result_items = mysqli_query($mysqli, $query_items);

    // Prepare items for image
    $items = [];
    while ($item = mysqli_fetch_assoc($result_items)) {
        $items[] = $item;
    }

    // Create invoice image
    $imagePath = createInvoiceImage($fileId, $customer, $invoice, $items);

    // Create a new PHPMailer instance
    $mail = new PHPMailer();

    // Setup sender and recipient
    $mail->SetFrom('antzyn@datass.uno', 'Antzyn');
    $mail->AddAddress($emailId);  // Recipient email

    // Set email subject
    $mail->Subject = 'Your Invoice #' . $fileId;

    // Body of the email
    $mail->Body = "Please find attached the image of your invoice.";

    // Attach the invoice image
    $mail->AddAttachment($imagePath);

    // Send the email
    if ($mail->Send()) {
        echo json_encode(['status' => 'Success', 'message' => 'Email sent successfully!']);
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Mailer error: ' . $mail->ErrorInfo]);
    }
}

 function createInvoiceImage($fileId, $customer, $invoice, $items) {
    // Tentukan ukuran gambar
    $width = 600;
    $height = 400 + (count($items) * 20);  // Adjust height based on the number of items
    
    // Membuat gambar kosong
    $image = imagecreatetruecolor($width, $height);

    // Warna
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    // Background putih
    imagefilledrectangle($image, 0, 0, $width, $height, $white);

    // Path ke font (pastikan font ada di direktori yang benar)
    $font = __DIR__ . '/fonts/poppins.ttf';  // Pastikan Anda mengunggah font ini

    // Menulis teks pada gambar
    $y = 20;  // Mulai dari posisi y=20

    imagettftext($image, 14, 0, 10, $y, $black, $font, "Invoice #$fileId");
    $y += 30;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Name: " . $customer['name']);
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Email: " . $customer['email']);
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Address: " . $customer['address_1']);
    $y += 40;

    imagettftext($image, 12, 0, 10, $y, $black, $font, "Invoice Details");
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Date: " . $invoice['invoice_date']);
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Due Date: " . $invoice['invoice_due_date']);
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Subtotal: Rp." . number_format($invoice['subtotal'], 2));
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Shipping: Rp." . number_format($invoice['shipping'], 2));
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Discount: Rp." . number_format($invoice['discount'], 2));
    $y += 20;
    imagettftext($image, 12, 0, 10, $y, $black, $font, "Total: Rp." . number_format(($invoice['subtotal'] + $invoice['shipping'] - $invoice['discount']), 2));
    $y += 40;

    imagettftext($image, 12, 0, 10, $y, $black, $font, "Items:");
    $y += 20;

    foreach ($items as $item) {
        imagettftext($image, 12, 0, 10, $y, $black, $font, $item['product'] . " - Rp." . number_format($item['price'], 2) . " x " . $item['qty'] . " = Rp." . number_format($item['subtotal'], 2));
        $y += 20;
    }

    // Pastikan direktori 'invoice_images' ada
    $imageDir = __DIR__ . '/invoice_images/';
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);  // Buat folder jika tidak ada
    }

    // Simpan gambar ke dalam file
    $filePath = $imageDir . "invoice_$fileId.png";
    imagepng($image, $filePath);

    // Hancurkan gambar untuk mengosongkan memori
    imagedestroy($image);

    return $filePath;  // Return the path to the saved image
}
 

// download invoice csv sheet
if ($action == 'download_csv'){

	header("Content-type: text/csv"); 

	// output any connection error
	if ($mysqli->connect_error) {
		die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
	}
 
    $file_name = 'invoice-export-'.date('d-m-Y').'.csv';   // file name
    $file_path = 'downloads/'.$file_name; // file path

	$file = fopen($file_path, "w"); // open a file in write mode
    chmod($file_path, 0777);    // set the file permission

    $query_table_columns_data = "SELECT * 
									FROM invoices i
									JOIN customers c
									ON c.invoice = i.invoice
									WHERE i.invoice = c.invoice
									ORDER BY i.invoice";

    if ($result_column_data = mysqli_query($mysqli, $query_table_columns_data)) {

    	// fetch table fields data
        while ($column_data = $result_column_data->fetch_row()) {

            $table_column_data = array();
            foreach($column_data as $data) {
                $table_column_data[] = $data;
            }

            // Format array as CSV and write to file pointer
            fputcsv($file, $table_column_data, ",", '"');
        }

	}

    //if saving success
    if ($result_column_data = mysqli_query($mysqli, $query_table_columns_data)) {
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'CSV has been generated and is available in the /downloads folder for future reference, you can download by <a href="/downloads/'.$file_name.'">clicking here</a>.'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

 
    // close file pointer
    fclose($file);

    $mysqli->close();

}

// Create customer
if ($action == 'create_customer'){

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	$query = "INSERT INTO store_customers (
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?
				);
			";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param(
		'ssssssssssssss',
		$customer_name,$customer_email,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,
		$customer_phone,$customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship);

	if($stmt->execute()){
		//if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message' => 'Customer has been created successfully!'
		));
	} else {
		// if unable to create invoice
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'There has been an error, please try again.'
			// debug
			//'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
		));
	}

	//close database connection
	$mysqli->close();
}

// Create invoice
if ($action == 'create_invoice'){

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// invoice details
	$invoice_number = $_POST['invoice_id']; // invoice number
	$custom_email = $_POST['custom_email']; // invoice custom email body
	$invoice_date = $_POST['invoice_date']; // invoice date
	$custom_email = $_POST['custom_email']; // custom invoice email
	$invoice_due_date = $_POST['invoice_due_date']; // invoice due date
	$invoice_subtotal = $_POST['invoice_subtotal']; // invoice sub-total
	$invoice_shipping = $_POST['invoice_shipping']; // invoice shipping amount
	$invoice_discount = $_POST['invoice_discount']; // invoice discount
	$invoice_vat = $_POST['invoice_vat']; // invoice vat
	$invoice_total = $_POST['invoice_total']; // invoice total
	$invoice_notes = $_POST['invoice_notes']; // Invoice notes
	$invoice_type = $_POST['invoice_type']; // Invoice type
	$invoice_status = $_POST['invoice_status']; // Invoice status

	// insert invoice into database
	$query = "INSERT INTO invoices (
					invoice,
					custom_email,
					invoice_date, 
					invoice_due_date, 
					subtotal, 
					shipping, 
					discount, 
					vat, 
					total,
					notes,
					invoice_type,
					status
				) VALUES (
				  	'".$invoice_number."',
				  	'".$custom_email."',
				  	'".$invoice_date."',
				  	'".$invoice_due_date."',
				  	'".$invoice_subtotal."',
				  	'".$invoice_shipping."',
				  	'".$invoice_discount."',
				  	'".$invoice_vat."',
				  	'".$invoice_total."',
				  	'".$invoice_notes."',
				  	'".$invoice_type."',
				  	'".$invoice_status."'
			    );
			";
	// insert customer details into database
	$query .= "INSERT INTO customers (
					invoice,
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					'".$invoice_number."',
					'".$customer_name."',
					'".$customer_email."',
					'".$customer_address_1."',
					'".$customer_address_2."',
					'".$customer_town."',
					'".$customer_county."',
					'".$customer_postcode."',
					'".$customer_phone."',
					'".$customer_name_ship."',
					'".$customer_address_1_ship."',
					'".$customer_address_2_ship."',
					'".$customer_town_ship."',
					'".$customer_county_ship."',
					'".$customer_postcode_ship."'
				);
			";

	// invoice product items
	foreach($_POST['invoice_product'] as $key => $value) {
	    $item_product = $value;
	    // $item_description = $_POST['invoice_product_desc'][$key];
	    $item_qty = $_POST['invoice_product_qty'][$key];
	    $item_price = $_POST['invoice_product_price'][$key];
	    $item_discount = $_POST['invoice_product_discount'][$key];
	    $item_subtotal = $_POST['invoice_product_sub'][$key];

	    // insert invoice items into database
		$query .= "INSERT INTO invoice_items (
				invoice,
				product,
				qty,
				price,
				discount,
				subtotal
			) VALUES (
				'".$invoice_number."',
				'".$item_product."',
				'".$item_qty."',
				'".$item_price."',
				'".$item_discount."',
				'".$item_subtotal."'
			);
		";

	}

	header('Content-Type: application/json');

	// execute the query
	if($mysqli -> multi_query($query)){
		//if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message' => 'Invoice has been created successfully!'
		));

		//Set default date timezone
		date_default_timezone_set(TIMEZONE);
		//Include Invoicr class
		include('invoice.php');
		//Create a new instance
		$invoice = new invoicr('P', 'mm', 'A4');
		//Set number formatting
		$invoice->setNumberFormat('.',',');
		//Set your logo
		$invoice->setLogo(COMPANY_LOGO,COMPANY_LOGO_WIDTH,COMPANY_LOGO_HEIGHT);
		//Set theme color
		$invoice->setColor(INVOICE_THEME);
		//Set type
		$invoice->setType($invoice_type);
		//Set reference
		$invoice->setReference($invoice_number);
		//Set date
		$invoice->setDate($invoice_date);
		//Set due date
		$invoice->setDue($invoice_due_date);
		//Set from
		$invoice->setFrom(array(COMPANY_NAME,COMPANY_ADDRESS_1,COMPANY_ADDRESS_2,COMPANY_COUNTY,COMPANY_POSTCODE,COMPANY_NUMBER,COMPANY_VAT));
		//Set to
		$invoice->setTo(array($customer_name,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,"Phone: ".$customer_phone));
		//Ship to
		$invoice->shipTo(array($customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,''));
		//Add items
		// invoice product items
		foreach($_POST['invoice_product'] as $key => $value) {
		    $item_product = $value;
		    // $item_description = $_POST['invoice_product_desc'][$key];
		    $item_qty = $_POST['invoice_product_qty'][$key];
		    $item_price = $_POST['invoice_product_price'][$key];
		    $item_discount = $_POST['invoice_product_discount'][$key];
		    $item_subtotal = $_POST['invoice_product_sub'][$key];

		   	if(ENABLE_VAT == true) {
		   		$item_vat = (VAT_RATE / 100) * $item_subtotal;
		   	}

		    $invoice->addItem($item_product,'',$item_qty,$item_vat,$item_price,$item_discount,$item_subtotal);
		}
		//Add totals
		$invoice->addTotal("Total",$invoice_subtotal);
		if(!empty($invoice_discount)) {
			$invoice->addTotal("Discount",$invoice_discount);
		}
		if(!empty($invoice_shipping)) {
			$invoice->addTotal("Delivery",$invoice_shipping);
		}
		if(ENABLE_VAT == true) {
			$invoice->addTotal("TAX/VAT ".VAT_RATE."%",$invoice_vat);
		}
		$invoice->addTotal("Total Due",$invoice_total,true);
		//Add Badge
		$invoice->addBadge($invoice_status);
		// Customer notes:
		if(!empty($invoice_notes)) {
			$invoice->addTitle("Cusatomer Notes");
			$invoice->addParagraph($invoice_notes);
		}
		//Add Title
		$invoice->addTitle("Payment information");
		//Add Paragraph
		$invoice->addParagraph(PAYMENT_DETAILS);
		//Set footer note
		$invoice->setFooternote(FOOTER_NOTE);
		//Render the PDF
		$invoice->render('invoices/'.$invoice_number.'.pdf','F');
	} else {
		// if unable to create invoice
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'There has been an error, please try again.'
			// debug
			//'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
		));
	}

	//close database connection
	$mysqli->close();

}

// Adding new product
if($action == 'delete_invoice') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM invoices WHERE invoice = ".$id.";";
	$query .= "DELETE FROM customers WHERE invoice = ".$id.";";
	$query .= "DELETE FROM invoice_items WHERE invoice = ".$id.";";

	unlink('invoices/'.$id.'.pdf');

	if($mysqli -> multi_query($query)) {
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Adding new product
if($action == 'update_customer') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$getID = $_POST['id']; // id

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// the query
	$query = "UPDATE store_customers SET
				name = ?,
				email = ?,
				address_1 = ?,
				address_2 = ?,
				town = ?,
				county = ?,
				postcode = ?,
				phone = ?,

				name_ship = ?,
				address_1_ship = ?,
				address_2_ship = ?,
				town_ship = ?,
				county_ship = ?,
				postcode_ship = ?

				WHERE id = ?

			";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param(
		'sssssssssssssss',
		$customer_name,$customer_email,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,
		$customer_phone,$customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,$getID);

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Customer has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}
if ($action == 'update_product') {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $product_desc = $_POST['product_desc'];
    $imei = isset($_POST['imei']) ? $_POST['imei'] : null; // Optional
    $gps_type = isset($_POST['gps_type']) ? $_POST['gps_type'] : null; // Optional

    // Update query without product_price
    $query = "UPDATE products SET 
                product_name = ?, 
                product_desc = ?, 
                imei = ?, 
                gps_type = ? 
              WHERE product_id = ?";

    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    // Bind parameters, without product_price
    $stmt->bind_param('ssssi', $product_name, $product_desc, $imei, $gps_type, $id);

    if ($stmt->execute()) {
        echo json_encode(array(
            'status' => 'Success',
            'message' => 'Product has been updated successfully!'
        ));
    } else {
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'There has been an error, please try again.<pre>' . $mysqli->error . '</pre><pre>' . $query . '</pre>'
        ));
    }

    // Close statement and connection
    $stmt->close();
    $mysqli->close();
}


// Adding new product
if($action == 'update_invoice') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["update_id"];

	// the query
	$query = "DELETE FROM invoices WHERE invoice = ".$id.";";
	$query .= "DELETE FROM customers WHERE invoice = ".$id.";";
	$query .= "DELETE FROM invoice_items WHERE invoice = ".$id.";";

	unlink('invoices/'.$id.'.pdf');

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// invoice details
	$invoice_number = $_POST['invoice_id']; // invoice number
	$custom_email = $_POST['custom_email']; // invoice custom email body
	$invoice_date = $_POST['invoice_date']; // invoice date
	$invoice_due_date = $_POST['invoice_due_date']; // invoice due date
	$invoice_subtotal = $_POST['invoice_subtotal']; // invoice sub-total
	$invoice_shipping = $_POST['invoice_shipping']; // invoice shipping amount
	$invoice_discount = $_POST['invoice_discount']; // invoice discount
	$invoice_vat = $_POST['invoice_vat']; // invoice vat
	$invoice_total = $_POST['invoice_total']; // invoice total
	$invoice_notes = $_POST['invoice_notes']; // Invoice notes
	$invoice_type = $_POST['invoice_type']; // Invoice type
	$invoice_status = $_POST['invoice_status']; // Invoice status

	// insert invoice into database
	$query .= "INSERT INTO invoices (
					invoice, 
					invoice_date, 
					invoice_due_date, 
					subtotal, 
					shipping, 
					discount, 
					vat, 
					total,
					notes,
					invoice_type,
					status
				) VALUES (
				  	'".$invoice_number."',
				  	'".$invoice_date."',
				  	'".$invoice_due_date."',
				  	'".$invoice_subtotal."',
				  	'".$invoice_shipping."',
				  	'".$invoice_discount."',
				  	'".$invoice_vat."',
				  	'".$invoice_total."',
				  	'".$invoice_notes."',
				  	'".$invoice_type."',
				  	'".$invoice_status."'
			    );
			";
	// insert customer details into database
	$query .= "INSERT INTO customers (
					invoice,
					custom_email,
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					'".$invoice_number."',
					'".$custom_email."',
					'".$customer_name."',
					'".$customer_email."',
					'".$customer_address_1."',
					'".$customer_address_2."',
					'".$customer_town."',
					'".$customer_county."',
					'".$customer_postcode."',
					'".$customer_phone."',
					'".$customer_name_ship."',
					'".$customer_address_1_ship."',
					'".$customer_address_2_ship."',
					'".$customer_town_ship."',
					'".$customer_county_ship."',
					'".$customer_postcode_ship."'
				);
			";

	// invoice product items
	foreach($_POST['invoice_product'] as $key => $value) {
	    $item_product = $value;
	    // $item_description = $_POST['invoice_product_desc'][$key];
	    $item_qty = $_POST['invoice_product_qty'][$key];
	    $item_price = $_POST['invoice_product_price'][$key];
	    $item_discount = $_POST['invoice_product_discount'][$key];
	    $item_subtotal = $_POST['invoice_product_sub'][$key];

	    // insert invoice items into database
		$query .= "INSERT INTO invoice_items (
				invoice,
				product,
				qty,
				price,
				discount,
				subtotal
			) VALUES (
				'".$invoice_number."',
				'".$item_product."',
				'".$item_qty."',
				'".$item_price."',
				'".$item_discount."',
				'".$item_subtotal."'
			);
		";

	}

	header('Content-Type: application/json');

	if($mysqli -> multi_query($query)) {
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been updated successfully!'
		));

		//Set default date timezone
		date_default_timezone_set(TIMEZONE);
		//Include Invoicr class
		include('invoice.php');
		//Create a new instance
		$invoice = new invoicr("A4",CURRENCY,"en");
		//Set number formatting
		$invoice->setNumberFormat('.',',');
		//Set your logo
		$invoice->setLogo(COMPANY_LOGO,COMPANY_LOGO_WIDTH,COMPANY_LOGO_HEIGHT);
		//Set theme color
		$invoice->setColor(INVOICE_THEME);
		//Set type
		$invoice->setType("Invoice");
		//Set reference
		$invoice->setReference($invoice_number);
		//Set date
		$invoice->setDate($invoice_date);
		//Set due date
		$invoice->setDue($invoice_due_date);
		//Set from
		$invoice->setFrom(array(COMPANY_NAME,COMPANY_ADDRESS_1,COMPANY_ADDRESS_2,COMPANY_COUNTY,COMPANY_POSTCODE,COMPANY_NUMBER,COMPANY_VAT));
		//Set to
		$invoice->setTo(array($customer_name,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,"Phone: ".$customer_phone));
		//Ship to
		$invoice->shipTo(array($customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,''));
		//Add items
		// invoice product items
		foreach($_POST['invoice_product'] as $key => $value) {
		    $item_product = $value;
		    // $item_description = $_POST['invoice_product_desc'][$key];
		    $item_qty = $_POST['invoice_product_qty'][$key];
		    $item_price = $_POST['invoice_product_price'][$key];
		    $item_discount = $_POST['invoice_product_discount'][$key];
		    $item_subtotal = $_POST['invoice_product_sub'][$key];

		   	if(ENABLE_VAT == true) {
		   		$item_vat = (VAT_RATE / 100) * $item_subtotal;
		   	}

		    $invoice->addItem($item_product,'',$item_qty,$item_vat,$item_price,$item_discount,$item_subtotal);
		}
		//Add totals
		$invoice->addTotal("Total",$invoice_subtotal);
		if(!empty($invoice_discount)) {
			$invoice->addTotal("Discount",$invoice_discount);
		}
		if(!empty($invoice_shipping)) {
			$invoice->addTotal("Delivery",$invoice_shipping);
		}
		if(ENABLE_VAT == true) {
			$invoice->addTotal("TAX/VAT ".VAT_RATE."%",$invoice_vat);
		}
		$invoice->addTotal("Total Due",$invoice_total,true);
		//Add Badge
		$invoice->addBadge($invoice_status);
		// Customer notes:
		if(!empty($invoice_notes)) {
			$invoice->addTitle("Cusatomer Notes");
			$invoice->addParagraph($invoice_notes);
		}
		//Add Title
		$invoice->addTitle("Payment information");
		//Add Paragraph
		$invoice->addParagraph(PAYMENT_DETAILS);
		//Set footer note
		$invoice->setFooternote(FOOTER_NOTE);
		//Render the PDF
		$invoice->render('invoices/'.$invoice_number.'.pdf','F');

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Adding new product
if($action == 'delete_product') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM products WHERE product_id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Login to system
if($action == 'login') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	session_start();

    extract($_POST);

    $username = mysqli_real_escape_string($mysqli,$_POST['username']);
    $pass_encrypt = md5(mysqli_real_escape_string($mysqli,$_POST['password']));

    $query = "SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'";

    $results = mysqli_query($mysqli,$query) or die (mysqli_error());
    $count = mysqli_num_rows($results);

    if($count!="") {
		$row = $results->fetch_assoc();

		$_SESSION['login_username'] = $row['username'];

		// processing remember me option and setting cookie with long expiry date
		if (isset($_POST['remember'])) {	
			session_set_cookie_params('604800'); //one week (value in seconds)
			session_regenerate_id(true);
		}  
		
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Login was a success! Transfering you to the system now, hold tight!'
		));
    } else {
    	echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'Login incorrect, does not exist or simply a problem! Try again!'
	    ));
    }
}

// Adding new product// Adding new product
if ($action == 'add_product') {

    $product_name = $_POST['product_name'];
    $product_desc = $_POST['product_desc'];
    $imei = $_POST['imei']; // New field for IMEI
    $gps_type = $_POST['gps_type']; // New field for GPS type

    // Our insert query
    $query = "INSERT INTO products
                (
                    product_name,
                    product_desc,
                    imei,
                    gps_type
                )
                VALUES (
                    ?, 
                    ?, 
                    ?, 
                    ?
                );";

    header('Content-Type: application/json');

    /* Prepare statement */
    $stmt = $mysqli->prepare($query);
    if ($stmt === false) {
        trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    /* Bind parameters. Types: s = string, i = integer, d = double, b = blob */
    $stmt->bind_param('ssss', $product_name, $product_desc, $imei, $gps_type); // Changed to 'ssss'

    if ($stmt->execute()) {
        // If saving success
        echo json_encode(array(
            'status' => 'Success',
            'message' => 'Product has been added successfully!'
        ));
    } else {
        // If unable to create new record
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'There has been an error, please try again.<pre>' . $mysqli->error . '</pre><pre>' . $query . '</pre>'
        ));
    }

    // Close database connection
    $mysqli->close();
}

// Adding new GPS rental package
if($action == 'add_package') {
    $package_name = $_POST['package_name'];
    $package_desc = $_POST['package_desc'];
    $package_price = $_POST['package_price'];

    // Query untuk memasukkan paket penyewaan GPS
    $query  = "INSERT INTO gps_packages
                (
                    package_name,
                    package_desc,
                    package_price
                )
                VALUES (
                    ?, 
                    ?,
                    ?
                );
              ";

    header('Content-Type: application/json');

    /* Prepare statement */
    $stmt = $mysqli->prepare($query);
    if($stmt === false) {
      trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('sss', $package_name, $package_desc, $package_price);

    if($stmt->execute()){
        // Jika berhasil menyimpan
        echo json_encode(array(
            'status' => 'Success',
            'message'=> 'GPS rental package has been added successfully!'
        ));

    } else {
        // Jika gagal menyimpan
        echo json_encode(array(
            'status' => 'Error',
            'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
        ));
    }

    // Menutup koneksi database
    $stmt->close();
    $mysqli->close();
}


// Adding new user
if($action == 'add_user') {

	$user_name = $_POST['name'];
	$user_username = $_POST['username'];
	$user_email = $_POST['email'];
	$user_phone = $_POST['phone'];
	$user_password = $_POST['password'];

	//our insert query query
	$query  = "INSERT INTO users
				(
					name,
					username,
					email,
					phone,
					password
				)
				VALUES (
					?,
					?, 
                	?,
                	?,
                	?
                );
              ";

    header('Content-Type: application/json');

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	$user_password = md5($user_password);
	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('sssss',$user_name,$user_username,$user_email,$user_phone,$user_password);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been added successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
}

// Update product
if($action == 'update_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// user information
	$getID = $_POST['id']; // id
	$name = $_POST['name']; // name
	$username = $_POST['username']; // username
	$email = $_POST['email']; // email
	$phone = $_POST['phone']; // phone
	$password = $_POST['password']; // password

	if($password == ''){
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?
				 WHERE id = ?
				";
	} else {
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?,
					password =?
				 WHERE id = ?
				";
	}

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	if($password == ''){
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'sssss',
			$name,$username,$email,$phone,$getID
		);
	} else {
		$password = md5($password);
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'ssssss',
			$name,$username,$email,$phone,$password,$getID
		);
	}

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}

// Delete User
if($action == 'delete_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM users WHERE id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Delete User
if($action == 'delete_customer') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM store_customers WHERE id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Customer has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

?>