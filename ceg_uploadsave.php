<?php
ini_set('display_errors', 1);
session_start();
include('classes/Database.class.php');

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$proj_name = utf8_decode(trim(strtoupper($_POST["proj_name"])));
$proj_id = $_POST["proj_id"];
$remarks = utf8_decode(trim($_POST["remarks"]));
$prepby = trim($_POST["prepby"]);

if ($_FILES["fileToUpload"]["name"] !='') {
    $file_uploaded_id = $brcode.'_'.$proj_id.'_'.date('mdYhis');

    //get details of the uploaded file
    $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
    $fileName = $_FILES['fileToUpload']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileBackup = $fileNameCmps[0];
    $fileExtension = strtolower($fileNameCmps[1]);
    
    // directory in which the uploaded file will be moved
    $uploadFileDir = '../CEG/File Maintenance/';
    $dest_path = $uploadFileDir.$file_uploaded_id.'.'.$fileExtension;

    if(move_uploaded_file($fileTmpPath, $dest_path)){
        // successfull
        $DB->query("INSERT INTO tbl_file_uploaded (brcode, file_uploaded_id, file_uploaded_name, uploaded_by, uploaded_date, proj_id, transno, remarks) VALUES ('$brcode','$file_uploaded_id','$fileName','$prepby',getdate(),'$proj_id','','$remarks')");
        $DB->execute([]);    
        header("Refresh:0; url='ceg_uploadfile.php?msg=File uploaded successfully'"); exit(); 
    }
    else{
        // removing file if error occured
        unlink($dest_path);
        header("Refresh:0; url='ceg_uploadfile.php?msg=The data uploader failed to execute your choosen file! Please try again...'"); exit(); 
    }    
} 

header("Refresh:0; url='ceg_uploadfile.php?msg=No choosen file'"); exit();

?>