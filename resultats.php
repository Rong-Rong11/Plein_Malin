<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$fuel = $_GET["fuel"] ?? "Gazole";
$view = $_GET["view"] ?? "summary";
$sort = $_GET["sort"] ?? "price";
$departmentMode = isset($_GET["department_mode"]);
$useGeo = isset($_GET["use_geo"]);

$region = $_GET["region"] ?? "";
$department = $_GET["department"] ?? "";
$city = $_GET["city"] ?? "";

$currentCity = null;
$stations = [];
$geo = null;
$message = "Aucune recherche lancee.";

if ($useGeo) {
	$geo = recuperer_geolocalisation();
	$currentCity = trouver_ville_plus_proche((float) $geo["latitude"], (float) $geo["longitude"]);
} elseif ($city !== "") {
	$currentCity = trouver_ville($city);
}

if ($currentCity !== null) {
	$department = $currentCity["department_code"];
	$departmentInfo = trouver_departement($department);
	$region = $departmentInfo["region_code"] ?? $region;
	$regionInfo = trouver_region($region);

	enregistrer_derniere_ville($currentCity["city_code"]);
	$stations = rechercher_stations($currentCity, $fuel, $sort, $departmentMode);

	enregistrer_consultation([
		"region" => $regionInfo["region_name"] ?? "",
		"department" => $departmentInfo["department_name"] ?? "",
		"city" => $currentCity["city_name"],
		"mode" => $useGeo ? "geolocalisation" : ($departmentMode ? "departement" : "ville"),
		"view" => $view,
		"fuel" => $fuel,
		"station_count" => count($stations),
	]);

	if ($useGeo) {
		$message = "Recherche autour de votre position approximative.";
	} elseif ($departmentMode) {
		$message = "Recherche dans tout le departement selectionne.";
	} elseif ($stations === []) {
		$message = "Aucune station trouvee dans la ville selectionnee.";
	} else {
		$message = "Recherche dans la ville selectionnee.";
	}
}

$pageTitle = "Resultats - Plein Malin";
$pageDescription = "Resultats des stations-service et des prix.";
$activePage = "resultats";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
		<section class="panel">
			<p class="eyebrow">Resultats</p>
			<h1>Stations-service</h1>
			<p class="lead">
				Consultez les stations trouvees puis revenez a la recherche si besoin.
			</p>
			<div class="form-actions">
				<a class="cta-link" href="recherche.php?region=<?= texte_securise($region) ?>&amp;department=<?= texte_securise($department) ?>&amp;city=<?= texte_securise($city) ?>#recherche">Modifier ma recherche</a>
			</div>
		</section>

		<section class="results-panel" id="resultats">
			<h2>Resultats</h2>
			<p class="small-note">
				<?= texte_securise($message) ?>
				<?php if ($currentCity !== null): ?>
					<strong><?= texte_securise($currentCity["city_name"]) ?></strong>
				<?php endif; ?>
			</p>

			<?php if ($currentCity === null): ?>
				<p class="empty-state">Aucune recherche lancee.</p>
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
									<span><?= texte_securise($fuel) ?></span>
									<strong><?= texte_securise(formater_prix($station["main_price"])) ?></strong>
								</div>
							</div>

							<p class="meta-line">
								Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
								| flux <?= texte_securise($station["source"]) ?>
							</p>

							<?php if ($view === "detailed"): ?>
								<div class="details-grid">
									<div>
										<h4>Carburants</h4>
										<ul class="plain-list">
											<?php foreach ($station["prices"] as $price): ?>
												<li><?= texte_securise($price["name"]) ?> : <?= texte_securise(formater_prix((float) $price["value"])) ?></li>
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
