<?php
session_start();
ini_set('display_errors', 1);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$brcode = $_SESSION["ceg_brcode"];
$position = $_SESSION['positn'];

$DB = new classes\Database;

$DB->query('SELECT DISTINCT brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE empid=? ORDER BY brloc'); 
$DB->execute([$empid]);
$rslstbranchname = $DB->resultset();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Billing Materials - Posting</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/datepicker/dpicker.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.css">
    <link rel="stylesheet" href="vendor/material-icons/icons.css">
    <link rel="stylesheet" href="vendor/jquery/jquery-confirm.css">

</head>

<body class="bg-light mb-5">

    <?php include('menu-start.php'); ?>

    <div class="container-fluid">

        <!-- START TITLE -->
        <div class="row mt-2 mb-2">
            <div class="col-lg-12">
                <h3>Billing Materials - Posting</h3>
            </div>
        </div>
        <!-- END TITLE -->

        <div class="row">
            <div class="col-lg-12">

                <div class="card shadow">
                    <div class="card-body">

                        <form name="disform" id="disform" method="POST">
                        <input type="hidden" id="transdata" name="transdata" value="" />
                        <input type="hidden" id="status" name="status" value="0" />
                            
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="branch">Branch Name</label>
                                        <input list="lstbranchname" class="form-control req" id="branchname" name="branchname" value="" />
                                        <datalist id="lstbranchname">
                                        <?php
                                        foreach ($rslstbranchname as $row){
                                            $xbrcode = trim($row->brcode);
                                            $xbrloc = trim($row->brloc);
                                            echo "<option value='$xbrloc' label='$xbrcode'></option>";
                                            $today = date("Y-m-d", strtotime($row->today));
                                        }
                                        ?>
                                        </datalist>
                                    </div>
                                </div> 
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transno">Branch Code</label>
                                        <input type="text" class="form-control req" id="brcode" name="brcode" value="" readonly />
                                    </div>
                                </div>                                

                                <div class="col-lg-12">&nbsp;</div>

                                <div class="col-lg-12">
                                    <div class="card mb-3">

                                        <div class="card-header bg-dark text-light"><i class="fa fa-file"></i> <strong>DETAILS</strong></div>

                                        <div class="card-body">

                                            <div class="row">
                                                <div class="col-lg-12 mt-3">
                                                    <div class="cards">
                                                        <div class="cards-body">
                                                            <div class="responsive-table">

                                                                <table class="table table-striped table-sm">
                                                                    <thead class="bg-light text-secondary">
                                                                        <tr>
                                                                            <th class="text-left">Transaction No.</th>
                                                                            <th class="text-left">Transaction Date</th>
                                                                            <th class="text-left">Project Name</th>
                                                                            <th class="text-left">Remarks</th>
                                                                            <th class="text-left">&nbsp;</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="listofitem">
                                                                    
                                                                    </tbody>
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>                                               
                                            </div>
                                        
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-lg-4 offset-lg-9 mt-4">
                                    <button type="button" class="btn btn-link" id="btnApprove" onclick="validate_posting(1)"><i class="fa fa-check"></i> Approve</button>
                                    <button type="button" class="btn btn-link" id="btnDisapprove" onclick="validate_posting(2)"><i class="fa fa-times"></i> Disapprove</button>
                                </div>

                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>

    <?php include('menu-end.php'); ?>    

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/jquery/jquery-confirm.js"></script>
    <script src="vendor/datepicker/dpicker.js"></script>
    <script text="text/javascript" src="main/js/ceg_billing_materials.js"></script>
    <script text="text/javascript" src="main/js/menu.js"></script>
    <script type='text/javascript'>
    $(function() {

        $("#branchname").blur(function(e) {
            clearform();
            $("#brcode").val(""); 	
            var thisval = ($(this).val()).toUpperCase();
            
            $('#lstbranchname option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#brcode").val(optlbl);

                    $.post("ceg_ajax.php", {
                        "brcode": optlbl,
                        "proj_id": "",
                        "trans": "getmateriallist" 
                        }, function (str) {
                        //-- load data to list
                        if (str.length > 0) {
                            let arrdetails = JSON.parse(str);
                            let transno = "";
                            let transdate = "";
                            let proj_name = "";
                            let remarks = "";

                            for (let ix = 0; ix < arrdetails.length; ix++) {
                                transno = arrdetails[ix]["Transno"];
                                transdate = arrdetails[ix]["Transdate"];
                                proj_name = arrdetails[ix]["proj_name"];
                                remarks = arrdetails[ix]["Remarks"];

                                //-- Insert records to array
                                if ((arr.filter((item) => item.transno == transno)).length <= 0) {
                                    arr.push({
                                        "transno": transno,
                                        "transdate": transdate,
                                        "proj_name": proj_name,
                                        "remarks": remarks
                                    });
                                }
                            }

                            materials_posting();
                        } else {
                            $.alert({
                                title: 'Notice',
                                icon: 'fa fa-exclamation-triangle',
                                content: "No Record Found!",
                                type: 'blue',
                                theme: "modern",
                                typeAnimated: true,
                                buttons: {
                                    close: function () {}
                                }
                            });
                            return;
                        }
                    });
                }
            });            
        });

    });
    </script>

</body>

</html>