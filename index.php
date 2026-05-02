<?php
/**
 * @file
 * @brief Page d'accueil du site Plein Malin.
 *
 * @mainpage Documentation du projet Plein Malin
 *
 * Plein Malin est un site PHP permettant de rechercher des stations-service
 * en France et de comparer les prix des carburants.
 *
 * @section auteurs Auteurs
 * - Enzo Phung
 * - Fatma-Zahra Baarir
 *
 * @section fonctionnalites Fonctionnalites principales
 * - Recherche manuelle par region, departement et ville.
 * - Recherche autour d'une position approximative par adresse IP.
 * - Filtrage par carburant et tri des resultats.
 * - Consultation des stations, prix, distances et liens cartographiques.
 * - Statistiques basees sur les consultations et les visites.
 * - Theme jour/nuit et interface francais/anglais.
 *
 * @section fichiers Fichiers importants
 * - includes/functions.php : fonctions communes et logique metier.
 * - recherche.php : formulaire et choix des criteres.
 * - resultats.php : traitement de la recherche et affichage des stations.
 * - stats.php : statistiques et tendances de prix.
 * - tech.php : demonstration des formats JSON, XML, CSV et cache.
 *
 * Page d'accueil.
 *
 * Elle prepare les liens de reprise de recherche a partir des cookies, puis
 * presente le fonctionnement general du site.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$derniereVille = null;
$dernierDepartement = null;
$codeDerniereVille = lire_derniere_ville();
$lienDerniereRecherche = "";
$libelleDerniereRecherche = "";
$typeDerniereRecherche = "";
$dateDerniereRecherche = "";
$derniereRecherche = lire_derniere_recherche();

// La date du cookie est convertie en format francais uniquement si elle est valide.
if (isset($derniereRecherche["date"]) && is_string($derniereRecherche["date"])) {
	$dateCookie = strtotime($derniereRecherche["date"]);

	if ($dateCookie !== false) {
		$dateDerniereRecherche = date("d/m/Y", $dateCookie);
	}
}

// Priorite a la derniere recherche complete, puis a l'ancien cookie de derniere ville.
if (($derniereRecherche["type"] ?? "") === "departement") {
	$dernierDepartement = trouver_departement((string) $derniereRecherche["code"]);

	if ($dernierDepartement !== null) {
		$lienDerniereRecherche = lien_resultats_departement($dernierDepartement["department_code"]);
		$libelleDerniereRecherche = $dernierDepartement["department_name"] . " (" . $dernierDepartement["department_code"] . ")";
		$typeDerniereRecherche = "Dernier département consulté";
	}
} elseif (($derniereRecherche["type"] ?? "") === "ville") {
	$codeDerniereVille = (string) $derniereRecherche["code"];
}

if ($typeDerniereRecherche === "" && $codeDerniereVille !== "") {
	$derniereVille = trouver_ville($codeDerniereVille);

	if ($derniereVille !== null) {
		$lienDerniereRecherche = lien_resultats_ville($derniereVille);
		$libelleDerniereRecherche = $derniereVille["city_name"] . " (" . $derniereVille["postal_code"] . ")";
		$typeDerniereRecherche = "Dernière ville consultée";
	}
}

$titrePage = "Plein Malin";
$descriptionPage = "Comparer les prix des carburants et trouver une station en France.";
$pageActive = "index";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell home-page">
	<section class="hero home-hero">
		<div class="hero-copy">
			<p class="eyebrow">Prix des carburants</p>
			<h1>Trouvez une station plus facilement</h1>
			<div class="home-hero-content">
				<div class="home-hero-text">
					<p class="lead">
						Plein Malin aide à comparer rapidement les prix du carburant en France.
						Le site propose une recherche manuelle par région, département et ville,
						mais aussi une recherche autour de votre position approximative.
					</p>
					<p class="small-note">
						L'objectif est de garder une interface simple : choisir un périmètre,
						sélectionner un ou plusieurs carburants, puis consulter des résultats
						lisibles sans surcharge inutile.
					</p>
					<div class="form-actions">
						<a class="cta-link" href="recherche.php?search_mode=manual">Rechercher une station</a>
					</div>
				</div>
				<div class="home-hero-visual">
					<img class="home-hero-image home-hero-image-light" src="image/image-accueil(light).png" alt="Illustration de recherche de carburant Plein Malin" width="888" height="898" decoding="async" fetchpriority="high" />
					<img class="home-hero-image home-hero-image-dark" src="image/image-accueil(dark).png" alt="Illustration de recherche de carburant Plein Malin" width="1604" height="1616" decoding="async" fetchpriority="high" />
				</div>
			</div>

			<?php if ($typeDerniereRecherche !== "") { ?>
				<div class="home-last-search-inline">
					<h2>Dernière recherche</h2>
					<p>
						<?= texte_securise($typeDerniereRecherche) ?> :
						<strong><?= texte_securise($libelleDerniereRecherche) ?></strong>
					</p>
					<?php if ($dateDerniereRecherche !== "") { ?>
						<p class="small-note">Dernière recherche le <?= texte_securise($dateDerniereRecherche) ?></p>
					<?php } ?>
					<div class="form-actions">
						<a class="cta-link" href="<?= texte_securise($lienDerniereRecherche) ?>">Reprendre cette recherche</a>
					</div>
				</div>
			<?php } ?>
		</div>
	</section>

	<section class="panel home-intro">
		<h2>Comment fonctionne le site</h2>
		<p>
			La recherche principale suit un parcours guidé : vous choisissez d'abord
			une région, puis un département, puis une ville. Ce fonctionnement permet
			d'encadrer la recherche et de garder une navigation compréhensible.
		</p>
		<p>
			Si vous voulez aller plus vite, le mode <strong>Autour de moi</strong>
			utilise une estimation de position basée sur l'adresse IP pour proposer
			des stations proches dans un rayon choisi.
		</p>
		<p>
			Les résultats affichent les stations trouvées, les prix disponibles pour
			les carburants sélectionnés et un accès direct à une carte quand les
			coordonnées sont connues.
		</p>

		<h2>Informations utiles</h2>
		<div class="home-columns">
			<article>
				<h3>Recherche guidée</h3>
				<p>
					La recherche suit l'ordre région, département puis ville pour rester
					simple et éviter les choix incohérents.
				</p>
			</article>
			<article>
				<h3>Résultats lisibles</h3>
				<p>
					Chaque station affiche son adresse, ses prix et les informations
					utiles pour comparer rapidement plusieurs points de vente.
				</p>
			</article>
			<article>
				<h3>Statistiques</h3>
				<p>
					Une page dédiée résume les recherches effectuées, les visites et les
					tendances de prix observées dans les données disponibles.
				</p>
			</article>
		</div>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
