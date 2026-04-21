<?php
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$stats = calculer_statistiques();
$maxCount = $stats['top_cities'] === [] ? 1 : max($stats['top_cities']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Statistiques - Plein Malin</title>
	<meta name="description" content="Page statistiques de Plein Malin.">
	<link rel="stylesheet" href="style.css">
	<link rel="icon" href="image/favicon.ico" type="image/x-icon">
</head>
<body class="theme-<?= texte_securise($theme) ?>">
	<header class="site-header compact-header">
		<div class="brand-row">
			<a class="brand-link" href="index.php">
				<img class="logo" src="image/<?= $theme === 'night' ? 'logoblanc.svg' : 'logonoir.svg' ?>" alt="Logo Plein Malin">
			</a>
			<div class="theme-switch">
				<a href="?theme=day" class="theme-link">
					<img src="image/theme-day.svg" alt="">
					<span>Jour</span>
				</a>
				<a href="?theme=night" class="theme-link">
					<img src="image/theme-night.svg" alt="">
					<span>Nuit</span>
				</a>
			</div>
		</div>
		<nav class="main-nav">
			<a href="index.php">Recherche</a>
			<a href="stats.php">Statistiques</a>
			<a href="tech.php">Page tech</a>
		</nav>
	</header>

	<main class="page-shell">
		<section class="panel">
			<p class="eyebrow">Rubrique statistiques</p>
			<h1>Consultations Plein Malin</h1>
			<p class="lead">
				Le total visiteurs reste une approximation pragmatique basee sur le nombre de
				hashes IP distincts stockes dans l'historique CSV.
			</p>
			<div class="stats-inline">
				<div class="stat-chip">
					<strong><?= texte_securise((string) $stats['consultation_count']) ?></strong>
					<span>consultations</span>
				</div>
				<div class="stat-chip">
					<strong><?= texte_securise((string) $stats['total_visitors']) ?></strong>
					<span>visiteurs approx.</span>
				</div>
			</div>
		</section>

		<section class="panel">
			<h2>Top des villes consultees</h2>
			<?php if ($stats['top_cities'] === []): ?>
				<p class="empty-state">Aucune consultation enregistree pour le moment.</p>
			<?php else: ?>
				<div class="bar-chart">
					<?php foreach ($stats['top_cities'] as $city => $count): ?>
						<div class="bar-row">
							<span class="bar-label"><?= texte_securise($city) ?></span>
							<div class="bar-track">
								<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxCount) * 100))) ?>%"></div>
							</div>
							<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	</main>

	<footer class="site-footer">
		<div class="footer-links">
			<a href="index.php">Retour a la recherche</a>
			<a href="tech.php">Page tech</a>
		</div>
		<p>Statistiques generees a partir du CSV de consultations.</p>
	</footer>
</body>
</html>
