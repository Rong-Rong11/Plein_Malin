<?php
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$geoData = recuperer_geolocalisation();
$stats = calculer_statistiques();
$stationsXml = lire_stations_xml_demo();

$url = "https://ghibliapi.vercel.app/films";
$json = @file_get_contents($url);
$data = $json !== false ? json_decode($json, true) : [];
$film = is_array($data) && $data !== [] ? $data[array_rand($data)] : null;

$pageTitle = "Page tech - Plein Malin";
$pageDescription = "Page technique conservee pour la validation de la partie 1 du projet.";
$activePage = "tech";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Page technique conservee pour validation.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel">
		<h1>Page tech</h1>
		<p class="lead">
			Cette page reste accessible depuis le footer pour montrer l'avancement initial
			et la logique technique reutilisee dans Plein Malin.
		</p>
		</section>

		<section class="panel">
			<h2>Synthese technique du projet</h2>
			<ul class="plain-list">
				<li><strong>JSON</strong> : geolocalisation IP et API officielle des prix carburants, traitees cote serveur en PHP.</li>
				<li><strong>XML</strong> : lecture de <code>data/sample_fuel_prices.xml</code> et archive annuelle officielle pour les tendances de prix.</li>
				<li><strong>CSV</strong> : regions, departements, villes, consultations et visites de pages.</li>
				<li><strong>Cookies</strong> : theme jour/nuit, langue, derniere recherche et derniere ville consultee.</li>
				<li><strong>Statistiques</strong> : tops des villes, departements, regions, carburants, modes de recherche et tendances de prix.</li>
			</ul>
		</section>

		<section class="panel-grid">
			<article class="panel">
				<h2>API Ghibli</h2>
			<?php if ($film === null): ?>
				<p class="empty-state">API Ghibli indisponible pour le moment.</p>
			<?php else: ?>
				<h3>
					<?= texte_securise((string) $film['title']) ?>
				</h3>
				<h3 lang="jp">
					<?= texte_securise((string) $film['original_title']) ?>
				</h3>
				<p>
					<?= texte_securise((string) $film['release_date']) ?>
				</p>
				<p lang="en">
					<?= texte_securise((string) $film['description']) ?>
				</p>
				<img src="<?= texte_securise((string) $film['image']) ?>" width="200" alt="">
				<img src="<?= texte_securise((string) $film['movie_banner']) ?>" width="400" alt="">
			<?php endif; ?>
			</article>

			<article class="panel">
				<h2>Flux XML carburants</h2>
				<p>Lecture de <code>data/sample_fuel_prices.xml</code> avec <code>simplexml_load_file()</code>.</p>
				<?php if ($stationsXml === []): ?>
					<p class="empty-state">Aucune donnee XML disponible.</p>
				<?php else: ?>
					<ul class="plain-list">
						<?php foreach (array_slice($stationsXml, 0, 5) as $station): ?>
							<li>
								<strong><?= texte_securise($station["enseigne"]) ?></strong>
								- <?= texte_securise($station["ville"]) ?>
								(<?= texte_securise($station["cp"]) ?>)
								<?php if ($station["prix"] !== []): ?>
									:
									<?php foreach ($station["prix"] as $indexPrix => $prix): ?>
										<?= $indexPrix > 0 ? ", " : "" ?><?= texte_securise($prix["nom"]) ?>
										<?= texte_securise($prix["valeur"]) ?> EUR/L
									<?php endforeach; ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</article>

			<article class="panel">
				<h2>Flux JSON cote serveur</h2>
			<p>Geolocalisation IP approx. obtenue en PHP avec cache fichier JSON.</p>
			<ul class="plain-list">
				<li>IP detectee: <?= texte_securise($geoData['ip']) ?></li>
				<li>Ville retournee: <?= texte_securise($geoData['city']) ?></li>
				<li>Region retournee: <?= texte_securise($geoData['region']) ?></li>
				<li>Latitude: <?= texte_securise((string) $geoData['latitude']) ?></li>
				<li>Longitude: <?= texte_securise((string) $geoData['longitude']) ?></li>
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
					<li>Cookie <code>last_search_params</code>: derniere recherche complete</li>
					<li>Cookie <code>theme</code>: jour ou nuit</li>
					<li>Cookie <code>lang</code>: langue d'affichage</li>
					<li>Cache JSON: reponses externes et fallback sur cache expire</li>
				</ul>
			</article>

		<article class="panel">
			<h2>Etat des statistiques</h2>
			<ul class="plain-list">
					<li>Consultations enregistrees: <?= texte_securise((string) $stats['consultation_count']) ?></li>
					<li>Visites de pages: <?= texte_securise((string) $stats['page_visit_count']) ?></li>
					<li>Visiteurs approx.: <?= texte_securise((string) $stats['page_visitor_count']) ?></li>
					<li>Nombre de villes dans le top: <?= texte_securise((string) count($stats['top_cities'])) ?></li>
				</ul>
			</article>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
