<?php
declare(strict_types=1);
/**
 * @file
 * @brief En-tete HTML commun.
 *
 * En-tete commun du site.
 *
 * Ce fichier initialise le theme, la langue et les liens de navigation memorises.
 */
$titrePage = $titrePage ?? "Plein Malin";
$descriptionPage = $descriptionPage ?? "Site Plein Malin";
$pageActive = $pageActive ?? "";
$theme = isset($theme) && in_array($theme, ["day", "night"], true) ? $theme : gerer_theme();
$langue = gerer_langue();
$lienRechercheNavigation = lien_recherche_memorisee();
$lienResultatsNavigation = lien_resultats_memorises();
enregistrer_visite_page();
?>
<!DOCTYPE html>
	<html lang="<?= texte_securise($langue) ?>">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?= texte_securise($titrePage) ?></title>
		<meta name="description" content="<?= texte_securise($descriptionPage) ?>" />
		<link rel="stylesheet" href="style.css" />
		<?php if ($theme === "night") { ?>
			<link rel="stylesheet" href="style-alt.css" />
		<?php } ?>
		<link rel="icon" href="image/favicon.svg" type="image/svg+xml" />
	</head>
	<body class="theme-<?= texte_securise($theme) ?>">
		<div id="top"></div>

		<header class="site-header">
			<div class="ligne-entete">
				<a class="brand-link" href="index.php">
					<img class="logo" src="image/<?= $theme === "night" ? "logoblanc.svg" : "logonoir.svg" ?>" alt="Logo Plein Malin" width="360" height="110" decoding="async" fetchpriority="high" />
				</a>
					<div class="options-entete">
						<a href="<?= texte_securise(lien_bascule_theme($theme)) ?>" class="theme-link">
							<img src="image/theme-<?= $theme === "night" ? "day" : "night" ?>.svg" alt="" width="24" height="24" decoding="async" />
							<span><?= texte_securise("Mode") ?> <?= texte_securise(nom_theme($theme === "night" ? "day" : "night")) ?></span>
						</a>
						<a href="<?= texte_securise(lien_bascule_langue($langue)) ?>" class="theme-link">
							<span><?= texte_securise(libelle_bascule_langue($langue)) ?></span>
						</a>
					</div>
				</div>

			<nav class="menu">
				<a href="<?= texte_securise($lienRechercheNavigation) ?>"<?= $pageActive === "recherche" ? ' aria-current="page"' : "" ?>><?= texte_securise("Recherche") ?></a>
				<a href="<?= texte_securise($lienResultatsNavigation) ?>"<?= $pageActive === "resultats" ? ' aria-current="page"' : "" ?>><?= texte_securise("Résultats") ?></a>
				<a href="stats.php"<?= $pageActive === "stats" ? ' aria-current="page"' : "" ?>><?= texte_securise("Statistiques") ?></a>
			</nav>
		</header>
