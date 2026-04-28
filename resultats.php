<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$selectedFuels = normaliser_carburants_selection($_GET["fuel"] ?? []);
$view = $_GET["view"] ?? "summary";
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
$message = "Aucune recherche lancee.";
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
				"city_name" => "Departement " . $department,
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
		"city" => $departmentMode ? "Departement " . ($departmentInfo["department_name"] ?? $department) : $currentCity["city_name"],
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
	$message = "Vous avez choisi le departement " . $departmentLabel . ". Selectionnez une ville ou cochez \"Tout le departement\" pour lancer la recherche.";
}

$searchModeLabel = "Ville";
if ($useGeo) {
	$searchModeLabel = "Autour de moi";
} elseif ($departmentMode) {
	$searchModeLabel = "Departement";
}

$sortLabel = "Prix croissant";
if ($sort === "distance") {
	$sortLabel = "Proximite";
} elseif ($sort === "name") {
	$sortLabel = "Nom";
}

$viewLabel = $view === "detailed" ? "Detaillee" : "Synthese";
$selectedFuelsLabel = texte_carburants_selectionnes($selectedFuels);
$searchTargetLabel = "Non defini";

if ($useGeo) {
	$searchTargetLabel = "votre position approximative";
} elseif ($departmentMode) {
	$searchTargetLabel = "tout le departement " . ($departmentInfo["department_name"] ?? $department);
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

enregistrer_parametres_derniere_recherche($searchParameters);

$searchLink = "recherche.php?" . http_build_query($searchParameters) . "#recherche";

$pageTitle = "Resultats - Plein Malin";
$pageDescription = "Resultats des stations-service et des prix.";
$activePage = "resultats";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
			<section class="panel">
				<p class="eyebrow">Resultats</p>
				<h1>Stations pour <?= texte_securise($selectedFuelsLabel) ?></h1>
					<p class="lead">
						Consultez les stations trouvees puis revenez a la recherche si besoin.
					</p>
				<div class="form-actions">
						<a class="cta-link" href="<?= texte_securise($searchLink) ?>">Modifier ma recherche</a>
					<details class="search-details">
						<summary class="detail-toggle">Detail</summary>
						<div class="search-details-box">
							<h2>Recherche actuelle</h2>
								<ul class="plain-list">
									<li>Mode : <?= texte_securise($searchModeLabel) ?></li>
										<li>Carburants choisis : <?= texte_securise($selectedFuelsLabel) ?></li>
									<li>Tri choisi : <?= texte_securise($sortLabel) ?></li>
									<li>Vue choisie : <?= texte_securise($viewLabel) ?></li>
									<li>Stations trouvees : <?= texte_securise((string) count($stations)) ?></li>
									<li>Perimetre : <?= texte_securise($searchTargetLabel) ?></li>
									<li>Region : <?= texte_securise($regionInfo["region_name"] ?? "Non definie") ?></li>
									<li>Departement : <?= texte_securise($departmentInfo["department_name"] ?? "Non defini") ?><?= $department !== "" ? " (" . texte_securise($department) . ")" : "" ?></li>
									<?php if (!$departmentMode): ?>
										<li>Ville de reference : <?= texte_securise($currentCity["city_name"] ?? "Non definie") ?></li>
									<?php endif; ?>
									<?php if (!$departmentMode && $currentCity !== null): ?>
										<li>Code ville : <?= texte_securise($currentCity["city_code"]) ?></li>
										<li>Code postal : <?= texte_securise($currentCity["postal_code"]) ?></li>
									<?php endif; ?>
								<?php if ($useGeo && $geo !== null): ?>
									<li>Rayon geolocalise : <?= texte_securise((string) $geoRadius) ?> km</li>
									<li>Latitude : <?= texte_securise((string) $geo["latitude"]) ?></li>
									<li>Longitude : <?= texte_securise((string) $geo["longitude"]) ?></li>
									<li>Ville retournee par l'IP : <?= texte_securise($geo["city"]) ?></li>
									<li>Region retournee par l'IP : <?= texte_securise($geo["region"]) ?></li>
									<li>Source de localisation : <?= texte_securise($geo["source"]) ?></li>
								<?php endif; ?>
							</ul>
							<?php if ($useGeo && $stations !== []): ?>
								<h2>Stations retenues</h2>
								<ul class="plain-list">
									<?php foreach ($stations as $station): ?>
										<li>
											<?= texte_securise($station["name"]) ?>
											<?php if ($station["address"] !== ""): ?>
												- <?= texte_securise($station["address"]) ?>
											<?php endif; ?>
											<?php if ($station["postal_code"] !== "" || $station["city_name"] !== ""): ?>
												(<?= texte_securise(trim($station["postal_code"] . " " . $station["city_name"])) ?>)
											<?php endif; ?>
											- <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</details>
				</div>
			</section>

		<section class="results-panel" id="resultats">
			<h2>Resultats</h2>
				<p class="small-note">
					<?= texte_securise($message) ?>
					<?php if ($currentCity !== null): ?>
						<strong><?= texte_securise($searchTargetLabel) ?></strong>
					<?php endif; ?>
				</p>
				<?php if ($useGeo): ?>
					<p class="small-note">Position estimee a partir de l'adresse IP.</p>
				<?php endif; ?>

				<?php if ($currentCity === null): ?>
					<p class="empty-state">
						<?= texte_securise($choixRechercheIncomplet ? "Choisissez une ville dans le departement " . $departmentLabel . " ou activez la recherche dans tout le departement." : "Aucune recherche lancee.") ?>
					</p>
				<?php elseif ($stations === []): ?>
				<p class="empty-state">Aucune station trouvee avec ces criteres.</p>
			<?php else: ?>
				<p class="small-note"><?= texte_securise((string) count($stations)) ?> station(s) trouvee(s).</p>

				<div class="cards">
					<?php foreach ($stations as $station): ?>
						<article class="station-card">
							<div class="station-top">
								<div>
									<h3><?= texte_securise($station["name"]) ?></h3>
									<p><?= texte_securise($station["address"]) ?>, <?= texte_securise($station["postal_code"]) ?> <?= texte_securise($station["city_name"]) ?></p>
								</div>
									<div class="price-box">
										<span><?= texte_securise($station["main_fuel"] !== "" ? $station["main_fuel"] : $selectedFuelsLabel) ?></span>
										<strong><?= texte_securise(formater_prix($station["main_price"])) ?></strong>
									</div>
							</div>

								<p class="meta-line">
									Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
									| flux <?= texte_securise($station["source"]) ?>
									<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== ""): ?>
										| prix mis a jour le <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
									<?php endif; ?>
								</p>

							<?php if ($view === "detailed"): ?>
								<div class="details-grid">
									<div>
										<h4>Carburants</h4>
										<ul class="plain-list">
												<?php foreach ($station["prices"] as $price): ?>
													<li>
														<?= texte_securise($price["name"]) ?> :
														<?= texte_securise(formater_prix((float) $price["value"])) ?>
														<?php if (formater_date_heure($price["updated_at"] ?? "") !== ""): ?>
															(mis a jour le <?= texte_securise(formater_date_heure($price["updated_at"])) ?>)
														<?php endif; ?>
													</li>
												<?php endforeach; ?>
										</ul>
									</div>
									<div>
										<h4>Services</h4>
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
			<?php endif; ?>
		</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
