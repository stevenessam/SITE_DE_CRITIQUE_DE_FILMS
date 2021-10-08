<?php
session_start();
include("PHPFunc/utilitaire.php");

$db = connectToDB();

  if(isset($_GET["id"])) {
    $uID = $_GET["id"];
  } else {
      header("Location: home.php");
  }

// Modification des infos

  if (isset($_POST["modif"])) {
    header("Location: changeInfos.php");
  }


    $requ = $db->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $requ->execute(array($uID));
    $infos = $requ->fetch();


?>



<!DOCTYPE html>
<html lang="FR">
<head>
<meta charset="UTF-8">

<link rel="stylesheet" href="CSS/styleprofile.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<title>Profil</title>
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
<div id="profile">

<div class="wrapper">
    <div class="left">
        <?php if ($infos["photoProfil"] != NULL) { displayImage($infos["photoProfil"]); } else { echo '<img title="Photo profil de base" src="logo/usericon.png" width="128" height="128"/>';} ?>
        <h4><?php echo $infos["pseudo"]; ?></h4>
         <p>Profil</p>
		 

    </div>
    <div class="right">
        <div class="info">
            <h3>Informations</h3>    
            <div class="info_data">
                 <div class="data">
                    <h4>Username</h4>
                    <p><?php echo $infos["pseudo"]; ?></p>
                 </div>

              <?php
                if (isConnected()) {
                  if ($uID == $_SESSION["id"]) {
                    echo '<div class="data">';
                    echo '<h4>Email</h4>';
                    echo '<p>';
                    echo $infos["email"];
                    echo '</p>';
                    echo '</div>';
                  }
                }
              ?>
            </div>
        </div>
      
      <div class="infotwo">
            <h3></h3>
            <div class="infotwo_data">
                 <div class="data">
                    <h4>Prenom</h4>
                    <p><?php echo $infos["prenom"]; ?></p>
                 </div>
                 <div class="data">
                   <h4>Nom</h4>
                    <p><?php echo $infos["nom"]; ?></p>
              </div>
            </div>
        </div> 

      <div class="infotwo">
            <h3></h3>
            <div class="infotwo_data">
                 <div class="data">
                    <h4>Film préféré</h4>
                    <p><?php 
                          if ($infos["filmPref"] > 0) {
                            $request = $db->prepare("SELECT * FROM film WHERE id= ?");
                            $request->execute(array($infos["filmPref"]));
                            if ($request) {
                              $infosFilm = $request->fetch();
                              echo "<h4>".$infosFilm["titre"]."</h4>";
                              $image = $infosFilm["image"];
                              $fID = $infos["filmPref"];
                              $page = "critipage.php?id=".$fID;
                              echo '<a title="Lien vers le film '.$infosFilm["titre"].'" href='.$page.'><img src="data:image/png;base64,'.base64_encode( $image ).'" width="10%"/></a>';
                            }
                          } else {
                            echo "Aucun";
                          }
                      ?>
                        
                      </p>
                 </div>
                 <div class="data">
                   <h4>Nombre de critiques</h4>
                    <p><?php echo $infos["nCritiques"]; ?></p>
              </div>
            </div>
        </div> 

  <?php
    if (isset($uID) && isConnected()) {
      if ($uID == $_SESSION["id"]) {
        echo '<div class="infotwo">';
        echo '<h3>Modifier vos infos</h3>';
        echo '<h4>Cliquez sur le lien pour changer vos informations : </h4>';
        echo '<form method="POST" action="">';
        echo '<input type="submit" value="Modifier les infos" name="modif" title="Cliquez pour modifier vos informations de compte">';
        echo '</form>';
        echo '</div>';
      }
    }
  ?>
      
    </div>
</div>
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