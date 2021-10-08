<?php
session_start();
include("PHPFunc/utilitaire.php");

$db = connectToDB();

if (isConnected()) {
	header("Location: home.php");
}

if (isset($_POST["validate"])) {


	if (!empty($_POST["pseudo"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["pass2"])) {

		$PSEUDO = htmlspecialchars($_POST["pseudo"]);
		$FNAME = htmlspecialchars($_POST["fName"]);
		$LNAME = htmlspecialchars($_POST["lName"]);
		$EMAIL = htmlspecialchars($_POST["email"]);
		$PASSW = sha1($_POST["password"]);
		$PASSW2 = sha1($_POST["pass2"]);
		
		if (empty($_POST["fName"])) {
			$FNAME = "-";
		}
		if (empty($_POST["lName"])) {
			$LNAME = "-";
		}
		$pseudolength = strlen($PSEUDO);
		if ($pseudolength > 255) {
			$erreur = "Le pseudo ne doit pas dépasser 255 caractères";
		} else {
			$reqPseudo = $db->prepare("SELECT * FROM utilisateur WHERE pseudo= ?");
			$reqPseudo->execute(array($PSEUDO));
			$pseudoExists = $reqPseudo->rowCount();
			if ($pseudoExists == 0) {
				$reqMail = $db->prepare("SELECT * FROM utilisateur WHERE email= ?");
				$reqMail->execute(array($EMAIL));
				$mailExists = $reqMail->rowCount();
				if ($mailExists == 0 ) {
					if ($PASSW != $PASSW2) {
						$erreur = "Les mots de passe doivent être identiques";
					} else {
						if (filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) {
							$insertmbr = $db->prepare("INSERT INTO utilisateur (pseudo, nom, prenom, dateInscription, email, mdp) VALUES (?, ?, ? ,?, ?, ?)");
							$insertmbr->execute(array($PSEUDO, $LNAME, $FNAME,  date("y-m-d"), $EMAIL, $PASSW));

							$reqUser = $db->prepare("SELECT * FROM utilisateur WHERE pseudo = ? AND mdp = ?");
							$reqUser->execute(array($PSEUDO, $PASSW));
							$userInfos = $reqUser->fetch();
							userConnection($userInfos);
							$iD = $_SESSION["id"];
							header("Location: profil.php?id=$iD");
						} else {
							$erreur = "L'adresse mail n'est pas valide !";
						}
					}
				} else {
					$erreur = "Cette adresse mail est déjà utilisée";
				}
			} else {
				$erreur = "Ce pseudo existe déjà";
			}
		}
	} else {
		$erreur = "Tous les champs doivent être complétés !";
	}
}

?>
<!DOCTYPE html>
<html lang="FR">
<head>
	<meta charset="utf-8" />
	<title>SignUp</title>
	<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
	<link rel="stylesheet" type="text/css" href="CSS/signup.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="login.css">
</head>
<body>
 <div class="sign-up-form">
	<img src="logo/usericon.png" alt="Icon">
		<h1> Sign Up Now</h1>
		<font color="red">* : obligatoire</font>
	<form method="POST" >
		<input type="text" class="input-box" placeholder="First Name" name="fName" title="Entrez votre prénom (optionnel)" value="<?php if (isset($FNAME)) {echo $FNAME;} ?>">
		<input type="text" class="input-box" placeholder="Last Name" name="lName" title="Entrez votre nom (optionnel)" value="<?php if (isset($LNAME)) {echo $LNAME;} ?>">
		<input type="text" class="input-box" placeholder="User Name" name="pseudo" title="Entrez un pseudo (obligatoire)" value="<?php if (isset($PSEUDO)) {echo $PSEUDO;} ?>">
		<span class="error">*</span>
		<input type="email" class="input-box" placeholder="Your Email" name="email" title="Entrez votre email (obligatoire)" value="<?php if (isset($EMAIL)) {echo $EMAIL;} ?>">
		<span class="error">*</span>
		<input type="password" class="input-box" placeholder="Your Password" name="password" title="Entrez un mot de passe (obligatoire)">
		<span class="error">*</span>
		<input type="password" class="input-box" placeholder="Confimez le mot de passe" name="pass2" title="Répétez le mot de passe (obligatoire)">
		<span class="error">*</span>
	
		<p><span><input type="checkbox" required></span>I agree to the terms of services</p>
			<input type="submit" value="S'inscrire" class="signup-btn" name="validate" title="Créer un compte">
			<br>
			<?php
			if (isset($erreur)) {
				echo'<font color="red">'. $erreur.'</font>';
			}
			?>
				<hr>
			<p>Vous avez déjà un compte ?<a href="signin.php">Se connecter</a></p>
	</form>

 </div>




</body>
</html>