<?php
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$geoData = recuperer_geolocalisation();
$stats = calculer_statistiques();

$pageTitle = "Page tech - Plein Malin";
$pageDescription = "Page technique conservee pour la validation de la partie 1 du projet.";
$activePage = "tech";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Page technique conservee pour validation.";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
		<section class="panel">
			<p class="eyebrow">Partie 1 conservee</p>
			<h1>Page tech</h1>
			<p class="lead">
				Cette page reste accessible depuis le footer pour montrer l'avancement initial
				et la logique technique reutilisee dans Plein Malin.
			</p>
		</section>

		<section class="panel-grid">
			<article class="panel">
				<h2>Flux JSON cote serveur</h2>
				<p>Geolocalisation IP approx. obtenue en PHP avec cache fichier JSON.</p>
				<ul class="plain-list">
					<li>IP detectee: <?= texte_securise($geoData['ip']) ?></li>
					<li>Ville retournee: <?= texte_securise($geoData['city']) ?></li>
					<li>Region retournee: <?= texte_securise($geoData['region']) ?></li>
					<li>Source utilisee: <?= texte_securise($geoData['source']) ?></li>
				</ul>
			</article>

			<article class="panel">
				<h2>Flux carburants cote serveur</h2>
				<p>
					Les stations-service sont recherchees depuis l'API JSON officielle du
					gouvernement avec un filtre sur le departement et la ville.
				</p>
				<ul class="plain-list">
					<li>Requete HTTP cote serveur en PHP</li>
					<li>Reponse JSON transformee en tableaux PHP</li>
					<li>Reutilisation dans la page resultats pour les prix et services</li>
				</ul>
			</article>
		</section>

		<section class="panel-grid">
			<article class="panel">
				<h2>Stockages attendus</h2>
				<ul class="plain-list">
					<li>CSV serveur: historique des consultations</li>
					<li>Cookie <code>last_visited_city</code>: derniere ville</li>
					<li>Cookie <code>theme</code>: jour ou nuit</li>
					<li>Cache JSON: reponses externes et fallback sur cache expire</li>
				</ul>
			</article>

			<article class="panel">
				<h2>Etat des statistiques</h2>
				<ul class="plain-list">
					<li>Consultations enregistrees: <?= texte_securise((string) $stats['consultation_count']) ?></li>
					<li>Visiteurs approx.: <?= texte_securise((string) $stats['total_visitors']) ?></li>
					<li>Nombre de villes dans le top: <?= texte_securise((string) count($stats['top_cities'])) ?></li>
				</ul>
			</article>
		</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
