<?php
$pageTitle = $pageTitle ?? "Plein Malin";
$pageDescription = $pageDescription ?? "Site Plein Malin";
$activePage = $activePage ?? "";
$lienRechercheNavigation = lien_recherche_memorisee();
$lienResultatsNavigation = lien_resultats_memorises();
?>
<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= texte_securise($pageTitle) ?></title>
		<meta name="description" content="<?= texte_securise($pageDescription) ?>">
		<link rel="stylesheet" href="style.css">
		<link rel="icon" href="image/favicon.ico" type="image/x-icon">
	</head>
	<body class="theme-<?= texte_securise($theme) ?>">
		<div id="top"></div>

		<header class="site-header">
			<div class="brand-row">
				<a class="brand-link" href="index.php">
					<img class="logo" src="image/<?= $theme === "night" ? "logoblanc.svg" : "logonoir.svg" ?>" alt="Logo Plein Malin">
				</a>
				<div class="theme-switch">
					<a href="<?= texte_securise(lien_bascule_theme($theme)) ?>" class="theme-link">
						<img src="image/theme-<?= $theme === "night" ? "day" : "night" ?>.svg" alt="">
						<span>Mode <?= texte_securise(nom_theme($theme === "night" ? "day" : "night")) ?></span>
					</a>
				</div>
			</div>

				<nav class="main-nav">
					<a href="index.php"<?= $activePage === "index" ? ' aria-current="page"' : "" ?>>Accueil</a>
					<a href="<?= texte_securise($lienRechercheNavigation) ?>"<?= $activePage === "recherche" ? ' aria-current="page"' : "" ?>>Recherche</a>
					<a href="<?= texte_securise($lienResultatsNavigation) ?>"<?= $activePage === "resultats" ? ' aria-current="page"' : "" ?>>Resultats</a>
			<a href="stats.php"<?= $activePage === "stats" ? ' aria-current="page"' : "" ?>>Statistiques</a>
			<a href="tech.php"<?= $activePage === "tech" ? ' aria-current="page"' : "" ?>>Page tech</a>
		</nav>
	</header>
