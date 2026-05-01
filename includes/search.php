<?php

/**
 * @file
 * @brief Recherche de stations, geolocalisation et appels API carburants.
 */

/**
 * Convertit les booleens du formulaire en libelle de mode de recherche.
 *
 * @param bool $utiliserGeo Recherche autour de la position approximative.
 * @param bool $modeDepartement Recherche dans tout le departement.
 * @return string Mode stocke dans les statistiques.
 */
function mode_recherche(bool $utiliserGeo, bool $modeDepartement): string
{
	if ($utiliserGeo) {
		return "geolocalisation";
	}

	if ($modeDepartement) {
		return "departement";
	}

	return "ville";
}
/**
 * Prepare le message de contexte affiche au-dessus des resultats.
 *
 * @param array|null $villeCourante Ville de reference, si elle existe.
 * @param bool $utiliserGeo Recherche par geolocalisation IP.
 * @param bool $modeDepartement Recherche par departement complet.
 * @param array $stations Stations trouvees apres filtrage.
 * @return string Message adapte a l'etat de la recherche.
 */
function message_resultats(?array $villeCourante, bool $utiliserGeo, bool $modeDepartement, array $stations): string
{
	if ($villeCourante === null) {
		return "Aucune recherche lancée.";
	}

	if ($utiliserGeo) {
		return "Recherche autour de votre position approximative.";
	}

	if ($modeDepartement) {
		return "Recherche dans tout le département sélectionné.";
	}

	if ($stations === []) {
		return "Aucune station trouvée dans la ville sélectionnée.";
	}

	return "Recherche dans la ville sélectionnée.";
}
/**
 * Liste les rayons autorises pour les recherches de proximite.
 *
 * @return int[] Rayons en kilometres.
 */
function rayons_geo_disponibles(): array
{
	return PM_GEO_RADII;
}
/**
 * Force un rayon a rester dans la liste des valeurs autorisees.
 *
 * @param int $rayon Rayon demande.
 * @return int Rayon valide, rayon par defaut sinon.
 */
function normaliser_rayon_geo(int $rayon): int
{
	if (in_array($rayon, rayons_geo_disponibles(), true)) {
		return $rayon;
	}

	return PM_DEFAULT_RADIUS;
}
/**
 * Construit un lien direct vers les resultats d'une ville.
 *
 * @param array $ville Ville issue des donnees CSV.
 * @return string URL de resultats.
 */
function lien_resultats_ville(array $ville): string
{
	$departement = $ville["department_code"] ?? "";
	$infosRegion = trouver_departement($departement);
	$region = $infosRegion["region_code"] ?? "";

	return "resultats.php?region="
		. rawurlencode($region)
		. "&department=" . rawurlencode($departement)
		. "&city=" . rawurlencode((string) ($ville["city_code"] ?? ""))
		. "#resultats";
}
/**
 * Construit un lien direct vers les resultats d'un departement complet.
 *
 * @param string $codeDepartement Code du departement.
 * @return string URL de resultats en mode departement.
 */
function lien_resultats_departement(string $codeDepartement): string
{
	$infosDepartement = trouver_departement($codeDepartement);
	$region = $infosDepartement["region_code"] ?? "";

	return "resultats.php?region="
		. rawurlencode($region)
		. "&department=" . rawurlencode($codeDepartement)
		. "&department_mode=1#resultats";
}
/**
 * Liste les carburants geres par le formulaire et par l'API.
 *
 * @return array<string,string> Code carburant => libelle affiche.
 *
 * @ingroup recherche
 */
function liste_carburants(): array
{
	return [
		"Gazole" => "Gazole",
		"SP95" => "SP95",
		"SP98" => "SP98",
		"E10" => "SP95-E10",
		"E85" => "E85",
		"GPLc" => "GPLc",
	];
}
/**
 * Valide la selection de carburants et applique Gazole par defaut.
 *
 * @param mixed $entreeCarburants Valeur issue de GET, chaine ou tableau.
 * @return string[] Codes carburants valides.
 *
 * @ingroup recherche
 */
function normaliser_carburants_selection($entreeCarburants): array
{
	$carburantsValides = array_keys(liste_carburants());

	if (!is_array($entreeCarburants)) {
		if (is_string($entreeCarburants) && $entreeCarburants !== "") {
			$entreeCarburants = [$entreeCarburants];
		} else {
			$entreeCarburants = [];
		}
	}

	$resultat = [];
	foreach ($entreeCarburants as $carburant) {
		if (is_string($carburant) && in_array($carburant, $carburantsValides, true)) {
			$resultat[] = $carburant;
		}
	}

	$resultat = array_values(array_unique($resultat));

	if ($resultat === []) {
		return [PM_DEFAULT_FUEL];
	}

	return $resultat;
}
/**
 * Transforme une liste de codes carburants en texte lisible.
 *
 * @param string[] $typesCarburants Codes carburants selectionnes.
 * @return string Libelles separes par des virgules.
 *
 * @ingroup recherche
 */
function texte_carburants_selectionnes(array $typesCarburants): string
{
	$libelles = liste_carburants();
	$noms = [];

	foreach ($typesCarburants as $typeCarburant) {
		if (isset($libelles[$typeCarburant])) {
			$noms[] = $libelles[$typeCarburant];
		}
	}

	return implode(", ", $noms);
}
/**
 * Lit un fichier de cache JSON produit par les appels API.
 *
 * @param string $fichierCache Chemin du fichier cache.
 * @return array|null Cache valide contenant time et body, ou null.
 */
function lire_cache_api(string $fichierCache): ?array
{
	if (!file_exists($fichierCache)) {
		return null;
	}

	$contenu = (string) file_get_contents($fichierCache);
	if (trim($contenu) === "") {
		return null;
	}

	$cache = json_decode($contenu, true);
	if (!is_array($cache) || !isset($cache["time"], $cache["body"])) {
		return null;
	}

	if (!is_string($cache["body"]) || trim($cache["body"]) === "") {
		return null;
	}

	return $cache;
}
/**
 * Interroge une URL avec cache fichier et reutilise l'ancien cache si l'API echoue.
 *
 * @param string $adresseUrl URL distante a appeler.
 * @param string $nomCache Nom logique du fichier cache.
 * @return string|null Corps de reponse ou null si aucune donnee n'est disponible.
 *
 * @ingroup recherche
 */
function lire_api_avec_cache(string $adresseUrl, string $nomCache): ?string
{
	$fichierCache = PM_CACHE_DIR . "/" . $nomCache . ".json";
	$duree = PM_API_CACHE_DURATION;
	$maintenant = time();
	$cache = lire_cache_api($fichierCache);

	if ($cache !== null && ($maintenant - $cache["time"]) < $duree) {
		return (string) $cache["body"];
	}

	$contexte = stream_context_create([
		"http" => [
			"timeout" => 5,
			"header" => "User-Agent: PleinMalin/1.0\r\n",
		],
		"ssl" => [
			"verify_peer" => false,
			"verify_peer_name" => false,
		],
	]);

	$contenu = @file_get_contents($adresseUrl, false, $contexte);

	if ($contenu !== false) {
		$cache = [
			"time" => $maintenant,
			"body" => $contenu,
		];
		file_put_contents($fichierCache, json_encode($cache, JSON_PRETTY_PRINT));
		return $contenu;
	}

	if ($cache !== null) {
		return (string) $cache["body"];
	}

	return null;
}
/**
 * Recupere l'adresse IP du visiteur en tenant compte d'un eventuel proxy.
 *
 * @return string Adresse IP detectee ou localhost par defaut.
 *
 * @ingroup recherche
 */
function recuperer_ip_visiteur(): string
{
	if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$morceaux = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
		return trim($morceaux[0]);
	}

	if (!empty($_SERVER["REMOTE_ADDR"])) {
		return $_SERVER["REMOTE_ADDR"];
	}

	return "127.0.0.1";
}
/**
 * Recupere une position approximative a partir de l'adresse IP.
 *
 * @return array Donnees de geolocalisation : source, IP, ville, region, latitude et longitude.
 *
 * @ingroup recherche
 */
function recuperer_geolocalisation(): array
{
	$ip = recuperer_ip_visiteur();

	$contenu = lire_api_avec_cache("https://ipapi.co/" . rawurlencode($ip) . "/json/", "geo_" . md5($ip));

	if ($contenu !== null) {
		$json = json_decode($contenu, true);
		if (is_array($json) && isset($json["latitude"], $json["longitude"])) {
			return [
				"source" => "api json",
				"ip" => $ip,
				"city" => (string) ($json["city"] ?? ""),
				"region" => (string) ($json["region"] ?? ""),
				"latitude" => (float) $json["latitude"],
				"longitude" => (float) $json["longitude"],
			];
		}
	}

	return [
		"source" => "indisponible",
		"ip" => $ip,
		"city" => "",
		"region" => "",
		"latitude" => 0.0,
		"longitude" => 0.0,
	];
}
/**
 * Calcule la distance geographique entre deux coordonnees avec la formule de Haversine.
 *
 * @param float $latitude1 Latitude du premier point.
 * @param float $longitude1 Longitude du premier point.
 * @param float $latitude2 Latitude du deuxieme point.
 * @param float $longitude2 Longitude du deuxieme point.
 * @return float Distance en kilometres.
 *
 * @ingroup recherche
 */
function calculer_distance_km(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
{
	$rayonTerre = 6371;
	$deltaLatitude = deg2rad($latitude2 - $latitude1);
	$deltaLongitude = deg2rad($longitude2 - $longitude1);

	$a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2)
		+ cos(deg2rad($latitude1)) * cos(deg2rad($latitude2))
		* sin($deltaLongitude / 2) * sin($deltaLongitude / 2);

	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	return $rayonTerre * $c;
}
/**
 * Trouve la ville locale la plus proche d'une position geographique.
 *
 * @param float $latitude Latitude de reference.
 * @param float $longitude Longitude de reference.
 * @return array|null Ville la plus proche, enrichie avec la distance.
 *
 * @ingroup recherche
 */
function trouver_ville_plus_proche(float $latitude, float $longitude): ?array
{
	$villeProche = null;
	$distanceMini = null;

	foreach (lire_villes() as $ville) {
		$distance = calculer_distance_km($latitude, $longitude, (float) $ville["latitude"], (float) $ville["longitude"]);

		if ($distanceMini === null || $distance < $distanceMini) {
			$distanceMini = $distance;
			$villeProche = $ville;
			$villeProche["distance"] = $distance;
		}
	}

	return $villeProche;
}
/**
 * Arrondit une coordonnee pour stabiliser les cles de cache des recherches proches.
 *
 * @param float $valeur Coordonne brute.
 * @return float Coordonne arrondie.
 */
function arrondir_coordonnee_cache(float $valeur): float
{
	return round($valeur, 2);
}
/**
 * Detecte les arrondissements municipaux de Paris, Lyon et Marseille.
 *
 * Ces villes necessitent souvent un filtre par code postal plutot que par nom.
 *
 * @param array $ville Ville issue du CSV.
 * @return bool True si la ville correspond a un arrondissement municipal.
 */
function est_arrondissement_municipal(array $ville): bool
{
	$departement = (string) ($ville["department_code"] ?? "");
	$codeVilleApi = (string) ($ville["city_code"] ?? "");
	$nomVille = trim((string) ($ville["city_name"] ?? ""));

	if ($departement === "75" && str_starts_with($nomVille, "Paris ")) {
		return true;
	}

	if (str_starts_with($codeVilleApi, "6938") && str_starts_with($nomVille, "Lyon ")) {
		return true;
	}

	if (preg_match('/^132(0[1-9]|1[0-6])$/', $codeVilleApi) === 1 && str_starts_with($nomVille, "Marseille ")) {
		return true;
	}

	return false;
}
/**
 * Lit les stations depuis l'API officielle des prix des carburants.
 *
 * @param array|null $ville Ville ou departement de reference.
 * @param bool $modeDepartement True pour chercher dans tout le departement.
 * @param array|null $origine Position de reference pour la recherche autour de moi.
 * @param int $rayonKm Rayon de recherche en kilometres.
 * @return array|null Liste de stations, ou null si l'API est indisponible.
 *
 * @ingroup recherche
 */
function lire_stations_api(?array $ville, bool $modeDepartement = false, ?array $origine = null, int $rayonKm = PM_DEFAULT_RADIUS): ?array
{
	if ($ville === null && $origine === null) {
		return [];
	}

	$departement = (string) ($ville["department_code"] ?? "");
	$ville = trim((string) ($ville["city_name"] ?? ""));
	$codePostal = trim((string) ($ville["postal_code"] ?? ""));
	$filtres = [];

	if ($origine !== null) {
		$latitude = arrondir_coordonnee_cache((float) ($origine["latitude"] ?? 0));
		$longitude = arrondir_coordonnee_cache((float) ($origine["longitude"] ?? 0));
		$rayonKm = normaliser_rayon_geo($rayonKm);

		$filtres[] = "within_distance(geom, geom'POINT(" . $longitude . " " . $latitude . ")', " . $rayonKm . " km)";
	} else {
		if ($departement !== "") {
			$filtres[] = 'code_departement="' . addslashes($departement) . '"';
		}

		if (!$modeDepartement && $ville !== "") {
			if ($codePostal !== "" && est_arrondissement_municipal($ville)) {
				$filtres[] = 'code_postal="' . addslashes($codePostal) . '"';
			} else {
				$filtres[] = 'ville="' . addslashes($ville) . '"';
			}
		}
	}

	if ($filtres === []) {
		return [];
	}

	$clauseWhere = implode(" AND ", $filtres);
	$adresseUrl = "https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/"
		. "prix-des-carburants-en-france-flux-instantane-v2/records?"
		. "where=" . rawurlencode($clauseWhere)
		. "&limit=" . PM_FUEL_API_LIMIT
		. "&timezone=Europe%2FParis";

	$contenu = lire_api_avec_cache($adresseUrl, "fuel_search_" . md5($adresseUrl));

	if ($contenu === null) {
		return null;
	}

	$donnees = json_decode($contenu, true);

	if (!is_array($donnees) || !isset($donnees["results"]) || !is_array($donnees["results"])) {
		return null;
	}

	$stations = [];

	foreach ($donnees["results"] as $ligne) {
		$prix = [];
		$carburants = [
			"SP95" => ["price" => "sp95_prix", "updated_at" => "sp95_maj"],
			"SP98" => ["price" => "sp98_prix", "updated_at" => "sp98_maj"],
			"Gazole" => ["price" => "gazole_prix", "updated_at" => "gazole_maj"],
			"E10" => ["price" => "e10_prix", "updated_at" => "e10_maj"],
			"E85" => ["price" => "e85_prix", "updated_at" => "e85_maj"],
			"GPLc" => ["price" => "gplc_prix", "updated_at" => "gplc_maj"],
		];

		foreach ($carburants as $nomCarburant => $champsCarburant) {
			$champPrix = $champsCarburant["price"];
			if (isset($ligne[$champPrix]) && $ligne[$champPrix] !== null && $ligne[$champPrix] !== "") {
				$prix[$nomCarburant] = [
					"name" => $nomCarburant,
					"value" => (float) $ligne[$champPrix],
					"updated_at" => (string) ($ligne[$champsCarburant["updated_at"]] ?? ""),
				];
			}
		}

		$latitude = 0.0;
		$longitude = 0.0;

		if (isset($ligne["geom"]) && is_array($ligne["geom"]) && isset($ligne["geom"]["lat"], $ligne["geom"]["lon"])) {
			$latitude = (float) $ligne["geom"]["lat"];
			$longitude = (float) $ligne["geom"]["lon"];
		} elseif (isset($ligne["latitude"], $ligne["longitude"])) {
			$latitude = (float) $ligne["latitude"];
			$longitude = (float) $ligne["longitude"];
		}

		$services = [];
		foreach ($ligne as $cle => $valeur) {
			if (str_starts_with((string) $cle, "services_") && is_string($valeur) && trim($valeur) !== "") {
				$services[] = trim($valeur);
			}
		}

		$nomStation = trim((string) ($ligne["nom"] ?? ""));
		if ($nomStation === "") {
			$nomStation = trim((string) ($ligne["enseigne"] ?? ""));
		}
		if ($nomStation === "") {
			$nomStation = "Station " . (string) ($ligne["id"] ?? "");
		}

		$stations[] = [
			"id" => (string) ($ligne["id"] ?? ""),
			"name" => $nomStation,
			"address" => (string) ($ligne["adresse"] ?? ""),
			"postal_code" => (string) ($ligne["code_postal"] ?? ""),
			"raw_city_name" => (string) ($ligne["ville"] ?? ""),
			"city_name" => (string) ($ligne["ville"] ?? ""),
			"department_code" => (string) ($ligne["code_departement"] ?? $departement),
			"latitude" => $latitude,
			"longitude" => $longitude,
			"services" => array_values(array_unique($services)),
			"prices" => $prix,
			"source" => "api json",
		];
	}

	return $stations;
}
/**
 * Recherche et trie les stations proposant au moins un carburant selectionne.
 *
 * @param array|null $ville Ville ou departement de reference.
 * @param string[] $typesCarburants Carburants selectionnes.
 * @param string $tri Mode de tri : price, price_desc, distance ou name.
 * @param bool $modeDepartement Recherche sur tout le departement.
 * @param array|null $origine Position de reference pour la geolocalisation.
 * @param int $rayonKm Rayon de recherche.
 * @return array<int,array> Stations filtrees et enrichies avec distance/prix principal.
 */
function rechercher_stations(?array $ville, array $typesCarburants, string $tri, bool $modeDepartement = false, ?array $origine = null, int $rayonKm = PM_DEFAULT_RADIUS): array
{
	if ($ville === null && $origine === null) {
		return [];
	}

	$resultat = [];
	$stationsDisponibles = lire_stations_api($ville, $modeDepartement, $origine, $rayonKm);

	if ($stationsDisponibles === null) {
		return [];
	}

	$referenceLatitude = (float) ($origine["latitude"] ?? $ville["latitude"] ?? 0);
	$referenceLongitude = (float) ($origine["longitude"] ?? $ville["longitude"] ?? 0);

	foreach ($stationsDisponibles as $station) {
		$distance = calculer_distance_km(
			$referenceLatitude,
			$referenceLongitude,
			(float) $station["latitude"],
			(float) $station["longitude"]
		);

		$prixSelectionnes = [];
		foreach ($typesCarburants as $typeCarburant) {
			if (isset($station["prices"][$typeCarburant])) {
				$prixSelectionnes[$typeCarburant] = (float) $station["prices"][$typeCarburant]["value"];
			}
		}

		if ($prixSelectionnes === []) {
			continue;
		}

		$prixPrincipal = min($prixSelectionnes);
		$carburantPrincipal = array_search($prixPrincipal, $prixSelectionnes, true);

		$station["distance"] = $distance;
		$station["main_price"] = $prixPrincipal;
		$station["main_fuel"] = is_string($carburantPrincipal) ? $carburantPrincipal : "";
		$station["main_updated_at"] = is_string($carburantPrincipal) ? (string) ($station["prices"][$carburantPrincipal]["updated_at"] ?? "") : "";
		$resultat[] = $station;
	}

	usort($resultat, static function (array $a, array $b) use ($tri): int {
		if ($tri === "price_desc") {
			return ($b["main_price"] ?? 0) <=> ($a["main_price"] ?? 0);
		}

		if ($tri === "distance") {
			return $a["distance"] <=> $b["distance"];
		}

		if ($tri === "name") {
			return strcmp($a["name"], $b["name"]);
		}

		return ($a["main_price"] ?? 999) <=> ($b["main_price"] ?? 999);
	});

	return $resultat;
}
/**
 * Variante de recherche qui indique aussi si l'API carburants a echoue.
 *
 * @param array|null $ville Ville ou departement de reference.
 * @param string[] $typesCarburants Carburants selectionnes.
 * @param string $tri Mode de tri.
 * @param bool $modeDepartement Recherche sur tout le departement.
 * @param array|null $origine Position de reference pour la geolocalisation.
 * @param int $rayonKm Rayon de recherche.
 * @return array{stations:array,api_error:bool} Resultats et statut API.
 *
 * @ingroup recherche
 */
function rechercher_stations_avec_statut(?array $ville, array $typesCarburants, string $tri, bool $modeDepartement = false, ?array $origine = null, int $rayonKm = PM_DEFAULT_RADIUS): array
{
	if ($ville === null && $origine === null) {
		return [
			"stations" => [],
			"api_error" => false,
		];
	}

	$stationsDisponibles = lire_stations_api($ville, $modeDepartement, $origine, $rayonKm);

	if ($stationsDisponibles === null) {
		return [
			"stations" => [],
			"api_error" => true,
		];
	}

	$resultat = [];
	$referenceLatitude = (float) ($origine["latitude"] ?? $ville["latitude"] ?? 0);
	$referenceLongitude = (float) ($origine["longitude"] ?? $ville["longitude"] ?? 0);

	foreach ($stationsDisponibles as $station) {
		$distance = calculer_distance_km(
			$referenceLatitude,
			$referenceLongitude,
			(float) $station["latitude"],
			(float) $station["longitude"]
		);

		$prixSelectionnes = [];
		foreach ($typesCarburants as $typeCarburant) {
			if (isset($station["prices"][$typeCarburant])) {
				$prixSelectionnes[$typeCarburant] = (float) $station["prices"][$typeCarburant]["value"];
			}
		}

		if ($prixSelectionnes === []) {
			continue;
		}

		$prixPrincipal = min($prixSelectionnes);
		$carburantPrincipal = array_search($prixPrincipal, $prixSelectionnes, true);

		$station["distance"] = $distance;
		$station["main_price"] = $prixPrincipal;
		$station["main_fuel"] = is_string($carburantPrincipal) ? $carburantPrincipal : "";
		$station["main_updated_at"] = is_string($carburantPrincipal) ? (string) ($station["prices"][$carburantPrincipal]["updated_at"] ?? "") : "";
		$resultat[] = $station;
	}

	usort($resultat, static function (array $a, array $b) use ($tri): int {
		if ($tri === "price_desc") {
			return ($b["main_price"] ?? 0) <=> ($a["main_price"] ?? 0);
		}

		if ($tri === "distance") {
			return $a["distance"] <=> $b["distance"];
		}

		if ($tri === "name") {
			return strcmp($a["name"], $b["name"]);
		}

		return ($a["main_price"] ?? 999) <=> ($b["main_price"] ?? 999);
	});

	return [
		"stations" => $resultat,
		"api_error" => false,
	];
}
