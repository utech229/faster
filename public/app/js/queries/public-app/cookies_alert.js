"use strict";
//toast elements gettings
const toastElement = document.getElementById('kt_docs_toast_cookies');
const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
window.addEventListener("cookieAlertAccept", function() {
    //toast show
    toast.show();
});

