<?php
session_start();
ini_set('display_errors', 1);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$brcode = $_SESSION["ceg_brcode"];

$DB = new classes\Database;

$DB->query('SELECT DISTINCT brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE empid=? ORDER BY brloc'); 
$DB->execute([$empid]);
$rslstbranchname = $DB->resultset();

$DB->query('SELECT DISTINCT proj_id, proj_name FROM tbl_proj_profile ORDER BY proj_name');
$DB->execute([]);
$rslstprojectname = $DB->resultset();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Create Project</title>
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
                <h3>Create Project</h3>
            </div>
        </div>
        <!-- END TITLE -->

        <div class="row">
            <div class="col-lg-12">

                <div class="card shadow">
                    <div class="card-body">

                        <form name="disform" id="disform" method="POST" enctype="multipart/form-data">
                            
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
                                                        <input type="text" class="form-control" id="proj_id" name="proj_id" placeholder="" value="" tabindex="-1" readonly />
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row">        

                                                <div class="col-md-3 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="datefrom">Project Date From</label>
                                                        <input type="date" class="form-control req" id="datefrom" name="datefrom" value="" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="dateto">Project Date To</label>
                                                        <input type="date" class="form-control req" id="dateto" name="dateto" value="" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="projcost">Project Cost</label>
                                                        <input type="number" class="form-control req" id="projcost" name="projcost" value="" />
                                                    </div>
                                                </div>                                                                                                   

                                            </div>     
                                            
                                            <div class="row">        

                                                <div class="col-md-6 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="particulars">Particulars</label>
                                                        <input type="text" class="form-control req" id="particulars" name="particulars" value="" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="remarks">Remarks</label>
                                                        <input type="text" class="form-control" id="remarks" name="remarks" value="" />
                                                    </div>
                                                </div>                                                                                                  

                                            </div>    
                                            
                                            <div class="row">

                                                <div class="col-md-6 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="fileToUpload">File to upload</label>
                                                        <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" accept="" />
                                                        <input type="hidden" id="file_uploaded_id" name="file_uploaded_id" value="" />
                                                    </div>
                                                </div>
                                                <!-- <div class="col-md-3 mt-4">
                                                    <div class="form-label-group">
                                                        <br/><button type="button" class="btn btn-link" id="btnExecute"><i class="fa fa-check"></i> Execute</button>
                                                    </div>
                                                </div>                -->

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

    <?php include('menu-end.php'); ?>

    <datalist id="lstprojname1">
    <?php
    foreach ($rslstprojectname as $row){
        $xproj_id = trim($row->proj_id);
        $xproj_name = strtoupper(trim($row->proj_name));
        echo "<option value='$xproj_name' label='$xproj_id'></option>";
    }
    ?>
    </datalist>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/jquery/jquery-confirm.js"></script>
    <script src="vendor/datepicker/dpicker.js"></script>
    <script text="text/javascript" src="main/js/ceg_createproject.js"></script>
    <script text="text/javascript" src="main/js/menu.js"></script>
    <script type='text/javascript'>
    $(function() {

        $("#branchname").change(function(e) {
            clearform();
            $("#brcode").val(""); 	
            var thisval = ($(this).val()).toUpperCase();
            
            $('#lstbranchname option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#brcode").val(optlbl); 
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

    });
    </script>

</body>

</html>