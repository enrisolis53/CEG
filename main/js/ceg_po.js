//-- Created for CEG
arr = [];
//--
function listOfItem() {
    let str = "";

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            str += "<tr>";
            str += "<td><button type='button' class='btn btn-link' id='btnSelect' onclick='selectme(\"" + arr[i]["transno"] + "\")' title='Select option number to view request to purchase details' style='font-size: 14px; margin-top: -7px;'>" + (i+1) + "</i></button></td>";
            str += "<td>" + arr[i]["branch"] + "</td>";
            str += "<td>" + arr[i]["transno"] + "</td>";
            str += "<td>" + arr[i]["transdate"] + "</td>";
            str += "<td>" + arr[i]["proj_name"] + "</td>";
            str += "<td>" + arr[i]["remarks"] + "</td>";
            str += "</tr>";
        }

        $("#listofitem").html(str);
    }
}

function edit() {
    let brcode = $("#brcode").val();
    let transno = $("#transno").val();

    disform.action = 'ceg_po.php?brcode='+brcode+'&transno='+transno+'&rpno=';
    disform.submit();
}

function reprint() {
    let brcode = $("#brcode").val();
    let transno = $("#transno").val();

    if (brcode.length <= 0) {
        $.alert({
            title: 'Notice',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please select branch name and try again!',
            type: 'red',
            theme: "modern",
            typeAnimated: true,
            buttons: {
                close: function () {}
            }
        });
        return;
    }

    if (transno.length <= 0) {
        $.alert({
            title: 'Notice',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please enter transaction number and try again!',
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
    window.open("ceg_po_print.php?brcode=" + brcode + "&transno=" + transno, "RP PDF", "height=500, width=800, left=10, top=10");
}

function selectme(transno) {
    let brcode = $("#brcode").val();
    
    disform.action = 'ceg_po.php?brcode='+brcode+'&transno=&rpno='+transno;
    disform.submit();
}

function clearform() {
    $("#brcode").val("");
    arr = [];
    listOfItem();
}

function loaddata(arrdata){
    let itemcode, itemdescrip, units = "";
    let quantity, ucost, tcost = 0;
    let arrdetails = JSON.parse(arrdata);

    if (arrdata.length > 0) {
        for (let i = 0; i < arrdetails.length; i++) {
            itemcode = arrdetails[i]["itemcode"];
            itemdescrip = arrdetails[i]["descrip"];
            units = arrdetails[i]["uom"];
            quantity = arrdetails[i]["bal"];
            ucost = arrdetails[i]["ucost"];
            tcost = arrdetails[i]["tcost"];

            if ((arr.filter((item) => item.itemcode == itemcode)).length <= 0) {
                arr.push({
                    "itemcode": itemcode,
                    "itemdescrip": itemdescrip,
                    "units": units,
                    "quantity": quantity,
                    "ucost": ucost,
                    "tcost": tcost
                });
            }
        }
    }
    listPO();
}

function listPO(){
    let discount = ($("#discount").val()=="")?0:parseFloat($("#discount").val());
    let downpayment = ($("#downpayment").val()=="")?0:parseFloat($("#downpayment").val());
    let addpaymentamt = ($("#addpaymentamt").val()=="")?0:parseFloat($("#addpaymentamt").val());
    let str = "";
    let ctr = totalpo = 0;

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            ctr ++;

            str += "<tr>";
            str += "<td align='left'><span style='cursor:pointer; color:#f00;' onclick='removeme(\"" + arr[i]["itemcode"] + "\");' data-toggle='tooltip' data-html='true' title='Remove this item?'>&nbsp;&nbsp;&nbsp;" + ctr + "</span></td>";
            str += "<td>" + arr[i]["itemcode"] + "</td>";
            str += "<td>" + arr[i]["itemdescrip"] + "</td>";
            str += "<td align='center'>" + arr[i]["units"] + "</td>";
            str += "<td align='right'><input type='number' class='form-control' id='quantity' name='quantity' value=\"" + arr[i]["quantity"] +"\" style='text-align: right' maxlength='20 size='20' onchange='updateqty(\"" + arr[i]["itemcode"] + "\", this.value);' /></td>";
            str += "<td align='right'><input type='number' class='form-control' id='ucost' name='ucost' value=\"" + arr[i]["ucost"] +"\" style='text-align: right' maxlength='20 size='20' onchange='updateucost(\"" + arr[i]["itemcode"] + "\", this.value);' /></td>";
            str += "<td align='center'>" + arr[i]["tcost"] + "</td>";
            str += "</tr>";

            totalpo+=parseFloat(arr[i]["tcost"]);
        }

        $("#listofitem").html(str);

        totalpo=parseFloat((totalpo+addpaymentamt)-(discount+downpayment));
        $("#totalpo").val(totalpo);
    }
}

function updateqty(itemcode, val) {
    for (let i = 0; i < arr.length; i++) {
        if (arr[i]["itemcode"] == itemcode) {
            arr[i]["quantity"]=val;
            break;
        }
    }
    computeTotalCost();
}

function updateucost(itemcode, val) {
    for (let i = 0; i < arr.length; i++) {
        if (arr[i]["itemcode"] == itemcode) {
            arr[i]["ucost"]=val;
            break;
        }
    }
    computeTotalCost();
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
                listPO();
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
                disform.action = 'ceg_po_save.php';
                disform.submit();
            },
            close: function () {}
        }
    });
}

function computeTotalCost() {
    let quantity, ucost, tcost = 0;

    for (let i = 0; i < arr.length; i++) {
        quantity = (arr[i]["quantity"]=="")?0:parseFloat(arr[i]["quantity"]);
        ucost = (arr[i]["ucost"]=="")?0:parseFloat(arr[i]["ucost"]);
        tcost = parseFloat(quantity*ucost);
        arr[i]["tcost"]=tcost;
        totalpo+=tcost;
    }

    listPO();
}