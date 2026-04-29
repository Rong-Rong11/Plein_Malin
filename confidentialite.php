<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$pageTitle = "Confidentialité - Plein Malin";
$pageDescription = "Informations sur les cookies et les données conservées par Plein Malin.";
$activePage = "confidentialite";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Confidentialité.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel">
		<p class="eyebrow">Confidentialité</p>
		<h1>Confidentialité</h1>
		<p class="lead">
			Le site utilise quelques mécanismes de mémorisation pour améliorer l'expérience utilisateur
			et produire les statistiques demandées dans le projet.
		</p>
	</section>

	<section class="panel">
		<h2>Cookies utilisés</h2>
		<ul class="plain-list">
			<li><code>theme</code> : mémorise le choix du mode jour ou nuit.</li>
			<li><code>lang</code> : mémorise la langue d'affichage.</li>
			<li><code>last_visited_city</code> : mémorise la dernière ville consultée.</li>
			<li><code>last_search</code> et <code>last_search_params</code> : mémorisent la dernière recherche.</li>
		</ul>
	</section>

	<section class="panel">
		<h2>Données stockées côté serveur</h2>
		<ul class="plain-list">
			<li>Les consultations sont enregistrées dans <code>storage/consultations.csv</code>.</li>
			<li>Les visites de pages sont enregistrées dans <code>storage/page_visits.csv</code>.</li>
			<li>Chaque ligne contient un horodatage pour permettre les statistiques.</li>
		</ul>
	</section>

	<section class="panel">
		<h2>Géolocalisation</h2>
		<p class="small-note">
			La recherche autour de moi utilise une position estimée à partir de l'adresse IP.
			Cette position est approximative et peut être différente de la position réelle de l'utilisateur.
		</p>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
