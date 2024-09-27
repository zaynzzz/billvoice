<?php
/*******************************************************************************
* Md Invoice 
* Billing Management System                                             *
*                                                                              *
* Version: 1.0	                                                               *
* Author:  Abhishek Raj                                    				   *
*******************************************************************************/

// Debugging
ini_set('error_reporting', E_ALL);

// DATABASE INFORMATION
define('DATABASE_HOST', getenv('IP'));
define('DATABASE_NAME', 'u692140442_invoices');
define('DATABASE_USER', 'u692140442_root');
define('DATABASE_PASS', 'sEf[/#i/r1@');

// DATABASE Local
// define('DATABASE_HOST', getenv('IP'));
// define('DATABASE_NAME', 'invoice');
// define('DATABASE_USER', 'root');
// define('DATABASE_PASS', '');

// COMPANY INFORMATION (Sesuaikan dengan informasi perusahaan di Indonesia)
define('COMPANY_LOGO', 'images/logo.png');
define('COMPANY_LOGO_WIDTH', 'auto');
define('COMPANY_LOGO_HEIGHT', '40');
define('COMPANY_NAME','PT. Indo Tech Solutions');
define('COMPANY_ADDRESS_1','Jl. Jendral Sudirman No. 45');
define('COMPANY_ADDRESS_2','Jakarta Selatan');
define('COMPANY_ADDRESS_3','DKI Jakarta, 12345');
define('COMPANY_COUNTY','Indonesia');
define('COMPANY_POSTCODE','12345');

define('COMPANY_NUMBER','Nomor Perusahaan: 01.123.456.7-891.000'); // Nomor NPWP perusahaan
define('COMPANY_VAT', 'Nomor NPWP: 01.123.456.7-891.000'); // Nomor NPWP perusahaan

// EMAIL DETAILS
define('EMAIL_FROM', 'admin@indotechsolutions.co.id'); // Email address invoice emails will be sent from
define('EMAIL_NAME', 'PT. Indo Tech Solutions'); // Email from address
define('EMAIL_SUBJECT', 'Faktur Pajak'); // Invoice email subject
define('EMAIL_BODY_INVOICE', 'Berikut ini adalah faktur pajak Anda.'); // Invoice email body
define('EMAIL_BODY_QUOTE', 'Berikut ini adalah penawaran harga.'); // Quote email body
define('EMAIL_BODY_RECEIPT', 'Berikut ini adalah tanda terima pembayaran.'); // Receipt email body

// OTHER SETTINGS
define('INVOICE_PREFIX', 'BVIndo'); // Prefix at start of invoice - leave empty '' for no prefix
define('INVOICE_INITIAL_VALUE', ''); // Initial invoice order number (start of increment)
define('INVOICE_THEME', '#222222'); // Theme colour, this sets a colour theme for the PDF generated invoice
define('TIMEZONE', 'Asia/Jakarta'); // Timezone
define('DATE_FORMAT', 'DD/MM/YYYY'); // Format tanggal yang umum di Indonesia
define('CURRENCY', 'Rp'); // Simbol mata uang Rupiah
define('ENABLE_VAT', true); // Aktifkan pajak PPN
define('VAT_INCLUDED', false); // Apakah PPN termasuk dalam harga atau tidak
define('VAT_RATE', '10'); // Persentase PPN (10% di Indonesia)

define('PAYMENT_DETAILS', 'Zairul Antasya Zein<br>Bank BCA<br>No. Rekening: 2040546807'); // Informasi pembayaran
define('FOOTER_NOTE', 'https://www.maqoli.com');

// CONNECT TO THE DATABASE
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// Cek koneksi
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

?>
