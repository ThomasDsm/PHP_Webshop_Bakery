<?php
include_once "../logNiveau3.php";
include_once "../errorHandling.php";
require '../connection.php';

session_start();

function makeSQLfriendly($value,$connection)
{
    // htmlspecialchars is not neccessary in these situations
    return mysqli_real_escape_string($connection,strip_tags(trim($value)));
}

// https://gist.github.com/voku/dd277e9c660f38b8c3a3
function checkDateFormat($date)
{
    // match the format of the date
    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
    {

        // check whether the date is valid or not
        if (checkdate($parts[2],$parts[3],$parts[1])) {
        return true;
        } else {
        return false;
        }

    } else {
        return false;
    }
}

// Is user logged in?
if (!isset($_SESSION['email']))
{
    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    // Log this ip tried to make POST
    trigger_error( $_SESSION['email'].'with IP :'.$ip.'tried to place an order without being logged in' , E_USER_NOTICE );
    exit();
}
if (!isset($_SESSION['cart']))
{ 
    echo("cartError");
    exit();
}
if (empty($_SESSION['cart']))
{
    echo("cartError");
    exit();
}

/*
data: { 
    'postalCode' : postalCode,
    'streetName' : streetName,
    'streetNum' : streetNum,
    'deliveryDate' : deliveryDate,
}
*/
// Are all variables set?
if ( !isset($_POST['streetName']) || !isset($_POST['streetNum']) || !isset($_POST['postalCode']) || !isset($_POST['deliveryDate']) )
{
    echo("setAllVars");
    exit();
}

$streetName = makeSQLfriendly($_POST['streetName'],$con);
$streetNum = makeSQLfriendly($_POST['streetNum'],$con);
$postalCode = makeSQLfriendly($_POST['postalCode'],$con);
$deliveryDate = makeSQLfriendly($_POST['deliveryDate'],$con);

// Next step is to validate all the POST variables

    // Check if streetName only contains letters
    // https://www.sitepoint.com/community/t/check-whether-string-contains-numbers/5953
if (1 === preg_match('~[0-9]~', $streetName))
{
    echo("invalidStreetName");
    exit();
}
if (strlen($streetName) > 64 || strlen($streetName) < 1 )
{   
    echo("invalidStreetName");
    exit();
}


    // Check if streetNum consist of only decimals & smaller than 10000
    // https://www.php.net/ctype_digit
if (!ctype_digit($streetNum))
{
    echo("invalidStreetNumber"); 
    exit();
}
if ($streetNum > 9999)
{
    echo("invalidStreetNumber");
    exit();
}


    // Check if postalcode consist of decimals only & between [1000,9999]
if (!ctype_digit($postalCode))
{ 
    echo("invalidPostalCode");
    exit();
}

if ($postalCode < 1000 || $postalCode > 9999)
{
    echo("invalidPostalCode");
    exit();
}
    // Check if postalCode in the deliveryzone
$sql = "SELECT postalCode from postalCodes where postalCode = ".$postalCode.";";
if ( $result = mysqli_query($con,$sql) )
{
    if (mysqli_num_rows($result) == 0 )
    {
        mysqli_free_result($result);
        echo("noDelivery");
        exit();
    }
}
else
{
    echo("dbError");
    exit();
}


    // Check if the date is correctly formatted as 'yyyy-mm-dd' and is at least 5 days from now
if (checkDateFormat($deliveryDate))
{
    // https://stackoverflow.com/questions/15092639/php-determine-if-the-date-is-in-the-future-using-datetime-object
    $futureDate = new Datetime();
    $futureDate->setTime(0,0,0);
    $futureDate->modify('+5 days');

    if ($deliveryDate < date_format($futureDate,'Y-m-d'))
    {
        echo("dateError");
        exit();
    }
}
else
{
    echo("dateError");
    exit();
}


// Finished checking the POST variables
// Time to make the order

// http://www.sitemasters.be/scripts/1/23/1820/PHP/Genereren_van_een_OGM
function generateRandomOGM($markup = false) {
	$randNumber = rand(1000000000, 9999999999);
	$rest       = $randNumber % 97;
 
	if ($rest === 0) {
		$rest = 97;
	}
	if ($rest < 10) {
		$rest = "0" . $rest;
	}
 
	$ogm = $randNumber.$rest;
	if ($markup) {
		$ogm = "+++".substr($ogm,0,3)."/".substr($ogm,3,4)."/".substr($ogm,7,5)."+++";
	}
 
	return $ogm;
}

// Generate unique OGM
$ogmfound = 1; 
while ($ogmfound == 1)
{
    $OGM = generateRandomOGM(true);
    $sql = "SELECT structRef FROM orders where structRef = '".$OGM."';";
    if ( $result = mysqli_query($con,$sql) )
    {
        if (mysqli_num_rows($result) == 0 )
        {
            $ogmfound = 0;
        }
        mysqli_free_result($result);
    }
    else
    {
        echo("dbError");
        exit();
    }
}

// Before these steps we need to make sure that all products are still available
// Combine this with step 2: Go through the cart; retrieve [productID, price, amount] array; retrieve orderPrice

if (!isset($_SESSION['cart']))
{ 
    echo("cartError");
    exit();
}
if (empty($_SESSION['cart']))
{
    echo("cartError");
    exit();
}

$cartContents = array();    // storage
$orderPrice = 0; 
foreach ( $_SESSION['cart'] as $cartrow => $item)
{
    // Check if product available
    $sql = "SELECT availableAmount from products where productID =".$item["productID"];
    if ( $result = mysqli_query($con,$sql) )
    {
        if (mysqli_num_rows($result) > 0)
        {
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            $availability = $row[0];
            if ($availability < $item['amount'])
            {
                echo($item["productName"]);
                if ($cartrow == 0)
                {
                    array_shift($_SESSION['cart']);
                }
                else
                {
                    unset($_SESSION['cart'][$cartrow]);
                }
                exit();
            }
        }
        else
        {
            echo("dbError");
            exit();
        }
    }
    else
    {
        echo("dbError");
        exit();
    }

    // Get the price of the product
    $sql = "SELECT productPrice from products where productID =".$item["productID"];
    if ( $result = mysqli_query($con,$sql) )
    {
        if (mysqli_num_rows($result) > 0)
        {
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            $price = $row[0];
        }
        else
        {
            echo("dbError");
            exit();
        }
    }
    else
    {
        echo("dbError");
        exit();
    }
    $cartContentRow = array('productID'=>$item['productID'], "price"=>$price, 'amount'=>$item['amount'], 'available'=>$availability);
    array_push($cartContents,$cartContentRow);
    
    $orderPrice += $price * $item['amount'];
}

// address table: postalCode, streetName, streetNumber
// orders table: userID, orderPrice, orderDate, structRef, addressID
// orderDetails table: orderID, productID, amount

// 1. Check if address exists, if not make it; Retrieve addressID

// To make the entry in the orders table, orderPrice has to be calculated;
// To calculate orderPrice; the whole cart needs to be checked. 
// 2. Go through the cart; retrieve [productID, price, amount] array; retrieve orderPrice

// 3. Create orders table entry; Retrieve orderID
// 4. Create orderDetails table entry; Order finished


    // 1. Check if address exists, if not make it; Retrieve addressID  
$sql = "SELECT addressID from address where postalCode =".$postalCode." AND streetName = '".$streetName."' AND streetNumber=".$streetNum.";";
if ( $result = mysqli_query($con,$sql) )
{
    if (mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        $addressID = $row[0];
    }
    else
    {
        mysqli_free_result($result);
        
        $sql = "INSERT INTO address(postalCode,streetName,streetNumber) VALUES (".$postalCode.",'".$streetName."',".$streetNum.");";
        if (!mysqli_query($con,$sql))
        {
            echo("dbError");
            exit();
        }

        $sql = "SELECT addressID from address where postalCode =".$postalCode." AND streetName = '".$streetName."' AND streetNumber=".$streetNum.";";
        if ($result = mysqli_query($con,$sql))
        {
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            $addressID = $row[0];
        }
        else
        { 
            echo("dbError");
            exit();
        }
    }
}
else
{
    echo("dbError");
    exit();
}


    // 2. already done when checking availability


    // 3. Create orders table entry; Retrieve orderID
    // userID, orderPrice, orderDate, structRef, addressID
$sql = "SELECT userID from users where email = '".$_SESSION['email']."';";
if ( $result = mysqli_query($con,$sql) )
{
    if (mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        $userID = $row[0];
    }
    else
    {
        // Report
        echo("dbError");
        exit();
    }
}
else
{
    echo("dbError");
    exit();  
}

$sql = "INSERT INTO orders(userID, orderPrice, orderDate, structRef, addressID) VALUES (".$userID.",".$orderPrice.",'".$deliveryDate."','".$OGM."',".$addressID.");";
if (!mysqli_query($con,$sql) )
{
    echo("dbError");
    exit();
}

$sql = "SELECT orderID from orders where structRef = '".$OGM."';";
if ( $result = mysqli_query($con,$sql) )
{
    if (mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_row($result);
        mysqli_free_result($result);
        $orderID = $row[0];
    }
    else
    {
        // Report
        echo("dbError");
        exit();
    }
}
else
{
    echo("dbError");
    exit();  
}


    // 4. Create orderDetails table entry; Order finished   
    // orderDetails table: orderID, productID, amount
    // EXTRA decrement availableAmount

foreach ($cartContents as $cartContentRow => $item)
{
    $sql = "INSERT INTO orderDetails(orderId,productID,amount) VALUES (".$orderID.",".$item['productID'].",".$item['amount'].");";
    if (!mysqli_query($con,$sql) )
    {
        echo("dbError");
        exit();
    }
}

    // remove available amounts from DB
foreach ($cartContents as $cartContentRow => $item)
{
    // Get previous availableAmount
    $newAvailable = $item['available'] - $item['amount'];
    // update availableAmount
    $sql = "UPDATE products SET availableAmount = ".$newAvailable." WHERE productID = ".$item['productID'].";";
    if (!mysqli_query($con,$sql) )
    {
        echo("dbError");
        exit();
    }
}
unset($_SESSION['cart']);
echo("succes");
?>