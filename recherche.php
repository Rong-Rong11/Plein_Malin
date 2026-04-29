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
$view = $_GET["view"] ?? "summary";
$sort = $_GET["sort"] ?? "price";
$geoRadius = normaliser_rayon_geo((int) ($_GET["geo_radius"] ?? 10));
$departmentMode = isset($_GET["department_mode"]);

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

if (array_intersect(["region", "department", "city", "fuel", "view", "sort", "geo_radius", "department_mode"], array_keys($_GET)) !== []) {
	enregistrer_parametres_derniere_recherche([
		"region" => $region,
		"department" => $department,
		"city" => $city,
		"fuel" => $selectedFuels,
		"view" => $view,
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
		<p class="lead">
			Choisissez une région sur la carte, puis un département, puis une ville.
		</p>
	</section>

	<section class="panel">
		<h2>Carte des régions</h2>
		<p class="small-note">
			Cliquez sur une région pour commencer.
			<?php if ($regionInfo !== null): ?>
				Région choisie : <strong><?= texte_securise($regionInfo["region_name"]) ?></strong>
			<?php endif; ?>
		</p>
		<div class="map-scroll">
			<img src="image/<?= $theme === "night" ? "map(dark).png" : "map(light).png" ?>" alt="Carte des régions de France"
				usemap="#regions-map" class="map-image" width="<?= texte_securise((string) $largeurCarte) ?>" height="<?= texte_securise((string) $hauteurCarte) ?>" />
		</div>
		<map name="regions-map">
			<area shape="rect" coords="<?= coordonnees_carte("252,314,423,380", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=53#recherche" alt="Bretagne" title="Bretagne" />
			<area shape="rect" coords="<?= coordonnees_carte("481,232,642,295", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=28#recherche" alt="Normandie" title="Normandie" />
			<area shape="rect" coords="<?= coordonnees_carte("749,83,917,179", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=32#recherche" alt="Hauts-de-France" title="Hauts-de-France" />
			<area shape="rect" coords="<?= coordonnees_carte("982,248,1168,320", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=44#recherche" alt="Grand Est" title="Grand Est" />
			<area shape="rect" coords="<?= coordonnees_carte("405,409,625,485", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=52#recherche" alt="Pays de la Loire" title="Pays de la Loire" />
			<area shape="rect" coords="<?= coordonnees_carte("654,387,860,487", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=24#recherche" alt="Centre-Val de Loire" title="Centre-Val de Loire" />
			<area shape="rect" coords="<?= coordonnees_carte("747,228,874,328", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=11#recherche" alt="Ile-de-France" title="Ile-de-France" />
			<area shape="rect" coords="<?= coordonnees_carte("902,423,1117,542", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=27#recherche" alt="Bourgogne-Franche-Comte" title="Bourgogne-Franche-Comte" />
			<area shape="rect" coords="<?= coordonnees_carte("543,636,729,733", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=75#recherche" alt="Nouvelle-Aquitaine" title="Nouvelle-Aquitaine" />
			<area shape="rect" coords="<?= coordonnees_carte("653,837,848,905", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=76#recherche" alt="Occitanie" title="Occitanie" />
			<area shape="rect" coords="<?= coordonnees_carte("831,617,1048,719", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=84#recherche" alt="Auvergne-Rhone-Alpes" title="Auvergne-Rhone-Alpes" />
			<area shape="rect" coords="<?= coordonnees_carte("989,781,1183,880", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=93#recherche" alt="Provence-Alpes-Cote d'Azur" title="Provence-Alpes-Cote d'Azur" />
			<area shape="rect" coords="<?= coordonnees_carte("1277,898,1384,955", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?region=94#recherche" alt="Corse" title="Corse" />
		</map>
	</section>

	<section class="panel" id="recherche">
		<h2>Formulaire</h2>
		<form action="resultats.php#resultats" method="get" class="search-form search-form-structured">
			<input type="hidden" name="region" value="<?= texte_securise($region) ?>" />

			<div class="search-section search-context">
				<p class="section-label">1. Contexte</p>
				<?php if ($regionInfo !== null): ?>
					<div class="context-card">
						<div>
							<p class="context-title">Région déjà choisie</p>
							<div class="region-badge"><?= texte_securise($regionInfo["region_name"]) ?></div>
						</div>
						<a href="recherche.php#carte" class="context-link">Changer sur la carte</a>
					</div>
				<?php else: ?>
					<div class="context-card">
						<div>
							<p class="context-title">Aucune région sélectionnée</p>
							<p class="small-note">Commencez par cliquer sur la carte au-dessus.</p>
						</div>
					</div>
				<?php endif; ?>
				<div class="geo-shortcut">
					<button type="submit" name="use_geo" value="1" class="secondary-btn">Autour de moi</button>
					<span class="small-note">Position approximative par IP.</span>
				</div>
			</div>

			<div class="search-section">
				<p class="section-label">2. Localisation precise</p>
				<div class="field-grid field-grid-main">
					<label class="field-card">
						<span class="field-title">Département</span>
						<span class="field-help">Choisissez d'abord le département de la région.</span>
						<select name="department"
							onchange="window.location.href='recherche.php?region=<?= texte_securise($region) ?>&amp;department=' + encodeURIComponent(this.value) + '#recherche'"
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
					</label>

					<label class="field-card">
						<span class="field-title">Ville</span>
						<span class="field-help">La liste dépend du département choisi.</span>
						<select name="city" <?= $department === "" ? 'disabled="disabled"' : "" ?>>
							<option value=""><?= $department === "" ? "Choisir d'abord un département" : "Choisir une ville" ?></option>
							<?php foreach ($cities as $uneVille): ?>
								<option value="<?= texte_securise($uneVille["city_code"]) ?>" <?= $city === $uneVille["city_code"] ? 'selected="selected"' : "" ?>>
									<?= texte_securise($uneVille["city_name"]) ?> (<?= texte_securise($uneVille["postal_code"]) ?>)
								</option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>

				<div class="search-section">
					<p class="section-label">3. Préférences et actions</p>
					<div class="field-grid field-grid-secondary">
						<div class="field-card field-card-soft field-card-wide">
							<span class="field-title">Carburants</span>
							<span class="field-help">Cochez un ou plusieurs carburants.</span>
							<span class="field-help">Si aucun carburant n'est sélectionné, le Gazole est utilisé par défaut.</span>
							<span class="fuel-choice-list">
								<?php foreach ($fuelLabels as $codeCarburant => $nomCarburant): ?>
									<label class="fuel-choice">
										<input type="checkbox" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
											<?= in_array($codeCarburant, $selectedFuels, true) ? 'checked="checked"' : "" ?> />
										<span><?= texte_securise($nomCarburant) ?></span>
									</label>
								<?php endforeach; ?>
							</span>
						</div>

						<label class="field-card field-card-soft">
							<span class="field-title">Vue</span>
							<select name="view">
								<option value="summary" <?= $view === "summary" ? 'selected="selected"' : "" ?>>Synthèse</option>
								<option value="detailed" <?= $view === "detailed" ? 'selected="selected"' : "" ?>>Détaillée</option>
							</select>
						</label>

						<label class="field-card field-card-soft">
							<span class="field-title">Tri</span>
							<select name="sort">
								<option value="price" <?= $sort === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
								<option value="distance" <?= $sort === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
								<option value="name" <?= $sort === "name" ? 'selected="selected"' : "" ?>>Nom</option>
							</select>
						</label>

						<label class="field-card field-card-soft">
							<span class="field-title">Mode département</span>
							<span class="field-help">Afficher les stations du département choisi.</span>
							<span class="fuel-choice">
								<input type="checkbox" name="department_mode" value="1" <?= $departmentMode ? 'checked="checked"' : "" ?>
									<?= $department === "" ? 'disabled="disabled"' : "" ?> />
								<span>Tout le département</span>
							</span>
						</label>
					</div>

					<div class="action-panel">
						<div class="action-copy">
							<p class="context-title">Lancer la recherche</p>
							<p class="small-note">Choisissez votre mode puis affichez les stations.</p>
						</div>
						<div class="form-actions action-buttons">
							<label class="inline-filter">
								<span>Rayon</span>
								<select name="geo_radius">
									<?php foreach (rayons_geo_disponibles() as $radius): ?>
										<option value="<?= texte_securise((string) $radius) ?>" <?= $geoRadius === $radius ? 'selected="selected"' : "" ?>>
											<?= texte_securise((string) $radius) ?> km
										</option>
									<?php endforeach; ?>
								</select>
							</label>
							<button type="submit">Rechercher</button>
							<button type="submit" name="use_geo" value="1" class="secondary-btn">Autour de moi</button>
							<span class="small-note geo-note">Position approximative par IP.</span>
							<a href="recherche.php?reset=1#recherche" class="secondary-btn reset-link">Réinitialiser</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
