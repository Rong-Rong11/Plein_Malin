<?php
declare(strict_types=1);
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
$positionGeoValide = false;

// Reconstitution du contexte geographique a partir des parametres recus.
if ($departement !== "") {
	$infosDepartement = trouver_departement($departement);
	if ($infosDepartement !== null) {
		$region = $infosDepartement["region_code"] ?? $region;
		$infosRegion = trouver_region($region);
		$libelleDepartement = $infosDepartement["department_name"] . " (" . $departement . ")";
	}
}

// Le mode geolocalise utilise une position estimee ; les autres modes utilisent les CSV locaux.
if ($utiliserGeo) {
	$geolocalisation = recuperer_geolocalisation();
	$positionGeoValide = (float) ($geolocalisation["latitude"] ?? 0) !== 0.0 || (float) ($geolocalisation["longitude"] ?? 0) !== 0.0;
	if ($positionGeoValide) {
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

if ($villeCourante !== null || ($utiliserGeo && $positionGeoValide)) {
	if ($villeCourante !== null) {
		$departement = $villeCourante["department_code"];
		$infosDepartement = trouver_departement($departement);
		$region = $infosDepartement["region_code"] ?? $region;
		$infosRegion = trouver_region($region);
	}

	if ($villeCourante !== null && !$modeDepartement && $villeCourante["city_code"] !== "") {
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
			"city" => $modeDepartement ? "Département " . ($infosDepartement["department_name"] ?? $departement) : ($villeCourante["city_name"] ?? "Position géolocalisée"),
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
} elseif ($utiliserGeo && $positionGeoValide) {
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
	<main class="page">
			<section class="panel">
				<p class="eyebrow"><?= texte_securise("Résultats") ?></p>
				<h1><?= texte_securise("Stations pour") ?> <?= texte_securise($libelleCarburantsSelectionnes) ?></h1>
					<p class="lead">
						<?= texte_securise("Consultez les stations trouvées puis revenez à la recherche si besoin.") ?>
					</p>
					<?php if ($utiliserGeo) { ?>
						<p class="lead">
							<?php if ($villeCourante !== null) { ?>
								<?= texte_securise("Vous êtes approximativement à") ?> <strong><?= texte_securise($villeCourante["city_name"]) ?></strong>,
								<?= texte_securise("d'après une position estimée à partir de l'adresse IP.") ?>
							<?php } elseif ($geolocalisation !== null && trim((string) ($geolocalisation["city"] ?? "")) !== "") { ?>
								<?= texte_securise("Vous êtes approximativement à") ?> <strong><?= texte_securise((string) $geolocalisation["city"]) ?></strong>,
								<?= texte_securise("d'après une position estimée à partir de l'adresse IP.") ?>
							<?php } elseif ($positionGeoValide) { ?>
								<?= texte_securise("Recherche autour de la position estimée à partir de l'adresse IP.") ?>
							<?php } else { ?>
								<?= texte_securise("Position estimée indisponible pour le moment.") ?>
							<?php } ?>
						</p>
					<?php } ?>
					<div class="form-actions">
						<a class="cta-link" href="recherche.php?search_mode=manual"><?= texte_securise("Recherche manuelle") ?></a>
					</div>
					<div class="outils-resultats">
					<form action="resultats.php#resultats" method="get" class="formulaire-resultats search-form formulaire-structure">
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
						<div class="bloc-recherche recherche-simple">
							<div class="bloc-champ champ-large">
								<span class="field-title"><?= texte_securise("Carburants") ?></span>
								<span class="field-help"><?= texte_securise("Gazole par défaut si rien n'est coché.") ?></span>
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
							<div class="grille-champs grille-principale">
								<div class="bloc-champ">
									<label class="field-title" for="result-sort-select"><?= texte_securise("Tri") ?></label>
									<select id="result-sort-select" name="sort">
										<option value="price" <?= $tri === "price" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix croissant") ?></option>
										<option value="price_desc" <?= $tri === "price_desc" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix décroissant") ?></option>
										<option value="distance" <?= $tri === "distance" ? 'selected="selected"' : "" ?>><?= texte_securise("Distance") ?></option>
										<option value="name" <?= $tri === "name" ? 'selected="selected"' : "" ?>><?= texte_securise("Nom") ?></option>
									</select>
								</div>
								<?php if (!$modeDepartement) { ?>
									<div class="bloc-champ">
										<label class="field-title" for="result-radius-select"><?= texte_securise("Rayon") ?></label>
										<select id="result-radius-select" name="geo_radius">
											<?php foreach (rayons_geo_disponibles() as $rayon) { ?>
												<option value="<?= texte_securise((string) $rayon) ?>" <?= $rayonGeo === $rayon ? 'selected="selected"' : "" ?>>
													<?= texte_securise((string) $rayon) ?> km
												</option>
											<?php } ?>
										</select>
									</div>
								<?php } ?>
								<div class="bloc-champ">
									<label class="field-title" for="result-view-select"><?= texte_securise("Vue") ?></label>
									<select id="result-view-select" name="view">
										<option value="summary" <?= $vue === "summary" ? 'selected="selected"' : "" ?>><?= texte_securise("Synthèse") ?></option>
										<option value="detailed" <?= $vue === "detailed" ? 'selected="selected"' : "" ?>><?= texte_securise("Détaillée") ?></option>
									</select>
								</div>
							</div>
							<div class="form-actions action-buttons">
								<button type="submit"><?= texte_securise("Appliquer") ?></button>
							</div>
						</div>
					</form>
					<div class="form-actions results-detail-toggle">
						<details class="search-details">
							<summary class="detail-toggle"><?= texte_securise("Détail") ?></summary>
							<div class="search-details-box">
								<h2><?= texte_securise("Recherche actuelle") ?></h2>
								<ul class="liste-simple">
									<li><?= texte_securise("Mode") ?> : <?= texte_securise($libelleModeRecherche) ?></li>
									<li><?= texte_securise("Carburants choisis") ?> : <?= texte_securise($libelleCarburantsSelectionnes) ?></li>
									<li><?= texte_securise("Tri choisi") ?> : <?= texte_securise($libelleTri) ?></li>
									<li><?= texte_securise("Vue choisie") ?> : <?= texte_securise($libelleVue) ?></li>
									<li><?= texte_securise("Stations trouvées") ?> : <?= texte_securise((string) count($stations)) ?></li>
									<li><?= texte_securise("Périmètre") ?> : <?= texte_securise($libelleCibleRecherche) ?></li>
									<li><?= texte_securise("Région") ?> : <?= texte_securise($infosRegion["region_name"] ?? "Non définie") ?></li>
									<li><?= texte_securise("Département") ?> : <?= texte_securise($infosDepartement["department_name"] ?? "Non défini") ?><?= $departement !== "" ? " (" . texte_securise($departement) . ")" : "" ?></li>
									<?php if (!$modeDepartement) { ?>
										<li><?= texte_securise("Ville de référence") ?> : <?= texte_securise($villeCourante["city_name"] ?? "Non définie") ?></li>
									<?php } ?>
									<?php if (!$modeDepartement && $villeCourante !== null) { ?>
										<li><?= texte_securise("Code ville") ?> : <?= texte_securise($villeCourante["city_code"]) ?></li>
										<li><?= texte_securise("Code postal") ?> : <?= texte_securise($villeCourante["postal_code"]) ?></li>
									<?php } ?>
									<?php if ($utiliserGeo && $geolocalisation !== null) { ?>
										<li><?= texte_securise("Rayon géolocalisé") ?> : <?= texte_securise((string) $rayonGeo) ?> km</li>
										<li>Latitude : <?= texte_securise((string) $geolocalisation["latitude"]) ?></li>
										<li>Longitude : <?= texte_securise((string) $geolocalisation["longitude"]) ?></li>
										<li><?= texte_securise("Ville estimée par IP") ?> : <?= texte_securise($geolocalisation["city"] !== "" ? $geolocalisation["city"] : "Non définie") ?></li>
										<li><?= texte_securise("Région estimée par IP") ?> : <?= texte_securise($geolocalisation["region"] !== "" ? $geolocalisation["region"] : "Non définie") ?></li>
										<li><?= texte_securise("Source de localisation") ?> : <?= texte_securise($geolocalisation["source"]) ?></li>
									<?php } elseif ($modeRayonManuel) { ?>
										<li><?= texte_securise("Rayon") ?> : <?= texte_securise((string) $rayonGeo) ?> km</li>
									<?php } ?>
								</ul>
							</div>
						</details>
					</div>
					</div>
			</section>

		<section class="results-panel" id="resultats">
			<h2><?= texte_securise("Résultats") ?></h2>
				<p class="small-note">
					<?= texte_securise($message) ?>
					<?php if ($villeCourante !== null && !$utiliserGeo && !$modeRayonManuel) { ?>
						<strong><?= texte_securise($libelleCibleRecherche) ?></strong>
					<?php } ?>
					</p>
				<?php if ($villeCourante === null) { ?>
					<p class="message-vide">
						<?= texte_securise($choixRechercheIncomplet ? "Choisissez une ville dans le département " . $libelleDepartement . " ou activez la recherche dans tout le département." : "Aucune recherche lancée.") ?>
					</p>
				<?php } elseif ($apiCarburantsErreur) { ?>
				<p class="message-vide"><?= texte_securise("Impossible d'afficher les stations : l'API officielle des carburants ne répond pas. Réessayez plus tard.") ?></p>
				<?php } elseif ($stations === []) { ?>
				<p class="message-vide">
					<?= texte_securise("Aucune station trouvée avec ces critères.") ?>
					<?php if ($modeRayonManuel) { ?>
						<?= texte_securise("Vous pouvez aussi augmenter le rayon de recherche.") ?>
					<?php } ?>
				</p>
				<?php } else { ?>
					<p class="small-note"><?= texte_securise((string) count($stations)) ?> <?= texte_securise("station(s) trouvée(s).") ?></p>
					<?php if ($stationsMasquees > 0) { ?>
						<p class="small-note">
							<?= texte_securise("Pour limiter le poids de la page, seules les") ?> <?= texte_securise((string) count($stationsAffichees)) ?>
							<?= texte_securise("premières stations sont affichées.") ?> <?= texte_securise("Elles correspondent aux résultats") ?>
							<?= texte_securise($tri === "price" ? "les moins chers" : "les plus pertinents selon le tri choisi") ?>.
						</p>
					<?php } ?>
					<p class="small-note">
						<?= texte_securise("Les prix dépendent de la dernière mise à jour transmise par l'API officielle.") ?>
						<?= texte_securise("Certaines stations peuvent ne pas proposer tous les carburants sélectionnés.") ?>
					</p>

					<?php if ($prixMoyenRecherche !== null && $meilleureStation !== null) { ?>
						<div class="stats-inline result-summary">
							<div class="stat-chip">
								<strong><?= texte_securise(formater_prix($prixMoyenRecherche)) ?></strong>
								<span><?= texte_securise("prix moyen trouvé sur les stations affichées") ?></span>
							</div>
							<a class="stat-chip best-price-link" href="#station-<?= texte_securise((string) $meilleureStation["id"]) ?>">
								<strong><?= texte_securise(formater_prix((float) $meilleureStation["main_price"])) ?></strong>
								<span><?= texte_securise("meilleur prix affiché") ?> - <?= texte_securise($meilleureStation["name"]) ?></span>
								<small><?= texte_securise("Cliquer pour voir la station") ?></small>
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
										<summary class="secondary-btn detail-toggle station-detail-summary"><?= texte_securise("Voir les détails") ?></summary>
										<div class="station-detail-content">
											<p class="meta-line">
												<?= texte_securise("Distance :") ?> <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
												| <?= texte_securise("flux") ?> <?= texte_securise($station["source"]) ?>
												<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== "") { ?>
													| <?= texte_securise("prix mis à jour le") ?> <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
												<?php } ?>
											</p>

											<div class="services-block">
												<p class="services-title"><?= texte_securise("Services disponibles") ?></p>
												<?php if ($servicesStation !== []) { ?>
													<div class="services-list">
														<?php foreach ($servicesStation as $serviceStation) { ?>
															<span class="badge service-badge"><?= texte_securise($serviceStation) ?></span>
														<?php } ?>
													</div>
												<?php } else { ?>
													<p class="small-note"><?= texte_securise("Aucun service indique.") ?></p>
												<?php } ?>
											</div>
										</div>
									</details>
								<?php } else { ?>
									<p class="meta-line">
										<?= texte_securise("Distance :") ?> <?= texte_securise(number_format($station["distance"], 1, ",", " ")) ?> km
										| <?= texte_securise("flux") ?> <?= texte_securise($station["source"]) ?>
										<?php if (formater_date_heure($station["main_updated_at"] ?? "") !== "") { ?>
											| <?= texte_securise("prix mis à jour le") ?> <?= texte_securise(formater_date_heure($station["main_updated_at"])) ?>
										<?php } ?>
										</p>

									<div class="services-block">
										<p class="services-title"><?= texte_securise("Services disponibles") ?></p>
										<?php if ($servicesStation !== []) { ?>
											<div class="services-list">
												<?php foreach ($servicesStation as $serviceStation) { ?>
													<span class="badge service-badge"><?= texte_securise($serviceStation) ?></span>
												<?php } ?>
											</div>
										<?php } else { ?>
											<p class="small-note"><?= texte_securise("Aucun service indique.") ?></p>
										<?php } ?>
									</div>
								<?php } ?>

								<div class="form-actions station-actions">
									<?php if ($lienCarteStation !== "") { ?>
										<a class="secondary-btn map-link-btn" href="<?= texte_securise($lienCarteStation) ?>" target="_blank" rel="noopener"><?= texte_securise("Voir sur une carte") ?></a>
									<?php } ?>
								</div>
							</article>
						<?php } ?>
					</div>
					<div class="form-actions bottom-actions">
						<a class="cta-link" href="<?= texte_securise($lienRecherche) ?>"><?= texte_securise("Retour à la recherche") ?></a>
					</div>
				<?php } ?>
			</section>
	</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
