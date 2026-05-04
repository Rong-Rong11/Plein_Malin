<?php
/**
 * @file
 * @brief Plan du site.
 *
 * Page du plan du site.
 *
 * Elle regroupe les principales pages accessibles et les pages d'information.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$titrePage = "Plan du site - Plein Malin";
$descriptionPage = "Plan du site Plein Malin.";
$pageActive = "plan-site";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Plan du site.";

$lienRecherche = lien_recherche_memorisee();
$lienResultats = lien_resultats_memorises();

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel">
		<p class="eyebrow"><?= texte_securise("Navigation") ?></p>
		<h1><?= texte_securise("Plan du site") ?></h1>
		<p class="lead">
			<?= texte_securise("Retrouvez ici les pages principales du site et les pages d'information du projet.") ?>
		</p>
	</section>

	<section class="panel">
		<h2><?= texte_securise("Schéma de navigation") ?></h2>
		<p class="small-note">
			<?= texte_securise("Le schéma se lit de haut en bas : commencez par l'accueil, puis suivez la colonne de gauche") ?>
			<?= texte_securise("pour effectuer une recherche. La colonne de droite regroupe les pages d'explication du projet.") ?>
		</p>
		<div class="sitemap-legend">
			<span><strong><?= texte_securise("Trait orange") ?></strong> : <?= texte_securise("parcours principal de l'utilisateur") ?></span>
			<span><strong><?= texte_securise("Trait gris") ?></strong> : <?= texte_securise("pages d'information") ?></span>
		</div>
		<div class="sitemap-diagram">
			<a class="sitemap-node sitemap-home" href="index.php">
				<small><?= texte_securise("Départ") ?></small>
				<strong><?= texte_securise("Accueil") ?></strong>
				<span><?= texte_securise("Page d'entrée du site") ?></span>
			</a>

			<div class="sitemap-branches">
				<div class="sitemap-group sitemap-main-path">
					<h3><?= texte_securise("Parcours principal : chercher une station") ?></h3>
					<div class="sitemap-line">
						<a class="sitemap-node" href="<?= texte_securise($lienRecherche) ?>">
							<small><?= texte_securise("Étape 1") ?></small>
							<strong><?= texte_securise("Recherche") ?></strong>
							<span><?= texte_securise("Choisir une région, un département, une ville et un carburant") ?></span>
						</a>
						<a class="sitemap-node" href="<?= texte_securise($lienResultats) ?>">
							<small><?= texte_securise("Étape 2") ?></small>
							<strong><?= texte_securise("Résultats") ?></strong>
							<span><?= texte_securise("Comparer les stations, les prix et les liens de carte") ?></span>
						</a>
						<a class="sitemap-node" href="stats.php">
							<small><?= texte_securise("Étape 3") ?></small>
							<strong><?= texte_securise("Statistiques") ?></strong>
							<span><?= texte_securise("Voir les recherches, visites et tendances de prix") ?></span>
						</a>
					</div>
				</div>

				<div class="sitemap-group">
					<h3><?= texte_securise("Pages d'explication") ?></h3>
					<div class="sitemap-line sitemap-line-info">
						<a class="sitemap-node" href="a-propos.php">
							<strong><?= texte_securise("À propos") ?></strong>
							<span><?= texte_securise("Comprendre le but du site") ?></span>
						</a>
						<a class="sitemap-node" href="aide.php">
							<strong><?= texte_securise("Aide") ?></strong>
							<span><?= texte_securise("Apprendre à utiliser la recherche et les résultats") ?></span>
						</a>
						<a class="sitemap-node" href="sources.php">
							<strong><?= texte_securise("Sources des données") ?></strong>
							<span><?= texte_securise("Voir d'où viennent les données CSV, JSON, XML et API") ?></span>
						</a>
						<a class="sitemap-node" href="confidentialite.php">
							<strong><?= texte_securise("Confidentialité") ?></strong>
							<span><?= texte_securise("Comprendre les cookies et le stockage serveur") ?></span>
						</a>
						<a class="sitemap-node" href="tech.php">
							<strong><?= texte_securise("Page technique") ?></strong>
							<span><?= texte_securise("Afficher la démonstration technique des flux") ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
