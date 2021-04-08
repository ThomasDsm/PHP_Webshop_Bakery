function genProducts(catName) 
{
    var request;

    request = $.ajax({
        type: 'POST',
        url: './Scripts/generateProducts.php',
        data: { categoryName: catName }
    });

    request.done(function(response,textStatus,jqXHR){
        if (response == "dbError")  {  $("#productContainer").html('<span class="productError">Error connecting to backend</span>');}
        if (response == "noMore")   {  $("#productContainer").html('<span class="productError">Geen '+catName+' meer beschikbaar</span>');}
        else                        {  $("#productContainer").html(response);}
        
        // Wanneer iemand een product aan zijn winkelwagen wilt toevoegen, moet deze ingelicht worden dat hij een account moet maken
        $(".card-footer button").click(function(){
            alert("Om dingen toe te voegen aan je winkelkar, moet je aangemeld zijn.")
        })
    })
}

$(document).ready(function() {

    if (window.location.pathname.indexOf("/about") > -1 )
    {
        console.log("We are on the about page");
        $(".navbar .nav-item:nth-of-type(2)").addClass("active");
    }
    else
    {
        console.log("We are not on the about page");
        $(".navbar .nav-item:nth-of-type(1)").addClass("active");
        // Kijk met javascript na of er een categorie wordt ingeduwd, zo ja verander je de content met AJAX
       // Bij het laden van de pagina moet de 1e categorie ingeladen worden
       $("#categories .list-group-item:nth-of-type(1)").addClass("active");
       genProducts($("#categories .list-group-item:nth-of-type(1)").text());

       // Wanneer een andere categorie gekozen wordt
       $("#categories .list-group-item").click(function() {
           if ($(this).hasClass('active')) { console.log("Already loaded"); }
           else
           {
               $("#categories .list-group-item.active").removeClass("active");
               $(this).addClass("active")
               genProducts($(this).html())
           }
       });
    }
});