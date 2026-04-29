<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$pageTitle = "Plan du site - Plein Malin";
$pageDescription = "Plan du site Plein Malin.";
$activePage = "plan-site";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Plan du site.";

$lienRecherche = lien_recherche_memorisee();
$lienResultats = lien_resultats_memorises();

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel">
		<p class="eyebrow">Navigation</p>
		<h1>Plan du site</h1>
		<p class="lead">
			Retrouvez ici les pages principales du site et les pages d'information du projet.
		</p>
	</section>

	<section class="panel">
		<h2>Schéma de navigation</h2>
		<div class="sitemap-diagram">
			<a class="sitemap-node sitemap-home" href="index.php">
				<strong>Accueil</strong>
				<span>Entrée du site</span>
			</a>

			<div class="sitemap-branches">
				<div class="sitemap-group">
					<h3>Parcours utilisateur</h3>
					<div class="sitemap-line">
						<a class="sitemap-node" href="<?= texte_securise($lienRecherche) ?>">
							<strong>Recherche</strong>
							<span>Choix région, département, ville</span>
						</a>
						<a class="sitemap-node" href="<?= texte_securise($lienResultats) ?>">
							<strong>Résultats</strong>
							<span>Stations, prix et détails</span>
						</a>
						<a class="sitemap-node" href="stats.php">
							<strong>Statistiques</strong>
							<span>Consultations et tendances</span>
						</a>
					</div>
				</div>

				<div class="sitemap-group">
					<h3>Informations du projet</h3>
					<div class="sitemap-line sitemap-line-info">
						<a class="sitemap-node" href="a-propos.php">
							<strong>À propos</strong>
							<span>Présentation du projet</span>
						</a>
						<a class="sitemap-node" href="aide.php">
							<strong>Aide</strong>
							<span>Mode d'emploi et FAQ</span>
						</a>
						<a class="sitemap-node" href="sources.php">
							<strong>Sources des données</strong>
							<span>CSV, JSON, XML et API</span>
						</a>
						<a class="sitemap-node" href="confidentialite.php">
							<strong>Confidentialité</strong>
							<span>Cookies et stockage</span>
						</a>
						<a class="sitemap-node" href="tech.php">
							<strong>Page technique</strong>
							<span>Démonstration des flux</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
