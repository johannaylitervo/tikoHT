
<?php 

session_start(); 

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=jy415643 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");


if (isset($_POST['haeKohde']))
{

    //Suojataan merkkijonot ennen kyselyn suorittamista.
    $kohdeid = intval($_POST['kohdeid']);
    $osoite= pg_escape_string($_POST['osoite']);


    if ($kohdeid == '' AND $osoite == '')
    {
        echo "Hakukenttiin syötettävä jotain!\n";
        exit;
    }

    if(empty($kohdeid)) {
        $kysely = pg_query("SELECT suoritusid, osoite, tyon_tila FROM tyokohde, tyosuoritus WHERE osoite = '$osoite' 
            AND tyokohde.kohdeid = tyosuoritus.kohdeid");

    }
    else {
        $kysely = pg_query("SELECT suoritusid, osoite, tyon_tila FROM tyosuoritus, tyokohde WHERE tyosuoritus.kohdeid ='$kohdeid' AND tyosuoritus.kohdeid = tyokohde.kohdeid");

        if (!$kysely) 
        {
            echo "Virhe kyselyssä. \n";
            exit;
        }
    }
    $rivi = pg_fetch_row($kysely);

    if (empty($rivi))
    {
        $_SESSION['virhe'] = "Työkohteella ei ole työsuorituksia!";
        
        header("Location: etsiTyokohde.php");
        exit; 
    }

    $_SESSION['suoritusid'] = $suoritusid;
    
    
    $tyokohteet = "Työsuorituksen ID: $rivi[0] osoite: $rivi[1] työntila: ";
    switch ($rivi[2]) {
        case 0:
            $tyokohteet .= "tarjous <br/>\n";
            break;
        case 1:
            $tyokohteet .= "kesken <br/>\n";
            break;
        case 2:
            $tyokohteet .= "valmis <br/>\n";
            break;
        default:
            $tyokohteet .= "ei löydy <br/>\n";
            break;
    }
    
    while($rivi = pg_fetch_row($kysely)) {
        $tyokohteet .= "Työsuorituksen ID: $rivi[0] osoite: $rivi[1] työntila: ";
        switch ($rivi[2]) {
        case 0:
            $tyokohteet .= "tarjous <br/>\n";
            break;
        case 1:
            $tyokohteet .= "kesken <br/>\n";
            break;
        case 2:
            $tyokohteet .= "valmis <br/>\n";
            break;
        default:
            $tyokohteet .= "ei löydy <br/>\n";
            break;
        }
    }
    $_SESSION['tyokohteet'] = $tyokohteet;
    
        
    pg_close($yhteys);
        
    
}
?>
<!-- etsitään oikea työkohde id:n tai osoitteen perusteella
- luetaan löydetyn työkohteen suoritusID
- lisätään ”koostuu” -tauluun halutut työtunnit tyypin mukaan ja niiden lukumäärä sekä suoritusID 
ja hinnoista mahdollisesti annetut alennukset
- lisätään ”sisältää” -tauluun suoritusID, käytettyjen tarvikkeiden ID, joka etsitään tarviketaulusta, ja
käytettyjen tarvikkeiden lukumäärä 
lisätään/päivitetään ”sisältää” -tauluun-->


<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>TikoHT2016 Ryhmä 11</title>

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
    <form action="tarvikeLisays.php" method="post">
    <h2>Valitse työsuoritus, johon tiedot lisätään.</h2>
    <?php 
        if (isset($_SESSION['tyokohteet'])) echo '<p style="color:blue">'.$_SESSION['tyokohteet'].'</p>'; 

        if (isset($_SESSION['virhe'])) {
            echo '<p style="color:red">'.$_SESSION['virhe'].'</p>'; 
            unset($_SESSION['virhe']);
        }
    ?>
    <table border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>Työsuoritus ID</td>
            <td><input type ="text" name = "suoritusid" value ="" /></td>
            
        </tr>
        
        </table>
        <br />
        <!--hiddenkenttää
        käytetään varotoimena, esim. IE ei välttämättä
        lähetä submittyyppisen
        kentän arvoja jos lomake lähetetään
        enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
        skriptissä päätellä, saavutaanko lomakkeelta. -->
        <input type="hidden" name="valitseTyokohde" value="jep" />
        <input type="submit" value="Valitse työsuoritus" />
    </form>
    
    </body>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>