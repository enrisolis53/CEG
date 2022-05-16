//-- Created for CEG
arr = [];
//--
function listOfItem() {
    let str = "";

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            str += "<tr>";
            str += "<td><button type='button' class='btn btn-link' id='btnSelect' onclick='selectme(\"" + arr[i]["transno"] + "\")' title='Select option number to view billing material details' style='font-size: 14px; margin-top: -7px;'>" + (i+1) + "</i></button></td>";
            str += "<td>" + arr[i]["transno"] + "</td>";
            str += "<td>" + arr[i]["transdate"] + "</td>";
            str += "<td>" + arr[i]["proj_name"] + "</td>";
            str += "<td>" + arr[i]["remarks"] + "</td>";
            str += "</tr>";
        }

        $("#listofitem").html(str);
    }
}

function reprint() {
    let brcode = $("#brcode").val();
    let transno = $("#transno").val();

    if (transno.length <= 0) {
        $.alert({
            title: 'No Transaction  Number',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please enter Transaction number and try again!',
            type: 'red',
            theme: "modern",
            typeAnimated: true,
            buttons: {
                close: function () {}
            }
        });
        return;
    }
    $("#transno").val("");
    clearform();
    window.open("ceg_rp_print.php?brcode=" + brcode + "&transno=" + transno, "RP PDF", "height=500, width=800, left=10, top=10");
}

function selectme(transno) {
    let brcode = $("#brcode").val();
    
    disform.action = 'ceg_rp.php?brcode='+brcode+'&transno=&bmno='+transno;
    disform.submit();
}

function clearform() {
    $("#brcode").val("");
    arr = [];
    listOfItem();
}


function loaddata(arrdata){
    let itemcode, itemdescrip, units = "";
    let quantity = 0;
    let arrdetails = JSON.parse(arrdata);

    if (arrdata.length > 0) {
        for (let i = 0; i < arrdetails.length; i++) {
            itemcode = arrdetails[i]["itemcode"];
            itemdescrip = arrdetails[i]["descrip"];
            units = arrdetails[i]["uom"];
            quantity = arrdetails[i]["bal"];

            if ((arr.filter((item) => item.itemcode == itemcode)).length <= 0) {
                arr.push({
                    "itemcode": itemcode,
                    "itemdescrip": itemdescrip,
                    "units": units,
                    "quantity": quantity
                });
            }
        }
    }
    listRP();
}

function listRP(){
    let str = "";
    let ctr = 0;

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            ctr ++;

            str += "<tr>";
            str += "<td align='left'><span style='cursor:pointer; color:#f00;' onclick='removeme(\"" + arr[i]["itemcode"] + "\");' data-toggle='tooltip' data-html='true' title='Remove this item?'>&nbsp;&nbsp;&nbsp;" + ctr + "</span></td>";
            str += "<td>" + arr[i]["itemcode"] + "</td>";
            str += "<td>" + arr[i]["itemdescrip"] + "</td>";
            str += "<td align='center'>" + arr[i]["units"] + "</td>";
            str += "<td align='right'><input type='number' class='form-control' id='quantity' name='quantity' value=\"" + arr[i]["quantity"] +"\" style='text-align: right' maxlength='20 size='20' onchange='updateme(\"" + arr[i]["itemcode"] + "\", this.value);' /></td>";
            str += "</tr>";
        }

        $("#listofitem").html(str);
    }
}

function updateme(itemcode, val) {
    for (let i = 0; i < arr.length; i++) {
        if (arr[i]["itemcode"] == itemcode) {
            arr[i]["quantity"]=val;
            break;
        }
    }
}

function removeme(itemcode) {
    $.confirm({
        title: 'Confirmation',
        icon: 'fa fa-question-circle',
        content: 'Are you sure you want to delete this item?',
        type: 'red',
        theme: "modern",
        typeAnimated: true,
        buttons: {
            yes: function () {
                for (let i = 0; i < arr.length; i++) {
                    if (arr[i]["itemcode"] == itemcode) {
                        arr.splice(i, 1);
                        break;
                    }
                }
                listRP();
            },
            close: function () {}
        }
    });
}

function validate() {

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

    if (error_count > 0) {
        return;
    }

    if (arr.length <= 0) {
        $.alert({
            title: 'Item list is empty',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please check your entry and try again!',
            type: 'red',
            theme: "modern",
            typeAnimated: true,
            buttons: {
                close: function () {}
            }
        });
        return;
    }

    $("#transdata").val(JSON.stringify(arr));

    $.confirm({
        title: 'Confirmation',
        icon: 'fa fa-question-circle',
        content: 'Are you sure you want to save this transaction?',
        type: 'blue',
        theme: "modern",
        typeAnimated: true,
        buttons: {
            yes: function () {
                disform.action = 'ceg_rp_save.php';
                disform.submit();
            },
            close: function () {}
        }
    });
}