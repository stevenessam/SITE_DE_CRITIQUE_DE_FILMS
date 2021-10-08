<?php
session_start();
	
include("PHPFunc/utilitaire.php");


	if (isset($_SESSION["Connected"])) {
		if ($_SESSION["Connected"] == true) {
			header("Location: home.php");
		}
	}


	$bd = connectToDB();

	if (isset($_POST["ValidateCon"])) {
		$pseudoCon = htmlspecialchars($_POST["pseudo"]);
		$passCon = sha1($_POST["password"]);
		if (!empty($pseudoCon) && !empty($passCon)) {
			$reqUser = $bd->prepare("SELECT * FROM utilisateur WHERE pseudo = ? AND mdp = ?");
			$reqUser->execute(array($pseudoCon, $passCon));
			$userExists = $reqUser->rowCount();
			if ($userExists == 1) {
				
				$userInfos = $reqUser->fetch();
				userConnection($userInfos);
				header("Location: profil.php?id=".$_SESSION["id"]);

			} else {
				$erreur = "Ces identifiants n'existent pas";
			}
		} else {
			$erreur = "Tous les champs ne sont pas remplis !";
		}
	}
?>


<!DOCTYPE html>
<html lang="FR">
<head>
	<title>SignIn</title>
	<link rel="shortcut icon" type="image/png" href="logo/logo.png"/>
	<link rel="stylesheet" type="text/css" href="CSS/signin.css?v=<?php echo time(); ?>">
	<link rel="stylesheet" href="login.css">
</head>
<body>
 <div class="sign-up-form">
	<img src="logo/usericon.png" alt="Icon">
		<h1> Sign in</h1>
	<form method="POST" >
	<input  type="text" class="input-box" placeholder="Pseudo" name="pseudo" title="Entrez votre pseudo" required>
	<input type="password" class="input-box" placeholder="Your Password" name="password" title="Entrez votre mot de passe" required>
		<input type="submit" class="signup-btn" value="Se connecter" name="ValidateCon" title="Cliquez pour vous connecter">
		<br>
		<?php if (isset($erreur)) {echo "<font color='red'>".$erreur."</font>"; } ?>
		<hr>
		<p>Vous n'avez pas de compte ?<a href="signup.php">S'inscrire</a></p>
	</form>

 </div>

</body>
</html>