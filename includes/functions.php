<?php

define("PM_CACHE_DIR", __DIR__ . "/../cache");
define("PM_DATA_DIR", __DIR__ . "/../data");
define("PM_STORAGE_DIR", __DIR__ . "/../storage");
define("PM_CITIES_FILE", __DIR__ . "/../ressources/Postcodes Cours 2025-2026.csv");

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

function enregistrer_derniere_ville(string $codeVille): void
{
	if ($codeVille !== "") {
		setcookie("last_visited_city", $codeVille, time() + 30 * 24 * 3600, chemin_cookie());
		$_COOKIE["last_visited_city"] = $codeVille;
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
	return lire_csv_assoc(PM_DATA_DIR . "/regions.csv");
}

function lire_departements(): array
{
	return lire_csv_assoc(PM_DATA_DIR . "/departments.csv");
}

function lire_villes(): array
{
	$lignes = lire_csv_assoc(PM_CITIES_FILE);
	$villes = [];

	foreach ($lignes as $ligne) {
		$codeInsee = trim($ligne["code_commune_insee"] ?? "");
		$nomVille = trim($ligne["nom_de_la_commune"] ?? "");
		$codePostal = trim($ligne["code_postal"] ?? "");

		if ($codeInsee === "" || $nomVille === "" || $codePostal === "") {
			continue;
		}

		if (!isset($villes[$codeInsee])) {
			$villes[$codeInsee] = [
				"city_code" => $codeInsee,
				"city_name" => formater_nom_ville($nomVille),
				"postal_code" => $codePostal,
				"department_code" => trouver_code_departement($codeInsee),
				"latitude" => (float) ($ligne["latitude"] ?? 0),
				"longitude" => (float) ($ligne["longitude"] ?? 0),
			];
		}
	}

	$villes = array_values($villes);

	usort($villes, static function (array $a, array $b): int {
		return strcmp($a["city_name"], $b["city_name"]);
	});

	return $villes;
}

function formater_nom_ville(string $nomVille): string
{
	return ucwords(strtolower($nomVille));
}

function trouver_code_departement(string $codeInsee): string
{
	if (str_starts_with($codeInsee, "2A") || str_starts_with($codeInsee, "2B")) {
		return substr($codeInsee, 0, 2);
	}

	if (str_starts_with($codeInsee, "971") || str_starts_with($codeInsee, "972") || str_starts_with($codeInsee, "973") || str_starts_with($codeInsee, "974") || str_starts_with($codeInsee, "976")) {
		return substr($codeInsee, 0, 3);
	}

	return substr($codeInsee, 0, 2);
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
	foreach (lire_villes() as $city) {
		if ($city["city_code"] === $code) {
			return $city;
		}
	}

	return null;
}

function trouver_ville_par_nom(string $nomVille): ?array
{
	foreach (lire_villes() as $city) {
		if (strtolower($city["city_name"]) === strtolower($nomVille)) {
			return $city;
		}
	}

	return null;
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
	$resultat = [];

	foreach (lire_villes() as $city) {
		if ($codeDepartment === "" || $city["department_code"] === $codeDepartment) {
			$resultat[] = $city;
		}
	}

	return $resultat;
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

function lire_api_avec_cache(string $url, string $nomCache): ?string
{
	$fichierCache = PM_CACHE_DIR . "/" . $nomCache . ".json";
	$duree = 21600;
	$maintenant = time();

	if (file_exists($fichierCache)) {
		$cache = json_decode((string) file_get_contents($fichierCache), true);
		if (is_array($cache) && isset($cache["time"], $cache["body"])) {
			if (($maintenant - $cache["time"]) < $duree) {
				return (string) $cache["body"];
			}
		}
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

	if (file_exists($fichierCache)) {
		$cache = json_decode((string) file_get_contents($fichierCache), true);
		if (is_array($cache) && isset($cache["body"])) {
			return (string) $cache["body"];
		}
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
		$ip = "8.8.8.8";
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

function lire_stations(): array
{
	$contenuXml = lire_api_avec_cache("https://donnees.roulez-eco.fr/opendata/instantane", "fuel_xml");

	if ($contenuXml === null) {
		$contenuXml = (string) file_get_contents(PM_DATA_DIR . "/sample_fuel_prices.xml");
		$source = "xml local";
	} else {
		$source = "api xml";
	}

	libxml_use_internal_errors(true);
	$xml = simplexml_load_string($contenuXml);

	if ($xml === false) {
		$xml = simplexml_load_file(PM_DATA_DIR . "/sample_fuel_prices.xml");
		$source = "xml local";
	}

	$stations = [];

	if ($xml && isset($xml->pdv)) {
		foreach ($xml->pdv as $pdv) {
			$services = [];
			if (isset($pdv->services)) {
				foreach ($pdv->services->service as $service) {
					$services[] = trim((string) $service);
				}
			}

			$prix = [];
			foreach ($pdv->prix as $unPrix) {
				$nom = (string) $unPrix["nom"];
				$prix[$nom] = [
					"name" => $nom,
					"value" => (float) $unPrix["valeur"],
					"updated_at" => (string) $unPrix["maj"],
				];
			}

			$nomVille = (string) $pdv->ville;
			$villeLocale = trouver_ville_par_nom($nomVille);
			$enseigne = trim((string) $pdv->enseigne);

			$stations[] = [
				"id" => (string) $pdv["id"],
				"name" => $enseigne !== "" ? $enseigne : "Station " . (string) $pdv["id"],
				"address" => (string) $pdv->adresse,
				"postal_code" => (string) $pdv["cp"],
				"city_name" => $nomVille,
				"department_code" => $villeLocale["department_code"] ?? substr((string) $pdv["cp"], 0, 2),
				"latitude" => ((float) $pdv["latitude"]) / 100000,
				"longitude" => ((float) $pdv["longitude"]) / 100000,
				"services" => $services,
				"prices" => $prix,
				"source" => $source,
			];
		}
	}

	return $stations;
}

function rechercher_stations(?array $city, string $fuelType, string $sortBy, bool $departmentMode = false): array
{
	if ($city === null) {
		return [];
	}

	$resultat = [];

	foreach (lire_stations() as $station) {
		$distance = calculer_distance_km(
			(float) $city["latitude"],
			(float) $city["longitude"],
			(float) $station["latitude"],
			(float) $station["longitude"]
		);

		$memeVille = strtolower($station["city_name"]) === strtolower($city["city_name"]);
		$memeDepartement = $station["department_code"] === $city["department_code"];
		$dansLesEnvirons = $memeVille || $distance <= 25;

		if ($departmentMode && !$memeDepartement) {
			continue;
		}

		if (!$departmentMode && !$dansLesEnvirons) {
			continue;
		}

		if ($fuelType !== "" && !isset($station["prices"][$fuelType])) {
			continue;
		}

		$prixPrincipal = null;
		if ($fuelType !== "" && isset($station["prices"][$fuelType])) {
			$prixPrincipal = (float) $station["prices"][$fuelType]["value"];
		}

		$station["distance"] = $distance;
		$station["main_price"] = $prixPrincipal;
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
		fputcsv($handle, $ligne);
		fclose($handle);
	}
}

function calculer_statistiques(): array
{
	$lignes = lire_csv_assoc(PM_STORAGE_DIR . "/consultations.csv");
	$topVilles = [];
	$visiteurs = [];

	foreach ($lignes as $ligne) {
		$ville = trim($ligne["city"] ?? "");
		if ($ville !== "") {
			if (!isset($topVilles[$ville])) {
				$topVilles[$ville] = 0;
			}
			$topVilles[$ville]++;
		}

		$hash = trim($ligne["visitor_hash"] ?? "");
		if ($hash !== "") {
			$visiteurs[$hash] = true;
		}
	}

	arsort($topVilles);

	return [
		"top_cities" => array_slice($topVilles, 0, 8, true),
		"total_visitors" => count($visiteurs),
		"consultation_count" => count($lignes),
	];
}
