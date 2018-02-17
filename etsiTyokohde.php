<?php session_start();?>

<!DOCTYPE HTML>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>TikoHt2016 Ryhmä 11</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
  <body>
  <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="etusivu.html">Tikoht2016 Ryhmä 11</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="haeAsiakas.php">Lisää asiakas ja työkohde</a></li>
        <li><a href="haeTyokohde.php">Lisää työsuoritus työkohteeseen</a></li>
        <li><a href="etsiTyokohde.php">Lisää tunteja / tarvikkeita työsuoritukseen</a></li>
        <li><a href="tarjous.php">Tulosta tarjous</a></li>
        <li><a href="laskuta.php">Tulosta lasku</a></li>
  </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
    
    
    <!--Lomake
    lähetetään samalle sivulle (vrt lomakkeen kutsuminen) -->
    <form action="tyosuoritusValinta.php" method="post">
    <h2>Tarvikkeiden ja tuntien lisäys työsuoritukseen</h2>
    <h3>Anna joko työkohteen ID tai osoite:</h3>
    <?php if (isset($_SESSION['virhe'])) {
            echo '<p style="color:red">'.$_SESSION['virhe'].'</p>'; 
            unset($_SESSION['virhe']);
        } 
    ?>
    <table border="0" cellspacing="0" cellpadding="5">
        <tr>
            <td>Työkohteen ID</td>
            <td><input type ="text" name = "kohdeid" value ="" /></td>
        </tr>
        <tr>
            <td>Osoite</td>
            <td><input type ="text" name = "osoite" value ="" /></td>
        </tr>
    </table>
    <br />
    <!--hiddenkenttää
    käytetään varotoimena, esim. IE ei välttämättä
    lähetä submittyyppisen
    kentän arvoja jos lomake lähetetään
    enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
    skriptissä päätellä, saavutaanko lomakkeelta. -->
    <input type="hidden" name="haeKohde" value="jep" />
    <input type="submit" value="Etsi työkohde" />
    </form>
    </body>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>