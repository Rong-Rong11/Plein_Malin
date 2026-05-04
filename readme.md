# Plein Malin

## Auteurs

- Enzo Phung
- Fatma-Zahra Baarir

## Contexte

Projet de developpement web realise dans le cadre de la Licence 2, semestre 4, a CY Cergy Paris Universite.

## Description

Plein Malin est un site web PHP qui permet de rechercher des stations-service en France et de comparer les prix des carburants. L'utilisateur peut faire une recherche manuelle par region, departement et ville, ou lancer une recherche autour de sa position approximative.

Le site propose aussi une page de resultats, des statistiques de consultation, des pages d'information, une page technique et un plan du site.

## URL du site

- URL publique du site : a completer apres mise en ligne
- URL locale de developpement : `http://localhost/Plein_Malin/`
- Page d'accueil : `index.php`

## Pages principales

- `index.php` : page d'accueil
- `recherche.php` : formulaire de recherche
- `resultats.php` : affichage des stations et des prix
- `stats.php` : statistiques des consultations et tendances de prix
- `sources.php` : sources des donnees
- `a-propos.php` : presentation du projet
- `aide.php` : aide d'utilisation
- `confidentialite.php` : informations sur les cookies et les donnees conservees
- `plan-site.php` : plan du site
- `tech.php` : page technique

## Architecture PHP

Les pages du site chargent `includes/functions.php`, qui sert de point d'entree commun et inclut les modules suivants :

- `includes/fonctions-config.php` : constantes, chemins, durees de cache et valeurs par defaut
- `includes/fonctions-securite.php` : securisation HTML, traduction directe des textes affiches, initialisation des dossiers et chemin des cookies
- `includes/fonctions-preferences.php` : theme, langue, cookies de derniere recherche et liens memorises
- `includes/fonctions-traductions.php` : tables de traduction francais / anglais et traduction texte par texte
- `includes/fonctions-donnees.php` : lecture des fichiers CSV locaux et extraction du flux XML officiel
- `includes/fonctions-recherche.php` : recherche de stations, API carburants, geolocalisation IP avec `ipinfo.io` et distances
- `includes/fonctions-stats.php` : enregistrement des consultations, statistiques et tendances de prix
- `includes/fonctions-format.php` : formatage des prix et des dates
- `includes/header.php` : en-tete commun
- `includes/footer.php` : pied de page commun

## Feuilles de style

- `style.css` : style principal du site et mode jour
- `style-alt.css` : surcharge du mode nuit, chargee seulement quand le theme actif est `night`

Les classes CSS ont ete renommees avec des noms plus lisibles en francais, par exemple `page-conteneur`, `panneau`, `formulaire-recherche`, `bloc-champ`, `choix-carburant`, `bouton-secondaire`, `message-vide`, `carte-station` et `carte-stats`.

## Constantes importantes

Les principales constantes sont definies dans `includes/fonctions-config.php` :

- `PM_DEFAULT_FUEL` : carburant utilise par defaut, `Gazole`
- `PM_DEFAULT_RADIUS` : rayon de recherche par defaut, `10` km
- `PM_MAX_STATIONS_DISPLAYED` : nombre maximum de stations affichees, `15`
- `PM_COOKIE_DURATION` : duree de conservation des cookies, 30 jours
- `PM_API_CACHE_DURATION` : duree du cache API, 6 heures
- `PM_TREND_FUELS` : carburants utilises pour les tendances annuelles

## Technologies utilisees

- PHP
- HTML / XHTML
- CSS
- XML
- JSON
- CSV
- Doxygen

## Donnees utilisees

- Fichiers CSV locaux pour les regions, departements et villes
- API officielle des prix des carburants
- Geolocalisation approximative par adresse IP avec `ipinfo.io`
- En developpement local, les adresses `127.0.0.1` et `::1` ne declenchent pas d'appel externe de geolocalisation
- Archive annuelle XML officielle `donnees.roulez-eco.fr` pour les tendances de prix et la demonstration XML de la page technique
- Fichiers CSV locaux pour les consultations et les visites
- Cache local pour limiter les appels aux services externes

## Fonctionnalites principales

- Recherche de stations par region, departement et ville
- Recherche dans tout un departement
- Recherche autour de la position approximative de l'utilisateur
- Filtrage par carburant
- Tri des resultats par prix, distance ou nom
- Consultation des prix et des informations des stations
- Lien vers OpenStreetMap pour les stations avec coordonnees
- Statistiques sur les recherches et les visites
- Tendance annuelle des prix des carburants
- Mode jour / nuit
- Interface en francais et en anglais

## Page technique

La page `tech.php` conserve les exemples demandes pour la validation technique :

- appel JSON a l'API Ghibli avec choix aleatoire d'un film
- affichage du titre, du titre original, de l'annee, de la description et de deux images avec legendes
- geolocalisation approximative cote serveur via un flux JSON `ipinfo.io`
- lecture du flux XML officiel des carburants depuis l'archive annuelle `donnees.roulez-eco.fr`
- affichage resume des derniers prix par carburant pour eviter de lister tout l'historique annuel

## Traduction de l'interface

La traduction ne repose pas sur un tampon de sortie PHP. Les textes visibles sont traduits au moment de l'affichage avec `texte_securise()`, qui appelle la table de traduction lorsque la langue active est l'anglais.

Consequence importante : un texte ecrit directement dans le HTML ne sera pas traduit automatiquement. Pour qu'un texte soit traduisible, il doit etre affiche avec une forme comme :

```php
<?= texte_securise("Rechercher une station") ?>
```

Les textes qui contiennent volontairement du HTML, par exemple des balises `<code>`, peuvent utiliser `traduire_texte()` quand il ne faut pas echapper les balises.

## Documentation Doxygen

Le projet contient un `Doxyfile`.

Pour generer la documentation :

```bash
doxygen Doxyfile
```

La documentation HTML est generee dans :

```text
doc/index.html
```

La generation LaTeX est desactivee avec :

```text
GENERATE_LATEX = NO
```

## Validation XML

Les fichiers du dossier `xml/` correspondent a des sorties HTML/XML generees pour la validation.

Important : les fichiers PHP bruts ne doivent pas etre colles directement dans un validateur XML, car ils contiennent du code PHP comme `<?= ... ?>`. Il faut valider le code source genere par le navigateur apres execution par le serveur.

Commande utile :

```bash
xmllint --noout xml/*.xml
```

Pour verifier une page PHP, il faut d'abord la servir avec PHP, ouvrir la page dans le navigateur, puis utiliser le code source genere. Exemple avec le serveur local :

```bash
php -S 127.0.0.1:8080
```

Puis ouvrir `http://127.0.0.1:8080/index.php`, afficher le code source genere dans le navigateur, l'enregistrer dans `xml/index.xml`, puis lancer :

```bash
xmllint --noout xml/index.xml
```

## Commandes de verification

Verifier la syntaxe PHP :

```bash
find . -path './doc' -prune -o -name '*.php' -print | while read fichier; do php -l "$fichier" || exit 1; done
```

Verifier les XML sauvegardes :

```bash
xmllint --noout xml/*.xml
```

Generer la documentation :

```bash
doxygen Doxyfile
```

Lancer un serveur PHP local :

```bash
php -S 127.0.0.1:8080
```

Puis ouvrir :

```text
http://127.0.0.1:8080/index.php
```
