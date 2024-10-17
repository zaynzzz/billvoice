<?php
include('header.php');
include('functions.php');

$getID = $_GET['id'];

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// Output any connection error
if ($mysqli->connect_error) {
    die('Error : (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// The query
$query = "SELECT * FROM gps_packages WHERE package_id = '" . $mysqli->real_escape_string($getID) . "'";

$result = mysqli_query($mysqli, $query);

// mysqli select query
if ($result) {
    // Fetch package details
    if ($row = mysqli_fetch_assoc($result)) {
        $package_name = $row['package_name']; // package name
        $package_desc = $row['package_desc']; // package description
        $package_price = $row['package_price']; // package price
    } else {
        // Handle case where no package is found
        die("No package found with the given ID.");
    }
} else {
    // Handle query error
    die("Query Error: " . mysqli_error($mysqli));
}

/* close connection */
$mysqli->close();
?>

<h1>Edit Package</h1>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>
						
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Editing Package ID (<?php echo $getID; ?>)</h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <form method="post" id="update_package">
                    <input type="hidden" name="action" value="update_package">
                    <input type="hidden" name="package_id" value="<?php echo $getID; ?>">
                    <div class="row">
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="package_name" placeholder="Enter package name" value="<?php echo htmlspecialchars($package_name); ?>">
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="package_desc" placeholder="Enter package description" value="<?php echo htmlspecialchars($package_desc); ?>">
                        </div>
                        <div class="col-xs-4">
                            <div class="input-group">
                                <span class="input-group-addon"><?php echo CURRENCY; ?></span>
                                <input type="text" name="package_price" class="form-control required" placeholder="0.00" aria-describedby="sizing-addon1" value="<?php echo htmlspecialchars($package_price); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 margin-top btn-group">
                            <input type="submit" id="action_update_package" class="btn btn-success float-right custom-btn" value="Update Package" data-loading-text="Updating...">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
