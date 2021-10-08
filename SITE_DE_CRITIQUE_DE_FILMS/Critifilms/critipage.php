<?php
session_start();
include("PHPFunc/utilitaire.php");
$bd = connectToDB();

$id = NULL;

if (isset($_GET["id"])) {
  $id = $_GET["id"];
  $reFilm = $bd->prepare("SELECT * FROM film WHERE id= ?");
  $reFilm->execute(array($id));
  $filmInfos = $reFilm->fetch();

  $titre = $filmInfos["titre"];
  $synopsis = $filmInfos["synopsis"];
  $image = $filmInfos["image"];
  $lienBA = $filmInfos["lienBA"];
  $réal = $filmInfos["réalisateur"];
  $studio = $filmInfos["studio"];
  $genre = $filmInfos["genre"];
  $durée = $filmInfos["durée"];
  $sortie = $filmInfos["dateSortie"];
  $noteG = $filmInfos["noteGlobale"];

  $reqSortie = $bd->prepare("SELECT titre FROM film WHERE id = ? AND dateSortie <= NOW() LIMIT 1");
  $reqSortie->execute(array($id));
  $release = $reqSortie->fetch();

  if ($release == NULL) {
  	header("Location: home.php");
  }

  $newDate = convertDate($sortie, false);

  /// Supprimer comm
	if (isConnected()) {
		if (isset($_POST["remove"])) {
			$noteR = $bd->prepare("SELECT note FROM critique WHERE userID = ? AND filmID = ?");
			$noteR->execute(array($_SESSION["id"], $id));
			$note = $noteR->fetch();
			$del = $bd->prepare("DELETE FROM critique WHERE userID = ? AND filmID = ?");
			$del->execute(array($_SESSION["id"], $id));
			$nMoins = $bd->prepare("UPDATE utilisateur SET nCritiques = nCritiques - 1 WHERE id = ?");
			$nMoins->execute(array($_SESSION["id"]));
			$_SESSION["nCritiques"] = $_SESSION["nCritiques"] - 1;
			$filmNote = $bd->prepare("UPDATE film SET noteGlobale = noteGlobale - ?, nCritiques = nCritiques - 1 WHERE id = ?");
			$filmNote->execute(array($note[0], $id));
			header("Location: critipage.php?id=".$id);
		}
	}

    $nCris = $filmInfos["nCritiques"];
  	$ids = critiIDs($bd, $id);
	$listeIDs = listCritiIDs($ids);
	$taille = $nCris;
}

if (!isset($id)) {
	header("Location: home.php");
}

// Film pref ?
	
	if (isConnected()) {

		if (isset($_GET[" "])) {
			if ($_SESSION["filmPref"] == $id) {
				header("Location: critipage.php?id=".$id);
				$request = $bd->prepare("UPDATE utilisateur SET filmPref = 0 WHERE id= ?");
				$request->execute(array($_SESSION["id"]));
				$_SESSION["filmPref"] = 0;	
			} else {
				header("Location: critipage.php?id=".$id);
				$request = $bd->prepare("UPDATE utilisateur SET filmPref = ? WHERE id=?");
				$request->execute(array($id, $_SESSION["id"]));
				$_SESSION["filmPref"] = $id;
			}
		}

		if ($_SESSION["filmPref"] == $id) {
			$coeur = "logo/add.png";
		} else {
			$coeur = "logo/noadd.png";
		}


	}

/// Envoyer comm
	$erreur = "";

	if (isConnected() && isset($id)) {
		if (isset($_POST["send"]) && !critiExists($bd, $id)) {
			$starVal = $_POST["star"];
			if (isset($starVal)) {
				$note = 0;
				switch ($starVal) {
					case "star5":
						$note = 5;
					break;
					case "star4":
						$note = 4;
					break;
					case "star3":
						$note = 3;
					break;
					case "star2":
						$note = 2;
					break;
					case "star1":
						$note = 1;
					break;
				}
				$comm = $_POST["criti"];
				if (strlen(trim($comm)) != 0) {
					$envoi = $bd->prepare("INSERT INTO critique (note, commentaire, dateCriti, filmID, userID) VALUES (?, ?, ?, ?, ?)");
					$envoi->execute(array($note, $comm, date("Y-m-d"), $id, $_SESSION["id"]));

					$update = $bd->prepare("UPDATE utilisateur SET nCritiques = nCritiques + 1 WHERE id = ?");
					$update->execute(array($_SESSION["id"]));
					$_SESSION["nCritiques"] = $_SESSION["nCritiques"] + 1;

					$moyenne = $bd->prepare("UPDATE film SET noteGlobale = ?, nCritiques = nCritiques + 1 WHERE id = ?");
					$moyenne->execute(array($noteG+$note, $id));
					$noteG = $filmInfos["noteGlobale"];
					if (!$envoi) {
						$erreur = "Il y a eu un problème lors de la mise en ligne de votre avis";
					}
				} else {
					$erreur = "Veuillez écrire un commentaire";
				}

			} else {
				$erreur = "Veuillez donner une note";
			}
		}
	}

// Goto forum
	if (isset($_POST["sendForum"])) {
		header("Location: sujets.php?id=".$id."&page=1");
	}
?>


<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">
<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
<link rel="stylesheet" href="CSS/stylecriti.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php if (isset($id)) {echo "<title>Critifilms - $titre</title";} ?>

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
	  
<div class="header">
	<div class="image">
		<?php if (isset($id)) {
			echo "<h2>$titre</h2>"; 
			echo '<hr class="line">';

			 echo '<img alt="Affiche du film '.$titre.'" src="data:image/png;base64,'.base64_encode( $image ).'" width="50%"/>'; 

			echo "<p>";
	    	echo "$synopsis<br><br><br>Réalisateur : $réal<br><br> Studio : $studio<br><br> Genre : $genre<br><br> Durée : $durée min<br><br> Sortie : $newDate<br><br>";
	    	if (isset($noteG) && isset($taille)) {
	    		if ($noteG > 0 && $taille > 0) {
	    			echo "Note globale : ".$noteG/$taille."/5";
	    		} else {
	    			echo "Note globale : -/5";
	    		}
	    	} else {
	    		echo "Note globale : -/5";
	    	}
	    	echo "<br><br>";
			if (isConnected()) {
				

			    echo "<a title='Cliquez pour définir comme film préféré' href='critipage.php?id=".$id."& =1'><img alt='Image Coeur' id='prefimg' alt='imageCoeur' src=$coeur width='2%'></a>";
				echo "<font color='white' id='preftext'>Définir comme film favori</font>";
			}
			
			echo "</p>";
			}
			
    	?>

	</div>

</div>
	
	
	
	
	
	<br><br>
	<br><br>
	<br><br>
<div class="trailer">
		<h2>BANDE-ANNONCE</h2>
    <?php 
    echo "<iframe src=$lienBA>"; 
    echo "</iframe>";
    ?>


</div>

	<div class="section">
		<hr class="line">
	
			<div class="insidesectionforum">
			
				<h3>Aller vers le forum du film</h3>
				<form method="POST" action="">
				<input class="btnforum" type="submit" value="Forum" name="sendForum" title="Bouton du forum du film">
				</form>
			</div>
		
		
		
	</div>


	<?php echo "<font color = 'red'>".$erreur."</font>"; ?>
	
	



	<div class="textrating">
	

	
<?php 
	if (isConnected()) {
		if (!critiExists($bd, $id)) {
			echo '<h2>Donnez votre avis sur ce film</h2>
			<hr class="line">
			<div class="stars">
			<form method="POST" action="">
			<input class="star star-5" id="star-5" type="radio" name="star" value="star5"/>
			<label class="star star-5" for="star-5"></label>
			<input class="star star-4" id="star-4" type="radio" name="star" value="star4"/>
			<label class="star star-4" for="star-4"></label>
			<input class="star star-3" id="star-3" type="radio" name="star" value="star3"/>
			<label class="star star-3" for="star-3"></label>
			<input class="star star-2" id="star-2" type="radio" name="star" value="star2"/>
			<label class="star star-2" for="star-2"></label>
			<input class="star star-1" id="star-1" type="radio" name="star" value="star1"/>
			<label class="star star-1" for="star-1"></label>
			</div>
				<class="criticsection" >
					 <textarea id="w3mission" type="text" rows="7" cols="70" name="criti">
					</textarea>
					<br>
				  <input class="btn" type="submit" value="Envoyer" name="send">
			</div>
			</form>';
		}

	}
?>
	
	
	
	
	
	
	
		<hr class="line2">
		
		
	
	<div class="commentsection">
	
	
	<?php 
		if (isset($id)) {

			$ids = critiIDs($bd, $id);
			$listeIDs = listCritiIDs($ids);
			$taille = nCriti($listeIDs);
			for ($i=0; $i<$taille; $i++) {
				$currCriti = $bd->prepare("SELECT * FROM critique WHERE id = ?");
				$currCriti->execute(array($listeIDs[$i]));
				$comInfos = $currCriti->fetch();
				$currUser = $bd->prepare("SELECT * FROM utilisateur WHERE id = ?");
				$currUser->execute(array($comInfos["userID"]));
				$cUser = $currUser->fetch();

				echo '<div class="infosection">';
				if ($cUser["photoProfil"] != NULL) {
					displayImage($cUser["photoProfil"]);
				} else {
					echo '<img alt="Image utilisateur basique" src="logo/usericon.png" width="128" height="128"/>';
				}
				echo '<a title="Vers le profil de '.$cUser["pseudo"].'" href = "profil.php?id='.$cUser["id"].'"><h4>'.$cUser["pseudo"].'</h4></a>';
				$realDate = convertDate($comInfos["dateCriti"], false);
				echo '<p>'.$realDate.'</p>';
				echo '<p> Note : '.$comInfos["note"].'/5'.'</p>';
				echo '</div>';
				echo '<div class="textrating2">';
				echo '<p>'.$comInfos["commentaire"].'</p>';
				echo '</div>';
				echo '<form class="delete" method="POST" action="">';
				if (isConnected()) {
					if ($cUser["id"] == $_SESSION["id"]) {
						echo '<input class="btn2" type="submit" value="Supprimer" name="remove" title="Supprimer ma critique">';
					}
				}
				echo '</form>';
				echo "<br><br><br><br><br><br><br><br>";
				}

		}
	?>
	
	</div>
	
	
	
	
	
	   </br>
</br>
<br>


  </main>

<div class="footer">
  
  
 <a href="home.php"><img src="siteimg/cf2.png"  alt="logo"></a>
  
 <p>© CritiFilms.com, Inc. All rights reserved.</p>
  
</div>




</body>
</html>

