<?php

/**
 * @file
 * @brief Securite, initialisation et chemins techniques.
 */

/**
 * @file
 * @brief Fonctions communes et logique metier du site Plein Malin.
 *
 * Ce fichier regroupe les traitements partages par les pages : securisation
 * HTML, cookies, lecture CSV, appels API, recherche de stations, statistiques,
 * graphiques SVG et lecture XML.
 */

/**
 * @defgroup securite Securite et initialisation
 * Fonctions liees a la preparation du site, aux chemins et a la securisation HTML.
 */

/**
 * @defgroup preferences Preferences utilisateur
 * Gestion du theme, de la langue et des cookies de navigation.
 */

/**
 * @defgroup donnees Donnees locales
 * Lecture des fichiers CSV de regions, departements et villes.
 */

/**
 * @defgroup recherche Recherche de carburants
 * Normalisation des criteres, appels API et filtrage des stations.
 */

/**
 * @defgroup statistiques Statistiques et graphiques
 * Enregistrement des consultations, calcul des statistiques et tendances de prix.
 */

/**
 * Chargement de la configuration et des traductions communes.
 */
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/translations.php";

/**
 * Echappe une chaine avant affichage dans le HTML.
 *
 * @param string $texte Texte potentiellement fourni par une source externe.
 * @return string Texte protege contre l'injection HTML.
 *
 * @ingroup securite
 */
function texte_securise(string $texte): string
{
	return htmlspecialchars($texte, ENT_QUOTES, "UTF-8");
}
/**
 * Cree les dossiers et fichiers de stockage attendus par l'application.
 *
 * @return void
 *
 * @ingroup securite
 */
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
/**
 * Retourne le chemin a utiliser pour les cookies du site.
 *
 * @return string Chemin compatible avec une installation locale ou en sous-dossier.
 *
 * @ingroup securite
 */
function chemin_cookie(): string
{
	$chemin = dirname($_SERVER["SCRIPT_NAME"] ?? "/");

	if ($chemin === "\\" || $chemin === "." || $chemin === "/") {
		return "/";
	}

	return rtrim(str_replace("\\", "/", $chemin), "/") . "/";
}
