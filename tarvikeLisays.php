
<?php
session_start();

// luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=jy415643 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");


if(isset($_POST['valitseTyokohde']))
{
    $suoritusid = intval($_POST['suoritusid']);
    $tarkistus = pg_query("SELECT suoritusid FROM tyosuoritus WHERE suoritusid = $suoritusid");
    $rivi = pg_fetch_row($tarkistus);
    if(empty($rivi)) {
        $_SESSION['virhe'] = "Virheellinen suoritus ID!";
        
        header("Location: tyosuoritusValinta.php");
        exit; 
    }

    $_SESSION['suoritusid'] = $suoritusid;

}

if (isset($_POST['haeTarvike']))
{

    // suojataan merkkijonot ennen kyselyn suorittamista
    $tarvike = pg_escape_string($_POST['tarvike']);

    // jos kenttiin on syötetty jotain, lisätään tiedot kantaan
    $tiedot_ok = trim($tarvike) != '';
    
    if ($tiedot_ok)
    {
        //Etsitään tarvikkeita nimellä tai sen osalla.
        $kysely = pg_query("SELECT tarvikeid, nimi, varastotilanne FROM tarvike WHERE nimi LIKE '%$tarvike%'");
        
        if (!$kysely) 
        {
            $viesti1 = "Virhe kyselyssä.\n";
        }


        $rivi = pg_fetch_row($kysely);
        if(empty($rivi)) {
            $viesti1 = "Hakuehdolla ei löytynyt tarvikkeita.";
        }
        else {
            //Tallennetaan kaikki löydetyt tarvikkeet tulostusta varten.
            $tarvikkeet = "Tarvike ID: $rivi[0] || $rivi[1] || varastossa: $rivi[2] <br/>\n";
            while($rivi = pg_fetch_row($kysely)) {
                 $tarvikkeet .= "Tarvike ID: $rivi[0] || $rivi[1] || varastossa: $rivi[2] <br/>\n";
            }
        }


        pg_close($yhteys);
    }
    else
        $viesti1 = "Hakukenttään syötettävä jotain!";
}

if (isset($_POST['tallennaTarvike']))
{
    // suojataan merkkijonot ennen kyselyn suorittamista
    $suoritusid = intval($_SESSION['suoritusid']);
    
    $tarvikeid = intval($_POST['tarvikeid']);
    $ale_prosentti = intval($_POST['ale_prosentti']);
    $tarvike_lkm = intval($_POST['tarvike_lkm']);

    //Tarkistetaan syötettyjen tietojen oikeellisuus.
    $tiedot_ok = !empty(trim($tarvikeid)) && ((trim($ale_prosentti) < 100 && trim($ale_prosentti) >= 1)|| empty(trim($ale_prosentti)))  && trim($tarvike_lkm) > 0 ;
    

    if ($tiedot_ok)
    {
        //Tarkistetaan, että varastosta löytyy tarpeeksi tuotteita lisättäväksi.
        $tarkistus = pg_query("SELECT varastotilanne FROM tarvike WHERE tarvikeid = $tarvikeid");
        $tuotteita = pg_fetch_row($tarkistus);
        
        //Tarkistetaan työsuorituksen tila, jos on tarjous tuotteita ei vähennetä kannasta.
        $rivi = pg_query("SELECT tyon_tila FROM tyosuoritus WHERE suoritusid = $suoritusid");
        $tyon_tila = pg_fetch_result($rivi, 0, 0);

        
        if($tuotteita[0]-$tarvike_lkm > 0) {
         
            //Suoritetaan kysely.
            $ale_prosentti = $ale_prosentti/100;
            $kysely = "INSERT INTO sisaltaa (suoritusid, tarvikeid, ale_prosentti, tarvike_lkm)
             VALUES ($suoritusid, $tarvikeid, '$ale_prosentti', $tarvike_lkm); ";
            
            $hinnanhaku = pg_query("SELECT myyntihinta FROM tarvike WHERE tarvikeid = '$tarvikeid'");
            $hinta = pg_fetch_result($hinnanhaku, 0, 0);
            
            
            if(empty($ale_prosentti) || $ale_prosentti = 0) {
                $hinnanhaku = pg_query("SELECT myyntihinta FROM tarvike WHERE tarvikeid = '$tarvikeid'");
                $hinta = pg_fetch_result($hinnanhaku, 0, 0);
                $kysely .="UPDATE tyosuoritus SET tyosumma = (tyosumma + ($hinta*$tarvike_lkm)) WHERE suoritusid = $suoritusid; ";
            }
            else {
                //Kohdistetaan alennus alv:ttomaan hintaan.
                $hinnanhaku = pg_query("SELECT sisaanostohinta FROM tarvike WHERE tarvikeid = '$tarvikeid'");
                $hinta = pg_fetch_result($hinnanhaku, 0, 0);
                $kysely .="UPDATE tyosuoritus SET tyosumma = (tyosumma + ($hinta*(1-$ale_prosentti)*$tarvike_lkm*1.24)) WHERE suoritusid = $suoritusid; ";
            }

            if($tyon_tila != 0) {
                $kysely .= "UPDATE tarvike SET varastotilanne = (varastotilanne - $tarvike_lkm) WHERE tarvikeid = $tarvikeid";
            }
            $paivitys = pg_query($kysely);

            //Asetetaan viesti-muuttuja lisäämisen onnistumisen mukaan.
            //Lisätään virheilmoitukseen myös virheen syy (pg_last_error).
            if ($paivitys && (pg_affected_rows($paivitys) > 0))
                $viesti = 'Tarvikkeet lisätty!';
            else
                $viesti = 'Tarvikkeita ei lisätty: ' . pg_last_error($yhteys);
        }
        //Annetaan virheilmoitus, jos varastossa ei ole tarpeeksi tuotteita.
        else
            $viesti = 'Varastossa ei tarpeeksi tuotteita!';
    }
    else
        $viesti = 'Annetut tiedot virheelliset - tarkista, ole hyvä!';

}

if (isset($_POST['tallennaTyotunti']))
{
    //Suojataan merkkijonot ennen kyselyn suorittamista.
    $suoritusid = intval($_SESSION['suoritusid']);
    $radio_tyotunti_nimi = $_POST['tyotuntityyppi'];
    $tyotunti_nimi = '';
    $tunti_lkm = intval($_POST['tunti_lkm']);
    $ale_prosentti = floatval($_POST['ale_prosentti']);

    //Tallennetaan radiopainiketta vastaava arvo tyotunti-muuttujaan.
    if($radio_tyotunti_nimi == 'tyo') {
        $tyotunti_nimi = 'tyo';
    }
    else if($radio_tyotunti_nimi == 'suunnittelu') {
        $tyotunti_nimi = 'suunnittelu';
    }
    else if($radio_tyotunti_nimi == 'aputyo') {
        $tyotunti_nimi = 'aputyo';
    }
    else {
        $tyotunti_nimi = '';
    }

    if(empty($ale_prosentti)){
        $ale_prosentti = 0;
    }

    //Tarkistetaan syötettyjen tietojen oikeellisuus.
    $tiedot_ok = !empty(trim($tyotunti_nimi)) && $tunti_lkm > 0 && ((trim($ale_prosentti) < 100 && trim($ale_prosentti) >= 1) || empty(trim($ale_prosentti)));

    if ($tiedot_ok)
    {
        //Suoritetaan lisäys.
        $ale_prosentti = $ale_prosentti/100;

        $kysely = "INSERT INTO koostuu (suoritusid, tyotunti_nimi, tunti_lkm, ale_prosentti) VALUES ($suoritusid, '$tyotunti_nimi', $tunti_lkm, $ale_prosentti); ";
        $hinnanhaku = pg_query("SELECT hinta FROM tyotunti WHERE nimi = '$tyotunti_nimi'");
        $hinta = pg_fetch_result($hinnanhaku, 0, 0);
        
        
        //Jos aleprosentti kenttään ei ole syötetty mitään, laitetaan arvo nollaksi.
        if(empty($ale_prosentti) || $ale_prosentti = 0) {
            $kysely .="UPDATE tyosuoritus SET tyosumma = (tyosumma + $hinta*$tunti_lkm) WHERE suoritusid = $suoritusid";
        }
        else {
            //Kohdistetaan alennus alv:ttomaan hintaan.
            $hinta = $hinta*0.76;
            
            $kysely .="UPDATE tyosuoritus SET tyosumma = (tyosumma + $hinta*(1-$ale_prosentti)*$tunti_lkm*1.24) WHERE suoritusid = $suoritusid; ";
        }
        $paivitys = pg_query($kysely);

        //Asetetaan viesti-muuttuja lisäämisen onnistumisen mukaan.
        //Lisätään virheilmoitukseen myös virheen syy (pg_last_error).

        if ($paivitys && (pg_affected_rows($paivitys) > 0))
            $viesti2 = 'Työtunnit lisätty!';
        else
            $viesti2 = 'Työtunteja ei lisätty: ' . pg_last_error($yhteys);
    }
    else
        $viesti2 = 'Annetut tiedot puutteelliset - tarkista, ole hyvä!';

}

//Suljetaan tietokantayhteys.
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
      <a class="navbar-brand" href="#">Tikoht2016 Ryhmä 11</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li><a href="haeAsiakas.php">Lisää asiakas ja työkohde</a></li>
        <li><a href="haeTyokohde.php">Lisää työsuoritus työkohteeseen</a></li>
        <li><a href="etsiTyokohde.php">Lisää tunteja / tarvikkeita työsuoritukseen</a></li>
        <li><a href="tarjous.php">Tulosta tarjous</a></li>
        <li><a href="laskuta.php">Tulosta lasku</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

    <form action="tarvikeLisays.php" method="post">

    <h3>Hae tarvikkeita</h3>

    <?php if (isset($viesti1)) echo '<p style="color:red">'.$viesti1.'</p>'; ?>

    <!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
    <table border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>Hakusana:</td>
            <td><input type="text" name="tarvike" value="" /></td>
        </tr>
        
    </table>
    <br />

    <!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
     lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
     enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
     skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

    <input type="hidden" name="haeTarvike" value="jep" />
    <input type="submit" value="Etsi tarvike" />
    <br />
    <?php if (isset($tarvikkeet)) 
        echo '<p style="color:blue">'.$tarvikkeet.'</p>'; 
    ?>
    </form>


    <!-- Lomake lähetetään samalle sivulle (vrt lomakkeen kutsuminen) -->
    <form action="tarvikeLisays.php" method="post">

    <h3>Tarvikkeiden lisäys työsuoritukseen</h3>

    
    <?php if (isset($viesti)) echo '<p style="color:red">'.$viesti.'</p>'; ?>
	<!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
    
	<table border="0" cellspacing="0" cellpadding="3">
	    <tr>
            <td>Työsuorituksen ID</td>
             <td><input type="text" name="suoritusid" value="<?php echo $_SESSION["suoritusid"];?>" /></td>
        </tr>
        <tr>
    	    <td>Tarvikkeen ID</td>
    	    <td><input type="text" name="tarvikeid" value="" /></td>
	    </tr>
	    <tr>
    	    <td>Tarvikkeiden määrä</td>
    	    <td><input type="text" name="tarvike_lkm" value="" /></td>
	    </tr>
	    <tr>
    	    <td>Mahdollinen alennusprosentti</td>
    	    <td><input type="text" name="ale_prosentti" value="" /></td>
            <td>%</td>
	    </tr>
	</table>

	<br />

	<!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
	 lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
	 enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
	 skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

	<input type="hidden" name="tallennaTarvike" value="jep" />
	<input type="submit" value="Lisää tarvike/tarvikkeet" />
	</form>

    <!-- Lomake lähetetään samalle sivulle (vrt lomakkeen kutsuminen) -->
    <form action="tarvikeLisays.php" method="post">

    <h3>Tuntitöiden lisäys työsuoritukseen</h3>

    
    <?php if (isset($viesti2)) echo '<p style="color:red">'.$viesti2.'</p>'; ?>

    <!—PHP-ohjelmassa viitataan kenttien nimiin (name) -->
    
    <table border="0" cellspacing="0" cellpadding="3">
        <tr>
            <td>Työsuorituksen ID</td>
            <td><input type="text" name="suoritusid" value="<?php echo $_SESSION["suoritusid"];?>" /></td>
        </tr>
        <tr>
            <td>Työtuntityyppi</td>
            <td>
               <input type="radio" name="tyotuntityyppi" value="tyo"/> Työ
               <input type="radio" name="tyotuntityyppi" value="suunnittelu"/> Suunnittelu
               <input type="radio" name="tyotuntityyppi" value="aputyo"/> Aputyö <br>   
            </td>
        </tr>
        <tr>
            <td>Tuntien lukumäärä</td>
            <td><input type="text" name="tunti_lkm" value="" /></td>
        </tr>
        <tr>
            <td>Mahdollinen alennusprosentti</td>
            <td><input type="text" name="ale_prosentti" value="" /></td>
            <td>%</td>
        </tr>
    </table>

    <br />

    <!-- hidden-kenttää käytetään varotoimena, esim. IE ei välttämättä
     lähetä submit-tyyppisen kentän arvoja jos lomake lähetetään
     enterin painalluksella. Tätä arvoa tarkkailemalla voidaan
     skriptissä helposti päätellä, saavutaanko lomakkeelta. -->

    <input type="hidden" name="tallennaTyotunti" value="jep" />
    <input type="submit" value="Lisää työtunti/-tunnit" />
    </form>

    </body>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>