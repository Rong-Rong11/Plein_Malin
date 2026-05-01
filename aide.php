<?php
/**
 * Page d'aide.
 *
 * Elle explique le parcours de recherche, la lecture des resultats et les
 * questions frequentes du site.
 */
require __DIR__ . "/includes/functions.php";

preparer_dossiers_et_fichiers();
$theme = gerer_theme();

$pageTitle = "Aide - Plein Malin";
$pageDescription = "Mode d'emploi et questions fréquentes du site Plein Malin.";
$activePage = "aide";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Aide et mode d'emploi.";

require __DIR__ . "/includes/header.php";
?>
<main class="page-shell info-page">
	<section class="panel">
		<p class="eyebrow">Aide</p>
		<h1>Mode d'emploi</h1>
		<p class="lead">
			Cette page explique rapidement comment utiliser Plein Malin et comment lire les résultats affichés.
		</p>
		<section class="info-block">
			<h2>Faire une recherche</h2>
		<ol class="plain-list">
			<li>Cliquez sur une région dans la carte interactive.</li>
			<li>Choisissez un département dans la liste.</li>
			<li>Choisissez une ville ou cochez le mode tout le département.</li>
			<li>Sélectionnez un ou plusieurs carburants.</li>
			<li>Lancez la recherche pour afficher les stations.</li>
		</ol>
		</section>

		<section class="info-block">
			<h2>Autour de moi</h2>
		<p class="small-note">
			Le bouton Autour de moi utilise une position estimée à partir de l'adresse IP.
			Cette localisation est pratique pour une recherche rapide, mais elle reste approximative.
		</p>
		</section>

		<section class="info-block">
			<h2>Lire les résultats</h2>
		<ul class="plain-list">
			<li>Le prix moyen résume les prix trouvés pour la recherche actuelle.</li>
			<li>Le meilleur prix permet d'aller directement à la station correspondante.</li>
			<li>Chaque station affiche son adresse, sa distance et les prix des carburants sélectionnés.</li>
			<li>Le lien Voir sur une carte ouvre la station dans OpenStreetMap quand les coordonnées sont disponibles.</li>
		</ul>
		</section>

		<section class="info-block">
			<h2>Questions fréquentes</h2>
		<h3>Pourquoi la position est approximative ?</h3>
		<p class="small-note">La position vient de l'adresse IP. Elle peut pointer vers une ville proche plutôt que vers l'adresse exacte.</p>

		<h3>Pourquoi certains carburants n'apparaissent pas ?</h3>
		<p class="small-note">Une station ne propose pas toujours tous les carburants, ou l'API ne fournit pas toujours un prix à jour pour chaque carburant.</p>

		<h3>D'où viennent les prix ?</h3>
		<p class="small-note">Les prix viennent de l'API officielle des prix des carburants et sont traités côté serveur en PHP.</p>
		</section>
	</section>
</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
