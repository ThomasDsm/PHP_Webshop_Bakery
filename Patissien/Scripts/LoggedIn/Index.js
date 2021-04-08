function changeProduct(productID, amount)
{
    var request;

    request = $.ajax(
    {
        type: 'GET',
        url: './Scripts/LoggedIn/cartActions.php',
        data:
        {
            'change': productID,
            'amount': amount
        }
    });
    
    request.done(function(response,textStatus,jqXHR)
    {
        console.log(response);
        if (response == "succes") { getCartContents(); }
        else if (response == "cleared") { getCartContents(); alert("Product is verwijdert uit de winkelkar"); }
        else if (response == 'report') { alert("Your actions have been reported"); }
        else if (response == 'dbError') { alert("Internal database error"); }
        else { alert("Je kan maximaal "+response+" van dit product in je winkelkar stoppen"); }
    });
}

function getCartContents()
{
    var request;

    request = $.ajax(
    {
        type: 'GET',
        url: './Scripts/LoggedIn/cartActions.php',
        data: {
            cart : 1
        }
    });
    
    request.done(function(response,textStatus,jqXHR)
    {
        if (response == "empty") 
        { 
            $("#cartContent").empty().html("<div class=\"alert alert-warning\" role=\"alert\">Winkelkar is leeg</div>");
            $("#cartModal .modal-footer").css({'display':'none'});
        }
        else
        {
            // Response is a JSON encoded array of the cart
            var cartObject = JSON.parse(response);
            var total=0;
            var table="\t<table class=\"table\">\n";
            table += "\t\t<tr>\n";
            table += "\t\t\t<th>Aantal</th>\n";
            table += "\t\t\t<th>Product</th>\n";
            table += "\t\t\t<th>Prijs</th>\n";
            table += "\t\t</tr>\n";

            for (var i=0; i< cartObject.length; i++)
            {
                var productID = cartObject[i]["productID"];
                var td1 = cartObject[i]["amount"];
                var td2 = cartObject[i]["productName"];
                var td3 = cartObject[i]["price"];  
                total += td1 * td3;

                table += "\t\t<tr>\n";
                table += "\t\t\t<td><a class=\"text-danger changeProduct\">Verander</a><input type=\"hidden\" value=\""+productID+"\">&nbsp;"+td1+"</td>\n";
                table += "\t\t\t<td class=>"+td2+"</td>\n";
                table += "\t\t\t<td> &euro; "+td3+"</td>\n";
                table += "\t\t</tr>\n";
            }

            table += "\t</table>\n";
            table += "\t<h5 class=\"alert alert-danger\">Totaal: &euro; "+total.toFixed(2)+"</h5>\n";

            $("#cartContent").empty().append(table);          
            $("#cartModal .modal-footer").css({'display':'block'});

            $(".changeProduct").click(function() {
                var productNaam = $(this).parent().next().text();
                var prodID = $(this).next().val();
                var amount = window.prompt("Hoeveel van "+productNaam+" zou je dan willen?");
                if (amount != null && amount != "")
                {
                    console.log("Change productid "+prodID+" to amount "+amount);
                    changeProduct(prodID,amount);
                }
            })
        }
    });
}

function clearCart()
{
    var request;

    request = $.ajax(
    {
        type: 'GET',
        url: './Scripts/LoggedIn/cartActions.php',
        data:
        {
            'clear':1
        }
    });
    
    request.done(function(response,textStatus,jqXHR)
    {
        if (response == "empty") 
        { 
            $("#cartContent").empty().html("<div class=\"alert alert-warning\" role=\"alert\">Winkelkar is leeg</div>");
            $("#cartModal .modal-footer").css({'display':'none'});
        }
    });
}

function addToCart(prodID,amt)
{
    var request;

    request = $.ajax({
        type: 'GET',
        url: './Scripts/LoggedIn/cartActions.php',
        data: { 
            'add':prodID,
            'amount':amt
        }
    });

    request.done(function(response,textStatus,jqXHR){
        console.log(response);  
        if (response == 'succes') { alert("Toegevoegd aan je winkelkar!"); }
        else if (response == "LE0") { alert("Je moet minstens 1 product toevoegen."); }
        else if (response == 'report') { alert("Your actions have been reported"); }
        else if (response == 'dbError') { alert("Internal database error"); }
        else { alert("Er zijn nog "+response+" stuks verkrijgbaar. Alvast mijn excuses voor het ongemak.    "); }
    });
}

function genProducts(catName) 
{
    var request;

    request = $.ajax({
        type: 'POST',
        url: './Scripts/generateProducts.php',
        data: { categoryName: catName }
    });

    request.done(function(response,textStatus,jqXHR){
        if (response == "dbError") { $("#productContainer").html('<span class="productError">Error connecting to backend</span>');}
        else { $("#productContainer").html(response);}

        // Wanneer een product gekozen wordt, voeg toe aan winkelmandje
        $(".card-footer button").click(function()
        {
            // Verander dit naar een modal waarin de vragen gesteld worden i.p.v. pop up windows
            var productName = $(this).parent().prev().children('h4').children('a').text();
            var productID = $(this).next().val();
            var amount = window.prompt("Hoeveel keer wil je \'"+productName+"\' toevoegen aan je winkelmandje?")
            console.log(productID)

            if (amount != null || amount != '')
            {
                if (Number.isInteger(+amount))
                {
                    if (amount <= 0)
                    {
                        alert("Je moet minstens 1 taart bestellen");
                    }
                    else
                    {
                        if (window.confirm("Wil je "+amount+" x "+productName+" in je winkelmandje plaatsen?"))
                        {
                            addToCart(productID,amount);
                        }
                    }
                }
                else
                {
                    alert("Gelieve een getal in te voeren");
                }
            }
        });
    });
}

$(document).ready(function() {
    if (window.location.pathname.indexOf("/about") > -1 )
    {
        $(".navbar .nav-item:nth-of-type(2)").addClass("active");
    }
    else if (window.location.pathname.indexOf("/admin") > -1 )
    {  
        $(".navbar .nav-item:nth-of-type(6)").addClass("active");

    }
    else
    {
        $(".navbar .nav-item:nth-of-type(1)").addClass("active");
        // Duid aan welke categorie er actief is
        $("#categories .list-group-item:nth-of-type(1)").addClass("active");
        genProducts($("#categories .list-group-item:nth-of-type(1)").text());

        // Wanneer een andere categorie gekozen wordt, maak deze actief en laad producten
        $("#categories .list-group-item").click(function() {
            if ($(this).hasClass('active')) { console.log("Already loaded"); }
            else
            {
                $("#categories .list-group-item.active").removeClass("active");
                $(this).addClass("active")
                genProducts($(this).html())
            }
        });

        $("#cartKnop").click(function() {
            getCartContents();
            $('#cartModal').modal('show');
        });

        $("#clearCart").click(function() {
            clearCart();
        });

        $("#orderButton").click(function(){
            console.log("Order plaatsen");
            setTimeout(function() {
                window.location.href= "./order.php?place=1";
            }, 500);
        })
    }    
});