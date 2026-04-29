<?php
$footerText = $footerText ?? "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";
$lienRechercheFooter = lien_recherche_memorisee();
$lienResultatsFooter = lien_resultats_memorises();
?>
		<footer class="site-footer">
			<div class="footer-links">
				<a href="index.php">Accueil</a>
				<a href="<?= texte_securise($lienRechercheFooter) ?>">Recherche</a>
					<a href="<?= texte_securise($lienResultatsFooter) ?>">Résultats</a>
				<a href="stats.php">Statistiques</a>
				<a href="tech.php">Page tech</a>
			</div>
			<p><?= texte_securise($footerText) ?></p>
		</footer>
		<a href="#top" class="back-top">
			<img src="image/back_top.png" alt="Retour en haut">
		</a>
	</body>
	</html>
<?php
if (ob_get_level() > 0) {
	ob_end_flush();
}
?>
