<?php
session_start();
// luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=jy415643 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");

// isset funktiolla jäädään odottamaan syötettä.
// POST on tapa tuoda tietoa lomaketta (tavallaan kutsutaan lomaketta).
// Argumentti tallenna saadaan lomakkeen napin nimestä.
if (isset($_POST['haeKohde']))
{

    function id ()
    {
        
        $id = rand();
        $m = pg_query("SELECT suoritusid from tyosuoritus where suoritusid = '$id'");

        $maara = pg_fetch_row($m);
        if ($maara[0] > 0)
            id();
        else
            return $id;
    }

    $id=id();
    
    // suojataan merkkijonot ennen kyselyn suorittamista
    if ($_POST['kohdeid'] != null)
    {
        $muuttuja = intval($_POST['kohdeid']);
        $kysely = pg_query("SELECT kohdeid FROM tyokohde WHERE kohdeid = '$muuttuja'");

        // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
        if (!$kysely) 
        {
            $_SESSION['virhe1'] = "Ei työkohteita hakuehdolla!";
        
            header("Location: haeTyokohde.php");
            exit;
        }
    }
    if ($_POST['osoite'] != '')
    {
        $muuttuja = pg_escape_string($_POST['osoite']);
        $kysely = pg_query("SELECT kohdeid FROM tyokohde WHERE osoite = '$muuttuja'");
        
        if (!$kysely) 
        {
            $_SESSION['virhe1'] = "Työkohteella ei ole työsuorituksia tai hakuosoite on virheellinen!";
        
            header("Location: haeTyokohde.php");
            exit; 
        }
    }
    if ($_POST['osoite'] == '' && $_POST['kohdeid'] == null)
    {
        $_SESSION['virhe1'] = "Virheelliset hakuehdot!";
        
        header("Location: haeTyokohde.php");
        exit;
    }

    $rivi = pg_fetch_row($kysely);
    $sum = pg_query("SELECT COUNT (kohdeid) FROM tyokohde WHERE kohdeid = '$rivi[0]'");
    $maara = pg_fetch_row($sum);
    if (empty($maara)) {
        $_SESSION['virhe1'] = "Ei työkohteita hakuehdoilla!";
        
        header("Location: haeTyokohde.php");
        exit;
    }
    $_SESSION['kohdeid'] = $rivi[0];
    $_SESSION['suoritusid'] = $id;
    pg_close($yhteys);

}

if (isset($_POST['tallenna']))
{
    // suojataan merkkijono ennen kyselyn suorittamista
  

    $suoritusid = $_SESSION['suoritusid'];
    $radio_suoritustyyppi = $_POST['suoritustyyppi'];
    $suoritustyyppi = '';
    $radio_tyon_tila   = $_POST['tyon_tila'];
    $tyon_tila = 0;
    $kohdeid = $_SESSION['kohdeid'];
    

    if($radio_suoritustyyppi == 'tuntityo') {
        $suoritustyyppi = 'tuntityo';
    }
    else if($radio_suoritustyyppi == 'urakka') {
        $suoritustyyppi = 'urakka';
    }
    else {
        $suoritustyyppi = null;
    }

    if($radio_tyon_tila == 'tarjous') {
        $tyon_tila = 0;
    }
    else if($radio_tyon_tila == 'kesken') {
        $tyon_tila = 1;
    }
    else {
        $tyon_tila = -1;
    }
    // jos kenttiin on syötetty jotain, lisätään tiedot kantaan

    $tiedot_ok = $suoritusid != 0 && trim($suoritustyyppi) != null && $tyon_tila >= 0;

    if ($tiedot_ok)
    {
        $kysely = "INSERT INTO tyosuoritus (suoritusid, kohdeid, suoritustyyppi, tyon_tila, tyosumma)
		 VALUES ($suoritusid, $kohdeid, '$suoritustyyppi', $tyon_tila, 0)";
        $paivitys = pg_query($kysely);

        // asetetaan viesti-muuttuja lisäämisen onnistumisen mukaan
	// lisätään virheilmoitukseen myös virheen syy (pg_last_error)

        if ($paivitys && (pg_affected_rows($paivitys) > 0))
            $viesti = 'Työsuoritus lisätty!';
        else
            $viesti = 'Työsuoritusta ei lisätty: ' . pg_last_error($yhteys);
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
    <title>Tikoht2016 Ryhmä 11</title>

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
    <form action="tyosuoritusLisays.php" method="post">

    <h2>Työsuorituksen lisäys</h2>

    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>

	<!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
	<table border="0" cellspacing="0" cellpadding="3">
	    <tr>
    	    <td>TyösuoritusID</td>
    	    <td><input type="text" name="asiakasid" value="<?php echo $_SESSION["suoritusid"];?>"></td>
	    </tr>
	    <tr>
    	    <td>Työsuoritustyyppi</td>
    	    <td>
               <input type="radio" name="suoritustyyppi" value="tuntityo"/> Tuntityö
               <input type="radio" name="suoritustyyppi" value="urakka"/> Urakka <br>
            </td>
	    </tr>
	    <tr>
    	    <td>Työntila</td>
            <td>
               <input type="radio" name="tyon_tila" value="tarjous"/> Tarjous
               <input type="radio" name="tyon_tila" value="kesken"/> Kesken
            </td>
	    </tr>
	</table>

	<br />

	<!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
	 lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
	 enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
	 skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

	<input type="hidden" name="tallenna" value="jep" />
	<input type="submit" value="Lisää työsuoritus" />
	</form>

</body>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>