<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$selectedFuels = normaliser_carburants_selection($_GET["fuel"] ?? []);
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
$apiCarburantsErreur = false;
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
	if ((float) ($geo["latitude"] ?? 0) !== 0.0 || (float) ($geo["longitude"] ?? 0) !== 0.0) {
		$currentCity = trouver_ville_plus_proche((float) $geo["latitude"], (float) $geo["longitude"]);
	}
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
	$resultatRecherche = rechercher_stations_avec_statut($currentCity, $selectedFuels, $sort, $departmentMode, $useGeo ? $geo : null, $geoRadius);
	$stations = $resultatRecherche["stations"];
	$apiCarburantsErreur = $resultatRecherche["api_error"];

	enregistrer_consultation([
		"region" => $regionInfo["region_name"] ?? "",
		"department" => $departmentInfo["department_name"] ?? "",
		"city" => $departmentMode ? "Département " . ($departmentInfo["department_name"] ?? $department) : $currentCity["city_name"],
		"mode" => mode_recherche($useGeo, $departmentMode),
		"view" => "",
		"fuel" => texte_carburants_selectionnes($selectedFuels),
		"station_count" => count($stations),
	]);
}

$message = message_resultats($currentCity, $useGeo, $departmentMode, $stations);
if ($apiCarburantsErreur) {
	$message = "L'API officielle des carburants ne répond pas pour le moment.";
} elseif ($useGeo && $currentCity !== null) {
	$message = "Recherche autour de votre position approximative dans un rayon de " . $geoRadius . " km.";
} elseif ($useGeo) {
	$message = "Impossible de trouver votre position approximative pour le moment.";
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
if ($sort === "price_desc") {
	$sortLabel = "Prix décroissant";
} elseif ($sort === "distance") {
	$sortLabel = "Proximité";
} elseif ($sort === "name") {
	$sortLabel = "Nom";
}

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
					<?php if ($useGeo) { ?>
						<p class="lead">
							<?php if ($currentCity !== null) { ?>
								Vous êtes approximativement à <strong><?= texte_securise($currentCity["city_name"]) ?></strong>,
								d'après une position estimée à partir de l'adresse IP.
							<?php } elseif ($geo !== null && trim((string) ($geo["city"] ?? "")) !== "") { ?>
								Vous êtes approximativement à <strong><?= texte_securise((string) $geo["city"]) ?></strong>,
								d'après une position estimée à partir de l'adresse IP.
							<?php } else { ?>
								Position estimée à partir de l'adresse IP.
							<?php } ?>
						</p>
					<?php } ?>
					<div class="form-actions">
						<a class="cta-link" href="recherche.php?search_mode=manual">Recherche manuelle</a>
					</div>
					<div class="results-tools">
					<form action="resultats.php#resultats" method="get" class="results-refine-form search-form search-form-structured">
						<input type="hidden" name="region" value="<?= texte_securise($region) ?>" />
						<input type="hidden" name="department" value="<?= texte_securise($department) ?>" />
						<input type="hidden" name="city" value="<?= texte_securise($city) ?>" />
						<input type="hidden" name="geo_radius" value="<?= texte_securise((string) $geoRadius) ?>" />
						<?php if ($departmentMode) { ?>
							<input type="hidden" name="department_mode" value="1" />
						<?php } ?>
						<?php if ($useGeo) { ?>
							<input type="hidden" name="use_geo" value="1" />
						<?php } ?>
						<div class="search-section search-plain">
							<div class="field-card field-card-wide">
								<span class="field-title">Carburants</span>
								<span class="field-help">Gazole par défaut si rien n'est coché.</span>
								<span class="fuel-choice-list">
									<?php foreach (liste_carburants() as $codeCarburant => $nomCarburant) { ?>
										<label class="fuel-choice">
											<input type="checkbox" id="result-fuel-<?= texte_securise(strtolower($codeCarburant)) ?>" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
												<?= in_array($codeCarburant, $selectedFuels, true) ? 'checked="checked"' : "" ?> />
											<span><?= texte_securise($nomCarburant) ?></span>
										</label>
									<?php } ?>
								</span>
							</div>
							<div class="field-grid field-grid-main">
								<div class="field-card">
									<label class="field-title" for="result-sort-select">Tri</label>
									<select id="result-sort-select" name="sort">
										<option value="price" <?= $sort === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
										<option value="price_desc" <?= $sort === "price_desc" ? 'selected="selected"' : "" ?>>Prix décroissant</option>
										<option value="distance" <?= $sort === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
										<option value="name" <?= $sort === "name" ? 'selected="selected"' : "" ?>>Nom</option>
									</select>
								</div>
							</div>
							<div class="form-actions action-buttons">
								<button type="submit">Appliquer</button>
							</div>
						</div>
					</form>
					<div class="form-actions results-detail-toggle">
						<details class="search-details">
							<summary class="detail-toggle">Détail</summary>
							<div class="search-details-box">
								<h2>Recherche actuelle</h2>
								<ul class="plain-list">
									<li>Mode : <?= texte_securise($searchModeLabel) ?></li>
									<li>Carburants choisis : <?= texte_securise($selectedFuelsLabel) ?></li>
									<li>Tri choisi : <?= texte_securise($sortLabel) ?></li>
									<li>Stations trouvées : <?= texte_securise((string) count($stations)) ?></li>
									<li>Périmètre : <?= texte_securise($searchTargetLabel) ?></li>
									<li>Région : <?= texte_securise($regionInfo["region_name"] ?? "Non définie") ?></li>
									<li>Département : <?= texte_securise($departmentInfo["department_name"] ?? "Non défini") ?><?= $department !== "" ? " (" . texte_securise($department) . ")" : "" ?></li>
									<?php if (!$departmentMode) { ?>
										<li>Ville de référence : <?= texte_securise($currentCity["city_name"] ?? "Non définie") ?></li>
									<?php } ?>
									<?php if (!$departmentMode && $currentCity !== null) { ?>
										<li>Code ville : <?= texte_securise($currentCity["city_code"]) ?></li>
										<li>Code postal : <?= texte_securise($currentCity["postal_code"]) ?></li>
									<?php } ?>
									<?php if ($useGeo && $geo !== null) { ?>
										<li>Rayon géolocalisé : <?= texte_securise((string) $geoRadius) ?> km</li>
										<li>Latitude : <?= texte_securise((string) $geo["latitude"]) ?></li>
										<li>Longitude : <?= texte_securise((string) $geo["longitude"]) ?></li>
										<li>Ville retournee par l'IP : <?= texte_securise($geo["city"]) ?></li>
										<li>Région retournée par l'IP : <?= texte_securise($geo["region"]) ?></li>
										<li>Source de localisation : <?= texte_securise($geo["source"]) ?></li>
									<?php } ?>
								</ul>
							</div>
						</details>
					</div>
					</div>
			</section>

		<section class="results-panel" id="resultats">
			<h2>Résultats</h2>
				<p class="small-note">
					<?= texte_securise($message) ?>
					<?php if ($currentCity !== null) { ?>
						<strong><?= texte_securise($searchTargetLabel) ?></strong>
					<?php } ?>
					</p>
				<?php if ($currentCity === null) { ?>
					<p class="empty-state">
						<?= texte_securise($choixRechercheIncomplet ? "Choisissez une ville dans le département " . $departmentLabel . " ou activez la recherche dans tout le département." : "Aucune recherche lancée.") ?>
					</p>
				<?php } elseif ($apiCarburantsErreur) { ?>
				<p class="empty-state">Impossible d'afficher les stations : l'API officielle des carburants ne répond pas. Réessayez plus tard.</p>
				<?php } elseif ($stations === []) { ?>
				<p class="empty-state">Aucune station trouvée avec ces critères.</p>
				<?php } else { ?>
					<p class="small-note"><?= texte_securise((string) count($stations)) ?> station(s) trouvée(s).</p>
					<?php if ($stationsMasquees > 0) { ?>
						<p class="small-note">
							Pour limiter le poids de la page et réduire sa complexité, seules les <?= texte_securise((string) count($stationsAffichees)) ?>
							premières stations sont affichées sur <?= texte_securise((string) count($stations)) ?> trouvées. Elles correspondent aux résultats
							<?= $sort === "price" ? "les moins chers" : "les plus pertinents selon le tri choisi" ?>.
						</p>
					<?php } ?>
					<p class="small-note">
						Les prix dépendent de la dernière mise à jour transmise par l'API officielle.
						Certaines stations peuvent ne pas proposer tous les carburants sélectionnés.
					</p>

					<?php if ($prixMoyenRecherche !== null && $meilleureStation !== null) { ?>
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
					<?php } ?>

					<div class="cards">
						<?php foreach ($stationsAffichees as $station) { ?>
							<?php
									$stationAnchor = rawurlencode((string) $station["id"]);
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
											<?php foreach ($prixCarburantsSelectionnes as $prixSelectionne) { ?>
												<div class="price-line">
													<span><?= texte_securise($prixSelectionne["name"]) ?></span>
													<strong><?= texte_securise(formater_prix((float) $prixSelectionne["value"])) ?></strong>
												</div>
											<?php } ?>
										</div>
							</div>

								<p class="meta-line">
									Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
									| flux <?= texte_securise($station["source"]) ?>
									<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== "") { ?>
										| prix mis à jour le <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
									<?php } ?>
									</p>

								<div class="form-actions station-actions">
									<?php if ($lienCarteStation !== "") { ?>
										<a class="secondary-btn" href="<?= texte_securise($lienCarteStation) ?>" target="_blank" rel="noopener">Voir sur une carte</a>
									<?php } ?>
								</div>
							</article>
						<?php } ?>
					</div>
					<div class="form-actions bottom-actions">
						<a class="cta-link" href="<?= texte_securise($searchLink) ?>">Retour à la recherche</a>
					</div>
				<?php } ?>
			</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
