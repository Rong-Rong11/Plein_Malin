<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$selectedFuels = normaliser_carburants_selection($_GET["fuel"] ?? []);
$view = $_GET["view"] ?? "summary";
$detailStation = isset($_GET["station"]) ? (string) $_GET["station"] : "";
$sort = $_GET["sort"] ?? "price";
$geoRadius = normaliser_rayon_geo((int) ($_GET["geo_radius"] ?? 10));
$departmentMode = isset($_GET["department_mode"]);
$useGeo = isset($_GET["use_geo"]);

$region = $_GET["region"] ?? "";
$department = $_GET["department"] ?? "";
$city = $_GET["city"] ?? "";

$currentCity = null;
$stations = [];
$geo = null;
$message = "Aucune recherche lancée.";
$departmentInfo = null;
$regionInfo = null;
$choixRechercheIncomplet = !$useGeo && !$departmentMode && $department !== "" && $city === "";
$departmentLabel = $department;

if ($department !== "") {
	$departmentInfo = trouver_departement($department);
	if ($departmentInfo !== null) {
		$region = $departmentInfo["region_code"] ?? $region;
		$regionInfo = trouver_region($region);
		$departmentLabel = $departmentInfo["department_name"] . " (" . $department . ")";
	}
}

if ($useGeo) {
	$geo = recuperer_geolocalisation();
	$currentCity = trouver_ville_plus_proche((float) $geo["latitude"], (float) $geo["longitude"]);
} elseif ($departmentMode && $department !== "") {
	if ($departmentInfo !== null) {
		$region = $departmentInfo["region_code"] ?? $region;
		$regionInfo = trouver_region($region);
		$currentCity = $city !== "" ? trouver_ville($city) : null;

		if ($currentCity === null || $currentCity["department_code"] !== $department) {
			$villesDepartement = villes_par_departement($department);
			$currentCity = $villesDepartement[0] ?? [
				"city_code" => "",
				"city_name" => "Département " . $department,
				"postal_code" => "",
				"department_code" => $department,
				"latitude" => 0,
				"longitude" => 0,
			];
		}
	}
} elseif ($city !== "") {
	$currentCity = trouver_ville($city);
}

if ($currentCity !== null) {
	$department = $currentCity["department_code"];
	$departmentInfo = trouver_departement($department);
	$region = $departmentInfo["region_code"] ?? $region;
	$regionInfo = trouver_region($region);

	if (!$departmentMode && $currentCity["city_code"] !== "") {
		enregistrer_derniere_ville($currentCity["city_code"]);
	} elseif ($departmentMode && $department !== "") {
		enregistrer_derniere_recherche("departement", $department);
	}
	$stations = rechercher_stations($currentCity, $selectedFuels, $sort, $departmentMode, $useGeo ? $geo : null, $geoRadius);

	enregistrer_consultation([
		"region" => $regionInfo["region_name"] ?? "",
		"department" => $departmentInfo["department_name"] ?? "",
		"city" => $departmentMode ? "Département " . ($departmentInfo["department_name"] ?? $department) : $currentCity["city_name"],
		"mode" => mode_recherche($useGeo, $departmentMode),
		"view" => $view,
		"fuel" => texte_carburants_selectionnes($selectedFuels),
		"station_count" => count($stations),
	]);
}

$message = message_resultats($currentCity, $useGeo, $departmentMode, $stations);
if ($useGeo && $currentCity !== null) {
	$message = "Recherche autour de votre position approximative dans un rayon de " . $geoRadius . " km.";
} elseif ($choixRechercheIncomplet) {
	$message = "Vous avez choisi le département " . $departmentLabel . ". Sélectionnez une ville ou cochez \"Tout le département\" pour lancer la recherche.";
}

$searchModeLabel = "Ville";
if ($useGeo) {
	$searchModeLabel = "Autour de moi";
} elseif ($departmentMode) {
	$searchModeLabel = "Département";
}

$sortLabel = "Prix croissant";
if ($sort === "distance") {
	$sortLabel = "Proximité";
} elseif ($sort === "name") {
	$sortLabel = "Nom";
}

$viewLabel = $view === "detailed" ? "Détaillée" : "Synthèse";
$selectedFuelsLabel = texte_carburants_selectionnes($selectedFuels);
$searchTargetLabel = "Non défini";

if ($useGeo) {
	$searchTargetLabel = "votre position approximative";
} elseif ($departmentMode) {
	$searchTargetLabel = "tout le département " . ($departmentInfo["department_name"] ?? $department);
	if ($department !== "") {
		$searchTargetLabel .= " (" . $department . ")";
	}
} elseif ($currentCity !== null) {
	$searchTargetLabel = $currentCity["city_name"];
}

$searchParameters = [
	"region" => $region,
	"department" => $department,
	"city" => $city,
	"fuel" => $selectedFuels,
	"view" => $view,
	"sort" => $sort,
	"geo_radius" => $geoRadius,
];

if ($departmentMode) {
	$searchParameters["department_mode"] = "1";
}

if ($useGeo) {
	$searchParameters["use_geo"] = "1";
}

$prixMoyenRecherche = null;
$meilleureStation = null;
$limiteStationsAffichees = 15;
$stationsAffichees = array_slice($stations, 0, $limiteStationsAffichees);
$stationsMasquees = max(0, count($stations) - count($stationsAffichees));
$prixRecherche = array_column($stationsAffichees, "main_price");

if ($prixRecherche !== []) {
	$prixMoyenRecherche = array_sum($prixRecherche) / count($prixRecherche);

	foreach ($stationsAffichees as $station) {
		if ($meilleureStation === null || (float) $station["main_price"] < (float) $meilleureStation["main_price"]) {
			$meilleureStation = $station;
		}
	}
}

enregistrer_parametres_derniere_recherche($searchParameters);

$searchLink = "recherche.php?" . http_build_query($searchParameters) . "#recherche";

$pageTitle = "Résultats - Plein Malin";
$pageDescription = "Résultats des stations-service et des prix.";
$activePage = "resultats";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
			<section class="panel">
				<p class="eyebrow">Résultats</p>
				<h1>Stations pour <?= texte_securise($selectedFuelsLabel) ?></h1>
					<p class="lead">
						Consultez les stations trouvées puis revenez à la recherche si besoin.
					</p>
					<div class="form-actions">
						<a class="cta-link" href="<?= texte_securise($searchLink) ?>">Modifier ma recherche</a>
						<form action="resultats.php#resultats" method="get" class="inline-sort-form">
							<input type="hidden" name="region" value="<?= texte_securise($region) ?>" />
							<input type="hidden" name="department" value="<?= texte_securise($department) ?>" />
							<input type="hidden" name="city" value="<?= texte_securise($city) ?>" />
							<?php foreach ($selectedFuels as $fuel): ?>
								<input type="hidden" name="fuel[]" value="<?= texte_securise($fuel) ?>" />
							<?php endforeach; ?>
							<input type="hidden" name="view" value="<?= texte_securise($view) ?>" />
							<input type="hidden" name="geo_radius" value="<?= texte_securise((string) $geoRadius) ?>" />
							<?php if ($departmentMode): ?>
								<input type="hidden" name="department_mode" value="1" />
							<?php endif; ?>
							<?php if ($useGeo): ?>
								<input type="hidden" name="use_geo" value="1" />
							<?php endif; ?>
							<div class="inline-filter">
								<label for="result-sort-select">Trier</label>
								<select id="result-sort-select" name="sort">
									<option value="price" <?= $sort === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
									<option value="distance" <?= $sort === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
									<option value="name" <?= $sort === "name" ? 'selected="selected"' : "" ?>>Nom</option>
								</select>
							</div>
							<button type="submit" class="secondary-btn">Appliquer le tri</button>
						</form>
						<details class="search-details">
						<summary class="detail-toggle">Détail</summary>
						<div class="search-details-box">
							<h2>Recherche actuelle</h2>
								<ul class="plain-list">
									<li>Mode : <?= texte_securise($searchModeLabel) ?></li>
									<li>Carburants choisis : <?= texte_securise($selectedFuelsLabel) ?></li>
									<li>Tri choisi : <?= texte_securise($sortLabel) ?></li>
									<li>Vue choisie : <?= texte_securise($viewLabel) ?></li>
									<li>Stations trouvées : <?= texte_securise((string) count($stations)) ?></li>
									<li>Périmètre : <?= texte_securise($searchTargetLabel) ?></li>
									<li>Région : <?= texte_securise($regionInfo["region_name"] ?? "Non définie") ?></li>
									<li>Département : <?= texte_securise($departmentInfo["department_name"] ?? "Non défini") ?><?= $department !== "" ? " (" . texte_securise($department) . ")" : "" ?></li>
									<?php if (!$departmentMode): ?>
										<li>Ville de référence : <?= texte_securise($currentCity["city_name"] ?? "Non définie") ?></li>
									<?php endif; ?>
									<?php if (!$departmentMode && $currentCity !== null): ?>
										<li>Code ville : <?= texte_securise($currentCity["city_code"]) ?></li>
										<li>Code postal : <?= texte_securise($currentCity["postal_code"]) ?></li>
									<?php endif; ?>
								<?php if ($useGeo && $geo !== null): ?>
									<li>Rayon géolocalisé : <?= texte_securise((string) $geoRadius) ?> km</li>
									<li>Latitude : <?= texte_securise((string) $geo["latitude"]) ?></li>
									<li>Longitude : <?= texte_securise((string) $geo["longitude"]) ?></li>
									<li>Ville retournee par l'IP : <?= texte_securise($geo["city"]) ?></li>
									<li>Région retournée par l'IP : <?= texte_securise($geo["region"]) ?></li>
									<li>Source de localisation : <?= texte_securise($geo["source"]) ?></li>
								<?php endif; ?>
							</ul>
							</div>
					</details>
				</div>
			</section>

		<section class="results-panel" id="resultats">
			<h2>Résultats</h2>
				<p class="small-note">
					<?= texte_securise($message) ?>
					<?php if ($currentCity !== null): ?>
						<strong><?= texte_securise($searchTargetLabel) ?></strong>
					<?php endif; ?>
					</p>
					<?php if ($useGeo): ?>
						<p class="small-note">
							Position estimée à partir de l'adresse IP.
							<?php if ($geo !== null && trim((string) ($geo["city"] ?? "")) !== ""): ?>
								Vous êtes approximativement à <strong><?= texte_securise((string) $geo["city"]) ?></strong>.
							<?php elseif ($currentCity !== null): ?>
								Vous êtes approximativement près de <strong><?= texte_securise($currentCity["city_name"]) ?></strong>.
							<?php endif; ?>
						</p>
					<?php endif; ?>

				<?php if ($currentCity === null): ?>
					<p class="empty-state">
						<?= texte_securise($choixRechercheIncomplet ? "Choisissez une ville dans le département " . $departmentLabel . " ou activez la recherche dans tout le département." : "Aucune recherche lancée.") ?>
					</p>
				<?php elseif ($stations === []): ?>
				<p class="empty-state">Aucune station trouvée avec ces critères.</p>
				<?php else: ?>
					<p class="small-note"><?= texte_securise((string) count($stations)) ?> station(s) trouvée(s).</p>
					<?php if ($stationsMasquees > 0): ?>
						<p class="small-note">
							Pour limiter le poids de la page et réduire sa complexité, seules les <?= texte_securise((string) count($stationsAffichees)) ?>
							premières stations sont affichées sur <?= texte_securise((string) count($stations)) ?> trouvées. Elles correspondent aux résultats
							<?= $sort === "price" ? "les moins chers" : "les plus pertinents selon le tri choisi" ?>.
						</p>
					<?php endif; ?>
					<p class="small-note">
						Les prix dépendent de la dernière mise à jour transmise par l'API officielle.
						Certaines stations peuvent ne pas proposer tous les carburants sélectionnés.
					</p>

					<?php if ($prixMoyenRecherche !== null && $meilleureStation !== null): ?>
						<div class="stats-inline result-summary">
							<div class="stat-chip">
								<strong><?= texte_securise(formater_prix($prixMoyenRecherche)) ?></strong>
								<span>prix moyen trouvé sur les stations affichées</span>
							</div>
							<a class="stat-chip best-price-link" href="#station-<?= texte_securise(rawurlencode((string) $meilleureStation["id"])) ?>">
								<strong><?= texte_securise(formater_prix((float) $meilleureStation["main_price"])) ?></strong>
								<span>meilleur prix affiché - <?= texte_securise($meilleureStation["name"]) ?></span>
								<small>Cliquer pour voir la station</small>
							</a>
						</div>
					<?php endif; ?>

					<div class="cards">
						<?php foreach ($stationsAffichees as $station): ?>
							<?php
									$stationAnchor = rawurlencode((string) $station["id"]);
									$detailParameters = $searchParameters;
								$detailParameters["view"] = "detailed";
								$detailParameters["station"] = (string) $station["id"];
								$detailLink = "resultats.php?" . http_build_query($detailParameters) . "#station-" . $stationAnchor;
								$afficherDetailsStation = $view === "detailed" && ($detailStation === "" || $detailStation === (string) $station["id"]);
								$stationName = (string) $station["name"];
								if ($station["address"] !== "") {
									$stationName = trim(str_replace(" - " . $station["address"], "", $stationName));
								}
								$prixCarburantsSelectionnes = [];
								foreach ($selectedFuels as $carburantSelectionne) {
									if (isset($station["prices"][$carburantSelectionne])) {
										$prixCarburantsSelectionnes[] = $station["prices"][$carburantSelectionne];
									}
								}
								$latitudeStation = (float) ($station["latitude"] ?? 0);
								$longitudeStation = (float) ($station["longitude"] ?? 0);
								$lienCarteStation = "";
								if ($latitudeStation !== 0.0 || $longitudeStation !== 0.0) {
									$lienCarteStation = "https://www.openstreetmap.org/?mlat="
										. rawurlencode((string) $latitudeStation)
										. "&mlon=" . rawurlencode((string) $longitudeStation)
										. "#map=16/"
										. rawurlencode((string) $latitudeStation)
										. "/"
										. rawurlencode((string) $longitudeStation);
								}
								?>
							<article class="station-card" id="station-<?= texte_securise($stationAnchor) ?>">
							<div class="station-top">
								<div>
										<h3><?= texte_securise($stationName) ?></h3>
									<p><?= texte_securise($station["address"]) ?>, <?= texte_securise($station["postal_code"]) ?> <?= texte_securise($station["city_name"]) ?></p>
									</div>
										<div class="price-box">
											<?php foreach ($prixCarburantsSelectionnes as $prixSelectionne): ?>
												<div class="price-line">
													<span><?= texte_securise($prixSelectionne["name"]) ?></span>
													<strong><?= texte_securise(formater_prix((float) $prixSelectionne["value"])) ?></strong>
												</div>
											<?php endforeach; ?>
										</div>
							</div>

								<p class="meta-line">
									Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
									| flux <?= texte_securise($station["source"]) ?>
									<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== ""): ?>
										| prix mis à jour le <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
									<?php endif; ?>
									</p>

									<?php if (!$afficherDetailsStation): ?>
										<div class="form-actions station-actions">
											<a class="secondary-btn" href="<?= texte_securise($detailLink) ?>">Voir les détails</a>
											<?php if ($lienCarteStation !== ""): ?>
												<a class="secondary-btn" href="<?= texte_securise($lienCarteStation) ?>" target="_blank" rel="noopener">Voir sur une carte</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if ($afficherDetailsStation): ?>
								<div class="form-actions station-actions">
									<?php if ($lienCarteStation !== ""): ?>
										<a class="secondary-btn" href="<?= texte_securise($lienCarteStation) ?>" target="_blank" rel="noopener">Voir sur une carte</a>
									<?php endif; ?>
								</div>
								<div class="details-grid">
									<div>
										<h4>Carburants</h4>
										<ul class="plain-list">
												<?php foreach ($station["prices"] as $price): ?>
													<li>
														<?= texte_securise($price["name"]) ?> :
														<?= texte_securise(formater_prix((float) $price["value"])) ?>
														<?php if (formater_date_heure($price["updated_at"] ?? "") !== ""): ?>
																(mis à jour le <?= texte_securise(formater_date_heure($price["updated_at"])) ?>)
														<?php endif; ?>
													</li>
												<?php endforeach; ?>
										</ul>
									</div>
									<div>
										<h4>Services</h4>
										<p class="small-note">Équipements et prestations proposés par la station.</p>
										<?php if ($station["services"] === []): ?>
											<p class="small-note">Aucun service indique.</p>
										<?php else: ?>
											<ul class="plain-list">
												<?php foreach ($station["services"] as $service): ?>
													<li><?= texte_securise($service) ?></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
							</article>
						<?php endforeach; ?>
					</div>
					<div class="form-actions bottom-actions">
						<a class="cta-link" href="<?= texte_securise($searchLink) ?>">Retour à la recherche</a>
					</div>
				<?php endif; ?>
			</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
