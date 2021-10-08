<?php
session_start();
include("PHPFunc/utilitaire.php");
$bd = connectToDB();

if (isset($_GET["id"]) && isset($_GET["page"])) {
  $idFilm = $_GET["id"];
  $page = $_GET["page"];
} else {
  header("Location: home.php");
}

$limite = 25;

$reqListeSujets = $bd->prepare("SELECT id FROM sujet WHERE idFilm = ?");
$reqListeSujets->execute(array($idFilm));
$total = 0;
while ($rListe = $reqListeSujets->fetch()) {
  $total += 1;
  $liste[] = $rListe["id"];
}

$nSujets = $total;
$nPages = ceil($nSujets/$limite);

if ($page > $nPages) {
  $page = 1;
}


// Créer nouveau sujet
if (isset($_POST["send"])) {
  if (isConnected()) {
    $title = $_POST["title"];
    $mess = $_POST["message"];
    $tailleT = strlen(trim($title));
    $tailleM = strlen(trim($mess));
    if ($tailleT != 0) {
      if ($tailleT < 50) {
        if ($tailleM != 0) {
          $reqSujPos = $bd->prepare("UPDATE sujet SET pos = pos + 1");
          $reqSujPos->execute();

          $reqSujNew = $bd->prepare("INSERT INTO sujet (idFilm, idAuteur, titre) VALUES (?, ?, ?)");
          $reqSujNew->execute(array($idFilm, $_SESSION["id"], $title));

          $reqID = $bd->prepare("SELECT MAX(id) FROM sujet");
          $reqID->execute();
          $lastID = $reqID->fetch();

          $reqSujMess = $bd->prepare("INSERT INTO message (idSujet, idAuteur, message, datePubli) VALUES (?, ?, ?, NOW())");
          $reqSujMess->execute(array($lastID[0], $_SESSION["id"], $mess));


          if (!$reqSujPos || !$reqSujNew || !$reqID || !$reqSujMess) {
            $erreurSuj = "Il y a eu un problème lors de la mise en ligne de votre sujet.";
          }
        } else {
          $erreurSuj = "Veuillez écrire un message";
        }
      } else {
        $erreurSuj = "Titre trop long";
      }
    } else {
      $erreurSuj = "Veuillez écrire un titre.";
    }
  } else {
    $erreurSuj = "Vous n'êtes pas connecté.";
  }
}

$reqFilm = $bd->prepare("SELECT titre FROM film WHERE id = ?");
$reqFilm->execute(array($idFilm));
$titreFilm = $reqFilm->fetch();






$reqSujets = $bd->prepare("SELECT * FROM sujet WHERE pos > ?  AND idFilm = ? ORDER BY pos ASC LIMIT $limite");
$reqSujets->execute(array(($limite*$page)-$limite, $idFilm));

?>


<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="CSS/stylesujets.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Forum <?php echo $titreFilm["titre"]; ?> - CritiFilms</title>
<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>

</head>

<body>



     <nav class="navbar">
    <div class="brand-title"><a href="home.php"><img src="siteimg/cf2.png" class="logoimg" alt="logo"></a></div>       
	<form class="search" action="searchmovie.php" method="POST">
      <input type="text" placeholder="Search.." name="search" title="Entrez le nom du film à chercher sur le site" required>
      <button type="submit" title="Valider la recherche"><i class="fa fa-search"></i></button>
	</form>

	   <a href="#" class="toggle-button">
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </a>
        <div class="navbar-links">
          <ul>
	  <?php topCon(); ?>
          </ul>
        </div>
      </nav>

<script>
const toggleButton = document.getElementsByClassName('toggle-button')[0]
const navbarLinks = document.getElementsByClassName('navbar-links')[0]

toggleButton.addEventListener('click', () => {
  navbarLinks.classList.toggle('active')
})
</script>

	  <main> 



<div class="section">
<h2>Forum - <?php echo $titreFilm["titre"]; ?></h2>
<hr class="line">
</div>



<div class="tableall">
<table>
  <tr>
    <th>Sujets</th>
    <th>Auteur</th>
    <th>Réponses</th>
  </tr>

  <?php

  for ($i=0; $i < $limite; $i++) {
    $infos = $reqSujets->fetch();
    if ($infos != NULL) {
      $reqUser = $bd->prepare("SELECT id, pseudo FROM utilisateur WHERE id = ?");
      $reqUser->execute(array($infos["idAuteur"]));
      $user = $reqUser->fetch();
      echo '<tr>';
      echo '<td><a title="Lien vers le sujet '.$infos["titre"].'" href="forumpage.php?id='.$infos["id"].'&page=1&f='.$idFilm.'">'.$infos["titre"].'</a></td>';
      echo '<td><a title="Lien vers le profil de '.$user["pseudo"].'" href="profil.php?id='.$user["id"].'">'.$user["pseudo"].'</td>';
      echo '<td>'.$infos["nRéponses"].'</td>';
      echo '</tr>';  
    }
  }

  ?>


</table>
</div>

<br>
<br>
<br>
<br>


<div class="pagination">
<ul>

  <?php
  $max = 6;
  if ($page > 1) {
    $prev = $page-1;
    echo '<li><a title="Vers la page précédente" href="sujets.php?id='.$idFilm.'&page='.$prev.'"><</a></li>';
    if ($page > $max) {
      echo '<li><a title="Vers la première page" href="sujets.php?id='.$idFilm.'&page=1">1</a></li>';
      echo '<li><a title="Vers la page '.$max.'" href="sujets.php?id='.$idFilm.'&page='.$max.'">...</a></li>';
    }
  }
  $curr = $page;
  $far = $curr+$max/2;
  for ($e = $curr-($max/2); $e<=$far; $e++) {
    if ($e <= $nPages && $e >= 1) {
      if ($e == $page) {
        echo '<li><a title="Page actuelle" href="sujets.php?id='.$idFilm.'&page='.$e.'" class="active">'.$e.'</a></li>';
      } else {
        echo '<li><a title="Lien vers la page '.$e.'" href="sujets.php?id='.$idFilm.'&page='.$e.'">'.$e.'</a></li>';
      }
    }
  }
  if ($page+$max/2 < $nPages) {
    echo '<li><a title="Lien vers la page '.$far.'" href="sujets.php?id='.$idFilm.'&page='.$far.'">...</a></li>';
    echo '<li><a title="Lien vers la page '.$nPages.'" href="sujets.php?id='.$idFilm.'&page='.$nPages.'">'.$nPages.'</a></li>';
  }
  if ($page <$nPages) {
    $proch = $page+1;
    echo '<li><a title="Lien vers la prochaine page" href="sujets.php?id='.$idFilm.'&page='.$proch.'">></a></li>';
  }
  ?>

</ul>
	
	</div>


<br>
<br>
<br>
<br>
<br>
<br>



<?php
if (isConnected()) {
  echo '<div class="section2">';
  if (isset($erreurSuj)) {
    echo '<font color = "red">'.$erreurSuj.'</font>';
  }
  echo '<h2>Nouveau sujet</h2>';
  echo '<hr class="line">';
  echo '</div>';
  echo '<div class="textrating">';
  echo '<class="sujetsection">';
  echo '<form method = "POST" action ="">';
  echo '<textarea id="w3mission" type="text" placeholder="Saisir le titre du sujet" cols="70" name="title" required></textarea>';
  echo '<br>';
  echo '</class="criticsection">';
  echo '</div>';
  echo '<div class="section2">';
  echo '<h2 id="h2_2">Message</h2>';
  echo '<hr class="line">';
  echo '</div>';
  echo '<textarea id="messagearea" type="text" placeholder="Saisir votre message" rows="10"cols="70" name="message" required></textarea>';
  echo '<input class="btn" type="submit" value="Envoyer" name="send" title="Créer nouveau sujet">';
  echo '</form>';
}

?>



</br>
</br>
</br>






	 	


  </main>

<div class="footer">
  
  
 <a href="home.php"><img src="siteimg/cf2.png"  alt="logo"></a>
  
 <p>© CritiFilms.com, Inc. All rights reserved.</p>
  
</div>




</body>
</html>

