<?php

/**
 * @file
 * @brief Fonctions de formatage pour l'affichage.
 */

/**
 * Formate un prix carburant en euros par litre.
 *
 * @param float|null $prix Prix numerique ou null.
 * @return string Prix formate pour l'interface.
 */
function formater_prix(?float $prix): string
{
	if ($prix === null) {
		return "Indisponible";
	}

	return number_format($prix, 3, ",", " ") . " EUR/L";
}
/**
 * Convertit une date technique en date lisible.
 *
 * @param string|null $date Date ISO ou chaine vide.
 * @return string Date au format francais, ou chaine vide si invalide.
 */
function formater_date_heure(?string $date): string
{
	if ($date === null || trim($date) === "") {
		return "";
	}

	$horodatage = strtotime($date);
	if ($horodatage === false) {
		return "";
	}

	return date("d/m/Y H:i", $horodatage);
}
