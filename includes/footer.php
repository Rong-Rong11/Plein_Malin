<?php
/**
 * @file
 * @brief Pied de page HTML commun.
 *
 * Pied de page commun.
 *
 * Il centralise les liens secondaires et ferme le tampon de sortie ouvert dans
 * l'en-tete pour permettre la traduction globale de l'interface.
 */
$textePiedPage = $textePiedPage ?? "Enzo Phung | Fatma-Zahra Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";
$lienRecherchePiedPage = lien_recherche_memorisee();
$lienResultatsPiedPage = lien_resultats_memorises();
?>
<footer class="site-footer">
	<div class="footer-nav-block">
		<div class="footer-links footer-links-main">
			<a href="index.php">Accueil</a>
			<a href="<?= texte_securise($lienRecherchePiedPage) ?>">Recherche</a>
			<a href="<?= texte_securise($lienResultatsPiedPage) ?>">Résultats</a>
			<a href="stats.php">Statistiques</a>
		</div>
		<div class="footer-links footer-links-info">
			<a href="a-propos.php">À propos</a>
			<a href="aide.php">Aide</a>
			<a href="sources.php">Sources des données</a>
			<a href="confidentialite.php">Confidentialité</a>
			<a href="plan-site.php">Plan du site</a>
			<a href="tech.php">Page tech</a>
		</div>
	</div>
	<p><?= texte_securise($textePiedPage) ?></p>
</footer>
<a href="#top" class="back-top">
	<img src="image/back_top_small.png" alt="Retour en haut" width="64" height="64" loading="lazy" decoding="async" />
</a>
</body>

</html>
<?php
if (ob_get_level() > 0) {
	ob_end_flush();
}
?>
