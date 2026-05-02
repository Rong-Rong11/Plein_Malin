<?php
/**
 * @file
 * @brief Page technique de demonstration des formats exploites.
 *
 * Page technique.
 *
 * Elle montre les formats et services manipules par le projet : JSON, XML,
 * CSV, cookies, cache et API externes, ainsi qu'un exemple d'images annotees
 * avec legendes pour l'API Ghibli.
 */
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$donneesGeo = recuperer_geolocalisation();
$statistiques = calculer_statistiques();
$stationsXml = lire_stations_xml_demo();

// Appel de demonstration JSON conserve pour montrer l'exploitation d'une API.
$adresseUrl = "https://ghibliapi.vercel.app/films";
$json = @file_get_contents($adresseUrl);
$donnees = $json !== false ? json_decode($json, true) : [];
$film = is_array($donnees) && $donnees !== [] ? $donnees[array_rand($donnees)] : null;

$titrePage = "Page tech - Plein Malin";
$descriptionPage = "Page technique conservee pour la validation de la partie 1 du projet.";
$pageActive = "tech";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Page technique conservee pour validation.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell tech-page info-page">
	<section class="panel tech-hero">
		<h1>Page tech</h1>
		<p class="lead">
			Cette page reste accessible depuis le footer pour montrer l'avancement initial
			et la logique technique reutilisee dans Plein Malin.
		</p>
		<section class="info-block tech-summary">
			<h2>Synthese technique du projet</h2>
			<ul class="plain-list">
				<li><strong>JSON</strong> : geolocalisation IP et API officielle des prix carburants, traitees cote serveur en PHP.</li>
				<li><strong>XML</strong> : lecture de <code>data/sample_fuel_prices.xml</code> et archive annuelle officielle pour les tendances de prix.</li>
				<li><strong>CSV</strong> : regions, departements, villes, consultations et visites de pages.</li>
				<li><strong>Cookies</strong> : theme jour/nuit, langue, derniere recherche et derniere ville consultee.</li>
				<li><strong>Statistiques</strong> : tops des villes, departements, regions, carburants, modes de recherche et tendances de prix.</li>
			</ul>
		</section>

		<section class="info-block tech-feature">
				<h2>API Ghibli</h2>
			<?php if ($film === null) { ?>
				<p class="empty-state">API Ghibli indisponible pour le moment.</p>
			<?php } else { ?>
				<h3 class="tech-subtitle">
					<?= texte_securise((string) $film['title']) ?>
				</h3>
				<h3 class="tech-original-title" lang="ja">
					<?= texte_securise((string) $film['original_title']) ?>
				</h3>
				<p class="meta-line">
					<?= texte_securise((string) $film['release_date']) ?>
				</p>
				<p class="tech-copy" lang="en">
					<?= texte_securise((string) $film['description']) ?>
				</p>
				<div class="tech-media">
					<figure class="tech-figure">
						<img
							src="<?= texte_securise((string) $film['image']) ?>"
							width="200"
							alt="Affiche du film <?= texte_securise((string) $film['title']) ?>"
						/>
						<figcaption>Affiche du film</figcaption>
					</figure>
					<figure class="tech-figure tech-figure-wide">
						<img
							class="tech-banner"
							src="<?= texte_securise((string) $film['movie_banner']) ?>"
							width="400"
							alt="Bannière du film <?= texte_securise((string) $film['title']) ?>"
						/>
						<figcaption>Bannière du film</figcaption>
					</figure>
				</div>
			<?php } ?>
		</section>

		<section class="info-block tech-feature">
				<h2>Flux XML carburants</h2>
				<p>Lecture de <code>data/sample_fuel_prices.xml</code> avec <code>simplexml_load_file()</code>.</p>
				<?php if ($stationsXml === []) { ?>
					<p class="empty-state">Aucune donnee XML disponible.</p>
				<?php } else { ?>
					<ul class="plain-list">
						<?php foreach (array_slice($stationsXml, 0, 5) as $station) { ?>
							<li>
								<strong><?= texte_securise($station["enseigne"]) ?></strong>
								- <?= texte_securise($station["ville"]) ?>
								(<?= texte_securise($station["cp"]) ?>)
								<?php if ($station["prix"] !== []) { ?>
									:
									<?php foreach ($station["prix"] as $indicePrix => $prix) { ?>
										<?= $indicePrix > 0 ? ", " : "" ?><?= texte_securise($prix["nom"]) ?>
										<?= texte_securise($prix["valeur"]) ?> EUR/L
									<?php } ?>
								<?php } ?>
							</li>
						<?php } ?>
					</ul>
				<?php } ?>
		</section>

		<section class="info-block tech-feature">
				<h2>Flux JSON cote serveur</h2>
			<p>Geolocalisation IP approx. obtenue en PHP avec cache fichier JSON.</p>
			<ul class="plain-list">
				<li>IP detectee: <?= texte_securise($donneesGeo['ip']) ?></li>
				<li>Ville retournee: <?= texte_securise($donneesGeo['city'] !== "" ? $donneesGeo['city'] : "Non trouvee") ?></li>
				<li>Region retournee: <?= texte_securise($donneesGeo['region'] !== "" ? $donneesGeo['region'] : "Non trouvee") ?></li>
				<li>Latitude: <?= texte_securise($donneesGeo['latitude'] !== 0.0 ? (string) $donneesGeo['latitude'] : "Non trouvee") ?></li>
				<li>Longitude: <?= texte_securise($donneesGeo['longitude'] !== 0.0 ? (string) $donneesGeo['longitude'] : "Non trouvee") ?></li>
				<li>Source utilisee: <?= texte_securise($donneesGeo['source']) ?></li>
			</ul>
		</section>

		<section class="info-block tech-feature">
			<h2>Flux carburants cote serveur</h2>
			<p>
				Les stations-service sont recherchees depuis l'API JSON officielle du
				gouvernement avec un filtre sur le departement et la ville.
			</p>
			<ul class="plain-list">
				<li>Requete HTTP cote serveur en PHP</li>
				<li>Reponse JSON transformee en tableaux PHP</li>
				<li>Reutilisation dans la page resultats pour les prix, distances et liens de carte</li>
			</ul>
		</section>

		<section class="info-block tech-feature">
			<h2>Stockages attendus</h2>
			<ul class="plain-list">
				<li>CSV serveur: historique des consultations</li>
				<li>Cookie <code>last_visited_city</code>: derniere ville</li>
					<li>Cookie <code>last_search_params</code>: derniere recherche complete</li>
					<li>Cookie <code>theme</code>: jour ou nuit</li>
					<li>Cookie <code>lang</code>: langue d'affichage</li>
					<li>Cache JSON: reponses externes et fallback sur cache expire</li>
				</ul>
		</section>

		<section class="info-block tech-feature">
			<h2>Etat des statistiques</h2>
			<ul class="plain-list">
					<li>Consultations enregistrees: <?= texte_securise((string) $statistiques['consultation_count']) ?></li>
					<li>Visites de pages: <?= texte_securise((string) $statistiques['page_visit_count']) ?></li>
					<li>Visiteurs approx.: <?= texte_securise((string) $statistiques['page_visitor_count']) ?></li>
					<li>Nombre de villes dans le top: <?= texte_securise((string) count($statistiques['top_cities'])) ?></li>
				</ul>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
