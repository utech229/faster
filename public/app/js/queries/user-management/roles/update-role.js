
var mdHTMLTitle = $("#kt_modal_add_role_text")
var roleIDInput =  $('#role_id');
var isRoleUpdating;


$(document).on('roleUpBegin', function(e, code) {
    mdHTMLTitle.html(_Edit+ ' '+ roleProperty[code][3]);
    $('#role_id').val(roleProperty[code][1]);
    isRoleUpdating = true;
});

$(document).on('roleUpStop', function(event) {
    mdHTMLTitle.html(_Add);
    $('#role_id').val(0);
    $("#kt_modal_add_role_form")[0].reset();
    isRoleUpdating = false;
});

$('#kt_modal_add_role').on('hidden.bs.modal', function(e) {
    $(document).trigger('roleUpStop');
});