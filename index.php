<?php
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();

$theme = gerer_theme();
$derniereVille = null;
$codeDerniereVille = lire_derniere_ville();
$lienDerniereRecherche = "";

if ($codeDerniereVille !== "") {
	$derniereVille = trouver_ville($codeDerniereVille);

	if ($derniereVille !== null) {
		$lienDerniereRecherche = lien_resultats_ville($derniereVille);
	}
}

$pageTitle = "Plein Malin";
$pageDescription = "Comparer les prix des carburants et trouver une station en France.";
$activePage = "index";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell">
	<section class="hero">
		<div class="hero-copy">
			<p class="eyebrow">Prix des carburants</p>
			<h1>Trouvez une station plus facilement</h1>
			<p class="lead">
				Plein Malin permet de choisir une region, un departement puis une ville
				pour consulter les stations-service et comparer les prix.
			</p>
			<div class="form-actions">
				<a class="cta-link" href="recherche.php#recherche">Rechercher une station</a>
			</div>
		</div>

		<div class="panel">
			<h2>Ce que fait le site</h2>
			<ul class="plain-list">
				<li>Recherche par region, departement et ville</li>
				<li>Affichage simple des stations et des prix</li>
				<li>Statistiques a partir des consultations enregistrees</li>
			</ul>
		</div>
	</section>

	<?php if ($derniereVille !== null): ?>
		<section class="panel">
			<h2>Derniere recherche</h2>
			<p class="lead">
				Derniere ville consultee : <strong><?= texte_securise($derniereVille["city_name"]) ?></strong>
				(<?= texte_securise($derniereVille["postal_code"]) ?>)
			</p>
			<div class="form-actions">
				<a class="cta-link" href="<?= texte_securise($lienDerniereRecherche) ?>">Reprendre cette recherche</a>
			</div>
		</section>
	<?php endif; ?>

	<section class="panel-grid">
		<article class="panel">
			<h2>Recherche guidee</h2>
			<p class="small-note">
				La recherche suit l'ordre region, departement puis ville pour rester simple.
			</p>
		</article>

		<article class="panel">
			<h2>Resultats lisibles</h2>
			<p class="small-note">
				Chaque station affiche son adresse, ses prix et ses informations utiles.
			</p>
		</article>

		<article class="panel">
			<h2>Statistiques</h2>
			<p class="small-note">
				Une page dediee resume les villes les plus consultees et le nombre de visites.
			</p>
		</article>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>