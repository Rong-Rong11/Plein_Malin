<?php
/**
 * Page de statistiques.
 *
 * Elle lit les fichiers CSV de suivi et calcule aussi les tendances annuelles
 * depuis l'archive XML officielle des prix carburants.
 */
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$stats = calculer_statistiques();
$maxCityCount = $stats['top_cities'] === [] ? 1 : max($stats['top_cities']);
$maxDepartmentCount = $stats['top_departments'] === [] ? 1 : max($stats['top_departments']);
$maxRegionCount = $stats['top_regions'] === [] ? 1 : max($stats['top_regions']);
$maxFuelCount = $stats['top_fuels'] === [] ? 1 : max($stats['top_fuels']);
$maxModeCount = $stats['top_modes'] === [] ? 1 : max($stats['top_modes']);
$fuelTrends = lire_tendances_prix_officielles(null, ["Gazole", "SP95", "SP98", "E10"]);

$pageTitle = "Statistiques - Plein Malin";
$pageDescription = "Page statistiques de Plein Malin.";
$activePage = "stats";
$footerText = "Enzo Phung | Fatma-Zhara Baarir | Statistiques générées à partir du CSV de consultations.";

require __DIR__ . "/includes/header.php";
?>
	<main class="page-shell">
		<section class="panel">
			<p class="eyebrow">Rubrique statistiques</p>
			<h1>Consultations Plein Malin</h1>
				<p class="lead">
					Les recherches correspondent aux consultations avec critères. Les visites comptent aussi
					les pages vues sans lancement de recherche.
				</p>
				<p class="small-note">
					Une recherche est enregistrée quand une page de résultats est produite ; une visite de page
					est comptée à chaque affichage d'une page du site, même sans recherche.
				</p>
				<div class="stats-inline">
					<div class="stat-chip">
						<strong><?= texte_securise((string) $stats['consultation_count']) ?></strong>
						<span>recherches</span>
					</div>
					<div class="stat-chip">
						<strong><?= texte_securise((string) $stats['page_visit_count']) ?></strong>
						<span>visites de pages</span>
					</div>
					<div class="stat-chip">
						<strong><?= texte_securise((string) $stats['page_visitor_count']) ?></strong>
						<span>visiteurs approx.</span>
					</div>
				</div>
		</section>

		<section class="panel">
			<h2>Graphiques des consultations</h2>
			<div class="stats-grid">
				<article class="stats-card">
					<h3>Top des villes consultées</h3>
					<?php if ($stats['top_cities'] === []) { ?>
						<p class="empty-state">Aucune consultation enregistrée pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($stats['top_cities'] as $city => $count) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($city) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxCityCount) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Top des départements consultés</h3>
					<?php if ($stats['top_departments'] === []) { ?>
						<p class="empty-state">Aucun département enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($stats['top_departments'] as $department => $count) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($department) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxDepartmentCount) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Top des régions consultées</h3>
					<?php if ($stats['top_regions'] === []) { ?>
						<p class="empty-state">Aucune région enregistrée pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($stats['top_regions'] as $region => $count) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($region) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxRegionCount) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Carburants les plus recherchés</h3>
					<?php if ($stats['top_fuels'] === []) { ?>
						<p class="empty-state">Aucun carburant enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($stats['top_fuels'] as $fuel => $count) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($fuel) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxFuelCount) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card stats-card-wide">
					<h3>Recherches par mode</h3>
					<?php if ($stats['top_modes'] === []) { ?>
						<p class="empty-state">Aucun mode de recherche enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($stats['top_modes'] as $mode => $count) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($mode) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($count / $maxModeCount) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $count) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>
			</div>
		</section>

				<section class="panel">
					<h2>Tendance annuelle des prix</h2>
					<p class="small-note">
					Moyennes mensuelles calculées côté serveur depuis l'archive annuelle officielle XML
						<?= texte_securise((string) ($fuelTrends["year"] ?? date("Y"))) ?>.
					</p>
					<p class="small-note">
						Source officielle :
						<a href="<?= texte_securise($fuelTrends["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee") ?>">
							donnees.roulez-eco.fr
						</a>
						<?php if (formater_date_heure($fuelTrends["cached_at"] ?? "") !== "") { ?>
							- dernière mise à jour du cache le <?= texte_securise(formater_date_heure($fuelTrends["cached_at"])) ?>
						<?php } ?>
					</p>

					<?php if (($fuelTrends["fuels"] ?? []) === []) { ?>
					<p class="empty-state">Tendances indisponibles pour le moment.</p>
				<?php } else { ?>
					<div class="trend-grid">
						<?php foreach ($fuelTrends["fuels"] as $fuelName => $months) { ?>
							<article class="trend-group">
								<h3><?= texte_securise($fuelName) ?></h3>
								<?php if ($months === []) { ?>
								<p class="empty-state">Aucune donnée disponible.</p>
								<?php } else { ?>
									<?php
									$points = points_graphique_tendance($months);
									$graduationsPrix = graduations_prix_tendance($months);
									$graduationsMois = graduations_mois_tendance($months);
									$firstMonth = $months[0];
									$lastMonth = $months[count($months) - 1];
									?>
									<div class="line-chart">
										<svg viewBox="0 0 420 170" role="img" aria-label="Evolution <?= texte_securise($fuelName) ?>">
											<?php foreach ($graduationsPrix as $graduation) { ?>
												<line x1="<?= texte_securise((string) $graduation["x1"]) ?>" y1="<?= texte_securise((string) $graduation["y"]) ?>" x2="<?= texte_securise((string) $graduation["x2"]) ?>" y2="<?= texte_securise((string) $graduation["y"]) ?>" class="chart-grid"></line>
												<text x="2" y="<?= texte_securise((string) ((float) $graduation["y"] + 4)) ?>" class="chart-label">
													<?= texte_securise(number_format((float) $graduation["value"], 2, ",", " ")) ?>
												</text>
											<?php } ?>
											<line x1="16" y1="154" x2="404" y2="154" class="chart-axis"></line>
											<line x1="16" y1="16" x2="16" y2="154" class="chart-axis"></line>
											<?php if ($points !== "") { ?>
												<polyline points="<?= texte_securise($points) ?>" class="chart-line"></polyline>
												<?php foreach (explode(" ", $points) as $point) { ?>
													<?php [$x, $y] = explode(",", $point); ?>
													<circle cx="<?= texte_securise($x) ?>" cy="<?= texte_securise($y) ?>" r="4" class="chart-point"></circle>
												<?php } ?>
											<?php } ?>
											<?php foreach ($graduationsMois as $graduation) { ?>
												<text x="<?= texte_securise((string) $graduation["x"]) ?>" y="168" class="chart-label chart-month">
													<?= texte_securise($graduation["label"]) ?>
												</text>
											<?php } ?>
										</svg>
										<div class="chart-caption">
											<span><?= texte_securise($firstMonth["month"]) ?> : <?= texte_securise(number_format((float) $firstMonth["average_price"], 3, ",", " ")) ?> EUR/L</span>
											<span><?= texte_securise($lastMonth["month"]) ?> : <?= texte_securise(number_format((float) $lastMonth["average_price"], 3, ",", " ")) ?> EUR/L</span>
										</div>
									</div>
								<?php } ?>
							</article>
						<?php } ?>
					</div>
				<?php } ?>
			</section>
		</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
