//-- Created for CEG
arr = [];
//--
Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "," : d,
        t = t == undefined ? "." : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function isNumberKey(evt) {
    let keycode = (evt.which) ? evt.which : event.keycode;
    if ((keycode == 46) || (keycode >= 48 && keycode <= 57)) return true;
    else return false;
}

function InsertItem() {
    let transtype = $("#transtype").val();
    let docnumber = $("#docnumber").val();
    let conumber = $("#conumber").val();
    let contnumber = $("#contnumber").val();
    let docdate = $("#docdate").val();
    let cname = $("#cname").val();
    let remarks = $("#remarks").val();
    let idx = transtype+'-'+docnumber;
    let posted = $("#posted").val();

    if(posted>0){
        $.confirm({
            title: 'Notice',
            icon: 'fa fa-exclamation-triangle',
            content: 'Billing materials already posted',
            type: 'red',
            theme: "modern",
            typeAnimated: true,
            buttons: {
                 close: function () { }
            }
        });
        return;
    }

    if (transtype.length <= 0 || docnumber.length <= 0|| docnumber.cname <= 0) {
        return;
    }

    //-- Check if exists
    for (let i = 0; i < arr.length; i++) {
        if (arr[i] == transtype && arr[i] == docnumber) {
            $("#transtype").val(""),
            $("#docnumber").val(""),
            $("#conumber").val(""),
            $("#contnumber").val(""),
            $("#docdate").val(""),
            $("#cname").val(""),
            $("#remarks").val("");
            return;
        }
    }
    //-- Insert records to array
    if ((arr.filter((item) => item.idx == idx)).length <= 0) {
        arr.push({
            "idx": idx,
            "transtype": transtype,
            "docnumber": docnumber,
            "conumber": conumber,
            "contnumber": contnumber,
            "docdate": docdate,
            "cname": cname,
            "remarks": remarks,
            "posted": "0"
        });
    }

    //$("#transtype").val("");
    $("#docnumber").val("");
    $("#conumber").val("");
    $("#contnumber").val("");
    $("#docdate").val("");
    $("#cname").val("");
    $("#remarks").val("");
    listOfItem();
}

function updateme(id,val) {
    for (let i = 0; i < arr.length; i++) {
       if (arr[i].idx == id) {
           arr[i].remarks = val;
           break;
       }
    }
    return;
}

function removeme(idx) {
    $.confirm({
        title: 'Confirmation',
        icon: 'fa fa-question-circle',
        content: 'Are you sure you want to delete this record?',
        type: 'red',
        theme: "modern",
        typeAnimated: true,
        buttons: {
            yes: function () {
                for (let i = 0; i < arr.length; i++) {
                    if (arr[i]["idx"] == idx) {
                        arr.splice(i, 1);
                        break;
                    }
                }
                listOfItem();
            },
            close: function () {}
        }
    });
}

// function listOfItem() {
//     let str = "";
//     let ctr = 0;
//     let posted = 0;

//     $("#listofitem").html("");

//     if (arr.length > 0) {
//         for (let i = 0; i < arr.length; i++) {
//             ctr ++;
//             posted = parseInt(arr[i]["posted"]);

//             str += "<tr>";
//             if(posted==0){
//                 str += "<td align='center'><span style='cursor:pointer; color:#f00;' onclick='removeme(\"" + arr[i]["idx"] + "\");' data-toggle='tooltip' data-html='true' title='Remove this record?'>" + ctr + "</span></td>";
//             } else {
//                 $("#posted").val("1");
//                 str += "<td align='center'>" + ctr + "</td>";
//             }
//             str += "<td>" + arr[i]["transtype"] + "</td>";
//             str += "<td align='center'>" + arr[i]["conumber"] + "</td>";
//             str += "<td align='center'>" + arr[i]["contnumber"] + "</td>";
//             str += "<td align='center'>" + arr[i]["docdate"] + "</td>";
//             str += "<td>" + arr[i]["cname"] + "</td>";
//             if(posted==0){
//                 str += "<td><input type='text' id='" + arr[i]["idx"] + "' name='" + arr[i]["idx"] + "' value='" + arr[i]["remarks"] + "' onchange='updateme(this.id,this.value);' style='text-align:left; width:300px; background-color: transparent; border: 0px solid;' /></td>";
//             } else {
//                 str += "<td>" + arr[i]["remarks"] + "</td>";
//             }
//             str += "</tr>";
//         }

//         $("#listofitem").html(str);
//     }
// }

// function reprint() {
//     let brcode = $("#brcode").val();
//     let transno = $("#transno").val();

//     if (transno.length <= 0) {
//         $.alert({
//             title: 'No Transmittal Number',
//             icon: 'fa fa-exclamation-triangle',
//             content: 'Please enter transmittal number and try again!',
//             type: 'red',
//             theme: "modern",
//             typeAnimated: true,
//             buttons: {
//                 close: function () {}
//             }
//         });
//         return;
//     }
//     $("#transno").val("");
//     clearform();
//     window.open("docu_createlist_print1.php?brcode=" + brcode + "&transno=" + transno, "CONTRACT TRANSMITTAL PDF", "height=500, width=800, left=10, top=10");
//     window.open("docu_createlist_print2.php?brcode=" + brcode + "&transno=" + transno, "CO TRANSMITTAL PDF", "height=500, width=800, left=20, top=20");
// }

// function transmit() {
//     let brcode = $("#brcode").val();
//     let transno = $("#transno").val();

//     if (transno.length <= 0) {
//         $.alert({
//             title: 'No Transmittal Number',
//             icon: 'fa fa-exclamation-triangle',
//             content: 'Please enter transmittal number and try again!',
//             type: 'red',
//             theme: "modern",
//             typeAnimated: true,
//             buttons: {
//                 close: function () {}
//             }
//         });
//         return;
//     }

//     $.post("docu_transmittal_ajax.php", {
//         "brcode": brcode,
//         "transtype": "",
//         "docnumber": transno,
//         "trans": "transmitme" 
//     }, function (str) {
//         //-- check data
//         if (str.length > 0) {
//             $.alert({
//                 title: 'Notice',
//                 icon: 'fa fa-exclamation-triangle',
//                 content: str,
//                 type: 'blue',
//                 theme: "modern",
//                 typeAnimated: true,
//                 buttons: {
//                     close: function () {}
//                 }
//             });
//             window.open("docu_createlist_print1.php?brcode=" + brcode + "&transno=" + transno, "CONTRACT TRANSMITTAL PDF", "height=500, width=800, left=10, top=10");
//             window.open("docu_createlist_print2.php?brcode=" + brcode + "&transno=" + transno, "CO TRANSMITTAL PDF", "height=500, width=800, left=20, top=20");
//             return;
//         }
//     });

//     $("#transno").val("");
//     clearform();
// }

// function clearform() {
//     arr = [];
//     listOfItem();
//     $("#transdata").val("");
//     $("#posted").val("0");
// }

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
            title: 'Billing list is empty',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please add billing details on the list and try again!',
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
                disform.action = 'ceg_billing_materials_save.php';
                disform.submit();
            },
            close: function () {}
        }
    });
}