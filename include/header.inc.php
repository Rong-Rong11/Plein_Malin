<?php
/**
 * @file header.inc.php
 * @brief Gabarit commun de l'en-tete HTML du site.
 * @author BAARIR Fatma-Zahra
 * @author PHUNG Enzo
 *
 * @details Ce fichier mutualise toute la partie haute des pages :
 * declaration HTML, metadonnees, selection du theme, logo, liens
 * de changement de langue et navigation principale.
 *
 * @note Le header conserve autant que possible l'etat courant du site
 * en propagant les parametres `lang` et `style` dans les liens proposes.
 */

require_once "./include/pages.inc.php";

/* Langue de la page HTML derivee du parametre `lang`. */
$pageLang = "fr";
if (isset($_GET["lang"]) && $_GET["lang"] === "en") {
	$pageLang = "en";
}

/* Determine les options de langue et de theme a proposer dans l'en-tete. */
$alternateLang = $pageLang === "fr" ? "en" : "fr";
$alternateLangLabel = $pageLang === "fr" ? "EN" : "FR";
?>

<!DOCTYPE html>
<html lang="<?= $pageLang ?>">

	<head>
		<meta charset="utf-8" />
		<title>
			<?= $title ?>
		</title>
		<meta name="author" content="BAARIR Fatma-Zahra PHUNG Enzo" />
		<meta name="description" content="<?= $description ?>" />

		<?php
		/* Selection de la feuille de style et du logo en fonction du mode choisi. */
		$stylesheet = "./style.css";

		if (isset($_GET["style"]) && !empty($_GET["style"]) && $_GET["style"] === "alternatif") {
			$style = "alternatif";
			$stylesheet = "./style-alt.css";
			$logo = "./image/logoblanc.svg";
		} else {
			$style = "standard";
			$stylesheet = "./style.css";
			$logo = "./image/logonoir.svg";
		}

		$alternateStyle = $style === "standard" ? "alternatif" : "standard";
		$alternateStyleLabel = $style === "standard" ? "Mode alternatif" : "Mode standard";
		?>
		<link rel="stylesheet" href="<?= $stylesheet ?>" />
		<link rel="icon" href="./image/favicon.ico" type="image/png" />

		<style>
			#exo4 .chiffre {
				color:
				<?php if ($style === "alternatif") {
					echo "#ff8a80";
				} else {
					echo "#8f1f1a";
				} ?>
				;
				font-weight: bold;
			}

			#exo4 .majuscule {
				color:
				<?php if ($style === "alternatif") {
					echo "#9be79b";
				} else {
					echo "#2c6b2c";
				} ?>
				;
				font-weight: bold;
			}

			#exo4 .minuscule {
				color:
				<?php if ($style === "alternatif") {
					echo "#9fc2ff";
				} else {
					echo "#274f96";
				} ?>
				;
				font-weight: bold;
			}
		</style>
		<style>
			.clear-url {
				display: inline-block;
				padding: 8px 14px;
				margin-bottom: 12px;
				margin-left: -12px;
				background-color:
				<?php if ($style === "alternatif") {
					echo "#a8beb0";
				} else {
					echo "#718A79";
				} ?>
				;
				color:
				<?php if ($style === "alternatif") {
					echo "#152019";
				} else {
					echo "#0f1712";
				} ?>
				;
				text-decoration: none;
				border-radius: 6px;
				font-weight: bold;
				border: 1px solid
				<?php if ($style === "alternatif") {
					echo "#d7e3db";
				} else {
					echo "#5d7365";
				} ?>
				;
			}
		</style>
	</head>

	<body>
		<header>
			<a href="index.php<?= "?style=" . $style ?>">
				<img class="logo" src="<?= $logo ?>" alt="Logo de notre site" width="500" height="200" />
			</a>
			<nav class="link-nav">
				<a href="index.php?lang=<?= $alternateLang ?>&amp;style=<?= $style ?>"><?= $alternateLangLabel ?></a>
				<a href="index.php?lang=<?= $pageLang ?>&amp;style=<?= $alternateStyle ?>"><?= $alternateStyleLabel ?></a>
			</nav>
			<nav class="main-nav">
				<ul>
					<?php foreach ($pages as $page) { ?>
						<li>
							<a href="<?= $page["lien"] . "?style=" . $style ?>"><?= $page["nom"] ?></a>
						</li>
					<?php } ?>
				</ul>
			</nav>
		</header>
