<?php
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$stats = calculer_statistiques();
$maxCount = $stats['top_cities'] === [] ? 1 : max($stats['top_cities']);

$pageTitle = "Statistiques - Plein Malin";
$pageDescription = "Page statistiques de Plein Malin.";
$activePage = "stats";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Statistiques generees a partir du CSV de consultations.";

require __DIR__ . "/includes/header.php";
?>
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

<?php require __DIR__ . "/includes/footer.php"; ?>
