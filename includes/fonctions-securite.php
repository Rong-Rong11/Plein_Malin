<?php

/**
 * @file
 * @brief Securite, initialisation et chemins techniques.
 */

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
	if (function_exists("traduire_texte")) {
		$texte = traduire_texte($texte);
	}

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
