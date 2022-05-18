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

$DB->query("SELECT a.RrDate, a.PoBrCode, a.PoNumber, a.CpdNumber, a.RpNumber, a.DrNumber, a.SiNumber, a.Remarks, a.Receivedby, a.Receivedpos, a.Notedby, a.Notedpos, b.ItemCode, descrip, b.UOM, b.Qty, b.UnitCost, b.IncidentalCost, b.TotalCost FROM tbl_RrHead AS a, tbl_RrBody AS b, lib_items AS c WHERE (a.BrCode=b.BrCode AND a.RrNumber=b.RrNumber AND b.ItemCode=c.itemcode) AND a.brcode=? AND a.RrNumber=?");
$DB->execute([$brcode, $transno]);
$rs = $DB->resultset();

$arr = [];
$disbody = '';
$gtcost = 0;

foreach ($rs as $row) {
    $transdate = $row->RrDate;
    $pobrcode = trim($row->PoBrCode);
    $pono = trim($row->PoNumber);
    $cpdno = trim($row->CpdNumber);
    $rpno = trim($row->RpNumber);
    $drno = trim($row->DrNumber);
    $sino = trim($row->SiNumber);
    if($drno!="" && $sino!="") { $receiptno = $drno.' / '.$sino; }
    else if($drno!="" && $sino==""){ $receiptno = $drno; }
    else if($drno=="" && $sino!=""){ $receiptno = $sino; }
    else { $receiptno = ""; }
    if($cpdno!="") { $xpono = $pono.' / '.$cpdno; }
    else { $xpono = $pono; }
    $remarks = utf8_decode(trim($row->Remarks));
    $receivedby = utf8_decode(trim($row->Receivedby));
    $receivedbypos = utf8_decode(trim($row->Receivedpos));
    $notedby = utf8_decode(trim($row->Notedby));
    $notedbypos = utf8_decode(trim($row->Notedpos));
    $itemcode = utf8_decode(trim($row->ItemCode));
    $descrip = utf8_decode(trim($row->descrip));
    $uom = utf8_decode(trim($row->UOM));
    $qty = floatval($row->Qty);
    $ucost = floatval($row->UnitCost);
    $icost = floatval($row->IncidentalCost);
    $tcost = floatval($row->TotalCost);

    $arr[] = array(
        "itemcode"=>$itemcode,
        "descrip"=>$descrip, 
        "uom"=>$uom, 						
        "qty"=>$qty,
        "ucost"=>$ucost,
        "icost"=>$icost,
        "tcost"=>$tcost
    );

    $gtcost+=$tcost;
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

$DB->query('SELECT Supplier FROM tbl_PoHead WHERE BrCode=? AND PoNumber=?');
$DB->execute([$pobrcode, $pono]);
$rssupplier = $DB->getrow();
$Supplier = (!isset($rssupplier[0]["Supplier"])) ? '' : utf8_decode(strtoupper(trim($rssupplier[0]["Supplier"])));

$disbody = "<table cellspacing='0' cellpadding='0' style='font-size:10px;'>";
foreach($arr as $row1){
    $xitemcode = $row1["itemcode"];
    $xdescrip = $row1["descrip"];
    $xuom = $row1["uom"];
    $xqty = $row1["qty"];
    $xucost = $row1["ucost"];
    $xicost = $row1["icost"];
    $xtcost = $row1["tcost"];

    $disbody .= "<tr>
    <td width='75' height='16' align='left'>$xitemcode</td>
    <td width='245' height='16' align='left'>$xdescrip</td>
    <td width='100' height='16' align='left'>$xuom</td>
    <td width='80' height='16' align='center'>$xqty</td>
    <td width='80' height='16' align='center'>$xucost</td>
    <td width='80' height='16' align='center'>$xicost</td>
    <td width='80' height='16' align='center'>$xtcost</td>
    </tr>";
}

$disbody .= "<tr><td>&nbsp;</td></tr>";

$disbody .= "<tr>
<td colspan=6 align='right'><b>TOTAL AMOUNT</b></td>
<td align='center'><b>$gtcost</b></td>
</tr>";

$disbody .= "</table><br/><br/><br/><br/>";

$disbody .= "<table>
<tr>
<td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>RECEIVED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
<font style='font-size:10px;'><b>".$receivedby."</b></font><br />
<font style='font-size:8px;'>".$receivedbypos."</font>
</td>
<td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>NOTED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
<font style='font-size:10px;'><b>".$notedby."</b></font><br />
<font style='font-size:8px;'>".$notedbypos."</font>
</td>
</tr>
</table>";

//-- Report header
$header = "<page_header>

<div style='position:absolute; width:100%; height:7mm; left:8mm; top:-2mm;'>
<img src='image/ceg_logo.png' alt='CEG LOGO' width='93' height='90'>
</div>

<div style='position:absolute; width:100%; height:7mm; left:152mm; top:5mm;'>
<font style='font-size:18px;'>&nbsp;<b>RECEIVING REPORT</b><br /></font>	
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
<td width=100>&nbsp;From</td><td width=10>:</td><td width=400>$Supplier</td>
</tr>

<tr>
<td width=100>&nbsp;D.R. / S.I.#</td><td width=10>:</td><td width=400>$receiptno</td>
</tr>

<tr>
<td width=100>&nbsp;P.O. / C.P.D.#</td><td width=10>:</td><td width=400>$xpono</td>
</tr>

<tr>
<td width=100>&nbsp;RP#</td><td width=10>:</td><td width=400>$rpno</td>
</tr>

<tr>
<td width=100>&nbsp;Remarks</td><td width=10>:</td><td width=400>$remarks</td>
</tr>

</table>
<hr />
</p>

</div> 

<div style='position:absolute; width:100%; height:7mm; left:5mm; top:60mm;'>
<table cellspacing='0' cellpadding='0' border='0' style='font-size:11px;'>
<tr>
<th width='80' height='16' valign='middle' align='left'><u>ITEM CODE</u></th>
<th width='250' height='16' valign='middle' align='left'><u>ITEM DESCRIPTION</u></th>
<th width='100' height='16' valign='middle' align='left'><u>UNIT</u></th>
<th width='80' height='16' valign='middle' align='center'><u>QTY</u></th>
<th width='80' height='16' valign='middle' align='center'><u>UNIT COST</u></th>
<th width='80' height='16' valign='middle' align='center'><u>INCIDENTAL COST</u></th>
<th width='80' height='16' valign='middle' align='center'><u>TOTAL COST</u></th>
</tr>
</table>
</div>        
</page_header>

<page_footer>
<table class='page_footer' border=0 cellspacing=0 cellpadding=0>

</table>
</page_footer>";

//-- To be printed
$content = '<page backtop="65mm" backbottom="5mm" backleft="5mm" backright="5mm">' . $header . $disbody . '</page>';

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
