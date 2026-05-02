<?php

/**
 * @file
 * @brief Preferences utilisateur, cookies et liens memorises.
 */

/**
 * Lit, valide et memorise le theme choisi par l'utilisateur.
 *
 * @return string Theme actif : "day" ou "night".
 *
 * @ingroup preferences
 */
function gerer_theme(): string
{
	$theme = "day";
	$themesValides = ["day", "night"];

	if (isset($_GET["theme"])) {
		if (in_array($_GET["theme"], $themesValides, true)) {
			$theme = $_GET["theme"];
			setcookie("theme", $theme, time() + PM_COOKIE_DURATION, chemin_cookie());
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
/**
 * Retourne le libelle francais d'un theme.
 *
 * @param string $theme Code du theme.
 * @return string Libelle lisible par l'utilisateur.
 *
 * @ingroup preferences
 */
function nom_theme(string $theme): string
{
	if ($theme === "night") {
		return "nuit";
	}

	return "jour";
}
/**
 * Construit l'URL permettant de basculer entre le mode jour et le mode nuit.
 *
 * @param string $theme Theme actuellement actif.
 * @return string URL de la page courante avec le theme inverse.
 *
 * @ingroup preferences
 */
function lien_bascule_theme(string $theme): string
{
	$themeCible = $theme === "night" ? "day" : "night";
	$parametres = $_GET;
	$parametres["theme"] = $themeCible;
	$requete = http_build_query($parametres);
	$nomScript = basename($_SERVER["PHP_SELF"] ?? "index.php");

	if ($requete === "") {
		return $nomScript;
	}

	return $nomScript . "?" . $requete;
}
/**
 * Lit, valide et memorise la langue d'affichage.
 *
 * @return string Langue active : "fr" ou "en".
 *
 * @ingroup preferences
 */
function gerer_langue(): string
{
	$langue = "fr";
	$languesValides = ["fr", "en"];

	if (isset($_GET["lang"])) {
		if (in_array($_GET["lang"], $languesValides, true)) {
			$langue = $_GET["lang"];
			setcookie("lang", $langue, time() + PM_COOKIE_DURATION, chemin_cookie());
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
/**
 * Construit l'URL permettant de basculer entre francais et anglais.
 *
 * @param string $langue Langue actuellement active.
 * @return string URL de la page courante avec la langue inverse.
 *
 * @ingroup preferences
 */
function lien_bascule_langue(string $langue): string
{
	$parametres = $_GET;
	$parametres["lang"] = $langue === "en" ? "fr" : "en";
	$requete = http_build_query($parametres);
	$nomScript = basename($_SERVER["PHP_SELF"] ?? "index.php");

	if ($requete === "") {
		return $nomScript;
	}

	return $nomScript . "?" . $requete;
}
/**
 * Retourne le texte affiche dans le bouton de changement de langue.
 *
 * @param string $langue Langue actuellement active.
 * @return string Langue cible a afficher.
 *
 * @ingroup preferences
 */
function libelle_bascule_langue(string $langue): string
{
	return $langue === "en" ? "Français" : "English";
}
/**
 * Memorise la derniere recherche simple dans un cookie.
 *
 * @param string $type Type de recherche : "ville" ou "departement".
 * @param string $code Code de la ville ou du departement.
 * @return void
 */
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
		setcookie("last_search", $valeur, time() + PM_COOKIE_DURATION, chemin_cookie());
		$_COOKIE["last_search"] = $valeur;
	}
}
/**
 * Lit et valide le cookie de derniere recherche.
 *
 * @return array Derniere recherche valide ou tableau vide.
 */
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
/**
 * Nettoie les parametres de recherche avant de les stocker ou reutiliser.
 *
 * @param array $parametres Parametres GET ou parametres issus du cookie.
 * @return array Parametres normalises avec valeurs par defaut.
 */
function normaliser_parametres_recherche(array $parametres): array
{
	$resultat = [
		"region" => isset($parametres["region"]) ? (string) $parametres["region"] : "",
		"department" => isset($parametres["department"]) ? (string) $parametres["department"] : "",
		"city" => isset($parametres["city"]) ? (string) $parametres["city"] : "",
		"fuel" => normaliser_carburants_selection($parametres["fuel"] ?? []),
		"view" => ($parametres["view"] ?? "summary") === "detailed" ? "detailed" : "summary",
		"sort" => in_array(($parametres["sort"] ?? "price"), ["price", "price_desc", "distance", "name"], true) ? (string) $parametres["sort"] : "price",
		"geo_radius" => normaliser_rayon_geo((int) ($parametres["geo_radius"] ?? PM_DEFAULT_RADIUS)),
	];

	if (isset($parametres["department_mode"])) {
		$resultat["department_mode"] = "1";
	}

	if (isset($parametres["use_geo"])) {
		$resultat["use_geo"] = "1";
	}

	return $resultat;
}
/**
 * Memorise les criteres complets de la derniere recherche.
 *
 * @param array $parametres Parametres de recherche courants.
 * @return void
 *
 * @ingroup preferences
 */
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
		setcookie("last_search_params", $valeur, time() + PM_COOKIE_DURATION, chemin_cookie());
		$_COOKIE["last_search_params"] = $valeur;
	}
}
/**
 * Lit les criteres de la derniere recherche complete.
 *
 * @return array Parametres normalises ou tableau vide.
 *
 * @ingroup preferences
 */
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
/**
 * Supprime le cookie qui stocke les criteres de recherche.
 *
 * @return void
 */
function effacer_parametres_derniere_recherche(): void
{
	setcookie("last_search_params", "", time() - 3600, chemin_cookie());
	unset($_COOKIE["last_search_params"]);
}
/**
 * Retourne le lien de navigation vers la recherche memorisee.
 *
 * @return string URL de recherche, ou recherche manuelle par defaut.
 *
 * @ingroup preferences
 */
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
/**
 * Retourne le lien de navigation vers les resultats memorises.
 *
 * @return string URL de resultats.
 *
 * @ingroup preferences
 */
function lien_resultats_memorises(): string
{
	$parametres = lire_parametres_derniere_recherche();

	if ($parametres === []) {
		return "resultats.php";
	}

	return "resultats.php?" . http_build_query($parametres) . "#resultats";
}
/**
 * Memorise la derniere ville consultee et met a jour la derniere recherche.
 *
 * @param string $codeVille Code de la ville consultee.
 * @return void
 *
 * @ingroup preferences
 */
function enregistrer_derniere_ville(string $codeVille): void
{
	if ($codeVille !== "") {
		setcookie("last_visited_city", $codeVille, time() + PM_COOKIE_DURATION, chemin_cookie());
		$_COOKIE["last_visited_city"] = $codeVille;
		enregistrer_derniere_recherche("ville", $codeVille);
	}
}
/**
 * Lit le code de la derniere ville consultee.
 *
 * @return string Code ville ou chaine vide.
 */
function lire_derniere_ville(): string
{
	if (isset($_COOKIE["last_visited_city"])) {
		return (string) $_COOKIE["last_visited_city"];
	}

	return "";
}
