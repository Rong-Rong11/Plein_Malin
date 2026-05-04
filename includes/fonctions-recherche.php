<?php
declare(strict_types=1);

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
	if (isset($ville["department_code"])) {
		$departement = $ville["department_code"];
	} else {
		$departement = "";
	}

	$infosDepartement = trouver_departement($departement);

	if (isset($infosDepartement["region_code"])) {
		$region = $infosDepartement["region_code"];
	} else {
		$region = "";
	}

	if (isset($ville["city_code"])) {
		$codeVille = $ville["city_code"];
	} else {
		$codeVille = "";
	}

	$lien = "resultats.php?region=" . rawurlencode($region);
	$lien .= "&department=" . rawurlencode($departement);
	$lien .= "&city=" . rawurlencode($codeVille);
	$lien .= "#resultats";

	return $lien;
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

	if (isset($infosDepartement["region_code"])) {
		$region = $infosDepartement["region_code"];
	} else {
		$region = "";
	}

	$lien = "resultats.php?region=" . rawurlencode($region);
	$lien .= "&department=" . rawurlencode($codeDepartement);
	$lien .= "&department_mode=1";
	$lien .= "#resultats";

	return $lien;
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
	$listeCarburants = liste_carburants();
	$carburantsValides = [];

	foreach ($listeCarburants as $codeCarburant => $nomCarburant) {
		$carburantsValides[] = $codeCarburant;
	}

	if (is_array($entreeCarburants)) {
		$selectionCarburants = $entreeCarburants;
	} else {
		if (is_string($entreeCarburants) && $entreeCarburants !== "") {
			$selectionCarburants = [$entreeCarburants];
		} else {
			$selectionCarburants = [];
		}
	}

	$resultat = [];
	foreach ($selectionCarburants as $carburant) {
		$estUnTexte = is_string($carburant);
		$estUnCarburantValide = in_array($carburant, $carburantsValides, true);
		$dejaAjoute = in_array($carburant, $resultat, true);

		if ($estUnTexte && $estUnCarburantValide && !$dejaAjoute) {
			$resultat[] = $carburant;
		}
	}

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
	$texte = "";

	foreach ($typesCarburants as $typeCarburant) {
		if (isset($libelles[$typeCarburant])) {
			if ($texte !== "") {
				$texte .= ", ";
			}

			$texte .= $libelles[$typeCarburant];
		}
	}

	return $texte;
}

/**
 * Rend certains libelles de services plus lisibles.
 *
 * @param string $service Libelle brut venant de l'API.
 * @return string Libelle formate pour l'affichage.
 *
 * @ingroup recherche
 */
function normaliser_libelle_service_station(string $service): string
{
	$serviceNettoye = trim($service);

	if ($serviceNettoye === "") {
		return "";
	}

	$correspondances = [
		"Automate CB 24/24" => "Automate CB 24/24",
		"Automate CB" => "Automate CB",
		"DAB" => "Distributeur de billets",
		"Gonflage" => "Pompe à air",
		"Station de gonflage" => "Pompe à air",
		"Lavage automatique" => "Lavage automatique",
		"Lavage manuel" => "Lavage manuel",
		"Toilettes publiques" => "Toilettes",
	];

	if (isset($correspondances[$serviceNettoye])) {
		return $correspondances[$serviceNettoye];
	}

	return $serviceNettoye;
}

/**
 * Prepare la liste des services a afficher sur une carte station.
 *
 * @param array $services Liste brute issue de l'API.
 * @return string[] Services dedoublonnes et tries.
 *
 * @ingroup recherche
 */
function services_station_affichables(array $services): array
{
	$servicesAffichables = [];

	foreach ($services as $service) {
		if (is_string($service)) {
			$serviceFormate = normaliser_libelle_service_station($service);
			if ($serviceFormate !== "") {
				$dejaAjoute = in_array($serviceFormate, $servicesAffichables, true);

				if (!$dejaAjoute) {
					$servicesAffichables[] = $serviceFormate;
				}
			}
		}
	}

	sort($servicesAffichables, SORT_NATURAL | SORT_FLAG_CASE);

	return $servicesAffichables;
}

/**
 * Indique si la station propose un service de gonflage.
 *
 * @param array $services Liste brute issue de l'API.
 * @return bool Vrai si une pompe a air est detectee.
 *
 * @ingroup recherche
 */
function station_a_une_pompe_a_air(array $services): bool
{
	foreach ($services as $service) {
		if (!is_string($service)) {
			continue;
		}

		$serviceNormalise = strtolower(trim($service));
		if ($serviceNormalise !== "" && (str_contains($serviceNormalise, "gonflage") || str_contains($serviceNormalise, "air"))) {
			return true;
		}
	}

	return false;
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
	$dureeCache = PM_API_CACHE_DURATION;
	$dateActuelle = time();
	$cache = lire_cache_api($fichierCache);

	if ($cache !== null) {
		$ageCache = $dateActuelle - $cache["time"];

		if ($ageCache < $dureeCache) {
			return $cache["body"];
		}
	}

	$contenuApi = "";
	$fichierDistant = @fopen($adresseUrl, "r");

	if ($fichierDistant !== false) {
		while (!feof($fichierDistant)) {
			$ligne = fgets($fichierDistant);

			if ($ligne !== false) {
				$contenuApi .= $ligne;
			}
		}

		fclose($fichierDistant);
	}

	if ($contenuApi !== "") {
		$nouveauCache = [
			"time" => $dateActuelle,
			"body" => $contenuApi,
		];
		$contenuCache = json_encode($nouveauCache, JSON_PRETTY_PRINT);

		if ($contenuCache !== false) {
			$fichierOuvert = fopen($fichierCache, "w");

			if ($fichierOuvert !== false) {
				fwrite($fichierOuvert, $contenuCache);
				fclose($fichierOuvert);
			}
		}

		return $contenuApi;
	}

	if ($cache !== null) {
		return $cache["body"];
	}

	return null;
}
/**
 * Fabrique un nom de cache lisible a partir d'un texte.
 *
 * @param string $prefixe Debut du nom de cache.
 * @param string $texte Texte a transformer.
 * @return string Nom utilisable pour un fichier de cache.
 */
function nom_cache_depuis_texte(string $prefixe, string $texte): string
{
	$nom = $prefixe;
	$somme = 0;
	$longueurTexte = strlen($texte);

	for ($position = 0; $position < $longueurTexte; $position++) {
		$caractere = $texte[$position];
		$somme += ord($caractere) * ($position + 1);

		if (strlen($nom) < 100) {
			$estUneLettreMinuscule = $caractere >= "a" && $caractere <= "z";
			$estUneLettreMajuscule = $caractere >= "A" && $caractere <= "Z";
			$estUnChiffre = $caractere >= "0" && $caractere <= "9";

			if ($estUneLettreMinuscule || $estUneLettreMajuscule || $estUnChiffre) {
				$nom .= $caractere;
			} else {
				$nom .= "_";
			}
		}
	}

	return $nom . "_" . $longueurTexte . "_" . $somme;
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
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && $_SERVER["HTTP_X_FORWARDED_FOR"] !== "") {
		$morceaux = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
		return trim($morceaux[0]);
	}

	if (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] !== "") {
		return $_SERVER["REMOTE_ADDR"];
	}

	return "127.0.0.1";
}
/**
 * Indique si l'adresse IP correspond a une machine locale.
 *
 * @param string $ip Adresse IP detectee.
 * @return bool True si l'adresse est locale.
 */
function est_ip_locale(string $ip): bool
{
	return $ip === "127.0.0.1" || $ip === "::1" || $ip === "localhost";
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

	$contenu = lire_api_avec_cache(
		"https://ipapi.co/" . rawurlencode($ip) . "/json/",
		construire_cle_cache("geo", $ip)
	);

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
	$departement = $ville["department_code"] ?? "";
	$codeVilleApi = $ville["city_code"] ?? "";
	$nomVille = trim($ville["city_name"] ?? "");

	if ($departement === "75" && str_starts_with($nomVille, "Paris ")) {
		return true;
	}

	if (str_starts_with($codeVilleApi, "6938") && str_starts_with($nomVille, "Lyon ")) {
		return true;
	}

	$codesMarseille = [
		"13201",
		"13202",
		"13203",
		"13204",
		"13205",
		"13206",
		"13207",
		"13208",
		"13209",
		"13210",
		"13211",
		"13212",
		"13213",
		"13214",
		"13215",
		"13216",
	];

	if (in_array($codeVilleApi, $codesMarseille, true) && str_starts_with($nomVille, "Marseille ")) {
		return true;
	}

	return false;
}
/**
 * Prepare les filtres utilises dans la requete API des stations.
 *
 * @param array $villeInfos Ville ou departement de reference.
 * @param bool $modeDepartement True pour chercher dans tout le departement.
 * @param array|null $origine Position de reference pour la recherche autour de moi.
 * @param int $rayonKm Rayon de recherche en kilometres.
 * @return string[] Filtres a assembler dans la clause where.
 */
function construire_filtres_stations(array $villeInfos, bool $modeDepartement, ?array $origine, int $rayonKm): array
{
	$departement = $villeInfos["department_code"] ?? "";
	$nomVille = trim($villeInfos["city_name"] ?? "");
	$codePostal = trim($villeInfos["postal_code"] ?? "");
	$filtres = [];

	if ($origine !== null) {
		$latitude = arrondir_coordonnee_cache((float) ($origine["latitude"] ?? 0));
		$longitude = arrondir_coordonnee_cache((float) ($origine["longitude"] ?? 0));
		$rayonKm = normaliser_rayon_geo($rayonKm);

		$filtres[] = "within_distance(geom, geom'POINT(" . $longitude . " " . $latitude . ")', " . $rayonKm . " km)";
		return $filtres;
	}

	if ($departement !== "") {
		$filtres[] = 'code_departement="' . addslashes($departement) . '"';
	}

	if (!$modeDepartement && $nomVille !== "") {
		if ($codePostal !== "" && est_arrondissement_municipal($villeInfos)) {
			$filtres[] = 'code_postal="' . addslashes($codePostal) . '"';
		} else {
			$filtres[] = 'ville="' . addslashes($nomVille) . '"';
		}
	}

	return $filtres;
}
/**
 * Assemble les filtres avec AND pour la requete API.
 *
 * @param string[] $filtres Filtres API.
 * @return string Clause where complete.
 */
function construire_clause_where(array $filtres): string
{
	$clauseWhere = "";

	foreach ($filtres as $filtre) {
		if ($clauseWhere !== "") {
			$clauseWhere .= " AND ";
		}

		$clauseWhere .= $filtre;
	}

	return $clauseWhere;
}
/**
 * Construit l'URL de l'API officielle a partir de la clause where.
 *
 * @param string $clauseWhere Filtres assembles.
 * @return string URL API complete.
 */
function construire_url_api_stations(string $clauseWhere): string
{
	return "https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/"
		. "prix-des-carburants-en-france-flux-instantane-v2/records?"
		. "where=" . rawurlencode($clauseWhere)
		. "&limit=" . PM_FUEL_API_LIMIT
		. "&timezone=Europe%2FParis";
}
/**
 * Extrait les prix carburants d'une ligne API.
 *
 * @param array $ligne Ligne issue de l'API.
 * @return array Prix disponibles par carburant.
 */
function extraire_prix_station_api(array $ligne): array
{
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
				"updated_at" => $ligne[$champsCarburant["updated_at"]] ?? "",
			];
		}
	}

	return $prix;
}
/**
 * Extrait les coordonnees GPS d'une ligne API.
 *
 * @param array $ligne Ligne issue de l'API.
 * @return array Coordonnees latitude/longitude.
 */
function extraire_coordonnees_station_api(array $ligne): array
{
	$coordonnees = [
		"latitude" => 0.0,
		"longitude" => 0.0,
	];

	if (isset($ligne["geom"]) && is_array($ligne["geom"]) && isset($ligne["geom"]["lat"], $ligne["geom"]["lon"])) {
		$coordonnees["latitude"] = (float) $ligne["geom"]["lat"];
		$coordonnees["longitude"] = (float) $ligne["geom"]["lon"];
	} elseif (isset($ligne["latitude"], $ligne["longitude"])) {
		$coordonnees["latitude"] = (float) $ligne["latitude"];
		$coordonnees["longitude"] = (float) $ligne["longitude"];
	}

	return $coordonnees;
}
/**
 * Ajoute un service non vide dans la liste.
 *
 * @param array $services Liste de services en cours.
 * @param mixed $service Service brut.
 * @return array Liste mise a jour.
 */
function ajouter_service_station_api(array $services, $service): array
{
	if (is_string($service) && trim($service) !== "") {
		$services[] = trim($service);
	}

	return $services;
}
/**
 * Extrait les services d'une ligne API.
 *
 * @param array $ligne Ligne issue de l'API.
 * @return string[] Services sans doublons.
 */
function extraire_services_station_api(array $ligne): array
{
	$services = [];

	foreach ($ligne as $cle => $valeur) {
		if (!str_starts_with((string) $cle, "services")) {
			continue;
		}

		if (is_string($valeur) && trim($valeur) !== "") {
			$servicesJson = json_decode($valeur, true);

			if (is_array($servicesJson) && isset($servicesJson["service"])) {
				$listeServices = $servicesJson["service"];

				if (is_array($listeServices)) {
					foreach ($listeServices as $service) {
						$services = ajouter_service_station_api($services, $service);
					}
				} else {
					$services = ajouter_service_station_api($services, $listeServices);
				}
			} else {
				$services = ajouter_service_station_api($services, $valeur);
			}
		} elseif (is_array($valeur)) {
			foreach ($valeur as $service) {
				$services = ajouter_service_station_api($services, $service);
			}
		}
	}

	$servicesSansDoublons = [];

	foreach ($services as $service) {
		if (!in_array($service, $servicesSansDoublons, true)) {
			$servicesSansDoublons[] = $service;
		}
	}

	return $servicesSansDoublons;
}
/**
 * Choisit le nom a afficher pour une station.
 *
 * @param array $ligne Ligne issue de l'API.
 * @return string Nom de station.
 */
function nom_station_api(array $ligne): string
{
	$nomStation = trim((string) ($ligne["nom"] ?? ""));

	if ($nomStation === "") {
		$nomStation = trim($ligne["enseigne"] ?? "");
	}

	if ($nomStation === "") {
		$nomStation = "Station " . ($ligne["id"] ?? "");
	}

	return $nomStation;
}
/**
 * Transforme une ligne API en station utilisable par l'interface.
 *
 * @param array $ligne Ligne issue de l'API.
 * @param string $departement Departement de secours.
 * @return array Station normalisee.
 */
function transformer_ligne_api_en_station(array $ligne, string $departement): array
{
	$coordonnees = extraire_coordonnees_station_api($ligne);

	return [
		"id" => $ligne["id"] ?? "",
		"name" => nom_station_api($ligne),
		"address" => $ligne["adresse"] ?? "",
		"postal_code" => $ligne["code_postal"] ?? "",
		"raw_city_name" => $ligne["ville"] ?? "",
		"city_name" => $ligne["ville"] ?? "",
		"department_code" => $ligne["code_departement"] ?? $departement,
		"latitude" => $coordonnees["latitude"],
		"longitude" => $coordonnees["longitude"],
		"services" => extraire_services_station_api($ligne),
		"prices" => extraire_prix_station_api($ligne),
		"source" => "api json",
	];
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

	$villeReference = $ville;
	$departement = (string) ($villeReference["department_code"] ?? "");
	$nomVille = trim((string) ($villeReference["city_name"] ?? ""));
	$codePostal = trim((string) ($villeReference["postal_code"] ?? ""));
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

		if (!$modeDepartement && $nomVille !== "") {
			if ($codePostal !== "" && est_arrondissement_municipal($villeReference)) {
				$filtres[] = 'code_postal="' . addslashes($codePostal) . '"';
			} else {
				$filtres[] = 'ville="' . addslashes($nomVille) . '"';
			}
		}
	}

	if (isset($villeInfos["department_code"])) {
		$departement = $villeInfos["department_code"];
	} else {
		$departement = "";
	}

	$filtres = construire_filtres_stations($villeInfos, $modeDepartement, $origine, $rayonKm);

	if ($filtres === []) {
		return [];
	}

	$clauseWhere = construire_clause_where($filtres);
	$adresseUrl = construire_url_api_stations($clauseWhere);
	$nomCache = nom_cache_depuis_texte("fuel_search_", $adresseUrl);

	$contenu = lire_api_avec_cache($adresseUrl, construire_cle_cache("fuel_search", $adresseUrl));

	if ($contenu === null) {
		return null;
	}

	$donnees = json_decode($contenu, true);

	if (!is_array($donnees)) {
		return null;
	}

	if (!isset($donnees["results"])) {
		return null;
	}

	if (!is_array($donnees["results"])) {
		return null;
	}

	$stations = [];

	foreach ($donnees["results"] as $ligne) {
		$station = transformer_ligne_api_en_station($ligne, $departement);
		$stations[] = $station;
	}

	return $stations;
}
/**
 * Determine les coordonnees de reference pour calculer les distances.
 *
 * @param array|null $ville Ville de reference.
 * @param array|null $origine Position GPS de reference.
 * @return array Coordonnees latitude/longitude.
 */
function coordonnees_reference_recherche(?array $ville, ?array $origine): array
{
	$coordonnees = [
		"latitude" => 0.0,
		"longitude" => 0.0,
	];

	if ($origine !== null) {
		if (isset($origine["latitude"])) {
			$coordonnees["latitude"] = (float) $origine["latitude"];
		}

		if (isset($origine["longitude"])) {
			$coordonnees["longitude"] = (float) $origine["longitude"];
		}

		return $coordonnees;
	}

	if ($ville !== null) {
		if (isset($ville["latitude"])) {
			$coordonnees["latitude"] = (float) $ville["latitude"];
		}

		if (isset($ville["longitude"])) {
			$coordonnees["longitude"] = (float) $ville["longitude"];
		}
	}

	return $coordonnees;
}
/**
 * Garde les prix disponibles pour les carburants selectionnes.
 *
 * @param array $station Station a analyser.
 * @param string[] $typesCarburants Carburants selectionnes.
 * @return array Prix trouves par carburant.
 */
function prix_carburants_selectionnes_station(array $station, array $typesCarburants): array
{
	$prixSelectionnes = [];

	foreach ($typesCarburants as $typeCarburant) {
		if (isset($station["prices"][$typeCarburant])) {
			$prixSelectionnes[$typeCarburant] = (float) $station["prices"][$typeCarburant]["value"];
		}
	}

	return $prixSelectionnes;
}
/**
 * Trouve le carburant le moins cher parmi les prix selectionnes.
 *
 * @param array $prixSelectionnes Prix par carburant.
 * @return array Prix principal et carburant principal.
 */
function trouver_prix_principal_station(array $prixSelectionnes): array
{
	$prixPrincipal = null;
	$carburantPrincipal = "";

	foreach ($prixSelectionnes as $carburant => $prix) {
		if ($prixPrincipal === null || $prix < $prixPrincipal) {
			$prixPrincipal = $prix;
			$carburantPrincipal = $carburant;
		}
	}

	return [
		"prix" => $prixPrincipal,
		"carburant" => $carburantPrincipal,
	];
}
/**
 * Compare deux nombres pour un tri.
 *
 * @param float $valeurA Premiere valeur.
 * @param float $valeurB Deuxieme valeur.
 * @return int -1 si A avant B, 1 si B avant A, 0 si egalite.
 */
function comparer_nombres_recherche(float $valeurA, float $valeurB): int
{
	if ($valeurA < $valeurB) {
		return -1;
	}

	if ($valeurA > $valeurB) {
		return 1;
	}

	return 0;
}
/**
 * Compare deux stations selon le tri demande.
 *
 * @param array $stationA Premiere station.
 * @param array $stationB Deuxieme station.
 * @param string $tri Mode de tri.
 * @return int Resultat de comparaison.
 */
function comparer_stations_recherche(array $stationA, array $stationB, string $tri): int
{
	if ($tri === "price_desc") {
		$prixA = isset($stationA["main_price"]) ? (float) $stationA["main_price"] : 0;
		$prixB = isset($stationB["main_price"]) ? (float) $stationB["main_price"] : 0;

		return comparer_nombres_recherche($prixB, $prixA);
	}

	if ($tri === "distance") {
		$distanceA = isset($stationA["distance"]) ? (float) $stationA["distance"] : 0;
		$distanceB = isset($stationB["distance"]) ? (float) $stationB["distance"] : 0;

		return comparer_nombres_recherche($distanceA, $distanceB);
	}

	if ($tri === "name") {
		$nomA = isset($stationA["name"]) ? $stationA["name"] : "";
		$nomB = isset($stationB["name"]) ? $stationB["name"] : "";

		return strcmp($nomA, $nomB);
	}

	$prixA = isset($stationA["main_price"]) ? (float) $stationA["main_price"] : 999;
	$prixB = isset($stationB["main_price"]) ? (float) $stationB["main_price"] : 999;

	return comparer_nombres_recherche($prixA, $prixB);
}
/**
 * Ajoute les infos de resultat a une station.
 *
 * @param array $station Station issue de l'API.
 * @param string[] $typesCarburants Carburants selectionnes.
 * @param float $referenceLatitude Latitude de reference.
 * @param float $referenceLongitude Longitude de reference.
 * @return array|null Station enrichie, ou null si aucun prix ne correspond.
 */
function preparer_station_resultat(array $station, array $typesCarburants, float $referenceLatitude, float $referenceLongitude): ?array
{
	$distance = calculer_distance_km(
		$referenceLatitude,
		$referenceLongitude,
		(float) $station["latitude"],
		(float) $station["longitude"]
	);

	$prixSelectionnes = prix_carburants_selectionnes_station($station, $typesCarburants);

	if ($prixSelectionnes === []) {
		return null;
	}

	$infosPrixPrincipal = trouver_prix_principal_station($prixSelectionnes);
	$prixPrincipal = $infosPrixPrincipal["prix"];
	$carburantPrincipal = $infosPrixPrincipal["carburant"];

	$station["distance"] = $distance;
	$station["main_price"] = $prixPrincipal;
	$station["main_fuel"] = $carburantPrincipal;
	$station["main_updated_at"] = $station["prices"][$carburantPrincipal]["updated_at"] ?? "";

	return $station;
}
/**
 * Trie les stations preparees selon le mode choisi.
 *
 * @param array $stations Stations a trier.
 * @param string $tri Mode de tri.
 * @return array Stations triees.
 */
function trier_resultats_stations(array $stations, string $tri): array
{
	$nombreStations = count($stations);

	for ($i = 0; $i < $nombreStations; $i++) {
		for ($j = $i + 1; $j < $nombreStations; $j++) {
			$comparaison = comparer_stations_recherche($stations[$i], $stations[$j], $tri);

			if ($comparaison > 0) {
				$stationTemporaire = $stations[$i];
				$stations[$i] = $stations[$j];
				$stations[$j] = $stationTemporaire;
			}
		}
	}

	return $stations;
}
/**
 * Filtre, enrichit et trie les stations disponibles.
 *
 * @param array $stationsDisponibles Stations issues de l'API.
 * @param array|null $ville Ville ou departement de reference.
 * @param string[] $typesCarburants Carburants selectionnes.
 * @param string $tri Mode de tri.
 * @param array|null $origine Position de reference pour la geolocalisation.
 * @return array<int,array> Stations pretes pour l'affichage.
 */
function preparer_resultats_stations(array $stationsDisponibles, ?array $ville, array $typesCarburants, string $tri, ?array $origine): array
{
	$resultat = [];
	$coordonneesReference = coordonnees_reference_recherche($ville, $origine);
	$referenceLatitude = $coordonneesReference["latitude"];
	$referenceLongitude = $coordonneesReference["longitude"];

	foreach ($stationsDisponibles as $station) {
		$stationPreparee = preparer_station_resultat(
			$station,
			$typesCarburants,
			$referenceLatitude,
			$referenceLongitude
		);

		if ($stationPreparee !== null) {
			$resultat[] = $stationPreparee;
		}
	}

	return trier_resultats_stations($resultat, $tri);
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

	$stationsDisponibles = lire_stations_api($ville, $modeDepartement, $origine, $rayonKm);

	if ($stationsDisponibles === null) {
		return [];
	}

	return preparer_resultats_stations($stationsDisponibles, $ville, $typesCarburants, $tri, $origine);
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

	$resultat = preparer_resultats_stations($stationsDisponibles, $ville, $typesCarburants, $tri, $origine);

	return [
		"stations" => $resultat,
		"api_error" => false,
	];
}
