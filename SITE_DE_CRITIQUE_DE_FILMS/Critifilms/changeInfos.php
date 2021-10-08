<?php
session_start();
include("PHPFunc/utilitaire.php");

$db = connectToDB();

  if (isConnected()) {
    $uID = $_SESSION["id"];
  } else {
      header("Location: home.php");
  }


    $requ = $db->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $requ->execute(array($uID));
    $infos = $requ->fetch();

    $erreur = "";
    $erreurN = "";
    $erreurP = "";

// Changement du mdp
    if (isset($_POST["changePass"])) {
        if (!empty($_POST["password1"]) && !empty($_POST["password2"])) {
                $PASSW = sha1($_POST["password1"]);
                $PASSW2 = sha1($_POST["password2"]);
            if ($PASSW == $PASSW2) {
                $insertmbr = $db->prepare("UPDATE utilisateur SET mdp = ? WHERE id = ?");
                $insertmbr->execute(array($PASSW, $_SESSION["id"]));
                $erreur = "Votre mot de passe a bien été modifié";
            } else {
                $erreur = "Les mots de passe doivent être identiques";
            }
        } else {
            $erreur = "Veuillez remplir les deux champs";
        }
    }

// Changement du nom

    if (isset($_POST["changeNom"])) {
        $nNOM= htmlspecialchars($_POST["nom"]);
        if (!isset($_POST["nom"])) {
          $nNOM = "-";
        }
        $insertmbr = $db->prepare("UPDATE utilisateur SET nom = ? WHERE id = ?");
        $insertmbr->execute(array($nNOM, $_SESSION["id"]));
        $erreurN = "Votre nom a bien été modifié";
    }

// Changement du prénom

    if (isset($_POST["changePrenom"])) {
        $nPRENOM= htmlspecialchars($_POST["prenom"]);
        if (!isset($_POST["prenom"])) {
          $nPRENOM = "-";
        }
        $insertmbr = $db->prepare("UPDATE utilisateur SET prenom = ? WHERE id = ?");
        $insertmbr->execute(array($nPRENOM, $_SESSION["id"]));
        $erreurN = "Votre prenom a bien été modifié";
    }

//Changement de la photo profil
    $erreurP = "";
    if (isset($_POST["subPhoto"]) && isConnected()) {
      $fileError = $_FILES["photo"]["error"];
      if ($fileError == 0) {
        $id = $_SESSION["id"];
        $name = $_FILES["photo"]["name"];
        $fileSize = $_FILES["photo"]["size"];
        $fileExt = explode(".", $name);
        $fileActualExt = strtolower(end($fileExt));
        $allowed = array("png", "jpg", "jpeg");
        if (in_array($fileActualExt, $allowed)) {
          if ($fileSize < 2000000) {
            $type = $_FILES["photo"]["type"];
            $data = file_get_contents($_FILES["photo"]["tmp_name"]);
            if (substr($type, 0, 5) == "image") {
              $stmt = $db->prepare("UPDATE utilisateur SET photoProfil= ? WHERE id = $id ");
              $stmt->execute(array($data));
              $_SESSION["pic"] = $data;
              $erreur = "";
              header("Location: profil.php?id=".$_SESSION["id"]);
            } else {
              $erreurP = "Veuillez sélectionner une image valide";
            }
          } else {
            $erreurP = "Taille maximale autorisée : 2mb";
          }
        } else {
          $erreurP = "Seules les extensions .png, .jpg, ,jpeg sont autorisées";
        }
      } else {
        $erreurP = "Veuillez sélectionner une image";
      }
    }

  // Supprimer Compte
  if (isset($_POST["suppr"]) && isConnected()) {
    $id = $_SESSION["id"];
    userDeconnection();

    $reqListeMess = $db->prepare("SELECT idSujet FROM message WHERE idAuteur = ?");
    $reqListeMess->execute(array($id));

    while ($liste = $reqListeMess->fetch()) {
      $reqUpdateSujs = $db->prepare("UPDATE sujet SET nRéponses = nRéponses - 1 WHERE id = ?");
      $reqUpdateSujs->execute(array($liste["idSujet"]));
    }

    $reqMessages = $db->prepare("DELETE FROM message WHERE idAuteur = ?");
    $reqMessages->execute(array($id));

    $reqListeSujets = $db->prepare("SELECT id, pos FROM sujet WHERE idAuteur = ?");
    $reqListeSujets->execute(array($id));

    while ($sujets = $reqListeSujets->fetch()) {
      $reqDelMess = $db->prepare("DELETE FROM message WHERE idSujet = ?");
      $reqDelMess->execute(array($sujets["id"]));

      $reqUpdatePos = $db->prepare("UPDATE sujet SET pos = pos-1 WHERE pos > ?");
      $reqUpdatePos->execute(array($sujets["pos"]));

      $reqDelSuj = $db->prepare("DELETE FROM sujet WHERE id = ?");
      $reqDelSuj->execute(array($sujets["id"]));
    }

    $reqListeCriti = $db->prepare("SELECT filmID, note FROM critique WHERE userID = ?");
    $reqListeCriti->execute(array($id));

    while ($critis = $reqListeCriti->fetch()) {
      $reqFilmUpdate = $db->prepare("UPDATE film SET nCritiques = nCritiques - 1, noteGlobale = noteGlobale - ? WHERE id = ?");
      $reqFilmUpdate->execute(array($critis["note"], $critis["filmID"]));
    }

    $reqDelCritis = $db->prepare("DELETE FROM critique WHERE userID = ?");
    $reqDelCritis->execute(array($id));

    $reqDelUser = $db->prepare("DELETE FROM utilisateur WHERE id = ? LIMIT 1");
    $reqDelUser->execute(array($id));

    header("Location: home.php");

  } 

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
  <?php
    if (isset($uID) && isConnected()) {
      if ($uID == $_SESSION["id"]) {
      /// MDP
      echo '<div class="password">';
                echo '<h3>Modification Du Mot De Passe</h3>';
                echo '<div class="password_data">';
                     echo '<div class="data">';
             echo '<form method="POST" action="">';
               echo'<font color="red">'. $erreur.'</font><br><br>';
                          echo '<label>Mot de passe: </label>';
                echo '<input type="password" id="pass" name="password1" title="Nouveau mot de passe"><br><br>';
                echo '<label>Confirmation: </label>';
                echo '<input type="password" id="pass" name="password2" title="Répétez le mot de passe"><br><br>';
                          
                echo '<input type="submit" name="changePass">';
        echo '</form>';
                     echo '</div>';
                     
               echo  '</div>';
            echo '</div>';
          echo '<div class="infotwo">';
                echo '<h3>Modification de la photo profil</h3>';
                echo '<div class="infotwo_data">';
                  echo '<form method="POST" action="" enctype="multipart/form-data">';
                  echo '<label>Choisir une image :</label>';
                  echo '<input type="file" class="filephoto" name="photo" title="Choisissez votre nouvelle photo">';
                  echo '<input type="submit" value="Envoyer" name="subPhoto" title="Envoyer votre nouvelle photo">';
                  echo "<font color='red'>".$erreurP."</font>";
                echo '</form>';
                echo '</div>';
            echo '</div>'; 

      /// NOM
      echo '<div class="password">';
                echo '<h3>Modification De Votre Nom</h3>';
                echo '<div class="password_data">';
                     echo '<div class="data">';
             echo '<form method="POST" action="">';
               echo'<font color="red">'. $erreurN.'</font><br><br>';
                          echo '<label>Nouveau nom : </label>';
                echo '<input type="text" id="pass" name="nom" title="Nouveau nom"><br><br>';
                          
                echo '<input type="submit" name="changeNom" title="Envoyer nom">';
        echo '</form>';
                     echo '</div>';
                     
               echo  '</div>';
            echo '</div>';
      /// PRENOM 
      echo '<div class="password">';
                echo '<h3>Modification De Votre Prénom</h3>';
                echo '<div class="password_data">';
                     echo '<div class="data">';
             echo '<form method="POST" action="">';
               echo'<font color="red">'. $erreurP.'</font><br><br>';
                          echo '<label>Nouveau prénom : </label>';
                echo '<input type="text" id="pass" name="prenom" title="Nouveau prénom"><br><br>';

                echo '<input type="submit" name="changePrenom" title="Envoyer prénom">';
        echo '</form>';
                     echo '</div>';
               echo  '</div>';
            echo '</div>';

      // Supp Compte
          echo '<div class="infotwo">';
                echo '<h3>Supprimer votre compte</h3>';
                echo '<div class="infotwo_data">';
                  echo '<form method="POST" action="" enctype="multipart/form-data">';
                  echo '<label>Cliquer pour supprimer votre compte. Cette action est irréversible </label>';
                  echo '<input type="submit" value="Supprimer" name="suppr" title="Cliquez pour supprimer votre compte">';
                echo '</form>';
                echo '</div>';
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