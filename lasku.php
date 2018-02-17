<?php
session_start();
// luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=jy415643 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");

    // suojataan merkkijonot ennen kyselyn suorittamista
    if ($_POST['kohdeid'] != null)
    {
        $muuttuja = intval($_POST['kohdeid']);
        $kysely = pg_query("SELECT kohdeid FROM tyokohde WHERE kohdeid = '$muuttuja'");

        // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
        if (!$kysely) 
        {
            echo "Virhe kyselyssä.\n";
            exit;
        }
    }
    if ($_POST['osoite'] != '')
    {
        $muuttuja = pg_escape_string($_POST['osoite']);
        $kysely = pg_query("SELECT kohdeid FROM tyokohde WHERE osoite = '$muuttuja'");
        // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
        if (!$kysely) 
        {
            echo "Virhe kyselyssä.\n";
            exit;
        }
    }
    if ($_POST['osoite'] == '' && $_POST['kohdeid'] == null)
    {
        echo "Ei työkohteita halutuilla tiedoilla!\n";
        exit;
    }
    $rivi = pg_fetch_row($kysely);
    if (empty($rivi))
        $viesti = "Ei työkohteita halutuilla tiedoilla!";
    $_SESSION['kohdeid'] = $rivi[0];
    $kysely = pg_query("SELECT * FROM tyokohde,asiakas WHERE tyokohde.kohdeid = '$rivi[0]' AND tyokohde.asiakasid = asiakas.asiakasid");
    if (!$kysely) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }
    $rivi = pg_fetch_row($kysely);
    if (empty($rivi))
        $viesti = "Ei työkohteita halutuilla tiedoilla!";
    $_SESSION['kohdeid'] = $rivi[0];
    $_SESSION['kohdeosoite'] = $rivi[2];
    $_SESSION['asiakasid'] = $rivi[3];
    $_SESSION['etunimi'] = $rivi[4];
    $_SESSION['sukunimi'] = $rivi[5];
    $_SESSION['osoite'] = $rivi[6];
    $_SESSION['puhelinnumero'] = $rivi[7];

    $kohdeid = $_SESSION['kohdeid'];
    // Työn tila 0=tarjous, 1 = kesken, 2 = valmis
    $kysely = pg_query("SELECT * FROM tyosuoritus WHERE kohdeid = '$kohdeid' AND tyon_tila = 2");
    $rivi = pg_fetch_row($kysely);
    if (empty($rivi))
        $viesti = "Ei työsuorituksia halutuilla tiedoilla!";
    else
    {
        $tulos = "Tyosuoritus ID: $rivi[0] , suoritustyyppi : $rivi[2] , tyon tila $rivi[3] , summa $rivi[4] <br/>\n";
        while ($rivi = pg_fetch_row($kysely))
        {
            $tulos .= "Tyosuoritus ID: $rivi[0] , suoritustyyppi : $rivi[2] , tyon tila $rivi[3] , summa $rivi[4] <br/>\n";
        }
    }

// suljetaan tietokantayhteys

pg_close($yhteys);

?>

<!--isset funktiolla jäädään odottamaan syötettä.
// POST on tapa tuoda tietoa lomaketta (tavallaan kutsutaan lomaketta).
// Argumentti tallenna saadaan lomakkeen napin nimestä. -->

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
    
<!--
    <!-- Lomake lähetetään samalle sivulle (vrt lomakkeen kutsuminen) 
    <form action="tyosuoritusLisays.php" method="post">-->

    <h2>Valmiiden työsuoritusten haku</h2>

    <?php 
    if (isset($viesti)) 
        echo '<p style="color:red">'.$viesti.'</p>';  
    ?>
  <!--PHPohjelmassa
    viitataan kenttien nimiin (name) -->
    <form action ="naytaLasku.php" method ="post">
        <table border="0" cellspacing="0" cellpadding="3">
        <tr>
        <td>Kohde ID</td>
        <td><input type="text" name="kohdeid" value="<?php echo $_SESSION["kohdeid"];?>" /></td>
        </tr>
        <tr>
        <td>Kohteen osoite</td>
        <td><input type="text" name="kohdeosoite" value="<?php echo $_SESSION["kohdeosoite"];?>" /></td>
        </tr>
        <tr>
        <td>Asiakas ID</td>
        <td><input type="text" name="asiakasid" value="<?php echo $_SESSION["asiakasid"];?>" /></td>
        </tr>
        <tr>
        <td>Etunimi</td>
        <td><input type="text" name="etunimi" value="<?php echo $_SESSION["etunimi"];?>" /></td>
        </tr>
        <tr>
        <td>Sukunimi</td>
        <td><input type="text" name="sukunimi" value="<?php echo $_SESSION["sukunimi"];?>" /></td>
        </tr>
        <tr>
        <td>Osoite</td>
        <td><input type="text" name="osoite" value="<?php echo $_SESSION["osoite"];?>" /></td>
        </tr>
        <tr>
        <td>Puhelinnumero</td>
        <td><input type="text" name="puhelinnumero" value="<?php echo $_SESSION["puhelinnumero"];?>" /></td>
        </table>
        <br />

	<!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
	 lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
	 enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
	 skriptissä helposti päätellä, saavutaanko lomakkeelta. -->


    <?php
        if (isset($tulos))
            echo '<p style="color:blue">'.$tulos.'</p>';
    ?>
    <table>
    <tr>
        <td>Anna työsuoritus ID</td>
        <td><input type="text" name="tyosuoritusid" value="" /></td>
    </tr></table>

    <input type="hidden" name="laskuta" value="jep" />
    <input type="submit" value="Näytä lasku" />
    </form>
</body>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>