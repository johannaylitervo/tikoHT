<?php
session_start();
// luodaan tietokantayhteys ja ilmoitetaan mahdollisesta virheestä

$y_tiedot = "host=dbstud.sis.uta.fi port=5432 dbname=tiko2016db11 user=jy415643 password='TupuJaJope'";

if (!$yhteys = pg_connect($y_tiedot))
   die("Tietokantayhteyden luominen epäonnistui.");

if (isset($_POST['laskuta']))
{
		$tyosuoritusid = $_POST['tyosuoritusid'];
    $kysely = pg_query("SELECT tyosumma FROM tyosuoritus WHERE $tyosuoritusid = suoritusid");

    if (!$kysely) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }

    $rivi = pg_fetch_row($kysely);
    if (!empty($rivi))
    {
      $summa = $rivi[0];
      $_SESSION['summa'] = $summa;
    }

    $tarvikkeet = pg_query ("SELECT sisaltaa.tarvikeid,sisaltaa.ale_prosentti,sisaltaa.tarvike_lkm,tarvike.nimi,tarvike.myyntihinta,tarvike.alv,tarvike.veroton_hinta,tarvike.yksikko FROM sisaltaa,tarvike WHERE sisaltaa.suoritusid = $tyosuoritusid AND sisaltaa.tarvikeid = tarvike.tarvikeid");
    if (!$tarvikkeet) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }
    $rivi = pg_fetch_row($tarvikkeet);
    $first_row = true;
  	if (empty($rivi))
      	$viesti1 = "Ei tarvikkeita halutuilla tiedoilla!";
  	else
  	{
        $tarvike .= "<table border = 3em solid black>";
        $tarvike .= "<tr>";
        foreach ($rivi as $key => $field)
        {
          $vastaus = pg_field_name($tarvikkeet,$key);
          if($key==0)
            $tarvike .= "<td>TarvikeID</td>";
          if ($key==1)
            $tarvike .= "<td>Alennusprosentti</td>";
          if ($key == 2)
            $tarvike .= "<td>Tarvikkeet / kpl</td>";
          if($key == 3)
            $tarvike .= "<td>Nimi</td>";
          if($key == 4)
            $tarvike .= "<td>Myyntihinta (€ / kpl)</td>";
          if($key == 5)
            $tarvike .= "<td>ALV</td>";
          if($key == 6)
            $tarvike .= "<td>Veroton hinta / kpl</td>";
          if($key == 7)
          $tarvike .= "<td>Yksikkö</td>";
        }
        $tarvike .= "</tr>";
        $tarvike .="<tr>";
        foreach ($rivi as $key => $field)
        {
          $tarvike .= "<td> $field </td>";
        }
        $tarvike .= "</tr>";
      	while ($rivi = pg_fetch_row($tarvikkeet))
      	{
          $tarvike .="<tr>";
          foreach ($rivi as $key => $field)
          {
            $tarvike .= "<td> $field </td>";
          }
          $tarvike .= "</tr>";
        }
        $tarvike .= "</table>";
    }
    $kysely = pg_query("SELECT tyotunti_nimi,tunti_lkm,ale_prosentti,hinta,alv,veroton_hinta FROM koostuu,tyotunti WHERE suoritusid = '$tyosuoritusid' AND koostuu.tyotunti_nimi = tyotunti.nimi");
    $rivi = pg_fetch_row($kysely);
    $first_row = true;
    if (empty($rivi))
        $viesti .= "Ei työsuorituksia halutuilla tiedoilla!";
    else
    {
        $tulos .= "<table border = 3em solid black>";
        $tulos .= "<tr>";
        foreach ($rivi as $key => $field)
        {
          $vastaus = pg_field_name($kysely,$key);
          if($key==0)
            $tulos .= "<td>Työn nimi</td>";
          if ($key==1)
            $tulos .= " <td>Työtunnit lkm </td>";
          if ($key == 2)
            $tulos .= "<td>Alennusprosentti </td>";
          if($key == 3)
            $tulos .= "<td>Hinta /kpl </td>";
           if($key == 4)
            $tulos .= "<td>ALV</td>";
           if($key == 5)
            $tulos .= "<td>Veroton hinta / kpl</td>";
        }
        $tulos .= "</tr>";
        $tulos .="<tr>";
        foreach ($rivi as $key => $field)
        {
          $tulos .= "<td> $field </td>";
        }
        $tulos .= "</tr>";
        while ($rivi = pg_fetch_row($kysely))
        {
          $tulos .="<tr>";
          foreach ($rivi as $key => $field)
          {
            $tulos .= "<td> $field </td>";
          }
          $tulos .= "</tr>";
        }
        $tulos .= "</table>";

    }
    $kysely = pg_query("SELECT hinta, tyotunti_nimi,tunti_lkm,ale_prosentti FROM koostuu,tyotunti WHERE $tyosuoritusid = suoritusid AND tyotunti_nimi = nimi");
    if (!$kysely) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }

    $rivi = pg_fetch_row($kysely);
    if (!empty($rivi))
    {
      $hinta = $rivi[0];
      $tyotunti_nimi = $rivi[1];
      $tunti_lkm = $rivi[2];
      $ale_prosentti = $rivi[3];
      $tyosumma = $tyosumma + $hinta*$tunti_lkm;
      while ($rivi = pg_fetch_row($kysely))
      {
        $hinta = $rivi[0];
        $tyotunti_nimi = $rivi[1];
        $tunti_lkm = $rivi[2];
        $ale_prosentti = $rivi[3];
        $tyosumma = $tyosumma + $hinta*$tunti_lkm;
      }

    }

    $ktvahennys = $tyosumma * 0.45 - 100;
    if ($ktvahennys > 2400)
      $ktvahennys = 2400;
    if ($ktvahennys < 100)
      $ktvahennys = 0;

    // Haetaan työn veroton hinta
    $kysely = pg_query("SELECT veroton_hinta, tyotunti_nimi,tunti_lkm,ale_prosentti FROM koostuu,tyotunti WHERE $tyosuoritusid = suoritusid AND tyotunti_nimi = nimi");
    if (!$kysely) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }

    $rivi = pg_fetch_row($kysely);
    if (!empty($rivi))
    {
      $hinta = $rivi[0];
      $tyotunti_nimi = $rivi[1];
      $tunti_lkm = $rivi[2];
      $ale_prosentti = $rivi[3];
      $verotontyosumma = $verotontyosumma + ($hinta*(1-$ale_prosentti))*$tunti_lkm;
      while ($rivi = pg_fetch_row($kysely))
      {
        $hinta = $rivi[0];
        $tyotunti_nimi = $rivi[1];
        $tunti_lkm = $rivi[2];
        $ale_prosentti = $rivi[3];
        $verotontyosumma = $verotontyosumma + ($hinta*(1-$ale_prosentti))*$tunti_lkm;
      }
    }

    $kysely = pg_query("SELECT veroton_hinta,ale_prosentti,tarvike_lkm FROM sisaltaa,tarvike WHERE sisaltaa.suoritusid = $tyosuoritusid AND sisaltaa.tarvikeid = tarvike.tarvikeid");
    if (!$kysely) 
    {
        echo "Virhe kyselyssä.\n";
        exit;
    }

    $rivi = pg_fetch_row($kysely);
    if (!empty($rivi))
    {
      $hinta = $rivi[0];
      $ale = $rivi[1];
      $tarvike_lkm = $rivi[2];
      $verotontarvikesumma = $verotontarvikesumma + ($hinta*(1-$ale))*$tarvike_lkm;
      while ($rivi = pg_fetch_row($kysely))
      {
        $hinta = $rivi[0];
        $ale = $rivi[1];
        $tarvike_lkm = $rivi[2];
        $verotontarvikesumma = $verotontarvikesumma + ($hinta*(1-$ale))*$tarvike_lkm;
      }
    }


    $_SESSION['verotonsumma'] = $verotontyosumma + $verotontarvikesumma;
    $_SESSION['ktvahennys'] = $ktvahennys;
    $_SESSION['tyosumma'] = $tyosumma;
    $_SESSION['suoritusid'] = $tyosuoritusid; 
    $_SESSION['kohdeosoite']= $_POST['kohdeosoite'];
    $_SESSION['asiakasid'] = $_POST['asiakasid'];
    $_SESSION['etunimi']= $_POST['etunimi'];
    $_SESSION['sukunimi'] = $_POST['sukunimi'];
    $_SESSION['osoite']= $_POST['osoite'];
    $_SESSION['puhelinnumero'] = $_POST['puhelinnumero'];
    $_SESSION['summa'] = $summa;
}

if (isset($_POST['luoLasku']))
{
  function id ()
    {
        
        $id = rand();
        $m = pg_query("SELECT laskuid from lasku where laskuid = '$id'");

        $maara = pg_fetch_row($m);
        if ($maara[0] > 0)
            id();
        else
            return $id;
    }
    // Laskuid
    $id=id();

    // Kohdeid
    $kohdeid = $_SESSION['kohdeid'];

    //Päivämäärä
    $pvm = strtotime("+3 Weeks");
    $erapvm = date("Y-m-d",$pvm);
    $tamapvm = date("Y-m-d");
    $vkorko = 16;
    $laskutuslisä = 5;
    $versionumero = 1;
    $summa = $_SESSION['summa'];

  $kysely = "INSERT INTO lasku (laskuid,kohdeid,erapvm,pvm,viivastyskorko,laskutuslisa,versionumero,summa) VALUES ($id,$kohdeid,'$erapvm','$tamapvm',$vkorko,$laskutuslisä,$versionumero,$summa)";
  $paivitys = pg_query($kysely);

    // asetetaan viesti-muuttuja lisäämisen onnistumisen mukaan
  // lisätään virheilmoitukseen myös virheen syy (pg_last_error)

  if ($paivitys && (pg_affected_rows($paivitys) > 0))
      $viesti = 'Lasku luotu!';
  else
      $viesti = 'Laskua ei luotu: ' . pg_last_error($yhteys);
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
    <link rel="stylesheet" type="text/css" href="style.css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
  <body>
  <div class ="container">
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

    <?php 
    if (isset($viesti)) 
      echo '<p style="color:red">'.$viesti.'</p>';
    if (isset($viesti1)) 
      echo '<p style="color:red">'.$viesti1.'</p>'; 

    ?>
    <div id ="summa" class ="jumbotron">
        <h2>Veroton summa </h2>
        <h4><?php echo $_SESSION["verotonsumma"];?>€</h2>
        <h2>Verollinen summa</h2>
        <h4><?php echo $_SESSION["summa"];?>€</h2>
        <h2>Työn osuus</h2>
        <h4><?php echo $_SESSION["tyosumma"];?>€</h2>
        <h2>Kotitalousvähennys</h2>
        <h4><?php echo $_SESSION["ktvahennys"];?>€</h4>
    </div>
    <div>
      <h2>Laskun erittely</h2>
      <h4>Asiakastiedot</h4>
    <!--<form action ="naytaLasku.php" method ="post" label ="Asiakastiedot">-->
      <table border= "1em solid black">
      <tr>
      <td>Kohde ID</td>
      <td><?php echo $_SESSION["kohdeid"];?></td>
      </tr>
      <tr>
      <td>Kohteen osoite</td>
      <td><?php echo $_SESSION["kohdeosoite"];?></td>
      </tr>
      <tr>
      <td>Asiakas ID</td>
      <td><?php echo $_SESSION["asiakasid"];?></td>
      </tr>
      <tr>
      <td>Etunimi</td>
      <td><?php echo $_SESSION["etunimi"];?></td>
      </tr>
      <tr>
      <td>Sukunimi</td>
      <td><?php echo $_SESSION["sukunimi"];?></td>
      </tr>
      <tr>
      <td>Osoite</td>
      <td><?php echo $_SESSION["osoite"];?></td>
      </tr>
      <tr>
      <td>Puhelinnumero</td>
      <td><?php echo $_SESSION["puhelinnumero"];?></td>
      </tr>
      <tr>
      <td>TyösuoritusID</td>
      <td><?php echo $_SESSION["suoritusid"];?></td>
      </tr>
      </table>

      <!--</form>-->
      <h2>Työsuoritukset</h2>
      <?php if (isset($tulos)) echo "$tulos";?>
      <h2>Tarvikkeet</h2>
      <?php if (isset($tarvike)) echo "$tarvike";?>
</div>
<div class = "col-md-6">
<form action ="naytaLasku.php" method ="post">
    <input type="hidden" name="luoLasku" value="jep" />
    <input type="submit" value="Laskuta" style="font-size:2em" />
</form>
<div>
    </div>
  
    </body>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
</html>