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

if ($trans == 'getmateriallist') {
  
    $DB->query('SELECT tbl_billing_head.Transno, tbl_billing_head.Transdate, tbl_billing_head.proj_id, proj_name, tbl_billing_head.Remarks FROM tbl_billing_head LEFT JOIN tbl_proj_profile ON (tbl_billing_head.proj_id=tbl_proj_profile.proj_id) WHERE tbl_billing_head.brcode=? AND posted=? ORDER BY tbl_billing_head.Transdate');    
    $DB->execute([$brcode, 0]);
    $rs = $DB->resultset();

    if(count($rs) == 0) $info = "";
    else $info = json_encode($rs);
}

if ($trans == 'getpostedmateriallist') {
    $arrlist = [];
    //$DB->query('SELECT tbl_billing_head.Transno, tbl_billing_head.Transdate, tbl_billing_head.proj_id, proj_name, tbl_billing_head.Remarks FROM tbl_billing_head LEFT JOIN tbl_proj_profile ON (tbl_billing_head.proj_id=tbl_proj_profile.proj_id) WHERE tbl_billing_head.brcode=? AND posted=? ORDER BY tbl_billing_head.Transdate');
    $DB->query('SELECT a.Transno, a.Transdate, a.proj_id, proj_name, a.Remarks, SUM(Qty-Delivered) AS cnt FROM tbl_billing_head AS a, tbl_billing_body AS b, tbl_proj_profile as C WHERE a.brcode=b.BrCode AND a.Transno=b.Transno AND a.proj_id=c.proj_id AND a.brcode=? AND posted=? GROUP BY a.brcode, a.transno, a.Transdate, a.proj_id, proj_name, a.Remarks ORDER BY a.Transdate');
    $DB->execute([$brcode, 1]);
    $rs = $DB->getrow();
    foreach($rs as $rows){
        $Transno = trim($rows["Transno"]);
        $Transdate = trim($rows["Transdate"]);
        $proj_id = $rows["proj_id"];
        $proj_name = trim($rows["proj_name"]);
        $Remarks = trim($rows["Remarks"]);
        $cnt = is_null($rows["cnt"])?0:intval($rows["cnt"]);

        if($cnt>0){
            $arrlist[] = array(
                "Transno"=>$Transno,
                "Transdate"=>$Transdate,
                "proj_name"=>utf8_decode($proj_name),
                "Remarks"=>utf8_decode($Remarks)
            );
        }
    }

    $info = json_encode($arrlist);

    if(count($arrlist) == 0) $info = "";
    else $info = json_encode($arrlist);
    
}

if ($trans == 'getrequestpurchasedlist') {
    $arrlist = [];
    $DB->query('SELECT d.BrLoc, a.RpNumber, a.RpDate, a.proj_id, proj_name, a.Remarks, SUM(Qty-Delivered) AS cnt FROM tbl_RpHead AS a, tbl_RpBody AS b, tbl_proj_profile as c, PIS.dbo.lbBranch AS d WHERE a.brcode=b.BrCode AND a.RpNumber=b.RpNumber AND a.proj_id=c.proj_id AND a.BrCode=d.BrCode AND a.brcode=? AND a.myLocal=? AND posted=? GROUP BY d.BrLoc, a.brcode, a.RpNumber, a.RpDate, a.proj_id, proj_name, a.Remarks ORDER BY  a.RpNumber, a.RpDate');
    $DB->execute([$brcode, 1, 0]);
    $rs = $DB->getrow();
    foreach($rs as $rows){
        $Branch = trim($rows["BrLoc"]);
        $Transno = trim($rows["RpNumber"]);
        $Transdate = trim($rows["RpDate"]);
        $proj_id = $rows["proj_id"];
        $proj_name = trim($rows["proj_name"]);
        $Remarks = trim($rows["Remarks"]);
        $cnt = is_null($rows["cnt"])?0:intval($rows["cnt"]);

        if($cnt>0){
            $arrlist[] = array(
                "Branch"=>$Branch,
                "Transno"=>$Transno,
                "Transdate"=>$Transdate,
                "proj_name"=>utf8_decode($proj_name),
                "Remarks"=>utf8_decode($Remarks)
            );
        }
    }

    $info = json_encode($arrlist);

    if(count($arrlist) == 0) $info = "";
    else $info = json_encode($arrlist);
    
}

if ($trans == 'getpreviewsdata') {
    $transno = str_pad(strval($_POST["transno"]), 8, "0", STR_PAD_LEFT);
    $arrhead = [];
    $arrbody = [];
    $ctr = 0;

    $DB->query("SELECT a.Transdate, a.proj_id, d.proj_name, a.Remarks, a.Preparedby, Preparedpos, a.posted, b.ItemCode, descrip, UOM, Qty FROM tbl_billing_head AS a, tbl_billing_body AS b, lib_items AS c, tbl_proj_profile AS d WHERE (a.BrCode=b.BrCode AND a.Transno=b.Transno AND b.ItemCode=c.itemcode AND a.proj_id=d.proj_id) AND a.brcode=? AND a.transno=?");
    $DB->execute([$brcode, $transno]);
    $rs = $DB->resultset();

    foreach($rs as $row){
        $transdate = $row->Transdate;
        $proj_name = utf8_decode(trim($row->proj_name));
        $proj_id = $row->proj_id;
        $remarks = utf8_decode(trim($row->Remarks));
        $preparedby = utf8_decode(trim($row->Preparedby));
        $preparedpos = utf8_decode(trim($row->Preparedpos));
        $itemcode = utf8_decode(trim($row->ItemCode));
        $descrip = utf8_decode(trim($row->descrip));
        $uom = utf8_decode(trim($row->UOM));
        $qty = floatval($row->Qty);
        $posted = intval($row->posted);

        if($ctr==0){
            $arrhead[] = array(
                "transdate"=>$transdate,
                "proj_name"=>$proj_name,
                "proj_id"=>$proj_id,
                "remarks"=>$remarks,
                "preparedby"=>$preparedby,
                "preparedpos"=>$preparedpos,
                "posted"=>$posted
            );
        }

        $arrbody[] = array(
            "itemcode"=>$itemcode,
            "itemdescrip"=>$descrip, 
            "units"=>$uom, 						
            "quantity"=>$qty
        );

        $ctr++;
    }

    if($ctr == 0) $info = "~error";
    else $info = json_encode($arrhead)."^".json_encode($arrbody);
}

echo $info;
return;
?>
