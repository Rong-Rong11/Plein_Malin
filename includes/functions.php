<?php

/**
 * @file
 * @brief Charge les modules communs du site Plein Malin.
 *
 * Ce fichier reste le point d'entree unique utilise par les pages PHP.
 * Les fonctions sont separees par theme dans les fichiers du dossier includes.
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

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/security.php";
require_once __DIR__ . "/translations.php";
require_once __DIR__ . "/data.php";
require_once __DIR__ . "/format.php";
require_once __DIR__ . "/search.php";
require_once __DIR__ . "/preferences.php";
require_once __DIR__ . "/stats.php";
