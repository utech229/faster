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