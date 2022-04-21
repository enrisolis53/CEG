<?php
session_start();
ini_set('display_errors', 1);
include('classes/Database.class.php');

$empid = $_SESSION['ceg_empid'];
$disempid = $_POST['empid'];
$dibrcode = $_POST['branchcode'];
$disbrloc = $_POST['branchname'];
$brloc = $_SESSION["ceg_brloc"];

$DB = new classes\Database;

$DB->query("SELECT MenuCode FROM tbl_tMenu");
$DB->execute([]);
$rsmenu = $DB->resultset();

if(count($rsmenu)>0){
    $DB->query("SELECT empid FROM lib_CEGusers WHERE empid=?"); 
    $DB->execute([$disempid]);
    $rschk = $DB->resultset();
    if(count($rschk)>0){  
        $DB->query("UPDATE lib_CEGusers SET brcode='$dibrcode', brloc='$disbrloc' WHERE empid=?");
        $DB->execute([$disempid]);      
    }
    else{
        $DB->query("INSERT INTO lib_CEGusers (empid, brcode, brloc) VALUES (?, ?, ?)"); 
        $DB->execute([$disempid, $dibrcode, $disbrloc]);
    }

    $DB->query("DELETE FROM tbl_tUserMenu WHERE UserID=?");
    $DB->execute([$disempid]);
	
	foreach ($rsmenu as $row) {
		$menucode = trim($row->MenuCode);
		if (isset($_POST[$menucode])) {
            $DB->query("INSERT INTO tbl_tUserMenu (UserID, MenuCode) VALUES (?, ?)");
            $DB->execute([$disempid, $menucode]); 
		}
	}
}

$DB->query("SELECT brcode, brloc FROM PIS.dbo.lbbranch ORDER BY company, brloc");
$DB->execute([]);
$rsbranch = $DB->resultset();

if(count($rsbranch)>0){
    $DB->query("DELETE FROM lib_access_accounts WHERE empid=?");
    $DB->execute([$disempid]);

    foreach ($rsbranch as $row){
        $xbrcode = trim($row->brcode);
        $xbrloc = trim($row->brloc);

        if (isset($_POST["br_".$xbrcode])) {
            $DB->query("INSERT INTO lib_access_accounts (empid, brcode, brloc) VALUES (?, ?, ?)");
            $DB->execute([$disempid, $xbrcode, $xbrloc]); 
		}
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $brloc; ?></title>
    <link rel="shortcut icon" href="image/favicon.png" type="image/png">
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/datepicker/dpicker.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.css">
    <link rel="stylesheet" href="vendor/jquery/jquery-confirm.css">
</head>

<body class="bg-light mb-5">

<?php 
include('menu-start.php'); 

if ($empid==$disempid){ $msg = "The system detected that you modified your own menu.<br />The system will refresh your page for the changes to take effect!"; }
else { $msg = "Changes will take effect on the next login of user!"; }
?>

<!-- Modal Save -->
<div class="modal hide fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Success</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php echo $msg; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('menu-end.php'); ?>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="main/js/menu.js"></script>
<script type='text/javascript'>

window.onload = function() { 
    $('#myModal').modal('show');
}

</script>

</body>

</html>

<?php
if ($empid==$disempid){ 
	echo "<script type='text/javascript'>
	window.setTimeout(function() {
		top.location.href = 'index.php';
    }, 300);	
	</script>";
}
else{ 
	echo "<script type='text/javascript'>
	window.setTimeout(function() {
		top.location.href = 'menu_creator.php';
    }, 300);	
	</script>";
}

return;
?>