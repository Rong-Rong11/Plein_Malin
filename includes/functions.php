<?php

define("PM_CACHE_DIR", __DIR__ . "/../cache");
define("PM_DATA_DIR", __DIR__ . "/../data");
define("PM_STORAGE_DIR", __DIR__ . "/../storage");
define("PM_CITIES_INDEX_FILE", __DIR__ . "/../data/villes_index.csv");
define("PM_CITIES_BY_DEPARTMENT_DIR", __DIR__ . "/../data/villes_par_departement");

function texte_securise(string $texte): string
{
	return htmlspecialchars($texte, ENT_QUOTES, "UTF-8");
}

function preparer_dossiers_et_fichiers(): void
{
	if (!is_dir(PM_CACHE_DIR)) {
		mkdir(PM_CACHE_DIR, 0777, true);
	}

	if (!is_dir(PM_STORAGE_DIR)) {
		mkdir(PM_STORAGE_DIR, 0777, true);
	}

	$fichier = PM_STORAGE_DIR . "/consultations.csv";
	if (!file_exists($fichier)) {
		file_put_contents($fichier, "timestamp,visitor_hash,region,department,city,mode,view,fuel,station_count\n");
	}
}

function chemin_cookie(): string
{
	$chemin = dirname($_SERVER["SCRIPT_NAME"] ?? "/");

	if ($chemin === "\\" || $chemin === "." || $chemin === "/") {
		return "/";
	}

	return rtrim(str_replace("\\", "/", $chemin), "/") . "/";
}

function gerer_theme(): string
{
	$theme = "day";
	$themesValides = ["day", "night"];

	if (isset($_GET["theme"])) {
		if (in_array($_GET["theme"], $themesValides, true)) {
			$theme = $_GET["theme"];
			setcookie("theme", $theme, time() + 30 * 24 * 3600, chemin_cookie());
			$_COOKIE["theme"] = $theme;
			return $theme;
		}

		setcookie("theme", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["theme"]);
		return "day";
	}

	if (isset($_COOKIE["theme"])) {
		if (in_array($_COOKIE["theme"], $themesValides, true)) {
			return $_COOKIE["theme"];
		}

		setcookie("theme", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["theme"]);
	}

	return $theme;
}

function nom_theme(string $theme): string
{
	if ($theme === "night") {
		return "nuit";
	}

	return "jour";
}

function lien_bascule_theme(string $theme): string
{
	$themeCible = $theme === "night" ? "day" : "night";
	$parametres = $_GET;
	$parametres["theme"] = $themeCible;
	$requete = http_build_query($parametres);
	$script = basename($_SERVER["PHP_SELF"] ?? "index.php");

	if ($requete === "") {
		return $script;
	}

	return $script . "?" . $requete;
}

function departement_existe_dans_region(string $codeDepartment, string $codeRegion): bool
{
	foreach (departements_par_region($codeRegion) as $department) {
		if ($department["department_code"] === $codeDepartment) {
			return true;
		}
	}

	return false;
}

function ville_existe_dans_departement(string $codeVille, string $codeDepartment): bool
{
	foreach (villes_par_departement($codeDepartment) as $city) {
		if ($city["city_code"] === $codeVille) {
			return true;
		}
	}

	return false;
}

function mode_recherche(bool $useGeo, bool $departmentMode): string
{
	if ($useGeo) {
		return "geolocalisation";
	}

	if ($departmentMode) {
		return "departement";
	}

	return "ville";
}

function message_resultats(?array $currentCity, bool $useGeo, bool $departmentMode, array $stations): string
{
	if ($currentCity === null) {
		return "Aucune recherche lancee.";
	}

	if ($useGeo) {
		return "Recherche autour de votre position approximative.";
	}

	if ($departmentMode) {
		return "Recherche dans tout le departement selectionne.";
	}

	if ($stations === []) {
		return "Aucune station trouvee dans la ville selectionnee.";
	}

	return "Recherche dans la ville selectionnee.";
}

function rayons_geo_disponibles(): array
{
	return [5, 10, 15, 20, 30];
}

function normaliser_rayon_geo(int $radius): int
{
	if (in_array($radius, rayons_geo_disponibles(), true)) {
		return $radius;
	}

	return 10;
}

function lien_resultats_ville(array $city): string
{
	$department = $city["department_code"] ?? "";
	$regionInfo = trouver_departement($department);
	$region = $regionInfo["region_code"] ?? "";

	return "resultats.php?region="
		. rawurlencode($region)
		. "&department=" . rawurlencode($department)
			. "&city=" . rawurlencode((string) ($city["city_code"] ?? ""))
			. "#resultats";
}

function lien_resultats_departement(string $codeDepartment): string
{
	$departmentInfo = trouver_departement($codeDepartment);
	$region = $departmentInfo["region_code"] ?? "";

	return "resultats.php?region="
		. rawurlencode($region)
		. "&department=" . rawurlencode($codeDepartment)
		. "&department_mode=1#resultats";
}

function enregistrer_derniere_recherche(string $type, string $code): void
{
	if (!in_array($type, ["ville", "departement"], true) || $code === "") {
		return;
	}

	$valeur = json_encode([
		"type" => $type,
		"code" => $code,
		"date" => date("c"),
	]);

	if ($valeur !== false) {
		setcookie("last_search", $valeur, time() + 30 * 24 * 3600, chemin_cookie());
		$_COOKIE["last_search"] = $valeur;
	}
}

function lire_derniere_recherche(): array
{
	if (!isset($_COOKIE["last_search"])) {
		return [];
	}

	$recherche = json_decode((string) $_COOKIE["last_search"], true);

	if (
		!is_array($recherche)
		|| !isset($recherche["type"], $recherche["code"])
		|| !in_array($recherche["type"], ["ville", "departement"], true)
		|| !is_string($recherche["code"])
		|| $recherche["code"] === ""
	) {
		setcookie("last_search", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["last_search"]);
		return [];
	}

	return $recherche;
}

function normaliser_parametres_recherche(array $parametres): array
{
	$resultat = [
		"region" => isset($parametres["region"]) ? (string) $parametres["region"] : "",
		"department" => isset($parametres["department"]) ? (string) $parametres["department"] : "",
		"city" => isset($parametres["city"]) ? (string) $parametres["city"] : "",
		"fuel" => normaliser_carburants_selection($parametres["fuel"] ?? []),
		"view" => ($parametres["view"] ?? "summary") === "detailed" ? "detailed" : "summary",
		"sort" => in_array(($parametres["sort"] ?? "price"), ["price", "distance", "name"], true) ? (string) $parametres["sort"] : "price",
		"geo_radius" => normaliser_rayon_geo((int) ($parametres["geo_radius"] ?? 10)),
	];

	if (isset($parametres["department_mode"])) {
		$resultat["department_mode"] = "1";
	}

	if (isset($parametres["use_geo"])) {
		$resultat["use_geo"] = "1";
	}

	return $resultat;
}

function enregistrer_parametres_derniere_recherche(array $parametres): void
{
	$parametres = normaliser_parametres_recherche($parametres);
	$anciensParametres = lire_parametres_derniere_recherche();

	if (
		$parametres["city"] === ""
		&& !isset($parametres["department_mode"], $parametres["use_geo"])
		&& ($anciensParametres["city"] ?? "") !== ""
		&& ($anciensParametres["region"] ?? "") === $parametres["region"]
		&& ($anciensParametres["department"] ?? "") === $parametres["department"]
	) {
		$parametres["city"] = $anciensParametres["city"];
	}

	$parametres["date"] = date("c");
	$valeur = json_encode($parametres);

	if ($valeur !== false) {
		setcookie("last_search_params", $valeur, time() + 30 * 24 * 3600, chemin_cookie());
		$_COOKIE["last_search_params"] = $valeur;
	}
}

function lire_parametres_derniere_recherche(): array
{
	if (!isset($_COOKIE["last_search_params"])) {
		return [];
	}

	$parametres = json_decode((string) $_COOKIE["last_search_params"], true);

	if (!is_array($parametres)) {
		setcookie("last_search_params", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["last_search_params"]);
		return [];
	}

	return normaliser_parametres_recherche($parametres);
}

function effacer_parametres_derniere_recherche(): void
{
	setcookie("last_search_params", "", time() - 3600, chemin_cookie());
	unset($_COOKIE["last_search_params"]);
}

function lien_recherche_memorisee(): string
{
	$parametres = lire_parametres_derniere_recherche();

	if ($parametres === []) {
		return "recherche.php#recherche";
	}

	unset($parametres["use_geo"]);
	return "recherche.php?" . http_build_query($parametres) . "#recherche";
}

function lien_resultats_memorises(): string
{
	$parametres = lire_parametres_derniere_recherche();

	if ($parametres === []) {
		return "resultats.php";
	}

	return "resultats.php?" . http_build_query($parametres) . "#resultats";
}

function enregistrer_derniere_ville(string $codeVille): void
{
	if ($codeVille !== "") {
		setcookie("last_visited_city", $codeVille, time() + 30 * 24 * 3600, chemin_cookie());
		$_COOKIE["last_visited_city"] = $codeVille;
		enregistrer_derniere_recherche("ville", $codeVille);
	}
}

function lire_derniere_ville(): string
{
	if (isset($_COOKIE["last_visited_city"])) {
		return (string) $_COOKIE["last_visited_city"];
	}

	return "";
}

function lire_csv_assoc(string $fichier): array
{
	$lignes = [];

	if (!file_exists($fichier)) {
		return $lignes;
	}

	$handle = fopen($fichier, "r");
	if ($handle === false) {
		return $lignes;
	}

	$entetes = fgetcsv($handle, 0, ",", "\"", "\\");
	if (!is_array($entetes)) {
		fclose($handle);
		return $lignes;
	}

	while (($valeurs = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
		if (count($valeurs) === count($entetes)) {
			$lignes[] = array_combine($entetes, $valeurs);
		}
	}

	fclose($handle);
	return $lignes;
}

function lire_regions(): array
{
	static $regions = null;

	if ($regions === null) {
		$regions = lire_csv_assoc(PM_DATA_DIR . "/regions.csv");
	}

	return $regions;
}

function lire_departements(): array
{
	static $departements = null;

	if ($departements === null) {
		$departements = lire_csv_assoc(PM_DATA_DIR . "/departments.csv");
	}

	return $departements;
}

function lire_villes(): array
{
	static $villes = null;

	if ($villes === null) {
		$villes = lire_csv_assoc(PM_CITIES_INDEX_FILE);
	}

	return $villes;
}

function trouver_region(string $code): ?array
{
	foreach (lire_regions() as $region) {
		if ($region["region_code"] === $code) {
			return $region;
		}
	}

	return null;
}

function trouver_departement(string $code): ?array
{
	foreach (lire_departements() as $department) {
		if ($department["department_code"] === $code) {
			return $department;
		}
	}

	return null;
}

function trouver_ville(string $code): ?array
{
	static $indexVilles = null;

	if ($indexVilles === null) {
		$indexVilles = [];

		foreach (lire_villes() as $city) {
			$indexVilles[$city["city_code"]] = $city;
		}
	}

	return $indexVilles[$code] ?? null;
}

function departements_par_region(string $codeRegion): array
{
	$resultat = [];

	foreach (lire_departements() as $department) {
		if ($codeRegion === "" || $department["region_code"] === $codeRegion) {
			$resultat[] = $department;
		}
	}

	usort($resultat, static function (array $a, array $b): int {
		return strcmp($a["department_name"], $b["department_name"]);
	});

	return $resultat;
}

function villes_par_departement(string $codeDepartment): array
{
	static $cacheDepartements = [];

	if ($codeDepartment === "") {
		return [];
	}

	if (!isset($cacheDepartements[$codeDepartment])) {
		$fichier = PM_CITIES_BY_DEPARTMENT_DIR . "/" . $codeDepartment . ".csv";
		$cacheDepartements[$codeDepartment] = lire_csv_assoc($fichier);
	}

	return $cacheDepartements[$codeDepartment];
}

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

function normaliser_carburants_selection($fuelInput): array
{
	$carburantsValides = array_keys(liste_carburants());

	if (!is_array($fuelInput)) {
		if (is_string($fuelInput) && $fuelInput !== "") {
			$fuelInput = [$fuelInput];
		} else {
			$fuelInput = [];
		}
	}

	$resultat = [];
	foreach ($fuelInput as $fuel) {
		if (is_string($fuel) && in_array($fuel, $carburantsValides, true)) {
			$resultat[] = $fuel;
		}
	}

	$resultat = array_values(array_unique($resultat));

	if ($resultat === []) {
		return ["Gazole"];
	}

	return $resultat;
}

function texte_carburants_selectionnes(array $fuelTypes): string
{
	$labels = liste_carburants();
	$noms = [];

	foreach ($fuelTypes as $fuelType) {
		if (isset($labels[$fuelType])) {
			$noms[] = $labels[$fuelType];
		}
	}

	return implode(", ", $noms);
}

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

function lire_api_avec_cache(string $url, string $nomCache): ?string
{
	$fichierCache = PM_CACHE_DIR . "/" . $nomCache . ".json";
	$duree = 21600;
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

	$contenu = @file_get_contents($url, false, $contexte);

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

function recuperer_geolocalisation(): array
{
	$ip = recuperer_ip_visiteur();

	if ($ip === "127.0.0.1" || $ip === "::1") {
		$local = json_decode((string) file_get_contents(PM_DATA_DIR . "/sample_ip_geo.json"), true);

		return [
			"source" => "echantillon local",
			"ip" => $ip,
			"city" => (string) ($local["city"] ?? "Paris"),
			"region" => (string) ($local["region"] ?? "Ile-de-France"),
			"latitude" => (float) ($local["latitude"] ?? 48.8566),
			"longitude" => (float) ($local["longitude"] ?? 2.3522),
		];
	}

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

	$local = json_decode((string) file_get_contents(PM_DATA_DIR . "/sample_ip_geo.json"), true);

	return [
		"source" => "echantillon local",
		"ip" => $ip,
		"city" => (string) ($local["city"] ?? "Paris"),
		"region" => (string) ($local["region"] ?? "Ile-de-France"),
		"latitude" => (float) ($local["latitude"] ?? 48.8566),
		"longitude" => (float) ($local["longitude"] ?? 2.3522),
	];
}

function calculer_distance_km(float $lat1, float $lon1, float $lat2, float $lon2): float
{
	$rayonTerre = 6371;
	$dLat = deg2rad($lat2 - $lat1);
	$dLon = deg2rad($lon2 - $lon1);

	$a = sin($dLat / 2) * sin($dLat / 2)
		+ cos(deg2rad($lat1)) * cos(deg2rad($lat2))
		* sin($dLon / 2) * sin($dLon / 2);

	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	return $rayonTerre * $c;
}

function trouver_ville_plus_proche(float $latitude, float $longitude): ?array
{
	$villeProche = null;
	$distanceMini = null;

	foreach (lire_villes() as $city) {
		$distance = calculer_distance_km($latitude, $longitude, (float) $city["latitude"], (float) $city["longitude"]);

		if ($distanceMini === null || $distance < $distanceMini) {
			$distanceMini = $distance;
			$villeProche = $city;
			$villeProche["distance"] = $distance;
		}
	}

	return $villeProche;
}

function arrondir_coordonnee_cache(float $value): float
{
	return round($value, 2);
}

function lire_stations_api(?array $city, bool $departmentMode = false, ?array $origin = null, int $radiusKm = 10): ?array
{
	if ($city === null && $origin === null) {
		return [];
	}

	$departement = (string) ($city["department_code"] ?? "");
	$ville = trim((string) ($city["city_name"] ?? ""));
	$filtres = [];

	if ($origin !== null) {
		$latitude = arrondir_coordonnee_cache((float) ($origin["latitude"] ?? 0));
		$longitude = arrondir_coordonnee_cache((float) ($origin["longitude"] ?? 0));
		$rayonKm = normaliser_rayon_geo($radiusKm);

		$filtres[] = "within_distance(geom, geom'POINT(" . $longitude . " " . $latitude . ")', " . $rayonKm . " km)";
	} else {
		if ($departement !== "") {
			$filtres[] = 'code_departement="' . addslashes($departement) . '"';
		}

		if (!$departmentMode && $ville !== "") {
			$filtres[] = 'ville="' . addslashes($ville) . '"';
		}
	}

	if ($filtres === []) {
		return [];
	}

	$where = implode(" AND ", $filtres);
	$url = "https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/"
		. "prix-des-carburants-en-france-flux-instantane-v2/records?"
		. "where=" . rawurlencode($where)
		. "&limit=100"
		. "&timezone=Europe%2FParis";

	$contenu = lire_api_avec_cache($url, "fuel_search_" . md5($url));

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
			"SP95" => "sp95_prix",
			"SP98" => "sp98_prix",
			"Gazole" => "gazole_prix",
			"E10" => "e10_prix",
			"E85" => "e85_prix",
			"GPLc" => "gplc_prix",
		];

		foreach ($carburants as $nomCarburant => $champPrix) {
			if (isset($ligne[$champPrix]) && $ligne[$champPrix] !== null && $ligne[$champPrix] !== "") {
				$prix[$nomCarburant] = [
					"name" => $nomCarburant,
					"value" => (float) $ligne[$champPrix],
					"updated_at" => "",
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
			$adresseStation = trim((string) ($ligne["adresse"] ?? ""));
			if ($adresseStation !== "") {
				$nomStation .= " - " . $adresseStation;
			}
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

function rechercher_stations(?array $city, array $fuelTypes, string $sortBy, bool $departmentMode = false, ?array $origin = null, int $radiusKm = 10): array
{
	if ($city === null && $origin === null) {
		return [];
	}

	$resultat = [];
	$stationsDisponibles = lire_stations_api($city, $departmentMode, $origin, $radiusKm);

	if ($stationsDisponibles === null) {
		return [];
	}

	$referenceLatitude = (float) ($origin["latitude"] ?? $city["latitude"] ?? 0);
	$referenceLongitude = (float) ($origin["longitude"] ?? $city["longitude"] ?? 0);

	foreach ($stationsDisponibles as $station) {
		$distance = calculer_distance_km(
			$referenceLatitude,
			$referenceLongitude,
			(float) $station["latitude"],
			(float) $station["longitude"]
		);

		$prixSelectionnes = [];
		foreach ($fuelTypes as $fuelType) {
			if (isset($station["prices"][$fuelType])) {
				$prixSelectionnes[$fuelType] = (float) $station["prices"][$fuelType]["value"];
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
		$resultat[] = $station;
	}

	usort($resultat, static function (array $a, array $b) use ($sortBy): int {
		if ($sortBy === "distance") {
			return $a["distance"] <=> $b["distance"];
		}

		if ($sortBy === "name") {
			return strcmp($a["name"], $b["name"]);
		}

		return ($a["main_price"] ?? 999) <=> ($b["main_price"] ?? 999);
	});

	return $resultat;
}

function formater_prix(?float $prix): string
{
	if ($prix === null) {
		return "Indisponible";
	}

	return number_format($prix, 3, ",", " ") . " EUR/L";
}

function lire_stations_xml_demo(): array
{
	$fichier = PM_DATA_DIR . "/sample_fuel_prices.xml";

	if (!file_exists($fichier)) {
		return [];
	}

	$xml = simplexml_load_file($fichier);

	if ($xml === false) {
		return [];
	}

	$stations = [];

	foreach ($xml->pdv as $pdv) {
		$prix = [];
		$services = [];

		foreach ($pdv->prix as $prixXml) {
			$prix[] = [
				"nom" => (string) ($prixXml["nom"] ?? ""),
				"valeur" => (string) ($prixXml["valeur"] ?? ""),
				"maj" => (string) ($prixXml["maj"] ?? ""),
			];
		}

		foreach ($pdv->services->service ?? [] as $serviceXml) {
			$services[] = (string) $serviceXml;
		}

		$stations[] = [
			"id" => (string) ($pdv["id"] ?? ""),
			"cp" => (string) ($pdv["cp"] ?? ""),
			"adresse" => (string) $pdv->adresse,
			"ville" => (string) $pdv->ville,
			"enseigne" => (string) $pdv->enseigne,
			"prix" => $prix,
			"services" => $services,
		];
	}

	return $stations;
}

function lire_tendances_prix_officielles(?int $annee = null, array $carburants = ["Gazole", "SP95", "SP98", "E10"]): array
{
	$annee = $annee ?? (int) date("Y");
	$cleCarburants = md5(implode("|", $carburants));
	$fichierCacheResultats = PM_CACHE_DIR . "/fuel_trends_" . $annee . "_" . $cleCarburants . ".json";
	$dureeCache = 24 * 3600;

	$cache = lire_cache_api($fichierCacheResultats);
	if ($cache !== null && time() - (int) $cache["time"] < $dureeCache) {
		$donnees = json_decode((string) $cache["body"], true);
		if (is_array($donnees)) {
			return $donnees;
		}
	}

	$fichierZip = PM_CACHE_DIR . "/fuel_history_" . $annee . ".zip";
	if (!file_exists($fichierZip) || time() - filemtime($fichierZip) > $dureeCache) {
		$url = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
		$contexte = stream_context_create([
			"http" => [
				"timeout" => 20,
				"header" => "User-Agent: PleinMalin/1.0\r\n",
			],
			"ssl" => [
				"verify_peer" => false,
				"verify_peer_name" => false,
			],
		]);

		$contenu = @file_get_contents($url, false, $contexte);
		if ($contenu === false || $contenu === "") {
			return [
				"source" => "archive officielle indisponible",
				"year" => $annee,
				"fuels" => [],
			];
		}

		file_put_contents($fichierZip, $contenu);
	}

	$zip = new ZipArchive();
	if ($zip->open($fichierZip) !== true) {
		return [
			"source" => "archive officielle illisible",
			"year" => $annee,
			"fuels" => [],
		];
	}

	$nomXml = "";
	for ($i = 0; $i < $zip->numFiles; $i++) {
		$nom = (string) $zip->getNameIndex($i);
		if (strtolower(pathinfo($nom, PATHINFO_EXTENSION)) === "xml") {
			$nomXml = $nom;
			break;
		}
	}
	$zip->close();

	if ($nomXml === "") {
		return [
			"source" => "archive officielle sans XML",
			"year" => $annee,
			"fuels" => [],
		];
	}

	$agregats = [];
	foreach ($carburants as $carburant) {
		$agregats[$carburant] = [];
	}

	$lecteur = new XMLReader();
	$cheminZip = "zip://" . realpath($fichierZip) . "#" . $nomXml;
	if (!$lecteur->open($cheminZip)) {
		return [
			"source" => "archive officielle non ouverte",
			"year" => $annee,
			"fuels" => [],
		];
	}

	while ($lecteur->read()) {
		if ($lecteur->nodeType !== XMLReader::ELEMENT || $lecteur->name !== "prix") {
			continue;
		}

		$nomCarburant = (string) $lecteur->getAttribute("nom");
		if (!isset($agregats[$nomCarburant])) {
			continue;
		}

		$valeur = (float) $lecteur->getAttribute("valeur");
		$dateMaj = (string) $lecteur->getAttribute("maj");
		$mois = substr($dateMaj, 0, 7);

		if ($valeur <= 0 || strlen($mois) !== 7) {
			continue;
		}

		if (!isset($agregats[$nomCarburant][$mois])) {
			$agregats[$nomCarburant][$mois] = [
				"sum" => 0.0,
				"count" => 0,
			];
		}

		$agregats[$nomCarburant][$mois]["sum"] += $valeur;
		$agregats[$nomCarburant][$mois]["count"]++;
	}
	$lecteur->close();

	$tendances = [];
	foreach ($agregats as $carburant => $moisAgreges) {
		ksort($moisAgreges);
		$tendances[$carburant] = [];

		foreach ($moisAgreges as $mois => $agregat) {
			if ($agregat["count"] > 0) {
				$tendances[$carburant][] = [
					"month" => $mois,
					"average_price" => round($agregat["sum"] / $agregat["count"], 3),
					"price_count" => $agregat["count"],
				];
			}
		}
	}

	$resultat = [
		"source" => "archive annuelle officielle XML",
		"year" => $annee,
		"fuels" => $tendances,
	];

	file_put_contents($fichierCacheResultats, json_encode([
		"time" => time(),
		"body" => json_encode($resultat),
	], JSON_PRETTY_PRINT));

	return $resultat;
}

function points_graphique_tendance(array $months, int $largeur = 420, int $hauteur = 170): string
{
	if (count($months) < 2) {
		return "";
	}

	$prix = array_map(static function (array $month): float {
		return (float) $month["average_price"];
	}, $months);

	$min = min($prix);
	$max = max($prix);
	$marge = 16;
	$amplitude = $max - $min;

	if ($amplitude <= 0) {
		$amplitude = 1;
	}

	$points = [];
	$dernierIndex = count($months) - 1;

	foreach ($prix as $index => $valeur) {
		$x = $marge + ($index / $dernierIndex) * ($largeur - 2 * $marge);
		$y = $hauteur - $marge - (($valeur - $min) / $amplitude) * ($hauteur - 2 * $marge);
		$points[] = round($x, 1) . "," . round($y, 1);
	}

	return implode(" ", $points);
}

function graduations_prix_tendance(array $months, int $largeur = 420, int $hauteur = 170, int $nombre = 4): array
{
	if ($months === []) {
		return [];
	}

	$prix = array_map(static function (array $month): float {
		return (float) $month["average_price"];
	}, $months);

	$min = min($prix);
	$max = max($prix);
	$amplitude = $max - $min;
	$marge = 16;

	if ($amplitude <= 0) {
		$amplitude = 0.1;
		$min -= 0.05;
		$max += 0.05;
	}

	$graduations = [];

	for ($i = 0; $i <= $nombre; $i++) {
		$ratio = $i / $nombre;
		$valeur = $min + $ratio * ($max - $min);
		$y = $hauteur - $marge - $ratio * ($hauteur - 2 * $marge);

		$graduations[] = [
			"value" => round($valeur, 3),
			"y" => round($y, 1),
			"x1" => $marge,
			"x2" => $largeur - $marge,
		];
	}

	return $graduations;
}

function graduations_mois_tendance(array $months, int $largeur = 420): array
{
	if ($months === []) {
		return [];
	}

	$marge = 16;
	$dernierIndex = count($months) - 1;
	$graduations = [];

	foreach ($months as $index => $month) {
		$x = $dernierIndex === 0 ? $largeur / 2 : $marge + ($index / $dernierIndex) * ($largeur - 2 * $marge);
		$graduations[] = [
			"label" => substr((string) $month["month"], 5, 2),
			"x" => round($x, 1),
		];
	}

	return $graduations;
}

function enregistrer_consultation(array $infos): void
{
	$fichier = PM_STORAGE_DIR . "/consultations.csv";
	$ip = recuperer_ip_visiteur();
	$visiteur = sha1($ip);

	$ligne = [
		date("c"),
		$visiteur,
		$infos["region"] ?? "",
		$infos["department"] ?? "",
		$infos["city"] ?? "",
		$infos["mode"] ?? "",
		$infos["view"] ?? "",
		$infos["fuel"] ?? "",
		(string) ($infos["station_count"] ?? 0),
	];

	$handle = fopen($fichier, "a");
	if ($handle !== false) {
		fputcsv($handle, $ligne, ",", "\"", "\\");
		fclose($handle);
	}
}

function calculer_statistiques(): array
{
	$lignes = lire_csv_assoc(PM_STORAGE_DIR . "/consultations.csv");
	$topVilles = [];
	$topDepartements = [];
	$topRegions = [];
	$visiteurs = [];

	foreach ($lignes as $ligne) {
		$mode = trim($ligne["mode"] ?? "");
		$ville = trim($ligne["city"] ?? "");
		if ($ville !== "" && $mode !== "departement") {
			if (!isset($topVilles[$ville])) {
				$topVilles[$ville] = 0;
			}
			$topVilles[$ville]++;
		}

		$departement = trim($ligne["department"] ?? "");
		if ($departement !== "") {
			if (!isset($topDepartements[$departement])) {
				$topDepartements[$departement] = 0;
			}
			$topDepartements[$departement]++;
		}

		$region = trim($ligne["region"] ?? "");
		if ($region !== "") {
			if (!isset($topRegions[$region])) {
				$topRegions[$region] = 0;
			}
			$topRegions[$region]++;
		}

		$hash = trim($ligne["visitor_hash"] ?? "");
		if ($hash !== "") {
			$visiteurs[$hash] = true;
		}
	}

	arsort($topVilles);
	arsort($topDepartements);
	arsort($topRegions);

	return [
		"top_cities" => array_slice($topVilles, 0, 8, true),
		"top_departments" => array_slice($topDepartements, 0, 8, true),
		"top_regions" => array_slice($topRegions, 0, 8, true),
		"total_visitors" => count($visiteurs),
		"consultation_count" => count($lignes),
	];
}
