//-- Created for CEG
arr = [];
//--
function listOfItem() {
    let str = "";

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            str += "<tr>";
            str += "<td><button type='button' id='btnSelect' onclick='selectme(\"" + arr[i]["transno"] + "\")' title='Select option number to view billing material details'>" + (i+1) + "</i></button></td>";
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
    
    disform.action = 'ceg_rp.php?brcode='+brcode+'&transno='+transno;
    disform.submit();
}