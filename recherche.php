<?php
/**
 * @file
 * @brief Formulaire de recherche des stations.
 *
 * Page de recherche.
 *
 * Elle gere deux parcours : la recherche manuelle par region/departement/ville
 * et la recherche autour de la position approximative de l'utilisateur, avec
 * memorisation de la vue synthese ou detaillee choisie.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$libellesCarburants = liste_carburants();

// Le bouton de reinitialisation efface les criteres memorises et repart d'un GET vide.
if (isset($_GET["reset"])) {
	effacer_parametres_derniere_recherche();
	$_GET = [];
}

// Validation defensive des parametres GET pour eviter les combinaisons incoherentes.
$region = $_GET["region"] ?? "";
$departement = $_GET["department"] ?? "";
$ville = $_GET["city"] ?? "";
$carburantsSelectionnes = normaliser_carburants_selection($_GET["fuel"] ?? []);
// Cette preference sera transmise a la page resultats et memorisee dans le cookie.
$vue = ($_GET["view"] ?? "summary") === "detailed" ? "detailed" : "summary";
$tri = $_GET["sort"] ?? "price";
$rayonGeo = normaliser_rayon_geo((int) ($_GET["geo_radius"] ?? PM_DEFAULT_RADIUS));
$modeDepartement = isset($_GET["department_mode"]);
$modeRecherche = $_GET["search_mode"] ?? "";

if ($modeRecherche !== "geo" && $modeRecherche !== "manual") {
	$modeRecherche = "manual";
}

if ($region === "") {
	$departement = "";
	$ville = "";
} else {
	if (!departement_existe_dans_region($departement, $region)) {
		$departement = "";
		$ville = "";
	}

	if ($departement !== "" && !ville_existe_dans_departement($ville, $departement)) {
		$ville = "";
	}
}

// Si l'utilisateur revient sur un departement, on restaure la ville memorisee.
$parametresMemorises = lire_parametres_derniere_recherche();
if (
	$ville === ""
	&& $departement !== ""
	&& ($parametresMemorises["region"] ?? "") === $region
	&& ($parametresMemorises["department"] ?? "") === $departement
	&& ($parametresMemorises["city"] ?? "") !== ""
	&& ville_existe_dans_departement((string) $parametresMemorises["city"], $departement)
) {
	$ville = (string) $parametresMemorises["city"];
}

if (!isset($_GET["view"]) && isset($parametresMemorises["view"])) {
	$vue = $parametresMemorises["view"] === "detailed" ? "detailed" : "summary";
}

if (array_intersect(["region", "department", "city", "fuel", "view", "sort", "geo_radius", "department_mode"], array_keys($_GET)) !== []) {
	enregistrer_parametres_derniere_recherche([
		"region" => $region,
		"department" => $departement,
		"city" => $ville,
		"fuel" => $carburantsSelectionnes,
		"view" => $vue,
		"sort" => $tri,
		"geo_radius" => $rayonGeo,
		"department_mode" => $modeDepartement ? "1" : null,
	]);
}

$departements = departements_par_region($region);
$villes = villes_par_departement($departement);
$infosRegion = $region !== "" ? trouver_region($region) : null;
$largeurCarteOriginale = $theme === "night" ? 1536 : 1530;
$hauteurCarteOriginale = $theme === "night" ? 1024 : 1028;
$largeurCarte = (int) round($largeurCarteOriginale / 1.35);
$hauteurCarte = (int) round($hauteurCarteOriginale * ($largeurCarte / $largeurCarteOriginale));

/**
 * Adapte les coordonnees de la carte cliquable a la taille affichee.
 *
 * @param string $coordonnees Coordonnees originales separees par des virgules.
 * @param int $largeurOriginale Largeur de l'image source.
 * @param int $hauteurOriginale Hauteur de l'image source.
 * @param int $largeurAffichee Largeur affichee dans la page.
 * @param int $hauteurAffichee Hauteur affichee dans la page.
 * @return string Coordonnees redimensionnees pour la balise area.
 */
function coordonnees_carte(string $coordonnees, int $largeurOriginale, int $hauteurOriginale, int $largeurAffichee, int $hauteurAffichee): string
{
	$valeurs = array_map("trim", explode(",", $coordonnees));
	$resultat = [];

	foreach ($valeurs as $indice => $valeur) {
		$ratio = $indice % 2 === 0 ? $largeurAffichee / $largeurOriginale : $hauteurAffichee / $hauteurOriginale;
		$resultat[] = (string) (int) round(((float) $valeur) * $ratio);
	}

	return implode(",", $resultat);
}

$titrePage = "Recherche - Plein Malin";
$descriptionPage = "Choisir une région, un département et une ville.";
$pageActive = "recherche";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
<main class="page-conteneur">
	<section class="panneau" id="carte">
		<p class="surtitre"><?= texte_securise("Recherche principale") ?></p>
		<h1><?= texte_securise("Rechercher une station") ?></h1>
		<?php if ($modeRecherche === "geo") { ?>
			<p class="accroche">
				<?= texte_securise("Utilisez votre position approximative pour lancer une recherche rapide,") ?>
				<?= texte_securise("sans passer par la carte ni le choix manuel de ville.") ?>
			</p>
		<?php } else { ?>
			<p class="accroche">
				<?= texte_securise("Choisissez votre région, puis précisez le département et la ville dans le formulaire.") ?>
			</p>
		<?php } ?>
		<div class="selecteur-mode-recherche">
			<form action="recherche.php#recherche" method="get" class="formulaire-mode">
				<input type="hidden" name="search_mode" value="manual" />
				<button type="submit" class="onglet-mode<?= $modeRecherche === "manual" ? " is-active" : "" ?>"><?= texte_securise("Recherche manuelle") ?></button>
			</form>
			<form action="recherche.php#recherche" method="get" class="formulaire-mode">
				<input type="hidden" name="search_mode" value="geo" />
				<button type="submit" class="onglet-mode<?= $modeRecherche === "geo" ? " is-active" : "" ?>"><?= texte_securise("Autour de moi") ?></button>
			</form>
		</div>
	</section>

	<section class="panneau" id="recherche">
		<h2><?= texte_securise("Formulaire") ?></h2>
		<?php if ($modeRecherche === "geo") { ?>
			<form action="resultats.php#resultats" method="get" class="formulaire-recherche formulaire-recherche-structure">
				<input type="hidden" name="use_geo" value="1" />
				<div class="section-recherche recherche-simple">
					<p class="libelle-section"><?= texte_securise("Recherche autour de moi") ?></p>
					<div class="grille-champs grille-champs-secondaire">
						<fieldset class="bloc-champ bloc-champ-doux bloc-champ-large">
							<legend class="titre-champ"><?= texte_securise("Carburants") ?></legend>
							<span class="liste-choix-carburants">
								<?php foreach ($libellesCarburants as $codeCarburant => $nomCarburant) { ?>
									<label class="choix-carburant">
										<input type="checkbox" id="geo-fuel-<?= texte_securise(strtolower($codeCarburant)) ?>" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
											<?= in_array($codeCarburant, $carburantsSelectionnes, true) ? 'checked="checked"' : "" ?> />
										<span><?= texte_securise($nomCarburant) ?></span>
									</label>
								<?php } ?>
							</span>
						</fieldset>

						<div class="bloc-champ bloc-champ-doux">
							<label class="titre-champ" for="geo-sort-select"><?= texte_securise("Tri") ?></label>
							<select id="geo-sort-select" name="sort">
								<option value="price" <?= $tri === "price" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix croissant") ?></option>
								<option value="price_desc" <?= $tri === "price_desc" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix décroissant") ?></option>
								<option value="distance" <?= $tri === "distance" ? 'selected="selected"' : "" ?>><?= texte_securise("Distance") ?></option>
								<option value="name" <?= $tri === "name" ? 'selected="selected"' : "" ?>><?= texte_securise("Nom") ?></option>
							</select>
						</div>

						<div class="bloc-champ bloc-champ-doux">
							<label class="titre-champ" for="geo-view-select"><?= texte_securise("Vue") ?></label>
							<select id="geo-view-select" name="view">
								<option value="summary" <?= $vue === "summary" ? 'selected="selected"' : "" ?>><?= texte_securise("Synthèse") ?></option>
								<option value="detailed" <?= $vue === "detailed" ? 'selected="selected"' : "" ?>><?= texte_securise("Détaillée") ?></option>
							</select>
						</div>
					</div>

					<div class="bloc-actions">
						<div class="texte-action">
							<p class="titre-contexte"><?= texte_securise("Lancer la recherche") ?></p>
							<p class="note-discrete"><?= texte_securise("La position utilisée reste approximative car elle vient de l'adresse IP.") ?></p>
						</div>
						<div class="actions-formulaire boutons-action">
							<div class="filtre-ligne">
								<label for="geo-radius-select"><?= texte_securise("Rayon") ?></label>
								<select id="geo-radius-select" name="geo_radius">
									<?php foreach (rayons_geo_disponibles() as $rayon) { ?>
										<option value="<?= texte_securise((string) $rayon) ?>" <?= $rayonGeo === $rayon ? 'selected="selected"' : "" ?>>
											<?= texte_securise((string) $rayon) ?> km
										</option>
									<?php } ?>
								</select>
							</div>
							<button type="submit"><?= texte_securise("Rechercher autour de moi") ?></button>
							<a href="recherche.php?search_mode=geo&amp;reset=1#recherche" class="bouton-secondaire lien-reinitialiser"><?= texte_securise("Réinitialiser") ?></a>
						</div>
					</div>
				</div>
			</form>
		<?php } else { ?>
			<form action="recherche.php#recherche" method="get" class="formulaire-recherche formulaire-recherche-structure">
				<input type="hidden" name="search_mode" value="manual" />
				<input type="hidden" name="region" value="<?= texte_securise($region) ?>" />

				<div class="section-recherche recherche-contexte">
					<p class="libelle-section">1. <?= texte_securise("Région") ?></p>
					<?php if ($infosRegion !== null) { ?>
						<div class="bloc-contexte">
							<div>
								<p class="titre-contexte"><?= texte_securise("Région déjà choisie") ?></p>
								<div class="badge-region"><?= texte_securise($infosRegion["region_name"]) ?></div>
							</div>
							<span class="lien-contexte"><?= texte_securise("Choisissez une autre région directement sur la carte ci-dessous.") ?></span>
						</div>
					<?php } else { ?>
						<div class="bloc-contexte">
							<div>
								<p class="titre-contexte"><?= texte_securise("Aucune région sélectionnée") ?></p>
								<p class="note-discrete"><?= texte_securise("Commencez par cliquer sur la carte ci-dessous.") ?></p>
							</div>
						</div>
					<?php } ?>
					<div class="defilement-carte">
						<img src="image/<?= $theme === "night" ? "map(dark).webp" : "map(light).webp" ?>" alt="<?= texte_securise("Carte des régions") ?>"
							usemap="#regions-map" class="image-carte" width="<?= texte_securise((string) $largeurCarte) ?>" height="<?= texte_securise((string) $hauteurCarte) ?>" decoding="async" fetchpriority="high" />
					</div>
					<map name="regions-map">
						<area shape="rect" coords="<?= coordonnees_carte("252,314,423,380", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=53#recherche" alt="Bretagne" title="Bretagne" />
						<area shape="rect" coords="<?= coordonnees_carte("481,232,642,295", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=28#recherche" alt="Normandie" title="Normandie" />
						<area shape="rect" coords="<?= coordonnees_carte("749,83,917,179", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=32#recherche" alt="Hauts-de-France" title="Hauts-de-France" />
						<area shape="rect" coords="<?= coordonnees_carte("982,248,1168,320", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=44#recherche" alt="Grand Est" title="Grand Est" />
						<area shape="rect" coords="<?= coordonnees_carte("405,409,625,485", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=52#recherche" alt="Pays de la Loire" title="Pays de la Loire" />
						<area shape="rect" coords="<?= coordonnees_carte("654,387,860,487", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=24#recherche" alt="Centre-Val de Loire" title="Centre-Val de Loire" />
						<area shape="rect" coords="<?= coordonnees_carte("747,228,874,328", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=11#recherche" alt="Ile-de-France" title="Ile-de-France" />
						<area shape="rect" coords="<?= coordonnees_carte("902,423,1117,542", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=27#recherche" alt="Bourgogne-Franche-Comte" title="Bourgogne-Franche-Comte" />
						<area shape="rect" coords="<?= coordonnees_carte("543,636,729,733", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=75#recherche" alt="Nouvelle-Aquitaine" title="Nouvelle-Aquitaine" />
						<area shape="rect" coords="<?= coordonnees_carte("653,837,848,905", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=76#recherche" alt="Occitanie" title="Occitanie" />
						<area shape="rect" coords="<?= coordonnees_carte("831,617,1048,719", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=84#recherche" alt="Auvergne-Rhone-Alpes" title="Auvergne-Rhone-Alpes" />
						<area shape="rect" coords="<?= coordonnees_carte("989,781,1183,880", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=93#recherche" alt="Provence-Alpes-Cote d'Azur" title="Provence-Alpes-Cote d'Azur" />
						<area shape="rect" coords="<?= coordonnees_carte("1277,898,1384,955", $largeurCarteOriginale, $hauteurCarteOriginale, $largeurCarte, $hauteurCarte) ?>" href="recherche.php?search_mode=manual&amp;region=94#recherche" alt="Corse" title="Corse" />
					</map>
					<div class="grille-champs grille-champs-principale">
						<div class="bloc-champ">
							<label class="titre-champ" for="department-select"><?= texte_securise("Département") ?></label>
							<span class="aide-champ"><?= texte_securise("Choisissez un département.") ?></span>
							<select id="department-select" name="department"
								<?= $region === "" ? 'disabled="disabled"' : "" ?>>
								<option value=""><?= texte_securise($region === "" ? "Choisir d'abord une région" : "Choisir un département") ?></option>
								<?php foreach ($departements as $unDepartement) { ?>
									<option value="<?= texte_securise($unDepartement["department_code"]) ?>"
										<?= $departement === $unDepartement["department_code"] ? 'selected="selected"' : "" ?>>
										<?= texte_securise($unDepartement["department_name"]) ?>
										(<?= texte_securise($unDepartement["department_code"]) ?>)
									</option>
								<?php } ?>
							</select>
						</div>

						<div class="bloc-champ">
							<label class="titre-champ" for="city-select"><?= texte_securise("Ville") ?></label>
							<span class="aide-champ"><?= texte_securise("Choisissez une ville.") ?></span>
							<select id="city-select" name="city" <?= $departement === "" ? 'disabled="disabled"' : "" ?>>
								<option value=""><?= texte_securise($departement === "" ? "Choisir d'abord un département" : "Choisir une ville") ?></option>
								<?php foreach ($villes as $uneVille) { ?>
									<option value="<?= texte_securise($uneVille["city_code"]) ?>" <?= $ville === $uneVille["city_code"] ? 'selected="selected"' : "" ?>>
										<?= texte_securise($uneVille["city_name"]) ?> (<?= texte_securise($uneVille["postal_code"]) ?>)
									</option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="actions-formulaire">
						<button type="submit" formaction="recherche.php#form-end" class="bouton-secondaire"><?= texte_securise("Mettre à jour la liste des villes") ?></button>
					</div>

				</div>

				<div class="section-recherche recherche-simple">
					<p class="libelle-section">2. <?= texte_securise("Préférences et actions") ?></p>
						<div class="grille-champs grille-champs-secondaire">
							<fieldset class="bloc-champ bloc-champ-doux bloc-champ-large">
								<legend class="titre-champ"><?= texte_securise("Carburants") ?></legend>
								<span class="liste-choix-carburants">
									<?php foreach ($libellesCarburants as $codeCarburant => $nomCarburant) { ?>
										<label class="choix-carburant">
											<input type="checkbox" id="fuel-<?= texte_securise(strtolower($codeCarburant)) ?>" name="fuel[]" value="<?= texte_securise($codeCarburant) ?>"
												<?= in_array($codeCarburant, $carburantsSelectionnes, true) ? 'checked="checked"' : "" ?> />
											<span><?= texte_securise($nomCarburant) ?></span>
										</label>
									<?php } ?>
								</span>
							</fieldset>

							<div class="bloc-champ bloc-champ-doux">
								<label class="titre-champ" for="sort-select"><?= texte_securise("Tri") ?></label>
								<select id="sort-select" name="sort">
									<option value="price" <?= $tri === "price" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix croissant") ?></option>
									<option value="price_desc" <?= $tri === "price_desc" ? 'selected="selected"' : "" ?>><?= texte_securise("Prix décroissant") ?></option>
									<option value="distance" <?= $tri === "distance" ? 'selected="selected"' : "" ?>><?= texte_securise("Distance") ?></option>
									<option value="name" <?= $tri === "name" ? 'selected="selected"' : "" ?>><?= texte_securise("Nom") ?></option>
								</select>
							</div>

							<div class="bloc-champ bloc-champ-doux">
								<label class="titre-champ" for="view-select"><?= texte_securise("Vue") ?></label>
								<select id="view-select" name="view">
									<option value="summary" <?= $vue === "summary" ? 'selected="selected"' : "" ?>><?= texte_securise("Synthèse") ?></option>
									<option value="detailed" <?= $vue === "detailed" ? 'selected="selected"' : "" ?>><?= texte_securise("Détaillée") ?></option>
								</select>
							</div>

							<div class="bloc-champ bloc-champ-doux">
								<span class="titre-champ" id="department-mode-title"><?= texte_securise("Mode département") ?></span>
								<span class="aide-champ"><?= texte_securise("Rechercher dans tout le département.") ?></span>
								<span class="choix-carburant">
									<input type="checkbox" id="department-mode" name="department_mode" value="1" aria-labelledby="department-mode-title"
										<?= $modeDepartement ? 'checked="checked"' : "" ?>
										<?= $departement === "" ? 'disabled="disabled"' : "" ?> />
									<label for="department-mode"><?= texte_securise("Tout le département") ?></label>
								</span>
							</div>
						</div>

						<div class="bloc-actions" id="form-end">
						<div class="actions-formulaire boutons-action">
							<div class="filtre-ligne">
								<label for="manual-radius-select"><?= texte_securise("Rayon") ?></label>
								<select id="manual-radius-select" name="geo_radius">
									<?php foreach (rayons_geo_disponibles() as $rayon) { ?>
										<option value="<?= texte_securise((string) $rayon) ?>" <?= $rayonGeo === $rayon ? 'selected="selected"' : "" ?>>
											<?= texte_securise((string) $rayon) ?> km
										</option>
									<?php } ?>
								</select>
							</div>
							<button type="submit" formaction="resultats.php#resultats"><?= texte_securise("Rechercher") ?></button>
							<a href="recherche.php?search_mode=manual&amp;reset=1#recherche" class="bouton-secondaire lien-reinitialiser"><?= texte_securise("Réinitialiser") ?></a>
						</div>
						</div>
				</div>
			</form>
		<?php } ?>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
