! function() {
    "use strict";
    var e = document.querySelector(".cookiealert"),
        t = document.querySelector(".acceptcookies");
    e && (e.offsetHeight, function(e) {
        for (var t = e + "=", o = decodeURIComponent(document.cookie).split(";"), c = 0; c < o.length; c++) {
            for (var n = o[c];
                " " === n.charAt(0);) n = n.substring(1);
            if (0 === n.indexOf(t)) return n.substring(t.length, n.length)
        }
        return ""
    }("acceptCookies") || e.classList.add("show"), t.addEventListener("click", (function() {
        ! function(e, t, o) {
            var c = new Date;
            c.setTime(c.getTime() + 24 * o * 60 * 60 * 1e3);
            var n = "expires=" + c.toUTCString();
            document.cookie = e + "=" + t + ";" + n + ";path=/"
        }("acceptCookies", !0, 365), e.classList.remove("show"), window.dispatchEvent(new Event("cookieAlertAccept"))
    })))
}();


! function() {
    "use strict";
    var fb = document.querySelector(".facebook-page-subscription"),
        fbk = document.querySelector(".accept-facebook-page-subscription");
        fb && (fb.offsetHeight, function(fb) {
        for (var fbk = fb + "=", o = decodeURIComponent(document.cookie).split(";"), c = 0; c < o.length; c++) {
            for (var n = o[c];
                " " === n.charAt(0);) n = n.substring(1);
            if (0 === n.indexOf(fbk)) return n.substring(fbk.length, n.length)
        }
        return ""
    }("acceptFbkSubscription") || fb.classList.add("show"), fbk.addEventListener("click", (function() {
        ! function(fb, fbk, o) {
            var c = new Date;
            c.setTime(c.getTime() + 1 * o * 60 * 60 * 1e3);
            var n = "expires=" + c.toUTCString();
            document.cookie = fb + "=" + fbk + ";" + n + ";path=/"
        }("acceptFbkSubscription", !0, 365), fb.classList.remove("show"), window.dispatchEvent(new Event("fbkSubscriptionAlertAccept"))
    })))
}();