<?php
include_once "../logNiveau3.php";
include_once "../errorHandling.php";
require '../connection.php';

session_start();

function makeSQLfriendly($value,$connection)
{
    // Hhtmlspecialchars is not neccessary in these situations
    return mysqli_real_escape_string($connection,strip_tags(trim($value)));
}

// Is user logged in?
if (!isset($_SESSION['email']))
{
    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

    trigger_error( 'The following IP tried to GET to cartActions.php without being loggedin'.$ip , E_USER_NOTICE );
    exit();
}

// Initialize cart if needed
if (!isset($_SESSION['cart']))
{
    $_SESSION['cart']= array();
}

// If GET parameter clear isset; clear cart
if (isset($_GET['clear'])) 
{
    unset($_SESSION['cart']);
    echo('empty');
    exit();
}

// If GET parameter cart isset; echo cart contents
if (isset($_GET['cart']))
{
    if (empty($_SESSION['cart']))
    {
        echo("empty");
    }
    else 
    {
        echo(json_encode($_SESSION['cart'],JSON_PRETTY_PRINT));
    }
}

// if GET parameter add & GET amount isset:
if (isset($_GET['add']) && isset($_GET['amount']))
{
    // Check if vars are not empty, filled with integer numbers and greater than 0
    $productID = makeSQLfriendly($_GET['add'],$con);
    $amount = makeSQLfriendly($_GET['amount'],$con);
    if (!empty($productID) && !empty($amount))
    {
        if ((int) $productID == $productID && (int) $amount == $amount)
        {
            // Type cast to remove leading zero's if there are 
            $productID = (int) $productID;
            $amount = (int) $amount;

            if ($amount > 0 && $productID >= 0)
            {
                // Check if productID exists
                $sql = "SELECT * FROM products WHERE productID = $productID";
                if ($result = mysqli_query($con,$sql))
                {
                    if (mysqli_num_rows($result) > 0)
                    {
                        $row = mysqli_fetch_assoc($result);
                        $productName = $row['productName'];
                        $available = $row['availableAmount'];
                        $pricePerItem = $row['productPrice'];
                        mysqli_free_result($result);
                        $foundInArray = 0;

                        // Check if item is already in cart
                        foreach ( $_SESSION['cart'] as $cartrow => $item)
                        {   
                            // If item is found in cart
                            if ($item['productID'] == $productID)
                            {
                                $foundInArray = 1;

                                // If total is more than available
                                $totalAvailable = $available - $item['amount'];
                                if ( $totalAvailable < $amount)
                                {
                                    echo($totalAvailable);
                                }
                                // Else add to total amount
                                else
                                {
                                    $_SESSION['cart'][$cartrow]['amount'] += $amount;
                                    echo('succes');
                                }
                                exit();
                            }
                        }
                        // If item not found in cart
                        if ($foundInArray == 0)
                        {
                            // Check if amount is available
                            if ($available >= $amount)
                            {
                                array_push($_SESSION['cart'], ['productID' => $productID, 'amount' => $amount, 'productName' => $productName, 'price' => $pricePerItem]);
                                echo("succes");
                            }
                            else 
                            {
                                echo($available);
                            }
                            exit();
                        }
                    }
                    else
                    {
                        // Add to logfile: This ip tried to add unexisting productname to cart
                        mysqli_free_result($result);
                        echo("report");
                        exit();
                    }
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
                echo("LE0");
                exit();
            }
        }
    }
}

if (isset($_GET['change']) && isset($_GET['amount']))
{
    $productID = makeSQLfriendly($_GET['change'],$con);
    $amount = makeSQLfriendly($_GET['amount'],$con);
    if (!empty($productID))
    {
        if ((int) $productID == $productID && (int) $amount == $amount)
        {
            if ($productID >= 0 && $amount >= 0)
            {
                // Check if productID exists
                $sql = "SELECT * FROM products WHERE productID = $productID";
                if ($result = mysqli_query($con,$sql))
                {
                    if (mysqli_num_rows($result) > 0)
                    {
                        $row = mysqli_fetch_assoc($result);
                        $productName = $row['productName'];
                        $available = $row['availableAmount'];
                        $pricePerItem = $row['productPrice'];
                        mysqli_free_result($result);

                        // Check if item is already in cart
                        foreach ( $_SESSION['cart'] as $cartrow => $item)
                        {   
                            // If item is found in cart
                            if ($item['productID'] == $productID)
                            {
                                // If total is more than available
                                if ($available < $amount)
                                {
                                    echo($available);
                                    exit();
                                }
                                // Else change total amount
                                else
                                {
                                    // If amount = 0; the cart entry for the productID should be removed
                                    if ($amount == 0)
                                    {
                                        if ($cartrow == 0)
                                        {
                                            array_shift($_SESSION['cart']);
                                        }
                                        else
                                        {
                                            unset($_SESSION['cart'][$cartrow]);
                                        }
                                        echo("cleared");
                                        exit();
                                    }
                                    else
                                    {
                                        $_SESSION['cart'][$cartrow]['amount'] = $amount;
                                        echo("succes");
                                        exit();
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        // Add to logfile: This ip tried to add unexisting productname to cart
                        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                        trigger_error( 'The following IP / email tried to add unexisting product to cart'.$ip." / ".$_SESSION['email'] , E_USER_NOTICE );
                        mysqli_free_result($result);
                        echo("report");
                        exit();
                    }
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
                echo("LE0");
                exit();
            }
        }
    }
}
?>