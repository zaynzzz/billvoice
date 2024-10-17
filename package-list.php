<?php

include('header.php');
include('functions.php');

?>

<h1>GPS Rental Package List</h1>
<hr>

<div class="row">
	
	<div class="col-xs-12">

		<div id="response" class="alert alert-success" style="display:none;">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<div class="message"></div>
		</div>
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>GPS Package Information</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getPackages(); // Function to fetch and display GPS packages ?>
			</div>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete_package_modal" tabindex="-1" role="dialog" aria-labelledby="deletePackageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePackageModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this package? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirm_delete" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<?php
	include('footer.php');
?>
