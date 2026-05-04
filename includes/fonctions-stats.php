<?php
declare(strict_types=1);

/**
 * @file
 * @brief Statistiques, suivi des consultations et graphiques.
 */

/**
 * Retourne une reponse vide pour les tendances de prix.
 *
 * @param string $source Message expliquant pourquoi les donnees sont absentes.
 * @param string $sourceUrl Adresse de la source officielle.
 * @param int $annee Annee demandee.
 * @return array Reponse vide standardisee.
 */
function trier_tableau_par_cle(array $valeurs): array
{
	$clesTriees = [];

	foreach ($valeurs as $cle => $_valeur) {
		$position = count($clesTriees);

		while ($position > 0 && strcmp((string) $clesTriees[$position - 1], (string) $cle) > 0) {
			$clesTriees[$position] = $clesTriees[$position - 1];
			$position--;
		}

		$clesTriees[$position] = $cle;
	}

	$resultat = [];
	foreach ($clesTriees as $cle) {
		$resultat[$cle] = $valeurs[$cle];
	}

	return $resultat;
}

/**
 * Trie un classement associatif par valeur decroissante puis par cle.
 *
 * @param array<string,int> $valeurs Classement a trier.
 * @return array<string,int> Classement trie.
 *
 * @ingroup statistiques
 */
function trier_classement_decroissant(array $valeurs): array
{
	$entreesTriees = [];

	foreach ($valeurs as $cle => $valeur) {
		$entree = [
			"key" => (string) $cle,
			"value" => (int) $valeur,
		];
		$position = count($entreesTriees);

		while ($position > 0) {
			$precedente = $entreesTriees[$position - 1];
			$doitMonter = $entree["value"] > $precedente["value"];
			$egalite = $entree["value"] === $precedente["value"];

			if (!$doitMonter && !($egalite && strcmp($entree["key"], $precedente["key"]) < 0)) {
				break;
			}

			$entreesTriees[$position] = $precedente;
			$position--;
		}

		$entreesTriees[$position] = $entree;
	}

	$resultat = [];
	foreach ($entreesTriees as $entreeTriee) {
		$resultat[$entreeTriee["key"]] = $entreeTriee["value"];
	}

	return $resultat;
}

/**
 * Prepare l'archive officielle en cache.
 *
 * @param string $fichierZip Chemin local du fichier ZIP.
 * @param string $adresseUrl Adresse officielle a telecharger.
 * @return bool True si l'archive est disponible localement.
 */
function preparer_archive_prix_annuelle(string $fichierZip, string $adresseUrl): bool
{
	$dureeCache = PM_FUEL_TRENDS_CACHE_DURATION;
	$archiveExiste = file_exists($fichierZip);
	$archiveEncoreValide = false;

	if ($archiveExiste) {
		$ageArchive = time() - filemtime($fichierZip);
		$archiveEncoreValide = $ageArchive <= $dureeCache;
	}

	if ($archiveExiste && $archiveEncoreValide) {
		return true;
	}

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
		return false;
	}

	file_put_contents($fichierZip, $contenu);
	return true;
}
/**
 * Trouve le premier fichier XML dans une archive ZIP.
 *
 * @param string $fichierZip Chemin du fichier ZIP.
 * @return string Nom du fichier XML dans l'archive, ou chaine vide.
 */
function trouver_xml_dans_archive(string $fichierZip): string
{
	$zip = new ZipArchive();

	if ($zip->open($fichierZip) !== true) {
		return "";
	}

	$nomXml = "";

	for ($i = 0; $i < $zip->numFiles; $i++) {
		$nom = (string) $zip->getNameIndex($i);
		$extension = strtolower(pathinfo($nom, PATHINFO_EXTENSION));

		if ($extension === "xml") {
			$nomXml = $nom;
			break;
		}
	}

	$zip->close();
	return $nomXml;
}
/**
 * Prepare le tableau d'agregats pour les carburants demandes.
 *
 * @param string[] $carburants Carburants a agreger.
 * @return array Agregats vides par carburant.
 */
function initialiser_agregats_carburants(array $carburants): array
{
	$agregats = [];

	foreach ($carburants as $carburant) {
		$agregats[$carburant] = [];
	}

	return $agregats;
}
/**
 * Ajoute un prix dans les agregats mensuels.
 *
 * @param array $agregats Agregats en cours.
 * @param string $nomCarburant Carburant concerne.
 * @param string $mois Mois au format YYYY-MM.
 * @param float $valeur Prix a ajouter.
 * @return array Agregats mis a jour.
 */
function ajouter_prix_agregat_mensuel(array $agregats, string $nomCarburant, string $mois, float $valeur): array
{
	if (!isset($agregats[$nomCarburant][$mois])) {
		$agregats[$nomCarburant][$mois] = [
			"sum" => 0.0,
			"count" => 0,
		];
	}

	$agregats[$nomCarburant][$mois]["sum"] += $valeur;
	$agregats[$nomCarburant][$mois]["count"]++;

	return $agregats;
}
/**
 * Lit le XML de l'archive et remplit les agregats.
 *
 * @param string $fichierZip Chemin du fichier ZIP.
 * @param string $nomXml Nom du XML dans l'archive.
 * @param array $agregats Agregats a remplir.
 * @return array|null Agregats remplis, ou null si le XML ne s'ouvre pas.
 */
function remplir_agregats_depuis_xml(string $fichierZip, string $nomXml, array $agregats): ?array
{
	$lecteur = new XMLReader();
	$cheminZip = "zip://" . $fichierZip . "#" . $nomXml;

	if (!$lecteur->open($cheminZip)) {
		return null;
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

		$agregats = ajouter_prix_agregat_mensuel($agregats, $nomCarburant, $mois, $valeur);
	}

	$lecteur->close();
	return $agregats;
}
/**
 * Trie manuellement des mois au format YYYY-MM.
 *
 * @param array $moisAgreges Agregats indexes par mois.
 * @return string[] Mois tries.
 */
function trier_mois_agreges(array $moisAgreges): array
{
	$moisTries = [];

	foreach ($moisAgreges as $mois => $agregat) {
		$moisTries[] = $mois;
	}

	$nombreMois = count($moisTries);

	for ($i = 0; $i < $nombreMois; $i++) {
		for ($j = $i + 1; $j < $nombreMois; $j++) {
			if ($moisTries[$i] > $moisTries[$j]) {
				$moisTemporaire = $moisTries[$i];
				$moisTries[$i] = $moisTries[$j];
				$moisTries[$j] = $moisTemporaire;
			}
		}
	}

	return $moisTries;
}
/**
 * Calcule les tendances mensuelles et moyennes annuelles depuis les agregats.
 *
 * @param array $agregats Agregats par carburant et par mois.
 * @return array Tendances et moyennes annuelles.
 */
function calculer_tendances_depuis_agregats(array $agregats): array
{
	$tendances = [];
	$moyennesAnnuelles = [];

	foreach ($agregats as $carburant => $moisAgreges) {
		$moisAgreges = trier_tableau_par_cle($moisAgreges);
		$tendances[$carburant] = [];
		$sommeAnnuelle = 0.0;
		$nombreAnnuel = 0;
		$moisTries = trier_mois_agreges($moisAgreges);

		foreach ($moisTries as $mois) {
			$agregat = $moisAgreges[$mois];

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
		"tendances" => $tendances,
		"moyennes_annuelles" => $moyennesAnnuelles,
	];
}
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
	$sourceUrl = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
	$fichierZip = PM_CACHE_DIR . "/fuel_history_" . $annee . ".zip";

	if (!preparer_archive_prix_annuelle($fichierZip, $sourceUrl)) {
		return reponse_tendances_vide("archive officielle indisponible", $sourceUrl, $annee);
	}

	$nomXml = trouver_xml_dans_archive($fichierZip);

	if ($nomXml === "") {
		return reponse_tendances_vide("archive officielle sans XML", $sourceUrl, $annee);
	}

	$agregats = initialiser_agregats_carburants($carburants);
	$agregats = remplir_agregats_depuis_xml($fichierZip, $nomXml, $agregats);

	if ($agregats === null) {
		return reponse_tendances_vide("archive officielle non ouverte", $sourceUrl, $annee);
	}

	$resultats = calculer_tendances_depuis_agregats($agregats);

	return [
		"source" => "archive annuelle officielle XML",
		"source_url" => $sourceUrl,
		"year" => $annee,
		"fuels" => $resultats["tendances"],
		"annual_averages" => $resultats["moyennes_annuelles"],
	];
}
/**
 * Choisit l'annee a utiliser pour les tendances.
 *
 * @param int|null $annee Annee demandee.
 * @return int Annee retenue.
 */
function choisir_annee_tendances(?int $annee): int
{
	if ($annee === null) {
		return (int) date("Y");
	}

	return $annee;
}
/**
 * Choisit les carburants a utiliser pour les tendances.
 *
 * @param array|null $carburants Carburants demandes.
 * @return array Carburants retenus.
 */
function choisir_carburants_tendances(?array $carburants): array
{
	if ($carburants === null) {
		return PM_TREND_FUELS;
	}

	return $carburants;
}
/**
 * Construit une cle lisible pour le cache des carburants.
 *
 * @param string[] $carburants Carburants demandes.
 * @return string Cle de cache.
 */
function cle_cache_carburants_tendances(array $carburants): string
{
	$cle = "";

	foreach ($carburants as $carburant) {
		if ($cle !== "") {
			$cle .= "_";
		}

		$cle .= $carburant;
	}

	if ($cle === "") {
		return "aucun";
	}

	return $cle;
}
/**
 * Complete les donnees lues depuis le cache de tendances.
 *
 * @param array $donnees Donnees decodees.
 * @param array $cache Cache brut contenant time/body.
 * @param int $annee Annee demandee.
 * @return array Donnees completees.
 */
function completer_donnees_cache_tendances(array $donnees, array $cache, int $annee): array
{
	if (!isset($donnees["source_url"])) {
		$donnees["source_url"] = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
	}

	$donnees["cached_at"] = date("c", (int) $cache["time"]);

	if (!isset($donnees["reference_year"])) {
		$donnees["reference_year"] = max(0, $annee - 1);
	}

	if (!isset($donnees["reference_averages"]) || !is_array($donnees["reference_averages"])) {
		$donnees["reference_averages"] = [];
	}

	return $donnees;
}
/**
 * Lit le cache des tendances s'il est encore valide.
 *
 * @param string $fichierCacheResultats Chemin du cache.
 * @param int $annee Annee demandee.
 * @return array|null Donnees de cache valides, ou null.
 */
function lire_cache_tendances_prix(string $fichierCacheResultats, int $annee): ?array
{
	$dureeCache = PM_FUEL_TRENDS_CACHE_DURATION;
	$cache = lire_cache_api($fichierCacheResultats);

	if ($cache === null) {
		return null;
	}

	$ageCache = time() - (int) $cache["time"];

	if ($ageCache >= $dureeCache) {
		return null;
	}

	$donnees = json_decode($cache["body"], true);

	if (!is_array($donnees)) {
		return null;
	}

	return completer_donnees_cache_tendances($donnees, $cache, $annee);
}
/**
 * Ecrit les resultats de tendances dans le cache.
 *
 * @param string $fichierCacheResultats Chemin du cache.
 * @param array $resultat Donnees a stocker.
 * @return void
 */
function ecrire_cache_tendances_prix(string $fichierCacheResultats, array $resultat): void
{
	$contenuCache = json_encode([
		"time" => time(),
		"body" => json_encode($resultat),
	], JSON_PRETTY_PRINT);

	if ($contenuCache === false) {
		return;
	}

	$fichierOuvert = fopen($fichierCacheResultats, "w");

	if ($fichierOuvert === false) {
		return;
	}

	fwrite($fichierOuvert, $contenuCache);
	fclose($fichierOuvert);
}
/**
 * Retourne une reponse de tendances indisponibles.
 *
 * @param array $resultatCourant Resultat annuel obtenu.
 * @param int $annee Annee demandee.
 * @return array Reponse vide pour l'interface.
 */
function reponse_tendances_indisponibles(array $resultatCourant, int $annee): array
{
	if (isset($resultatCourant["source"])) {
		$source = $resultatCourant["source"];
	} else {
		$source = "archive officielle indisponible";
	}

	if (isset($resultatCourant["source_url"])) {
		$sourceUrl = $resultatCourant["source_url"];
	} else {
		$sourceUrl = "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
	}

	return [
		"source" => $source,
		"source_url" => $sourceUrl,
		"year" => $annee,
		"fuels" => [],
		"reference_year" => max(0, $annee - 1),
		"reference_averages" => [],
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
	$cleCarburants = construire_cle_cache("fuel_trends", implode("|", $carburants));
	$fichierCacheResultats = PM_CACHE_DIR . "/fuel_trends_" . $annee . "_" . $cleCarburants . ".json";

	$donneesCache = lire_cache_tendances_prix($fichierCacheResultats, $annee);

	if ($donneesCache !== null) {
		return $donneesCache;
	}

	$resultatCourant = calculer_tendances_prix_annuelles($annee, $carburants);

	if (!isset($resultatCourant["fuels"]) || $resultatCourant["fuels"] === []) {
		return reponse_tendances_indisponibles($resultatCourant, $annee);
	}

	$anneeReference = max(0, $annee - 1);

	if ($anneeReference > 0) {
		$resultatReference = calculer_tendances_prix_annuelles($anneeReference, $carburants);
	} else {
		$resultatReference = ["annual_averages" => []];
	}

	if (isset($resultatReference["annual_averages"])) {
		$moyennesReference = $resultatReference["annual_averages"];
	} else {
		$moyennesReference = [];
	}

	$resultat = [
		"source" => $resultatCourant["source"],
		"source_url" => $resultatCourant["source_url"],
		"year" => $annee,
		"cached_at" => date("c"),
		"fuels" => $resultatCourant["fuels"],
		"reference_year" => $anneeReference,
		"reference_averages" => $moyennesReference,
	];

	ecrire_cache_tendances_prix($fichierCacheResultats, $resultat);

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
 * Cree une empreinte simple a partir d'un texte.
 *
 * @param string $texte Texte a transformer.
 * @return string Empreinte non reversible pour les statistiques.
 */
function empreinte_statistique(string $texte): string
{
	$somme = 0;
	$longueurTexte = strlen($texte);

	for ($position = 0; $position < $longueurTexte; $position++) {
		$somme += ord($texte[$position]) * ($position + 1);
	}

	return "v" . $longueurTexte . "_" . $somme;
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
	$visiteur = empreinte_statistique($ip);

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

	if (isset($_SERVER["PHP_SELF"])) {
		$page = basename($_SERVER["PHP_SELF"]);
	} else {
		$page = "";
	}

	if ($page === "") {
		$page = "inconnue";
	}

	$ip = recuperer_ip_visiteur();
	$visiteur = empreinte_statistique($ip);
	$date = date("c");

	$ligne = [
		$date,
		$visiteur,
		$page,
	];

	$poignee = fopen($fichier, "a");

	if ($poignee === false) {
		return;
	}

	fputcsv($poignee, $ligne, ",", "\"", "\\");
	fclose($poignee);
}
/**
 * Ajoute une occurrence dans un classement.
 *
 * @param array $classement Classement a mettre a jour.
 * @param string $cle Valeur a compter.
 * @return array Classement mis a jour.
 */
function ajouter_compteur_statistique(array $classement, string $cle): array
{
	if ($cle === "") {
		return $classement;
	}

	if (!isset($classement[$cle])) {
		$classement[$cle] = 0;
	}

	$classement[$cle]++;
	return $classement;
}
/**
 * Trie un classement du plus grand compteur au plus petit.
 *
 * @param array $classement Classement a trier.
 * @return array Classement trie.
 */
function trier_classement_statistique(array $classement): array
{
	$cles = [];

	foreach ($classement as $cle => $nombre) {
		$cles[] = $cle;
	}

	$nombreCles = count($cles);

	for ($i = 0; $i < $nombreCles; $i++) {
		for ($j = $i + 1; $j < $nombreCles; $j++) {
			if ($classement[$cles[$j]] > $classement[$cles[$i]]) {
				$cleTemporaire = $cles[$i];
				$cles[$i] = $cles[$j];
				$cles[$j] = $cleTemporaire;
			}
		}
	}

	$classementTrie = [];

	foreach ($cles as $cle) {
		$classementTrie[$cle] = $classement[$cle];
	}

	return $classementTrie;
}
/**
 * Garde les premieres entrees d'un classement.
 *
 * @param array $classement Classement deja trie.
 * @param int $limite Nombre maximum d'entrees.
 * @return array Classement limite.
 */
function limiter_classement_statistique(array $classement, int $limite): array
{
	$resultat = [];
	$compteur = 0;

	foreach ($classement as $cle => $nombre) {
		if ($compteur >= $limite) {
			break;
		}

		$resultat[$cle] = $nombre;
		$compteur++;
	}

	return $resultat;
}
/**
 * Trie puis limite un classement statistique.
 *
 * @param array $classement Classement complet.
 * @param int $limite Nombre maximum d'entrees.
 * @return array Top du classement.
 */
function top_classement_statistique(array $classement, int $limite): array
{
	$classementTrie = trier_classement_statistique($classement);
	return limiter_classement_statistique($classementTrie, $limite);
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
		$classementModes = ajouter_compteur_statistique($classementModes, $mode);

		$ville = trim($ligne["city"] ?? "");
		if ($ville !== "" && $mode !== "departement") {
			$classementVilles = ajouter_compteur_statistique($classementVilles, $ville);
		}

		$departement = trim($ligne["department"] ?? "");
		$classementDepartements = ajouter_compteur_statistique($classementDepartements, $departement);

		$region = trim($ligne["region"] ?? "");
		$classementRegions = ajouter_compteur_statistique($classementRegions, $region);

		if (isset($ligne["fuel"])) {
			$texteCarburants = $ligne["fuel"];
		} else {
			$texteCarburants = "";
		}

		$carburants = explode(",", $texteCarburants);

		foreach ($carburants as $carburant) {
			$carburant = trim($carburant);
			$classementCarburants = ajouter_compteur_statistique($classementCarburants, $carburant);
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

	$classementVilles = trier_classement_decroissant($classementVilles);
	$classementDepartements = trier_classement_decroissant($classementDepartements);
	$classementRegions = trier_classement_decroissant($classementRegions);
	$classementCarburants = trier_classement_decroissant($classementCarburants);
	$classementModes = trier_classement_decroissant($classementModes);

	return [
		"top_cities" => top_classement_statistique($classementVilles, 8),
		"top_departments" => top_classement_statistique($classementDepartements, 8),
		"top_regions" => top_classement_statistique($classementRegions, 8),
		"top_fuels" => top_classement_statistique($classementCarburants, 8),
		"top_modes" => $classementModes,
		"total_visitors" => count($visiteurs),
		"page_visit_count" => count($visites),
		"page_visitor_count" => count($visiteursPages),
		"consultation_count" => count($lignes),
	];
}
