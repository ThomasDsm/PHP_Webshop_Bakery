<?php
session_start();

include_once "./Scripts/logNiveau1.php";
include_once "./Scripts/errorHandling.php";

if (!isset($_SESSION['email']))
{
    header("Location: ./index.php");
    exit();
}

require "./Scripts/connection.php";

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
  <script src="./Scripts/LoggedIn/Order.js"></script>
  <script src="./Scripts/LoggedIn/Logout.js"></script>
  <link rel="stylesheet" href="./Styles/Index.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-md navbar-light sticky-top py-4">

        <a class="navbar-brand navbar-left" href="./index.php">
            <img src="./Images/logo.jpg" alt="Logo" id="logoPatissien"/>
        </a>

        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <ul class="nav navbar-nav navbar-left">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="./index.php">Shop</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="./about.php">Over&nbsp;Sien</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link">Winkelkar</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link">Bestelling</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" id="logoutKnop">Uitloggen</a>
                </li>
                <?php
                if (isset($_SESSION['admin']))
                {
                    echo('
                    <li class="nav-item mx-2">
                        <a class="nav-link" href="./admin.php">Admin</a>
                    </li>
                    ');
                }
                ?>
            </ul>
        </div>
    </nav>
    
    <?php
        require "./Content/LoggedIn/placement.html";
    ?>
    
    <div class="container mt-5 mx-auto">
        <div class="row text-center">
            <div class="col">
                <button id="placeButton" class="btn btn-warning">Bestelling plaatsen</button>
            </div>
        </div>

        <hr class="mt-3 mb-3">

        <div class="row text-center">

            <div class="col-lg-12">
                <h5 id="highlightMe">Er wordt geleverd aan de volgende gemeentes:</h5>
                <?php
                    // echo tabel met alle gemeentenamen en postcodes
                    $sql = "SELECT * from postalcodes";
                    if ($result = mysqli_query($con,$sql))
                    {
                        while ($row = mysqli_fetch_assoc($result))
                        {
                            echo '
                            <kbd>'.$row['postalCode'].', '.$row['municipName'].'</kbd>
                            ';
                        }
                    }
                    else
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    }
                ?>
            </div>
        </div>
        <hr class="mt-3 mb-3">
        <div class="row">
            <div class="col-auto py-5 mx-0">
                <h1>Huidige bestellingen</h1>
                <p>Het kan tot 2 dagen duren totdat een overschrijving bevestigd wordt. </p>
                <div id="orderContainer">
                </div>   
            </div>
            
        </div>
    </div>
<?php
require "./Content/footer.html";
echo("\n");
?>
</body>