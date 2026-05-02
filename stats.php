<?php
/**
 * @file
 * @brief Page des statistiques de consultation et de prix.
 *
 * Page de statistiques.
 *
 * Elle lit les fichiers CSV de suivi et calcule aussi les tendances annuelles
 * depuis l'archive XML officielle des prix carburants.
 */
require __DIR__ . '/includes/functions.php';

preparer_dossiers_et_fichiers();
$theme = gerer_theme();
$statistiques = calculer_statistiques();
$maxVilles = $statistiques['top_cities'] === [] ? 1 : max($statistiques['top_cities']);
$maxDepartements = $statistiques['top_departments'] === [] ? 1 : max($statistiques['top_departments']);
$maxRegions = $statistiques['top_regions'] === [] ? 1 : max($statistiques['top_regions']);
$maxCarburants = $statistiques['top_fuels'] === [] ? 1 : max($statistiques['top_fuels']);
$maxModes = $statistiques['top_modes'] === [] ? 1 : max($statistiques['top_modes']);
$tendancesCarburants = lire_tendances_prix_officielles(null, PM_TREND_FUELS);

$titrePage = "Statistiques - Plein Malin";
$descriptionPage = "Page statistiques de Plein Malin.";
$pageActive = "stats";
$textePiedPage = "Enzo Phung | Fatma-Zahra Baarir | Statistiques générées à partir du CSV de consultations.";

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
						<strong><?= texte_securise((string) $statistiques['consultation_count']) ?></strong>
						<span>recherches</span>
					</div>
					<div class="stat-chip">
						<strong><?= texte_securise((string) $statistiques['page_visit_count']) ?></strong>
						<span>visites de pages</span>
					</div>
					<div class="stat-chip">
						<strong><?= texte_securise((string) $statistiques['page_visitor_count']) ?></strong>
						<span>visiteurs approx.</span>
					</div>
				</div>
		</section>

		<section class="panel">
			<h2>Graphiques des consultations</h2>
			<div class="stats-grid">
				<article class="stats-card">
					<h3>Top des villes consultées</h3>
					<?php if ($statistiques['top_cities'] === []) { ?>
						<p class="empty-state">Aucune consultation enregistrée pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($statistiques['top_cities'] as $ville => $nombre) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($ville) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxVilles) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Top des départements consultés</h3>
					<?php if ($statistiques['top_departments'] === []) { ?>
						<p class="empty-state">Aucun département enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($statistiques['top_departments'] as $departement => $nombre) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($departement) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxDepartements) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Top des régions consultées</h3>
					<?php if ($statistiques['top_regions'] === []) { ?>
						<p class="empty-state">Aucune région enregistrée pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($statistiques['top_regions'] as $region => $nombre) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($region) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxRegions) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card">
					<h3>Carburants les plus recherchés</h3>
					<?php if ($statistiques['top_fuels'] === []) { ?>
						<p class="empty-state">Aucun carburant enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($statistiques['top_fuels'] as $carburant => $nombre) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($carburant) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxCarburants) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="stats-card stats-card-wide">
					<h3>Recherches par mode</h3>
					<?php if ($statistiques['top_modes'] === []) { ?>
						<p class="empty-state">Aucun mode de recherche enregistré pour le moment.</p>
					<?php } else { ?>
						<div class="bar-chart">
							<?php foreach ($statistiques['top_modes'] as $mode => $nombre) { ?>
								<div class="bar-row">
									<span class="bar-label"><?= texte_securise($mode) ?></span>
									<div class="bar-track">
										<div class="bar-fill" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxModes) * 100))) ?>%"></div>
									</div>
									<strong class="bar-value"><?= texte_securise((string) $nombre) ?></strong>
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
						<?= texte_securise((string) ($tendancesCarburants["year"] ?? date("Y"))) ?>,
						avec une moyenne annuelle de référence sur <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? ((int) date("Y") - 1))) ?>.
					</p>
					<p class="small-note">
						Source officielle :
						<a href="<?= texte_securise($tendancesCarburants["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee") ?>">
							donnees.roulez-eco.fr
						</a>
						<?php if (formater_date_heure($tendancesCarburants["cached_at"] ?? "") !== "") { ?>
							- dernière mise à jour du cache le <?= texte_securise(formater_date_heure($tendancesCarburants["cached_at"])) ?>
						<?php } ?>
					</p>

					<?php if (($tendancesCarburants["fuels"] ?? []) === []) { ?>
					<p class="empty-state">Tendances indisponibles pour le moment.</p>
				<?php } else { ?>
					<div class="trend-grid">
						<?php foreach ($tendancesCarburants["fuels"] as $nomCarburant => $moisDonnees) { ?>
							<article class="trend-group">
								<h3><?= texte_securise($nomCarburant) ?></h3>
								<?php if ($moisDonnees === []) { ?>
								<p class="empty-state">Aucune donnée disponible.</p>
								<?php } else { ?>
									<?php $moyenneReference = $tendancesCarburants["reference_averages"][$nomCarburant] ?? null; ?>
									<p class="small-note">Tableau mensuel des prix</p>
									<div class="trend-table-wrap">
										<table class="trend-table">
											<thead>
												<tr>
													<th>Mois</th>
													<th>Prix moyen</th>
													<th>Relevés</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($moisDonnees as $moisDonnee) { ?>
													<tr>
														<td><?= texte_securise(formater_mois_tendance((string) $moisDonnee["month"])) ?></td>
														<td><?= texte_securise(number_format((float) $moisDonnee["average_price"], 3, ",", " ")) ?> EUR/L</td>
														<td><?= texte_securise((string) $moisDonnee["price_count"]) ?></td>
													</tr>
												<?php } ?>
											</tbody>
											<?php if (is_array($moyenneReference)) { ?>
												<tfoot>
													<tr>
														<td>Moyenne annuelle de référence <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? "")) ?></td>
														<td><?= texte_securise(number_format((float) $moyenneReference["average_price"], 3, ",", " ")) ?> EUR/L</td>
														<td><?= texte_securise((string) $moyenneReference["price_count"]) ?> relevés</td>
													</tr>
												</tfoot>
											<?php } ?>
										</table>
									</div>
									<?php if (is_array($moyenneReference)) { ?>
										<p class="chart-caption">
											Moyenne annuelle de référence <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? "")) ?>
											de l'archive complète : <?= texte_securise(number_format((float) $moyenneReference["average_price"], 3, ",", " ")) ?> EUR/L
										</p>
									<?php } ?>
								<?php } ?>
							</article>
						<?php } ?>
					</div>
				<?php } ?>
			</section>
		</main>

<?php require __DIR__ . "/includes/footer.php"; ?>
