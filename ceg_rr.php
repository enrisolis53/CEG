<?php
session_start();
ini_set('display_errors', 0);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$mybrcode = $_SESSION["ceg_brcode"];
$position = $_SESSION['positn'];
$brcode = $_GET["brcode"];
$transno = $_GET["transno"];
$pono = $_GET["pono"];

$DB = new classes\Database;

$DB->query('SELECT TOP (1) brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE brcode=?'); 
$DB->execute([$brcode]);
$rs = $DB->getrow();
$brloc = utf8_decode(strtoupper(trim($rs[0]["brloc"])));
$transdate = date("Y-m-d", strtotime($rs[0]["today"]));

$DB->query('SELECT DISTINCT brcode, brloc FROM lib_access_accounts WHERE empid=? ORDER BY brloc'); 
$DB->execute([$empid]);
$rslstbranchname = $DB->resultset();

$DB->query("SELECT a.BrCode, a.CpdNumber, a.RpNumber, a.Supplier, a.discount, a.downpayment, a.add_payment_type, a.add_payment_amt, b.ItemCode, descrip, UOM, (Qty-Delivered) as bal, UnitCost, TotalCost FROM tbl_PoHead AS a, tbl_PoBody AS b, lib_items AS c WHERE (a.BrCode=b.BrCode AND a.PoNumber=b.PoNumber AND b.ItemCode=c.itemcode) AND a.RpBrCode=? AND a.PoNumber=?");
$DB->execute([$brcode, $pono]);
$rs = $DB->resultset();

$DB->query('SELECT itemcode, descrip, buum, brum FROM lib_items ORDER BY descrip');
$DB->execute([]);
$rslstitems = $DB->resultset();

$DB->query("SELECT (RTRIM(Fname)+' '+LEFT(Mname,1)+' '+RTRIM(Lname)) AS cname, EmpID, Positn FROM PIS.dbo.tEmployee WHERE eRem='Active' AND (Positn NOT LIKE '%DRIVER%' AND Positn NOT LIKE '%JANITOR%' AND Positn NOT LIKE '%MESSENGER%') ORDER BY Lname,Fname"); 
$DB->execute([]);
$rslstemp = $DB->resultset();

$arr = [];

foreach ($rs as $row) {
    $PoBrCode = trim($row->BrCode);
    $cpdno = trim($row->CpdNumber);
    $rpno = trim($row->RpNumber);
    $supplier = utf8_decode(trim($row->Supplier));
    $discount = floatval($row->discount);
    $downpayment = floatval($row->downpayment);
    $addpayment = trim($row->add_payment_type);
    $addpaymentamt = floatval($row->add_payment_amt);
    $itemcode = trim($row->ItemCode);
    $descrip = utf8_decode(trim($row->descrip));
    $uom = utf8_decode(trim($row->UOM));
    $bal = floatval($row->bal);
    $ucost = floatval($row->UnitCost);
    $tcost = floatval($row->TotalCost);

    if($bal>0){
        $arr[] = array(
            "itemcode"=>$itemcode,
            "descrip"=>$descrip, 
            "uom"=>$uom, 						
            "bal"=>$bal,
            "ucost"=>$ucost,
            "icost"=>"",
            "tcost"=>$tcost
        );
    }  
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Receiving Report</title>
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
                <h3>Receiving Report</h3>
            </div>
        </div>
        <!-- END TITLE -->

        <div class="row">
            <div class="col-lg-12">

                <div class="card shadow">
                    <div class="card-body">

                        <form name="disform" id="disform" method="POST">
                        <input type="hidden" id="pobrcode" name="pobrcode" value="<?php echo $PoBrCode; ?>" />
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
                                        <input type="number" class="form-control" id="transno" name="transno" value="" placeholder="Auto Generated" tabindex="-1" readonly />
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
                                                        <label for="supplier">Supplier</label>
                                                        <input text="text" class="form-control req" id="supplier" name="supplier" value="<?php echo $supplier; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="drno">Delivery Receipt Number</label>
                                                        <input type="text" class="form-control" id="drno" name="drno" value="" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="sino">Sales Invoice Number</label>
                                                        <input type="text" class="form-control" id="sino" name="sino" value="" />
                                                    </div>
                                                </div>
                                            </div>     
                                            
                                            <div class="row">
                                                <div class="col-md-3 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="rpno">Request To Purchase Number</label>
                                                        <input type="text" class="form-control req" id="rpno" name="rpno" placeholder="" value="<?php echo $rpno; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="pono">Purchase Order Number</label>
                                                        <input text="text" class="form-control req" id="pono" name="pono" placeholder="" value="<?php echo $pono; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                <div class="form-label-group">
                                                        <label for="cpdno">Central Purchasing Number</label>
                                                        <input type="number" class="form-control" id="cpdno" name="cpdno" placeholder="" value="<?php echo $cpdno; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <?php 
                                                if($discount!=""){
                                                    echo '
                                                    <div class="col-md-3 mt-2">
                                                        <div class="form-label-group">
                                                        <label for="discount">Discount</label>
                                                        <input type="number" class="form-control" id="discount" name="discount" value="'.$discount.'" tabindex="-1" readonly style="text-align:right;" />
                                                        </div>
                                                    </div>';
                                                } 
                                                ?>
                                                <?php 
                                                if($downpayment!=""){
                                                    echo '
                                                    <div class="col-md-3 mt-2">
                                                        <div class="form-label-group">
                                                        <label for="downpayment">Down Payment</label>
                                                        <input type="number" class="form-control" id="downpayment" name="downpayment" value="'.$downpayment.'" tabindex="-1" readonly style="text-align:right;"  />
                                                        </div>
                                                    </div>';
                                                } 
                                                ?>
                                                <?php 
                                                if($addpayment!=""){
                                                    echo '
                                                    <div class="col-md-3 mt-2">
                                                        <div class="form-label-group">
                                                            <label for="addpaymentamt">'.$addpayment.'</label>
                                                            <input type="number" class="form-control" id="addpaymentamt" name="addpaymentamt" value="'.$addpaymentamt.'" tabindex="-1" readonly style="text-align:right;" />
                                                        </div>
                                                    </div>';
                                                } 
                                                ?>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12 mt-2">
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
                                                                            <th class="text-center">Unit Cost</th>
                                                                            <th class="text-center">Incidental Cost</th>
                                                                            <th class="text-center">Total Cost</th>
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

                                            <div class="row">
                                                <div class="col-md-2 offset-lg-10 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="totalrr">Total Amount</label>
                                                        <input type="number" class="form-control" id="totalrr" name="totalrr" value="<?php echo $totalrr; ?>" tabindex="-1" readonly style="text-align:right;" />
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
                                                        <label for="receivedby">Received By</label>
                                                        <input list="lstempname" class="form-control req" id="receivedby" name="receivedby" value="<?php echo strtoupper($username); ?>" readonly tabindex='-1' />
                                                        <input type="text" class="form-control" id="receivedbypos" name="receivedbypos" value="<?php echo strtoupper($position); ?>" readonly readonly tabindex='-1' />
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-label-group">
                                                        <label for="notedby">Noted by</label> <!-- <label for="notedby">Noted by</label> -->
                                                        <input list="lstnotedby" class="form-control req" id="notedby" name="notedby" value="" />
                                                        <input type="text" class="form-control" id="notedbypos" name="notedbypos" value="" readonly readonly tabindex='-1' />
                                                        <datalist id="lstnotedby"></datalist>
                                                    </div>
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
                        <div class="col-lg-3 mt-3">
                            <div class="form-label-group">
                                <label for="ucost">Unit Cost</label>
                                <input type="number" class="form-control" id="ucost" name="ucost" value=""/>
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
    <script text="text/javascript" src="main/js/ceg_rr.js"></script>
    <script text="text/javascript" src="main/js/menu.js"></script>
    <script type='text/javascript'>

    window.onload = function() {
        let arrdata = '<?php echo json_encode($arr); ?>';
        loaddata(arrdata);
    }

    $(function() {

        $("#supplier").keyup(function(e) {
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstsupplier').html('');
            $('#lsttaxpayer option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 30) {
                        Debug.Break();
                    }
                    $('#lstsupplier').append('<option value=\"' + $(this).val() + '\">');
                    discnt++;
                }
            });
        });

        $("#discount").change(function(e) {
            computeTotalCost(); 
        });

        $("#downpayment").change(function(e) {
            computeTotalCost(); 
        });

        $("#addpayment").change(function(e) {
            if($(this).val()!="") { $("#addpaymentamt").attr("readonly", false); }
            else { $("#addpaymentamt").val(""); $("#addpaymentamt").attr("readonly", true); computeTotalCost(); }
        });

        $("#addpaymentamt").change(function(e) {
            computeTotalCost(); 
        });

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