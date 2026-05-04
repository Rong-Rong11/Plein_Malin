<?php

/**
 * @file
 * @brief Lecture et recherche dans les donnees locales CSV/XML.
 */

/**
 * Verifie qu'un departement appartient bien a une region.
 *
 * @param string $codeDepartement Code INSEE du departement.
 * @param string $codeRegion Code de la region.
 * @return bool True si le departement est trouve dans la region.
 */
function departement_existe_dans_region(string $codeDepartement, string $codeRegion): bool
{
	foreach (departements_par_region($codeRegion) as $departement) {
		if ($departement["department_code"] === $codeDepartement) {
			return true;
		}
	}

	return false;
}
/**
 * Verifie qu'une ville appartient au departement selectionne.
 *
 * @param string $codeVille Code de la ville.
 * @param string $codeDepartement Code du departement.
 * @return bool True si la ville est presente dans le departement.
 */
function ville_existe_dans_departement(string $codeVille, string $codeDepartement): bool
{
	foreach (villes_par_departement($codeDepartement) as $ville) {
		if ($ville["city_code"] === $codeVille) {
			return true;
		}
	}

	return false;
}
/**
 * Charge un CSV avec en-tetes sous forme de tableaux associatifs.
 *
 * @param string $fichier Chemin du fichier CSV.
 * @return array<int,array<string,string>> Lignes indexees par nom de colonne.
 *
 * @ingroup donnees
 */
function lire_csv_assoc(string $fichier): array
{
	$lignes = [];

	if (!file_exists($fichier)) {
		return $lignes;
	}

	$fichierOuvert = fopen($fichier, "r");
	if ($fichierOuvert === false) {
		return $lignes;
	}

	$entetes = fgetcsv($fichierOuvert, 0, ",", "\"", "\\");
	if (!is_array($entetes)) {
		fclose($fichierOuvert);
		return $lignes;
	}

	while (($valeurs = fgetcsv($fichierOuvert, 0, ",", "\"", "\\")) !== false) {
		if (count($valeurs) === count($entetes)) {
			$lignes[] = array_combine($entetes, $valeurs);
		}
	}

	fclose($fichierOuvert);
	return $lignes;
}
/**
 * Charge la liste des regions depuis le CSV local.
 *
 * @return array<int,array<string,string>> Regions disponibles.
 *
 * @ingroup donnees
 */
function lire_regions(): array
{
	static $regions = null;

	if ($regions === null) {
		$regions = lire_csv_assoc(PM_DATA_DIR . "/regions.csv");
	}

	return $regions;
}
/**
 * Charge la liste des departements depuis le CSV local.
 *
 * @return array<int,array<string,string>> Departements disponibles.
 *
 * @ingroup donnees
 */
function lire_departements(): array
{
	static $departements = null;

	if ($departements === null) {
		$departements = lire_csv_assoc(PM_DATA_DIR . "/departments.csv");
	}

	return $departements;
}
/**
 * Charge l'index national des villes depuis le CSV local.
 *
 * @return array<int,array<string,string>> Villes disponibles.
 *
 * @ingroup donnees
 */
function lire_villes(): array
{
	static $villes = null;

	if ($villes === null) {
		$villes = lire_csv_assoc(PM_CITIES_INDEX_FILE);
	}

	return $villes;
}
/**
 * Trouve une region par son code.
 *
 * @param string $code Code de region.
 * @return array|null Region trouvee ou null.
 *
 * @ingroup donnees
 */
function trouver_region(string $code): ?array
{
	foreach (lire_regions() as $region) {
		if ($region["region_code"] === $code) {
			return $region;
		}
	}

	return null;
}
/**
 * Trouve un departement par son code.
 *
 * @param string $code Code de departement.
 * @return array|null Departement trouve ou null.
 *
 * @ingroup donnees
 */
function trouver_departement(string $code): ?array
{
	foreach (lire_departements() as $departement) {
		if ($departement["department_code"] === $code) {
			return $departement;
		}
	}

	return null;
}
/**
 * Trouve une ville par son code, avec index en memoire pour accelerer les appels.
 *
 * @param string $code Code de la ville.
 * @return array|null Ville trouvee ou null.
 *
 * @ingroup donnees
 */
function trouver_ville(string $code): ?array
{
	static $indiceVilles = null;

	if ($indiceVilles === null) {
		$indiceVilles = [];

		foreach (lire_villes() as $ville) {
			$indiceVilles[$ville["city_code"]] = $ville;
		}
	}

	return $indiceVilles[$code] ?? null;
}
/**
 * Retourne les departements d'une region, tries par nom.
 *
 * @param string $codeRegion Code de region, ou chaine vide pour tout retourner.
 * @return array<int,array<string,string>> Departements correspondants.
 *
 * @ingroup donnees
 */
function departements_par_region(string $codeRegion): array
{
	$departements = lire_departements();
	$resultat = [];

	foreach ($departements as $departement) {
		$codeRegionDepartement = $departement["region_code"];

		if ($codeRegion === "" || $codeRegionDepartement === $codeRegion) {
			$resultat[] = $departement;
		}
	}

	usort($resultat, "comparer_departements_par_nom");

	return $resultat;
}
/**
 * Compare deux departements par leur nom pour les trier.
 *
 * @param array $departementA Premier departement a comparer.
 * @param array $departementB Deuxieme departement a comparer.
 * @return int Resultat de comparaison alphabetique.
 */
function comparer_departements_par_nom(array $departementA, array $departementB): int
{
	return strcmp($departementA["department_name"], $departementB["department_name"]);
}
/**
 * Retourne les villes d'un departement.
 *
 * @param string $codeDepartement Code du departement.
 * @return array<int,array<string,string>> Villes du departement.
 *
 * @ingroup donnees
 */
function villes_par_departement(string $codeDepartement): array
{
	static $cacheDepartements = [];

	if ($codeDepartement === "") {
		return [];
	}

	if (!isset($cacheDepartements[$codeDepartement])) {
		$cacheDepartements[$codeDepartement] = [];

		foreach (lire_villes() as $ville) {
			if (($ville["department_code"] ?? "") === $codeDepartement) {
				$cacheDepartements[$codeDepartement][] = $ville;
			}
		}
	}

	return $cacheDepartements[$codeDepartement];
}
/**
 * Transforme un noeud XML de point de vente en tableau affiche par la page tech.
 *
 * @param SimpleXMLElement $pointVente Noeud XML <pdv>.
 * @return array Station normalisee pour l'affichage.
 */
function transformer_point_vente_xml_demo(SimpleXMLElement $pointVente): array
{
	$prix = [];
	$services = [];

	foreach ($pointVente->prix as $prixXml) {
		$prix[] = [
			"nom" => (string) ($prixXml["nom"] ?? ""),
			"valeur" => (string) ($prixXml["valeur"] ?? ""),
			"maj" => (string) ($prixXml["maj"] ?? ""),
		];
	}

	$servicesXml = [];

	if (isset($pointVente->services->service)) {
		$servicesXml = $pointVente->services->service;
	}

	foreach ($servicesXml as $serviceXml) {
		$services[] = (string) $serviceXml;
	}

	return [
		"id" => (string) ($pointVente["id"] ?? ""),
		"cp" => (string) ($pointVente["cp"] ?? ""),
		"adresse" => (string) $pointVente->adresse,
		"ville" => (string) $pointVente->ville,
		"enseigne" => (string) $pointVente->enseigne,
		"prix" => $prix,
		"services" => $services,
	];
}
/**
 * Lit quelques stations dans le XML contenu dans l'archive officielle.
 *
 * @param string $fichierZip Chemin de l'archive officielle en cache.
 * @param string $nomXml Nom du fichier XML dans l'archive.
 * @param int $limite Nombre maximum de stations a retourner.
 * @return array<int,array> Stations extraites du flux XML officiel.
 */
function lire_stations_depuis_archive_xml_demo(string $fichierZip, string $nomXml, int $limite): array
{
	if ($nomXml === "" || !class_exists("XMLReader")) {
		return [];
	}

	$lecteur = new XMLReader();
	$cheminZip = "zip://" . $fichierZip . "#" . $nomXml;

	if (!$lecteur->open($cheminZip)) {
		return [];
	}

	$stations = [];

	while ($lecteur->read()) {
		if ($lecteur->nodeType !== XMLReader::ELEMENT || $lecteur->name !== "pdv") {
			continue;
		}

		$contenuPdv = $lecteur->readOuterXML();
		$pointVente = simplexml_load_string($contenuPdv);

		if ($pointVente !== false) {
			$stations[] = transformer_point_vente_xml_demo($pointVente);
		}

		if (count($stations) >= $limite) {
			break;
		}
	}

	$lecteur->close();
	return $stations;
}
/**
 * Lit le flux XML officiel des prix carburants utilise pour la demonstration technique.
 *
 * L'archive distante est conservee dans le dossier cache uniquement pour eviter
 * de retelcharger le flux officiel a chaque affichage de la page tech.
 *
 * @return array<int,array> Stations extraites de l'archive XML officielle.
 *
 * @ingroup donnees
 */
function lire_stations_xml_demo(): array
{
	if (!function_exists("preparer_archive_prix_annuelle") || !function_exists("trouver_xml_dans_archive")) {
		return [];
	}

	$annee = (int) date("Y");
	$sourceUrl = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
	$fichierZip = PM_CACHE_DIR . "/fuel_history_" . $annee . ".zip";

	if (!preparer_archive_prix_annuelle($fichierZip, $sourceUrl)) {
		return [];
	}

	$nomXml = trouver_xml_dans_archive($fichierZip);
	return lire_stations_depuis_archive_xml_demo($fichierZip, $nomXml, 5);
}
