<?php
	//check login
	include("session.php");
?>


<!DOCTYPE html>

<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Billvoice</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
 
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/AdminLTE.css">
 
  <link rel="stylesheet" href="css/skin-purple.css">
  
  	<!-- JS -->
	<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="js/moment.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.js"></script>
	<script src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>
	<script src="js/bootstrap.datetime.js"></script>
	<script src="js/bootstrap.password.js"></script>
	<script src="js/scripts.js"></script>
	<script>

function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
  </script>
	<!-- AdminLTE App -->
	<script src="js/app.min.js"></script>

	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap.datetimepicker.css">
	<link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.css">
	<link rel="stylesheet" href="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/styles.css">

</head>

<body class="hold-transition skin-purple sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">

     <!--Logo -->
     <a href="#" class="logo">
    <!-- Mini logo for sidebar (50x50 pixels) -->
    <span class="logo-mini">
    <img src="/images/billvoice.png" class="user-image" alt="BillVoice Logo">
    </span>
    <!-- Logo for regular state and mobile devices -->
    <span class="logo-lg">
        <img src="/images/billvoice.png" class="user-image" alt="BillVoice Logo">
        <b>Bill</b>Voice
    </span>
</a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
         
          <!-- User Account Menu -->
          <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!-- The user image in the navbar-->
              <img src="https://icons.veryicon.com/png/o/miscellaneous/two-color-icon-library/user-286.png" class="user-image" alt="User Image">
              <!-- hidden-xs hides the username on small devices so only the image appears. -->
              <span class="hidden-xs"><?php echo $_SESSION['login_username'];?></span>
            </a>
            <ul class="dropdown-menu">
             <!-- Drop down list-->
              <li><a href="logout.php" class="btn btn-default btn-flat">Sign out</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  
  
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">


      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
      <li class="nav-item header text-uppercase fw-bold py-2 px-3 bg-light text-center">
          <h5>Dashboard</h5> 
        </li>
        <!-- Menu 1 -->
         <li class="treeview">
          <a href="#"><i class="fa fa-file-text-o"></i> <span>Invoices</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="invoice-create.php"><i class="fa fa-plus"></i>Create Invoice</a></li>
            <li><a href="invoice-list.php"><i class="fa fa-cog"></i>Manage Invoices</a></li>
            <li><a href="#" class="download-csv"><i class="fa fa-download"></i>Download CSV</a></li>
          </ul>
        </li>
        <!-- Menu 2 -->
        <li class="treeview">
          <a href="#"><i class="fa fa-shopping-basket"></i><span>Items</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="product-add.php"><i class="fa fa-plus"></i>Add Item</a></li>
            <li><a href="product-list.php"><i class="fa fa-cog"></i>Manage Item</a></li>
          </ul>
        </li>
        <!-- Menu 3 -->
        <li class="treeview">
          <a href="#"><i class="fa fa-shopping-basket"></i><span>Package</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="package-add.php"><i class="fa fa-plus"></i>Add Package</a></li>
            <li><a href="package-list.php"><i class="fa fa-cog"></i>Manage Package</a></li>
          </ul>
        </li>
        <!-- Menu 4 -->
        <li class="treeview">
          <a href="#"><i class="fa fa-users"></i><span>Customers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="customer-add.php"><i class="fa fa-user-plus"></i>Add Customer</a></li>
            <li><a href="customer-list.php"><i class="fa fa-cog"></i>Manage Customers</a></li>
          </ul>
        </li>
        
        <!-- Menu 5 -->
        <li class="treeview">
          <a href="#"><i class="fa fa-user"></i><span>Users</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="user-add.php"><i class="fa fa-plus"></i>Add User</a></li>
            <li><a href="user-list.php"><i class="fa fa-cog"></i>Manage Users</a></li>
          </ul>
        </li>
        
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
   

    <!-- Main content -->
    <section class="content">

      <!-- Your Page Content Here -->


