<?php
session_start();
include('classes/Database.class.php');

$msg = !isset($_GET["msg"]) ? '' : trim($_GET["msg"]);
$empid = $_SESSION['ceg_empid'];
$brcode = $_SESSION["ceg_brcode"];

$DB = new classes\Database;

$DB->query('SELECT DISTINCT brcode, brloc FROM lib_access_accounts WHERE empid=? ORDER BY brloc'); 
$DB->execute([$empid]);
$rslstbranchname = $DB->resultset();

$DB->query('SELECT DISTINCT proj_id, proj_name FROM tbl_proj_profile ORDER BY proj_name');
$DB->execute([]);
$rslstprojectname = $DB->resultset();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Upload Files</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
                <h3>Upload Files</h3>
            </div>
        </div>
        <!-- END TITLE -->

        <div class="row">
            <div class="col-lg-12">

                <div class="card shadow">
                    <div class="card-body">

                        <form name="disform" id="disform" method="POST" enctype="multipart/form-data" action="ceg_uploadsave.php">
                            <input type="hidden" id="msg" name="msg" value="<?php echo $msg; ?>" />
                            <input type="hidden" id="prepby" name="prepby" value="<?php echo $username; ?>" />

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-label-group">
                                        <label for="branch">Branch Name</label>
                                        <input list="lstbranchname" class="form-control req" id="branchname" name="branchname" value="" />
                                        <datalist id="lstbranchname">
                                        <?php
                                        foreach ($rslstbranchname as $row){
                                            $xbrcode = trim($row->brcode);
                                            $xbrloc = trim($row->brloc);
                                            echo "<option value='$xbrloc' label='$xbrcode'></option>";
                                        }
                                        ?>
                                        </datalist>
                                        <input type="hidden" id="brcode" name="brcode" value="" />
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6 mt-3">
                                    <div class="form-label-group">
                                        <label for="proj_name">Project Name</label>
                                        <input list="lstprojname" class="form-control req" id="proj_name" name="proj_name" value="" />
                                        <datalist id="lstprojname"></datalist>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <div class="form-label-group">
                                        <label for="proj_id">Project ID</label>
                                        <input type="text" class="form-control" id="proj_id" name="proj_id" placeholder="" value="" tabindex="-1" readonly />
                                    </div>
                                </div>                                

                            </div>

                            <div class="row">
                                
                                <div class="col-md-6 mt-3">
                                    <div class="form-label-group">
                                        <label for="remarks">Remarks</label>
                                        <input type="text" class="form-control" id="remarks" name="remarks" value="" />
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6 mt-3">
                                    <div class="form-label-group">
                                        <label for="fileToUpload">File to upload</label>
                                        <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" accept="" />
                                    </div>
                                </div>   
                                <div class="col-md-3 mt-4">
                                    <div class="form-label-group">
                                        <br/><button type="button" class="btn btn-link" id="btnExecute"><i class="fa fa-check"></i> Execute</button>
                                    </div>
                                </div>                     

                            </div>

                            <div class="row">
                                <div class="col-md-12 mt-4">
                                    <div class="card mb-3">
                                        <div class="card-header bg-dark text-light"><i class="fa fa-list"></i> <strong>FILE LIST</strong></div>
                                        <div class="card-body">

                                            <div class="row">

                                                <div class="cards">
                                                    <div class="cards-body">
                                                        <div class="responsive-table">

                                                            <table class="table table-striped table-sm">
                                                                <thead class="bg-light text-secondary">
                                                                    <tr>
                                                                        <th class="text-center">No.</th>
                                                                        <th class="text-center">ID</th>
                                                                        <th>Name</th>
                                                                        <th>Uploaded By</th>
                                                                        <th>Date</th>
                                                                        <th>Remarks</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="listofitem">
                                                                    &nbsp;
                                                                </tbody>
                                                            </table>

                                                        </div>
                                                    </div>
                                                </div>                                              

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>  

                            <div id='mytable'>&nbsp;</div>  

                        </form>

                    </div>
                </div>

            </div>
            
        </div>

    </div>

    <!-- Modal Save -->
    <div class="modal hide fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Notice</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p><?php echo $msg; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('menu-end.php'); ?>

    <datalist id="lstprojname1">
    <?php
    foreach ($rslstprojectname as $row){
        $xproj_id = trim($row->proj_id);
        $xproj_name = strtoupper(trim($row->proj_name));
        echo "<option value='$xproj_name' label='$xproj_id'></option>";
    }
    ?>
    </datalist>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/jquery/jquery-confirm.js"></script>
    <script text="text/javascript" src="main/js/menu.js"></script>
    <script type='text/javascript'>

    arr = [];

    $(function() {

        $(window).on('load', function() {
            let msg = $("#msg").val();
            if (msg != "") {
                $('#myModal').modal('show');
                clear_content();
            }
            return;
        });

        $("#branchname").blur(function(e) {
            $("#brcode").val(""); 	
            $("#proj_name").val('');
            $("#proj_id").val('');
            $("#remarks").val('');
            $("#fileToUpload").val('');
            clear_content();

            var thisval = ($(this).val()).toUpperCase();

            $('#lstbranchname option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#brcode").val(optlbl); 
                }
            });            
        });

        $("#proj_name").keyup(function(e) {;
            var discnt = 0;
            var disval = $(this).val().toUpperCase();
            var dislen = disval.length;
            $('#lstprojname').html('');
            $('#lstprojname1 option').each(function (i, e) {
                var optval = $(this).val().toUpperCase().substr(0, dislen);
                if (disval == optval) {
                    if (discnt == 10) {
                        Debug.Break();
                    }
                    $('#lstprojname').append('<option value=\"' + $(this).val() + '\" label=\"' + $(this).attr('label') + '\">');
                    discnt++;
                }
            });
        });

        $("#proj_name").blur(function(e) {
            var thisval = ($(this).val()).toUpperCase();
            $("#proj_id").val("");
            clear_content();

            $('#lstprojname option').each(function(i,e) {
                var optval = ($(this).val()).toUpperCase();
                var optlbl = $(this).attr("label");
                if (optval==thisval) { 
                    $("#proj_name").val(optval); 
                    $("#proj_id").val(optlbl);
                }
            });  
            getuploadedfiles();
        });
  
        $("#btnExecute").click(function(e) {
            var fileToUpload = $("#fileToUpload").val();

            document.getElementById("mytable").innerHTML = "<img src='/../images/loader.gif' height='32' width='32' alt='loading' /><br /><br /><font face='arial' size=1 color='#00890F'><b>Uploading, Please wait...</b></font>";

            if (fileToUpload=='') {
                $.alert({
                    title: 'Message',
                    icon: 'fa fa-exclamation-triangle',
                    content: 'Please choose a file',
                    type: 'blue',
                    theme: "modern",
                    typeAnimated: true,
                    buttons: {
                        close: function () { document.getElementById("mytable").innerHTML = "&nbsp;"; }
                    }
                });
                return;
            }

            $.alert({
                title: 'Message',
                icon: 'fa fa-question-circle',
                content: 'Do you really wish to upload this file',
                type: 'red',
                theme: "modern",
                typeAnimated: true,
                buttons: {
                    yes: function () {
                        disform.submit();
                    },
                    close: function () { document.getElementById("mytable").innerHTML = "&nbsp;"; return; }
                }
            });                        
        });        	
    }); 

    function getuploadedfiles(){
        let brcode = $("#brcode").val();
        let proj_id = $("#proj_id").val();

        //-- get list of uploaded file under this project
        $.post("ceg_ajax.php", {
            "brcode": brcode,
            "proj_id": proj_id,
            "trans": "getuploadedfiles" 
        }, function (str) {
            //-- load data to form
            if (str.length > 0) {
                let arrdetails = JSON.parse(str);
                let file_uploaded_id = ""; 
                let file_uploaded_name = "";
                let uploaded_by = "";
                let uploaded_date = "";
                let remarks = "";
                
                for (let ix = 0; ix < arrdetails.length; ix++) {                            
                    file_uploaded_id = arrdetails[ix]["file_uploaded_id"];
                    file_uploaded_name = arrdetails[ix]["file_uploaded_name"];
                    uploaded_by = arrdetails[ix]["uploaded_by"];
                    uploaded_date = arrdetails[ix]["uploaded_date"];
                    remarks = arrdetails[ix]["remarks"];
                        
                    //-- Insert records to array
                    if ((arr.filter((item) => item.file_uploaded_id == file_uploaded_id)).length <= 0) {
                        arr.push({
                            "file_uploaded_id": file_uploaded_id,
                            "file_uploaded_name": file_uploaded_name,
                            "uploaded_by": uploaded_by,
                            "uploaded_date": uploaded_date,
                            "remarks": remarks
                        });
                    }
                }

                inituploadedfiles();
            }
            else{
                document.getElementById("mytable").innerHTML = "<b><font face='arial' size=1 color='red'>Loading Failed!</font></b>"; 
                window.setTimeout(function() {
                    document.getElementById("mytable").innerHTML = "&nbsp;"; 
                    return false;
                }, 3000);
            }
        });            
    }

    function inituploadedfiles(){
        let str = "";
        let ctr = 0;

        if (arr.length > 0) {
            for (let i = 0  ; i < arr.length; i++) {
                ctr ++;
                str += "<tr>";
                str += "<td align='center'>" + ctr + "</td>";
                str += "<td align='center'>" + arr[i]["file_uploaded_id"] + "</td>";
                str += "<td>" + arr[i]["file_uploaded_name"] + "</td>";
                str += "<td>" + arr[i]["uploaded_by"] + "</td>";
                str += "<td>" + arr[i]["uploaded_date"] + "</td>";
                str += "<td>" + arr[i]["remarks"] + "</td>";
                str += "</tr>";
            }
        }

        document.getElementById("listofitem").innerHTML = str;
    }

    function clear_content(){
        $("#msg").val('');
        arr = [];
        document.getElementById("mytable").innerHTML = "&nbsp;"; 
        document.getElementById("listofitem").innerHTML = "&nbsp;";
    }

    </script>

</body>
</html>