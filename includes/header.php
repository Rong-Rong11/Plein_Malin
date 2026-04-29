<?php
$pageTitle = $pageTitle ?? "Plein Malin";
$pageDescription = $pageDescription ?? "Site Plein Malin";
$activePage = $activePage ?? "";
$langue = gerer_langue();
$lienRechercheNavigation = lien_recherche_memorisee();
$lienResultatsNavigation = lien_resultats_memorises();
enregistrer_visite_page();
ob_start(static function (string $html) use ($langue): string {
	return traduire_interface($html, $langue);
});
?>
<!DOCTYPE html>
	<html lang="<?= texte_securise($langue) ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= texte_securise($pageTitle) ?></title>
		<meta name="description" content="<?= texte_securise($pageDescription) ?>">
		<link rel="stylesheet" href="style.css">
			<link rel="icon" href="image/favicon.svg" type="image/svg+xml">
			<link rel="alternate icon" href="image/favicon.ico" type="image/x-icon">
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
						<a href="<?= texte_securise(lien_bascule_langue($langue)) ?>" class="theme-link">
							<span><?= texte_securise(libelle_bascule_langue($langue)) ?></span>
						</a>
					</div>
				</div>

			<nav class="main-nav">
				<a href="<?= texte_securise($lienRechercheNavigation) ?>"<?= $activePage === "recherche" ? ' aria-current="page"' : "" ?>>Recherche</a>
				<a href="<?= texte_securise($lienResultatsNavigation) ?>"<?= $activePage === "resultats" ? ' aria-current="page"' : "" ?>>Résultats</a>
				<a href="stats.php"<?= $activePage === "stats" ? ' aria-current="page"' : "" ?>>Statistiques</a>
			</nav>
		</header>
