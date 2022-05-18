<?php
ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];
$mybrcode = $_SESSION["ceg_brcode"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Saving, Please wait...</b></font>";

$DB = new classes\Database;

$pobrcode = trim($_POST["pobrcode"]);
$brcode = trim($_POST["brcode"]);
$transdate = $_POST["transdate"];
$pono = trim($_POST["pono"]);
$rpno = trim($_POST["rpno"]);
$drno = trim($_POST["drno"]);
$sino = trim($_POST["sino"]);
$remarks = utf8_decode(trim($_POST["remarks"]));

$receivedby = utf8_decode(strtoupper(trim($_POST["receivedby"])));
$receivedbypos = utf8_decode(strtoupper(trim($_POST["receivedbypos"])));
$notedby = utf8_decode(strtoupper(trim($_POST["notedby"])));
$notedbypos = utf8_decode(strtoupper(trim($_POST["notedbypos"])));

$arrs = json_decode($_POST["transdata"], true);

$Now = date("m/d/Y H:i");
$encoded = $empid." [".$Now."]";

$DB->query("SELECT MAX(RrNumber) AS maxno FROM tbl_RrHead WHERE BrCode=?");
$DB->execute([$brcode]);
$rsmax = $DB->getrow();
$transno = str_pad(strval($rsmax[0]["maxno"]+1), 8, "0", STR_PAD_LEFT);

$DB->query("INSERT INTO tbl_RrHead (BrCode, RrNumber, RrDate, PoBrCode, PoNumber, RpNumber, DrNumber, SiNumber, Remarks, Receivedby, Receivedpos, Notedby, Notedpos) VALUES ('$brcode','$transno','$transdate','$pobrcode','$pono','$rpno','$drno','$sino','$remarks','$receivedby','$receivedbypos','$notedby','$notedbypos')");
$DB->execute([]);

foreach ($arrs as $row) {
    $newucost = 0;

    $itemcode = trim($row["itemcode"]);
    $units = trim($row["units"]);
    $quantity = $row["quantity"];
    $ucost = $row["ucost"];
    $icost = $row["icost"];
    $tcost = $row["tcost"];

    $newucost = floatval($ucost+$icost);

    $DB->query("INSERT INTO tbl_RrBody (BrCode, RrNumber, ItemCode, UOM, Qty, UnitCost, IncidentalCost, NewUnitCost, TotalCost) VALUES ('$brcode','$transno','$itemcode','$units',$quantity,$ucost,$icost,$newucost,$tcost)");
    $DB->execute([]);

    $DB->query("SELECT SUM(Qty) AS newQty FROM tbl_RrHead INNER JOIN tbl_RrBody ON tbl_RrHead.BrCode = tbl_RrBody.BrCode AND tbl_RrHead.RrNumber = tbl_RrBody.RrNumber WHERE tbl_RrHead.PoBrCode = ? AND tbl_RrHead.PoNumber = ? AND ItemCode=?");
    $DB->execute([$pobrcode, $pono, $itemcode]);
    $rsnewQty = $DB->getrow();
    $newQty = floatval($rsnewQty[0]["newQty"]);

    $DB->query("UPDATE tbl_PoBody SET Delivered=$newQty WHERE BrCode=? AND PoNumber=? AND ItemCode=?");
    $DB->execute([$brcode, $pono, $itemcode]);
}

echo "<script type='text/javascript'>window.open('ceg_rr_print.php?brcode=$brcode&transno=$transno', 'Receiving Report', 'height=550, width=700');</script>";

header("Refresh:5; url='ceg_rr_start.php'"); 
exit();
?>