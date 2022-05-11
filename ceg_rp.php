<?php
session_start();
ini_set('display_errors', 1);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$mybrcode = $_SESSION["ceg_brcode"];
$position = $_SESSION['positn'];
$brcode = $_GET["brcode"];
$transno = $_GET["transno"];

$DB = new classes\Database;

$DB->query('SELECT TOP (1) brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE brcode=?'); 
$DB->execute([$brcode]);
$rs = $DB->getrow();
$brloc = utf8_decode(strtoupper(trim($rs[0]["brloc"])));
$today = $rs[0]["today"];

$DB->query('SELECT DISTINCT proj_id, proj_name FROM tbl_proj_profile ORDER BY proj_name');
$DB->execute([]);
$rslstprojectname = $DB->resultset();

$DB->query('SELECT itemcode, descrip, buum, brum FROM lib_items ORDER BY descrip');
$DB->execute([]);
$rslstitems = $DB->resultset();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Request to Purchase</title>
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
                <h3>Request to Purchase</h3>
            </div>
        </div>
        <!-- END TITLE -->

        <div class="row">
            <div class="col-lg-12">

                <div class="card shadow">
                    <div class="card-body">

                        <form name="disform" id="disform" method="POST">
                        <input type="hidden" id="transdata" name="transdata" value="" />
                            
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="branch">Branch Name</label>
                                        <input list="lstbranchname" class="form-control req" id="branchname" name="branchname" value="<?php echo $brloc; ?>" readonly />
                                    </div>
                                </div> 
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transno">Branch Code</label>
                                        <input type="text" class="form-control req" id="brcode" name="brcode" value="<?php echo $brcode; ?>" readonly />
                                    </div>
                                </div>                                
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transno">Transaction Number</label>
                                        <input type="number" class="form-control" id="transno" name="transno" value="" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transdate">Transaction Date</label>
                                        <input type="date" class="form-control req" id="transdate" name="transdate" value="<?php echo $today; ?>" readonly />
                                        <input type="hidden" id="today" name="today" value="<?php echo $today; ?>" />
                                    </div>
                                </div>

                                <div class="col-lg-12">&nbsp;</div>

                                <div class="col-lg-12">
                                    <div class="card mb-3">

                                        <div class="card-header bg-dark text-light"><i class="fa fa-file"></i> <strong>DETAILS</strong></div>

                                        <div class="card-body">

                                        
                                            <div class="row">        
                                                <div class="col-md-6">
                                                    <div class="form-label-group">
                                                        <label for="proj_name">Project Name</label>
                                                        <input list="lstprojname" class="form-control req" id="proj_name" name="proj_name" value="" />
                                                        <datalist id="lstprojname"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="proj_id">Project ID</label>
                                                        <input type="text" class="form-control req" id="proj_id" name="proj_id" placeholder="" value="" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-9 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="remarks">Remarks</label>
                                                        <input type="text" class="form-control" id="remarks" name="remarks" value="" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="cards mt-3">
                                                    <div class="cards-body">
                                                        <div class="responsive-table">

                                                            <table class="table table-striped table-sm">
                                                                <thead class="bg-light text-secondary">
                                                                    <tr>
                                                                        <th><button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#materialsModal"><i class="fa fa-plus"></i></button></th>
                                                                        <th class="text-center">Item Code</th>
                                                                        <th class="text-center">Item Desciption</th>
                                                                        <th class="text-center">Units</th>
                                                                        <th class="text-center">Quantity</th>
                                                                        <!-- <th class="text-center">Notes</th> -->
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

                                <div class="col-lg-12">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-label-group">
                                                        <label for="prepby">Prepared By</label>
                                                        <input list="lstprep" class="form-control req" id="prepby" name="prepby" value="<?php echo $username; ?>" />
                                                        <input type="hidden" id="prepbypos" name="prepbypos" value="<?php echo $position; ?>" />
                                                        <datalist id="lstprep">
                                                            <option value='<?php echo $username; ?>' label='<?php echo $empid; ?>'>
                                                        </datalist>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-lg-3 offset-lg-9 mt-4">
                                    <button type="button" class="btn btn-link" id="btnSave" onclick="validate()"><i class="fa fa-check"></i> Save</button>
                                    <button type="button" class="btn btn-link" id="btnPrint" onclick="reprint()"><i class="fa fa-print"></i> Print</button>
                                </div>

                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Modal Materials -->
    <div class="modal fade" id="materialsModal" tabindex="-1" role="dialog" aria-labelledby="materialsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-frame" role="document" style="width: 700px; overflow-y: auto">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="materialsModalTitle">MATERIALS ENTRY</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-label-group">
                                <label for="itemdescrip">Item Description</label>
                                <input list="lstitems" class="form-control" id="itemdescrip" name="itemdescrip" value="" autofocus />
                                <datalist id="lstitems"></datalist>
                            </div>
                        </div>                              
                    </div>

                    <div class="row">
                        <div class="col-lg-3 mt-3">
                            <div class="form-label-group">
                                <label for="itemcode">Item Code</label>
                                <input type="text" class="form-control" id="itemcode" name="itemcode" value="" readonly tabindex="-1"/>
                            </div>                
                        </div>    
                        <div class="col-lg-3 mt-3">
                            <div class="form-label-group">
                                <label for="units">Units</label>
                                <select name="units" id="units" class="form-control">
                                    <option value="">--</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 mt-3">
                            <div class="form-label-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value=""/>
                            </div>
                        </div>  
                        <!-- <div class="col-lg-9 mt-3">
                            <div class="form-label-group">
                                <label for="notes">Notes</label>
                                <input type="text" class="form-control" id="notes" name="notes" value="" />
                            </div>
                        </div>  -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" id="btnAdd" name="btnAdd" onclick="InsertItem()"><i class="fa fa-plus"></i> Add</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('menu-end.php'); ?>

    <datalist id="lstprojname1"></datalist>

    <datalist id="lstitems1">
    <?php
    foreach ($rslstitems as $row){
        $xitemcode = trim($row->itemcode);
        $xdescrip = trim($row->descrip);
        $xunit = trim($row->buum)."^".trim($row->brum);
        echo "<option value='$xdescrip' label='$xitemcode' unit='$xunit'></option>";
    }
    ?>
    </datalist>
    

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
                        "trans": "getprojectnamelist" 
                        }, function (str) {
                        //-- load data to list
                        if (str.length > 0) {
                            let arrdetails = JSON.parse(str);
                            let proj_id = "";
                            let proj_name = "";
                            let options = "";

                            for (let ix = 0; ix < arrdetails.length; ix++) {                            
                                proj_id = arrdetails[ix]["proj_id"];
                                proj_name = arrdetails[ix]["proj_name"];

                                options += '<option value="' + proj_name + '" label="' + proj_id + '"></option>';
                            }

                            document.getElementById('lstprojname').innerHTML = options;
                            document.getElementById('lstprojname1').innerHTML = options;
                        }
                    });
                }
            });            
        });

        $("#transno").change(function(e) {
            clearform();
        });

        $("#proj_name").keyup(function(e) {;
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstprojname').html('');
            $('#lstprojname1 option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstprojname').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#proj_name").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#proj_id").val("");

            $('#lstprojname option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#proj_name").val(optval); 
                    $("#proj_id").val(optlbl);
                }
            });            
        });

        $("#itemdescrip").keyup(function(e) {;
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstitems').html('');
            $('#lstitems1 option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstitems').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\" unit=\"' + $(this).attr('unit') + '\">');
                    discnt++;
                }
            });
        });

        $("#itemdescrip").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#itemcode").val("");
            $("#units").val("");

            $('#lstitems option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                var optunit = ($(this).attr("unit")).toUpperCase();
                if (optval==thisval) { 
                    $("#itemdescrip").val(optval);
                    $("#itemcode").val(optlbl);
                    loadunits(optunit);
                }
            });            
        });

    });

    function loadunits(str){
        document.getElementById('units').innerHTML = '';
        var list = document.getElementById('units');
        
        if (str.length>0) {
            var arritem = str.split("^");
            for (var ix=0; ix<arritem.length; ix++) {
                if (arritem[ix]!="") {
                    var xunit = arritem[ix];                   
                    var option = document.createElement('option');
                    option.value = xunit;
                    option.label = xunit;

                    $(list).each(function() {
                        var selection = $(this).val();
                        if(selection!=xunit) list.appendChild(option);
                    });
                }                
            }
        }
    }
    </script>

</body>

</html>