<?php
session_start();
ini_set('display_errors', 1);
include('classes/Database.class.php');

$empid = $_SESSION["ceg_empid"];
$brloc = $_SESSION["ceg_brloc"];

$host = 'localhost'; 
$user = 'sa';
$pass = 'morpheus';
$database = 'PIS';

$PIS = new classes\Database($host, $user, $pass, $database);

$PIS->query("SELECT (RTRIM(Lname)+', '+RTRIM(Fname)+' '+LEFT(Mname,1)) AS cname, EmpID, Positn FROM tEmployee WHERE eRem='Active' ORDER BY Lname,Fname"); 
$PIS->execute([]);
$rslstemp = $PIS->resultset();

$PIS->query("SELECT brcode, brloc FROM lbbranch ORDER BY company, brloc"); 
$PIS->execute([]);
$rslstbranchname = $PIS->resultset();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $brloc; ?></title>
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/datepicker/dpicker.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.css">
    <link rel="stylesheet" href="vendor/material-icons/icons.css">
    <link rel="stylesheet" href="vendor/jquery/jquery-confirm.css">
</head>

<body class="bg-light mb-5">

<?php include('menu-start.php'); ?>

<div class="container-fluid">

<!-- START TITLE -->
<div class="row mt-2 mb-2">
    <div class="col-lg-12">
        <h3>Menu Creator</h3>
    </div>
</div>
<!-- END TITLE -->

<div class="row">
<div class="col-lg-12">

<div class="card shadow">
<div class="card-body">

<form name="disform" id="disform" method="POST" action="menu_creator_save.php">
<input type="hidden" id="msg" name="msg" value="<?php echo $msg; ?>" />

<div class="row">

    <div class="col-md-3">
        <div class="form-label-group">
			<label for="ename">Name</label>
			<input list="lstename" class="form-control req" id="ename" name="ename" value="" required />
			<datalist id="lstename">
			<?php
			foreach ($rslstemp as $row){
				$EmpID = $row->EmpID;
				$cname = $row->cname;
				$Positn = $row->Positn;
				echo "<option value='$cname' label='$EmpID' id='$Positn'></option>";
			}
			?>
			</datalist>
        </div>
    </div>

    <div class="col-md-1">
        <div class="form-label-group">
			<label for="empid">User ID</label>
			<input type="text" class="form-control req" id="empid" name="empid" placeholder="" value="" tabindex="-1" readonly required />
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-label-group">
			<label for="positn">Designation</label>
			<input type="text" class="form-control" id="positn" name="positn" placeholder="" value="" tabindex="-1" readonly />
        </div>
    </div>	

    <div class="col-md-3">
        <div class="form-label-group">
			<label for="branchname">Main Branch</label>
			<input list="lstbranchname" class="form-control req" id="branchname" name="branchname" value="" required />
			<datalist id="lstbranchname">
			<?php
			foreach ($rslstbranchname as $row){
				$cegBrCode = $row->brcode;
				$cegBrLoc = $row->brloc;
				echo "<option value='$cegBrLoc' label='$cegBrCode'></option>";
			}
			?>
			</datalist>
        </div>
    </div>	

    <div class="col-md-2">
        <div class="form-label-group">
			<label for="branchcode">Branch Code</label>
			<input type="text" class="form-control req" id="branchcode" name="branchcode" placeholder="" value="" tabindex="-1" readonly required />
        </div>
    </div>	

    <div class="col-lg-12">&nbsp;</div>

    <div class="col-lg-12">
    <div class="card mb-3">

    <div class="card-header bg-dark text-light"><i class="fa fa-list-ul"></i> <strong>MENU DETAILS</strong></div>

    <div class="card-body">

        <div class="row">

			<div id="tblmenu">
			
			<!-- check list -->
			
			</div>

        </div>

    </div>

	</div>
    </div>

	<div class="col-lg-12">
    <div class="card mb-3">

	<div class="card-header bg-dark text-light"><i class="fa fa-list-ul"></i> <strong>DIVISION TO ACCESS</strong></div>

	<div class="card-body">

		<div class="row">

			<div id="tblbranch">
			
			<!-- check list -->
			
			</div>

		</div>

	</div>

    </div>
    </div>
    
</div>

<div class="row">

    <div class="col-lg-12">
		<button type="button" class="btn btn-link" id="btnSelAll"><i class="fa fa-check"></i> Select All</button>
		<button type="button" class="btn btn-link" id="btnDesAll"><i class="fa fa-times"></i> Deselect All</button>  
		<button type="button" class="btn btn-link" id="btnSave"><i class="fa fa-save"></i> Save</button>
    </div>

</div>

</form>

</div>
</div>

</div>
</div>

</div>

<?php include('menu-end.php'); ?>

<!-- JS -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="vendor/jquery/jquery-confirm.js"></script>
<script src="vendor/datepicker/dpicker.js"></script>
<script text="text/javascript" src="main/js/menu.js"></script>
<script type='text/javascript'>

$(function() {

    $(window).on('load',function(){
		let	userid = "<?php echo $empid; ?>"

		$("#btnDesAll").hide();
		update_setting(userid); 
		getbranch(userid);
	}); 
	
	$("#ename").change(function(e) { 
		$("#empid").val(""); 
		$("#positn").val(""); 
		$("#branchcode").val(""); 
		$("#branchname").val("");
		var thisval = ($(this).val()).toUpperCase();

		$('#lstename option').each(function(i,e) {
		    var optval = ($(this).val()).toUpperCase();
			var optlbl = $(this).attr("label");
			var optid = $(this).attr("id");
			if (optval==thisval) { 
				$("#empid").val(optlbl); 
			   	$("#positn").val(optid); 
				update_setting(optlbl);
				getbranch(optlbl);
			}
		});
	}); 

	$("#branchname").blur(function(e) {
		$("#branchcode").val(""); 	
		var thisval = ($(this).val()).toUpperCase();

		$('#lstbranchname option').each(function(i,e) {
		    var optval = ($(this).val()).toUpperCase();
			var optlbl = $(this).attr("label");
			if (optval==thisval) { 
				$("#branchcode").val(optlbl);
			}
		});
	}); 	

	$("#btnSelAll").click(function(e) { 	
		$("#btnDesAll").show(); 
		$("#btnSelAll").hide();
		$(':checkbox').prop('checked', true);
	}); 
	
	$("#btnDesAll").click(function(e) { 
		$("#btnSelAll").show(); 
		$("#btnDesAll").hide();	
		$(':checkbox').prop('checked', false);
	});	

	$("#btnSave").click(function(e) { 
		let error_count = 0;
		//-- check  required inputs
		$(".req").each(function () {
			if ($(this).val().trim() === "") {
				error_count++;
				$(this).addClass('is-invalid');

			} else {
				$(this).removeClass('is-invalid');
				$(this).addClass('is-valid');
			}
		});
		if(error_count>0){ return; }
		
		$.confirm({
			title: 'Confirmation',
			icon: 'fa fa-question-circle',
			content: 'Are you sure you want to save this menu setting?',
			type: 'blue',
			theme: "modern",
			typeAnimated: true,
			buttons: {
				yes: function () { disform.submit(); },
				close: function () { return; }
			}
		});	
	});		

});

function update_setting(userid){
	$(':checkbox').prop('checked', false);
	
	$.post("menu_creator_ajax.php", { "trans":"menu", "userid": userid }, function (str1) {
		$("#tblmenu").html(str1);
	});

	$.post("menu_creator_ajax.php", { "trans":"divisiontoaccess", "userid": userid }, function (str2) {
		$("#tblbranch").html(str2);
	});

	getbranch(userid);
}

function getbranch(userid){
	$.post("menu_creator_ajax.php", { "trans":"branch", "userid": userid }, function (str) {
		let chk = str.search("error");
		if(chk>0){ return; }

		if(str.length>0){
			let arrdetails = JSON.parse(str);
			for (let ix=0; ix<arrdetails.length; ix++){
				let disbrcode = arrdetails[ix]["brcode"];
				let disbrloc = arrdetails[ix]["brloc"];

				$("#branchcode").val(disbrcode); 
				$("#branchname").val(disbrloc);
			}
		}
	});
}

</script>

</body>

</html>