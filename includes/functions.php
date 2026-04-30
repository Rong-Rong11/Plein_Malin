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

	$fichierVisites = PM_STORAGE_DIR . "/page_visits.csv";
	if (!file_exists($fichierVisites)) {
		file_put_contents($fichierVisites, "timestamp,visitor_hash,page\n");
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

function gerer_langue(): string
{
	$langue = "fr";
	$languesValides = ["fr", "en"];

	if (isset($_GET["lang"])) {
		if (in_array($_GET["lang"], $languesValides, true)) {
			$langue = $_GET["lang"];
			setcookie("lang", $langue, time() + 30 * 24 * 3600, chemin_cookie());
			$_COOKIE["lang"] = $langue;
			return $langue;
		}

		setcookie("lang", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["lang"]);
		return "fr";
	}

	if (isset($_COOKIE["lang"])) {
		if (in_array($_COOKIE["lang"], $languesValides, true)) {
			return $_COOKIE["lang"];
		}

		setcookie("lang", "", time() - 3600, chemin_cookie());
		unset($_COOKIE["lang"]);
	}

	return $langue;
}

function lien_bascule_langue(string $langue): string
{
	$parametres = $_GET;
	$parametres["lang"] = $langue === "en" ? "fr" : "en";
	$requete = http_build_query($parametres);
	$script = basename($_SERVER["PHP_SELF"] ?? "index.php");

	if ($requete === "") {
		return $script;
	}

	return $script . "?" . $requete;
}

function libelle_bascule_langue(string $langue): string
{
	return $langue === "en" ? "Français" : "English";
}

function traductions_communes(): array
{
	return [
		"Mode jour" => "Day mode",
		"Mode nuit" => "Night mode",
		"Recherche" => "Search",
		"Résultats" => "Results",
		"Resultats" => "Results",
		"Statistiques" => "Statistics",
		"Retour en haut" => "Back to top",
		"Accueil" => "Home",
		"À propos" => "About",
		"Confidentialité" => "Privacy",
		"Aide" => "Help",
		"Sources des données" => "Data sources",
		"Page tech" => "Tech page",
		"Page technique" => "Technical page",
	];
}

function traductions_accueil(): array
{
	return [
		"Prix des carburants" => "Fuel prices",
		"Trouvez une station plus facilement" => "Find a station more easily",
		"Plein Malin permet de choisir une région, un département puis une ville" => "Plein Malin lets you choose a region, a department, then a city",
		"pour consulter les stations-service et comparer les prix." => "to view service stations and compare prices.",
		"Rechercher une station" => "Search for a station",
		"Ce que fait le site" => "What the site does",
		"Recherche par région, département et ville" => "Search by region, department and city",
		"Affichage simple des stations et des prix" => "Simple display of stations and prices",
		"Statistiques à partir des consultations enregistrées" => "Statistics based on recorded searches",
		"Statistiques a partir des consultations enregistrees" => "Statistics based on recorded searches",
		"Dernière recherche" => "Last search",
		"Dernière recherche le" => "Last search on",
		"Reprendre cette recherche" => "Resume this search",
		"Recherche guidée" => "Guided search",
		"Recherche guidee" => "Guided search",
		"La recherche suit l'ordre région, département puis ville pour rester simple." => "The search follows region, department, then city to stay simple.",
		"Résultats lisibles" => "Readable results",
		"Chaque station affiche son adresse, ses prix et ses informations utiles." => "Each station shows its address, prices and useful information.",
		"Une page dédiée résume les villes les plus consultées et le nombre de visites." => "A dedicated page summarizes the most viewed cities and visit counts.",
	];
}

function traductions_recherche(): array
{
	return [
		"Choisir une région, un département et une ville." => "Choose a region, a department and a city.",
		"Recherche manuelle" => "Manual search",
		"Utilisez votre position approximative pour lancer une recherche rapide," => "Use your approximate position to launch a quick search,",
		"sans passer par la carte ni le choix manuel de ville." => "without using the map or manual city selection.",
		"Recherche principale" => "Main search",
		"Choisissez une région sur la carte, puis un département, puis une ville." => "Choose a region on the map, then a department, then a city.",
		"Carte des régions" => "Regions map",
		"Cliquez sur une région pour commencer." => "Click a region to start.",
		"Région choisie" => "Selected region",
		"Formulaire" => "Form",
		"Contexte" => "Context",
		"Région déjà choisie" => "Region already selected",
		"Changer sur la carte" => "Change on the map",
		"Aucune région sélectionnée" => "No region selected",
		"Commencez par cliquer sur la carte au-dessus." => "Start by clicking the map above.",
		"Localisation precise" => "Precise location",
		"Département" => "Department",
		"Choisissez d'abord le département de la région." => "First choose the department in the region.",
		"Choisir d'abord une région" => "Choose a region first",
		"Choisir un département" => "Choose a department",
		"Choisissez un département." => "Choose a department.",
		"Ville" => "City",
		"La liste dépend du département choisi." => "The list depends on the selected department.",
		"Choisir d'abord un département" => "Choose a department first",
		"Choisir une ville" => "Choose a city",
		"Choisissez une ville." => "Choose a city.",
		"Préférences et actions" => "Preferences and actions",
		"Carburants" => "Fuels",
		"Cochez un ou plusieurs carburants." => "Select one or more fuels.",
		"Vue" => "View",
		"Synthèse" => "Summary",
		"Détaillée" => "Detailed",
		"Trier" => "Sort",
		"Tri" => "Sort",
		"Prix croissant" => "Lowest price",
		"Prix décroissant" => "Highest price",
		"Prix decroissant" => "Highest price",
		"Distance" => "Distance",
		"Nom</option>" => "Name</option>",
		"Mode département" => "Department mode",
		"Afficher les stations du département choisi." => "Show stations in the selected department.",
		"Tout le département" => "Whole department",
		"Lancer la recherche" => "Launch search",
		"Rechercher" => "Search",
		"Choisissez votre mode puis affichez les stations." => "Choose your mode, then show stations.",
		"Rayon" => "Radius",
		"Autour de moi" => "Near me",
		"Recherche autour de moi" => "Near me search",
		"Position approximative par IP." => "Approximate position by IP.",
		"Réinitialiser" => "Reset",
		"Si aucun carburant n'est sélectionné, le Gazole est utilisé par défaut." => "If no fuel is selected, Gazole is used by default.",
		"La position utilisée reste approximative car elle vient de l'adresse IP." => "The position used remains approximate because it comes from the IP address.",
		"Rechercher autour de moi" => "Search near me",
		"Choisissez votre région, puis précisez le département et la ville dans le formulaire." => "Choose your region, then specify the department and city in the form.",
		"Choisissez une autre région directement sur la carte ci-dessous." => "Choose another region directly on the map below.",
		"Commencez par cliquer sur la carte ci-dessous." => "Start by clicking the map below.",
		"Rechercher dans tout le département." => "Search in the whole department.",
		"Affichez les stations selon vos critères." => "Display the stations according to your criteria.",
	];
}

function traductions_resultats(): array
{
	return [
		"Résultats des stations-service et des prix." => "Service station and price results.",
		"Resultats des stations-service et des prix." => "Service station and price results.",
		"Stations pour" => "Stations for",
		"Consultez les stations trouvées puis revenez à la recherche si besoin." => "View the stations found, then return to search if needed.",
		"Modifier ma recherche" => "Edit my search",
		"Détail" => "Details",
		"Recherche actuelle" => "Current search",
		"Mode" => "Mode",
		"Carburants choisis" => "Selected fuels",
		"Tri choisi" => "Selected sort",
		"Vue choisie" => "Selected view",
		"Stations trouvées" => "Stations found",
		"Périmètre" => "Scope",
		"Région" => "Region",
		"Ville de référence" => "Reference city",
		"Code ville" => "City code",
		"Code postal" => "Postal code",
		"Rayon géolocalisé" => "Geolocated radius",
		"Ville retournée par l'IP" => "City returned by IP",
		"Région retournée par l'IP" => "Region returned by IP",
		"Source de localisation" => "Location source",
		"Position estimée à partir de l'adresse IP." => "Position estimated from the IP address.",
		"Aucune recherche lancée." => "No search launched.",
		"Aucune recherche lancee." => "No search launched.",
		"Recherche autour de votre position approximative." => "Search around your approximate position.",
		"Recherche dans tout le département sélectionné." => "Search in the whole selected department.",
		"Recherche dans tout le departement selectionne." => "Search in the whole selected department.",
		"Recherche dans la ville sélectionnée." => "Search in the selected city.",
		"Recherche dans la ville selectionnee." => "Search in the selected city.",
		"Aucune station trouvée dans la ville sélectionnée." => "No station found in the selected city.",
		"Aucune station trouvee dans la ville selectionnee." => "No station found in the selected city.",
		"Aucune station trouvée avec ces critères." => "No station found with these criteria.",
		"station(s) trouvée(s)." => "station(s) found.",
		"prix moyen trouvé" => "average price found",
		"meilleur prix trouvé" => "best price found",
		"Cliquer pour voir la station" => "Click to view the station",
		"Distance :" => "Distance:",
		"flux" => "feed",
		"prix mis à jour le" => "price updated on",
		"Voir les détails" => "View details",
		"Services" => "Services",
		"Aucun service indique." => "No service indicated.",
		"Retour à la recherche" => "Back to search",
		"Voir sur une carte" => "View on a map",
		"Recherche manuelle" => "Manual search",
		"Gazole par défaut si rien n'est coché." => "Gazole by default if nothing is checked.",
		"Prix décroissant" => "Highest price",
		"Prix decroissant" => "Highest price",
		"Appliquer" => "Apply",
		"Choisissez une ville dans le département " => "Choose a city in the department ",
		" ou activez la recherche dans tout le département." => " or enable search in the whole department.",
		"Les prix affichés dépendent de la dernière mise à jour disponible dans l'API officielle." => "Displayed prices depend on the latest update available from the official API.",
		"La géolocalisation par adresse IP est approximative et ne donne pas une position exacte." => "IP geolocation is approximate and does not provide an exact position.",
		"Certaines stations ne proposent pas tous les carburants ou ne fournissent pas toujours un prix pour chaque carburant." => "Some stations do not offer all fuels or do not always provide a price for each fuel.",
		"Les prix dépendent de la dernière mise à jour transmise par l'API officielle." => "Prices depend on the latest update sent by the official API.",
		"Certaines stations peuvent ne pas proposer tous les carburants sélectionnés." => "Some stations may not offer all selected fuels.",
		"Pour limiter le poids de la page, seules les" => "To limit page weight, only the",
		"premières stations sont affichées." => "first stations are displayed.",
		"Elles correspondent aux résultats" => "They match the results",
		"les moins chers" => "with the lowest prices",
		"les plus pertinents selon le tri choisi" => "that are most relevant according to the selected sort",
		"prix moyen trouvé sur les stations affichées" => "average price found on displayed stations",
		"meilleur prix affiché" => "best displayed price",
	];
}

function traductions_statistiques(): array
{
	return [
		"Rubrique statistiques" => "Statistics section",
		"Consultations Plein Malin" => "Plein Malin consultations",
		"Les recherches correspondent aux consultations avec critères. Les visites comptent aussi" => "Searches correspond to queries with criteria. Visits also count",
		"les pages vues sans lancement de recherche." => "page views without launching a search.",
		"Une recherche est enregistrée quand une page de résultats est produite ; une visite de page" => "A search is recorded when a results page is produced; a page visit",
		"est comptée à chaque affichage d'une page du site, même sans recherche." => "is counted each time a site page is displayed, even without a search.",
		"recherches" => "searches",
		"visites de pages" => "page visits",
		"visiteurs approx." => "approx. visitors",
		"Top des villes consultées" => "Top viewed cities",
		"Top des départements consultés" => "Top viewed departments",
		"Top des régions consultées" => "Top viewed regions",
		"Carburants les plus recherchés" => "Most searched fuels",
		"Recherches par mode" => "Searches by mode",
		"Tendance annuelle des prix" => "Annual price trend",
		"Moyennes mensuelles calculées côté serveur depuis l'archive annuelle officielle XML" => "Monthly averages calculated server-side from the official annual XML archive",
		"Source officielle" => "Official source",
		"dernière mise à jour du cache le" => "cache last updated on",
		"Statistiques générées à partir du CSV de consultations." => "Statistics generated from the consultations CSV.",
		"Statistics générées à partir du CSV de consultations." => "Statistics generated from the consultations CSV.",
		"Etat des statistiques" => "Statistics status",
		"Consultations enregistrees" => "Recorded searches",
		"Visites de pages" => "Page visits",
		"Visiteurs approx." => "Approx. visitors",
		"Nombre de villes dans le top" => "Number of cities in the top",
	];
}

function traductions_technique(): array
{
	return [
		"Page technique conservee pour la validation de la partie 1 du projet." => "Technical page kept for validation of part 1 of the project.",
		"Page technique conservee pour validation." => "Technical page kept for validation.",
		"Cette page reste accessible depuis le footer pour montrer l'avancement initial" => "This page remains accessible from the footer to show the initial progress",
		"et la logique technique reutilisee dans Plein Malin." => "and the technical logic reused in Plein Malin.",
		"Synthese technique du projet" => "Project technical summary",
		"geolocalisation IP et API officielle des prix carburants, traitees cote serveur en PHP." => "IP geolocation and the official fuel price API, processed server-side in PHP.",
		"lecture de <code>data/sample_fuel_prices.xml</code> et archive annuelle officielle pour les tendances de prix." => "reading <code>data/sample_fuel_prices.xml</code> and the official annual archive for price trends.",
		"regions, departements, villes, consultations et page visits." => "regions, departments, cities, searches and page visits.",
		"regions, departements, villes, consultations et visites de pages." => "regions, departments, cities, searches and page visits.",
		"theme jour/nuit, langue, derniere recherche et derniere ville consultee." => "day/night theme, language, last search and last viewed city.",
		"tops des villes, departements, regions, carburants, modes de recherche et tendances de prix." => "top cities, departments, regions, fuels, search modes and price trends.",
		"API Ghibli indisponible pour le moment." => "Ghibli API is unavailable for now.",
		"Flux XML carburants" => "Fuel XML feed",
		"Lecture de <code>data/sample_fuel_prices.xml</code> avec <code>simplexml_load_file()</code>." => "Reading <code>data/sample_fuel_prices.xml</code> with <code>simplexml_load_file()</code>.",
		"Aucune donnee XML disponible." => "No XML data available.",
		"Flux JSON cote serveur" => "Server-side JSON feed",
		"Geolocalisation IP approx. obtenue en PHP avec cache fichier JSON." => "Approximate IP geolocation obtained in PHP with a JSON file cache.",
		"IP detectee" => "Detected IP",
		"Ville retournee" => "Returned city",
		"City retournee" => "Returned city",
		"Region retournee" => "Returned region",
		"Source utilisee" => "Source used",
		"echantillon local" => "local sample",
		"Flux carburants cote serveur" => "Server-side fuel feed",
		"Les stations-service sont recherchees depuis l'API JSON officielle du" => "Service stations are searched from the official JSON API of the",
		"gouvernement avec un filtre sur le departement et la ville." => "government with a filter on department and city.",
		"Requete HTTP cote serveur en PHP" => "Server-side HTTP request in PHP",
		"Reponse JSON transformee en tableaux PHP" => "JSON response transformed into PHP arrays",
		"Reutilisation dans la page resultats pour les prix et services" => "Reused in the results page for prices and services",
		"Stockages attendus" => "Expected storage",
		"CSV serveur: historique des consultations" => "Server CSV: search history",
		"Cookie <code>last_visited_city</code>: derniere ville" => "Cookie <code>last_visited_city</code>: last city",
		"Cookie <code>last_search_params</code>: derniere recherche complete" => "Cookie <code>last_search_params</code>: complete last search",
		"Cookie <code>theme</code>: jour ou nuit" => "Cookie <code>theme</code>: day or night",
		"Cookie <code>lang</code>: langue d'affichage" => "Cookie <code>lang</code>: display language",
		"Cache JSON: reponses externes et fallback sur cache expire" => "JSON cache: external responses and fallback on expired cache",
	];
}

function traductions_pages_info(): array
{
	return [
		"À propos de Plein Malin" => "About Plein Malin",
		"Présentation du projet Plein Malin." => "Presentation of the Plein Malin project.",
		"Plein Malin est un site réalisé dans le cadre du projet de développement web." => "Plein Malin is a website created for the web development project.",
		"Il permet de rechercher des stations-service en France et de comparer les prix des carburants." => "It lets users search for service stations in France and compare fuel prices.",
		"Objectif du site" => "Website goal",
		"Choisir une région depuis une carte interactive." => "Choose a region from an interactive map.",
		"Sélectionner un département et une ville." => "Select a department and a city.",
		"Afficher les stations disponibles et les prix des carburants." => "Display available stations and fuel prices.",
		"Consulter des statistiques sur les recherches effectuées." => "View statistics about completed searches.",
		"Fonctionnalités principales" => "Main features",
		"Recherche par ville, par département ou autour d'une position estimée." => "Search by city, by department, or around an estimated position.",
		"Choix de plusieurs carburants." => "Choice of several fuels.",
		"Mode jour/nuit mémorisé avec un cookie." => "Day/night mode remembered with a cookie.",
		"Affichage en français ou en anglais." => "Display in French or English.",
		"Stockage serveur des consultations dans un fichier CSV." => "Server-side storage of searches in a CSV file.",
		"Sobriété numérique" => "Digital sobriety",
		"Les images de la carte et du bouton retour en haut sont compressées pour réduire le poids des pages." => "The map and back-to-top button images are compressed to reduce page weight.",
		"Les réponses des API sont mises en cache côté serveur afin d'éviter des requêtes répétées." => "API responses are cached server-side to avoid repeated requests.",
		"La page de résultats affiche uniquement les premières stations utiles pour limiter la quantité de contenu chargé." => "The results page only displays the first useful stations to limit the amount of loaded content.",
		"Le site utilise très peu de JavaScript et privilégie des fichiers simples comme CSV, JSON et XML." => "The site uses very little JavaScript and favors simple files such as CSV, JSON and XML.",
		"Sources des données utilisées par Plein Malin." => "Data sources used by Plein Malin.",
		"Cette page résume les fichiers et services utilisés par le site pour répondre aux consignes du projet." => "This page summarizes the files and services used by the site to meet the project requirements.",
		"Données statiques" => "Static data",
		"Régions, départements et villes : fichiers CSV locaux placés dans le dossier" => "Regions, departments and cities: local CSV files placed in the",
		"Ces fichiers servent à alimenter les listes de sélection du formulaire de recherche." => "These files populate the selection lists in the search form.",
		"Données dynamiques" => "Dynamic data",
		"Prix des carburants : API officielle des prix des carburants, interrogée côté serveur en PHP." => "Fuel prices: official fuel price API, queried server-side in PHP.",
		"Géolocalisation : position estimée à partir de l'adresse IP de l'utilisateur." => "Geolocation: position estimated from the user's IP address.",
		"Tendances : archive annuelle XML officielle de" => "Trends: official annual XML archive from",
		"Formats exploités" => "Used formats",
		"données locales et historique des consultations." => "local data and search history.",
		"réponses de l'API carburants et cache serveur." => "fuel API responses and server cache.",
		"lecture d'un fichier de démonstration et archive annuelle des tendances." => "reading a demonstration file and the annual trends archive.",
		"thème, langue et dernière recherche." => "theme, language and last search.",
		"Mode d'emploi" => "User guide",
		"Mode d'emploi et questions fréquentes du site Plein Malin." => "User guide and frequently asked questions for Plein Malin.",
		"Cette page explique rapidement comment utiliser Plein Malin et comment lire les résultats affichés." => "This page quickly explains how to use Plein Malin and how to read the displayed results.",
		"Faire une recherche" => "Make a search",
		"Cliquez sur une région dans la carte interactive." => "Click a region on the interactive map.",
		"Choisissez un département dans la liste." => "Choose a department from the list.",
		"Choisissez une ville ou cochez le mode tout le département." => "Choose a city or select whole department mode.",
		"Sélectionnez un ou plusieurs carburants." => "Select one or more fuels.",
		"Lancez la recherche pour afficher les stations." => "Launch the search to display stations.",
		"Le bouton Autour de moi utilise une position estimée à partir de l'adresse IP." => "The Near me button uses a position estimated from the IP address.",
		"Cette localisation est pratique pour une recherche rapide, mais elle reste approximative." => "This location is useful for a quick search, but it remains approximate.",
		"Lire les résultats" => "Read the results",
		"Le prix moyen résume les prix trouvés pour la recherche actuelle." => "The average price summarizes prices found for the current search.",
		"Le meilleur prix permet d'aller directement à la station correspondante." => "The best price takes you directly to the matching station.",
		"Le bouton Voir les détails affiche les carburants et les services proposés par une station." => "The View details button displays fuels and services offered by a station.",
		"Le lien Voir sur une carte ouvre la station dans OpenStreetMap quand les coordonnées sont disponibles." => "The View on a map link opens the station in OpenStreetMap when coordinates are available.",
		"Questions fréquentes" => "Frequently asked questions",
		"Pourquoi la position est approximative ?" => "Why is the position approximate?",
		"La position vient de l'adresse IP. Elle peut pointer vers une ville proche plutôt que vers l'adresse exacte." => "The position comes from the IP address. It may point to a nearby city instead of the exact address.",
		"Pourquoi certains carburants n'apparaissent pas ?" => "Why do some fuels not appear?",
		"Une station ne propose pas toujours tous les carburants, ou l'API ne fournit pas toujours un prix à jour pour chaque carburant." => "A station does not always offer all fuels, or the API does not always provide an up-to-date price for each fuel.",
		"D'où viennent les prix ?" => "Where do prices come from?",
		"Les prix viennent de l'API officielle des prix des carburants et sont traités côté serveur en PHP." => "Prices come from the official fuel price API and are processed server-side in PHP.",
		"À quoi correspondent les services ?" => "What do services mean?",
		"Les services sont les équipements ou prestations proposés par la station, par exemple lavage, gonflage, boutique ou automate CB." => "Services are the equipment or facilities offered by the station, for example washing, air pump, shop or card machine.",
		"Informations sur les cookies et les données conservées par Plein Malin." => "Information about cookies and data stored by Plein Malin.",
		"Le site utilise quelques mécanismes de mémorisation pour améliorer l'expérience utilisateur" => "The site uses a few storage mechanisms to improve the user experience",
		"et produire les statistiques demandées dans le projet." => "and produce the statistics required by the project.",
		"Cookies utilisés" => "Cookies used",
		"mémorise le choix du mode jour ou nuit." => "remembers the day or night mode choice.",
		"mémorise la langue d'affichage." => "remembers the display language.",
		"mémorise la dernière ville consultée." => "remembers the last viewed city.",
		"mémorisent la dernière recherche." => "remember the last search.",
		"Données stockées côté serveur" => "Data stored server-side",
		"Les consultations sont enregistrées dans" => "Searches are recorded in",
		"Les visites de pages sont enregistrées dans" => "Page visits are recorded in",
		"Chaque ligne contient un horodatage pour permettre les statistiques." => "Each line contains a timestamp to enable statistics.",
		"La recherche autour de moi utilise une position estimée à partir de l'adresse IP." => "The near me search uses a position estimated from the IP address.",
		"Cette position est approximative et peut être différente de la position réelle de l'utilisateur." => "This position is approximate and may differ from the user's real position.",
		"Plan du site" => "Site map",
		"Plan du site Plein Malin." => "Plein Malin site map.",
		"Retrouvez ici les pages principales du site et les pages d'information du projet." => "Find the main site pages and project information pages here.",
		"Pages principales" => "Main pages",
		"Pages d'information" => "Information pages",
		"Limites des données" => "Data limits",
		"Mode d'emploi et FAQ" => "User guide and FAQ",
	];
}

function traductions_interface(): array
{
	return array_merge(
		traductions_communes(),
		traductions_accueil(),
		traductions_recherche(),
		traductions_resultats(),
		traductions_statistiques(),
		traductions_technique(),
		traductions_pages_info()
	);
}

function traduire_interface(string $html, string $langue): string
{
	if ($langue !== "en") {
		return $html;
	}

	return strtr($html, traductions_interface());
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
		return "Aucune recherche lancée.";
	}

	if ($useGeo) {
		return "Recherche autour de votre position approximative.";
	}

	if ($departmentMode) {
		return "Recherche dans tout le département sélectionné.";
	}

	if ($stations === []) {
		return "Aucune station trouvée dans la ville sélectionnée.";
	}

	return "Recherche dans la ville sélectionnée.";
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
		"sort" => in_array(($parametres["sort"] ?? "price"), ["price", "price_desc", "distance", "name"], true) ? (string) $parametres["sort"] : "price",
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
		return "recherche.php?search_mode=manual";
	}

	unset($parametres["use_geo"]);
	$parametres["search_mode"] = "manual";
	return "recherche.php?" . http_build_query($parametres);
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

function est_arrondissement_municipal(array $city): bool
{
	$department = (string) ($city["department_code"] ?? "");
	$cityCode = (string) ($city["city_code"] ?? "");
	$cityName = trim((string) ($city["city_name"] ?? ""));

	if ($department === "75" && str_starts_with($cityName, "Paris ")) {
		return true;
	}

	if (str_starts_with($cityCode, "6938") && str_starts_with($cityName, "Lyon ")) {
		return true;
	}

	if (preg_match('/^132(0[1-9]|1[0-6])$/', $cityCode) === 1 && str_starts_with($cityName, "Marseille ")) {
		return true;
	}

	return false;
}

function lire_stations_api(?array $city, bool $departmentMode = false, ?array $origin = null, int $radiusKm = 10): ?array
{
	if ($city === null && $origin === null) {
		return [];
	}

	$departement = (string) ($city["department_code"] ?? "");
	$ville = trim((string) ($city["city_name"] ?? ""));
	$codePostal = trim((string) ($city["postal_code"] ?? ""));
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
			if ($codePostal !== "" && est_arrondissement_municipal($city)) {
				$filtres[] = 'code_postal="' . addslashes($codePostal) . '"';
			} else {
				$filtres[] = 'ville="' . addslashes($ville) . '"';
			}
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
			$station["main_updated_at"] = is_string($carburantPrincipal) ? (string) ($station["prices"][$carburantPrincipal]["updated_at"] ?? "") : "";
			$resultat[] = $station;
		}

	usort($resultat, static function (array $a, array $b) use ($sortBy): int {
		if ($sortBy === "price_desc") {
			return ($b["main_price"] ?? 0) <=> ($a["main_price"] ?? 0);
		}

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

function formater_date_heure(?string $date): string
{
	if ($date === null || trim($date) === "") {
		return "";
	}

	$timestamp = strtotime($date);
	if ($timestamp === false) {
		return "";
	}

	return date("d/m/Y H:i", $timestamp);
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
			$donnees["source_url"] = $donnees["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee/" . $annee;
			$donnees["cached_at"] = date("c", (int) $cache["time"]);
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
		"source_url" => "https://donnees.roulez-eco.fr/opendata/annee/" . $annee,
		"year" => $annee,
		"cached_at" => date("c"),
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

	$handle = fopen($fichier, "a");
	if ($handle !== false) {
		fputcsv($handle, $ligne, ",", "\"", "\\");
		fclose($handle);
	}
}

function calculer_statistiques(): array
{
	$lignes = lire_csv_assoc(PM_STORAGE_DIR . "/consultations.csv");
	$visites = lire_csv_assoc(PM_STORAGE_DIR . "/page_visits.csv");
	$topVilles = [];
	$topDepartements = [];
	$topRegions = [];
	$topCarburants = [];
	$topModes = [];
	$visiteurs = [];
	$visiteursPages = [];

	foreach ($lignes as $ligne) {
		$mode = trim($ligne["mode"] ?? "");
		if ($mode !== "") {
			if (!isset($topModes[$mode])) {
				$topModes[$mode] = 0;
			}
			$topModes[$mode]++;
		}

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

		foreach (explode(",", (string) ($ligne["fuel"] ?? "")) as $carburant) {
			$carburant = trim($carburant);
			if ($carburant === "") {
				continue;
			}

			if (!isset($topCarburants[$carburant])) {
				$topCarburants[$carburant] = 0;
			}
			$topCarburants[$carburant]++;
		}

		$hash = trim($ligne["visitor_hash"] ?? "");
		if ($hash !== "") {
			$visiteurs[$hash] = true;
		}
	}

	foreach ($visites as $visite) {
		$hash = trim($visite["visitor_hash"] ?? "");
		if ($hash !== "") {
			$visiteursPages[$hash] = true;
		}
	}

	arsort($topVilles);
	arsort($topDepartements);
	arsort($topRegions);
	arsort($topCarburants);
	arsort($topModes);

	return [
		"top_cities" => array_slice($topVilles, 0, 8, true),
		"top_departments" => array_slice($topDepartements, 0, 8, true),
		"top_regions" => array_slice($topRegions, 0, 8, true),
		"top_fuels" => array_slice($topCarburants, 0, 8, true),
			"top_modes" => $topModes,
			"total_visitors" => count($visiteurs),
			"page_visit_count" => count($visites),
			"page_visitor_count" => count($visiteursPages),
			"consultation_count" => count($lignes),
		];
	}
