<?php 
session_start();
include("PHPFunc/utilitaire.php");
$bd = connectToDB();
?>

<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">
<title>À propos</title>
<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
<link rel="stylesheet" href="CSS/styleabout.css?v=<?php echo time(); ?>">
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
  
  <section class="team-section">
     <div class="container">
         <div class="row">
             <div class="section-title">
                 <h1>À propos de nous</h1>
				 <hr class="line">
                 <p>Bienvenue sur le nouveau site Critifilms.com, la destination par excellence du monde de la critique de films et des commentaires.</p>
             </div>
         </div>
		 
		 
		 
         <div class="row">
             <div class="team-items">
                  <div class="item">
                      <img src="about/matheo.png" alt="Photo Pasquier Matéo" />
                      <div class="inner">
                          <div class="info">
                               <h5>Matéo Pasquier</h5>
                               <p>PHP / SQL</p>
                               <div class="social-links">
                                   <a href="https://twitter.com/Datkii"><span class="fa fa-twitter"></span></a>
                                   <a href="https://www.linkedin.com/in/matéo-pasquier-a4376a1aa"><span class="fa fa-linkedin"></span></a>
                                   <a href="https://www.youtube.com/channel/UC5K7iGKedN7ROdNWMNPkE9w"><span class="fa fa-youtube"></span></a>
                               </div>
                          </div>
                      </div>
                  </div>
                  <div class="item">
                      <img src="about/steven.jpg" alt="Photo Essam Steven" />
                      <div class="inner">
                          <div class="info">
                               <h5>Steven Essam</h5>
                               <p>HTML / CSS / Design</p>
                               <div class="social-links">
                                   <a href="https://twitter.com/StevenEssam7"><span class="fa fa-twitter"></span></a>
                                   <a href="https://fr.linkedin.com/in/steven-essam-2126ab195"><span class="fa fa-linkedin"></span></a>
                                   <a href="https://www.youtube.com/channel/UCxuwsaWVrd3v3OT2HtV-DZg"><span class="fa fa-youtube"></span></a>
                               </div>
                          </div>
                      </div>
                  </div>
                 
                </div>
             </div>
          </div>
           
  </section>


  </main>
<div class="footer">
  
  
 <a href="home.php"><img src="siteimg/cf2.png"  alt="logo"></a>
  
 <p>© CritiFilms.com, Inc. All rights reserved.</p>
  
</div>


</body>
</html>
