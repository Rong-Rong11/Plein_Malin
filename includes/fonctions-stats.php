<?php

/**
 * @file
 * @brief Statistiques, suivi des consultations et graphiques.
 */

/**
 * Lit l'archive XML officielle d'une annee et calcule les moyennes mensuelles.
 *
 * @param int $annee Annee a analyser.
 * @param string[] $carburants Carburants a agreger.
 * @return array Donnees mensuelles et moyenne annuelle pour chaque carburant.
 *
 * @ingroup statistiques
 */
function calculer_tendances_prix_annuelles(int $annee, array $carburants): array
{
	$fichierZip = PM_CACHE_DIR . "/fuel_history_" . $annee . ".zip";
	$dureeCache = PM_FUEL_TRENDS_CACHE_DURATION;

	if (!file_exists($fichierZip) || time() - filemtime($fichierZip) > $dureeCache) {
		$adresseUrl = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
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

		$contenu = @file_get_contents($adresseUrl, false, $contexte);
		if ($contenu === false || $contenu === "") {
			return [
				"source" => "archive officielle indisponible",
				"source_url" => $adresseUrl,
				"year" => $annee,
				"fuels" => [],
				"annual_averages" => [],
			];
		}

		file_put_contents($fichierZip, $contenu);
	}

	$zip = new ZipArchive();
	if ($zip->open($fichierZip) !== true) {
		return [
			"source" => "archive officielle illisible",
			"source_url" => "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
			"year" => $annee,
			"fuels" => [],
			"annual_averages" => [],
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
			"source_url" => "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
			"year" => $annee,
			"fuels" => [],
			"annual_averages" => [],
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
			"source_url" => "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
			"year" => $annee,
			"fuels" => [],
			"annual_averages" => [],
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
	$moyennesAnnuelles = [];

	foreach ($agregats as $carburant => $moisAgreges) {
		ksort($moisAgreges);
		$tendances[$carburant] = [];
		$sommeAnnuelle = 0.0;
		$nombreAnnuel = 0;

		foreach ($moisAgreges as $mois => $agregat) {
			if ($agregat["count"] <= 0) {
				continue;
			}

			$moyenneMois = round($agregat["sum"] / $agregat["count"], 3);
			$tendances[$carburant][] = [
				"month" => $mois,
				"average_price" => $moyenneMois,
				"price_count" => $agregat["count"],
			];

			$sommeAnnuelle += $agregat["sum"];
			$nombreAnnuel += $agregat["count"];
		}

		if ($nombreAnnuel > 0) {
			$moyennesAnnuelles[$carburant] = [
				"average_price" => round($sommeAnnuelle / $nombreAnnuel, 3),
				"price_count" => $nombreAnnuel,
			];
		}
	}

	return [
		"source" => "archive annuelle officielle XML",
		"source_url" => "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
		"year" => $annee,
		"fuels" => $tendances,
		"annual_averages" => $moyennesAnnuelles,
	];
}

/**
 * Calcule les moyennes mensuelles de l'annee courante et ajoute une reference annuelle.
 *
 * @param int|null $annee Annee a analyser, annee courante par defaut.
 * @param string[] $carburants Carburants a agreger.
 * @return array Donnees de tendance pretes pour la page statistiques, avec
 *               reference_averages pour l'annee precedente.
 *
 * @ingroup statistiques
 */
function lire_tendances_prix_officielles(?int $annee = null, ?array $carburants = null): array
{
	$annee = $annee ?? (int) date("Y");
	$carburants = $carburants ?? PM_TREND_FUELS;
	$cleCarburants = md5(implode("|", $carburants));
	$fichierCacheResultats = PM_CACHE_DIR . "/fuel_trends_" . $annee . "_" . $cleCarburants . ".json";
	$dureeCache = PM_FUEL_TRENDS_CACHE_DURATION;

	$cache = lire_cache_api($fichierCacheResultats);
	if ($cache !== null && time() - (int) $cache["time"] < $dureeCache) {
		$donnees = json_decode((string) $cache["body"], true);
		if (is_array($donnees)) {
			$donnees["source_url"] = $donnees["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
			$donnees["cached_at"] = date("c", (int) $cache["time"]);
			$donnees["reference_year"] = $donnees["reference_year"] ?? max(0, $annee - 1);
			$donnees["reference_averages"] = is_array($donnees["reference_averages"] ?? null) ? $donnees["reference_averages"] : [];
			return $donnees;
		}
	}

	$resultatCourant = calculer_tendances_prix_annuelles($annee, $carburants);
	if (($resultatCourant["fuels"] ?? []) === []) {
		return [
			"source" => $resultatCourant["source"] ?? "archive officielle indisponible",
			"source_url" => $resultatCourant["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
			"year" => $annee,
			"fuels" => [],
			"reference_year" => max(0, $annee - 1),
			"reference_averages" => [],
		];
	}

	$anneeReference = max(0, $annee - 1);
	$resultatReference = $anneeReference > 0
		? calculer_tendances_prix_annuelles($anneeReference, $carburants)
		: ["annual_averages" => []];

	$resultat = [
		"source" => $resultatCourant["source"],
		"source_url" => $resultatCourant["source_url"],
		"year" => $annee,
		"cached_at" => date("c"),
		"fuels" => $resultatCourant["fuels"],
		"reference_year" => $anneeReference,
		"reference_averages" => $resultatReference["annual_averages"] ?? [],
	];

	file_put_contents($fichierCacheResultats, json_encode([
		"time" => time(),
		"body" => json_encode($resultat),
	], JSON_PRETTY_PRINT));

	return $resultat;
}

/**
 * Formate un mois technique YYYY-MM en libelle lisible.
 *
 * @param string $mois Mois au format YYYY-MM.
 * @return string Libelle de mois court en francais.
 *
 * @ingroup statistiques
 */
function formater_mois_tendance(string $mois): string
{
	$moisNoms = [
		"01" => "Janvier",
		"02" => "Février",
		"03" => "Mars",
		"04" => "Avril",
		"05" => "Mai",
		"06" => "Juin",
		"07" => "Juillet",
		"08" => "Août",
		"09" => "Septembre",
		"10" => "Octobre",
		"11" => "Novembre",
		"12" => "Décembre",
	];

	$annee = substr($mois, 0, 4);
	$numeroMois = substr($mois, 5, 2);

	if (!isset($moisNoms[$numeroMois])) {
		return $mois;
	}

	return $moisNoms[$numeroMois] . " " . $annee;
}
/**
 * Transforme une serie mensuelle en points SVG pour une courbe.
 *
 * @param array $moisDonnees Donnees mensuelles avec average_price.
 * @param int $largeur Largeur du SVG.
 * @param int $hauteur Hauteur du SVG.
 * @return string Points de polyline SVG.
 *
 * @ingroup statistiques
 */
function points_graphique_tendance(array $moisDonnees, int $largeur = 420, int $hauteur = 170): string
{
	if (count($moisDonnees) < 2) {
		return "";
	}

	$prix = array_map(static function (array $moisDonnee): float {
		return (float) $moisDonnee["average_price"];
	}, $moisDonnees);

	$min = min($prix);
	$max = max($prix);
	$marge = 16;
	$amplitude = $max - $min;

	if ($amplitude <= 0) {
		$amplitude = 1;
	}

	$points = [];
	$dernierIndex = count($moisDonnees) - 1;

	foreach ($prix as $indice => $valeur) {
		$x = $marge + ($indice / $dernierIndex) * ($largeur - 2 * $marge);
		$y = $hauteur - $marge - (($valeur - $min) / $amplitude) * ($hauteur - 2 * $marge);
		$points[] = round($x, 1) . "," . round($y, 1);
	}

	return implode(" ", $points);
}
/**
 * Calcule les lignes horizontales et libelles de prix d'un graphique SVG.
 *
 * @param array $moisDonnees Donnees mensuelles.
 * @param int $largeur Largeur du SVG.
 * @param int $hauteur Hauteur du SVG.
 * @param int $nombre Nombre d'intervalles.
 * @return array<int,array<string,float|int>> Graduations de prix.
 *
 * @ingroup statistiques
 */
function graduations_prix_tendance(array $moisDonnees, int $largeur = 420, int $hauteur = 170, int $nombre = 4): array
{
	if ($moisDonnees === []) {
		return [];
	}

	$prix = array_map(static function (array $moisDonnee): float {
		return (float) $moisDonnee["average_price"];
	}, $moisDonnees);

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
/**
 * Calcule les positions des libelles de mois d'un graphique SVG.
 *
 * @param array $moisDonnees Donnees mensuelles.
 * @param int $largeur Largeur du SVG.
 * @return array<int,array<string,float|string>> Graduations de mois.
 *
 * @ingroup statistiques
 */
function graduations_mois_tendance(array $moisDonnees, int $largeur = 420): array
{
	if ($moisDonnees === []) {
		return [];
	}

	$marge = 16;
	$dernierIndex = count($moisDonnees) - 1;
	$graduations = [];

	foreach ($moisDonnees as $indice => $moisDonnee) {
		$x = $dernierIndex === 0 ? $largeur / 2 : $marge + ($indice / $dernierIndex) * ($largeur - 2 * $marge);
		$graduations[] = [
			"label" => substr((string) $moisDonnee["month"], 5, 2),
			"x" => round($x, 1),
		];
	}

	return $graduations;
}
/**
 * Ajoute une recherche terminee dans le fichier CSV de consultations.
 *
 * @param array $infos Informations de recherche et nombre de stations trouvees.
 * @return void
 *
 * @ingroup statistiques
 */
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

	$poignee = fopen($fichier, "a");
	if ($poignee !== false) {
		fputcsv($poignee, $ligne, ",", "\"", "\\");
		fclose($poignee);
	}
}
/**
 * Enregistre une visite de page pour les statistiques globales.
 *
 * @return void
 *
 * @ingroup statistiques
 */
function enregistrer_visite_page(): void
{
	$fichier = PM_STORAGE_DIR . "/page_visits.csv";
	$page = basename((string) ($_SERVER["PHP_SELF"] ?? ""));

	if ($page === "") {
		$page = "inconnue";
	}

	$ligne = [
		date("c"),
		sha1(recuperer_ip_visiteur()),
		$page,
	];

	$poignee = fopen($fichier, "a");
	if ($poignee !== false) {
		fputcsv($poignee, $ligne, ",", "\"", "\\");
		fclose($poignee);
	}
}
/**
 * Calcule les statistiques affichees a partir des fichiers CSV locaux.
 *
 * @return array Tops des villes, departements, regions, carburants, modes et compteurs.
 *
 * @ingroup statistiques
 */
function calculer_statistiques(): array
{
	$lignes = lire_csv_assoc(PM_STORAGE_DIR . "/consultations.csv");
	$visites = lire_csv_assoc(PM_STORAGE_DIR . "/page_visits.csv");
	$classementVilles = [];
	$classementDepartements = [];
	$classementRegions = [];
	$classementCarburants = [];
	$classementModes = [];
	$visiteurs = [];
	$visiteursPages = [];

	foreach ($lignes as $ligne) {
		$mode = trim($ligne["mode"] ?? "");
		if ($mode !== "") {
			if (!isset($classementModes[$mode])) {
				$classementModes[$mode] = 0;
			}
			$classementModes[$mode]++;
		}

		$ville = trim($ligne["city"] ?? "");
		if ($ville !== "" && $mode !== "departement") {
			if (!isset($classementVilles[$ville])) {
				$classementVilles[$ville] = 0;
			}
			$classementVilles[$ville]++;
		}

		$departement = trim($ligne["department"] ?? "");
		if ($departement !== "") {
			if (!isset($classementDepartements[$departement])) {
				$classementDepartements[$departement] = 0;
			}
			$classementDepartements[$departement]++;
		}

		$region = trim($ligne["region"] ?? "");
		if ($region !== "") {
			if (!isset($classementRegions[$region])) {
				$classementRegions[$region] = 0;
			}
			$classementRegions[$region]++;
		}

		foreach (explode(",", (string) ($ligne["fuel"] ?? "")) as $carburant) {
			$carburant = trim($carburant);
			if ($carburant === "") {
				continue;
			}

			if (!isset($classementCarburants[$carburant])) {
				$classementCarburants[$carburant] = 0;
			}
			$classementCarburants[$carburant]++;
		}

		$empreinteVisiteur = trim($ligne["visitor_hash"] ?? "");
		if ($empreinteVisiteur !== "") {
			$visiteurs[$empreinteVisiteur] = true;
		}
	}

	foreach ($visites as $visite) {
		$empreinteVisiteur = trim($visite["visitor_hash"] ?? "");
		if ($empreinteVisiteur !== "") {
			$visiteursPages[$empreinteVisiteur] = true;
		}
	}

	arsort($classementVilles);
	arsort($classementDepartements);
	arsort($classementRegions);
	arsort($classementCarburants);
	arsort($classementModes);

	return [
		"top_cities" => array_slice($classementVilles, 0, 8, true),
		"top_departments" => array_slice($classementDepartements, 0, 8, true),
		"top_regions" => array_slice($classementRegions, 0, 8, true),
		"top_fuels" => array_slice($classementCarburants, 0, 8, true),
		"top_modes" => $classementModes,
		"total_visitors" => count($visiteurs),
		"page_visit_count" => count($visites),
		"page_visitor_count" => count($visiteursPages),
		"consultation_count" => count($lignes),
	];
}
