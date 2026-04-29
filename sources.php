<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$pageTitle = "Sources des données - Plein Malin";
$pageDescription = "Sources des données utilisées par Plein Malin.";
$activePage = "sources";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Sources des données.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel">
		<p class="eyebrow">Données</p>
		<h1>Sources des données</h1>
		<p class="lead">
			Cette page résume les fichiers et services utilisés par le site pour répondre aux consignes du projet.
		</p>
	</section>

	<section class="panel">
		<h2>Données statiques</h2>
		<ul class="plain-list">
			<li>Régions, départements et villes : fichiers CSV locaux placés dans le dossier <code>data</code>.</li>
			<li>Ces fichiers servent à alimenter les listes de sélection du formulaire de recherche.</li>
		</ul>
	</section>

	<section class="panel">
		<h2>Données dynamiques</h2>
		<ul class="plain-list">
			<li>Prix des carburants : API officielle des prix des carburants, interrogée côté serveur en PHP.</li>
			<li>Géolocalisation : position estimée à partir de l'adresse IP de l'utilisateur.</li>
			<li>Tendances : archive annuelle XML officielle de <code>donnees.roulez-eco.fr</code>.</li>
		</ul>
	</section>

	<section class="panel">
		<h2>Limites des données</h2>
		<ul class="plain-list">
			<li>Les prix affichés dépendent de la dernière mise à jour disponible dans l'API officielle.</li>
			<li>La géolocalisation par adresse IP est approximative et ne donne pas une position exacte.</li>
			<li>Certaines stations ne proposent pas tous les carburants ou ne fournissent pas toujours un prix pour chaque carburant.</li>
		</ul>
	</section>

	<section class="panel">
		<h2>Formats exploités</h2>
		<ul class="plain-list">
			<li><strong>CSV</strong> : données locales et historique des consultations.</li>
			<li><strong>JSON</strong> : réponses de l'API carburants et cache serveur.</li>
			<li><strong>XML</strong> : lecture d'un fichier de démonstration et archive annuelle des tendances.</li>
			<li><strong>Cookies</strong> : thème, langue et dernière recherche.</li>
		</ul>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
