<?php
session_start();
include_once "./Scripts/logNiveau1.php";
include_once "./Scripts/errorHandling.php";
require "./Scripts/connection.php";

if (!isset($_SESSION['admin']))
{
    header('Location: ./index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patissien</title>
    <link rel="stylesheet" href="./Styles/Reset.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./Styles/Index.css">
<?php
    echo ("\t".'<script src="./Scripts/LoggedIn/Index.js"></script>'."\n");
    echo ("\t".'<script src="./Scripts/LoggedIn/Logout.js"></script>'."\n");

    echo ("</head>\n<body>\n");
    require "./Content/LoggedIn/indexNav.php"; 
    require "./Content/LoggedIn/cart.html";
    // Place admin panel below
?>  

    <div class="container text-center mx-auto">
        <div class="row text-center mt-3">

            <div class="col-lg-12 border">
                <h5 class="my-3">Betaalde / te leveren bestellingen</h5>

                <?php
                // Echo tabel met alle bestellingen waarvan orderDate >= huidige datum en paymentStatus = 1
                    $sql = "SELECT * FROM orders WHERE orderDate >= CURDATE() AND paymentStatus = 1 ORDER BY orderDate;";
                    if ($result1 = mysqli_query($con,$sql))
                    {
                        echo '
                        <div class="table mx-0 px-0 adminTable">
                            <div class="tr">
                                <div class="th">Leveringsdatum</div>
                                <div class="th">Address</div>
                                <div class="th">Wat er geleverd moet worden:</div> 
                            </div>
                        ';
                        while ($row1 = mysqli_fetch_assoc($result1))
                        {
                            $leveringsNota = "";
                            $address = "";

                            // Get the address
                            $sql = "SELECT * from address where addressID =".$row1['addressID'].";";
                            $result2 = mysqli_query($con,$sql);
                            $row2 = mysqli_fetch_assoc($result2);
                            $address .= $row2['streetName']." ".$row2['streetNumber'].", ".$row2['postalCode']." ";
                            mysqli_free_result($result2);

                            // Get the municipName
                            $sql = "SELECT municipName from postalcodes where postalCode =".$row2['postalCode'].";";
                            $result3 = mysqli_query($con,$sql);
                            $row3 = mysqli_fetch_assoc($result3);
                            $address .= $row3['municipName'];
                            mysqli_free_result($result3);

                           // Get the amounts & productID's from orderDetails
                            $sql = "SELECT productID, amount from orderDetails where orderID =".$row1['orderID'].";";
                            $result4 = mysqli_query($con, $sql);
                            $rowCount = mysqli_num_rows($result4);
                            $count = 0;

                            while ($row4 = mysqli_fetch_assoc($result4))
                            {
                                $count +=1;

                                $sql = "SELECT productName from products where productID=".$row4['productID'].";";
                                $result5 = mysqli_query($con,$sql);
                                $row5 = mysqli_fetch_assoc($result5);
                                $leveringsNota .= $row4['amount']." x ".$row5['productName'];  
                                mysqli_free_result($result5);

                                if ($count < $rowCount)
                                {
                                    $leveringsNota.=", ";
                                }
                            }
                            mysqli_free_result($result4);


                            echo '
                            <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                                <input class="form-control" type="hidden" name="orderID" value="'.$row1['orderID'].'">
                                <span class="td" style="width:8rem;"><input class="form-control" type="text" name="orderDate" value="'.$row1['orderDate'].'"></span>
                                <span class="td"><input class="form-control" type="text" name="address" value="'.$address.'"></span>
                                <span class="td"><input class="form-control" type="text" name="orderDetails" value="'.$leveringsNota.'"></span>
                            </form>
                            ';
                        }
                        mysqli_free_result($result1);
                        echo '
                        </div>
                        ';
                    }
                    else
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    }
                ?>

            </div>
        </div>

        <hr class="mt-5 mb-5">

        <div class="row">

            <div class="col-lg-7 border">
                <h5 class="my-3 text-center">Invoer gemaakte overschrijvingen</h5>

                <div class="table mx-0 px-0 adminTable">
                    <div class="tr">
                        <div class="th">Bedrag</div>
                        <div class="th">Gestructureerde mededeling</div>
                    </div>
                    <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                        <span class="td"><input class="form-control input-lg" type="number" min="0" step='0.01' name="orderPrice"></span>
                        <span class="td"><input class="form-control input-sm" type="text" pattern="/\-\-\-+[0-9]{3}+\/+[0-9]{4}+\/+[0-9]{5}+\-\-\-/" name="structRef"></span> 
                        <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Betaling controleren" name="checkPayment"></span>
                    </form>
                </div>

            </div>

            <div class="col-lg-5 border">
                <h5 class="my-3 text-center">Leveringsgemeentes</h5>
                <?php
                    // echo tabel met alle gemeentenamen en postcodes
                    $sql = "SELECT * from postalcodes";
                    if ($result = mysqli_query($con,$sql))
                    {
                        echo '
                        <div class="table mx-0 px-0 adminTable">
                            <div class="tr">
                                <div class="th">Postcode</div>
                                <div class="th">Gemeentenaam</div>
                            </div>
                            ';
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            echo '
                            <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                                <input type="hidden" name="postalCode" value="'.$row['postalCode'].'">
                                <span class="td"><input class="form-control input-lg" type="text" value="'.$row['postalCode'].'"></span>
                                <span class="td"><input class="form-control input-sm" type="text" name="municipName" value="'.$row['municipName'].'"></span> 
                                <span class="td"><input class="form-control input-sm btn-danger" type="submit" value="Verwijderen" name="removeMunicip"></span>
                            </form>
                            ';
                        }
                        mysqli_free_result($result);
                        echo '
                        <div class="tr my-1">&nbsp;</div>

                        <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                            <span class="td"><input class="form-control input-sm" type="number" name="postalCode" min="1000" max="9999" step="1"></span>
                            <span class="td"><input class="form-control" type="text" name="municipName"></span>
                            <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Toevoegen" name="addMunicip"></span>
                        </form>
                    </div>';
                    }
                    else
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    }

                ?>
            </div>

        </div>

        <hr class="mt-5 mb-5">
        <div class="row">
            <div class="col-lg-12">
                <h5>Categorieën</h5>
            <?php
            $sql = "SELECT * from categories;";
            if ($result = mysqli_query($con,$sql))
            {
                echo '
                <div class="table mx-0 px-0 adminTable">
                    <div class="tr">
                        <div class="th">CategorieID</div>
                        <div class="th">Categorienaam</div>
                    </div>
                    ';
                while ($row = mysqli_fetch_assoc($result))
                {
                    echo '
                    <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                        <input type="hidden" name="categoryID" value="'.$row['categoryID'].'">
                        <span class="td"><input class="form-control" type="text" value="'.$row['categoryID'].'"></span> 
                        <span class="td"><input class="form-control" type="text" name="categoryName" value="'.$row['categoryName'].'"></span>
                        <span class="td"><input class="form-control btn-warning" type="submit" value="Wijzigen" name="changeCategory"></span>
                    </form>
                    ';
                }
                mysqli_free_result($result);
                    echo '
                    <div class="tr mt-1">&nbsp;</div>
                        <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                            <span class="td"></span>
                            <span class="td"><input class="form-control" type="text" name="categoryName" value="'.$row['categoryName'].'"></span>
                            <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Toevoegen" name="addCategory"></span>
                        </form>
                    </div>';
            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
            }
            ?>
            </div>
        </div>
        
        <hr class="mt-5 mb-5">

        <div class="row">
            <div class="col-lg-6 border">
                <h5 class="my-3">Gebruikersadministratie</h5>
                <?php
                
                $sql = "SELECT * from users";
                if ($result = mysqli_query($con,$sql))
                {
                    echo '
                    <div class="table mx-0 px-0 adminTable">
                        <div class="tr">
                            <div class="th">Email</div>
                            <div class="th">Voornaam</div>
                            <div class="th">Achternaam</div>
                        </div>
                        ';
                    while ($row = mysqli_fetch_assoc($result))
                    {
                        echo 
                            '<form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                                <input class="form-control input-sm" type="hidden" name="userID" value="'.$row['userID'].'">
                                <span class="td"><input class="form-control" type="email" name="email" value="'.$row['email'].'"></span> 
                                <span class="td"><input class="form-control" type="text" name="fname" value="'.$row['fname'].'"></span>
                                <span class="td"><input class="form-control" type="text" name="lname" value="'.$row['lname'].'"></span> 
                                <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Wijzigen" name="changeUser"></span>
                                <span class="td"><input class="form-control input-sm btn-danger" type="submit" value="Verwijderen" name="removeUser"></span>
                            </form>
                            ';
                    }
                    mysqli_free_result($result);
                    echo '
                    </div>
                    ';
                }
                else
                {
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                }
                ?>
            </div>

            <div class="col-lg-6 border">
                <h5 class="my-3 text-center ">Administrators aanstellen</h5>
                <?php
                $sql = "SELECT email, fName, lName  from users where admin=1";
                if ($result = mysqli_query($con,$sql))
                {
                    echo '
                    <div class="table mx-0 px-0 adminTable">
                        <div class="tr">
                            <div class="th">Email</div>
                            <div class="th">Voornaam</div>
                            <div class="th">Achternaam</div>
                        </div>
                    ';
                    while ($row = mysqli_fetch_assoc($result))
                    {
                        echo '
                        <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                            <input type="hidden" name="email" value="'.$row['email'].'">
                            <span class="td"><input class="form-control" type="text" value="'.$row['email'].'"></span>
                            <span class="td"><input class="form-control" type="text" name="fName" value="'.$row['fName'].'"></span>
                            <span class="td"><input class="form-control" type="text" name="lName" value="'.$row['lName'].'"></span>
                            <span class="td"><input class="form-control input-sm btn-danger" type="submit" value="Verwijderen" name="removeAdmin"></span>
                        </form>
                        ';
                    }
                    mysqli_free_result($result);
                    echo '
                        <div class="tr my-1">&nbsp;</div>

                        <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                            <span class="td"><input class="form-control input-sm" type="email" name="email"></span>
                            <span class="td"></span>
                            <span class="td"></span>
                            <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Toevoegen" name="addAdmin"></span>
                        </form>
                    </div>';
                }
                else
                {
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                }
                ?>
            </div>
        </div>

        <hr class="mt-5 mb-5">

        <div class="row">
            <div class="col border">
                <h5 class="my-3">Productadministratie</h5>
                <?php
                
                $sql = "SELECT * from products;";
                if ($result = mysqli_query($con,$sql))
                {
                    echo '
                    <div class="table mx-0 px-0 adminTable">
                        <div class="tr">
                            <div class="th">Productnaam</div>
                            <div class="th">Productprijs</div>
                            <div class="th">Productafbeelding</div>
                            <div class="th">Productbeschrijving</div>
                            <div class="th">Beschikbare hoeveelheid</div>
                            <div class="th">CategorieID</div>
                        </div>
                        ';
                    while ($row = mysqli_fetch_assoc($result))
                    {
                        echo 
                            '<form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                                <input class="form-control" type="hidden" name="productID" value="'.$row['productID'].'">
                                <span class="td"><input class="form-control" type="text" name="productName" value="'.$row['productName'].'"></span> 
                                <span class="td"><input class="form-control input-sm" type="number" name="productPrice" min="0" step="0.01" value="'.$row['productPrice'].'"></span>
                                <span class="td"><input class="form-control" type="text" name="productPicture" value="'.$row['productPicture'].'"></span>
                                <span class="td"><input class="form-control" type="text" name="productDescription" value="'.$row['productDescription'].'"></span> 
                                <span class="td"><input class="form-control input-sm" type="number" name="availableAmount" min="0" step="1" value="'.$row['availableAmount'].'"></span>
                                <span class="td"><input class="form-control input-sm" type="number" name="categoryID" min="0" step="1" value="'.$row['categoryID'].'"></span>
                                <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Wijzigen" name="changeProduct"></span>
                                <span class="td"><input class="form-control input-sm btn-danger" type="submit" value="Verwijderen" name="removeProduct"></span>
                            </form>
                            ';
                    }
                    mysqli_free_result($result);
                    echo '
                        <div class="tr my-1">&nbsp;</div>
                        <form method="POST" action="./Scripts/LoggedIn/adminActions.php" class="tr form-group">
                            <span class="td"><input class="form-control" type="text" name="productName"></span> 
                            <span class="td"><input class="form-control input-sm" type="number" name="productPrice" min="0" step="0.01"></span>
                            <span class="td"><input class="form-control" type="text" name="productPicture"></span>
                            <span class="td"><input class="form-control" type="text" name="productDescription"></span> 
                            <span class="td"><input class="form-control input-sm" type="number" name="availableAmount" min="0" step="1"></span>
                            <span class="td"><input class="form-control input-sm" type="number" name="categoryID" min="0" step="1"></span>
                            <span class="td"><input class="form-control input-sm btn-success" type="submit" value="Toevoegen" name="addProduct"></span>
                            <span class="td">&nbsp;</span>
                        </form>
                    </div>';
                }
                else
                {
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                }
                ?>
            </div>
        </div>

        <div class="mt-4 mb-4">&nbsp;</div>

    </div>

<?php
    require "./Content/footer.html";
?>

</body>
</html>