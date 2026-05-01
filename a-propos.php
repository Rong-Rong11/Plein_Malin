<?php
/**
 * Page "A propos".
 *
 * Elle presente le projet, ses objectifs et les choix de sobriete numerique.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$pageTitle = "À propos - Plein Malin";
$pageDescription = "Présentation du projet Plein Malin.";
$activePage = "a-propos";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | À propos du projet Plein Malin.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell info-page">
	<section class="panel">
		<p class="eyebrow">À propos</p>
		<h1>À propos de Plein Malin</h1>
		<p class="lead">
			Plein Malin est un site réalisé dans le cadre du projet de développement web.
			Il permet de rechercher des stations-service en France et de comparer les prix des carburants.
		</p>
		<section class="info-block">
			<h2>Objectif du site</h2>
		<ul class="plain-list">
			<li>Choisir une région depuis une carte interactive.</li>
			<li>Sélectionner un département et une ville.</li>
			<li>Afficher les stations disponibles et les prix des carburants.</li>
			<li>Consulter des statistiques sur les recherches effectuées.</li>
		</ul>
		</section>

		<section class="info-block">
			<h2>Fonctionnalités principales</h2>
		<ul class="plain-list">
			<li>Recherche par ville, par département ou autour d'une position estimée.</li>
			<li>Choix de plusieurs carburants.</li>
			<li>Mode jour/nuit mémorisé avec un cookie.</li>
			<li>Affichage en français ou en anglais.</li>
			<li>Stockage serveur des consultations dans un fichier CSV.</li>
		</ul>
		</section>

		<section class="info-block">
			<h2>Sobriété numérique</h2>
		<ul class="plain-list">
			<li>Les images de la carte et du bouton retour en haut sont compressées pour réduire le poids des pages.</li>
			<li>Les réponses des API sont mises en cache côté serveur afin d'éviter des requêtes répétées.</li>
			<li>La page de résultats affiche uniquement les premières stations utiles pour limiter la quantité de contenu chargé.</li>
			<li>Le site utilise très peu de JavaScript et privilégie des fichiers simples comme CSV, JSON et XML.</li>
		</ul>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
