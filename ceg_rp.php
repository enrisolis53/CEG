<?php
session_start();
ini_set('display_errors', 0);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$mybrcode = $_SESSION["ceg_brcode"];
$preparedby = $username;
$preparedpos = $_SESSION['positn'];
$brcode = $_GET["brcode"];
$transno = str_pad(strval($_GET["transno"]), 8, "0", STR_PAD_LEFT);
$bmno = $_GET["bmno"];

$DB = new classes\Database;

$DB->query('SELECT TOP (1) brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE brcode=?'); 
$DB->execute([$brcode]);
$rs = $DB->getrow();
$brloc = utf8_decode(strtoupper(trim($rs[0]["brloc"])));
$transdate = date("Y-m-d", strtotime($rs[0]["today"]));

$DB->query("SELECT a.proj_id, d.proj_name, a.Remarks, b.ItemCode, descrip, UOM, (Qty-Delivered) as bal FROM tbl_billing_head AS a, tbl_billing_body AS b, lib_items AS c, tbl_proj_profile AS d WHERE (a.BrCode=b.BrCode AND a.Transno=b.Transno AND b.ItemCode=c.itemcode AND a.proj_id=d.proj_id) AND a.brcode=? AND a.transno=?");
$DB->execute([$brcode, $bmno]);
$rs = $DB->resultset();

$DB->query('SELECT itemcode, descrip, buum, brum FROM lib_items ORDER BY descrip');
$DB->execute([]);
$rslstitems = $DB->resultset();

$DB->query("SELECT (RTRIM(Fname)+' '+LEFT(Mname,1)+' '+RTRIM(Lname)) AS cname, EmpID, Positn FROM PIS.dbo.tEmployee WHERE eRem='Active' AND (Positn NOT LIKE '%DRIVER%' AND Positn NOT LIKE '%JANITOR%' AND Positn NOT LIKE '%MESSENGER%') ORDER BY Lname,Fname"); 
$DB->execute([]);
$rslstemp = $DB->resultset();

$arr = [];

if(isset($_GET["transno"])){
    $DB->query("SELECT a.RpDate, a.proj_id, d.proj_name, a.BmNo, a.Remarks, a.Preparedby, a.Preparedpos, a.Checkedby, a.Checkedpos, a.Notedby, a.Notedbypos, a.RecAppby, a.RecApppos, a.Approvedby, a.Approvedpos, b.ItemCode, descrip, UOM, Qty FROM tbl_RpHead AS a, tbl_RpBody AS b, lib_items AS c, tbl_proj_profile AS d WHERE (a.BrCode=b.BrCode AND a.RpNumber=b.RpNumber AND b.ItemCode=c.itemcode AND a.proj_id=d.proj_id) AND a.brcode=? AND a.RpNumber=?");
    $DB->execute([$brcode, $transno]);
    $rs = $DB->resultset();

    foreach ($rs as $row) {
        $transdate = $row->RpDate;
        $proj_id = utf8_decode(trim($row->proj_id));
        $proj_name = utf8_decode(trim($row->proj_name));
        $bmno = utf8_decode(trim($row->BmNo));
        $remarks = utf8_decode(trim($row->Remarks));
        $preparedby = utf8_decode(trim($row->Preparedby));
        $preparedpos = utf8_decode(trim($row->Preparedpos));
        $Checkedby = utf8_decode(trim($row->Checkedby));
        $Checkedpos = utf8_decode(trim($row->Checkedpos));
        $Notedby = utf8_decode(trim($row->Notedby));
        $Notedbypos = utf8_decode(trim($row->Notedbypos));
        $RecAppby = utf8_decode(trim($row->RecAppby));
        $RecApppos = utf8_decode(trim($row->RecApppos));
        $Approvedby = utf8_decode(trim($row->Approvedby));
        $Approvedpos = utf8_decode(trim($row->Approvedpos));
        $itemcode = utf8_decode(trim($row->ItemCode));
        $descrip = utf8_decode(trim($row->descrip));
        $uom = utf8_decode(trim($row->UOM));
        $qty = floatval($row->Qty);

        $arr[] = array(
            "itemcode"=>$itemcode,
            "descrip"=>$descrip, 
            "uom"=>$uom, 						
            "bal"=>$qty
        );  
    }
} else {
    foreach ($rs as $row) {
        $proj_name = utf8_decode(trim($row->proj_name));
        $proj_id = $row->proj_id;
        $remarks = utf8_decode(trim($row->Remarks));
        $itemcode = utf8_decode(trim($row->ItemCode));
        $descrip = utf8_decode(trim($row->descrip));
        $uom = utf8_decode(trim($row->UOM));
        $bal = floatval($row->bal);
    
        if($bal>0){
            $arr[] = array(
                "itemcode"=>$itemcode,
                "descrip"=>$descrip, 
                "uom"=>$uom, 						
                "bal"=>$bal
            );
        }  
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Request to Purchase</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="vendor/datepicker/dpicker.min.css"> -->
    <link rel="stylesheet" href="vendor/fontawesome/css/all.css">
    <!-- <link rel="stylesheet" href="vendor/material-icons/icons.css"> -->
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
                                        <input list="lstbranchname" class="form-control req" id="branchname" name="branchname" value="<?php echo $brloc; ?>" tabindex="-1" readonly />
                                    </div>
                                </div> 
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transno">Branch Code</label>
                                        <input type="text" class="form-control req" id="brcode" name="brcode" value="<?php echo $brcode; ?>" tabindex="-1" readonly />
                                    </div>
                                </div>                                
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transno">Transaction Number</label>
                                        <input type="number" class="form-control" id="transno" name="transno" value="<?php echo $transno; ?>" tabindex="-1" readonly />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="transdate">Transaction Date</label>
                                        <input type="date" class="form-control req" id="transdate" name="transdate" value="<?php echo $transdate; ?>" tabindex="-1" readonly />
                                        <input type="hidden" id="today" name="today" value="<?php echo $transdate; ?>" />
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
                                                        <input list="lstprojname" class="form-control req" id="proj_name" name="proj_name" value="<?php echo $proj_name; ?>" tabindex="-1" readonly />
                                                        <datalist id="lstprojname"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="proj_id">Project ID</label>
                                                        <input type="text" class="form-control req" id="proj_id" name="proj_id" placeholder="" value="<?php echo $proj_id; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="bmno">Billing Materials Number</label>
                                                        <input type="text" class="form-control req" id="bmno" name="bmno" placeholder="" value="<?php echo $bmno; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12 mt-3">
                                                    <div class="form-label-group">
                                                        <label for="remarks">Remarks</label>
                                                        <input type="text" class="form-control" id="remarks" name="remarks" value="<?php echo $remarks; ?>" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12 mt-3">
                                                    <div class="cards">
                                                        <div class="cards-body">
                                                            <div class="responsive-table">

                                                                <table class="table table-striped table-sm">
                                                                    <thead class="bg-light text-secondary">
                                                                        <tr>
                                                                            <th class="text-left"><button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#materialsModal"><i class="fa fa-plus"></i></button></th>
                                                                            <th class="text-left">Item Code</th>
                                                                            <th class="text-left">Item Desciption</th>
                                                                            <th class="text-center">Units</th>
                                                                            <th class="text-center">Quantity</th>
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

                                <div class="col-lg-12">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-label-group">
                                                        <label for="prepby">Prepared By</label>
                                                        <input list="lstempname" class="form-control req" id="prepby" name="prepby" value="<?php echo strtoupper($preparedby); ?>" readonly tabindex='-1' />
                                                        <input type="text" class="form-control" id="prepbypos" name="prepbypos" value="<?php echo strtoupper($preparedpos); ?>" readonly readonly tabindex='-1' />
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-label-group">
                                                        <label for="notedby">Requested by</label> <!-- <label for="notedby">Noted by</label> -->
                                                        <input list="lstnotedby" class="form-control req" id="notedby" name="notedby" value="<?php echo strtoupper($Notedby); ?>" />
                                                        <input type="text" class="form-control" id="notedbypos" name="notedbypos" value="<?php echo strtoupper($Notedbypos); ?>" readonly readonly tabindex='-1' />
                                                        <datalist id="lstnotedby"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-label-group">
                                                        <label for="checkedby">Checked & Verified by</label>
                                                        <input list="lstcheckedby" class="form-control" id="checkedby" name="checkedby" value="<?php echo strtoupper($Checkedby); ?>" />
                                                        <input type="text" class="form-control" id="checkedbypos" name="checkedbypos" value="<?php echo strtoupper($Checkedpos); ?>" readonly readonly tabindex='-1' />
                                                        <datalist id="lstcheckedby"></datalist>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-4 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="recommendby">Recommending Approval</label>
                                                        <input list="lstrecommendby" class="form-control req" id="recommendby" name="recommendby" value="<?php echo strtoupper($RecAppby); ?>" />
                                                        <input type="text" class="form-control" id="recommendbypos" name="recommendbypos" value="<?php echo strtoupper($RecApppos); ?>" readonly readonly tabindex='-1' />
                                                        <datalist id="lstrecommendby"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="approvedby">Approved By</label>
                                                        <input list="lstapprovedby" class="form-control req" id="approvedby" name="approvedby" value="<?php echo strtoupper($Approvedby); ?>" />
                                                        <input type="text" class="form-control" id="approvebypos" name="approvebypos" value="<?php echo strtoupper($Approvedpos); ?>" readonly readonly tabindex='-1' />
                                                        <datalist id="lstapprovedby"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 mt-2">
                                                    &nbsp;
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-lg-12 offset-lg-10 mt-4">
                                    <button type="button" class="btn btn-link" id="btnSave" onclick="validate()"><i class="fa fa-check"></i> Save</button>
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" id="btnAdd" name="btnAdd" onclick="InsertItem()"><i class="fa fa-plus"></i> Add</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('menu-end.php'); ?>

    <datalist id="lstempname">
    <?php
    foreach ($rslstemp as $row){
        $EmpID = $row->EmpID;
        $cname = $row->cname;
        $Positn = $row->Positn;
        echo "<option value='$cname' label='$Positn'></option>";
    }
    ?>
    </datalist>

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
    <!-- <script src="vendor/datepicker/dpicker.js"></script> -->
    <script text="text/javascript" src="main/js/ceg_rp.js"></script>
    <script text="text/javascript" src="main/js/menu.js"></script>
    <script type='text/javascript'>

    window.onload = function() {
        let arrdata = '<?php echo json_encode($arr); ?>';
        loaddata(arrdata);
    }

    $(function() {

        $("#notedby").keyup(function(e) {
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstnotedby').html('');
            $('#lstempname option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstnotedby').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#notedby").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#notedbypos").val("");

            $('#lstnotedby option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#notedby").val(optval);
                    $("#notedbypos").val(optlbl);
                }
            });            
        });

        $("#checkedby").keyup(function(e) {
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstcheckedby').html('');
            $('#lstempname option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstcheckedby').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#checkedby").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#checkedbypos").val("");

            $('#lstcheckedby option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#checkedby").val(optval);
                    $("#checkedbypos").val(optlbl);
                }
            });            
        });

        $("#recommendby").keyup(function(e) {
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstrecommendby').html('');
            $('#lstempname option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstrecommendby').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#recommendby").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#recommendbypos").val("");

            $('#lstrecommendby option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#recommendby").val(optval);
                    $("#recommendbypos").val(optlbl);
                }
            });            
        });

        $("#approvedby").keyup(function(e) {
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstapprovedby').html('');
            $('#lstempname option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstapprovedby').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#approvedby").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#approvebypos").val("");

            $('#lstapprovedby option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#approvedby").val(optval);
                    $("#approvebypos").val(optlbl);
                }
            });            
        });

        $("#itemdescrip").keyup(function(e) {
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