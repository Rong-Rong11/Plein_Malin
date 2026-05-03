<?php

/**
 * @file
 * @brief Tables de traduction francais/anglais de l'interface.
 *
 * Les traductions sont appliquees sur le HTML final afin de garder les pages
 * PHP simples et compatibles avec la version francaise par defaut.
 */

/**
 * Traductions communes reutilisees dans l'en-tete, le menu et le pied de page.
 *
 * @return array<string,string> Table de correspondance francais vers anglais.
 */
function traductions_communes(): array
{
	return [
		"Données" => "Data",
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

/**
 * Traductions propres a la page d'accueil.
 *
 * @return array<string,string> Textes de l'accueil traduits en anglais.
 */
function traductions_accueil(): array
{
	return [
		"Prix des carburants" => "Fuel prices",
		"Trouvez une station plus facilement" => "Find a station more easily",
		"Comparer les prix des carburants et trouver une station en France." => "Compare fuel prices and find a station in France.",
		"Plein Malin aide à comparer rapidement les prix du carburant en France." => "Plein Malin helps you quickly compare fuel prices in France.",
		"Le site propose une recherche manuelle par région, département et ville," => "The site offers a manual search by region, department and city,",
		"mais aussi une recherche autour de votre position approximative." => "as well as a search around your approximate location.",
		"L'objectif est de garder une interface simple : choisir un périmètre," => "The goal is to keep the interface simple: choose an area,",
		"sélectionner un ou plusieurs carburants, puis consulter des résultats" => "select one or more fuels, then view results",
		"lisibles sans surcharge inutile." => "that are readable without unnecessary clutter.",
		"Plein Malin permet de choisir une région, un département puis une ville" => "Plein Malin lets you choose a region, a department, then a city",
		"pour consulter les stations-service et comparer les prix." => "to view service stations and compare prices.",
		"Rechercher une station" => "Search for a station",
		"Ce que fait le site" => "What the site does",
		"Comment fonctionne le site" => "How the site works",
		"La recherche principale suit un parcours guidé : vous choisissez d'abord" => "The main search follows a guided path: first choose",
		"une région, puis un département, puis une ville. Ce fonctionnement permet" => "a region, then a department, then a city. This approach helps",
		"d'encadrer la recherche et de garder une navigation compréhensible." => "structure the search and keep navigation easy to understand.",
		"Si vous voulez aller plus vite, le mode" => "If you want to go faster, the",
		"Autour de moi" => "Near me",
		"utilise une estimation de position basée sur l'adresse IP pour proposer" => "mode uses an estimated location based on the IP address to suggest",
		"des stations proches dans un rayon choisi." => "nearby stations within a selected radius.",
		"Les résultats affichent les stations trouvées, les prix disponibles pour" => "The results show the stations found, the available prices for",
		"les carburants sélectionnés et un accès direct à une carte quand les" => "the selected fuels and a direct map link when",
		"coordonnées sont connues." => "coordinates are available.",
		"Informations utiles" => "Useful information",
		"Recherche par région, département et ville" => "Search by region, department and city",
		"Affichage simple des stations et des prix" => "Simple display of stations and prices",
		"Statistiques à partir des consultations enregistrées" => "Statistics based on recorded searches",
		"Statistiques a partir des consultations enregistrees" => "Statistics based on recorded searches",
		"Dernière recherche" => "Last search",
		"Dernière recherche le" => "Last search on",
		"Dernière ville consultée" => "Last viewed city",
		"Dernier département consulté" => "Last viewed department",
		"Reprendre cette recherche" => "Resume this search",
		"Recherche guidée" => "Guided search",
		"Recherche guidee" => "Guided search",
		"La recherche suit l'ordre région, département puis ville pour rester simple." => "The search follows region, department, then city to stay simple.",
		"La recherche suit l'ordre région, département puis ville pour rester" => "The search follows region, department, then city to stay",
		"simple et éviter les choix incohérents." => "simple and avoid inconsistent choices.",
		"Résultats lisibles" => "Readable results",
		"Chaque station affiche son adresse, ses prix et ses informations utiles." => "Each station shows its address, prices and useful information.",
		"Chaque station affiche son adresse, ses prix et les informations" => "Each station shows its address, prices and information",
		"utiles pour comparer rapidement plusieurs points de vente." => "useful for quickly comparing several stations.",
		"Une page dédiée résume les recherches effectuées, les visites et les" => "A dedicated page summarizes completed searches, visits and",
		"tendances de prix observées dans les données disponibles." => "price trends observed in the available data.",
		"Une page dédiée résume les villes les plus consultées et le nombre de visites." => "A dedicated page summarizes the most viewed cities and visit counts.",
		"Illustration de recherche de carburant Plein Malin" => "Plein Malin fuel search illustration",
	];
}

/**
 * Traductions propres au formulaire de recherche.
 *
 * @return array<string,string> Textes de recherche traduits en anglais.
 */
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
		"Nom" => "Name",
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

/**
 * Traductions propres a la page de resultats.
 *
 * @return array<string,string> Textes de resultats traduits en anglais.
 */
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
		"Vous êtes approximativement à" => "You are approximately in",
		"d'après une position estimée à partir de l'adresse IP." => "according to a position estimated from the IP address.",
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
		"Impossible d'afficher les stations : l'API officielle des carburants ne répond pas. Réessayez plus tard." => "Unable to display stations: the official fuel API is not responding. Please try again later.",
		"Vous pouvez aussi augmenter le rayon de recherche." => "You can also increase the search radius.",
		"station(s) trouvée(s)." => "station(s) found.",
		"prix moyen trouvé" => "average price found",
		"meilleur prix trouvé" => "best price found",
		"Cliquer pour voir la station" => "Click to view the station",
		"Distance :" => "Distance:",
		"flux" => "feed",
		"prix mis à jour le" => "price updated on",
		"Voir les détails" => "View details",
		"Services" => "Services",
		"Services disponibles" => "Available services",
		"Aucun service indique." => "No service indicated.",
		"Pompe à air" => "Air pump",
		"Pompe a air" => "Air pump",
		"Oui" => "Yes",
		"Non" => "No",
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

/**
 * Traductions propres a la page de statistiques.
 *
 * @return array<string,string> Textes de statistiques traduits en anglais.
 */
function traductions_statistiques(): array
{
	return [
		"Rubrique statistiques" => "Statistics section",
		"Consultations Plein Malin" => "Plein Malin consultations",
		"Page statistiques de Plein Malin." => "Plein Malin statistics page.",
		"Les recherches correspondent aux consultations avec critères. Les visites comptent aussi" => "Searches correspond to queries with criteria. Visits also count",
		"les pages vues sans lancement de recherche." => "page views without launching a search.",
		"Une recherche est enregistrée quand une page de résultats est produite ; une visite de page" => "A search is recorded when a results page is produced; a page visit",
		"est comptée à chaque affichage d'une page du site, même sans recherche." => "is counted each time a site page is displayed, even without a search.",
		"recherches" => "searches",
		"visites de pages" => "page visits",
		"visiteurs approx." => "approx. visitors",
		"Graphiques des consultations" => "Consultation charts",
		"Top des villes consultées" => "Top viewed cities",
		"Top des départements consultés" => "Top viewed departments",
		"Top des régions consultées" => "Top viewed regions",
		"Carburants les plus recherchés" => "Most searched fuels",
		"Recherches par mode" => "Searches by mode",
		"Tendance annuelle des prix" => "Annual price trend",
		"Moyennes mensuelles calculées côté serveur depuis l'archive annuelle officielle XML" => "Monthly averages calculated server-side from the official annual XML archive",
		"Tableau mensuel des prix" => "Monthly price table",
		"Mois" => "Month",
		"Prix moyen" => "Average price",
		"Relevés" => "Records",
		"Moyenne annuelle de référence" => "Reference yearly average",
		"archive complète" => "complete archive",
		"de l'archive complète" => "from the complete archive",
		"Source officielle" => "Official source",
		"dernière mise à jour du cache le" => "cache last updated on",
		"Statistiques générées à partir du CSV de consultations." => "Statistics generated from the consultations CSV.",
		"Statistics générées à partir du CSV de consultations." => "Statistics generated from the consultations CSV.",
		"Etat des statistiques" => "Statistics status",
		"Consultations enregistrees" => "Recorded searches",
		"Visites de pages" => "Page visits",
		"Visiteurs approx." => "Approx. visitors",
		"Nombre de villes dans le top" => "Number of cities in the top",
		"Aucune consultation enregistrée pour le moment." => "No recorded search for now.",
		"Aucun département enregistré pour le moment." => "No recorded department for now.",
		"Aucune région enregistrée pour le moment." => "No recorded region for now.",
		"Aucun carburant enregistré pour le moment." => "No recorded fuel for now.",
		"Aucun mode de recherche enregistré pour le moment." => "No recorded search mode for now.",
		"Tendances indisponibles pour le moment." => "Trends are unavailable for now.",
		"Aucune donnée disponible." => "No data available.",
		"avec une moyenne annuelle de référence sur" => "with a reference yearly average for",
		"relevés" => "records",
		"de l'archive complète :" => "from the complete archive:",
	];
}

/**
 * Traductions propres a la page technique.
 *
 * @return array<string,string> Textes techniques traduits en anglais.
 */
function traductions_technique(): array
{
	return [
		"Page technique conservee pour la validation de la partie 1 du projet." => "Technical page kept for validation of part 1 of the project.",
		"Page technique conservee pour validation." => "Technical page kept for validation.",
		"API Ghibli" => "Ghibli API",
		"Cette page reste accessible depuis le footer pour montrer l'avancement initial" => "This page remains accessible from the footer to show the initial progress",
		"et la logique technique reutilisee dans Plein Malin." => "and the technical logic reused in Plein Malin.",
		"Synthese technique du projet" => "Project technical summary",
		"geolocalisation IP et API officielle des prix carburants, traitees cote serveur en PHP." => "IP geolocation and the official fuel price API, processed server-side in PHP.",
		"lecture de <code>data/sample_fuel_prices.xml</code> et archive annuelle officielle pour les tendances de prix." => "reading <code>data/sample_fuel_prices.xml</code> and the official annual archive for price trends.",
		"lecture de" => "reading",
		"et archive annuelle officielle pour les tendances de prix." => "and the official annual archive for price trends.",
		"regions, departements, villes, consultations et page visits." => "regions, departments, cities, searches and page visits.",
		"regions, departements, villes, consultations et visites de pages." => "regions, departments, cities, searches and page visits.",
		"theme jour/nuit, langue, derniere recherche et derniere ville consultee." => "day/night theme, language, last search and last viewed city.",
		"tops des villes, departements, regions, carburants, modes de recherche et tendances de prix." => "top cities, departments, regions, fuels, search modes and price trends.",
		"API Ghibli indisponible pour le moment." => "Ghibli API is unavailable for now.",
		"Affiche du film" => "Film poster",
		"Affiche du film " => "Film poster ",
		"Bannière du film" => "Film banner",
		"Bannière du film " => "Film banner ",
		"Flux XML carburants" => "Fuel XML feed",
		"Lecture de <code>data/sample_fuel_prices.xml</code> avec <code>simplexml_load_file()</code>." => "Reading <code>data/sample_fuel_prices.xml</code> with <code>simplexml_load_file()</code>.",
		"Lecture de" => "Reading",
		"avec" => "with",
		"Aucune donnee XML disponible." => "No XML data available.",
		"Flux JSON cote serveur" => "Server-side JSON feed",
		"Geolocalisation IP approx. obtenue en PHP avec cache fichier JSON." => "Approximate IP geolocation obtained in PHP with a JSON file cache.",
		"IP detectee" => "Detected IP",
		"Ville retournee" => "Returned city",
		"City retournee" => "Returned city",
		"Region retournee" => "Returned region",
		"Non trouvee" => "Not found",
		"Source utilisee" => "Source used",
		"echantillon local" => "local sample",
		"Flux carburants cote serveur" => "Server-side fuel feed",
		"Les stations-service sont recherchees depuis l'API JSON officielle du" => "Service stations are searched from the official JSON API of the",
		"gouvernement avec un filtre sur le departement et la ville." => "government with a filter on department and city.",
		"Requete HTTP cote serveur en PHP" => "Server-side HTTP request in PHP",
		"Reponse JSON transformee en tableaux PHP" => "JSON response transformed into PHP arrays",
		"Reutilisation dans la page resultats pour les prix et services" => "Reused in the results page for prices and services",
		"Reutilisation dans la page resultats pour les prix, distances et liens de carte" => "Reused in the results page for prices, distances and map links",
		"Stockages attendus" => "Expected storage",
		"CSV serveur: historique des consultations" => "Server CSV: search history",
		"Cookie <code>last_visited_city</code>: derniere ville" => "Cookie <code>last_visited_city</code>: last city",
		"Cookie <code>last_search_params</code>: derniere recherche complete" => "Cookie <code>last_search_params</code>: complete last search",
		"Cookie <code>theme</code>: jour ou nuit" => "Cookie <code>theme</code>: day or night",
		"Cookie <code>lang</code>: langue d'affichage" => "Cookie <code>lang</code>: display language",
		"derniere ville" => "last city",
		"derniere recherche complete" => "complete last search",
		"jour ou nuit" => "day or night",
		"langue d'affichage" => "display language",
		"Cache JSON: reponses externes et fallback sur cache expire" => "JSON cache: external responses and fallback on expired cache",
	];
}

/**
 * Traductions des pages d'information du projet.
 *
 * @return array<string,string> Textes des pages annexes traduits en anglais.
 */
function traductions_pages_info(): array
{
	return [
		"À propos de Plein Malin" => "About Plein Malin",
		"Présentation du projet Plein Malin." => "Presentation of the Plein Malin project.",
		"À propos du projet Plein Malin." => "About the Plein Malin project.",
		"Mode d&#039;emploi et questions fréquentes du site Plein Malin." => "User guide and frequently asked questions for Plein Malin.",
		"Aide et mode d&#039;emploi." => "Help and user guide.",
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
		"Cette page résume les sources et fichiers utilisés par le site pour répondre aux consignes du projet." => "This page summarizes the sources and files used by the site to meet the project requirements.",
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
		"Chaque station affiche son adresse, sa distance et les prix des carburants sélectionnés." => "Each station shows its address, distance and the selected fuel prices.",
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
		"<code>last_search</code> et <code>last_search_params</code> : mémorisent la dernière recherche." => "<code>last_search</code> and <code>last_search_params</code>: remember the last search.",
		"Géolocalisation" => "Geolocation",
		"La recherche autour de moi utilise une position estimée à partir de l'adresse IP." => "The near me search uses a position estimated from the IP address.",
		"Cette position est approximative et peut être différente de la position réelle de l'utilisateur." => "This position is approximate and may differ from the user's real position.",
		"Plan du site" => "Site map",
		"Navigation" => "Navigation",
		"Plan du site Plein Malin." => "Plein Malin site map.",
		"Retrouvez ici les pages principales du site et les pages d'information du projet." => "Find the main site pages and project information pages here.",
		"Schéma de navigation" => "Navigation diagram",
		"Le schéma se lit de haut en bas : commencez par l'accueil, puis suivez la colonne de gauche" => "Read the diagram from top to bottom: start at the home page, then follow the left column",
		"pour effectuer une recherche. La colonne de droite regroupe les pages d'explication du projet." => "to perform a search. The right column groups the project's explanation pages.",
		"Trait orange" => "Orange line",
		"Trait gris" => "Gray line",
		"parcours principal de l'utilisateur" => "main user journey",
		"pages d'information" => "information pages",
		"Départ" => "Start",
		"Page d'entrée du site" => "Site entry page",
		"Parcours principal : chercher une station" => "Main journey: find a station",
		"Étape 1" => "Step 1",
		"Étape 2" => "Step 2",
		"Étape 3" => "Step 3",
		"Choisir une région, un département, une ville et un carburant" => "Choose a region, a department, a city and a fuel",
		"Comparer les stations, les prix et les liens de carte" => "Compare stations, prices and map links",
		"Voir les recherches, visites et tendances de prix" => "View searches, visits and price trends",
		"Pages d'explication" => "Explanation pages",
		"Comprendre le but du site" => "Understand the site's purpose",
		"Apprendre à utiliser la recherche et les résultats" => "Learn how to use search and results",
		"Voir d'où viennent les données CSV, JSON, XML et API" => "See where the CSV, JSON, XML and API data come from",
		"Comprendre les cookies et le stockage serveur" => "Understand cookies and server-side storage",
		"Afficher la démonstration technique des flux" => "View the technical demo of the feeds",
		"Pages principales" => "Main pages",
		"Pages d'information" => "Information pages",
		"Limites des données" => "Data limits",
		"Mode d'emploi et FAQ" => "User guide and FAQ",
		"Si l'API officielle ne répond pas, le site affiche un message d'erreur au lieu d'annoncer simplement zéro station." => "If the official API does not respond, the site shows an error message instead of simply reporting zero stations.",
		"La géolocalisation par adresse IP est approximative : elle peut indiquer une ville proche, pas une adresse exacte." => "IP geolocation is approximate: it may point to a nearby city, not an exact address.",
		"Le cache serveur évite de refaire trop souvent les mêmes requêtes, mais il peut afficher une réponse déjà enregistrée pendant quelques heures." => "The server cache avoids repeating the same requests too often, but it may display a previously saved response for a few hours.",
	];
}

/**
 * Fusionne toutes les tables de traduction de l'interface.
 *
 * @return array<string,string> Table complete de traduction.
 */
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
/**
 * Traduit un texte directement lorsque la langue anglaise est active.
 *
 * @param string $texte Texte a traduire.
 * @param string|null $langue Langue active, ou langue globale par defaut.
 * @return string Texte traduit ou laisse intact.
 */
function traduire_texte(string $texte, ?string $langue = null): string
{
	if ($langue === null) {
		$langue = $GLOBALS["langue"] ?? "fr";
	}

	if ($langue !== "en") {
		return $texte;
	}

	return strtr($texte, traductions_interface());
}

/**
 * Traduit le HTML final lorsque la langue anglaise est active.
 *
 * @param string $contenuHtml HTML produit par la page.
 * @param string $langue Langue active.
 * @return string HTML traduit ou laisse intact en francais.
 */
function traduire_interface(string $contenuHtml, string $langue): string
{
	if ($langue !== "en") {
		return $contenuHtml;
	}

	return strtr($contenuHtml, traductions_interface());
}
