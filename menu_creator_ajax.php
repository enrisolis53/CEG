<?php
ini_set('display_errors', 1);
include('classes/Database.class.php');

$DB = new classes\Database;

$userid = (!isset($_POST["userid"]))?0:$_POST["userid"];
$trans = (!isset($_POST["trans"]))?"":$_POST["trans"];
$content = "";

if($trans=="branch"){
	$DB->query("SELECT brcode, brloc FROM lib_CEGusers WHERE empid=?");
	$DB->execute([$userid]);
	$rs = $DB->getrows();

	if(count($rs)==0){ $content = "~error"; }
	else { $content.= json_encode($rs); }
}

if($trans=="menu"){
	$content .= "<table><tr><th class='tblhd' width=470>Name</th><th class='tblhd' width=120>Code</th></tr>";

	//-- User Setting
	$DB->query("SELECT * FROM tbl_tMenu WHERE MenuLevel=? ORDER BY MenuLevel, Ordinal");
	$DB->execute([1]);
	$rs1 = $DB->resultset();
	if(count($rs1)>0){
		foreach ($rs1 as $row1){
			$repeatme = 5;
			$menucode1 = $row1->MenuCode;
			$menuname1 = $row1->MenuName;
			
			$content .= "<tr>";
			$DB->query("SELECT * FROM tbl_tUserMenu WHERE UserID=? AND MenuCode=?"); 
			$DB->execute([$userid,$menucode1]);
			$rschk = $DB->resultset();
			if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode1' id='$menucode1' checked />&nbsp;"; }
			else{ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode1' id='$menucode1' />&nbsp;"; }
			$content .= "<b><label for='$menucode1' style='font-size:11px;'>$menuname1</label></b></td>
			<td class='tblbd'><b><label style='font-size:11px;'>$menucode1</label></b></td>
			</tr>";
			$rschk=null;
	
			//-- Sub menu2
			$DB->query("SELECT * FROM tbl_tMenu WHERE MenuLevel=? AND ParentCode=? ORDER BY menulevel, Ordinal"); 
			$DB->execute([2,$menucode1]);
			$rs2 = $DB->resultset();
			if(count($rs2)>0){
				foreach ($rs2 as $row2){
					$repeatme = 10;
					$menucode2 = $row2->MenuCode;
					$menuname2 = $row2->MenuName;		
					
					$content .= "<tr>";
					$DB->query("SELECT * FROM tbl_tUserMenu WHERE UserID=? AND MenuCode=?"); 
					$DB->execute([$userid,$menucode2]);
					$rschk = $DB->resultset();
					if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode2' id='$menucode2' checked />&nbsp;"; }
					else { $content .= "<td class='tblbd'><input type='checkbox' name='$menucode2' id='$menucode2' />&nbsp;"; }
					$content .= "<label for='$menucode2'>".str_repeat('&nbsp;',$repeatme).$menuname2."</label></td>
					<td class='tblbd'><label>".str_repeat('&nbsp;',$repeatme).$menucode2."</label></td>
					</tr>";	
					$rschk=null;
	
					//-- Sub menu3
					$DB->query("SELECT * FROM tbl_tMenu WHERE MenuLevel=? AND ParentCode=? ORDER BY menulevel, Ordinal"); 
					$DB->execute([3,$menucode2]);
					$rs3 = $DB->resultset();
					if(count($rs3)>0){
						foreach ($rs3 as $row3){
							$repeatme = 15;
							$menucode3 = $row3->MenuCode;
							$menuname3 = $row3->MenuName;	
							
							$content .= "<tr>";
							$DB->query("SELECT * FROM tbl_tUserMenu WHERE UserID=? AND MenuCode=?"); 
							$DB->execute([$userid,$menucode3]);
							$rschk = $DB->resultset();
							if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode3' id='$menucode3' checked />&nbsp;"; }
							else { $content .= "<td class='tblbd'><input type='checkbox' name='$menucode3' id='$menucode3' />&nbsp;"; }
							$content .= "<label for='$menucode3'>".str_repeat('&nbsp;',$repeatme).$menuname3."</label></td>
							<td class='tblbd'><label>".str_repeat('&nbsp;',$repeatme).$menucode3."</label></td>
							</tr>";	
							$rschk=null;
	
							//-- Sub menu4
							$DB->query("SELECT * FROM tbl_tMenu WHERE MenuLevel=? AND ParentCode=? ORDER BY menulevel, Ordinal"); 
							$DB->execute([4,$menucode3]);
							$rs4 = $DB->resultset();
							if(count($rs4)>0){	
								foreach ($rs4 as $row4){
									$repeatme = 20;
									$menucode4 = $row4->MenuCode;
									$menuname4 = $row4->MenuName;	
	
									$content .= "<tr>";
									$DB->query("SELECT * FROM tbl_tUserMenu WHERE UserID=? AND MenuCode=?"); 
									$DB->execute([$userid,$menucode4]);
									$rschk = $DB->resultset();
									if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode4' id='$menucode4' checked />&nbsp;"; }
									else { $content .= "<td class='tblbd'><input type='checkbox' name='$menucode4' id='$menucode4' />&nbsp;"; }
									$content .= "<label for='$menucode4'>".str_repeat('&nbsp;',$repeatme).$menuname4."</label></td>
									<td class='tblbd'><label>".str_repeat('&nbsp;',$repeatme).$menucode4."</label></td>
									</tr>";	
									$rschk=null;
									
									//-- Sub menu5
									$DB->query("SELECT * FROM tbl_tMenu WHERE MenuLevel=? AND ParentCode=? ORDER BY menulevel, Ordinal"); 
									$DB->execute([5,$menucode4]);
									$rs5 = $DB->resultset();
									if(count($rs5)>0){	
										foreach ($rs5 as $row5){
											$repeatme = 25;
											$menucode5 = $row5->MenuCode;
											$menuname5 = $row5->MenuName;															
	
											$content .= "<tr>";
											$DB->query("SELECT * FROM tbl_tUserMenu WHERE UserID=? AND MenuCode=?"); 
											$DB->execute([$userid,$menucode5]);
											$rschk = $DB->resultset();
											if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='$menucode5' id='$menucode5' checked />&nbsp;"; }
											else { $content .= "<td class='tblbd'><input type='checkbox' name='$menucode5' id='$menucode5' />&nbsp;"; }
											$content .= "<label for='$menucode5'>".str_repeat('&nbsp;',$repeatme).$menuname5."</label></td>
											<td class='tblbd'><label>".str_repeat('&nbsp;',$repeatme).$menucode5."</label></td>
											</tr>";	
											$rschk=null;														
										}
									}
									$rs5=null;
								}																			
							}
							$rs4=null;
						}
					}								
					$rs3=null;
				}							
			}
			$rs2=null;			
		}
	}
	
	$content .= "<tr><td colspan=2 height=3></td></tr></table>";
}

if($trans=="divisiontoaccess"){
	$content .= "<table><tr><th class='tblhd' width=470>Name</th><th class='tblhd' width=120>Code</th></tr>";

	//-- User Setting
	$DB->query("SELECT brcode, brloc FROM PIS.dbo.lbbranch ORDER BY company, brloc");
	$DB->execute([]);
	$rs2 = $DB->resultset();
	if(count($rs2)>0){
		foreach ($rs2 as $row2){
			$xbrcode = trim($row2->brcode);
			$xbrloc = trim($row2->brloc);
			
			$content .= "<tr>";
			$DB->query("SELECT brloc FROM lib_access_accounts WHERE empid=? AND brcode=?");
			$DB->execute([$userid,$xbrcode]);
			$rschk = $DB->resultset();
			if(count($rschk)>0){ $content .= "<td class='tblbd'><input type='checkbox' name='br_$xbrcode' id='br_$xbrcode' checked />&nbsp;"; }
			else{ $content .= "<td class='tblbd'><input type='checkbox' name='br_$xbrcode' id='br_$xbrcode' />&nbsp;"; }
			$content .= "<b><label for='br_$xbrcode' style='font-size:11px;'>$xbrloc</label></b></td>
			<td class='tblbd'><b><label style='font-size:11px;'>$xbrcode</label></b></td>
			</tr>";
			$rschk=null;		
		}
	}
	
	$content .= "<tr><td colspan=2 height=3></td></tr></table>";
	$rs2=null;
}

echo $content;
return; 
?>