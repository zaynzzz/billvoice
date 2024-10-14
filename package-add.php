<?php
/*******************************************************************************
* GPS Rental System                                            					*
*                                                                               *
* Version: 1.0                                                      	        *
* Author:  Abhishek Raj                                   						*
*******************************************************************************/

include('header.php');
?>

<h2>Add GPS Rental Package</h2>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<div class="message"></div>
</div>
						
<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading semi-rounded">
				<h4>GPS Package Information</h4>
			</div>
			<div class="panel-body form-group form-group-sm semi-rounded">
				<form method="post" id="add_package">
					<input type="hidden" name="action" value="add_package">

					<div class="row">
						<!-- Package Name -->
						<div class="col-xs-4">
							<input type="text" class="form-control required" name="package_name" placeholder="Enter package name (e.g., Basic, Premium)">
						</div>

						<!-- Package Description -->
						<div class="col-xs-4">
							<input type="text" class="form-control required" name="package_desc" placeholder="Enter package description">
						</div>

						<!-- Package Price -->
						<div class="col-xs-4">
							<div class="input-group">
								<span class="input-group-addon"><?php echo CURRENCY ?></span>
								<input type="text" name="package_price" class="form-control required" placeholder="Enter package price (e.g., Rp.100.000)" aria-describedby="sizing-addon1">
							</div>
						</div>
					</div>

					<div class="row">
						<!-- Submit Button -->
						<div class="col-xs-12 margin-top btn-group">
							<input type="submit" id="action_add_package" class="btn btn-success custom-btn float-right" value="Add Package" data-loading-text="Adding...">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
 <script>
 
	$("#action_add_package").click(function(e) {
		e.preventDefault();  // Cegah form submit default
		actionAddPackage();   // Jalankan fungsi tambah paket
	});
	function actionAddPackage() {

// Validasi form sebelum submit
var errorCounter = validateForm();  // Pastikan form validasi ini ada

if (errorCounter > 0) {
	// Jika ada error, tampilkan pesan peringatan
	$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
	$("#response .message").html("<strong>Error</strong>: It appears you have forgotten to complete something!");
	$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
} else {
	// Hapus error sebelumnya dan kirim form
	$(".required").parent().removeClass("has-error");

	var $btn = $("#action_add_package").button("loading"); // Ubah tombol jadi "loading"

	// Kirim data menggunakan AJAX
	$.ajax({
		url: 'response.php',  // Lokasi PHP yang akan memproses form
		type: 'POST',
		data: $("#add_package").serialize(),  // Serialisasi data form
		dataType: 'json',
		success: function(data) {
			// Tampilkan pesan sukses
			$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
			$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			$btn.button("reset");  // Reset tombol
		},
		error: function(data) {
			// Tampilkan pesan error jika gagal
			$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
			$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			$btn.button("reset");  // Reset tombol
		}
	});
}
}

function validateForm() {
	    // error handling
	    var errorCounter = 0;

	    $(".required").each(function(i, obj) {

	        if($(this).val() === ''){
	            $(this).parent().addClass("has-error");
	            errorCounter++;
	        } else{ 
	            $(this).parent().removeClass("has-error"); 
	        }


	    });

	    return errorCounter;
	}
 </script>
<?php
	include('footer.php');
?>
