    <?php
ini_set('display_errors', 0);
session_start();
include('classes/Database.class.php');
$empid = $_SESSION["ceg_empid"];

echo "<img src='/../images/loader.gif' height='20' width='20' alt='saving' />&nbsp;<font face='arial' size=1 color='#00890F'><b>Saving, Please wait...</b></font>";

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$transno = trim($_POST["transno"]);
if (empty($transno) || trim($transno) == "") { $trans = "new"; } else { $trans = "edit"; }
$transdate = $_POST["transdate"];
$proj_name = utf8_decode(trim(strtoupper($_POST["proj_name"])));
$proj_id = $_POST["proj_id"];
$datefrom = $_POST["datefrom"];
$dateto = $_POST["dateto"];
$projcost = $_POST["projcost"];
$particulars = utf8_decode(trim($_POST["particulars"]));
$remarks = utf8_decode(trim($_POST["remarks"]));
$file_uploaded_id = trim($_POST["file_uploaded_id"]);
$fileName = "";
$prepby = trim($_POST["prepby"]);
//print_r($_FILES["fileToUpload"]["tmp_name"]); exit();

if($proj_id==""){
    $DB->query("SELECT MAX(proj_id+1) AS maxid FROM tbl_proj_profile");
    $DB->execute([]);
    $rsmax = $DB->getrow();
    $proj_id = intval($rsmax[0]["maxid"]);
}

if ($trans == "new") {
    $DB->query("SELECT MAX(transno) AS maxno FROM tbl_proj_profile WHERE brcode=?");
    $DB->execute([$brcode]);
    $rsmax = $DB->getrow();
    $transno = str_pad(strval($rsmax[0]["maxno"]+1), 8, "0", STR_PAD_LEFT);

    $DB->query("INSERT INTO tbl_proj_profile (brcode, transno, transdate, proj_id, proj_name, proj_date_from, proj_date_to, proj_cost, particulars, remarks, preparedby) VALUES ('$brcode','$transno','$transdate',$proj_id,'$proj_name','$datefrom','$dateto',$projcost,'$particulars','$remarks','$prepby')");
    $DB->execute([]);
} else {
    // $DB->query('UPDATE tbl_proj_profile SET WHERE brcode=? AND transno=?');
    // $DB->execute([$brcode, $transno]);
}

if ($_FILES["fileToUpload"]["name"] !='') {
    if($file_uploaded_id==""){ $file_uploaded_id = $brcode.'_'.$proj_id.'_'.date('mdYhis'); }
    //get details of the uploaded file
    $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
    $fileName = $_FILES['fileToUpload']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileBackup = $fileNameCmps[0];
    $fileExtension = strtolower($fileNameCmps[1]);
    
    // directory in which the uploaded file will be moved
    $uploadFileDir = '../CEG/File Maintenance/';
    $dest_path = $uploadFileDir.$file_uploaded_id.'.'.$fileExtension;

    move_uploaded_file($fileTmpPath, $dest_path);

    $DB->query("INSERT INTO tbl_file_uploaded (brcode, file_uploaded_id, file_uploaded_name, uploaded_by, uploaded_date, proj_id, transno, remarks) VALUES ('$brcode','$file_uploaded_id','$fileName','$prepby','$transdate',$proj_id,'$transno','$remarks')");
    $DB->execute([]);    
}

//echo "<script type='text/javascript'>window.open('ceg_createproject_print.php?brcode=$brcode&transno=$transno', 'Project Profile', 'height=550, width=700');</script>";

header("Refresh:5; url='ceg_createproject.php'"); 
exit();
?>