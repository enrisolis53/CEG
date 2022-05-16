<?php
ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Saving, Please wait...</b></font>";

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$transno = "00000001"; //trim($_POST["transno"]);
if (empty($transno) || trim($transno) == "") { $trans = "new"; } else { $trans = "edit"; }
$transdate = $_POST["transdate"];
$proj_id = $_POST["proj_id"];
$bmno = trim($_POST["bmno"]);
$remarks = utf8_decode(trim($_POST["remarks"]));
$prepby = utf8_decode(strtoupper(trim($_POST["prepby"])));
$prepbypos = utf8_decode(strtoupper(trim($_POST["prepbypos"])));
$notedby = utf8_decode(strtoupper(trim($_POST["notedby"])));
$notedbypos = utf8_decode(strtoupper(trim($_POST["notedbypos"])));
$checkedby = utf8_decode(strtoupper(trim($_POST["checkedby"])));
$checkedbypos = utf8_decode(strtoupper(trim($_POST["checkedbypos"])));
$recommendby = utf8_decode(strtoupper(trim($_POST["recommendby"])));
$recommendbypos = utf8_decode(strtoupper(trim($_POST["recommendbypos"])));
$approvedby = utf8_decode(strtoupper(trim($_POST["approvedby"])));
$approvedbypos = utf8_decode(strtoupper(trim($_POST["approvebypos"])));

$arrs = json_decode($_POST["transdata"], true);

$Now = date("m/d/Y H:i");
$encoded = $empid." [".$Now."]";

$DB->query("DELETE FROM tbl_RpBody WHERE BrCode=? AND RpNumber=?");
$DB->execute([$brcode, $transno]);

if ($trans == "new") {
    $DB->query("SELECT MAX(RpNumber) AS maxno FROM tbl_RpHead WHERE BrCode=?");
    $DB->execute([$brcode]);
    $rsmax = $DB->getrow();
    $transno = str_pad(strval($rsmax[0]["maxno"]+1), 8, "0", STR_PAD_LEFT);

    $DB->query("INSERT INTO tbl_RpHead (BrCode, RpNumber, RpDate, Proj_Id, BmNo, Remarks, Preparedpos, Preparedpos, Checkedby, Checkedpos, Notedby, Notedbypos, RecAppby, RecApppos, Approvedby, Approvedpos, Lastupdate) VALUES ('$brcode','$transno','$transdate',$proj_id,'$bmno','$remarks','$prepby','$prepbypos','$checkedby','$checkedbypos','$notedby','$notedbypos','$recommendby','$recommendbypos','$approvedby','$approvedbypos','$encoded')");
    $DB->execute([]);
} else {
    $DB->query("UPDATE tbl_RpHead SET Remarks='$remarks', Preparedby='$prepby', Preparedpos='$prepbypos', Notedby='$notedby', Notedbypos='$notedbypos', Checkedby='$Checkedby', Checkedpos='$checkedbypos', RecAppby='$recommendby', RecApppos='$recommendbypos', Approvedby='$approvedby', Approvedpos='$approvedbypos', Lastupdate='$encoded'  WHERE BrCode=? AND RpNumber=?");
    $DB->execute([$brcode, $transno]);
}

foreach ($arrs as $row) {

    $itemcode = trim($row["itemcode"]);
    $units = trim($row["units"]);
    $quantity = $row["quantity"];

    $DB->query("INSERT INTO tbl_RpBody (BrCode, RpNumber, ItemCode, UOM, Qty) VALUES ('$brcode','$transno','$itemcode','$units',$quantity)");
    $DB->execute([]);

    $DB->query("SELECT SUM(Qty) AS newQty FROM tbl_RpHead INNER JOIN tbl_RpBody ON tbl_RpHead.BrCode = tbl_RpBody.BrCode AND tbl_RpHead.RpNumber = tbl_RpBody.RpNumber WHERE tbl_RpHead.BrCode = '$brcode' AND tbl_RpHead.BmNo = '$bmno' AND ItemCode='$itemcode' AND Cancelled=0");
    $DB->execute([$brcode]);
    $rsnewQty = $DB->getrow();
    $newQty = floatval($rsnewQty[0]["newQty"]);

    $DB->query("UPDATE tbl_billing_body SET Delivered=$newQty WHERE BrCode=? AND Transno=? AND ItemCode=?");
    $DB->execute([$brcode, $bmno, $itemcode]);
}

echo "<script type='text/javascript'>window.open('ceg_rp_print.php?brcode=$brcode&transno=$transno', 'Request To Purchase', 'height=550, width=700');</script>";

header("Refresh:5; url='ceg_rp_start.php'"); 
exit();
?>