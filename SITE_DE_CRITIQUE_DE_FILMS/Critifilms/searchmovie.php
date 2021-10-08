<?php
session_start();

include("PHPFunc/utilitaire.php");
$bd = connectToDB();

$headLine = "Résultat de recherche";

if (isset($_POST["search"])) {
	$txt = $_POST["search"];
}

if (isset($txt)) {
	$query = $bd->prepare("SELECT * FROM film WHERE titre LIKE '%$txt%'");
	$query->execute();
	$nResults = $query->rowCount();
	if ($nResults == 0) {
		$headLine = "Aucun résultat trouvé !";
	} else {
		if ($nResults > 1) {
			$headLine = "Résultats de recherche";
		}
	}
}

?>

<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">
<title>CritiFilms</title>
<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
<link rel="stylesheet" href="CSS/stylesearchmovie.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

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
<h2><?php echo $headLine; ?></h2>
<hr class="line">
</div>



<div class="searchlist">

<ul class="list">
  
	<?php
		if (isset($nResults)) {
			if ($nResults > 0) {
				for ($i = 0; $i < $nResults; $i++) {
					$results = $query->fetch();
					echo '<li id="rectangle">';
					echo '<h2>'.$results["titre"].'</h2>';
					echo '<p>'.$results["synopsis"].'</p>';
					echo '<span class="imglist"><img alt="Affiche du film '.$results["titre"].'" src="data:image/png;base64,'.base64_encode( $results["image"] ).'" class="imglist"></span>';
					echo '<a title="Lien vers la page du film '.$results["titre"].'" href="critipage.php?id='.$results["id"].'" class="btn">Ouvrir la page</a>';
					echo '</li>';
				}
			} else {
				echo '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
			}
		}
	?>

</ul>





</div>

















  </main>

<div class="footer">
  
  
 <a href="home.php"><img src="siteimg/cf2.png"  alt="logo"></a>
  
 <p>© CritiFilms.com, Inc. All rights reserved.</p>
  
</div>
	
	

</body>
</html>