<?php
ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Saving, Please wait...</b></font>";

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$transno = trim($_POST["transno"]);
if (empty($transno) || trim($transno) == "") { $trans = "new"; } else { $trans = "edit"; }
$transdate = $_POST["transdate"];
//$proj_name = utf8_decode(trim(strtoupper($_POST["proj_name"])));
$proj_id = $_POST["proj_id"];
$remarks = utf8_decode(trim($_POST["remarks"]));
$prepby = utf8_decode(strtoupper(trim($_POST["prepby"])));
$prepbypos = utf8_decode(strtoupper(trim($_POST["prepbypos"])));

$arrs = json_decode($_POST["transdata"], true);

$transno = str_pad(strval($transno), 8, "0", STR_PAD_LEFT);

$DB->query("DELETE FROM tbl_billing_body WHERE BrCode=? AND Transno=?");
$DB->execute([$brcode, $transno]);

if ($trans == "new") {
    $DB->query("SELECT MAX(transno) AS maxno FROM tbl_billing_head WHERE brcode=?");
    $DB->execute([$brcode]);
    $rsmax = $DB->getrow();
    $transno = str_pad(strval($rsmax[0]["maxno"]+1), 8, "0", STR_PAD_LEFT);

    $DB->query("INSERT INTO tbl_billing_head (BrCode, Transno, Transdate, proj_id, Remarks, Preparedby, Preparedpos) VALUES ('$brcode','$transno','$transdate',$proj_id,'$remarks','$prepby','$prepbypos')");
    $DB->execute([]);
} else {
    $DB->query("UPDATE tbl_billing_head SET proj_id=$proj_id, Remarks='$remarks', Preparedby='$prepby', Preparedpos='$prepbypos' WHERE brcode=? AND transno=?");
    $DB->execute([$brcode, $transno]);
}

foreach ($arrs as $row) {

    $itemcode = trim($row["itemcode"]);
    //$itemdescrip = trim($row["itemdescrip"]);
    $units = trim($row["units"]);
    $quantity = $row["quantity"];

    $DB->query("INSERT INTO tbl_billing_body (BrCode, Transno, ItemCode, UOM, Qty) VALUES ('$brcode','$transno','$itemcode','$units',$quantity)");
    $DB->execute([]);

}

echo "<script type='text/javascript'>window.open('ceg_billing_materials_print.php?brcode=$brcode&transno=$transno', 'Billing Materials', 'height=550, width=700');</script>";

header("Refresh:5; url='ceg_billing_materials.php'"); 
exit();
?>