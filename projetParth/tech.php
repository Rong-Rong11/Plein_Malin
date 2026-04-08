<?php
$titre = "Page Tech";
$description = "Page technique - Film Ghibli et géolocalisation IP";
require_once("./includes/header.inc.php");
?>

<section>
	<h2>🎬 Film Ghibli aléatoire</h2>

	<?php
	// On récupère tous les films depuis l'API Ghibli
	$json = file_get_contents("https://ghibliapi.vercel.app/films");
	$films = json_decode($json, true);

	// On choisit un film au hasard
	$film = $films[array_rand($films)];
	?>

	<h3><?= htmlspecialchars($film['title']) ?></h3>
	<p lang="ja"><?= htmlspecialchars($film['original_title']) ?></p>
	<p>Année de sortie : <?= htmlspecialchars($film['release_date']) ?></p>
	<p><?= htmlspecialchars($film['description']) ?></p>

	<figure>
		<img src="<?= htmlspecialchars($film['image']) ?>"
			alt="Affiche du film <?= htmlspecialchars($film['title']) ?>">
		<figcaption>Affiche — <?= htmlspecialchars($film['title']) ?></figcaption>
	</figure>

	<figure>
		<img src="<?= htmlspecialchars($film['movie_banner']) ?>"
			alt="Bannière du film <?= htmlspecialchars($film['title']) ?>">
		<figcaption>Bannière — <?= htmlspecialchars($film['title']) ?></figcaption>
	</figure>
</section>

<hr>

<section>
	<h2>📍 Votre position approximative</h2>

	<?php
	// On récupère l'IP du visiteur
	$ip = $_SERVER['REMOTE_ADDR'];

	// Si on est en local, IP de test
	if ($ip === '127.0.0.1' || $ip === '::1') {
		$ip = '193.54.115.192';
	}

	// On appelle l'API ipinfo
	$geo_json = file_get_contents("https://ipinfo.io/{$ip}/geo");
	$geo = json_decode($geo_json, true);
	?>

	<p>Adresse IP : <?= htmlspecialchars($ip) ?></p>
	<p>Ville estimée : <?= htmlspecialchars($geo['city'] ?? 'Inconnue') ?></p>
	<p>Région : <?= htmlspecialchars($geo['region'] ?? 'Inconnue') ?></p>
	<p>Pays : <?= htmlspecialchars($geo['country'] ?? 'Inconnu') ?></p>
	<p>Coordonnées : <?= htmlspecialchars($geo['loc'] ?? 'Inconnues') ?></p>
</section>

<?php require_once("./includes/footer.inc.php"); ?>