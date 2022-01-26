<?php

function checksex($genre) {
  if ( isset($_GET['sexe']) && $_GET['sexe'] == $genre && empty($_GET['chercher']))
  return "checked";
}

function sanitize_query() {
  if(isset($_GET['tri'] ) ) {
    $query = $_SERVER['QUERY_STRING'];
    $query = strstr($query,"&tri",true);
    return $query;
  }
}

function ordonnerPerformance($tri,$item,$query) {
  if(isset($tri)) {
    if($tri == $item)
    $ordering =  '<span class="ordering ordering-active">↑↓</span>';
    else $ordering =  '<span class="ordering"><a href="?'.$query.'&tri='.$item.'">↑↓</a></span>';
    return $ordering;
}
}


    $liste_nages = "";

    $nageurs = [];

    $req_nage = "SELECT CONCAT(TRIM(perf_distance),' ',perf_style) AS epreuve FROM nageur_performance GROUP BY perf_style, perf_distance";

    require_once('connect.php');

    foreach ($conn->query($req_nage) as $epreuve) {
      if ( empty($_GET['chercher']) && isset($_GET['nagechoisie']) && $_GET['nagechoisie'] == $epreuve[0]) $selectionne = "selected";
      else $selectionne = "";
      $liste_nages .= "<option $selectionne value=\"{$epreuve[0]}\">{$epreuve[0]}</option>";
    }

    if( isset($_GET['nagechoisie']) && $_GET['nagechoisie'] && isset($_GET['sexe']) && empty($_GET['chercher']) )

    {

      $query = sanitize_query();

      switch ($_GET['tri']) {
        case 'a':
          $tri = "profile_nom";
          break;
        case 'd':
          $tri = "perf_date DESC";
          break;
        case 't':
          $tri = "perf_temps ASC";
          break;
        default:
          $tri = "perf_temps ASC";
          break;
      }


      $req = "SELECT id_perf, CONCAT(profile_prenom,' ', profile_nom) AS lenageur, RIGHT(perf_temps,8) AS perf_temps, perf_bassin, CONCAT(perf_distance, ' ' ,perf_style) AS lanage, DATE_FORMAT(perf_date, '%d/%m/%Y') AS ladate, perf_lieu, club_nom FROM nageur_performance
      INNER JOIN nageur_profile ON nageur_performance.FK_id_profile = nageur_profile.id_profile
      INNER JOIN nageur_club ON nageur_performance.FK_id_club = nageur_club.id_club
      WHERE CONCAT(perf_distance, ' ' ,perf_style) = :nage  and profile_genre = :sexe
      ORDER BY {$tri} ";

      $sth = $conn->prepare($req);
      $sth->execute(array(":nage"=> $_GET['nagechoisie'], ":sexe"=> $_GET['sexe']));

      $jeu = $sth->fetchAll();

      $champrecherche = false;

    } // fin if

    else if( (isset($_GET['nagechoisie']) || isset($_GET['sexe'])) && empty($_GET['chercher']) ) {
      $jeu = "";
      $champrecherche = false;
      $error_message =  "Choisir une nage et un genre";
    }


    else if (isset($_GET['chercher']) && !empty(trim($_GET['chercher']))) {

    $query = sanitize_query();

    switch ($_GET['tri']) {
      case 'a':
        $tri = "profile_nom";
        break;
      case 'd':
        $tri = "perf_date DESC";
        break;
      case 't':
        $tri = "perf_temps ASC";
        break;
      default:
        $tri = "perf_temps ASC";
        break;
    }

    $nageur_str = trim($_GET['chercher'])."%";

    $champrecherche = true;

    $req = "SELECT id_perf, CONCAT (profile_prenom,' ', profile_nom) AS lenageur, profile_nom, RIGHT(perf_temps,8) AS perf_temps, perf_bassin, CONCAT(perf_distance, ' ' ,perf_style) AS lanage, DATE_FORMAT(perf_date, '%d/%m/%Y') AS ladate, perf_lieu, club_nom FROM nageur_performance
    INNER JOIN nageur_profile ON nageur_performance.FK_id_profile = nageur_profile.id_profile
    INNER JOIN nageur_club ON nageur_performance.FK_id_club = nageur_club.id_club
    WHERE profile_nom LIKE ?
    ORDER BY {$tri} ";

    $sth = $conn->prepare($req);

    $sth->execute(array($nageur_str));

    $jeu = $sth->fetchAll();

    if($jeu) {
      foreach ($jeu as $nageur) {
        $nageurs[] = $nageur['profile_nom'];
      }

    }

    }

    else {

    $req = "SELECT id_perf, CONCAT (profile_prenom,' ',profile_nom) AS lenageur, perf_bassin, RIGHT(perf_temps,8) AS perf_temps, CONCAT(perf_distance, ' ' ,perf_style) AS lanage, DATE_FORMAT(perf_date, '%d/%m/%Y') AS ladate, perf_lieu, club_nom FROM nageur_performance
    INNER JOIN nageur_profile ON nageur_performance.FK_id_profile = nageur_profile.id_profile
    INNER JOIN nageur_club ON nageur_performance.FK_id_club = nageur_club.id_club
    ORDER BY perf_date DESC, perf_temps LIMIT 10";

    $sth = $conn->prepare($req);

    $sth->execute();

    $jeu = $sth->fetchAll();

    $champrecherche = false;

  }


?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Performances natation française</title>
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <style media="screen">
      body {
        background-color: #fafafa;
        padding: 0;
        margin: 0;
        font-family: 'Lato', sans-serif;
      }
      header {
        background: linear-gradient(to top left,#fafafa, #fff);
        color: white;
        padding: 3vh 2%;
        border-bottom:  3px #003da0 solid;

      }

      header .inner {
        max-width: 1200px;
        margin: 0 auto;
      }

      header a {
      background: -webkit-linear-gradient(bottom left, #003da0, #c5ceff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-transform: uppercase;
      font-weight: 700;
      font-size: 4em;
      }


      main {
        max-width: 1200px;
        margin: 0 auto;
      }

      .display-perf {
        background-color: white;
        padding: 2%;
      }

      .performance, .heading-perf {
        display: flex;
        padding: 10px;
        align-items: center;
      }

      .performance p, .performance, .heading-perf p {
        font-size: 14px;
        flex: 1 1;
        padding: 5px;
      }

      .performance p:nth-of-type(4), .heading-perf p:nth-of-type(4){
        flex: 0 0 80px;
      }

      .performance p:nth-of-type(6) {
        font-size: 11px;
      }


      .performance p:last-of-type {
        font-size: 12px;
        color: grey;
      }

      .performance p:nth-of-type(3){
        color: grey;
        font-family: 'Menlo', serif;
      }

      .performance:nth-of-type(odd) {
        background-color: #d8e7ff;
        border-bottom: 1px dotted #003da0;
      }

      div.heading-perf {
        background-color: #f5f5f5;
        border-color: grey;
        font-size: 80%;
        text-transform: uppercase;
      }

      .genre {
        display: none;
      }


      .genre + label {
        display: inline-block;
        padding: 10px;
        color: #003da0;
        border: 2px solid #003da0;
      }

      .genre:checked + label{
        color: white;
        background-color: #003da0;
      }

      .form-search {
        padding: 0;
        margin: 2% 0;
        display: flex;
        justify-content: space-between;
        background-color: #fafafa;
      }

      form [type="submit"] {
        background-color: #003da0;
        padding: 10px 15px;
        color: white;
        text-transform: uppercase;
        cursor: pointer;
        border: 0;
      }

      header a {
        color: white;
      }

      h3 span {
        color: #003da0;
        font-size: 150%;
      }

      p.fail {
        padding: 7px;
        color: orange;
        border: #f90 1px solid;
        background-color: #fffcf1;
      }

      .ordering {
        font-weight: 600;
        color: #666;
        padding: 3px;
        border: 1px solid #333;
        border-radius: 5px;
        font-style: 6px;
        display:inline-block;
        margin: 0 5px;
        background: white;
      }

      .ordering a {
        text-decoration: none;
        color: inherit;
      }

      .ordering-active {
        background-color: #003da0;
        color: white;
      }

      select {
        padding: 10px 5px;
        background: #fefefe ;
        border: 0;
        font-size: 16px;
      }

      .display-perf .item {
        padding: 15px 10px;
        flex: 1 1;
        display: flex;
        justify-content: space-around;
        align-items: center;
        border-top: 1px solid #9a9a9a;
        border-bottom: 1px solid #9a9a9a;
      }

      .display-perf .item:first-child {
        border-right: 1px dashed #003da0cc;
        border-left: 1px solid #9a9a9a;
        position: relative;
      }
      .display-perf .item:first-child::after {
        content: "OU";
        position: absolute;
        right: -18px;
        background-color: #003da0cc;
        color: white;
        padding: 3px;
        border-radius: 5px;
      }

      .search-swimmer {
        padding: 10px;
      }


    </style>
  </head>
  <body>
    <header>
      <div class="inner">
        <a href="./">Performance sportive</a>
      </div>
    </header>
    <main>
      <section class="display-perf">
        <form action="" method="get" class="form-search">
        <div class="item">
        <select name="nagechoisie">
          <option value="0">Choisir une nage</option>
          <?=$liste_nages?>
        </select>

        <input type="radio" name="sexe" value="F" id="femme" class="genre" <?=checksex("F")?>><label for="femme">Femme</label>
        <input type="radio" name="sexe" value="H" id="homme" class="genre" <?=checksex("H")?>><label for="homme">Homme</label>

        </div>
        <div class="item">
        <label>Nom de l'athlète : </label>
        <input class="search-swimmer" type="search" name="chercher" placeholder="Premières lettres ...">
        <input type="hidden" name="tri" value="t">
        </div>
        <input type="submit" name="valider" value="Afficher les performances">
        </form>
        <?php
        if ($champrecherche)
        {
          if($nageurs)
          {   $nageur = array_unique($nageurs,SORT_STRING);
              sort($nageur);
              echo "<h3>Nageur(s) trouvés : <span>";
              echo implode(", ",$nageur);
          echo "</span></h3><hr>";
          }
        else echo "<p class=\"fail\">Aucun nageur trouvé : <i> " . $_GET['chercher'] . "</i></p>";
        }
        else if (isset($error_message)) {
          echo '<p class="fail">' . $error_message . '</p>';
        }
        ?>
        <div class="heading-perf">
        <p>Nageur
          <?= ordonnerPerformance($_GET['tri'],"a",$query);?>
        </p>
        <p>Epreuve </p>
        <p>Temps <?= ordonnerPerformance($_GET['tri'],"t",$query); ?></p>
        <p>Bassin</p>
        <p>Date  <?= ordonnerPerformance($_GET['tri'],"d",$query); ?></p>
        <p>Lieu</p>
        <p>Club </p>
        </div>
        <?php


      if($jeu)
      {
        foreach ($jeu as $perf) {
          echo "<div class=\"performance\" id=\"perf_{$perf['id_perf']}\">
          <p>{$perf['lenageur']} </p>
          <p> {$perf['lanage']}</p>
          <p> {$perf['perf_temps']}</p>
          <p> {$perf['perf_bassin']}</p>
          <p>{$perf['ladate']}</p>
          <p>{$perf['perf_lieu']}</p>
          <p>{$perf['club_nom']}</p>
          </div>";
        }
      }

          $conn = null;
    ?>

      </section>
    </main>
  </body>
</html>
