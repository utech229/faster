"use strict";
$(document).on('entityUpBeginP', function(e, identifier, id, icon) {
    $(identifier + id).removeClass("fas");
	$(identifier + id).removeClass("fas");
	$(identifier + id).removeClass(icon).addClass("fa fa-spin fa-circle-notch");
});


$(document).on('click', ".pricer", function(e) {
    var uid = $(this).data('id');
    $(document).trigger('entityUpBeginP', ['#priceOption', uid, 'fa-money-bill']);

    window.location.href = user_price_link.replace("_1_", uid);
});