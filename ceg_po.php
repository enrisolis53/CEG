<?php
session_start();
ini_set('display_errors', 0);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$mybrcode = $_SESSION["ceg_brcode"];
$position = $_SESSION['positn'];
$brcode = $_GET["brcode"];
$transno = $_GET["transno"];
$rpno = $_GET["rpno"];

$DB = new classes\Database;

$DB->query('SELECT TOP (1) brcode, brloc, getdate() AS today FROM lib_access_accounts WHERE brcode=?'); 
$DB->execute([$brcode]);
$rs = $DB->getrow();
$brloc = utf8_decode(strtoupper(trim($rs[0]["brloc"])));
$transdate = date("Y-m-d", strtotime($rs[0]["today"]));

$DB->query('SELECT DISTINCT brcode, brloc FROM lib_access_accounts WHERE empid=? ORDER BY brloc'); 
$DB->execute([$empid]);
$rslstbranchname = $DB->resultset();

$DB->query("SELECT b.ItemCode, descrip, UOM, (Qty-Delivered) as bal FROM tbl_RpHead AS a, tbl_RpBody AS b, lib_items AS c WHERE (a.BrCode=b.BrCode AND a.RpNumber=b.RpNumber AND b.ItemCode=c.itemcode) AND a.brcode=? AND a.RpNumber=?");
$DB->execute([$brcode, $rpno]);
$rs = $DB->resultset();

$DB->query('SELECT itemcode, descrip, buum, brum FROM lib_items ORDER BY descrip');
$DB->execute([]);
$rslstitems = $DB->resultset();

$DB->query("SELECT (RTRIM(Fname)+' '+LEFT(Mname,1)+' '+RTRIM(Lname)) AS cname, EmpID, Positn FROM PIS.dbo.tEmployee WHERE eRem='Active' AND (Positn NOT LIKE '%DRIVER%' AND Positn NOT LIKE '%JANITOR%' AND Positn NOT LIKE '%MESSENGER%') ORDER BY Lname,Fname"); 
$DB->execute([]);
$rslstemp = $DB->resultset();

$DB->query("SELECT RegName FROM TFINANCE.dbo.tTaxPayer WHERE Individual=? AND status=? ORDER BY RegName"); 
$DB->execute([0, 0]);
$rslsttaxpayer = $DB->resultset();

$arr = [];

foreach ($rs as $row) {
    $itemcode = utf8_decode(trim($row->ItemCode));
    $descrip = utf8_decode(trim($row->descrip));
    $uom = utf8_decode(trim($row->UOM));
    $bal = floatval($row->bal);

    if($bal>0){
        $arr[] = array(
            "itemcode"=>$itemcode,
            "descrip"=>$descrip, 
            "uom"=>$uom, 						
            "bal"=>$bal,
            "ucost"=>"",
            "tcost"=>""
        );
    }  
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Purchase Order</title>
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
                <h3>Purchase Order</h3>
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
                                                        <label for="deliverto">Deliver To</label>
                                                        <input list="lstbranch" class="form-control req" id="deliverto" name="deliverto" value="<?php echo $deliverto; ?>" />
                                                        <datalist id="lstbranch">
                                                        <?php
                                                        foreach ($rslstbranchname as $row){
                                                            $xbrcode = trim($row->brcode);
                                                            $xbrloc = trim($row->brloc);
                                                            echo "<option value='$xbrloc' label='$xbrcode'></option>";
                                                        }
                                                        ?>
                                                        </datalist>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="rpno">Request To Purchase Number</label>
                                                        <input type="text" class="form-control req" id="rpno" name="rpno" placeholder="" value="<?php echo $rpno; ?>" tabindex="-1" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-label-group">
                                                        <label for="cpdno">Central Purchasing Number</label>
                                                        <input type="number" class="form-control" id="cpdno" name="cpdno" placeholder="" value="<?php echo $cpdno; ?>" />
                                                    </div>
                                                </div>  
                                            </div>

                                            <div class="row">    
                                                <div class="col-md-6 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="supplier">Supplier</label>
                                                        <input list="lstsupplier" class="form-control req" id="supplier" name="supplier" value="<?php echo $supplier; ?>" />
                                                        <datalist id="lstsupplier"></datalist>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="contno">Contact Details</label>
                                                        <input type="text" class="form-control req" id="contno" name="contno" value="<?php echo $contno; ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="terms">Terms</label>
                                                        <input type="text" class="form-control req" id="terms" name="terms" value="<?php echo $terms; ?>" />
                                                    </div>
                                                </div>
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
                                                <div class="col-md-2 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="discount">Discount</label>
                                                        <input type="number" class="form-control" id="discount" name="discount" value="<?php echo $discount; ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="downpayment">Down Payment</label>
                                                        <input type="number" class="form-control" id="downpayment" name="downpayment" value="<?php echo $downpayment; ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="addpayment">Add Payment Option</label>
                                                        <Select class="form-control" id="addpayment" name="addpayment" value="<?php echo $addpayment; ?>">
                                                        <option value=""> -- </option>
                                                        <option value="Delivery Charge">Delivery Charge</option>
                                                        <option value="Installation Fee">Installation Fee</option>
                                                        <option value="Sercice Charge">Service Charge</option>
                                                        <option value="Other Charges">Other Charges</option>
                                                        </Select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="addpaymentamt">Add Payment Amount</label>
                                                        <input type="number" class="form-control" id="addpaymentamt" name="addpaymentamt" value="<?php echo $addpaymentamt; ?>" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mt-2">
                                                    <div class="form-label-group">
                                                        <label for="totalpo">Total Purchase Amount</label>
                                                        <input type="number" class="form-control" id="totalpo" name="totalpo" value="<?php echo $totalpo; ?>" tabindex="-1" readonly />
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
                                                        <input list="lstempname" class="form-control req" id="prepby" name="prepby" value="<?php echo strtoupper($username); ?>" readonly tabindex='-1' />
                                                        <input type="text" class="form-control" id="prepbypos" name="prepbypos" value="<?php echo strtoupper($position); ?>" readonly readonly tabindex='-1' />
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
                                                <div class="col-lg-4">
                                                    <div class="form-label-group">
                                                        <label for="approvedby">Approved By</label>
                                                        <input list="lstapprovedby" class="form-control req" id="approvedby" name="approvedby" value="" />
                                                        <input type="text" class="form-control" id="approvebypos" name="approvebypos" value="" readonly readonly tabindex='-1' />
                                                        <datalist id="lstapprovedby"></datalist>
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

    <datalist id="lsttaxpayer">
    <?php
    foreach ($rslsttaxpayer as $row){
        $RegName = utf8_decode(strtoupper(trim($row->RegName)));
        echo "<option value='$RegName'></option>";
    }
    ?>
    </datalist>

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
    <script text="text/javascript" src="main/js/ceg_po.js"></script>
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