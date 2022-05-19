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

$DB->query("SELECT a.PoDate, a.CpdNumber, a.RpNumber, a.DeliverTo, a.Supplier, a.TIN, a.Address, a.ContDetails, a.Terms, a.Remarks, a.discount, a.downpayment, a.add_payment_type, a.add_payment_amt, a.Preparedby, a.Preparedpos, a.Notedby, a.Notedbypos, a.Approvedby, a.Approvedpos, b.ItemCode, descrip, UOM, Qty, UnitCost, TotalCost FROM tbl_PoHead AS a, tbl_PoBody AS b, lib_items AS c WHERE (a.BrCode=b.BrCode AND a.PoNumber=b.PoNumber AND b.ItemCode=c.itemcode) AND a.brcode=? AND a.PoNumber=?");
$DB->execute([$brcode, $transno]);
$rs = $DB->resultset();

$arr = [];
$disbody = '';
$gtcost = 0;

foreach ($rs as $row) {
    $transdate = $row->PoDate;
    $CpdNumber = trim($row->CpdNumber);
    $RpNumber = trim($row->RpNumber);
    $DeliverTo = utf8_decode(strtoupper(trim($row->DeliverTo)));
    $Supplier = utf8_decode(trim($row->Supplier));
    $TIN = trim($row->TIN);
    $Address = utf8_decode(trim($row->Address));
    $ContDetails = utf8_decode(trim($row->ContDetails));
    $terms = utf8_decode(trim($row->Terms));
    $remarks = utf8_decode(trim($row->Remarks));
    $discount = floatval($row->discount);
    $downpayment = floatval($row->downpayment);
    $add_payment_type = strtoupper(trim($row->add_payment_type));
    $add_payment_amt = floatval($row->add_payment_amt);
    $preparedby = utf8_decode(trim($row->Preparedby));
    $preparedpos = utf8_decode(trim($row->Preparedpos));
    $Notedby = utf8_decode(trim($row->Notedby));
    $Notedbypos = utf8_decode(trim($row->Notedbypos));
    $Approvedby = utf8_decode(trim($row->Approvedby));
    $Approvedpos = utf8_decode(trim($row->Approvedpos));
    $itemcode = utf8_decode(trim($row->ItemCode));
    $descrip = utf8_decode(trim($row->descrip));
    $uom = utf8_decode(trim($row->UOM));
    $qty = floatval($row->Qty);
    $ucost = floatval($row->UnitCost);
    $tcost = floatval($row->TotalCost);

    $arr[] = array(
        "itemcode"=>$itemcode,
        "descrip"=>$descrip, 
        "uom"=>$uom, 						
        "qty"=>$qty,
        "ucost"=>$ucost,
        "tcost"=>$tcost
    );

    $gtcost+=$tcost;
}

$gtcost=floatval(($gtcost+$add_payment_amt)-($discount+$downpayment));

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
    $xucost = $row1["ucost"];
    $xtcost = $row1["tcost"];

    $disbody .= "<tr>
    <td width='75' height='16' align='left'>$xitemcode</td>
    <td width='315' height='16' align='left'>$xdescrip</td>
    <td width='100' height='16' align='left'>$xuom</td>
    <td width='80' height='16' align='center'>$xqty</td>
    <td width='80' height='16' align='center'>$xucost</td>
    <td width='80' height='16' align='center'>$xtcost</td>
    </tr>";
}

$disbody .= "<tr><td>&nbsp;</td></tr>";

if($discount>0){
    $disbody .= "<tr>
    <td colspan=5 align='right'>DISCOUNT</td>
    <td align='center'>($discount)</td>
    </tr>";
}

if($downpayment>0){
    $disbody .= "<tr>
    <td colspan=5 align='right'>DOWN PAYMENT</td>
    <td align='center'>($downpayment)</td>
    </tr>";
}

if($add_payment_amt>0){
    $disbody .= "<tr>
    <td colspan=5 align='right'>$add_payment_type</td>
    <td align='center'>$add_payment_amt</td>
    </tr>";
}

$disbody .= "<tr>
<td colspan=5 align='right'><b>TOTAL AMOUNT</b></td>
<td align='center'><b>$gtcost</b></td>
</tr>";

$disbody .= "</table><br/><br/><br/><br/>";

$disbody .= "<table>
<tr>
<td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>PREPARED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
<font style='font-size:10px;'><b>".$preparedby."</b></font><br />
<font style='font-size:8px;'>".$preparedpos."</font>
</td>
<td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>NOTED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
<font style='font-size:10px;'><b>".$Notedby."</b></font><br />
<font style='font-size:8px;'>".$Notedbypos."</font>
</td>
<td style='width:10%; min-width:80px; max-width:80px;'><font style='font-size:10px;'>APPROVED BY:</font><br/><br/><br/>".str_repeat('_',20)."<br />
<font style='font-size:10px;'><b>".$Approvedby."</b></font><br />
<font style='font-size:8px;'>".$Approvedpos."</font>
</td>
</tr>
</table>";

//-- Report header
$header = "<page_header>

<div style='position:absolute; width:100%; height:7mm; left:8mm; top:-2mm;'>
<img src='image/ceg_logo.png' alt='CEG LOGO' width='93' height='90'>
</div>

<div style='position:absolute; width:100%; height:7mm; left:154mm; top:5mm;'>
<font style='font-size:18px;'>&nbsp;<b>PURCHASE ORDER</b><br /></font>	
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
<td width=100>&nbsp;Supplier</td><td width=10>:</td><td width=1000>$Supplier</td>
</tr>

<tr>
<td width=100>&nbsp;Terms</td><td width=10>:</td><td width=400>$terms</td>
</tr>

<tr>
<td width=100>&nbsp;C.P.D.#</td><td width=10>:</td><td width=400>$CpdNumber</td>
</tr>

<tr>
<td width=100>&nbsp;R.P.#</td><td width=10>:</td><td width=400>$RpNumber</td>
</tr>

<tr>
<td width=100>&nbsp;Deliver To</td><td width=10>:</td><td width=400>$DeliverTo</td>
</tr>

<tr>
<td width=100>&nbsp;Remarks</td><td width=10>:</td><td width=400>$remarks</td>
</tr>

</table>
<hr />
</p>

</div> 

<div style='position:absolute; width:100%; height:7mm; left:5mm; top:63mm;'>
<table cellspacing='0' cellpadding='0' border='0' style='font-size:11px;'>
<tr>
<th width='80' height='16' valign='middle' align='left'><u>ITEM CODE</u></th>
<th width='320' height='16' valign='middle' align='left'><u>ITEM DESCRIPTION</u></th>
<th width='100' height='16' valign='middle' align='left'><u>UNIT</u></th>
<th width='80' height='16' valign='middle' align='center'><u>QUANTITY</u></th>
<th width='80' height='16' valign='middle' align='center'><u>UNIT COST</u></th>
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
$content = '<page backtop="68mm" backbottom="5mm" backleft="5mm" backright="5mm">' . $header . $disbody . '</page>';

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
