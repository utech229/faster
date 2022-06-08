"use strict";
$(document).ready(function() {
    $('#bodyFormReset').hide();
    $('#bodyFormOpen').hide();
    $('#errorlogin').hide();
    $('#successlogin').hide();
})

$('#goReset').click(function(e) {
    $('#bodyLogin').hide();
    $('#bodyFormReset').show();
})

$('#goReset1').click(function(e) {
    $('#bodyLogin').hide();
    $('#bodyFormReset').show();
})

$('#viewOpenAccount').click(function(e) {
    $('#bodyLogin').hide();
    $('#bodyFormOpen').show();
})

$('#viewOpenAccount1').click(function(e) {
    $('#bodyLogin').hide();
    $('#bodyFormReset').hide();
    $('#bodyFormOpen').show();
})
$('#viewAllLogin').click(function(e) {
    $('#bodyFormOpen').hide();
    $('#bodyLogin').show();
})

$('#viewLogin').click(function(e) {
    $('#bodyFormReset').hide();
    $('#bodyLogin').show();
})
$(document).on("begin", function() {
    $('#loginLoader').hide();
    $('#AccountProcess').show();
});

$(document).on("beginLogin", function() {
    $('#loginLoaderForm').hide();
    $('#PendingLogin').show();
});

$(document).on("beginVerify", function() {
    $('#Pendingverify').hide();
    $('#successverify').show();
});

$(document).on("beginEndVerify", function() {
    $('#successverify').hide();
    $('#Pendingverify').show();
});


$(document).on('ajaxError', function() {
    $('#AccountProcess').hide();
    $('#loginLoader').show();
});

$(document).on("beginRestauration", function() {
    $('#errorresetmessage').hide();
    $('#successresetmessage').show();
});

$(document).on('errorRestauration', function() {
    $('#successresetmessage').hide();
    $('#errorresetmessage').show();
});

$(document).on("hidebtnreset", function() {
    $('#successReset').hide();
    $('#PendingReset').show();
});

$(document).on('showbtnreset', function() {
    $('#PendingReset').hide();
    $('#successReset').show();
});

$(document).on("beginResetPwd", function() {
    $('#btnpendingFormReset').show();
    $('#btnsuccessForm').hide();
});

$(document).on('ajaxErrorResetPwd', function() {
    $('#btnpendingFormReset').hide();
    $('#btnsuccessForm').show();

});

$(document).on('ajaxErrorLogin', function() {
    $('#PendingLogin').hide();
    $('#loginLoaderForm').show();
});

$('#form_open_account').on('submit', function(e) {
    e.preventDefault();
    $('#loginLoaderopen').hide();
    $('#AccountProcessopen').show();
    var data = $(this).serializeArray();
    data.push({ name: 'full_number', value: intl.getNumber() })
    data.push({ name: 'country', value: intl.getSelectedCountryData()['iso2'] })
    const url = register_url;
    $.ajax({
        url: register_url,
        type: 'post',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('.errorlogin').hide();
                $('#messageregistererror').hide();
                $('#messageregister').show();
                $('#loginLoaderopen').show();
                $('#AccountProcessopen').hide();
                $('#form_open_account')[0].reset();
            } else {
                $('#loginLoaderopen').show();
                $('#AccountProcessopen').hide();
                $('#messageregister').hide();
                $('#messageregistererror').show().text(response.data.message);
                $('#form_open_account')[0].reset();
                setTimeout(
                    $('#messageregistererror').hide()
                , 5000);
            }
        },
        error: function(response) {
            $('#loginLoaderopen').show();
            $('#AccountProcessopen').hide();
            $('#messageregister').hide();
            $('#messageregistererror').show().text('error');
            setTimeout(
                $('#messageregistererror').hide()
            , 5000);
        }
    });
    
});

$('#form_validate_account').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize();
    var url = login_url;
    axios.post(url, data)
        .then(function(response) {
            if (response.data.status === 'success') {
                $('.errorlogin').hide();
                window.location.href = login_url + "/login?s=1"
            } else {
                $('#successverify').show();
                $('#Pendingverify').hide();
                $('.messageinfo').hide();
                $('.messageerror').show().text(response.data.message);
            }
        })
        .catch(function(error) {
            $(document).trigger('ajaxError');

        });
});

$('#login_users').on('submit', function(e) {
    $(document).trigger('beginLogin');
});

$('#formReset').on('submit', function(e) {
    $(document).trigger('hidebtnreset');
    e.preventDefault();
    var data = $(this).serialize();
    const url = login_url + "/reset";
    axios.post(url, data)
        .then(function(response) {
            if (response.data.status === 'success') {
                $('.errorlogin').hide();
                $(document).trigger('beginRestauration');
                $(document).trigger('showbtnreset');
                $('#formReset')[0].reset();
            } else {
                $(document).trigger('errorRestauration');
                $(document).trigger('showbtnreset');
                $('#formReset')[0].reset();
            }
        })
});

$('#form_news_pwd').on('submit', function(e) {
    $(document).trigger('beginResetPwd');
    e.preventDefault();
    var data = $(this).serialize();
    const url = window.location.href;
    axios.post(url, data)
        .then(function(response) {
            if (response.data.status === 'success') {
                $('.errorlogin').hide();
                $('#errorPwd').hide();
                $('#successPwd').show();
                $(document).trigger('ajaxErrorResetPwd');
                $('#form_news_pwd')[0].reset();
            } else {
                $('#successPwd').hide();
                $('#errorPwd').show();
                $(document).trigger('ajaxErrorResetPwd');
                $('#form_news_pwd')[0].reset();
            }
        })
});