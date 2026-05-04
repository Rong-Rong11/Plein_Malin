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
require_once __DIR__ . "/fonctions-traductions.php";

function texte_securise(string $texte): string
{
	$texte = traduire_texte($texte);

	return htmlspecialchars($texte, ENT_QUOTES, "UTF-8");
}

/**
 * Construit une cle de cache lisible a partir d'une valeur technique.
 *
 * @param string $prefixe Prefixe metier du cache.
 * @param string $valeur Valeur source a transformer.
 * @return string Nom de cle stable et compatible avec un fichier.
 *
 * @ingroup securite
 */
function construire_cle_cache(string $prefixe, string $valeur): string
{
	$segment = strtolower($valeur);
	$segment = preg_replace('/[^a-z0-9]+/', '-', $segment) ?? "";
	$segment = trim($segment, "-");

	if ($segment === "") {
		$segment = "vide";
	}

	if (strlen($segment) > 100) {
		$segment = substr($segment, 0, 60) . "-" . substr($segment, -30);
	}

	return $prefixe . "_" . $segment . "_" . strlen($valeur);
}

/**
 * Ferme proprement le tampon de sortie s'il a ete ouvert par l'en-tete.
 *
 * @return void
 *
 * @ingroup securite
 */
function fermer_tampon_sortie_si_actif(): void
{
	if (ob_get_level() > 0) {
		ob_end_flush();
	}
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
