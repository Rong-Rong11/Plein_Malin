<?php
/**
 * @file
 * @brief Page de confidentialite et de donnees conservees.
 *
 * Page confidentialite.
 *
 * Elle decrit les cookies utilises et les donnees conservees dans les fichiers
 * CSV du projet.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$titrePage = "Confidentialité - Plein Malin";
$descriptionPage = "Informations sur les cookies et les données conservées par Plein Malin.";
$pageActive = "confidentialite";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Confidentialité.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell info-page">
	<section class="panel">
		<p class="eyebrow"><?= texte_securise("Confidentialité") ?></p>
		<h1><?= texte_securise("Confidentialité") ?></h1>
		<p class="lead">
			<?= texte_securise("Le site utilise quelques mécanismes de mémorisation pour améliorer l'expérience utilisateur") ?>
			<?= texte_securise("et produire les statistiques demandées dans le projet.") ?>
		</p>
		<section class="info-block">
			<h2><?= texte_securise("Cookies utilisés") ?></h2>
		<ul class="plain-list">
			<li><code>theme</code> : <?= texte_securise("mémorise le choix du mode jour ou nuit.") ?></li>
			<li><code>lang</code> : <?= texte_securise("mémorise la langue d'affichage.") ?></li>
			<li><code>last_visited_city</code> : <?= texte_securise("mémorise la dernière ville consultée.") ?></li>
			<li><?= traduire_texte("<code>last_search</code> et <code>last_search_params</code> : mémorisent la dernière recherche.") ?></li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Données stockées côté serveur") ?></h2>
		<ul class="plain-list">
			<li><?= texte_securise("Les consultations sont enregistrées dans") ?> <code>storage/consultations.csv</code>.</li>
			<li><?= texte_securise("Les visites de pages sont enregistrées dans") ?> <code>storage/page_visits.csv</code>.</li>
			<li><?= texte_securise("Chaque ligne contient un horodatage pour permettre les statistiques.") ?></li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Géolocalisation") ?></h2>
		<p class="small-note">
			<?= texte_securise("La recherche autour de moi utilise une position estimée à partir de l'adresse IP.") ?>
			<?= texte_securise("Cette position est approximative et peut être différente de la position réelle de l'utilisateur.") ?>
		</p>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
