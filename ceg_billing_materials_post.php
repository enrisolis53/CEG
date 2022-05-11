<?php
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Posting, Please wait...</b></font>";

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$status = trim($_POST["status"]);

$arrs = json_decode($_POST["transdata"], true);

$ctr = 0;

$DB->query("SELECT CONCAT(Fname,' ',LEFT(Mname, 1),'. ',Lname,' ',suffix) as wname, getdate() AS today FROM PIS.dbo.tEmployee WHERE empid=?"); 
$DB->execute([$empid]);
$rs = $DB->getrow();
$wname = utf8_decode(strtoupper(trim($rs[0]["wname"])));
$today = trim($rs[0]["today"]);

foreach ($arrs as $row) {

    $transno = trim($row["transno"]);

    if(isset($_POST[$ctr])) {
    $DB->query("UPDATE tbl_billing_head SET posted=$status, postedby='$wname', posteddate='$today' WHERE brcode=? AND transno=?");
    $DB->execute([$brcode, $transno]);
    }

    $ctr++;
}

header("Refresh:5; url='ceg_billing_materials_posting.php'"); 
exit();
?>