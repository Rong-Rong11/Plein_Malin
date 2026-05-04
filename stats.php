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
	<main class="page-conteneur">
		<section class="panneau">
			<p class="surtitre"><?= texte_securise("Rubrique statistiques") ?></p>
			<h1><?= texte_securise("Consultations Plein Malin") ?></h1>
				<p class="accroche">
					<?= texte_securise("Les recherches correspondent aux consultations avec critères. Les visites comptent aussi") ?>
					<?= texte_securise("les pages vues sans lancement de recherche.") ?>
				</p>
				<p class="note-discrete">
					<?= texte_securise("Une recherche est enregistrée quand une page de résultats est produite ; une visite de page") ?>
					<?= texte_securise("est comptée à chaque affichage d'une page du site, même sans recherche.") ?>
				</p>
				<div class="stats-en-ligne">
					<div class="pastille-stat">
						<strong><?= texte_securise((string) $statistiques['consultation_count']) ?></strong>
						<span><?= texte_securise("recherches") ?></span>
					</div>
					<div class="pastille-stat">
						<strong><?= texte_securise((string) $statistiques['page_visit_count']) ?></strong>
						<span><?= texte_securise("visites de pages") ?></span>
					</div>
					<div class="pastille-stat">
						<strong><?= texte_securise((string) $statistiques['page_visitor_count']) ?></strong>
						<span><?= texte_securise("visiteurs approx.") ?></span>
					</div>
				</div>
		</section>

		<section class="panneau">
			<h2><?= texte_securise("Graphiques des consultations") ?></h2>
			<div class="grille-stats">
				<article class="carte-stats">
					<h3><?= texte_securise("Top des villes consultées") ?></h3>
					<?php if ($statistiques['top_cities'] === []) { ?>
						<p class="message-vide"><?= texte_securise("Aucune consultation enregistrée pour le moment.") ?></p>
					<?php } else { ?>
						<div class="graphique-barres">
							<?php foreach ($statistiques['top_cities'] as $ville => $nombre) { ?>
								<div class="ligne-barre">
									<span class="libelle-barre"><?= texte_securise($ville) ?></span>
									<div class="piste-barre">
										<div class="remplissage-barre" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxVilles) * 100))) ?>%"></div>
									</div>
									<strong class="valeur-barre"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="carte-stats">
					<h3><?= texte_securise("Top des départements consultés") ?></h3>
					<?php if ($statistiques['top_departments'] === []) { ?>
						<p class="message-vide"><?= texte_securise("Aucun département enregistré pour le moment.") ?></p>
					<?php } else { ?>
						<div class="graphique-barres">
							<?php foreach ($statistiques['top_departments'] as $departement => $nombre) { ?>
								<div class="ligne-barre">
									<span class="libelle-barre"><?= texte_securise($departement) ?></span>
									<div class="piste-barre">
										<div class="remplissage-barre" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxDepartements) * 100))) ?>%"></div>
									</div>
									<strong class="valeur-barre"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="carte-stats">
					<h3><?= texte_securise("Top des régions consultées") ?></h3>
					<?php if ($statistiques['top_regions'] === []) { ?>
						<p class="message-vide"><?= texte_securise("Aucune région enregistrée pour le moment.") ?></p>
					<?php } else { ?>
						<div class="graphique-barres">
							<?php foreach ($statistiques['top_regions'] as $region => $nombre) { ?>
								<div class="ligne-barre">
									<span class="libelle-barre"><?= texte_securise($region) ?></span>
									<div class="piste-barre">
										<div class="remplissage-barre" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxRegions) * 100))) ?>%"></div>
									</div>
									<strong class="valeur-barre"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="carte-stats">
					<h3><?= texte_securise("Carburants les plus recherchés") ?></h3>
					<?php if ($statistiques['top_fuels'] === []) { ?>
						<p class="message-vide"><?= texte_securise("Aucun carburant enregistré pour le moment.") ?></p>
					<?php } else { ?>
						<div class="graphique-barres">
							<?php foreach ($statistiques['top_fuels'] as $carburant => $nombre) { ?>
								<div class="ligne-barre">
									<span class="libelle-barre"><?= texte_securise($carburant) ?></span>
									<div class="piste-barre">
										<div class="remplissage-barre" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxCarburants) * 100))) ?>%"></div>
									</div>
									<strong class="valeur-barre"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>

				<article class="carte-stats carte-stats-large">
					<h3><?= texte_securise("Recherches par mode") ?></h3>
					<?php if ($statistiques['top_modes'] === []) { ?>
						<p class="message-vide"><?= texte_securise("Aucun mode de recherche enregistré pour le moment.") ?></p>
					<?php } else { ?>
						<div class="graphique-barres">
							<?php foreach ($statistiques['top_modes'] as $mode => $nombre) { ?>
								<div class="ligne-barre">
									<span class="libelle-barre"><?= texte_securise($mode) ?></span>
									<div class="piste-barre">
										<div class="remplissage-barre" style="width: <?= texte_securise((string) max(10, (int) round(($nombre / $maxModes) * 100))) ?>%"></div>
									</div>
									<strong class="valeur-barre"><?= texte_securise((string) $nombre) ?></strong>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</article>
			</div>
		</section>

				<section class="panneau">
					<h2><?= texte_securise("Tendance annuelle des prix") ?></h2>
					<p class="note-discrete">
					<?= texte_securise("Moyennes mensuelles calculées côté serveur depuis l'archive annuelle officielle XML") ?>
						<?= texte_securise((string) ($tendancesCarburants["year"] ?? date("Y"))) ?>,
						<?= texte_securise("avec une moyenne annuelle de référence sur") ?> <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? ((int) date("Y") - 1))) ?>.
					</p>
					<p class="note-discrete">
						<?= texte_securise("Source officielle") ?> :
						<a href="<?= texte_securise($tendancesCarburants["source_url"] ?? "https://donnees.roulez-eco.fr/opendata/annee") ?>">
							donnees.roulez-eco.fr
						</a>
						<?php if (formater_date_heure($tendancesCarburants["cached_at"] ?? "") !== "") { ?>
							- <?= texte_securise("dernière mise à jour du cache le") ?> <?= texte_securise(formater_date_heure($tendancesCarburants["cached_at"])) ?>
						<?php } ?>
					</p>

					<?php if (($tendancesCarburants["fuels"] ?? []) === []) { ?>
					<p class="message-vide"><?= texte_securise("Tendances indisponibles pour le moment.") ?></p>
				<?php } else { ?>
					<div class="grille-tendances">
						<?php foreach ($tendancesCarburants["fuels"] as $nomCarburant => $moisDonnees) { ?>
							<article class="bloc-tendance">
								<h3><?= texte_securise($nomCarburant) ?></h3>
								<?php if ($moisDonnees === []) { ?>
								<p class="message-vide"><?= texte_securise("Aucune donnée disponible.") ?></p>
								<?php } else { ?>
									<?php $moyenneReference = $tendancesCarburants["reference_averages"][$nomCarburant] ?? null; ?>
									<p class="note-discrete"><?= texte_securise("Tableau mensuel des prix") ?></p>
									<div class="zone-tableau-tendance">
										<table class="tableau-tendance">
											<thead>
												<tr>
													<th><?= texte_securise("Mois") ?></th>
													<th><?= texte_securise("Prix moyen") ?></th>
													<th><?= texte_securise("Relevés") ?></th>
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
														<td><?= texte_securise("Moyenne annuelle de référence") ?> <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? "")) ?></td>
														<td><?= texte_securise(number_format((float) $moyenneReference["average_price"], 3, ",", " ")) ?> EUR/L</td>
														<td><?= texte_securise((string) $moyenneReference["price_count"]) ?> <?= texte_securise("relevés") ?></td>
													</tr>
												</tfoot>
											<?php } ?>
										</table>
									</div>
									<?php if (is_array($moyenneReference)) { ?>
										<p class="legende-graphique">
											<?= texte_securise("Moyenne annuelle de référence") ?> <?= texte_securise((string) ($tendancesCarburants["reference_year"] ?? "")) ?>
											<?= texte_securise("de l'archive complète") ?> : <?= texte_securise(number_format((float) $moyenneReference["average_price"], 3, ",", " ")) ?> EUR/L
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
