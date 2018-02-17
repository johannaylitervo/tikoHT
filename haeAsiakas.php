<?php session_start(); ?>

<!DOCTYPE HTML>
<!-- etsitään oikea työkohde id:n tai osoitteen perusteella
- luetaan löydetyn työkohteen suoritusID
- lisätään ”koostuu” -tauluun halutut työtunnit tyypin mukaan ja niiden lukumäärä sekä suoritusID 
ja hinnoista mahdollisesti annetut alennukset
- lisätään ”sisältää” -tauluun suoritusID, käytettyjen tarvikkeiden ID, joka etsitään tarviketaulusta, ja
käytettyjen tarvikkeiden lukumäärä 
lisätään/päivitetään ”sisältää” -tauluun-->
<?php
    // luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä
    $y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=tk98628
    password=TupuJaJope";
    if (!$yhteys = pg_connect($y_tiedot))
        die("Tietokantayhteyden luominen epäonnistui.");
    // isset funktiolla jäädään odottamaan syötettä.
    // POST on tapa tuoda tietoa lomaketta (tavallaan kutsutaan lomaketta).
    // Argumentti ’tallenna’ saadaan lomakkeen napin nimestä.

    if (isset($_POST['luoAsiakas']))
    {
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

        // suojataan merkkijonot ennen kyselyn suorittamista
        $id = id();
        $etunimi = pg_escape_string($_POST['etunimi']);
        $sukunimi = pg_escape_string($_POST['sukunimi']);
        $osoite = pg_escape_string($_POST['osoite']);
        $puhelin = intval($_POST['puhelin']);

        // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
        $tiedot_ok = $id != 0 && trim($etunimi) != '' && trim($sukunimi) != '' && trim($osoite) != '';
        if ($tiedot_ok)
        {
        $kysely = "INSERT INTO asiakas (asiakasid, etunimi,sukunimi, osoite, puhelinnumero)
        VALUES ($id, '$etunimi', '$sukunimi', '$osoite', $puhelin)";
        $paivitys = pg_query($kysely);
        // asetetaan viestimuuttuja
        // lisätään virheilmoitukseen myös virheen syy (pg_last_error)
        if ($paivitys && (pg_affected_rows($paivitys) > 0))
        $viesti = 'Asiakas lisätty!';
        else
        $viesti = 'Asiakasta ei lisätty: ' . pg_last_error($yhteys);
        }
        else
        $viesti = 'Annetut tiedot puutteelliset tarkista,
        ole hyvä!';
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
    <form action="tyokohdeLisays.php" method="post">
    <h2>Hae lisättävän työkohteen asiakas</h2>
    <?php if (isset($_SESSION['virhe1'])) 
        echo '<p style="color:red">'.$_SESSION['virhe1'].'</p>'; 
        unset($_SESSION['virhe1']);
    ?>
    <table border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>Etunimi</td>
            <td><input type ="text" name = "etunimi" value ="" /></td>
        </tr>
        <tr>
            <td>Sukunimi</td>
            <td><input type ="text" name = "sukunimi" value ="" /></td>
        </tr>
        </table>
        <br />
        <!--hiddenkenttää
        käytetään varotoimena, esim. IE ei välttämättä
        lähetä submittyyppisen
        kentän arvoja jos lomake lähetetään
        enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
        skriptissä päätellä, saavutaanko lomakkeelta. -->
        <input type="hidden" name="haeAsiakas" value="jep" />
        <input type="submit" value="Lisää työkohde asiakkaalle" />
    </form>

    <form action="haeAsiakas.php" method="post">
    <h2>Asiakkaan lisäys</h2>
    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>
    <!--PHPohjelmassa
    viitataan kenttien nimiin (name) -->
    <table border="0" cellspacing="0" cellpadding="3">
    <!--<tr>
    <td>ID</td>
    <td><input type="text" name="id" value="" /></td>
    </tr>-->
    <tr>
    <td>Etunimi</td>
    <td><input type="text" name="etunimi" value="" /></td>
    </tr>
    <tr>
    <td>Sukunimi</td>
    <td><input type="text" name="sukunimi" value="" /></td>
    </tr>
    <tr>
    <td>Osoite</td>
    <td><input type="text" name="osoite" value="" /></td>
    </tr>
    <tr>
    <td>Puhelin</td>
    <td><input type="text" name="puhelin" value="" /></td>
    </tr>
    </table>
    <br />
    <!--hiddenkenttää
    käytetään varotoimena, esim. IE ei välttämättä
    lähetä submittyyppisen
    kentän arvoja jos lomake lähetetään
    enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
    skriptissä päätellä, saavutaanko lomakkeelta. -->
    <input type="hidden" name="luoAsiakas" value="jep" />
    <input type="submit" value="Lisää asiakas" />
    </form>
    </body>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>