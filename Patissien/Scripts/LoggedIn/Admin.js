// https://www.w3resource.com/javascript/form/all-numbers.php
function allNumbers (myString)
{
    var numbers = /^[0-9]+$/;
    return myString.match(numbers);
}
// https://stackoverflow.com/questions/5778020/check-whether-an-input-string-contains-a-number-in-javascript
function hasNumber(myString) 
{
    return /\d/.test(myString);
}


/* Generate list of existing municipalities */
function generateMunicipalities() {
    var request;
    request = $.ajax(
    {
        type: 'GET',
        url: './Scripts/LoggedIn/adminActions.php',
        data: {
            municipalities:1
        }
    });
    request.done(function(response,textStatus,jqXHR)
    {
        if (response == "") { $("#existingMuni").html("<li class='alert-warning'>Geen gemeentes waaraan geleverd wordt</li>"); }
        else {
            // Response is a JSON encoded array of the cart
            var muniObject = JSON.parse(response);
            var htmlString = "";

            for (var i=0; i< muniObject.length; i++)
            {
                htmlString += "\n\t\t<li>"+muniObject[i]['municipName']+", "+muniObject[i]['postalCode']+"<li>";
            }
            $("#existingMuni").html(htmlString);
        }
    });
}
/* END OF generation list of existing municipalities*/


/* Add Municipality */
function clearAddMuniError() { $("#addMuniError").text(""); }
function invalidPostalCode() { $("#addMuniError").text("Ongeldige postcode"); }
function invalidMuniName() { $("#addMuniError").text("Ongeldige gemeentenaam"); }
function successAddMuni() { $("#addMuniError").text("Gemeente succesvol toegevoegd!"); }
function existAddMuni() { $("#addMuniError").text("Gemeente is al toegevoegd!"); }

function addMunicipality() {
    clearAddMuniError();
    var muniName = $("#muniName").val();
    var postalCode = $("#postalCode").val();
    var error = 0;

    if (muniName == "" || hasNumber(muniName) || muniName.length <3 || muniName.length > 25)
    {
        invalidMuniName();
    } 
    else if (postalCode == "" || !allNumbers(postalCode) || postalCode < 1000 || postalCode > 9999)
    {
        invalidPostalCode();
    }
    else
    {
        var request;

        request = $.ajax(
        {
            type: 'GET',
            url: './Scripts/LoggedIn/adminActions.php',
            data: {
                'addMuni':1,
                'municipName':muniName,
                'postalCode':postalCode,
            }
        });
        
        request.done(function(response,textStatus,jqXHR)
        {
            if (response == "succes") { successAddMuni(); }
            else if (response == "exists") { existAddMuni() ;}
            else {
                console.log(response);
            }
        });
    }
}
/* END OF Add Municipality */


$(document).ready(function(){


});