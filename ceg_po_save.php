<?php
ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];
$mybrcode = $_SESSION["ceg_brcode"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Saving, Please wait...</b></font>";

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$transno = trim($_POST["transno"]);
if (empty($transno) || trim($transno) == "") { $trans = "new"; } else { $trans = "edit"; }
$transdate = $_POST["transdate"];
$deliverto = utf8_decode(trim($_POST["deliverto"]));
$rpno = trim($_POST["rpno"]);
$cpdno = trim($_POST["cpdno"]);
$supplier = utf8_decode(trim($_POST["supplier"]));
$contno = utf8_decode(trim($_POST["contno"]));
$terms = utf8_decode(trim($_POST["terms"]));
$remarks = utf8_decode(trim($_POST["remarks"]));

$supplierArr = explode("[", $supplier);
$tin = str_replace("]", "", $supplierArr[1]);
$DB->query("SELECT TOP (1) TIN, CONCAT(Address1,' ',Address2) AS address FROM TFINANCE.dbo.tTaxPayer WHERE TIN=?"); 
$DB->execute([$tin]);
$rs = $DB->getrow();
$address = ($rs[0]["address"]=="")?"":utf8_decode(trim($rs[0]["address"]));

$discount = floatval($_POST["discount"]);
$downpayment = floatval($_POST["downpayment"]);
$addpayment = trim($_POST["addpayment"]);
$addpaymentamt = floatval($_POST["addpaymentamt"]);

$prepby = utf8_decode(strtoupper(trim($_POST["prepby"])));
$prepbypos = utf8_decode(strtoupper(trim($_POST["prepbypos"])));
$notedby = utf8_decode(strtoupper(trim($_POST["notedby"])));
$notedbypos = utf8_decode(strtoupper(trim($_POST["notedbypos"])));
$approvedby = utf8_decode(strtoupper(trim($_POST["approvedby"])));
$approvedbypos = utf8_decode(strtoupper(trim($_POST["approvebypos"])));

$arrs = json_decode($_POST["transdata"], true);

$Now = date("m/d/Y H:i");
$encoded = $empid." [".$Now."]";

$DB->query("DELETE FROM tbl_PoBody WHERE BrCode=? AND PoNumber=?");
$DB->execute([$brcode, $transno]);

if ($trans == "new") {
    $DB->query("SELECT MAX(PoNumber) AS maxno FROM tbl_PoHead WHERE BrCode=?");
    $DB->execute([$brcode]);
    $rsmax = $DB->getrow();
    $transno = str_pad(strval($rsmax[0]["maxno"]+1), 8, "0", STR_PAD_LEFT);

    $DB->query("INSERT INTO tbl_PoHead (BrCode, PoNumber, PoDate, CpdNumber, DeliverTo, Supplier, TIN, Address, ContDetails, RpBrCode, RpNumber, Terms, Remarks, discount, downpayment, add_payment_type, add_payment_amt, Preparedby, Preparedpos, Notedby, Notedbypos, Approvedby, Approvedpos, lastupdate) VALUES ('$brcode','$transno','$transdate','$cpdno','$deliverto','$supplier','$tin','$address','$contno','$brcode','$rpno','$terms','$remarks',$discount,$downpayment,'$addpayment',$addpaymentamt,'$prepby','$prepbypos','$notedby','$notedbypos','$approvedby','$approvedbypos','$encoded')");
    $DB->execute([]);
} else {
    $DB->query("UPDATE tbl_PoHead SET CpdNumber='$cpdno', DeliverTo='$deliverto', Supplier='$supplier', TIN='$tin', Address='$address', ContDetails='$contno', RpBrCode='$brcode', RpNumber='$rpno', Terms='$terms', Remarks='$remarks', discount=$discount, downpayment=$downpayment, add_payment_type='$addpayment', add_payment_amt=$addpaymentamt, Preparedby='$prepby', Preparedpos='$prepbypos', Notedby='$notedby', Notedbypos='$notedbypos', Approvedby='$approvedby', Approvedpos='$approvedbypos', Lastupdate='$encoded' WHERE BrCode=? AND PoNumber=?");
    $DB->execute([$brcode, $transno]);
}

foreach ($arrs as $row) {

    $itemcode = trim($row["itemcode"]);
    $units = trim($row["units"]);
    $quantity = $row["quantity"];
    $ucost = $row["ucost"];
    $tcost = $row["tcost"];

    $DB->query("INSERT INTO tbl_PoBody (BrCode, PoNumber, ItemCode, UOM, Qty, UnitCost, TotalCost) VALUES ('$brcode','$transno','$itemcode','$units',$quantity,$ucost,$tcost)");
    $DB->execute([]);

    $DB->query("SELECT SUM(Qty) AS newQty FROM tbl_PoHead INNER JOIN tbl_PoBody ON tbl_PoHead.BrCode = tbl_PoBody.BrCode AND tbl_PoHead.PoNumber = tbl_PoBody.PoNumber WHERE tbl_PoHead.RpBrCode = '$brcode' AND tbl_PoHead.RpNumber = '$rpno' AND ItemCode='$itemcode' AND Cancelled=0");
    $DB->execute([$brcode]);
    $rsnewQty = $DB->getrow();
    $newQty = floatval($rsnewQty[0]["newQty"]);

    $DB->query("UPDATE tbl_RpBody SET Delivered=$newQty WHERE BrCode=? AND RpNumber=? AND ItemCode=?");
    $DB->execute([$brcode, $rpno, $itemcode]);
}

echo "<script type='text/javascript'>window.open('ceg_po_print.php?brcode=$brcode&transno=$transno', 'Purchase Order', 'height=550, width=700');</script>";

header("Refresh:5; url='ceg_po_start.php'"); 
exit();
?>