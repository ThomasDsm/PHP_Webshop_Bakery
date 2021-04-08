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
   // Log this ip tried to make POST
   trigger_error( 'The following IP tried to POST to adminActions.php without being loggedIn'.$ip , E_USER_NOTICE );
   exit();
}
if (!isset($_SESSION['admin']))
{
    // https://stackoverflow.com/questions/3003145/how-to-get-the-client-ip-address-in-php
    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    // Log this ip tried to make POST
    trigger_error( 'The following IP tried to POST to adminActions.php without being admin'.$ip , E_USER_NOTICE );
    exit();
}

if (isset($_POST['addCategory']))
{
    if (isset($_POST['categoryName']))
    {
        $categoryName = makeSQLfriendly($_POST['categoryName'],$con);
        if (strlen($categoryName) > 30 || $categoryName =="")
        {
            header("Location: ../../admin.php?invalidCategoryName");
            exit();
        }
        else
        {
            $sql = "INSERT INTO categories (categoryName) VALUES ('".$categoryName."');";
            if (!mysqli_query($con,$sql))
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?errorInsertingCategory");
            }
            else
            {
                header("Location: ../../admin.php");
            }
            exit();
        }
    }
}
if (isset($_POST['changeCategory']))
{
    if (isset($_POST['categoryName']) && isset($_POST['categoryID']))
    {
        $categoryName = makeSQLfriendly($_POST['categoryName'],$con);
        $categoryID = makeSQLfriendly($_POST['categoryID'],$con);

        if (strlen($categoryName) > 30 || $categoryName =="")
        {
            header("Location: ../../admin.php?invalidCategoryName");
            exit();
        }
        else
        {
            $sql = "UPDATE categories SET categoryName='".$categoryName."' where categoryID = ".$categoryID.";";
            if (!mysqli_query($con,$sql))
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?errorUpdatingCategoryName");
            }
            else
            {
                header("Location: ../../admin.php");
            }
            exit();
        }
    }
}

if (isset($_POST['changeUser']))
{
    if (isset($_POST['userID']) && isset($_POST['email']) && isset($_POST['fname']) && isset($_POST['lname']))
    {
        $userID = makeSQLfriendly($_POST['userID'], $con);
        $email = makeSQLfriendly($_POST['email'], $con);
        $fname =  makeSQLfriendly($_POST['fname'], $con);
        $lname =  makeSQLfriendly($_POST['lname'], $con);
        $sql = "SELECT userID FROM users where userID =".$userID.";";
        if ($result = mysqli_query($con,$sql))
        {
            if (mysqli_num_rows($result) == 0) { header("Location: ../../admin.php?userIDnotFound"); }
            else if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { header("Location: ../../admin.php?invalidEmail"); }
            else if ($fname == "" || strlen($fname) > 30) { header("Location: ../../admin.php?invalidfName"); }
            else if ($lname == "" || strlen($lname) > 30) { header("Location: ../../admin.php?invalidlName"); }
            else 
            {
                $sql = "UPDATE users SET email='".$email."', fname='".$fname."', lname='".$lname."' WHERE userID=".$userID.";";
                if (mysqli_query($con,$sql)) { header("Location: ../../admin.php"); }
                else { 
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    header("Location: ../../admin.php?updateUserFailed");
                }
                exit();
            }
            mysqli_free_result($result);
        }
        else 
        {
            trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
            header("Location: ../../admin.php");
            exit();
        }

    }
}

if (isset($_POST['removeUser']))
{
    if (isset($_POST['userID']))
    {
        $userID = makeSQLfriendly($_POST['userID'],$con);
        if ($userID == $_SESSION['userID'])
        {
            header("Location: ../../admin.php?cantRemoveActiveUser");
            exit();
        }
        else
        {
            $sql = "SELECT userID FROM users where userID =".$userID.";";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    mysqli_free_result($result);
                    $sql = "DELETE from users where userID=".$userID.";";
                    if (mysqli_query($con,$sql))
                    {
                        header("Location: ../../admin.php");
                    }
                    else
                    {
                        header("Location: ../../admin.php?deleteFailed");
                        trigger_error('userDelete SQL failed with query: '.$sql , E_USER_NOTICE );
                    }
                    exit();
                }
                else
                {
                    header("Location: ../../admin.php?userIDnotFound");
                    exit();
                }
            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php");
                exit();
            }
        }
    } 
}

if (isset($_POST['checkPayment']))
{
    if (isset($_POST['orderPrice']) && isset($_POST['structRef']))
    {
        $regexp = "/\-\-\-+[0-9]{3}+\/+[0-9]{4}+\/+[0-9]{5}+\-\-\-/";
        $orderPrice = makeSQLfriendly($_POST['orderPrice'], $con);
        if (preg_match($regexp, $_POST['structRef'])) { header("Location: ../../admin.php?invalidStructRef"); }
        else if ($orderPrice <= 0 ) { header("Location: ../../admin.php?invalidOrderPrice"); }
        else
        {
            $sql = "SELECT orderID, orderPrice from orders where structRef='".$_POST['structRef']."';";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    $row = mysqli_fetch_assoc($result);
                    mysqli_free_result($result);
                    if ($row['orderPrice'] == $orderPrice)
                    {
                        $sql = "UPDATE orders SET paymentStatus = 1 WHERE structRef ='".$_POST['structRef']."';";
                        if (mysqli_query($con,$sql))
                        {
                            header("Location: ../../admin.php");
                        }
                        else
                        {
                            trigger_error( 'paymentStatus update SQL failed with query: '.$sql , E_USER_NOTICE );
                            header("Location: ../../admin.php?errorUpdatingPaymentStatus");
                        }
                        exit();
                        
                    }
                    else
                    {
                        header("Location: ../../admin.php?orderPriceIncorrect");
                        exit();
                    }
                }
                else
                {
                    header("Location: ../../admin.php?structRefNotFound");
                    exit();
                }
            }
            else 
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php");
                exit();
            }
        }
    }
}

if (isset($_POST['removeAdmin']))
{
    if (isset($_POST['email']))
    {
        $email = makeSQLfriendly($_POST['email'], $con);

        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {header("Location: ../../admin.php?invalidEmail"); }
        else
        {   
            // Current user cannot be made not-admin
            if ($_SESSION['email'] == $email)
            {
                header("Location: ../../admin.php?cantUnmoderateYourself");
                exit();
            }
            else
            {
                // Check if email exists in DB, if so check if email is already not-admin
                $sql = "SELECT email, admin from users where email ='".$email."';";
                if ($result = mysqli_query($con,$sql))
                {
                    if (mysqli_num_rows($result) > 0)
                    {
                        $row = mysqli_fetch_assoc($result);
                        mysqli_free_result($result);
                        if ($row['admin'] != 0)
                        {
                            $sql = "UPDATE users set admin=0 where email='".$email."';";
                            if (mysqli_query($con,$sql))
                            {
                                header("Location: ../../admin.php");
                            }
                            else
                            {
                                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                                header("Location: ../../admin.php?errorUpdatingAdmin");
                            }
                            exit();
                        }
                        else
                        {
                            header("Location: ../../admin.php?userAlreadyadmin");
                            exit();
                        }
                    }
                    else
                    {
                        mysqli_free_result($result);
                        header("Location: ../../admin.php?emailDoesNotExist");
                        exit();
                    }
                }
                else
                {
                    trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    header("Location: ../../admin.php?errorCheckingEmail");
                    exit();
                }
            }
        }
    }
}

if (isset($_POST['addAdmin']))
{
    if (isset($_POST['email']))
    {
        $email = makeSQLfriendly($_POST['email'], $con);

        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) {header("Location: ../../admin.php?invalidEmail"); }
        else
        {
            // Check if email exists in DB, if so check if email is already admin
            $sql = "SELECT email, admin from users where email ='".$email."';";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    $row = mysqli_fetch_assoc($result);
                    mysqli_free_result($result);
                    if ($row['admin'] != 1)
                    {
                        $sql = "UPDATE users set admin=1 where email='".$email."';";
                        if (mysqli_query($con,$sql))
                        {
                            header("Location: ../../admin.php");
                        }
                        else
                        {
                            trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                            header("Location: ../../admin.php?errorUpdatingAdmin");
                        }
                        exit();
                    }
                    else
                    {
                        header("Location: ../../admin.php?userAlreadyadmin");
                        exit();
                    }        
                }
                else
                {
                    mysqli_free_result($result);
                    header("Location: ../../admin.php?emailDoesNotExist");
                    exit();
                }
            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?errorCheckingEmail");
                exit();
            }
        }
    }
}

if (isset($_POST['removeMunicip']))
{
    if (isset($_POST['postalCode']) && isset($_POST['municipName']))
    {
        $postalCode = makeSQLfriendly($_POST['postalCode'], $con);

        if ($postalCode == "" || $postalCode < 1000 || $postalCode > 9999) {header("Location: ../../admin.php?invalidPostalCode"); }
        else
        {
            // Check if postalCode does exist
            $sql = "SELECT postalCode from postalCodes where postalCode =".$postalCode.";";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    mysqli_free_result($result);
                    $sql = "DELETE FROM postalCodes where postalCode=".$postalCode.";";
                    if (!mysqli_query($con,$sql))
                    {
                        trigger_error( 'postalcode delete SQL failed with query: '.$sql , E_USER_NOTICE );
                        header("Location: ../../admin.php?deleteMuncipFailed.php");
                    }
                    else
                    {
                        header("Location: ../../admin.php");
                    }
                    exit();
        
                }
                else
                {
                    mysqli_free_result($result);
                    header("Location: ../../admin.php?postalCodeDoesNotExist");
                    exit();
                }
            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?errorCheckingPostalcode");
                exit();
            }
        }
    }
}

if (isset($_POST['addMunicip']))
{
    if (isset($_POST['postalCode']) && isset($_POST['municipName']))
    {
        $postalCode = makeSQLfriendly($_POST['postalCode'], $con);
        $municipName = makeSQLfriendly($_POST['municipName'], $con);

        if ($postalCode == "" || $postalCode < 1000 || $postalCode > 9999) {header("Location: ../../admin.php?invalidPostalCode"); }
        else if ($municipName == "" || strlen($municipName) > 30) { header("Location: ../../admin.php?invalidMunicipName"); }
        else
        {
            // Check if postalCode does not exist
            $sql = "SELECT postalCode from postalCodes where postalCode =".$postalCode.";";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) == 0)
                {
                    mysqli_free_result($result);
                    $sql = "INSERT INTO postalCodes (postalCode, municipName) VALUES (".$postalCode.",'".$municipName."');";
                    if(!mysqli_query($con,$sql))
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                    }
                    header("Location: ../../admin.php");
                    exit();        
                }
                else
                {
                    mysqli_free_result($result);
                    header("Location: ../../admin.php?postalCodeExists");
                    exit();
                }
            }
            else
            {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?errorCheckingPostalcode");
                exit();
            }
        }
    }
}

if (isset($_POST['changeProduct']))
{
    // Check if all variables are set
    if (isset($_POST['productName']) && isset($_POST['productID']) && isset($_POST['productPrice']) && isset($_POST['productPicture']) && isset($_POST['productDescription']) && isset($_POST['availableAmount']) && isset($_POST['categoryID']) )
    {
        // Check if all variables are not empty and have the correct type
        $productID = makeSQLfriendly($_POST['productID'], $con);
        $productName = htmlentities(makeSQLfriendly($_POST['productName'], $con));
        $productPrice = makeSQLfriendly($_POST['productPrice'], $con);
        $productPicture = makeSQLfriendly($_POST['productPicture'], $con);
        $productDescription = htmlentities(makeSQLfriendly($_POST['productDescription'], $con));
        $availableAmount = makeSQLfriendly($_POST['availableAmount'], $con);
        $categoryID = makeSQLfriendly($_POST['categoryID'], $con);

        if (strlen($productName) > 30) { header("Location: ../../admin.php?strlenProdName");}
        else if (preg_match('/\.\d{3,}/', $productPrice)) { header("Location: ../../admin.php?invalidPrice");} // https://stackoverflow.com/questions/20145385/php-how-to-check-if-a-number-has-more-than-two-decimals/20145557
        else if (intval($productPrice) > 999) { header("Location: ../../admin.php?priceToHigh");}
        else if (substr($productPicture, 0, 9) !== "./Images/" ) { header("Location: ../../admin.php?invalidImage");}
        else if (strlen($productDescription) > 255) { header("Location: ../../admin.php?descriptionToLong");}
        else if (!ctype_digit($availableAmount)) { header("Location: ../../admin.php?availableNaN"); }
        else
        {
            // Check if categoryID exists
            $sql = "SELECT categoryID from categories where categoryID =".$categoryID.";";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    mysqli_free_result($result);
                    $sql = "UPDATE products 
                    set productName='".$productName."', productPrice=".$productPrice.", productPicture='".$productPicture."', productDescription='".$productDescription."', availableAmount=".$availableAmount.", categoryID=".$categoryID." 
                    WHERE productID=".$productID.";";
                    mysqli_query($con, $sql);
                    header("Location: ../../admin.php");
                }
                else
                {
                    mysqli_free_result($result);
                    header("Location: ../../admin.php?categoryDoesNotExist");
                }
                exit();
            }
            else {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                exit();
            }
        }
    }
}

if (isset($_POST['removeProduct']))
{
    if (isset($_POST['productID']))
    {
        $productID = makeSQLfriendly($_POST['productID'],$con);
        $sql = "DELETE FROM products where productID=".$productID.";";
        if (!mysqli_query($con,$sql))
        {
            trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
        }
        header("Location: ../../admin.php");
        exit();
    }
}

if (isset($_POST['addProduct']))
{
    // Check if all variables are set
    if (isset($_POST['productName']) && isset($_POST['productPrice']) && isset($_POST['productPicture']) && isset($_POST['productDescription']) && isset($_POST['availableAmount']) && isset($_POST['categoryID']) )
    {
        // Check if all variables are not empty and have the correct type
        $productName = htmlentities(makeSQLfriendly($_POST['productName'], $con));
        $productPrice = makeSQLfriendly($_POST['productPrice'], $con);
        $productPicture = makeSQLfriendly($_POST['productPicture'], $con);
        $productDescription = htmlentities(makeSQLfriendly($_POST['productDescription'], $con));
        $availableAmount = makeSQLfriendly($_POST['availableAmount'], $con);
        $categoryID = makeSQLfriendly($_POST['categoryID'], $con);

        if (strlen($productName) > 30 || $productName == "") { header("Location: ../../admin.php?strlenProdName");}
        else if (preg_match('/\.\d{3,}/', $productPrice) || $productPrice == "") { header("Location: ../../admin.php?invalidPrice");} // https://stackoverflow.com/questions/20145385/php-how-to-check-if-a-number-has-more-than-two-decimals/20145557
        else if (intval($productPrice) > 999) { header("Location: ../../admin.php?priceToHigh");}
        else if (substr($productPicture, 0, 9) !== "./Images/" ) { header("Location: ../../admin.php?invalidImage");}
        else if (strlen($productDescription) > 255) { header("Location: ../../admin.php?descriptionToLong");}
        else if (!ctype_digit($availableAmount)) { header("Location: ../../admin.php?availableNaN"); }
        else
        {
            // Check if categoryID exists
            $sql = "SELECT categoryID from categories where categoryID =".$categoryID.";";
            if ($result = mysqli_query($con,$sql))
            {
                if (mysqli_num_rows($result) > 0)
                {
                    mysqli_free_result($result);
                    $sql = "INSERT INTO products 
                    (productName, productPrice, productPicture, productDescription, availableAmount, categoryID) 
                    VALUES ('".$productName."', ".$productPrice.", '".$productPicture."', '".$productDescription."', ".$availableAmount.", ".$categoryID." );";
                    if (!mysqli_query($con, $sql))
                    {
                        trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                        header("Location: ../../admin.php?insertFailed");
                    }
                    else
                    {
                        header("Location: ../../admin.php");
                    }
                    exit();
                    
                }
                else
                {
                    mysqli_free_result($result);
                    header("Location: ../../admin.php?categoryDoesNotExist");
                    exit();
                }
            }
            else {
                trigger_error('SQL failed with query: '.$sql , E_USER_NOTICE );
                header("Location: ../../admin.php?insertFailed");
                exit();
            }
        }  
    }  
}
?>