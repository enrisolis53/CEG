<?php
//ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');
// GET MENU DEPENDS ON USER ID
$empid = $_SESSION['ceg_empid'] = $_SESSION["empid"];

$DB = new classes\Database;

$DB->query('SELECT TOP (1) brcode, brloc FROM lib_CEGusers WHERE empid=?');
$DB->execute([$empid]);
$myinfo = $DB->getrows();
$_SESSION["ceg_brcode"] = trim($myinfo[0]["brcode"]);
$myBranch = $_SESSION["ceg_brloc"] = trim($myinfo[0]["brloc"]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CEG - <?php echo $myBranch; ?></title>
    <link rel="shortcut icon" href="image/favicon.png" type="image/png">
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.css">
</head>

<body class="bg-light mb-5">

    <?php include('menu-start.php'); ?>

    <div class="container-fluid">
        <img src="./image/ceg.jpg" height='150' width='300' />
    </div>

	<?php include('menu-end.php'); ?>
    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="main/js/menu.js"></script>

</body>

</html>