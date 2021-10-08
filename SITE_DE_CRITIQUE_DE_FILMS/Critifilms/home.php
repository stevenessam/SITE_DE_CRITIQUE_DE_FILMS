<?php
session_start();
include("PHPFunc/utilitaire.php");
$bd = connectToDB();


$reqGenres = $bd->prepare("SELECT genre, COUNT(*) FROM film GROUP BY genre ORDER BY COUNT(*) DESC");
$reqGenres->execute();

$reqBestNote = $bd->prepare("SELECT id, titre, image FROM film ORDER BY noteGlobale DESC LIMIT 6");
$reqBestNote->execute();

$reqProchain = $bd->prepare("SELECT lienBA, titre FROM film WHERE dateSortie >= NOW() ORDER BY dateSortie");
$reqProchain->execute();

$reqHasard = $bd->prepare("SELECT lienBA, titre FROM film ORDER BY RAND() LIMIT 3");
$reqHasard->execute();


$reqAffiche = $bd->prepare("SELECT imageHori, id, titre FROM film WHERE dateSortie <= NOW() ORDER BY RAND() LIMIT 4");
$reqAffiche->execute();

$reqFilmHasard = $bd->prepare("SELECT id, titre, image FROM film ORDER BY RAND() LIMIT 6");
$reqFilmHasard->execute();

?>


<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">
<title>CritiFilms</title>
<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
<link rel="stylesheet" href="CSS/stylehome.css?v=<?php echo time(); ?>">

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
<h2>Bienvenue sur CritiFilms</h2>
<hr class="line">
</div>

<div id="slider">
   <input type="radio" name="slider" id="slide1" checked>
   <input type="radio" name="slider" id="slide2">
   <input type="radio" name="slider" id="slide3">
   <input type="radio" name="slider" id="slide4">
   <div id="slides">
      <div id="overflow">
         <div class="inner">
          <?php
          for ($r=1; $r<=4; $r++) {
            echo '<div class="slide slide_'.$r.'">';
            echo '<div class="slide-content">';
            $affiche = $reqAffiche->fetch();
            echo '<p><a href="critipage.php?id='.$affiche["id"].'"><img alt="Lien vers '.$affiche["titre"].'" src="data:image/png;base64,'.base64_encode( $affiche["imageHori"] ).'"/></a></p>';
            echo '</div></div>';
          }
          ?>
               
                    

         </div>
      </div>
   </div>
   <div id="controls">
      <label for="slide1"></label>
      <label for="slide2"></label>
      <label for="slide3"></label>
      <label for="slide4"></label>
   </div>
   <div id="bullets">
      <label for="slide1"></label>
      <label for="slide2"></label>
      <label for="slide3"></label>
      <label for="slide4"></label>
   </div>
</div>






<div class="section">
<hr class="line">
</div>


	<span id="titre">	<h2> Les mieux notés</h2>	</span>	
<div class="populaire">

	
		
  <?php
    $bNote = 1;
    while ($listeBest = $reqBestNote->fetch()) {
      echo '<div class="movie">';
      echo '<a href="critipage.php?id='.$listeBest["id"].'"><img alt= "Position '.$bNote.' dans les mieux notés : '.$listeBest["titre"].'" src="data:image/png;base64,'.base64_encode( $listeBest["image"] ).'"/></a>';
      echo '<h2>'.$listeBest["titre"].'</h2>';
      echo '</div>';
      $bNote++;
    }
  ?>



</div>

<div class="section">
<hr class="line">
</div>


<span id="titre"> <h2>Bande-annonces au hasard</h2> </span>

    <div class="nouvfilms">
        

      <?php
      while ($random = $reqHasard->fetch()) {
        echo '<div class="container-container">';
          echo '<div class="video-container">';
            echo '<iframe src="'.$random["lienBA"].'" frameborder="0" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen>Bande annonce de ;'.$random["titre"].'.</iframe>';
          echo '</div>';
        echo '</div>';
      }


      ?>


    </div>
	

  <div class="section">
<hr class="line">
</div>


    <span id="titre"> <h2> Films au hasard</h2> </span> 
	
	<div class="populaire">

    
  <?php
  while ($films = $reqFilmHasard->fetch()) {
      echo '<div class="movie">';
      echo '<a href="critipage.php?id='.$films["id"].'"><img alt= "Film : '.$films["titre"].'" src="data:image/png;base64,'.base64_encode( $films["image"] ).'"/></a>';
      echo '<h2>'.$films["titre"].'</h2>';
      echo '</div>';
  }
  ?>



</div>
	
	
	<div class="section">
<hr class="line">
</div>
	
	
	

  <span id="titre"> <h2>Films à venir</h2> </span>

    <div class="nouvfilms">


      <?php
      while ($proch = $reqProchain->fetch()) {
        echo '<div class="container-container">';
          echo '<div class="video-container">';
            echo '<iframe src="'.$proch["lienBA"].'" frameborder="0" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
          echo '</div>';
        echo '<h3 class="titrefilm">'.$proch["titre"].'</h3>';
        echo '</div>';
      }


      ?>


    </div>
	
	<div class="section">
<hr class="line">
</div>
	
	

	  <span id="titre"> <h2>Statistiques</h2> </span>
	
	<div class="tableall">
<table>
  <tr>
    <th>Genre</th>
    <th>Total des films du genre</th>
  </tr>
<?php
  $tot = 0;
  while ($genres = $reqGenres->fetch()) {
    echo '<tr>';
    echo '<td><a href="genrePage.php?type='.$genres["genre"].'" title="Liste des films du genre '.$genres["genre"].'">'.$genres["genre"].'</a></td>';
    echo '<td>'.$genres["COUNT(*)"].'</td>';
    echo '</tr>';
    $tot += $genres["COUNT(*)"];
  }
    echo '<th>Total</th>';
    echo '<th>'.$tot.'</th>';
?>

</table>
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