<?php

	function userConnection($userInfos) {
		$_SESSION["Connected"] = true;
		$_SESSION["id"] = $userInfos["id"];
		$_SESSION["pseudo"] = $userInfos["pseudo"];
		$_SESSION["nom"] = $userInfos["nom"];
		$_SESSION["prenom"] = $userInfos["prenom"];
		$_SESSION["email"] = $userInfos["email"];
		$_SESSION["date"] = $userInfos["dateInscription"];
		$_SESSION["nCritiques"] = $userInfos["nCritiques"];
		$_SESSION["filmPref"] = $userInfos["filmPref"];
		$_SESSION["pic"] = $userInfos["photoProfil"];
		$_SESSION["password"] = $userInfos["mdp"];
	}

	function userDeconnection() {
		$_SESSION["Connected"] = false;
		$_SESSION["id"] = NULL;
		$_SESSION["pseudo"] = NULL;
		$_SESSION["nom"] = NULL;
		$_SESSION["prenom"] = NULL;
		$_SESSION["email"] = NULL;
		$_SESSION["date"] = NULL;
		$_SESSION["nCritiques"] = NULL;
		$_SESSION["filmPref"] = NULL;
		$_SESSION["pic"] = NULL;
		session_destroy();
		header("Location: home.php");
	}

	function connectToDB() {
		$bd = new PDO("mysql:host=127.0.0.1; dbname=critifilms", "root", "");
		return $bd;
	}

	function isConnected() {
		if (isset($_SESSION["Connected"])) {
			if ($_SESSION["Connected"] == true) {
				return true;
			}
		}
	}

	function topCon() {
		echo '<li><a title="Aller vers la page principale" href="home.php">Accueil</a></li>';
	    echo '<li><a title="Voir la liste des films" href="moviepage.php?page=1">Films</a></li>';
	    echo '<li><a title="À propos de nous" href="about.php">À propos de nous</a></li>';
		if (isConnected()) {
            echo '<li><a title="Aller vers le profil" href="profil.php?id='.$_SESSION["id"].'">Profil</a></li>';
            echo '<li><a title="Se déconnecter" href="deco.php">Se déconnecter</a></li>';
        } else {
            echo '<li><a title="Se connecter" href="signin.php">Se connecter</a></li>';
            echo '<li><a title="Créer un compte" href="signup.php">Créer un compte</a></li>';
        }
	}

	function displayImage($image) {
		echo '<img alt="Image utilisateur" src="data:image/png;base64,'.base64_encode( $image ).'" width="128" height="128"/>';
	}

	function critiExists($bd, $id) {
		$request = $bd->prepare("SELECT * FROM critique WHERE userID = ? AND filmID = ?");
		$request->execute(array($_SESSION["id"], $id));
		$exists = $request->rowCount();
		if ($exists != 0) {
			return true;
		}
	}

	function critiIDs($bd, $id) {
		$ids = $bd->prepare("SELECT id FROM critique WHERE filmID = ?");
		$ids->execute(array($id));
		return $ids;
	}

	function listCritiIDs($ids) {
		if ($ids) {
			$listeIDs = $ids->fetchAll(PDO::FETCH_COLUMN);
			return $listeIDs;
		}
	}

	function nCriti($liste) {
		return sizeof($liste);
	}

	function convertDate($oldDate, $time) {
		if ($time == true) {
			return $newDate = date("d-m-Y H:i:s", strtotime($oldDate));
		} else {
			return $newDate = date("d-m-Y", strtotime($oldDate));
		}
	}
?>

