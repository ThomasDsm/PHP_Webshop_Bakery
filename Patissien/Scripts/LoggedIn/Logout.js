$(document).ready(function(){
    // Logout
    $("#logoutKnop").click(function() {
        console.log("Logout initiated");
        var request;

        request = $.ajax({
            type: 'POST',
            url: './Scripts/LoggedIn/logout.php',
        });

        request.done(function(response,textStatus,jqXHR){
            if (response == "refresh") { location.reload(); }
            else { console.log("Logout failed"); }
        });
    });
});