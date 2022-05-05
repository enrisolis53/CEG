<?php
ini_set('display_errors', 0);
include('classes/Database.class.php');

$brcode = $_GET['brcode'];
$transno = $_GET['transno'];
$pdftitle = $brcode."_".$transno.".pdf";

$DB = new classes\Database;

// data query
$DB->query('SELECT BrLoc FROM PIS.dbo.lbbranch WHERE BrCode=?');
$DB->execute([$brcode]);
$rshead = $DB->getrow();
$BrLoc = (!isset($rshead[0]["BrLoc"])) ? '' : $rshead[0]["BrLoc"];

$DB->query("SELECT Transdate, proj_id, Remarks, Preparedby, Preparedpos, ItemCode, UOM, Qty FROM tbl_billing_head AS a LEFT JOIN tbl_billing_body AS b ON (a.BrCode=b.BrCode AND a.Transno=b.Transno) WHERE a.brcode=? AND a.transno=?");
$DB->execute([$brcode, $transno]);
$rs = $DB->resultset();

$arr = [];
$disbody = '';

foreach ($rs as $row) {
    $transdate = $row->Transdate;
    $proj_name = trim($row->proj_name);
    $proj_date_from = $row->proj_date_from;
    $proj_date_to = trim($row->proj_date_to);
    $proj_cost = $row->proj_cost;
    $particulars = trim($row->particulars);
    $remarks = trim($row->remarks);
    $preparedby = $row->preparedby;

    $arr[] = array(
        "contractnumber"=>$contractnumber,
        "contractdate"=>$contractdate, 
        "clientname"=>$clientname, 						
        "remarks"=>$remarks
    );  

}

// if(count($arr)<=0){
// 	echo "error~<table width='100%'><tr><td align='left'><font color='#FF0000' size=3><b>No Record Found!</b></font></td><td align='right' valign='top' rowspan=2><img src='/../../images/unlike1.png' width=30 height=25 /></td></tr><tr><td><font size=1>Redirecting, please wait...</font></td></tr></table>";
// 	echo "<!DOCTYPE html>
// 	<html>
// 	<body>
// 	<script>	
// 	window.setTimeout(function() {
// 		window.close(); 
// 		return false;
// 	}, 3000);	
// 	</script>
// 	</body>
// 	</html>";
// 	exit(); 
// }

// $disbody = "<table cellspacing='0' cellpadding='0' style='font-size:10px;'>";
// foreach($arr as $row1){
//     $xcontractnumber = $row1["contractnumber"];
//     $xcontractdate = $row1["contractdate"];
//     $xclientname = $row1["clientname"];
//     $xremarks = $row1["remarks"];

//     $disbody .= "<tr>
//     <td width='130' height='16' align='center'>$xcontractnumber</td>
//     <td width='400' height='16' align='left'>$xclientname</td>
//     <td width='210' height='16' align='left'>$xremarks</td>
//     </tr>";
// }
// $disbody .= "</table><br/><br/><br/><br/><br/>";

// $DB->query("SELECT TOP (1) CONCAT(Fname,' ',LEFT(Mname, 1),'. ',Lname,' ',Suffix) AS cname, positn FROM PIS.dbo.tEmployee WHERE empid=? ORDER BY empid DESC");
// $DB->execute([$brpreparedid]);
// $rssign = $DB->getrow();
// $sign = (!isset($rssign[0]["cname"])) ? '' : $rssign[0]["cname"];
// $signpos =  (!isset($rssign[0]["positn"])) ? '' : $rssign[0]["positn"];

// $disbody .= "<table><tr>
// <td><font style='font-size:10px;'>PREPARED BY:</font><br/><br/><br/>".str_repeat('_',25)."<br />
// <font style='font-size:10px;'><b>".$sign."</b></font><br />
// <font style='font-size:8px;'>".$signpos."</font>
// </td>
// </tr></table>";

// //-- Report header
// $header = "<page_header>
// <div style='position:absolute; width:100%; height:7mm; left:0mm; top:0mm; font-size:12px;'>

// <p><hr />
// <font style='font-size:18px;'>&nbsp;<b>CLIENT FOLDER TRANSMITTAL $draft</b><br /></font>	
// <table>

// <tr>
// <td width=100>&nbsp;Branch</td><td width=10>:</td><td width=400>".strtoupper($BrLoc)."</td>
// </tr>

// <tr>
// <td width=100>&nbsp;Number</td><td width=10>:</td><td width=400>$transno</td>
// </tr>

// <tr>
// <td width=100>&nbsp;Date</td><td width=10>:</td><td width=400>$brprepareddate</td>
// </tr>

// </table>
// <hr /></p>

// </div> 

// <div style='position:absolute; width:100%; height:7mm; left:0mm; top:33mm;'>
// <table cellspacing='0' cellpadding='0' border='1' style='font-size:11px;'>
// <tr>
// <th width='150' height='16' valign='middle' align='center' bgcolor='#C0C0C0'>CONTRACT NUMBER</th>
// <th width='400' height='16' valign='middle' align='center' bgcolor='#C0C0C0'>BUYER'S NAME</th>
// <th width='210' height='16' valign='middle' align='center' bgcolor='#C0C0C0'>REMARKS</th>
// </tr>
// </table>
// </div>        
// </page_header>

// <page_footer>
// <table class='page_footer' border=0 cellspacing=0 cellpadding=0>

// </table>
// </page_footer>";

//-- To be printed
$content = '<page backtop="40mm" backbottom="5mm" backleft="5mm" backright="5mm">' . $header . $disbody . '</page>';

//-- pdf
ob_get_clean();
ob_start();
require_once(dirname(__FILE__) . '/../html2pdf/html2pdf.class.php');
try {
    $pdf = new HTML2PDF('P', 'Letter', 'en');
    $pdf->setTestTdInOnePage(false);
    $pdf->writeHTML($content, isset($_GET['vuehtml']));
    $pdf->Output($pdftitle);
} catch (HTML2PDF_exception $e) {
    echo $e;
    exit;
}
