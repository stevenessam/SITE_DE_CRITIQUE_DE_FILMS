<?php
session_start();

include("PHPFunc/utilitaire.php");
$bd = connectToDB();

$ligne = 6;
$display = $ligne*$ligne;

if (isset($_GET["page"])) {
	$page = $_GET["page"];
} else {
	$page = 1;
}

$nFilms = $bd->prepare("SELECT COUNT(*) FROM film");
$nFilms->execute();
$nbrFilms = $nFilms->fetch();

$nPages = ceil($nbrFilms[0]/$display);

if ($page <= 0 || $page > $nPages) {
	$page = 1;
}


$minID = $display*($page-1);

$req = $bd->prepare("SELECT * FROM film WHERE id > ? LIMIT $display");
$req->execute(array($minID));


?>
<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="CSS/moviepage.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Critifilms - Liste des films</title>
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
<h2>Films</h2>
<hr class="line">
</div>

<div class="filmlist">
	<?php
		for ($i=0; $i<$display; $i++) {
			$liste = $req->fetch();
			if ($liste != NULL) {
				echo '<div class="movie">';
				echo '<a href="critipage.php?id='.$liste["id"].'"><img alt="Vers le film : '.$liste["titre"].'" src="data:image/png;base64,'.base64_encode( $liste["image"] ).'"/></a>';
				echo '<h2>'.$liste["titre"].'</h2>';
				echo '</div>';
			}
		}
	?>
	</div>
	
	
	
	<div class="pagination">
<ul>
	<?php
	$max = 6;
	if ($page > 1) {
		$prev = $page-1;
		echo '<li><a title="Vers la page précédente" href="moviepage.php?page='.$prev.'"><</a></li>';
		if ($page > $max) {
			echo '<li><a title="Vers la première page" href="moviepage.php?page=1">1</a></li>';
			echo '<li><a title="Vers la page '.$max.'" href="moviepage.php?page='.$max.'">...</a></li>';
		}
	}
	$curr = $page;
	$far = $curr+$max/2;
	for ($e = $curr-($max/2); $e<=$far; $e++) {
		if ($e <= $nPages && $e >= 1) {
			if ($e == $page) {
				echo '<li><a title="Page actuelle" href="moviepage.php?page='.$e.'" class="active">'.$e.'</a></li>';
			} else {
				echo '<li><a title="Lien vers la page '.$e.'" href="moviepage.php?page='.$e.'">'.$e.'</a></li>';
			}
		}
	}
	if ($page+$max/2 < $nPages) {
		echo '<li><a title="Lien vers la page '.$far.'" href="moviepage.php?page='.$far.'">...</a></li>';
		echo '<li><a title="Lien vers la page '.$nPages.'" href="moviepage.php?page='.$nPages.'">'.$nPages.'</a></li>';
	}
	if ($page <$nPages) {
		$proch = $page+1;
		echo '<li><a title="Lien vers la prochaine page" href="moviepage.php?page='.$proch.'">></a></li>';
	}
	?>
</ul>
	
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

