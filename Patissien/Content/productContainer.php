<!-- CONTAINER -->
<div class="container">
  <div class="row my-5">
    <div class="col-lg-2 mt-lg-5 mt-md-0" id="catContainer">  
      <div class="list-group mt-lg-5 mt-md-0" id="categories">
        <?php
        // Voor elke categoryName waar er beschikbare koeken
        $sql = "SELECT categoryName from categories where categoryID in ( SELECT categoryID FROM products);";
        $result = mysqli_query($con,$sql);

        $count=0;
        if (mysqli_num_rows($result) > 0) 
        {
          while ($row = mysqli_fetch_array($result))
          {
            echo "\n\t\t".'<a class="list-group-item">'.$row[0].'</a>';
          }
          echo "\n\n";
        }
        ?>
      </div>
    </div>
    <div class="col-lg-9">
      <h1 class="my-4">Patissien</h1>
      <div class="row mt-4" id="productContainer">
      </div>
    </div>
  </div>
</div>
