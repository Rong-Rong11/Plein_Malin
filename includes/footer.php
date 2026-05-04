<?php
/**
 * @file
 * @brief Pied de page HTML commun.
 *
 * Pied de page commun.
 *
 * Il centralise les liens secondaires du site.
 */
$textePiedPage = $textePiedPage ?? "Enzo Phung | Fatma-Zahra Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";
?>
<footer class="pied-site">
	<div class="bloc-nav-pied">
		<div class="liens-pied liens-pied-principaux">
			<a href="index.php"><?= texte_securise("Accueil") ?></a>
		</div>
		<div class="liens-pied liens-pied-infos">
			<a href="a-propos.php"><?= texte_securise("À propos") ?></a>
			<a href="aide.php"><?= texte_securise("Aide") ?></a>
			<a href="sources.php"><?= texte_securise("Sources des données") ?></a>
			<a href="confidentialite.php"><?= texte_securise("Confidentialité") ?></a>
			<a href="plan-site.php"><?= texte_securise("Plan du site") ?></a>
			<a href="tech.php"><?= texte_securise("Page tech") ?></a>
		</div>
	</div>
	<p><?= texte_securise($textePiedPage) ?></p>
</footer>
<a href="#top" class="retour-haut">
	<img src="image/back_top.png" alt="<?= texte_securise("Retour en haut") ?>" width="64" height="64"
		loading="lazy" decoding="async" />
</a>
</body>

</html>