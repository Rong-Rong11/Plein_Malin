<?php
declare(strict_types=1);
/**
 * @file
 * @brief Page d'aide et de questions frequentes.
 *
 * Page d'aide.
 *
 * Elle explique le parcours de recherche, la lecture des resultats et les
 * questions frequentes du site.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$titrePage = "Aide - Plein Malin";
$descriptionPage = "Mode d'emploi et questions fréquentes du site Plein Malin.";
$pageActive = "aide";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Aide et mode d'emploi.";

require __DIR__ . "/includes/header.php";
?>
<main class="page info-page">
	<section class="panel">
		<p class="eyebrow"><?= texte_securise("Aide") ?></p>
		<h1><?= texte_securise("Mode d'emploi") ?></h1>
		<p class="lead">
			<?= texte_securise("Cette page explique rapidement comment utiliser Plein Malin et comment lire les résultats affichés.") ?>
		</p>
		<section class="info-block">
			<h2><?= texte_securise("Faire une recherche") ?></h2>
		<ol class="liste-simple">
			<li><?= texte_securise("Cliquez sur une région dans la carte interactive.") ?></li>
			<li><?= texte_securise("Choisissez un département dans la liste.") ?></li>
			<li><?= texte_securise("Choisissez une ville ou cochez le mode tout le département.") ?></li>
			<li><?= texte_securise("Sélectionnez un ou plusieurs carburants.") ?></li>
			<li><?= texte_securise("Lancez la recherche pour afficher les stations.") ?></li>
		</ol>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Autour de moi") ?></h2>
		<p class="small-note">
			<?= texte_securise("Le bouton Autour de moi utilise une position estimée à partir de l'adresse IP.") ?>
			<?= texte_securise("Cette localisation est pratique pour une recherche rapide, mais elle reste approximative.") ?>
		</p>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Lire les résultats") ?></h2>
		<ul class="liste-simple">
			<li><?= texte_securise("Le prix moyen résume les prix trouvés pour la recherche actuelle.") ?></li>
			<li><?= texte_securise("Le meilleur prix permet d'aller directement à la station correspondante.") ?></li>
			<li><?= texte_securise("Chaque station affiche son adresse, sa distance et les prix des carburants sélectionnés.") ?></li>
			<li><?= texte_securise("Le lien Voir sur une carte ouvre la station dans OpenStreetMap quand les coordonnées sont disponibles.") ?></li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Questions fréquentes") ?></h2>
		<h3><?= texte_securise("Pourquoi la position est approximative ?") ?></h3>
		<p class="small-note"><?= texte_securise("La position vient de l'adresse IP. Elle peut pointer vers une ville proche plutôt que vers l'adresse exacte.") ?></p>

		<h3><?= texte_securise("Pourquoi certains carburants n'apparaissent pas ?") ?></h3>
		<p class="small-note"><?= texte_securise("Une station ne propose pas toujours tous les carburants, ou l'API ne fournit pas toujours un prix à jour pour chaque carburant.") ?></p>

		<h3><?= texte_securise("D'où viennent les prix ?") ?></h3>
		<p class="small-note"><?= texte_securise("Les prix viennent de l'API officielle des prix des carburants et sont traités côté serveur en PHP.") ?></p>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
