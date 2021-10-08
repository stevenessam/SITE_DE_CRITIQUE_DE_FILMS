<?php
session_start();

include("PHPFunc/utilitaire.php");
$bd = connectToDB();


if (isset($_GET["type"])) {
	$type = $_GET["type"];
} else {
	header("Location: home.php");
}





$req = $bd->prepare("SELECT * FROM film WHERE genre = ?");
$req->execute(array($type));


?>
<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="CSS/moviepage.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Critifilms - Liste des films du type <?php echo $type; ?></title>
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
<h2>Films du genre <?php echo $type; ?></h2>
<hr class="line">
</div>

<div class="filmlist">
	<?php
		while ($liste = $req->fetch()) {
			echo '<div class="movie">';
			echo '<a href="critipage.php?id='.$liste["id"].'"><img alt="Vers le film : '.$liste["titre"].'" src="data:image/png;base64,'.base64_encode( $liste["image"] ).'"/></a>';
			echo '<h2>'.$liste["titre"].'</h2>';
			echo '</div>';
		}
	?>
	</div>

	

</br>
</br>




  </main>

<div class="footer">
  
  
 <a href="home.php"><img src="siteimg/cf2.png"  alt="logo"></a>
  
 <p>© CritiFilms.com, Inc. All rights reserved.</p>
  
</div>






</body>
</html>

