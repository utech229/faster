"use strict";

function update(code)
{
    activeRoleCode = code;
    $(document).trigger('roleUpBegin', [code]);
    document.getElementById('role_name').value = roleProperty[code][2] ;
    document.getElementById('role_code').value = roleProperty[code][1] ;
    document.getElementById('role_level').value = roleProperty[code][5] ;
    document.getElementById('description').value = roleProperty[code][3];
    unselect_permissions();
    jsonToArray(authorizationByRoles[code]).forEach(select_permissions); 
}

function select_permissions(item, index)
{
    document.getElementById(item).checked =  true;
}

function unselect_permissions()
{
    let element = document.getElementsByClassName('permission');
    for (var i = 0; i < element.length; i++) 
    {
        element[i].checked = false;   
    }
}

        
function jsonToArray(data) {
    var obj = JSON.parse(data);
    var res = [];
    for(var i in obj)
        res.push(obj[i]);
    return res;
}