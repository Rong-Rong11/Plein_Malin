<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$regions = lire_regions();
$fuelLabels = liste_carburants();

$region = $_GET["region"] ?? "";
$department = $_GET["department"] ?? "";
$city = $_GET["city"] ?? "";
$fuel = $_GET["fuel"] ?? "Gazole";
$view = $_GET["view"] ?? "summary";
$sort = $_GET["sort"] ?? "price";
$departmentMode = isset($_GET["department_mode"]);
$useGeo = isset($_GET["use_geo"]);

if ($city === "" && !isset($_GET["region"]) && !isset($_GET["department"]) && !$useGeo) {
	$city = lire_derniere_ville();
}

$currentCity = null;
$geo = null;
$message = "Selectionnez une ville ou utilisez la geolocalisation.";
$stations = [];

if (!$useGeo) {
	if ($region === "") {
		$department = "";
		$city = "";
	} else {
		$departementsRegion = departements_par_region($region);
		$departementValide = false;

		foreach ($departementsRegion as $unDepartement) {
			if ($unDepartement["department_code"] === $department) {
				$departementValide = true;
			}
		}

		if (!$departementValide) {
			$department = "";
			$city = "";
		}

		if ($department === "") {
			$city = "";
		} else {
			$villesDepartement = villes_par_departement($department);
			$villeValide = false;

			foreach ($villesDepartement as $uneVille) {
				if ($uneVille["city_code"] === $city) {
					$villeValide = true;
				}
			}

			if (!$villeValide) {
				$city = "";
			}
		}
	}
}

if ($useGeo) {
	$geo = recuperer_geolocalisation();
	$currentCity = trouver_ville_plus_proche((float) $geo["latitude"], (float) $geo["longitude"]);

	if ($currentCity !== null) {
		$city = $currentCity["city_code"];
		$department = $currentCity["department_code"];
		$departmentInfo = trouver_departement($department);
		if ($departmentInfo !== null) {
			$region = $departmentInfo["region_code"];
		}
		$message = "Recherche autour de votre position approximative.";
	}
}

if (!$useGeo && $city !== "") {
	$currentCity = trouver_ville($city);
}

if ($currentCity !== null) {
	$department = $currentCity["department_code"];
	$departmentInfo = trouver_departement($department);
	if ($departmentInfo !== null) {
		$region = $departmentInfo["region_code"];
	}

	enregistrer_derniere_ville($currentCity["city_code"]);
	$stations = rechercher_stations($currentCity, $fuel, $sort, $departmentMode);

	$regionInfo = null;
	if ($departmentInfo !== null) {
		$regionInfo = trouver_region($departmentInfo["region_code"]);
	}

	enregistrer_consultation([
		"region" => $regionInfo["region_name"] ?? "",
		"department" => $departmentInfo["department_name"] ?? "",
		"city" => $currentCity["city_name"],
		"mode" => $useGeo ? "geolocalisation" : ($departmentMode ? "departement" : "ville"),
		"view" => $view,
		"fuel" => $fuel,
		"station_count" => count($stations),
	]);

	if (!$useGeo) {
		$message = "Recherche dans la ville selectionnee.";
	}
}

$departments = departements_par_region($region);
$cities = villes_par_departement($department);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Plein Malin</title>
	<meta name="description" content="Recherche simple de stations-service et de prix des carburants.">
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="image/favicon.ico" type="image/x-icon">
</head>
<body class="theme-<?= texte_securise($theme) ?>">
	<div id="top"></div>

	<header class="site-header">
		<div class="brand-row">
			<a class="brand-link" href="index.php">
				<img class="logo" src="image/<?= $theme === "night" ? "logoblanc.svg" : "logonoir.svg" ?>" alt="Logo Plein Malin">
			</a>
			<div class="theme-switch">
				<a href="?theme=day" class="theme-link">
					<img src="image/theme-day.svg" alt="">
					<span>Jour</span>
				</a>
				<a href="?theme=night" class="theme-link">
					<img src="image/theme-night.svg" alt="">
					<span>Nuit</span>
				</a>
			</div>
		</div>

		<nav class="main-nav">
			<a href="index.php">Accueil</a>
			<a href="stats.php">Statistiques</a>
			<a href="tech.php">Page tech</a>
		</nav>
	</header>

	<main class="page-shell">
		<section class="panel">
			<p class="eyebrow">Projet web carburants</p>
			<h1>Plein Malin</h1>
			<p class="lead">
				Choisissez une region, un departement puis une ville pour afficher les stations
				et les prix. La ville consultee peut etre retenue dans un cookie.
			</p>
			<p class="small-note">
				Theme actuel : <?= texte_securise(nom_theme($theme)) ?>
				<?php if ($currentCity !== null): ?>
					| derniere ville : <?= texte_securise($currentCity["city_name"]) ?>
				<?php endif; ?>
			</p>
		</section>

		<section class="panel">
			<h2>Carte des regions</h2>
			<img src="image/france-map-diagram.svg" alt="Carte simplifiee des regions de France" usemap="#regions-map" class="map-image">
			<map name="regions-map">
				<area shape="rect" coords="86,186,234,275" href="?region=53" alt="Bretagne">
				<area shape="rect" coords="255,124,420,201" href="?region=28" alt="Normandie">
				<area shape="rect" coords="473,78,629,154" href="?region=32" alt="Hauts-de-France">
				<area shape="rect" coords="648,123,826,246" href="?region=44" alt="Grand Est">
				<area shape="rect" coords="247,226,417,313" href="?region=52" alt="Pays de la Loire">
				<area shape="rect" coords="434,220,603,312" href="?region=24" alt="Centre-Val de Loire">
				<area shape="rect" coords="531,167,611,218" href="?region=11" alt="Ile-de-France">
				<area shape="rect" coords="620,246,781,347" href="?region=27" alt="Bourgogne-Franche-Comte">
				<area shape="rect" coords="210,335,442,515" href="?region=75" alt="Nouvelle-Aquitaine">
				<area shape="rect" coords="454,428,678,548" href="?region=76" alt="Occitanie">
				<area shape="rect" coords="653,362,827,491" href="?region=84" alt="Auvergne-Rhone-Alpes">
				<area shape="rect" coords="783,468,899,548" href="?region=93" alt="Provence-Alpes-Cote d'Azur">
				<area shape="rect" coords="845,579,906,667" href="?region=94" alt="Corse">
			</map>
		</section>

		<section class="panel">
			<h2>Recherche</h2>
			<form method="get" class="search-form">
				<label>
					Region
					<select name="region" onchange="this.form.submit()">
						<option value="">Choisir une region</option>
						<?php foreach ($regions as $uneRegion): ?>
							<option value="<?= texte_securise($uneRegion["region_code"]) ?>" <?= $region === $uneRegion["region_code"] ? "selected" : "" ?>>
								<?= texte_securise($uneRegion["region_name"]) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					Departement
					<select name="department" onchange="this.form.submit()" <?= $region === "" ? "disabled" : "" ?>>
						<option value=""><?= $region === "" ? "Choisir d'abord une region" : "Choisir un departement" ?></option>
						<?php foreach ($departments as $unDepartment): ?>
							<option value="<?= texte_securise($unDepartment["department_code"]) ?>" <?= $department === $unDepartment["department_code"] ? "selected" : "" ?>>
								<?= texte_securise($unDepartment["department_name"]) ?> (<?= texte_securise($unDepartment["department_code"]) ?>)
							</option>
							<?php endforeach; ?>
						</select>
					</label>

				<label>
					Ville
					<select name="city" <?= $department === "" ? "disabled" : "" ?>>
						<option value=""><?= $department === "" ? "Choisir d'abord un departement" : "Choisir une ville" ?></option>
						<?php foreach ($cities as $uneVille): ?>
							<option value="<?= texte_securise($uneVille["city_code"]) ?>" <?= $city === $uneVille["city_code"] ? "selected" : "" ?>>
								<?= texte_securise($uneVille["city_name"]) ?> (<?= texte_securise($uneVille["postal_code"]) ?>)
								</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					Carburant
					<select name="fuel">
						<?php foreach ($fuelLabels as $codeCarburant => $nomCarburant): ?>
						    <option value="<?= texte_securise($codeCarburant) ?>" <?= $fuel === $codeCarburant ? "selected" : "" ?>>
						        <?= texte_securise($nomCarburant) ?>
						    </option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					Vue
					<select name="view">
						<option value="summary" <?= $view === "summary" ? "selected" : "" ?>>Synthese</option>
						<option value="detailed" <?= $view === "detailed" ? "selected" : "" ?>>Detaillee</option>
					</select>
				</label>

				<label>
					Tri
					<select name="sort">
						<option value="price" <?= $sort === "price" ? "selected" : "" ?>>Prix croissant</option>
						<option value="distance" <?= $sort === "distance" ? "selected" : "" ?>>Proximite</option>
						<option value="name" <?= $sort === "name" ? "selected" : "" ?>>Nom</option>
					</select>
				</label>

				<label class="checkbox-row">
					<input type="checkbox" name="department_mode" value="1" <?= $departmentMode ? "checked" : "" ?>>
					Recherche dans tout le departement
				</label>

				<div class="form-actions">
					<button type="submit">Rechercher</button>
					<button type="submit" name="use_geo" value="1" class="secondary-btn">Autour de moi</button>
				</div>
			</form>
		</section>

		<section class="results-panel">
			<h2>Resultats</h2>
			<p class="small-note">
				<?= texte_securise($message) ?>
				<?php if ($currentCity !== null): ?>
					<strong><?= texte_securise($currentCity["city_name"]) ?></strong>
				<?php endif; ?>
				<?php if ($geo !== null): ?>
					| source geo <?= texte_securise($geo["source"]) ?>
				<?php endif; ?>
			</p>

			<?php if ($currentCity === null): ?>
				<p class="empty-state">Aucune recherche lancee.</p>
			<?php elseif ($stations === []): ?>
				<p class="empty-state">Aucune station trouvee avec ces criteres.</p>
			<?php else: ?>
				<p class="small-note"><?= count($stations) ?> station(s) trouvee(s).</p>

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

	<footer class="site-footer">
		<div class="footer-links">
			<a href="stats.php">Statistiques</a>
			<a href="tech.php">Page tech</a>
			<a href="#top" class="back-top">
				<img src="image/back_top.png" alt="Retour en haut">
			</a>
		</div>
		<p>Enzo Phung | CY Cergy Paris Universite | Projet Web 2025-2026</p>
	</footer>
</body>
</html>
