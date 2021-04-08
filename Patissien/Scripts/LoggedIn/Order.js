function dateInPast(dateToCheck, today)
{
    if (dateToCheck.setHours(0,0,0,0) < today.setHours(0,0,0,0))
    {
        return true;
    }
    else
    {
        return false;
    }
}

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


// https://stackoverflow.com/questions/23593052/format-javascript-date-as-yyyy-mm-dd
function formatDate(date) 
{
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}



function clearOrders() {
    $("#orderContainer").empty();
}
function generateOrders()
{
    console.log("Start order generation");
    var request;

    request = $.ajax({
        type: 'POST',
        url: './Scripts/LoggedIn/generateOrders.php',
    });

    request.done(function(response,textStatus,jqXHR){
        if (response == "noOrders") { $("#orderContainer").html("<h4 class='alert-danger text-center'>Geen bestellingen geplaatst</h4>"); }
        else if (response == "dbError") { $("#orderContainer").text("Error 404")}
        else { 
            $("#orderContainer").html(response);
        }
    });
}




function clearErrors() { console.log("Clearing errors"); $("#questionbox .form-group p").text(""); }
function errorPostalCode() { $("#errorPostalCode").text("Dit is geen bestaande postcode"); }
function errorDelivery() { $("#errorDelivery").text("De bestelling moet 5 dagen op voorhand worden doorgegeven!"); }
function errorStreet() { $("#errorStreet").text("Dit is geen bestaande straat"); }
function errorStreetNum() { $("#errorStreetNum").text("Dit is geen bestaande huisnummer"); }

function placementValidation() 
{
    console.log("Start validation");
    clearErrors();
    var error = 0;
    var postalCode = $("#postalcode").val();
    var streetName = $("#street").val();
    var streetNum = $("#streetnum").val();

    // postalCode validation
    if (postalCode != "")
    {
        if (allNumbers(postalCode))
        {
            if (postalCode < 1000 || postalCode > 9999)
            {
                errorPostalCode();
                error = 1;
            }
        }
        else
        {
            errorPostalCode();
            error = 1;
        }
    }
    else
    {
        errorPostalCode();
        error = 1;
    }

   
    // Validation of streetName
    if (streetName != "")
    {
        if (hasNumber(streetName))
        {
            errorStreet();
            error = 1;
        }
        else
        {
            if (streetName.length > 60 || streetName.length < 3)
            {
                errorStreet();
                error = 1;
            }
        }
    }
    else
    {
        errorStreet();
        error = 1;
    }

   
    // Validation of streetNum
    if (streetNum != "")
    {  
        if (allNumbers(streetNum))
        {
            if (streetNum > 1000)
            {
                errorStreetNum();
                error = 1;
            }
        }
        else    
        {
            errorStreetNum();
            error = 1;
        }
    }
    else
    {
        errorStreetNum();
        error = 1;
    }

   
    // Validation of the date field (Check if filled in/ a correct date; check if its is )
        // Set minimalDeliveryDate to 5 days in the future
        // https://stackoverflow.com/questions/3818193/how-to-add-number-of-days-to-todays-date
    var minimalDeliveryDate = new Date();
    var numberOfDaysToAdd = 5;
    minimalDeliveryDate.setDate(minimalDeliveryDate.getDate() + numberOfDaysToAdd); 
    

        // https://stackoverflow.com/questions/1353684/detecting-an-invalid-date-date-instance-in-javascript
    var timestamp = Date.parse($("#deliverydate").val());
    if (isNaN(timestamp) == false)
    {
        var deliveryDate = new Date(timestamp);

        if (dateInPast(deliveryDate,minimalDeliveryDate))
        {
            errorDelivery();
            error = 1;
        }
        else
        {
            // If date is verified, change format to "YYYY-MM-DD"
            deliveryDate = formatDate(deliveryDate);
        }
    }
    else
    {
        errorDelivery();
        error = 1;
    }
    if (error == 0)
    {
        // If fields are correctly validated, send them to the desired PHP script
        var request;

        request = $.ajax({
            type: 'POST',
            url: './Scripts/LoggedIn/placeOrder.php',
            data: { 
                'postalCode' : postalCode,
                'streetName' : streetName,
                'streetNum' : streetNum,
                'deliveryDate' : deliveryDate,
            }
        });
    
        request.done(function(response,textStatus,jqXHR){
            clearErrors();
            if (response == "succes")
            {
                $("#placementModal").empty().append('<div class="alert alert-success"><strong>U bestelling is geplaatst!</strong></div>');
                setTimeout(function() {
                    window.location.href="./order.php"
                },3000);
            }
            else if (response == "cartError")
            {
                $("#placementModal").empty().append('<div class="alert alert-danger"><strong>U winkelwagen is leeg</strong></div>');
                setTimeout(function() {
                    window.location.href="./index.php"
                },3000);
            }
            else if (response == "dbError")
            {
                var request;

                request = $.ajax({
                    type: 'POST',
                    url: './Scripts/LoggedIn/logout.php',
                });
        
                request.done(function(response,textStatus,jqXHR){
                    if (response == "refresh") { location.reload(); }
                    else { console.log("Logout failed"); }
                });
            }
            else if (response == "noDelivery")
            {
                $("#placementModal").empty().append('<div class="alert alert-danger"><strong>Er wordt niet geleverd in deze gemeente</strong></div>');
                setTimeout(function() {
                    window.location.href="./order.php?highlight=municipalities"
                },3000);
            }
            else if (response == "setAllVars") { $('#placement').trigger("reset") }
            else if (response == "invalidPostalCode") { errorPostalCode(); }
            else if (response == "invalidStreetName") { errorStreet(); }
            else if (response == "invalidStreetNumber") { errorStreetNum(); }
            else if (response == "dateError") { errorDelivery(); }
            else
            {
                alert("Iemand was je voor bij het bestellen van "+response+", de gevraagde hoeveelheid in je bestelling is niet meer beschikbaar! Het item wordt uit je winkelkar gehaald.");
            }
        });
    }
}

$(document).ready(function() {
    $(".navbar .nav-item:nth-of-type(4)").addClass("active");

    // When the page is loaded, the url is read for the ?place=1 tag.
    // The order placement procedure should start
    if (window.location.href.indexOf("?place=1") > -1)
    {
        $('#placementModal').modal('show');
        // Vraag om een adres op te geven
    }
    generateOrders();

    if (window.location.href.indexOf("?highlight=municipalities") > -1)
    {
        // Maak de gemeentes groter en rood
        $("#highlightMe").addClass("alert alert-danger");
    }

    $("#placeButton").click(function() {
        $('#placementModal').modal('show');
    });
    
    $("#clearOrder").click(function() {
        window.location.href = "./index.php";
    });

    $("#placement").submit(function(e){
        e.preventDefault();
        placementValidation();
    });
});