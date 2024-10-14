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

<!-- Confirmation Modal for Deletion -->
<div id="confirm" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete GPS Package</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this GPS package?</p>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
		<button type="button" data-dismiss="modal" class="btn">Cancel</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
	include('footer.php');
?>
