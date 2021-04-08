<!-- NAVBAR https://www.w3schools.com/bootstrap4/ -->
<nav class="navbar navbar-expand-md navbar-light sticky-top py-4" >

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
                <a class="nav-link" id="cartKnop">Winkelkar</a>
            </li>
            <li class="nav-item mx-2">
                <a class="nav-link" href="./order.php">Bestellingen</a>
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