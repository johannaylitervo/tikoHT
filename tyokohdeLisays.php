<?php
session_start();

// luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=tk98628 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");

// isset funktiolla jäädään odottamaan syötettä.
// POST on tapa tuoda tietoa lomaketta (tavallaan kutsutaan lomaketta).
// Argumentti tallenna saadaan lomakkeen napin nimestä.
if (isset($_POST['haeAsiakas']))
{

    // suojataan merkkijonot ennen kyselyn suorittamista
    $etunimi = pg_escape_string($_POST['etunimi']);
    $sukunimi= pg_escape_string($_POST['sukunimi']);
    // suojataan merkkijonot ennen kyselyn suorittamista
    if ($etunimi == '' OR $sukunimi == '')
    {
        
        $_SESSION['virhe1'] = "Virheelliset hakuehdot!";
      
        header("Location: haeAsiakas.php");
        exit;

    }

    function id ()
    {
        
        $id = rand();
        $m = pg_query("SELECT asiakasid from asiakas where asiakasid = '$id'");

        $maara = pg_fetch_row($m);
        if ($maara[0] > 0)
            id();
        else
            return $id;
    }

    $id=id();
    $_SESSION['id'] = $id;

    // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
    $tiedot_ok = trim($etunimi) != '' && trim($sukunimi) != '';
    // $maara = pg_query("SELECT COUNT (asiakasid) FROM asiakas WHERE etunimi = '$etunimi' AND sukunimi = '$sukunimi'"); 

    if ($tiedot_ok)
    {
        $kysely = pg_query("SELECT asiakasid, etunimi, sukunimi FROM  asiakas WHERE etunimi = '$etunimi' AND sukunimi = '$sukunimi'");

        if (!$kysely) 
        {
            echo "Virhe kyselyssä.\n";
            exit;
        }
        $rivi = pg_fetch_row($kysely);
        if (empty($rivi))
        {
            $_SESSION['virhe1'] = "Ei asiakkaita hakuehdoilla!";
            
            header("Location: haeAsiakas.php");
            exit;
        }

        $_SESSION["asiakasid"] = $rivi[0];
        pg_close($yhteys);
    }
    else {
        
        $_SESSION['virhe1'] = "Virheelliset hakuehdot!";
        header("Location: haeAsiakas.php");
        exit;
    }
}

if (isset($_POST['tallenna']))
{
    // suojataan merkkijonot ennen kyselyn suorittamista

    $kohdeid = intval($_POST['kohdeid']);
    $asiakasid = intval($_POST['asiakasid']);
    $osoite = pg_escape_string($_POST['osoite']);
    // jos kenttiin on syötetty jotain, lisätään tiedot kantaan

    $tiedot_ok = $asiakasid != 0 && $kohdeid != 0 && trim($osoite) != '' ;

    if ($tiedot_ok)
    {
        $kysely = "INSERT INTO tyokohde (kohdeid, asiakasid, osoite) VALUES 
        ('$kohdeid','$asiakasid','$osoite')";
        $paivitys = pg_query($kysely);

    // asetetaan viesti-muuttuja lisäämisen onnistumisen mukaan
	// lisätään virheilmoitukseen myös virheen syy (pg_last_error)

        if ($paivitys && (pg_affected_rows($paivitys) > 0))
            $viesti = 'Työkohde lisätty!';
        else
            $viesti = 'Työkohdetta ei lisätty: ' . pg_last_error($yhteys);
    }
    else
        $viesti = 'Annetut tiedot puutteelliset - tarkista, ole hyvä!';

}
// suljetaan tietokantayhteys

pg_close($yhteys);

?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Bootstrap 101 Template</title>

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
    
    <!-- Lomake lähetetään samalle sivulle (vrt lomakkeen kutsuminen) -->
    <form action="tyokohdeLisays.php" method="post">

    <h2>Työkohteen lisäys</h2>

    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

	<!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
    <p>Lisätään uusi työkohde asiakkaalle</p>
	<table border="0" cellspacing="0" cellpadding="3">
	    <tr>
    	    <td>Asiakkaan ID</td>
    	    <td><input type="text" name="asiakasid" value="<?php echo $_SESSION["asiakasid"];?>"></td>
	    </tr>
	    <tr>
    	    <td>Työkohteen osoite</td>
    	    <td><input type="text" name="osoite" value="" /></td>
	    </tr>
        <tr>
            <td>Kohteen id</td>
            <td><input type="text" name="kohdeid" value="<?php echo $_SESSION["id"];?>" /></td>
        </tr>
	</table>

	<br />

	<!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
	 lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
	 enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
	 skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

	<input type="hidden" name="tallenna" value="jep" />
	<input type="submit" value="Lisää työkohde" />
	</form>

</body>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>