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

// Check if user is logged in
if (!isset($_SESSION['email']))
{ 
    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    // Log this ip tried to make POST
    trigger_error( $_SESSION['email'].'with IP :'.$ip.'tried to acces orderActions.php' , E_USER_NOTICE );
    header("Location: ./index.php");
    exit();
}

if (isset($_POST['removeOrder']))
{
    if (isset($_POST['orderID']))
    {
        $orderID = makeSQLfriendly($_POST['orderID'], $con);
    
        // Check if orderID exists
        $sql = "SELECT * from orders where orderID =".$orderID.";";
        if ($result = mysqli_query($con,$sql))
        {
            if (mysqli_num_rows($result) > 0)
            {
                // If orderID exist, check if paymentstatus != 1
                $row = mysqli_fetch_assoc($result);
                mysqli_free_result($result);

                if ($row['paymentStatus'] != 1)
                {
                    // Get all amounts from orderDetails and add them back to the available amount in the products table
                    $sql = "SELECT * from orderDetails where orderID=".$orderID.";";
                    if ($result2 = mysqli_query($con,$sql))
                    {
                        while ($row2 = mysqli_fetch_assoc($result2))
                        {
                            $sql = "UPDATE products SET availableAmount = availableAmount +".$row2["amount"]." WHERE productID =".$row2['productID'].";";
                            if (!mysqli_query($con,$sql))
                            {
                                header("Location: ../../order.php?dbError");
                                exit();
                            }
                        }
                    }
                    else
                    {
                        header("Location: ../../order.php?dbError");
                        exit();
                    }

                    // remove all orderDetails with the orderID
                    $sql = "DELETE from orderDetails where orderID=".$orderID.";";
                    if (!mysqli_query($con,$sql))
                    {
                        header("Location: ../../order.php?dbError");
                        exit();
                    }
                    else
                    {
                        // Remove order entry
                        $sql = "DELETE from orders where orderID=".$orderID.";";
                        if (!mysqli_query($con,$sql))
                        {
                            header("Location: ../../order.php?dbError");
                            exit();
                        }
                        else
                        {
                            header("Location: ../../order.php");
                            exit(); 
                        }
                    }
                }
                else
                {
                    header("Location: ../../order.php?orderIsAlreadyPayed");
                    exit();
                }
            }
            else
            {
                mysqli_free_result($result);
                header("Location: ../../order.php?orderIDdoesNotExist");
                exit();
            }
        }
        else
        {
            header("Location: ../../order.php?dbError");
            exit();
        }
    }
}

header("Location: ../../order.php");
exit(); 
?>