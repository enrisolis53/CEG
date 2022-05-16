<?php
ini_set('display_errors', 0);
include('classes/Database.class.php');

$brcode = $_GET['brcode'];
$transno = $_GET['transno'];
$transno = str_pad(strval($transno), 8, "0", STR_PAD_LEFT);
$pdftitle = $brcode."_".$transno.".pdf";

$DB = new classes\Database;

// data query
$DB->query('SELECT BrLoc FROM PIS.dbo.lbbranch WHERE BrCode=?');
$DB->execute([$brcode]);
$rshead = $DB->getrow();
$BrLoc = (!isset($rshead[0]["BrLoc"])) ? '' : $rshead[0]["BrLoc"];

$DB->query("SELECT a.RpDate, a.proj_id, d.proj_name, a.BmNo, a.Remarks, a.Preparedby, a.Preparedpos, a.Checkedby, a.Checkedpos, a.Notedby, a.Notedbypos, a.RecAppby, a.RecApppos, a.Approvedby, a.Approvedpos, b.ItemCode, descrip, UOM, Qty FROM tbl_RpHead AS a, tbl_RpBody AS b, lib_items AS c, tbl_proj_profile AS d WHERE (a.BrCode=b.BrCode AND a.RpNumber=b.RpNumber AND b.ItemCode=c.itemcode AND a.proj_id=d.proj_id) AND a.brcode=? AND a.RpNumber=?");
$DB->execute([$brcode, $transno]);
$rs = $DB->resultset();

$arr = [];
$disbody = '';

foreach ($rs as $row) {
    $transdate = $row->RpDate;
    $proj_name = utf8_decode(trim($row->proj_name));
    $bmno = utf8_decode(trim($row->BmNo));
    $remarks = utf8_decode(trim($row->Remarks));
    $preparedby = utf8_decode(trim($row->Preparedby));
    $preparedpos = utf8_decode(trim($row->Preparedpos));
    $Checkedby = utf8_decode(trim($row->Checkedby));
    $Checkedpos = utf8_decode(trim($row->Checkedpos));
    $Notedby = utf8_decode(trim($row->Notedby));
    $Notedbypos = utf8_decode(trim($row->Notedbypos));
    $RecAppby = utf8_decode(trim($row->RecAppby));
    $RecApppos = utf8_decode(trim($row->RecApppos));
    $Approvedby = utf8_decode(trim($row->Approvedby));
    $Approvedpos = utf8_decode(trim($row->Approvedpos));
    $itemcode = utf8_decode(trim($row->ItemCode));
    $descrip = utf8_decode(trim($row->descrip));
    $uom = utf8_decode(trim($row->UOM));
    $qty = floatval($row->Qty);

    $arr[] = array(
        "itemcode"=>$itemcode,
        "descrip"=>$descrip, 
        "uom"=>$uom, 						
        "qty"=>$qty
    );  
}

if(count($arr)<=0){
	echo "error~<table width='100%'><tr><td align='left'><font color='#FF0000' size=3><b>No Record Found!</b></font></td><td align='right' valign='top' rowspan=2><img src='/../../images/unlike1.png' width=30 height=25 /></td></tr><tr><td><font size=1>Redirecting, please wait...</font></td></tr></table>";
	echo "<!DOCTYPE html>
	<html>
	<body>
	<script>	
	window.setTimeout(function() {
		window.close(); 
		return false;
	}, 3000);	
	</script>
	</body>
	</html>";
	exit(); 
}

$disbody = "<table cellspacing='0' cellpadding='0' style='font-size:10px;'>";
foreach($arr as $row1){
    $xitemcode = $row1["itemcode"];
    $xdescrip = $row1["descrip"];
    $xuom = $row1["uom"];
    $xqty = $row1["qty"];

    $disbody .= "<tr>
    <td width='95' height='16' align='left'>$xitemcode</td>
    <td width='395' height='16' align='left'>$xdescrip</td>
    <td width='120' height='16' align='left'>$xuom</td>
    <td width='80' height='16' align='center'>$xqty</td>
    </tr>";
}
$disbody .= "</table><br/><br/><br/><br/><br/>";

$disbody .= "<table><tr>";
if($Checkedby!=""){
    $disbody .= "
    <td max-width:10%;><font style='font-size:10px;'>PREPARED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$preparedby."</b></font><br />
    <font style='font-size:8px;'>".$preparedpos."</font>
    </td>
    <td max-width:10%;><font style='font-size:10px;'>REQUESTED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$Notedby."</b></font><br />
    <font style='font-size:8px;'>".$Notedbypos."</font>
    </td>
    <td max-width:10%;><font style='font-size:10px;'>CHECKED & VERIFIED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$Checkedby."</b></font><br />
    <font style='font-size:8px;'>".$Checkedpos."</font>
    </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
    <td max-width:10%;><font style='font-size:10px;'>RECOMMENDING APPROVAL:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$RecAppby."</b></font><br />
    <font style='font-size:8px;'>".$RecApppos."</font>
    </td max-width:10%;>
    <td><font style='font-size:10px;'>APPROVED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$Approvedby."</b></font><br />
    <font style='font-size:8px;'>".$Approvedpos."</font>
    </td>
    <td>&nbsp;</td>";
} else {
    $disbody .= "
    <td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>PREPARED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$preparedby."</b></font><br />
    <font style='font-size:8px;'>".$preparedpos."</font>
    </td>
    <td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>REQUESTED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$Notedby."</b></font><br />
    <font style='font-size:8px;'>".$Notedbypos."</font>
    </td>
    <td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>RECOMMENDING APPROVAL:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$RecAppby."</b></font><br />
    <font style='font-size:8px;'>".$RecApppos."</font>
    </td>
    <td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>APPROVED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
    <font style='font-size:10px;'><b>".$Approvedby."</b></font><br />
    <font style='font-size:8px;'>".$Approvedpos."</font>
    </td>";
}
$disbody .= "</tr></table>";

//-- Report header
$header = "<page_header>

<div style='position:absolute; width:100%; height:7mm; left:5mm; top:5mm;'>
<img src='image/ceg.jpg' alt='CEG LOGO' width='80' height='50'>
</div>

<div style='position:absolute; width:100%; height:7mm; left:140mm; top:5mm;'>
<font style='font-size:18px;'>&nbsp;<b>REQUEST TO PURCHASE</b><br /></font>	
</div>

<div style='position:absolute; width:100%; height:7mm; left:168mm; top:10mm;'>
<font style='font-size:18px;'>&nbsp;<b>NO.&nbsp;$transno</b><br /></font>	
</div>

<div style='position:absolute; width:100%; height:7mm; left:0mm; top:15mm; font-size:12px;'>

<p>
<hr />
<table>
<tr>
<td width=100>&nbsp;Branch</td><td width=10>:</td><td width=400>".strtoupper($BrLoc)."</td>
</tr>

<tr>
<td width=100>&nbsp;Date</td><td width=10>:</td><td width=400>".date('F d, Y', strtotime($transdate))."</td>
</tr>

<tr>
<td width=100>&nbsp;Project Name</td><td width=10>:</td><td width=400>$proj_name</td>
</tr>

<tr>
<td width=100>&nbsp;Billing Materials#</td><td width=10>:</td><td width=400>$bmno</td>
</tr>

<tr>
<td width=100>&nbsp;Remarks</td><td width=10>:</td><td width=400>$remarks</td>
</tr>

</table>
<hr />
</p>

</div> 

<div style='position:absolute; width:100%; height:7mm; left:5mm; top:52mm;'>
<table cellspacing='0' cellpadding='0' border='0' style='font-size:11px;'>
<tr>
<th width='100' height='16' valign='middle' align='left'><u>ITEM CODE</u></th>
<th width='400' height='16' valign='middle' align='left'><u>ITEM DESCRIPTION</u></th>
<th width='100' height='16' valign='middle' align='left'><u>UNIT</u></th>
<th width='100' height='16' valign='middle' align='center'><u>QUANTITY</u></th>
</tr>
</table>
</div>        
</page_header>

<page_footer>
<table class='page_footer' border=0 cellspacing=0 cellpadding=0>

</table>
</page_footer>";

//-- To be printed
$content = '<page backtop="56mm" backbottom="5mm" backleft="5mm" backright="5mm">' . $header . $disbody . '</page>';

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
