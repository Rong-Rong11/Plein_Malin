<?php
/**
 * @file
 * @brief Page d'affichage et de filtrage des resultats.
 *
 * Page de resultats.
 *
 * Elle reconstitue le contexte de recherche, appelle l'API carburants via les
 * fonctions metier, affiche les stations, propose une vue synthese ou detaillee
 * et enregistre la consultation.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$carburantsSelectionnes = normaliser_carburants_selection($_GET["fuel"] ?? []);
// La vue personnalise l'affichage des cartes station : synthese ou detaillee.
$vue = ($_GET["view"] ?? "summary") === "detailed" ? "detailed" : "summary";
$tri = $_GET["sort"] ?? "price";
$rayonGeo = normaliser_rayon_geo((int) ($_GET["geo_radius"] ?? PM_DEFAULT_RADIUS));
$modeDepartement = isset($_GET["department_mode"]);
$utiliserGeo = isset($_GET["use_geo"]);

$region = $_GET["region"] ?? "";
$departement = $_GET["department"] ?? "";
$ville = $_GET["city"] ?? "";

$villeCourante = null;
$stations = [];
$geolocalisation = null;
$message = "Aucune recherche lancée.";
$infosDepartement = null;
$infosRegion = null;
$apiCarburantsErreur = false;
$choixRechercheIncomplet = !$utiliserGeo && !$modeDepartement && $departement !== "" && $ville === "";
$libelleDepartement = $departement;
$modeRayonManuel = false;

// Reconstitution du contexte geographique a partir des parametres recus.
if ($departement !== "") {
	$infosDepartement = trouver_departement($departement);
	if ($infosDepartement !== null) {
		$region = $infosDepartement["region_code"] ?? $region;
		$infosRegion = trouver_region($region);
		$libelleDepartement = $infosDepartement["department_name"] . " (" . $departement . ")";
	}
}

// Le mode geolocalise utilise l'IP ; les autres modes utilisent les CSV locaux.
if ($utiliserGeo) {
	$geolocalisation = recuperer_geolocalisation();
	if ((float) ($geolocalisation["latitude"] ?? 0) !== 0.0 || (float) ($geolocalisation["longitude"] ?? 0) !== 0.0) {
		$villeCourante = trouver_ville_plus_proche((float) $geolocalisation["latitude"], (float) $geolocalisation["longitude"]);
	}
} elseif ($modeDepartement && $departement !== "") {
	if ($infosDepartement !== null) {
		$region = $infosDepartement["region_code"] ?? $region;
		$infosRegion = trouver_region($region);
		$villeCourante = $ville !== "" ? trouver_ville($ville) : null;

		if ($villeCourante === null || $villeCourante["department_code"] !== $departement) {
			$villesDepartement = villes_par_departement($departement);
			$villeCourante = $villesDepartement[0] ?? [
				"city_code" => "",
				"city_name" => "Département " . $departement,
				"postal_code" => "",
				"department_code" => $departement,
				"latitude" => 0,
				"longitude" => 0,
			];
		}
	}
} elseif ($ville !== "") {
	$villeCourante = trouver_ville($ville);
}

// Une recherche manuelle sur une ville utilise aussi un rayon autour de cette ville.
if (!$utiliserGeo && !$modeDepartement && $villeCourante !== null) {
	$modeRayonManuel = true;
}

if ($villeCourante !== null) {
	$departement = $villeCourante["department_code"];
	$infosDepartement = trouver_departement($departement);
	$region = $infosDepartement["region_code"] ?? $region;
	$infosRegion = trouver_region($region);

	if (!$modeDepartement && $villeCourante["city_code"] !== "") {
		enregistrer_derniere_ville($villeCourante["city_code"]);
	} elseif ($modeDepartement && $departement !== "") {
		enregistrer_derniere_recherche("departement", $departement);
	}
	$origineRecherche = $utiliserGeo ? $geolocalisation : ($modeRayonManuel ? $villeCourante : null);
	$resultatRecherche = rechercher_stations_avec_statut($villeCourante, $carburantsSelectionnes, $tri, $modeDepartement, $origineRecherche, $rayonGeo);
	$stations = $resultatRecherche["stations"];
	$apiCarburantsErreur = $resultatRecherche["api_error"];

		enregistrer_consultation([
			"region" => $infosRegion["region_name"] ?? "",
			"department" => $infosDepartement["department_name"] ?? "",
			"city" => $modeDepartement ? "Département " . ($infosDepartement["department_name"] ?? $departement) : $villeCourante["city_name"],
			"mode" => mode_recherche($utiliserGeo, $modeDepartement),
			"view" => $vue,
			"fuel" => texte_carburants_selectionnes($carburantsSelectionnes),
			"station_count" => count($stations),
		]);
	}

// Les libelles suivants alimentent le panneau "Detail" de la recherche.
$message = message_resultats($villeCourante, $utiliserGeo, $modeDepartement, $stations);
if ($apiCarburantsErreur) {
	$message = "L'API officielle des carburants ne répond pas pour le moment.";
} elseif ($utiliserGeo && $villeCourante !== null) {
	$message = "Recherche autour de votre position approximative dans un rayon de " . $rayonGeo . " km.";
} elseif ($modeRayonManuel && $villeCourante !== null) {
	$message = "Recherche autour de " . $villeCourante["city_name"] . " dans un rayon de " . $rayonGeo . " km.";
} elseif ($utiliserGeo) {
	$message = "Impossible de trouver votre position approximative pour le moment.";
} elseif ($choixRechercheIncomplet) {
	$message = "Vous avez choisi le département " . $libelleDepartement . ". Sélectionnez une ville ou cochez \"Tout le département\" pour lancer la recherche.";
}

$libelleModeRecherche = "Ville";
if ($utiliserGeo) {
	$libelleModeRecherche = "Autour de moi";
} elseif ($modeDepartement) {
	$libelleModeRecherche = "Département";
}

$libelleTri = "Prix croissant";
if ($tri === "price_desc") {
	$libelleTri = "Prix décroissant";
} elseif ($tri === "distance") {
	$libelleTri = "Proximité";
} elseif ($tri === "name") {
	$libelleTri = "Nom";
}

$libelleVue = $vue === "detailed" ? "Détaillée" : "Synthèse";

$libelleCarburantsSelectionnes = texte_carburants_selectionnes($carburantsSelectionnes);
$libelleCibleRecherche = "Non défini";

if ($utiliserGeo) {
	$libelleCibleRecherche = "votre position approximative";
} elseif ($modeDepartement) {
	$libelleCibleRecherche = "tout le département " . ($infosDepartement["department_name"] ?? $departement);
	if ($departement !== "") {
		$libelleCibleRecherche .= " (" . $departement . ")";
	}
} elseif ($villeCourante !== null) {
	$libelleCibleRecherche = $villeCourante["city_name"];
	if ($modeRayonManuel) {
		$libelleCibleRecherche .= " dans un rayon de " . $rayonGeo . " km";
	}
}

$parametresRecherche = [
	"region" => $region,
	"department" => $departement,
	"city" => $ville,
	"fuel" => $carburantsSelectionnes,
	"view" => $vue,
	"sort" => $tri,
	"geo_radius" => $rayonGeo,
];

if ($modeDepartement) {
	$parametresRecherche["department_mode"] = "1";
}

if ($utiliserGeo) {
	$parametresRecherche["use_geo"] = "1";
}

$prixMoyenRecherche = null;
$meilleureStation = null;
$limiteStationsAffichees = PM_MAX_STATIONS_DISPLAYED;
$stationsAffichees = array_slice($stations, 0, $limiteStationsAffichees);
$stationsMasquees = max(0, count($stations) - count($stationsAffichees));
$prixRecherche = array_column($stationsAffichees, "main_price");

// Resume rapide des resultats affiches : moyenne et meilleur prix visible.
if ($prixRecherche !== []) {
	$prixMoyenRecherche = array_sum($prixRecherche) / count($prixRecherche);

	foreach ($stationsAffichees as $station) {
		if ($meilleureStation === null || (float) $station["main_price"] < (float) $meilleureStation["main_price"]) {
			$meilleureStation = $station;
		}
	}
}

enregistrer_parametres_derniere_recherche($parametresRecherche);

$lienRecherche = "recherche.php?" . http_build_query($parametresRecherche) . "#recherche";

$titrePage = "Résultats - Plein Malin";
$descriptionPage = "Résultats des stations-service et des prix.";
$pageActive = "resultats";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
			<section class="panel">
				<p class="eyebrow">Résultats</p>
				<h1>Stations pour <?= texte_securise($libelleCarburantsSelectionnes) ?></h1>
					<p class="lead">
						Consultez les stations trouvées puis revenez à la recherche si besoin.
					</p>
					<?php if ($utiliserGeo) { ?>
						<p class="lead">
							<?php if ($villeCourante !== null) { ?>
								Vous êtes approximativement à <strong><?= texte_securise($villeCourante["city_name"]) ?></strong>,
								d'après une position estimée à partir de l'adresse IP.
							<?php } elseif ($geolocalisation !== null && trim((string) ($geolocalisation["city"] ?? "")) !== "") { ?>
								Vous êtes approximativement à <strong><?= texte_securise((string) $geolocalisation["city"]) ?></strong>,
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
						<input type="hidden" name="department" value="<?= texte_securise($departement) ?>" />
						<input type="hidden" name="city" value="<?= texte_securise($ville) ?>" />
						<?php if ($modeDepartement) { ?>
							<input type="hidden" name="geo_radius" value="<?= texte_securise((string) $rayonGeo) ?>" />
						<?php } ?>
						<?php if ($modeDepartement) { ?>
							<input type="hidden" name="department_mode" value="1" />
						<?php } ?>
						<?php if ($utiliserGeo) { ?>
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
												<?= in_array($codeCarburant, $carburantsSelectionnes, true) ? 'checked="checked"' : "" ?> />
											<span><?= texte_securise($nomCarburant) ?></span>
										</label>
									<?php } ?>
								</span>
							</div>
							<div class="field-grid field-grid-main">
								<div class="field-card">
									<label class="field-title" for="result-sort-select">Tri</label>
									<select id="result-sort-select" name="sort">
										<option value="price" <?= $tri === "price" ? 'selected="selected"' : "" ?>>Prix croissant</option>
										<option value="price_desc" <?= $tri === "price_desc" ? 'selected="selected"' : "" ?>>Prix décroissant</option>
										<option value="distance" <?= $tri === "distance" ? 'selected="selected"' : "" ?>>Distance</option>
										<option value="name" <?= $tri === "name" ? 'selected="selected"' : "" ?>>Nom</option>
									</select>
								</div>
								<?php if (!$modeDepartement) { ?>
									<div class="field-card">
										<label class="field-title" for="result-radius-select">Rayon</label>
										<select id="result-radius-select" name="geo_radius">
											<?php foreach (rayons_geo_disponibles() as $rayon) { ?>
												<option value="<?= texte_securise((string) $rayon) ?>" <?= $rayonGeo === $rayon ? 'selected="selected"' : "" ?>>
													<?= texte_securise((string) $rayon) ?> km
												</option>
											<?php } ?>
										</select>
									</div>
								<?php } ?>
								<div class="field-card">
									<label class="field-title" for="result-view-select">Vue</label>
									<select id="result-view-select" name="view">
										<option value="summary" <?= $vue === "summary" ? 'selected="selected"' : "" ?>>Synthèse</option>
										<option value="detailed" <?= $vue === "detailed" ? 'selected="selected"' : "" ?>>Détaillée</option>
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
									<li>Mode : <?= texte_securise($libelleModeRecherche) ?></li>
									<li>Carburants choisis : <?= texte_securise($libelleCarburantsSelectionnes) ?></li>
									<li>Tri choisi : <?= texte_securise($libelleTri) ?></li>
									<li>Vue choisie : <?= texte_securise($libelleVue) ?></li>
									<li>Stations trouvées : <?= texte_securise((string) count($stations)) ?></li>
									<li>Périmètre : <?= texte_securise($libelleCibleRecherche) ?></li>
									<li>Région : <?= texte_securise($infosRegion["region_name"] ?? "Non définie") ?></li>
									<li>Département : <?= texte_securise($infosDepartement["department_name"] ?? "Non défini") ?><?= $departement !== "" ? " (" . texte_securise($departement) . ")" : "" ?></li>
									<?php if (!$modeDepartement) { ?>
										<li>Ville de référence : <?= texte_securise($villeCourante["city_name"] ?? "Non définie") ?></li>
									<?php } ?>
									<?php if (!$modeDepartement && $villeCourante !== null) { ?>
										<li>Code ville : <?= texte_securise($villeCourante["city_code"]) ?></li>
										<li>Code postal : <?= texte_securise($villeCourante["postal_code"]) ?></li>
									<?php } ?>
									<?php if ($utiliserGeo && $geolocalisation !== null) { ?>
										<li>Rayon géolocalisé : <?= texte_securise((string) $rayonGeo) ?> km</li>
										<li>Latitude : <?= texte_securise((string) $geolocalisation["latitude"]) ?></li>
										<li>Longitude : <?= texte_securise((string) $geolocalisation["longitude"]) ?></li>
										<li>Ville retournee par l'IP : <?= texte_securise($geolocalisation["city"]) ?></li>
										<li>Région retournée par l'IP : <?= texte_securise($geolocalisation["region"]) ?></li>
										<li>Source de localisation : <?= texte_securise($geolocalisation["source"]) ?></li>
									<?php } elseif ($modeRayonManuel) { ?>
										<li>Rayon choisi : <?= texte_securise((string) $rayonGeo) ?> km</li>
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
					<?php if ($villeCourante !== null && !$utiliserGeo && !$modeRayonManuel) { ?>
						<strong><?= texte_securise($libelleCibleRecherche) ?></strong>
					<?php } ?>
					</p>
				<?php if ($villeCourante === null) { ?>
					<p class="empty-state">
						<?= texte_securise($choixRechercheIncomplet ? "Choisissez une ville dans le département " . $libelleDepartement . " ou activez la recherche dans tout le département." : "Aucune recherche lancée.") ?>
					</p>
				<?php } elseif ($apiCarburantsErreur) { ?>
				<p class="empty-state">Impossible d'afficher les stations : l'API officielle des carburants ne répond pas. Réessayez plus tard.</p>
				<?php } elseif ($stations === []) { ?>
				<p class="empty-state">
					Aucune station trouvée avec ces critères. Essayez d'élargir le champ de recherche.
					<?php if ($modeRayonManuel) { ?>
						Vous pouvez aussi augmenter le rayon de recherche.
					<?php } ?>
				</p>
				<?php } else { ?>
					<p class="small-note"><?= texte_securise((string) count($stations)) ?> station(s) trouvée(s).</p>
					<?php if ($stationsMasquees > 0) { ?>
						<p class="small-note">
							Pour limiter le poids de la page et réduire sa complexité, seules les <?= texte_securise((string) count($stationsAffichees)) ?>
							premières stations sont affichées sur <?= texte_securise((string) count($stations)) ?> trouvées. Elles correspondent aux résultats
							<?= $tri === "price" ? "les moins chers" : "les plus pertinents selon le tri choisi" ?>.
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
							<a class="stat-chip best-price-link" href="#station-<?= texte_securise((string) $meilleureStation["id"]) ?>">
								<strong><?= texte_securise(formater_prix((float) $meilleureStation["main_price"])) ?></strong>
								<span>meilleur prix affiché - <?= texte_securise($meilleureStation["name"]) ?></span>
								<small>Cliquer pour voir la station</small>
							</a>
						</div>
					<?php } ?>

					<div class="cards">
						<?php foreach ($stationsAffichees as $station) { ?>
							<?php
								$ancreStation = (string) $station["id"];
								$nomStationAffiche = (string) $station["name"];
								$prixCarburantsSelectionnes = [];
								foreach ($carburantsSelectionnes as $carburantSelectionne) {
									if (isset($station["prices"][$carburantSelectionne])) {
										$prixCarburantsSelectionnes[] = $station["prices"][$carburantSelectionne];
									}
								}
								$latitudeStation = (float) ($station["latitude"] ?? 0);
								$longitudeStation = (float) ($station["longitude"] ?? 0);
								$lienCarteStation = "";
								if ($latitudeStation !== 0.0 || $longitudeStation !== 0.0) {
									$lienCarteStation = "https://www.openstreetmap.org/?mlat="
										. (string) $latitudeStation
										. "&mlon=" . (string) $longitudeStation
										. "#map=16/"
										. (string) $latitudeStation
										. "/"
										. (string) $longitudeStation;
								}
								$servicesStation = services_station_affichables($station["services"] ?? []);
								?>
							<article class="station-card" id="station-<?= texte_securise($ancreStation) ?>">
							<div class="station-top">
								<div>
										<h3><?= texte_securise($nomStationAffiche) ?></h3>
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

								<?php if ($vue === "summary") { ?>
									<details class="station-detail-disclosure">
										<summary class="secondary-btn detail-toggle station-detail-summary">Voir les détails</summary>
										<div class="station-detail-content">
											<p class="meta-line">
												Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
												| flux <?= texte_securise($station["source"]) ?>
												<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== "") { ?>
													| prix mis à jour le <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
												<?php } ?>
											</p>

											<div class="services-block">
												<p class="services-title">Services disponibles</p>
												<?php if ($servicesStation !== []) { ?>
													<div class="services-list">
														<?php foreach ($servicesStation as $serviceStation) { ?>
															<span class="badge service-badge"><?= texte_securise($serviceStation) ?></span>
														<?php } ?>
													</div>
												<?php } else { ?>
													<p class="small-note">Aucun service indique.</p>
												<?php } ?>
											</div>
										</div>
									</details>
								<?php } else { ?>
									<p class="meta-line">
										Distance : <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
										| flux <?= texte_securise($station["source"]) ?>
										<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== "") { ?>
											| prix mis à jour le <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
										<?php } ?>
										</p>

									<div class="services-block">
										<p class="services-title">Services disponibles</p>
										<?php if ($servicesStation !== []) { ?>
											<div class="services-list">
												<?php foreach ($servicesStation as $serviceStation) { ?>
													<span class="badge service-badge"><?= texte_securise($serviceStation) ?></span>
												<?php } ?>
											</div>
										<?php } else { ?>
											<p class="small-note">Aucun service indique.</p>
										<?php } ?>
									</div>
								<?php } ?>

								<div class="form-actions station-actions">
									<?php if ($lienCarteStation !== "") { ?>
										<a class="secondary-btn map-link-btn" href="<?= texte_securise($lienCarteStation) ?>" target="_blank" rel="noopener">Voir sur une carte</a>
									<?php } ?>
								</div>
							</article>
						<?php } ?>
					</div>
					<div class="form-actions bottom-actions">
						<a class="cta-link" href="<?= texte_securise($lienRecherche) ?>">Retour à la recherche</a>
					</div>
				<?php } ?>
			</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
