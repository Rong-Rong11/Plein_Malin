# Plein Malin

## Auteurs

- Enzo Phung
- Fatma-Zahra Baarir

## Contexte

Projet de developpement web realise dans le cadre de la Licence 2, semestre 4, a CY Cergy Paris Universite.

## Description du site

Plein Malin est un site web qui permet de rechercher des stations-service en France et de comparer les prix des carburants. L'utilisateur peut effectuer une recherche manuelle par region, departement et ville, ou lancer une recherche autour de sa position approximative.

Le site propose aussi une page de resultats, des statistiques de consultation, des pages d'information, une page technique et un plan du site.

## URL du site

- URL publique du site : a completer apres mise en ligne
- URL locale de developpement : `http://localhost/Plein_Malin/`
- Page d'accueil : `index.php`

## Pages principales

- `index.php` : page d'accueil
- `recherche.php` : formulaire de recherche
- `resultats.php` : affichage des stations et des prix
- `stats.php` : statistiques des consultations
- `sources.php` : sources des donnees
- `a-propos.php` : presentation du projet
- `aide.php` : aide d'utilisation
- `confidentialite.php` : informations sur les cookies et les donnees conservees
- `plan-site.php` : plan du site
- `tech.php` : page technique

## Technologies utilisees

- PHP
- HTML / XHTML
- CSS
- XML
- JSON
- CSV

## Donnees utilisees

- Donnees CSV pour les regions, departements et villes
- API officielle des prix des carburants
- Geolocalisation approximative par adresse IP
- Fichiers CSV locaux pour les consultations et les visites
- Cache local pour certaines donnees externes

## Fonctionnalites principales

- Recherche de stations par region, departement et ville
- Recherche dans tout un departement
- Recherche autour de la position approximative de l'utilisateur
- Filtrage par carburant
- Tri des resultats par prix, distance ou nom
- Consultation des prix et des informations des stations
- Lien vers une carte OpenStreetMap pour les stations avec coordonnees
- Statistiques sur les recherches et les visites
- Mode jour / nuit
- Interface en francais et en anglais

## Validation

Les fichiers XML generes dans le dossier `xml/` sont prevus pour la validation XML. Les fichiers PHP doivent etre executes par le serveur avant validation : il faut valider le code source genere par le navigateur, pas le code PHP brut.
