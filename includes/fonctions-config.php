<?php

/**
 * @file
 * @brief Configuration generale et constantes du projet Plein Malin.
 *
 * Ce fichier centralise les chemins, durees de cache, valeurs par defaut et
 * limites fonctionnelles reutilisees par les pages et fonctions du site.
 */

/**
 * Constantes de chemins utilisees par tout le site.
 *
 * Elles centralisent les dossiers de cache, de donnees et de stockage pour
 * eviter de dupliquer les chemins dans chaque page PHP.
 */
define("PM_CACHE_DIR", __DIR__ . "/../cache");
define("PM_DATA_DIR", __DIR__ . "/../data");
define("PM_STORAGE_DIR", __DIR__ . "/../storage");
define("PM_CITIES_INDEX_FILE", __DIR__ . "/../data/villes_index.csv");

define("PM_COOKIE_DURATION", 30 * 24 * 3600);
define("PM_API_CACHE_DURATION", 21600);
define("PM_FUEL_TRENDS_CACHE_DURATION", 24 * 3600);
define("PM_DEFAULT_FUEL", "Gazole");
define("PM_DEFAULT_RADIUS", 10);
define("PM_MAX_STATIONS_DISPLAYED", 15);
define("PM_FUEL_API_LIMIT", 100);
define("PM_GEO_RADII", [5, 10, 15, 20, 30]);
define("PM_TREND_FUELS", ["Gazole", "SP95", "SP98", "E10"]);
