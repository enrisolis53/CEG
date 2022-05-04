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
    let itemcode = $("#itemcode").val();
    let itemdescrip = $("#itemdescrip").val();
    let units = $("#units").val();
    let quantity = $("#quantity").val();

    if (itemcode.length <= 0 || quantity.length <= 0) {
        return;
    }

    //-- Check if exists
    for (let i = 0; i < arr.length; i++) {
        if (arr[i] == itemcode) {
            $("#itemcode").val(""),
            $("#itemdescrip").val(""),
            $("#units").val(""),
            $("#quantity").val("")
            return;
        }
    }
    //-- Insert records to array
    if ((arr.filter((item) => item.itemcode == itemcode)).length <= 0) {
        arr.push({
            "itemcode": itemcode,
            "itemdescrip": itemdescrip,
            "units": units,
            "quantity": quantity
        });
    }

    $("#itemcode").val("");
    $("#itemdescrip").val("");
    $("#units").val("");
    $("#quantity").val("");
    listOfItem();
}

function updateme(id,val) {
    for (let i = 0; i < arr.length; i++) {
       if (arr[i].itemcode == id) {
           arr[i].remarks = val;
           break;
       }
    }
    return;
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
                listOfItem();
            },
            close: function () {}
        }
    });
}

function listOfItem() {
    let str = "";
    let ctr = 0;

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            ctr ++;

            str += "<tr>";
            str += "<td align='center'><span style='cursor:pointer; color:#f00;' onclick='removeme(\"" + arr[i]["itemcode"] + "\");' data-toggle='tooltip' data-html='true' title='Remove this item?'>" + ctr + "</span></td>";
            str += "<td>" + arr[i]["itemcode"] + "</td>";
            str += "<td>" + arr[i]["itemdescrip"] + "</td>";
            str += "<td align='center'>" + arr[i]["units"] + "</td>";
            str += "<td align='center'>" + arr[i]["quantity"] + "</td>";
            str += "</tr>";
        }

        $("#listofitem").html(str);
    }
}

function clearform() {
    arr = [];
    $("#transdata").val("");
    listOfItem();
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
            title: 'Billing list is empty',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please add item details on the list and try again!',
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