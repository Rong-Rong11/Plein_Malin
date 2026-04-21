<?php
$footerText = $footerText ?? "Enzo Phung | Fatma-Zhara Baarir | CY Cergy Paris Universite | Projet Web 2025-2026";
?>
	<footer class="site-footer">
		<div class="footer-links">
			<a href="index.php">Accueil</a>
			<a href="stats.php">Statistiques</a>
			<a href="tech.php">Page tech</a>
			<a href="#top" class="back-top">
				<img src="image/back_top.png" alt="Retour en haut">
			</a>
		</div>
		<p><?= texte_securise($footerText) ?></p>
	</footer>
</body>
</html>
