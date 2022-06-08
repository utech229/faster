"use strict";

// Class definition
var THEMECOOKIES = function() {
    var lightThemeButton;
    var darkThemeButton;
    var themeColor;
    var lightModeIcon;
    var darkModeIcon;
    var mjour = 'monjour';
    var mnuit = 'manuit';

    // Init form inputs
    var handleForm = function() {
        lightModeIcon.removeClass('d-none');

        lightThemeButton.click(function(){
            var date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000); // +2 day from now
            var options = { expires: date };
            KTCookie.remove(mnuit);
            KTCookie.set(mjour, "yes", options);
            lightThemeButton.addClass('active');
            darkThemeButton.removeClass('active');
            lightModeIcon.removeClass('d-none');
            darkModeIcon.addClass('d-none');
            KTApp.setThemeMode("light", function() {
                console.log("changed to dark mode");
            });
            
        });


        darkThemeButton.click(function(){
            var date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000); // +2 day from now
            var optionsnuit = { expires: date };
            KTCookie.set(mnuit, "yes", optionsnuit);
            KTCookie.remove(mjour);
            darkThemeButton.removeClass('active');
            darkThemeButton.addClass('active');
            lightModeIcon.addClass('d-none');
            darkModeIcon.removeClass('d-none');
            KTApp.setThemeMode("dark", function() {
                console.log("changed to light mode");
            });
        });

        
        var cookieNuit = KTCookie.get(mnuit);
        var cookieJour = KTCookie.get(mjour);
        if (cookieNuit == "yes") {
           darkModeIcon.removeClass('d-none');
            lightModeIcon.addClass('d-none');
            darkThemeButton.removeClass('active');
            darkThemeButton.addClass('active');
            KTApp.setThemeMode("dark", function() {
                console.log("changed to dark mode");
            });
        }
        if (cookieJour == "yes") {
            lightModeIcon.removeClass('d-none');
            darkModeIcon.addClass('d-none');
            lightThemeButton.addClass('active');
            darkThemeButton.removeClass('active');
            KTApp.setThemeMode("light", function() {
                console.log("changed to dark mode");
            });
        }

        if ((cookieJour == "yes") && (cookieNuit == "yes")) {
            KTCookie.remove(mnuit);
            lightModeIcon.removeClass('d-none');
            darkModeIcon.addClass('d-none');
        }
                
    }

    return {
        // Public functions
        init: function() {
            // Elements

            lightThemeButton = $('#lightThemeButton');
            darkThemeButton = $('#darkThemeButton');;
            themeColor      = $('#themeColor');;
            lightModeIcon   = $('#lightModeIcon');;
            darkModeIcon    = $('#darkModeIcon');;
            handleForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    THEMECOOKIES.init();
});

