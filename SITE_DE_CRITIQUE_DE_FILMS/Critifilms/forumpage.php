<?php
session_start();
include("PHPFunc/utilitaire.php");
$bd = connectToDB();

if (isset($_GET["id"]) && isset($_GET["page"]) && isset($_GET["f"])) {
	$idSujet = $_GET["id"];
	$page = $_GET["page"];
	$filmID = $_GET["f"];
} else {
	header("Location: home.php");
}

$limite = 20;

if (isset($_POST["send"]) && isConnected()) {
	$réponse = $_POST["reponse"];
	if (strlen(trim($réponse)) != 0) {	
		$reqRep = $bd->prepare("INSERT INTO message (idSujet, idAuteur, message, datePubli) VALUES (?, ?, ?, NOW())");
		$reqRep->execute(array($idSujet, $_SESSION["id"], $réponse));
		if (!$reqRep) {
			$erreurRep = "Il y a eu un problème lors de la mise en ligne de votre message.";
		}

		$reqPos = $bd->prepare("UPDATE sujet SET pos = pos+1 WHERE id != ?");
		$reqPos->execute(array($idSujet));

		$reqUpdate = $bd->prepare("UPDATE sujet SET nRéponses = nRéponses + 1, pos = 1 WHERE id = ?");
		$reqUpdate->execute(array($idSujet));



	} else {
		$erreurRep = "Veuillez écrire une réponse.";
	}
}

//Supprimer message
if (isset($_GET[" "]) && isConnected()) {
	$idMess = $_GET[" "];
	$reqMessExists = $bd->prepare("SELECT * FROM message WHERE idSujet = ? AND idAuteur = ? AND id = ? LIMIT 1");
	$reqMessExists->execute(array($idSujet, $_SESSION["id"], $idMess));
	$exists = $reqMessExists->fetch();
	if ($exists["id"] != NULL) {
		$reqFirst = $bd->prepare("SELECT id FROM message WHERE idSujet = ? LIMIT 1");
		$reqFirst->execute(array($exists["idSujet"]));
		$first = $reqFirst->fetch();
		if ($first["id"] == $exists["id"]) {
			$reqSupprMessages = $bd->prepare("DELETE FROM message WHERE idSujet = ?");
			$reqSupprMessages->execute(array($exists["idSujet"]));

			$reqSujPos = $bd->prepare("SELECT pos FROM sujet WHERE id = ?");
			$reqSujPos->execute(array($exists["idSujet"]));
			$pos = $reqSujPos->fetch();

			$reqUpdateSujs = $bd->prepare("UPDATE sujet SET pos = pos-1 WHERE pos > ?");
			$reqUpdateSujs->execute(array($pos["pos"]));

			$reqSupprSujet = $bd->prepare("DELETE FROM sujet WHERE id = ?");
			$reqSupprSujet->execute(array($exists["idSujet"]));
			header("Location: sujets.php?id=".$filmID."&page=1");
		} else {
			$reqUpdateSujet = $bd->prepare("UPDATE sujet set nRéponses = nRéponses - 1 WHERE id = ?");
			$reqUpdateSujet->execute(array($exists["idSujet"]));

			$reqSuppr = $bd->prepare("DELETE FROM message WHERE id = ?");
			$reqSuppr->execute(array($idMess));
			header("Location: forumpage.php?id=".$idSujet."&page=".$page."&f=".$filmID);	
		}



	} else {
		header("Location: forumpage.php?id=".$idSujet."&page=".$page."&f=".$filmID);
	}
}


$reqSujet = $bd->prepare("SELECT titre, idFilm FROM sujet WHERE id = ? LIMIT 1");
$reqSujet->execute(array($idSujet));
$infosSujet = $reqSujet->fetch();

$reqFilm = $bd->prepare("SELECT id, titre FROM film WHERE id = ? LIMIT 1");
$reqFilm->execute(array($infosSujet["idFilm"]));
$film = $reqFilm->fetch();

$reqNMess = $bd->prepare("SELECT id FROM message WHERE idSujet = ?");
$reqNMess->execute(array($idSujet));
$liste = array();

$total = 0;
while (($ids = $reqNMess->fetch()) != NULL) {
  $total += 1;
  $liste[] = $ids["id"];
}

$nPages = ceil($total/$limite);


?>


<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="CSS/styleforumpage.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title><?php echo $infosSujet["titre"]; ?> - Forum <?php echo $film["titre"]; ?> sur CritiFilms</title>
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
	  
<?php
echo '<h2>Forum - <a title="Lien vers le film '.$film["titre"].'" href="critipage.php?id='.$film["id"].'">'.$film["titre"].'</a></h2>';
?>
<p><?php echo $infosSujet["titre"]; ?></p>
<hr class="line">
</div>
	  


<?php
	
	$begin = $limite*($page-1);

	for ($i=$begin; $i<$begin+$limite; $i++) {
		$reqMessage = $bd->prepare("SELECT * FROM message WHERE id = ?");
		$reqMessage->execute(array($liste[$i]));
		$message = $reqMessage->fetch();
		if ($message != NULL) {
			$reqUser = $bd->prepare("SELECT id, pseudo, photoProfil FROM utilisateur WHERE id = ?");
			$reqUser->execute(array($message["idAuteur"]));
			$user = $reqUser->fetch();
			echo '<div class="reponsearea">';
			echo '<br>';
			echo '<div class="imageprofile">';
			if (isConnected()) {
				if ($user["id"] == $_SESSION["id"]) {
					echo '<a title="Supprimer message" href="forumpage.php?id='.$idSujet.'&page='.$page.'&f='.$filmID.'& ='.$message["id"].'" class="btndelete" name="deleteMess">Supprimer</a>';
				}
			}
			displayImage($user["photoProfil"]);
			echo '<div class ="insideinfo" >';
			echo '<h2><a title="Lien vers le profil de '.$user["pseudo"].'" href="profil.php?id='.$user["id"].'" >'.$user["pseudo"].'</a></h2>';
			$newDate = convertDate($message["datePubli"], true);
			echo '<h3>'.$newDate.'</h3>';
			echo '</div>';
			echo '</div>';

			echo '<div class="section2">';
			echo '<hr class="line">';
			echo '</div>';
			echo '<br>';
			echo '<div class="insidemessage">';
			echo '<p>';
			echo $message["message"];
			echo '</p>';
			echo '</div>';
			echo '</div>';
		}
	}

?>
	  

<br>
<br>
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
		echo '<li><a title="Vers la page précédente" href="forumpage.php?id='.$idSujet.'&page='.$prev.'&f='.$filmID.'"><</a></li>';
		if ($page > $max) {
			echo '<li><a title="Vers la première page" href="forumpage.php?id='.$idSujet.'&page=1&f='.$filmID.'">1</a></li>';
			echo '<li><a title="Vers la page '.$max.'" href="forumpage.php?id='.$idSujet.'&page='.$max.'&f='.$filmID.'">...</a></li>';
		}
	}
	$curr = $page;
	$far = $curr+$max/2;
	for ($e = $curr-($max/2); $e<=$far; $e++) {
		if ($e <= $nPages && $e >= 1) {
			if ($e == $page) {
				echo '<li><a title="Page actuelle" href="forumpage.php?id='.$idSujet.'&page='.$e.'&f='.$filmID.'" class="active">'.$e.'</a></li>';
			} else {
				echo '<li><a title="Lien vers la page '.$e.'" href="forumpage.php?id='.$idSujet.'&page='.$e.'&f='.$filmID.'">'.$e.'</a></li>';
			}
		}
	}
	if ($page+$max/2 < $nPages) {
		echo '<li><a title="Lien vers la page '.$far.'" href="forumpage.php?id='.$idSujet.'&page='.$far.'&f='.$filmID.'">...</a></li>';
		echo '<li><a title="Lien vers la page '.$nPages.'" href="forumpage.php?id='.$idSujet.'&page='.$nPages.'&f='.$filmID.'">'.$nPages.'</a></li>';
	}
	if ($page <$nPages) {
		$proch = $page+1;
		echo '<li><a title="Lien vers la prochaine page" href="forumpage.php?id='.$idSujet.'&page='.$proch.'&f='.$filmID.'">></a></li>';
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
		echo '<div class="section2">
<h2 id="h2_2">Répondre au sujet</h2>
<hr class="line">
</div>';
	if (isset($erreurRep)) {
		echo '<font color = "red">'.$erreurRep.'</font>';
	}

echo '<form method = "POST" action ="">';
echo '<textarea id="messagearea" type="text" placeholder="Ecrire votre réponse" rows="7"cols="70" name="reponse" required></textarea>';

echo '<input class="btn" type="submit" value="Envoyer" name="send" title="Envoyer message">';
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

