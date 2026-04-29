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
		<p class="small-note">
			Le schéma se lit de haut en bas : commencez par l'accueil, puis suivez la colonne de gauche
			pour effectuer une recherche. La colonne de droite regroupe les pages d'explication du projet.
		</p>
		<div class="sitemap-legend">
			<span><strong>Trait orange</strong> : parcours principal de l'utilisateur</span>
			<span><strong>Trait gris</strong> : pages d'information</span>
		</div>
		<div class="sitemap-diagram">
			<a class="sitemap-node sitemap-home" href="index.php">
				<small>Départ</small>
				<strong>Accueil</strong>
				<span>Page d'entrée du site</span>
			</a>

			<div class="sitemap-branches">
				<div class="sitemap-group sitemap-main-path">
					<h3>Parcours principal : chercher une station</h3>
					<div class="sitemap-line">
						<a class="sitemap-node" href="<?= texte_securise($lienRecherche) ?>">
							<small>Étape 1</small>
							<strong>Recherche</strong>
							<span>Choisir une région, un département, une ville et un carburant</span>
						</a>
						<a class="sitemap-node" href="<?= texte_securise($lienResultats) ?>">
							<small>Étape 2</small>
							<strong>Résultats</strong>
							<span>Comparer les stations, les prix, les services et la carte</span>
						</a>
						<a class="sitemap-node" href="stats.php">
							<small>Étape 3</small>
							<strong>Statistiques</strong>
							<span>Voir les recherches, visites et tendances de prix</span>
						</a>
					</div>
				</div>

				<div class="sitemap-group">
					<h3>Pages d'explication</h3>
					<div class="sitemap-line sitemap-line-info">
						<a class="sitemap-node" href="a-propos.php">
							<strong>À propos</strong>
							<span>Comprendre le but du site</span>
						</a>
						<a class="sitemap-node" href="aide.php">
							<strong>Aide</strong>
							<span>Apprendre à utiliser la recherche et les résultats</span>
						</a>
						<a class="sitemap-node" href="sources.php">
							<strong>Sources des données</strong>
							<span>Voir d'où viennent les données CSV, JSON, XML et API</span>
						</a>
						<a class="sitemap-node" href="confidentialite.php">
							<strong>Confidentialité</strong>
							<span>Comprendre les cookies et le stockage serveur</span>
						</a>
						<a class="sitemap-node" href="tech.php">
							<strong>Page technique</strong>
							<span>Afficher la démonstration technique des flux</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
