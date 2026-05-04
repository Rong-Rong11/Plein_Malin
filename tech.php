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
$demanderApiAvecCle = isset($_GET["api_cle"]);
$donneesGeoAvecCle = false;
$ipAvecCleAffichee = "";
$latitudeAvecCle = "Non trouvee";
$longitudeAvecCle = "Non trouvee";

if ($demanderApiAvecCle) {
	$ipAvecCle = "";
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$ipAvecCle = $_SERVER["REMOTE_ADDR"];
	}

	if ($ipAvecCle === "127.0.0.1" || $ipAvecCle === "::1" || $ipAvecCle === "") {
		$ipAvecCle = "193.54.115.192";
	}
	$ipAvecCleAffichee = $ipAvecCle;

	$cleApiGeo = "8cd4f65797dd4cb507a7b98e27153175";
	$urlApiAvecCle = "https://api.whatismyip.com/ip-address-lookup.php?key=" . $cleApiGeo . "&input=" . $ipAvecCle . "&output=xml";

	$xmlGeoAvecCle = @file_get_contents($urlApiAvecCle);
	if ($xmlGeoAvecCle !== false) {
		$donneesGeoAvecCle = @simplexml_load_string($xmlGeoAvecCle);
		if ($donneesGeoAvecCle !== false) {
			$latitudeAvecCle = (string) ($donneesGeoAvecCle->server_data->latitude ?? "");
			$longitudeAvecCle = (string) ($donneesGeoAvecCle->server_data->longitude ?? "");

			if ($latitudeAvecCle === "") {
				$latitudeAvecCle = (string) ($donneesGeoAvecCle->server_data->lat ?? "");
			}

			if ($longitudeAvecCle === "") {
				$longitudeAvecCle = (string) ($donneesGeoAvecCle->server_data->lon ?? "");
			}

			if ($latitudeAvecCle === "") {
				$latitudeAvecCle = "Non trouvee";
			}

			if ($longitudeAvecCle === "") {
				$longitudeAvecCle = "Non trouvee";
			}
		}
	}
}
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
		<h1><?= texte_securise("Page tech") ?></h1>
		<p class="lead">
			<?= texte_securise("Cette page reste accessible depuis le footer pour montrer l'avancement initial") ?>
			<?= texte_securise("et la logique technique reutilisee dans Plein Malin.") ?>
		</p>
		<section class="info-block tech-summary">
			<h2><?= texte_securise("Synthese technique du projet") ?></h2>
			<ul class="plain-list">
				<li><strong>JSON</strong> : <?= texte_securise("geolocalisation IP et API officielle des prix carburants, traitees cote serveur en PHP.") ?></li>
				<li><strong>XML</strong> : <?= texte_securise("lecture de") ?> <code>data/sample_fuel_prices.xml</code> <?= texte_securise("et archive annuelle officielle pour les tendances de prix.") ?></li>
				<li><strong>CSV</strong> : <?= texte_securise("regions, departements, villes, consultations et visites de pages.") ?></li>
				<li><strong>Cookies</strong> : <?= texte_securise("theme jour/nuit, langue, derniere recherche et derniere ville consultee.") ?></li>
				<li><strong>Statistiques</strong> : <?= texte_securise("tops des villes, departements, regions, carburants, modes de recherche et tendances de prix.") ?></li>
			</ul>
		</section>

		<section class="info-block tech-feature">
				<h2><?= texte_securise("API Ghibli") ?></h2>
			<?php if ($film === null) { ?>
				<p class="empty-state"><?= texte_securise("API Ghibli indisponible pour le moment.") ?></p>
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
							alt="<?= texte_securise("Affiche du film") ?> <?= texte_securise((string) $film['title']) ?>"
						/>
						<figcaption><?= texte_securise("Affiche du film") ?></figcaption>
					</figure>
					<figure class="tech-figure tech-figure-wide">
						<img
							class="tech-banner"
							src="<?= texte_securise((string) $film['movie_banner']) ?>"
							width="400"
							alt="<?= texte_securise("Bannière du film") ?> <?= texte_securise((string) $film['title']) ?>"
						/>
						<figcaption><?= texte_securise("Bannière du film") ?></figcaption>
					</figure>
				</div>
			<?php } ?>
		</section>

		<section class="info-block tech-feature">
				<h2><?= texte_securise("Flux XML carburants") ?></h2>
				<p><?= texte_securise("Lecture de") ?> <code>data/sample_fuel_prices.xml</code> <?= texte_securise("avec") ?> <code>simplexml_load_file()</code>.</p>
				<?php if ($stationsXml === []) { ?>
					<p class="empty-state"><?= texte_securise("Aucune donnee XML disponible.") ?></p>
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

		<section class="info-block tech-feature" id="geo-ip">
			<h2><?= texte_securise("Geolocalisation IP") ?></h2>
			<p>
				<?= texte_securise("Le projet actuel geolocalise l'utilisateur a partir de son adresse IP avec l'API") ?>
				<code>ipapi.co</code>.
				<?= texte_securise("Cette API est appelee cote serveur en PHP et la reponse revient au format JSON.") ?>
			</p>
			<p>
				<?= texte_securise("Dans une ancienne version, une autre API etait utilisee avec une cle. Avec ce type d'API, il vaut mieux declencher l'appel avec un bouton pour eviter d'utiliser la cle automatiquement a chaque chargement de page.") ?>
			</p>

			<h3><?= texte_securise("Ce que l'API sans cle affiche") ?></h3>
			<p>
				<?= texte_securise("L'API actuelle retourne ici l'adresse IP detectee, la ville, la region et des coordonnees approximatives.") ?>
			</p>
			<ul class="plain-list">
				<li><?= texte_securise("IP detectee") ?>: <?= texte_securise($donneesGeo['ip']) ?></li>
				<li><?= texte_securise("Ville retournee") ?>: <?= texte_securise($donneesGeo['city'] !== "" ? $donneesGeo['city'] : "Non trouvee") ?></li>
				<li><?= texte_securise("Region retournee") ?>: <?= texte_securise($donneesGeo['region'] !== "" ? $donneesGeo['region'] : "Non trouvee") ?></li>
				<li>Latitude: <?= texte_securise($donneesGeo['latitude'] !== 0.0 ? (string) $donneesGeo['latitude'] : "Non trouvee") ?></li>
				<li>Longitude: <?= texte_securise($donneesGeo['longitude'] !== 0.0 ? (string) $donneesGeo['longitude'] : "Non trouvee") ?></li>
				<li><?= texte_securise("Source utilisee") ?>: <?= texte_securise($donneesGeo['source']) ?></li>
			</ul>

			<h3><?= texte_securise("Exemple avec l'ancienne API a cle") ?></h3>
			<p>
				<?= texte_securise("Le bouton ci-dessous sert a montrer qu'une API avec cle ne doit pas forcement etre lancee automatiquement. Ici, l'appel ne se fait qu'au clic.") ?>
			</p>
			<form action="tech.php#api-cle" method="get">
				<p><button type="submit" name="api_cle" value="1" class="primary-btn"><?= texte_securise("Utiliser l'API avec cle") ?></button></p>
			</form>
			<div id="api-cle">
				<?php if ($demanderApiAvecCle) { ?>
					<?php if ($donneesGeoAvecCle !== false) { ?>
						<ul class="plain-list">
							<li><?= texte_securise("IP detectee") ?>: <?= texte_securise($ipAvecCleAffichee) ?></li>
							<li><?= texte_securise("Ville retournee") ?>: <?= texte_securise((string) ($donneesGeoAvecCle->server_data->city ?? "Non trouvee")) ?></li>
							<li><?= texte_securise("Region retournee") ?>: <?= texte_securise((string) ($donneesGeoAvecCle->server_data->region ?? "Non trouvee")) ?></li>
							<li><?= texte_securise("Pays retourne") ?>: <?= texte_securise((string) ($donneesGeoAvecCle->server_data->country ?? "Non trouve")) ?></li>
							<li>Latitude: <?= texte_securise($latitudeAvecCle) ?></li>
							<li>Longitude: <?= texte_securise($longitudeAvecCle) ?></li>
						</ul>
					<?php } else { ?>
						<p><?= texte_securise("Impossible de recuperer les donnees de l'API avec cle.") ?></p>
					<?php } ?>
				<?php } else { ?>
					<p><?= texte_securise("L'API avec cle n'est pas lancee automatiquement. Il faut cliquer sur le bouton.") ?></p>
				<?php } ?>
			</div>
		</section>

		<section class="info-block tech-feature">
			<h2><?= texte_securise("Flux carburants cote serveur") ?></h2>
			<p>
				<?= texte_securise("Les stations-service sont recherchees depuis l'API JSON officielle du") ?>
				<?= texte_securise("gouvernement avec un filtre sur le departement et la ville.") ?>
			</p>
			<ul class="plain-list">
				<li><?= texte_securise("Requete HTTP cote serveur en PHP") ?></li>
				<li><?= texte_securise("Reponse JSON transformee en tableaux PHP") ?></li>
				<li><?= texte_securise("Reutilisation dans la page resultats pour les prix, distances et liens de carte") ?></li>
			</ul>
		</section>

		<section class="info-block tech-feature">
			<h2><?= texte_securise("Stockages attendus") ?></h2>
			<ul class="plain-list">
				<li><?= texte_securise("CSV serveur: historique des consultations") ?></li>
				<li>Cookie <code>last_visited_city</code>: <?= texte_securise("derniere ville") ?></li>
					<li>Cookie <code>last_search_params</code>: <?= texte_securise("derniere recherche complete") ?></li>
					<li>Cookie <code>theme</code>: <?= texte_securise("jour ou nuit") ?></li>
					<li>Cookie <code>lang</code>: <?= texte_securise("langue d'affichage") ?></li>
					<li><?= texte_securise("Cache JSON: reponses externes et fallback sur cache expire") ?></li>
				</ul>
		</section>

		<section class="info-block tech-feature">
			<h2><?= texte_securise("Etat des statistiques") ?></h2>
			<ul class="plain-list">
					<li><?= texte_securise("Consultations enregistrees") ?>: <?= texte_securise((string) $statistiques['consultation_count']) ?></li>
					<li><?= texte_securise("Visites de pages") ?>: <?= texte_securise((string) $statistiques['page_visit_count']) ?></li>
					<li><?= texte_securise("Visiteurs approx.") ?>: <?= texte_securise((string) $statistiques['page_visitor_count']) ?></li>
					<li><?= texte_securise("Nombre de villes dans le top") ?>: <?= texte_securise((string) count($statistiques['top_cities'])) ?></li>
				</ul>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
