"use strict";

import { bj_ville } from './bj.js'
import { tg_ville } from './tg.js'
import { sn_ville } from './sn.js'
import { ne_ville } from './ne.js'
import { ci_ville } from './ci.js'

const citySelect    = $('#city_select')
const countrySelect = $('#kt_user_add_select2_country')
$('#kt_user_add_select2_country').val(userCountryCode).trigger('change');
//document ready
$( document ).ready(function() 
{
    switch (userCountryCode) 
    {
        case 'BJ':
            document.getElementById("city_select").innerHTML = bj_ville;
            break;
        case 'TG':
            document.getElementById("city_select").innerHTML = tg_ville;
        break;
        case 'SN':
            document.getElementById("city_select").innerHTML = sn_ville;
        break;
        case 'CI':
            document.getElementById("city_select").innerHTML = ci_ville;
        break;
        case 'NE':
            document.getElementById("city_select").innerHTML = ne_ville;
        break;
        default:
            document.getElementById("city_select").innerHTML = bj_ville
            break;
    }
    


    $('#kt_user_add_select2_country').change(function()
    {
        switch ($(this).val()) 
        {
            case 'BJ':
                document.getElementById("city_select").innerHTML = bj_ville;
                citySelect .val("Abomey-Calavi").trigger('change');
                break;
            case 'TG':
                document.getElementById("city_select").innerHTML = tg_ville;
            break;
            case 'SN':
                document.getElementById("city_select").innerHTML = sn_ville;
            break;
            case 'CI':
                document.getElementById("city_select").innerHTML = ci_ville;
            break;
            case 'NE':
                document.getElementById("city_select").innerHTML = ne_ville;
            break;
            default:
                document.getElementById("city_select").innerHTML = bj_ville
                break;
        }
    })

    input.addEventListener("countrychange", function() {
        var country = intl.getSelectedCountryData()['iso2'].toUpperCase()
        countrySelect.val(country).trigger('change');
    }) 
});

