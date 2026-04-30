<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$fuelLabels = liste_carburants();

if (isset($_GET["reset"])) {
	effacer_parametres_derniere_recherche();
	$_GET = [];
}

$region = $_GET["region"] ?? "";
$department = $_GET["department"] ?? "";
$city = $_GET["city"] ?? "";
$selectedFuels = normaliser_carburants_selection($_GET["fuel"] ?? []);
$sort = $_GET["sort"] ?? "price";
$geoRadius = normaliser_rayon_geo((int) ($_GET["geo_radius"] ?? 10));
$departmentMode = isset($_GET["department_mode"]);
$searchMode = $_GET["search_mode"] ?? "";

if ($searchMode !== "geo" && $searchMode !== "manual") {
	$searchMode = "manual";
}

if ($region === "") {
	$department = "";
	$city = "";
} else {
	if (!departement_existe_dans_region($department, $region)) {
		$department = "";
		$city = "";
	}

	if ($department !== "" && !ville_existe_dans_departement($city, $department)) {
		$city = "";
	}
}

$parametresMemorises = lire_parametres_derniere_recherche();
if (
	$city === ""
	&& $department !== ""
	&& ($parametresMemorises["region"] ?? "") === $region
	&& ($parametresMemorises["department"] ?? "") === $department
	&& ($parametresMemorises["city"] ?? "") !== ""
	&& ville_existe_dans_departement((string) $parametresMemorises["city"], $department)
) {
	$city = (string) $parametresMemorises["city"];
}

if (array_intersect(["region", "department", "city", "fuel", "sort", "geo_radius", "department_mode"], array_keys($_GET)) !== []) {
	enregistrer_parametres_derniere_recherche([
		"region" => $region,
		"department" => $department,
		"city" => $city,
		"fuel" => $selectedFuels,
		"sort" => $sort,
		"geo_radius" => $geoRadius,
		"department_mode" => $departmentMode ? "1" : null,
	]);
}

$departments = departements_par_region($region);
$cities = villes_par_departement($department);
$regionInfo = $region !== "" ? trouver_region($region) : null;
$largeurCarteOriginale = $theme === "night" ? 1536 : 1530;
$hauteurCarteOriginale = $theme === "night" ? 1024 : 1028;
$largeurCarte = (int) round($largeurCarteOriginale / 1.35);
$hauteurCarte = (int) round($hauteurCarteOriginale * ($largeurCarte / $largeurCarteOriginale));

function coordonnees_carte(string $coords, int $largeurOriginale, int $hauteurOriginale, int $largeurAffichee, int $hauteurAffichee): string
{
	$valeurs = array_map("trim", explode(",", $coords));
	$resultat = [];

	foreach ($valeurs as $index => $valeur) {
		$ratio = $index % 2 === 0 ? $largeurAffichee / $largeurOriginale : $hauteurAffichee / $hauteurOriginale;
		$resultat[] = (string) (int) round(((float) $valeur) * $ratio);
	}

	return implode(",", $resultat);
}

$pageTitle = "Recherche - Plein Malin";
$pageDescription = "Choisir une région, un département et une ville.";
$activePage = "recherche";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="panel" id="carte">
		<p class="eyebrow">Recherche principale</p>
		<h1>Rechercher une station</h1>
		<?php if ($searchMode === "geo"): ?>
			<p class="lead">
				Utilisez votre position approximative pour lancer une recherche rapide,
				sans passer par la carte ni le choix manuel de ville.
			</p>
		<?php else: ?>
			<p class="lead">
				Choisissez votre région, puis précisez le département et la ville dans le formulaire.
			</p>
		<?php endif; ?>
		<div class="search-mode-switch">
			<form action="recherche.php#recherche" method="get" class="mode-switch-form">
				<input type="hidden" name="search_mode" value="manual" />
				<button type="submit" class="mode-pill<?= $searchMode === "manual" ? " is-active" : "" ?>">Recherche manuelle</button>
			</form>
			<form action="recherche.php#recherche" method="get" class="mode-switch-form">
				<input type="hidden" name="search_mode" value="geo" />
				<button type="submit" class="mode-pill<?= $searchMode === "geo" ? " is-active" : "" ?>">Autour de moi</button>
			</form>
		</div>
	</section>

	<section class="panel" id="recherche">
		<h2>Formulaire</h2>
		<?php if ($searchMode === "geo"): ?>
			<form action="resultats.php#resultats" method="get" class="search-form search-form-structured">
				<input type="hidden" name="use_geo" value="1" />
				<div class="search-section search-plain">
					<p class="section-label">Recherche autour de moi</p>
					<div class="field-grid field-grid-secondary">
						<fieldset class="field-card field-card-soft field-card-wide">
							<legend class="field-title">Carburants</legend>
							<span class="fuel-choice-list">
								<?php foreach ($fuelLabels as $codeCarburant => $nomCarburant): ?>
									<label class="fuel-choice">
										<input type="checkbox" id="geo-fuel-<?= texte_securise(strtolower($codeCarburant)) ?>" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
											<?= in_array($codeCarburant, $selectedFuels, true) ? 'checked="checked"' : "" ?> />
										<span><?= texte_securise($nomCarburant) ?></span>
									</label>
								<?php endforeach; ?>
							</span>
						</fieldset>

						<div class="field-card field-card-soft">
							<label class="field-title" for="geo-sort-select">Tri</label>
							<select id="geo-sort-select" name="sort">
								<option value="price" <?= $sort === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
								<option value="price_desc" <?= $sort === "price_desc" ? 'selected="selected"' : "" ?>>Prix décroissant</option>
								<option value="distance" <?= $sort === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
								<option value="name" <?= $sort === "name" ? 'selected="selected"' : "" ?>>Nom</option>
							</select>
						</div>
					</div>

					<div class="action-panel">
						<div class="action-copy">
							<p class="context-title">Lancer la recherche</p>
							<p class="small-note">La position utilisée reste approximative car elle vient de l'adresse IP.</p>
						</div>
						<div class="form-actions action-buttons">
							<div class="inline-filter">
								<label for="geo-radius-select">Rayon</label>
								<select id="geo-radius-select" name="geo_radius">
									<?php foreach (rayons_geo_disponibles() as $radius): ?>
										<option value="<?= texte_securise((string) $radius) ?>" <?= $geoRadius === $radius ? 'selected="selected"' : "" ?>>
											<?= texte_securise((string) $radius) ?> km
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<button type="submit">Rechercher autour de moi</button>
							<a href="recherche.php?search_mode=geo&reset=1#recherche" class="secondary-btn reset-link">Réinitialiser</a>
						</div>
					</div>
				</div>
			</form>
		<?php else: ?>
			<form action="recherche.php#recherche" method="get" class="search-form search-form-structured">
				<input type="hidden" name="search_mode" value="manual" />
				<input type="hidden" name="region" value="<?= texte_securise($region) ?>" />

				<div class="search-section search-context">
					<p class="section-label">1. Région et localisation</p>
					<?php if ($regionInfo !== null): ?>
						<div class="context-card">
							<div>
								<p class="context-title">Région déjà choisie</p>
								<div class="region-badge"><?= texte_securise($regionInfo["region_name"]) ?></div>
							</div>
							<span class="context-link">Choisissez une autre région directement sur la carte ci-dessous.</span>
						</div>
					<?php else: ?>
						<div class="context-card">
							<div>
								<p class="context-title">Aucune région sélectionnée</p>
								<p class="small-note">Commencez par cliquer sur la carte ci-dessous.</p>
							</div>
						</div>
					<?php endif; ?>
					<div class="map-scroll">
						<img src="image/<?= $theme === "night" ? "map-dark-optimized.jpg" : "map-light-optimized.jpg" ?>" alt="Carte interactive des régions de France"
							usemap="#regions-map" class="map-image" width="<?= texte_securise((string) $largeurCarte) ?>" height="<?= texte_securise((string) $hauteurCarte) ?>" decoding="async" fetchpriority="high" />
					</div>
					<map name="regions-map">
						<area shape="rect" coords="<?= coordonnees_carte("252,314,423,380", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=53#recherche" alt="Bretagne" title="Bretagne" />
						<area shape="rect" coords="<?= coordonnees_carte("481,232,642,295", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=28#recherche" alt="Normandie" title="Normandie" />
						<area shape="rect" coords="<?= coordonnees_carte("749,83,917,179", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=32#recherche" alt="Hauts-de-France" title="Hauts-de-France" />
						<area shape="rect" coords="<?= coordonnees_carte("982,248,1168,320", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=44#recherche" alt="Grand Est" title="Grand Est" />
						<area shape="rect" coords="<?= coordonnees_carte("405,409,625,485", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=52#recherche" alt="Pays de la Loire" title="Pays de la Loire" />
						<area shape="rect" coords="<?= coordonnees_carte("654,387,860,487", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=24#recherche" alt="Centre-Val de Loire" title="Centre-Val de Loire" />
						<area shape="rect" coords="<?= coordonnees_carte("747,228,874,328", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=11#recherche" alt="Ile-de-France" title="Ile-de-France" />
						<area shape="rect" coords="<?= coordonnees_carte("902,423,1117,542", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=27#recherche" alt="Bourgogne-Franche-Comte" title="Bourgogne-Franche-Comte" />
						<area shape="rect" coords="<?= coordonnees_carte("543,636,729,733", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=75#recherche" alt="Nouvelle-Aquitaine" title="Nouvelle-Aquitaine" />
						<area shape="rect" coords="<?= coordonnees_carte("653,837,848,905", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=76#recherche" alt="Occitanie" title="Occitanie" />
						<area shape="rect" coords="<?= coordonnees_carte("831,617,1048,719", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=84#recherche" alt="Auvergne-Rhone-Alpes" title="Auvergne-Rhone-Alpes" />
						<area shape="rect" coords="<?= coordonnees_carte("989,781,1183,880", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=93#recherche" alt="Provence-Alpes-Cote d'Azur" title="Provence-Alpes-Cote d'Azur" />
						<area shape="rect" coords="<?= coordonnees_carte("1277,898,1384,955", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&region=94#recherche" alt="Corse" title="Corse" />
					</map>
					<div class="field-grid field-grid-main">
						<div class="field-card">
							<label class="field-title" for="department-select">Département</label>
							<span class="field-help">Choisissez un département.</span>
							<select id="department-select" name="department" onchange="this.form.action='recherche.php#form-end'; this.form.submit();"
								<?= $region === "" ? 'disabled="disabled"' : "" ?>>
								<option value=""><?= $region === "" ? "Choisir d'abord une région" : "Choisir un département" ?></option>
								<?php foreach ($departments as $unDepartment): ?>
									<option value="<?= texte_securise($unDepartment["department_code"]) ?>"
										<?= $department === $unDepartment["department_code"] ? 'selected="selected"' : "" ?>>
										<?= texte_securise($unDepartment["department_name"]) ?>
										(<?= texte_securise($unDepartment["department_code"]) ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="field-card">
							<label class="field-title" for="city-select">Ville</label>
							<span class="field-help">Choisissez une ville.</span>
							<select id="city-select" name="city" <?= $department === "" ? 'disabled="disabled"' : "" ?>>
								<option value=""><?= $department === "" ? "Choisir d'abord un département" : "Choisir une ville" ?></option>
								<?php foreach ($cities as $uneVille): ?>
									<option value="<?= texte_securise($uneVille["city_code"]) ?>" <?= $city === $uneVille["city_code"] ? 'selected="selected"' : "" ?>>
										<?= texte_securise($uneVille["city_name"]) ?> (<?= texte_securise($uneVille["postal_code"]) ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

				</div>

				<div class="search-section search-plain">
					<p class="section-label">2. Préférences et actions</p>
						<div class="field-grid field-grid-secondary">
							<fieldset class="field-card field-card-soft field-card-wide">
								<legend class="field-title">Carburants</legend>
								<span class="fuel-choice-list">
									<?php foreach ($fuelLabels as $codeCarburant => $nomCarburant): ?>
										<label class="fuel-choice">
											<input type="checkbox" id="fuel-<?= texte_securise(strtolower($codeCarburant)) ?>" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
												<?= in_array($codeCarburant, $selectedFuels, true) ? 'checked="checked"' : "" ?> />
											<span><?= texte_securise($nomCarburant) ?></span>
										</label>
									<?php endforeach; ?>
								</span>
							</fieldset>

							<div class="field-card field-card-soft">
								<label class="field-title" for="sort-select">Tri</label>
								<select id="sort-select" name="sort">
									<option value="price" <?= $sort === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
									<option value="price_desc" <?= $sort === "price_desc" ? 'selected="selected"' : "" ?>>Prix décroissant</option>
									<option value="distance" <?= $sort === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
									<option value="name" <?= $sort === "name" ? 'selected="selected"' : "" ?>>Nom</option>
								</select>
							</div>

							<div class="field-card field-card-soft">
								<span class="field-title" id="department-mode-title">Mode département</span>
								<span class="field-help">Rechercher dans tout le département.</span>
								<span class="fuel-choice">
									<input type="checkbox" id="department-mode" name="department_mode" value="1" aria-labelledby="department-mode-title"
										<?= $departmentMode ? 'checked="checked"' : "" ?>
										<?= $department === "" ? 'disabled="disabled"' : "" ?> />
									<label for="department-mode">Tout le département</label>
								</span>
							</div>
						</div>

						<div class="action-panel" id="form-end">
							<div class="action-copy">
								<p class="context-title">Lancer la recherche</p>
								<p class="small-note">Affichez les stations selon vos critères.</p>
							</div>
							<div class="form-actions action-buttons">
								<button type="submit" formaction="resultats.php#resultats">Rechercher</button>
								<a href="recherche.php?search_mode=manual&reset=1#recherche" class="secondary-btn reset-link">Réinitialiser</a>
							</div>
						</div>
				</div>
			</form>
		<?php endif; ?>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
