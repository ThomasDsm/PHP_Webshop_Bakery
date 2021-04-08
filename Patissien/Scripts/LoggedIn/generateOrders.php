<?php
include_once "../logNiveau3.php";
include_once "../errorHandling.php";
require '../connection.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['email']))
{ 
    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    // Log this ip tried to make POST
    trigger_error( $_SESSION['email'].'with IP :'.$ip.'tried to generate orders without being logged in' , E_USER_NOTICE );
    echo("notLoggedIn");
    header("Location: ./index.php");
    exit();
}

// Check if user has orders
$sql = "SELECT * from orders where userID=".$_SESSION['userID'].";";
if ($result1 = mysqli_query($con,$sql))
{
    if (mysqli_num_rows($result1) > 0) 
    {
        echo '
        <div class="table mx-0 px-0 orderTable text-right">
            <div class="tr">
                <div class="th">Prijs</div>
                <div class="th">Betalingsgegevens</div>
                <div class="th">Bestellingsgegevens</div> 
                <div class="th">Leveringsaddress</div> 
            </div>
        ';
        while ($row1 = mysqli_fetch_assoc($result1))
        {
            $bestellingsgegevens = "";
            $leveringsaddress = "";

            // Genereer de bestellingsgegevens
                // Haal alle producten op in 1 order
            $sql = "SELECT * from orderDetails where orderID=".$row1['orderID'].";";
            if ($result2 = mysqli_query($con,$sql))
            {   
                $rowCount = mysqli_num_rows($result2);
                $count = 0;
                while ($row2 = mysqli_fetch_assoc($result2))
                {   
                    $count += 1;
                    // Haal voor elk product de naam op
                    $sql = "SELECT productName FROM products where productID=".$row2['productID'].";";
                    if ($result3=mysqli_query($con,$sql))
                    {
                        $row3 = mysqli_fetch_assoc($result3);
                        mysqli_free_result($result3);
                    }
                    else
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                        echo('dbError');
                        exit();
                    }
                    
                    $bestellingsgegevens.= $row2['amount']." x ".$row3['productName'];
                    if ($count < $rowCount)
                    {
                        $bestellingsgegevens.=", ";
                    }
                }
                mysqli_free_result($result2);

            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                echo("dbError");
                exit();
            }

            // Genereer het leveringsadres
            $sql = "SELECT * from address where addressID=".$row1['addressID'].";";
            if ($result2 = mysqli_query($con,$sql))
            {
                $row2 = mysqli_fetch_assoc($result2);
                mysqli_free_result($result2);
                
                $sql = "SELECT municipName from postalcodes where postalCode=".$row2['postalCode'].";";
                if ($result3 = mysqli_query($con,$sql))
                {
                    $row3 =  mysqli_fetch_assoc($result3);
                    mysqli_free_result($result3);
                    $leveringsaddress .= $row2['streetName']." ".$row2['streetNumber'].", ".$row2['postalCode']." ".$row3['municipName'];
                }
                else
                {
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    echo("dbError");
                    exit();
                }
            }
            else
            {
                echo("dbError");
                exit();
            }

            if ($row1["paymentStatus"] == 1)
            {
                echo'
                <form class="tr" method="POST" action="./Scripts/LoggedIn/orderActions.php">
                    <span class="td">'.$row1['orderPrice'].'&nbsp;EUR</span>
                    <span class="td">Overschrijving is correct ontvangen</span>
                    <span class="td">'.$bestellingsgegevens.'</span>
                    <span class="td">'.$leveringsaddress.'</span>
                    <input type="hidden" name="orderID" value="'.$row1['orderID'].'">
                </form>
                ';
            }
            else
            {
                $betalingsgegevens = "Over te schrijven op 'BE71096123456769' met gestructureerde mededeling: '";
                $betalingsgegevens .= $row1['structRef']."'";

                echo'
                <form class="tr" method="POST" action="./Scripts/LoggedIn/orderActions.php">
                    <span class="td">'.$row1['orderPrice'].'&nbsp;EUR</span>
                    <span class="td">'.$betalingsgegevens.'</span>
                    <span class="td">'.$bestellingsgegevens.'</span>
                    <span class="td">'.$leveringsaddress.'</span>
                    <input type="hidden" name="orderID" value="'.$row1['orderID'].'">
                    <span class="td hasInput"><input type="submit" class="btn-small btn-danger" name="removeOrder" value="Verwijder bestelling"></span>
                </form>
                ';
            }
        }
        mysqli_free_result($result1);
        echo '
        </div>
        ';
        exit();
    }
    else
    {
        echo("noOrders");
        exit();
    }
}
else
{
    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
    echo("dbError");
    exit();
}
?>