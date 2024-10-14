<?php
/******************************************************************************* 
* Invoice System                                            					* 
*                                                                               * 
* Version: 1.0                                                      	        * 
* Author:  Abhishek Raj                                   						* 
*******************************************************************************/ 

include('header.php'); 
?>

<h2>Add Product</h2>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
    <a href="#" class="close" data-dismiss="alert">&times;</a>
    <div class="message"></div>
</div>
						
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading semi-rounded">
                <h4>Product Information</h4>
            </div>
            <div class="panel-body form-group form-group-sm semi-rounded">
                <form method="post" id="add_product">
                    <input type="hidden" name="action" value="add_product">

                    <div class="row">
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="product_name" placeholder="Enter product name" required>
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="product_desc" placeholder="Enter product description" required>
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="imei" placeholder="Enter IMEI" required>
                        </div>
                    </div>

                    <div class="row margin-top">
                        <div class="col-xs-4">
                            <input type="text" class="form-control required" name="gps_type" placeholder="Enter GPS Type" required>
                        </div>
                    </div>                    

                    <div class="row">
                        <div class="col-xs-12 margin-top btn-group">
                            <input type="submit" id="action_add_product" class="btn btn-success custom-btn float-right" value="Add Product" data-loading-text="Adding...">
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
