<?php
include_once "./connection.php";
include_once "./logNiveau2.php";
include_once "./errorHandling.php";


function makeSQLfriendly($value,$connection)
{
    return mysqli_real_escape_string($connection,strip_tags(trim(htmlspecialchars($value))));
}

$catName = makeSQLfriendly($_POST['categoryName'], $con);
$sql = "SELECT * FROM products where  availableAmount > 0 and categoryID = (SELECT categoryID FROM categories where categoryName = '".$catName."')";
if ($result = mysqli_query($con,$sql))
{
    if (mysqli_num_rows($result) > 0) 
    {
        while ($row = mysqli_fetch_assoc($result))
        {
            echo ('
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <a href="#">
                        <img class="card-img-top" src="'.$row["productPicture"].'" alt="productPicture">
                    </a>
                    <div class="card-body">
                        <h4 class="card-title">
                            <a class="productName">'.$row['productName'].'</a>
                        </h4>
                        <h5>&euro; '.$row['productPrice'].'</h5>
                        <p class="card-text">'.$row['productDescription'].'</p>
                    </div>
                    <div class="card-footer">
                        <button class="btn">Toevoegen aan winkelwagen</button>
                        <input type=hidden value="'.$row['productID'].'" />
                    </div>
                </div>
            </div>'."\n\n");
        }
        mysqli_free_result($result);
    }
    
    $sql = "SELECT * FROM products where  availableAmount = 0 and categoryID = (SELECT categoryID FROM categories where categoryName = '".$catName."')";
    if ($result = mysqli_query($con,$sql))
    {
        if (mysqli_num_rows($result) > 0) 
        {
            while ($row = mysqli_fetch_assoc($result))
            {
                echo ('
                <div class="col-lg-4 col-md-6 mb-4 text-muted">
                    <div class="card h-100">
                        <a href="#">
                            <img class="card-img-top" src="'.$row["productPicture"].'" alt="productPicture">
                        </a>
                        <div class="card-body">
                            <h4 class="card-title">
                                <a id="productName">'.$row['productName'].'</a>
                            </h4>
                            <h5>&euro; '.$row['productPrice'].'</h5>
                            <p class="card-text">'.$row['productDescription'].'</p>
                        </div>
                        <div class="card-footer">
                            <p class="my-1 alert-dark">Uitverkocht</p>
                        </div>
                    </div>
                </div>'."\n\n");

            }
        }
    }
    else
    {
        trigger_error( 'productGeneration SQL failed with query: '.$sql , E_USER_NOTICE );
        echo "dbError";
        exit();
    }
}
else
{
    trigger_error( 'productGeneration SQL failed with query: '.$sql , E_USER_NOTICE );
    echo "dbError";
    exit();
}
?>