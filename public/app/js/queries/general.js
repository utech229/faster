"use strict";

var pCondition = 'true';
$(document).on('onAjaxError', function(event) 
{
    Swal.fire({
        text: _AjaxError,
        icon: 'error',
        buttonsStyling: !1,
        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
        customClass: {
            confirmButton: "btn btn-primary"
        }
    })
});

$(document).on('onFormError', function(e) 
{
    toastr.error(_Form_Error_Swal);
});




$(document).on('onAjaxInfo', function(event) 
{
    Swal.fire({
        text: _AjaxNoTerminatedOperation,
        icon: 'error',
        buttonsStyling: !1,
        confirmButtonText: _Form_Ok_Swal_Button_Text_Notification,
        customClass: {
            confirmButton: "btn btn-primary"
        }
    })
});

$(document).on('toastr.onAjaxError', function(event) 
{
    toastr.error(_AjaxError);
});

$(document).on('toastr.tableListError', function(event) 
{
    toastr.error(_AjaxTableListIToken);
});

$(document).on('toastr.onAjaxInfo', function(event) 
{
    toastr.info(_AjaxError);
});

//verify if user have permission to do somethings
function permissionVerifier(pCondition){
    if (pCondition == false){
        toastr.error(_noPermissionText);
        return false; 
    }else
        return true;
}

function dateFormat(dateIN = "", onlyDate = false)
{
    var date = new Date(dateIN);
    if(date.toString().toUpperCase() == "INVALID DATE")
    {
        return "-";
    }
    else
    {
        var options = {year: "numeric", month: "numeric", day: "numeric", hour12: false};

        if(!onlyDate)
        {
            options.hour = "numeric";
            options.minute = "numeric";
            options.second = "numeric";
        }

        return new Intl.DateTimeFormat('fr-FR', options).format(date);
    }
}

//force input to be number
function verif_nombre(phone) {
    var chiffres = new RegExp("[0-9]");
    var verif;
    var points = 0;
    var x;
    for (x = 0; x < phone.value.length; x++) {
        verif = chiffres.test(phone.value.charAt(x));
        if (phone.value.charAt(x) == ".") {
            points++;
        }
        if (points > 1) {
            verif = false;
            points = 1;
        }
        if (verif == false) {
            phone.value = phone.value.substr(0, x) + phone.value.substr(x + 1, phone.value.length - x + 1);
            x--;
        }
    }
}

// Format options
const optionFormat = (item) => {
    if (!item.id) {
        return item.text;
    }

    var span = document.createElement('span');
    var template = '';

    template += '<div class="d-flex align-items-center">';
    template += '<img src="' + item.element.getAttribute('data-avatar-content-icon') + '" class="rounded-circle h-40px me-3" alt="' + item.text + '"/>';
    template += '<div class="d-flex flex-column">'
    template += '<span class="fs-4 fw-bolder lh-1">' + item.text + '</span>';
    template += '<span class="text-muted fs-5">' + item.element.getAttribute('data-avatar-content-subcontent') + '</span>';
    template += '</div>';
    template += '</div>';

    span.innerHTML = template;

    return $(span);
}

// Init Select2 --- more info: https://select2.org/
$('#select2_avatar').select2({
    placeholder: "Select an option",
    minimumResultsForSearch: Infinity,
    templateSelection: optionFormat,
    templateResult: optionFormat
});