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
            str += "<td align='left'><span style='cursor:pointer; color:#f00;' onclick='removeme(\"" + arr[i]["itemcode"] + "\");' data-toggle='tooltip' data-html='true' title='Remove this item?'>&nbsp;&nbsp;&nbsp;" + ctr + "</span></td>";
            str += "<td>" + arr[i]["itemcode"] + "</td>";
            str += "<td>" + arr[i]["itemdescrip"] + "</td>";
            str += "<td align='left'>" + arr[i]["units"] + "</td>";
            str += "<td align='center'>" + arr[i]["quantity"] + "</td>";
            str += "</tr>";
        }

        $("#listofitem").html(str);
    }
}

function getpreviewsdata() {
    let brcode = $("#brcode").val();
    let transno = $("#transno").val();
    clearform();
  
    if (brcode.length <= 0 && transno.length <= 0) return;

    $.post(
      "ceg_ajax.php", {
        trans: "getpreviewsdata",
        brcode: brcode,
        transno: transno
      },
      function (str) {
        let chk = str.search("error");
  
        if (chk > 0) {
          $.alert({
            title: "Invalid",
            icon: "fa fa-exclamation-triangle",
            content: "No record found please try again!",
            type: "red",
            theme: "modern",
            typeAnimated: true,
            buttons: {
              close: function () {
                clearform();
              },
            },
          });
          return;
        }
  
        //-- load to form
        if (str.length > 0) {
          let arrdetails = str.split("^");
  
          let arrhead = JSON.parse(arrdetails[0]);
          for (let ix = 0; ix < arrhead.length; ix++) {
            
            if((arrhead[ix]["posted"])==1){ 
                $.alert({
                    title: "Invalid",
                    icon: "fa fa-exclamation-triangle",
                    content: "Record already posted!",
                    type: "red",
                    theme: "modern",
                    typeAnimated: true,
                    buttons: {
                        close: function () {
                            clearform();
                        },
                    },
                });
                return;  
            }

            $("#transno").val(transno);
            $("#transdate").val(arrhead[ix]["transdate"]);
            $("#proj_name").val(arrhead[ix]["proj_name"]);
            $("#proj_id").val(arrhead[ix]["proj_id"]);
            $("#remarks").val(arrhead[ix]["remarks"]);
            $("#prepby").val(arrhead[ix]["preparedby"]);
            $("#prepbypos").val(arrhead[ix]["preparedpos"]);
          }
  
          let arrbody = JSON.parse(arrdetails[1]);
          for (let ix = 0; ix < arrbody.length; ix++) {
            let xitemcode = arrbody[ix]["itemcode"];
            let xitemdesc = arrbody[ix]["itemdescrip"];
            let xunits = arrbody[ix]["units"];
            let xquantity = parseFloat(arrbody[ix]["quantity"]);
            
            arr.push({
              "itemcode": xitemcode,
              "itemdescrip": xitemdesc,
              "units": xunits,
              "quantity": xquantity
            });
          }
          listOfItem();
        }
      }
    );
}

function materials_posting() {
    let str = "";

    $("#listofitem").html("");

    if (arr.length > 0) {
        for (let i = 0; i < arr.length; i++) {
            str += "<tr>";
            str += "<td>" + arr[i]["transno"] + "</span></td>";
            str += "<td>" + arr[i]["transdate"] + "</td>";
            str += "<td>" + arr[i]["proj_name"] + "</td>";
            str += "<td>" + arr[i]["remarks"] + "</td>";
            str += "<td><input type='checkbox' name='" + i + "' id='" + i + "' tabindex='-1' /></td>";
            str += "</tr>";
        }
        str += "<tr>";
        str += "<td colspan='5' align='right'><button type='button' class='btn btn-link' onclick='uncheckall()'><i class='fa fa-times'></i> Unselect All</button><button type='button' class='btn btn-link' onclick='checkall()'><i class='fa fa-check'></i> Select All</button></td>";
        str += "</tr>";

        $("#listofitem").html(str);
    }
}

function clearform() {
    $("#transno").val('');
    $("#transdate").val($("#today").val());
    $("#proj_name").val('');
    $("#proj_id").val('');
    $("#remarks").val('');
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

function validate_posting(n){
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
            title: 'Check box list is empty',
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

    if ($(':checkbox:checked').length < 1) {
        $.alert({
            title: 'No selected',
            icon: 'fa fa-exclamation-triangle',
            content: 'Please select item from billing details and try again!',
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
    $("#status").val(n);

    $.confirm({
        title: 'Confirmation',
        icon: 'fa fa-question-circle',
        content: 'Are you sure you want to post this transaction?',
        type: 'blue',
        theme: "modern",
        typeAnimated: true,
        buttons: {
            yes: function () {
                disform.action = 'ceg_billing_materials_post.php';
                disform.submit();
            },
            close: function () {}
        }
    });
}

function reprint() {
    let brcode = $("#brcode").val();
    if (brcode.length<=0) return;

    var transno = prompt("Please enter billing materials number"); 
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
    window.open("ceg_billing_materials_print.php?brcode=" + brcode + "&transno=" + transno, "BILLING MATERIALS PDF", "height=500, width=800, left=10, top=10");
}

function checkall() {
    if (arr.length > 0) {
        $(':checkbox').prop('checked', true); 
    }
}

function uncheckall() {
    if (arr.length > 0) {
        $(':checkbox').prop('checked', false);
    }
}