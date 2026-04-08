<?php
$titre = "Projet Web - Page technique";
$description = "Page technique du projet web.";

if (isset($_GET["style"]) && $_GET["style"] === "alternatif") { //verifie qu'il ya bien style dans url et si c bien le style alternative 
	$style = "alternatif";
	$feuille = "style-alt.css";
	$logo = "image/logoblanc.svg";
	$styleLabel = "Mode standard";
	$styleSuivant = "standard";
} else {
	$style = "standard";
	$feuille = "style.css";
	$logo = "image/logonoir.svg";
	$styleLabel = "Mode alternatif";
	$styleSuivant = "alternatif";
}

$jsonFilms = @file_get_contents("https://ghibliapi.vercel.app/films");
$films = json_decode($jsonFilms, true);

$film = null;
if (is_array($films) && count($films) > 0) {
	$indice = array_rand($films);
	$film = $films[$indice];
}

$ip = $_SERVER["REMOTE_ADDR"] ?? "";
if ($ip == "127.0.0.1" || $ip == "::1") {
	$ip = "193.54.115.192";
}

$jsonGeo = @file_get_contents("https://ipinfo.io/" . $ip . "/geo");
$geo = json_decode($jsonGeo, true);
?>
<!DOCTYPE html>
<html lang="fr">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $titre ?></title>
		<meta name="author" content="Enzo Phung">
		<meta name="description" content="<?= $description ?>">
		<link rel="stylesheet" href="<?= $feuille ?>">
		<link rel="icon" href="image/favicon.ico" type="image/x-icon">
	</head>

	<body>
		<div id="haut-page"></div>

		<header>
			<a href="index.php">
				<img class="logo" src="<?= $logo ?>" alt="Logo du projet" width="500" height="200">
			</a>
			<nav class="main-nav">
				<ul>
					<li><a href="tech.php?style=<?= $styleSuivant ?>"><?= $styleLabel ?></a></li>
				</ul>
			</nav>
		</header>

		<main>
			<h1>Page technique</h1>
			<p class="subtitle">Exemples d'utilisation d'API JSON en PHP</p>

			<section>
				<h2>API Ghibli</h2>
				<p>
					On récupère ici une liste de films au format JSON grâce à une API,
					puis on choisit un film aléatoire dans le tableau obtenu avec PHP.
				</p>

				<?php if ($film != null) { ?>
					<p><strong>Titre :</strong> <?= htmlspecialchars($film["title"]) ?></p>
					<p><strong>Titre original :</strong> <?= htmlspecialchars($film["original_title"]) ?></p>
					<p><strong>Année :</strong> <?= htmlspecialchars($film["release_date"]) ?></p>
					<p><strong>Description :</strong> <?= htmlspecialchars($film["description"]) ?></p>

					<div class="images">
						<figure>
							<img src="<?= htmlspecialchars($film["image"]) ?>" alt="Affiche du film">
							<figcaption>Affiche du film</figcaption>
						</figure>

						<figure>
							<img src="<?= htmlspecialchars($film["movie_banner"]) ?>" alt="Bannière du film">
							<figcaption>Bannière du film</figcaption>
						</figure>
					</div>
				<?php } else { ?>
					<p>Impossible de récupérer les données de l'API Ghibli.</p>
				<?php } ?>
			</section>

			<section>
				<h2>API de géolocalisation IP</h2>
				<p>
					On récupère d'abord l'adresse IP du visiteur, puis on interroge une
					seconde API JSON pour obtenir des informations de localisation.
				</p>

				<p><strong>Adresse IP :</strong> <?= htmlspecialchars($ip) ?></p>

				<?php if (is_array($geo)) { ?>
					<p><strong>Ville :</strong> <?= htmlspecialchars($geo["city"] ?? "Inconnue") ?></p>
					<p><strong>Région :</strong> <?= htmlspecialchars($geo["region"] ?? "Inconnue") ?></p>
					<p><strong>Pays :</strong> <?= htmlspecialchars($geo["country"] ?? "Inconnu") ?></p>
					<p><strong>Coordonnées :</strong> <?= htmlspecialchars($geo["loc"] ?? "Inconnues") ?></p>
				<?php } else { ?>
					<p>Impossible de récupérer les données de géolocalisation.</p>
				<?php } ?>
			</section>
		</main>

		<footer>
			<span style="text-align: right;">
				<a href="#haut-page" class="back-top">
					<img src="image/back_top.png" alt="Retour en haut">
				</a>
			</span>
			<div class="footer-info">
				<span><em>Enzo Phung</em></span>
				<span><em>CY Cergy Paris Université - ©2025-2026</em></span>
				<span><em>Projet Web - Avancement 1</em></span>
			</div>
			<div class="footer-links">
				<span class="link-btn">
					<a href="index.php?style=<?= $style ?>">Retour à l'accueil</a>
				</span>
				<span class="link-btn">
					<a href="tech.php?style=<?= $style ?>">Page technique</a>
				</span>
			</div>
		</footer>
	</body>

</html>