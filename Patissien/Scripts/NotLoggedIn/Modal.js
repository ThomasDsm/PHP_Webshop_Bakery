/* START OF REGISTER VALIDATION */
function validateEmail(email) {
    //https://www.w3docs.com/snippets/javascript/how-to-validate-an-e-mail-using-javascript.html
    const res = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return res.test(String(email).toLowerCase());
}

function clearErrors() { $("#registratie p").text("");}
function errorEmail() { $("#errorEmail").text("Gelieve een emailadres in te geven");}
function errorfName() { $("#errorfName").text("Gelieve een voornaam in te geven (Max. 30 tekens)");}
function errorlName() { $("#errorlName").text("Gelieve een achternaam in te geven (Max. 30 tekens)");}
function errorPasswd() { $("#errorPasswd").text("Maak je passwoord moeilijker (min. 10 tekens)");}
function errorRepeatPasswd() { $("#errorRepeatPasswd").text("De ingevoerde passwoorden komen niet overeen")}
function errorDB() { $("#errorDB").text("DB error, try again later.")}
function errorEmailExists() { $("#errorEmail").text("Er bestaat reeds een gebruiker met dit email-adres")};

function registrated(email) 
{
    $("#registerModal").empty().append('<div class="alert alert-success"><strong>'+email+' is geregistreerd!</strong></div>');
    $("#email2").val(email);
    setTimeout(function() {
        $("#registerModal").modal('hide');
        $("#loginModal").modal('show');
    },2000);

}

function registerValidation()
{
    var email =  $("#registratie #email").val();
    var fName = $("#registratie #fName").val();
    var lName = $("#registratie #lName").val();
    var passwd = $("#registratie #passwd").val();
    var repeatPasswd = $("#registratie #repeatPasswd").val();
    var error = 0;

    clearErrors();

    if (!validateEmail(email)) { error = 1; errorEmail();}
    if (fName == "" || fName.length > 30) { error = 1; errorfName();}
    if (lName == "" || lName.length > 30) { error = 1; errorlName();}
    if (passwd == "" || passwd.length < 10) { error = 1; errorPasswd();}
    else
    {
        if (passwd.localeCompare(repeatPasswd) != 0 ) { error = 1; errorRepeatPasswd();}
    }

    if (error == 1) { console.log("JS validation failed");}

    if (error == 0)
    {
        console.log("JS validation succeeded");
        var request;

        request = $.ajax({
            type: 'POST',
            url: './Scripts/NotLoggedIn/register.php',
            data: { 
                'email':email,
                'fName':fName,
                'lName':lName,
                'passwd':passwd
            }
        });

        request.done(function(response,textStatus,jqXHR){
            if (response == "fillInAllFields") {}
            else if (response == "errorEmail" ) { errorEmail(); }
            else if (response == "errorfName") { errorfName(); }
            else if (response == "errorlName") { errorlName(); }
            else if (response == "errorPasswd") { errorPasswd();}
            else if (response == "dbError") { errorDB(); }
            else if (response == "errorEmailExists") { errorEmailExists(); }
            else if (response == "succes") { registrated(email); }
            else { console.log("You broke the system\nresponse: "+response); } 
        })
    }
}
/* END OF REGISTER VALIDATION */
/* -------------------------- */
/*  START OF LOGIN VALIDATION */
function clearErrors2() { $("#error2").text("");}
function error2_1() { $("#error2").text("Vul alle velden in!");}
function error2_2() { $("#error2").text("Aanmelding mislukt, controleer gegevens."); $("#passwd2").val(""); }

function loggedIn(email) 
{
    $("#loginModal").empty().append('<div class="alert alert-success"><strong>Correct! U wordt ingelogd.</strong></div>');
    setTimeout(function() {
        location.reload(); 
    },2000);
}

function loginValidation()
{
    var email2 = $("#email2").val();
    var passwd2 = $("#passwd2").val();
    clearErrors2();
    error = 0;
    // JS validation
    // Check of velden leeg zijn
    // chekc grootte van velden 
    if (error == 0)
    {
        var request;

        request = $.ajax({
            type: 'POST',
            url: './Scripts/NotLoggedIn/login.php',
            data: { 
                'email2':email2,
                'passwd2':passwd2
            }
        });

        request.done(function(response,textStatus,jqXHR){
            if (response == "fillInAllFields") { error2_1();}
            else if (response == 'fail') { error2_2(); }
            else if (response == "succes") { loggedIn(); }
            else { console.log("You broke the system"); } 
        })
    }
}
/* END OF LOGIN VALIDATION */
/* ----------------------- */

$(document).ready(function() {
    $("#loginButton").click(function() { clearErrors2(); $('#loginModal').modal('show'); });
    $("#signupclick").click(function() {  $("#loginModal").modal('hide'); $('#registerModal').modal('show');})
    $("#registerButton").click(function() { $('#registerModal').modal('show'); });

    $("#registratieKnop").click(function()
    {
        console.log("Start register validation");
        registerValidation();
    });
    $("#loginform").submit(function(e){
        e.preventDefault();
        loginValidation();
    });
});