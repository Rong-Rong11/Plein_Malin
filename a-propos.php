<?php
/**
 * @file
 * @brief Page de presentation du projet.
 *
 * Page "A propos".
 *
 * Elle presente le projet, ses objectifs et les choix de sobriete numerique.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$titrePage = "À propos - Plein Malin";
$descriptionPage = "Présentation du projet Plein Malin.";
$pageActive = "a-propos";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | À propos du projet Plein Malin.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-conteneur page-info">
	<section class="panneau">
		<p class="surtitre"><?= texte_securise("À propos") ?></p>
		<h1><?= texte_securise("À propos de Plein Malin") ?></h1>
		<p class="accroche">
			<?= texte_securise("Plein Malin est un site réalisé dans le cadre du projet de développement web.") ?>
			<?= texte_securise("Il permet de rechercher des stations-service en France et de comparer les prix des carburants.") ?>
		</p>
		<section class="bloc-info">
			<h2><?= texte_securise("Objectif du site") ?></h2>
		<ul class="liste-simple">
			<li><?= texte_securise("Choisir une région depuis une carte interactive.") ?></li>
			<li><?= texte_securise("Sélectionner un département et une ville.") ?></li>
			<li><?= texte_securise("Afficher les stations disponibles et les prix des carburants.") ?></li>
			<li><?= texte_securise("Consulter des statistiques sur les recherches effectuées.") ?></li>
		</ul>
		</section>

		<section class="bloc-info">
			<h2><?= texte_securise("Fonctionnalités principales") ?></h2>
		<ul class="liste-simple">
			<li><?= texte_securise("Recherche par ville, par département ou autour d'une position estimée.") ?></li>
			<li><?= texte_securise("Choix de plusieurs carburants.") ?></li>
			<li><?= texte_securise("Mode jour/nuit mémorisé avec un cookie.") ?></li>
			<li><?= texte_securise("Affichage en français ou en anglais.") ?></li>
			<li><?= texte_securise("Stockage serveur des consultations dans un fichier CSV.") ?></li>
		</ul>
		</section>

		<section class="bloc-info">
			<h2><?= texte_securise("Sobriété numérique") ?></h2>
		<ul class="liste-simple">
			<li><?= texte_securise("Les images de la carte et du bouton retour en haut sont compressées pour réduire le poids des pages.") ?></li>
			<li><?= texte_securise("Les réponses des API sont mises en cache côté serveur afin d'éviter des requêtes répétées.") ?></li>
			<li><?= texte_securise("La page de résultats affiche uniquement les premières stations utiles pour limiter la quantité de contenu chargé.") ?></li>
			<li><?= texte_securise("Le site utilise très peu de JavaScript et privilégie des fichiers simples comme CSV, JSON et XML.") ?></li>
		</ul>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
