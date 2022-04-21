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

function reprint() {
    // let brcode = $("#brcode").val();
    // let transno = $("#transno").val();

    // if (transno.length <= 0) {
    //     $.alert({
    //         title: 'No Transmittal Number',
    //         icon: 'fa fa-exclamation-triangle',
    //         content: 'Please enter transmittal number and try again!',
    //         type: 'red',
    //         theme: "modern",
    //         typeAnimated: true,
    //         buttons: {
    //             close: function () {}
    //         }
    //     });
    //     return;
    // }
    // $("#transno").val("");
    // clearform();
    // window.open("docu_createlist_print1.php?brcode=" + brcode + "&transno=" + transno, "CONTRACT TRANSMITTAL PDF", "height=500, width=800, left=10, top=10");
}

function clearform() {
    let today = $("#today").val();
    $("#transno").val("");
    $("#transdate").val(today);
    $("#proj_id").val("");
    $("#proj_name").val("");
    $("#datefrom").val("");
    $("#dateto").val("");
    $("#projcost").val("");
    $("#particulars").val("");
    $("#remarks").val("");
    $("#file_uploaded_id").val("");
    $("#fileToUpload").val("");
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

    $.confirm({
        title: 'Confirmation',
        icon: 'fa fa-question-circle',
        content: 'Are you sure you want to save this project?',
        type: 'blue',
        theme: "modern",
        typeAnimated: true,
        buttons: {
            yes: function () {
                disform.action = 'ceg_createproject_save.php';
                disform.submit();
            },
            close: function () {}
        }
    });
}