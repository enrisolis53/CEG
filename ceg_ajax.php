<?php
ini_set('display_errors', 1);
//-- Connection to database
include('classes/Database.class.php');
//require_once(dirname(__FILE__).'/../obpfunc/phpfunc.php'); 

$DB = new classes\Database;

$brcode = trim($_POST["brcode"]);
$trans = trim($_POST["trans"]);
$info = "";

if ($trans == 'getprojectnamelist') {
  
    $DB->query('SELECT DISTINCT proj_id, proj_name FROM tbl_proj_profile WHERE brcode=? ORDER BY proj_name');    
    $DB->execute([$brcode]);
    $rs = $DB->resultset();

    if(count($rs) == 0) $info = "";
    else $info = json_encode($rs);
}

if ($trans == 'getuploadedfiles') {
    $proj_id = trim($_POST["proj_id"]);
    
    $query = $DB->query("SELECT file_uploaded_id, file_uploaded_name, uploaded_by, uploaded_date, remarks FROM tbl_file_uploaded WHERE brcode=? AND proj_id=? ORDER BY uid DESC");
    $DB->execute([$brcode, $proj_id]);
    $rs = $DB->resultset();

    if(count($rs) == 0) $info = "";
    else $info = json_encode($rs);
}

if ($trans == 'savedownloadedfiles') {
    $prepby = trim($_POST["prepby"]);
    $arrfile_to_dl = explode(".",trim($_POST["file_to_dl"]));
    $file_uploaded_id = $arrfile_to_dl[0];

    $DB->query("SELECT file_uploaded_name FROM tbl_file_uploaded WHERE file_uploaded_id=?");
    $DB->execute([$file_uploaded_id]);
    $rs = $DB->getrow();
    $fileName = trim($rs[0]["file_uploaded_name"]);        

    // successfull
    $DB->query("INSERT INTO tbl_file_downloaded (brcode, file_uploaded_id, file_uploaded_name, downloaded_by, downloaded_date) VALUES ('$brcode','$file_uploaded_id','$fileName','$prepby',getdate())");
    $DB->execute([]);  
}

echo $info;
return;
?>
