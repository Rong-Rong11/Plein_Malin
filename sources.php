<?php
/**
 * @file
 * @brief Page des sources de donnees.
 *
 * Page des sources.
 *
 * Elle liste les donnees locales et les services externes exploites par le site.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$titrePage = "Sources des données - Plein Malin";
$descriptionPage = "Sources des données utilisées par Plein Malin.";
$pageActive = "sources";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Sources des données.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell info-page">
	<section class="panel">
		<p class="eyebrow"><?= texte_securise("Données") ?></p>
		<h1><?= texte_securise("Sources des données") ?></h1>
		<p class="lead">
			<?= texte_securise("Cette page résume les sources et fichiers utilisés par le site pour répondre aux consignes du projet.") ?>
		</p>
		<section class="info-block">
			<h2><?= texte_securise("Données statiques") ?></h2>
		<ul class="plain-list">
			<li><?= texte_securise("Régions, départements et villes : fichiers CSV locaux placés dans le dossier") ?> <code>data</code>.</li>
			<li><?= texte_securise("Ces fichiers servent à alimenter les listes de sélection du formulaire de recherche.") ?></li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Données dynamiques") ?></h2>
		<ul class="plain-list">
			<li><?= texte_securise("Prix des carburants : API officielle des prix des carburants, interrogée côté serveur en PHP.") ?></li>
			<li><?= texte_securise("Géolocalisation : position estimée à partir de l'adresse IP de l'utilisateur.") ?></li>
			<li><?= texte_securise("Tendances : archive annuelle XML officielle de") ?> <code>donnees.roulez-eco.fr</code>.</li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Limites des données") ?></h2>
		<ul class="plain-list">
			<li><?= texte_securise("Les prix affichés dépendent de la dernière mise à jour disponible dans l'API officielle.") ?></li>
			<li><?= texte_securise("Si l'API officielle ne répond pas, le site affiche un message d'erreur au lieu d'annoncer simplement zéro station.") ?></li>
			<li><?= texte_securise("La géolocalisation par adresse IP est approximative : elle peut indiquer une ville proche, pas une adresse exacte.") ?></li>
			<li><?= texte_securise("Le cache serveur évite de refaire trop souvent les mêmes requêtes, mais il peut afficher une réponse déjà enregistrée pendant quelques heures.") ?></li>
			<li><?= texte_securise("Certaines stations ne proposent pas tous les carburants ou ne fournissent pas toujours un prix pour chaque carburant.") ?></li>
		</ul>
		</section>

		<section class="info-block">
			<h2><?= texte_securise("Formats exploités") ?></h2>
		<ul class="plain-list">
			<li><strong>CSV</strong> : <?= texte_securise("données locales et historique des consultations.") ?></li>
			<li><strong>JSON</strong> : <?= texte_securise("réponses de l'API carburants et cache serveur.") ?></li>
			<li><strong>XML</strong> : <?= texte_securise("lecture d'un fichier de démonstration et archive annuelle des tendances.") ?></li>
			<li><strong>Cookies</strong> : <?= texte_securise("thème, langue et dernière recherche.") ?></li>
		</ul>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
