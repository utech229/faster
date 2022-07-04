"use strict";

// Class definition
var THEMECOOKIES = function() {

    // Init form inputs
    var handleForm = function() {
        var mjour = 'monjour';
        var mnuit = 'manuit';

        var thememode = document.querySelector('#kt_user_menu_dark_mode_toggle');
            thememode.addEventListener('change', function(element) {
            console.log('Agreement changed to ' + thememode.checked);
            if (thememode.checked == true) {
                KTApp.setThemeMode("dark", function() {
                    console.log("changed to dark mode");

                    var date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000); // +2 day from now
                    var optionsnuit = { expires: date };
                    KTCookie.set(mnuit, "yes", optionsnuit);
                    KTCookie.remove(mjour);

                }); // set dark mode
            }else{
                KTApp.setThemeMode("light", function() {
                    console.log("changed to light mode");

                    var date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000); // +2 day from now
                    var options = { expires: date };
                    KTCookie.remove(mnuit);
                    KTCookie.set(mjour, "yes", options);
                }); // set light mode
                
            }
            
        });

        
        var cookieNuit = KTCookie.get(mnuit);
        var cookieJour = KTCookie.get(mjour);
        if (cookieNuit == "yes") {
            KTApp.setThemeMode("dark", function() {
                console.log("changed to dark mode");
            });
        }
        if (cookieJour == "yes") {
            KTApp.setThemeMode("light", function() {
                console.log("changed to dark mode");
            });
        }

        if ((cookieJour == "yes") && (cookieNuit == "yes")) {
            KTCookie.remove(mnuit);;
        }
                        
    }

    return {
        // Public functions
        init: function() {
            // Elements
            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    THEMECOOKIES.init();
});

