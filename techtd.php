<?php
declare(strict_types=1);
$title = "Projet Web - Page technique";
$description = "Page technique du projet web.";
require_once "./include/pages.inc.php";
require_once("./include/util.inc.php");
require_once("./include/functions.inc.php");
require_once "./include/header.inc.php";

/**
 * Ghibli api
 * @var mixed
 */
$url = "https://ghibliapi.vercel.app/films"; //adresse où on va chercher données
$json = file_get_contents($url); // pour bien formatter le flux de l'api
$data = json_decode($json, true); //transforme json en tableau associatif grâce à true
// choisir un film aléatoire
$film = $data[array_rand($data)];


$ip = $_SERVER["REMOTE_ADDR"] ?? "";
$ipDeTest = "193.54.115.192";

if ($ip == "127.0.0.1" || $ip == "::1" || $ip == "") {
	$ip = $ipDeTest;
}

$cle = "2a034b1e3b5b3083089d0d66813a0cb9";
$urlXml = "https://api.whatismyip.com/ip-address-lookup.php?key=" . $cle . "&input=" . $ip . "&output=xml";

$xmlGeo = file_get_contents($urlXml);
echo $xmlGeo;
$geo = false;
if ($xmlGeo !== false) {
	$geo = simplexml_load_string($xmlGeo);
}
print_r($geo);

?>
<div class="page-layout">
	<aside>
		<?php
		$currentPage = basename($_SERVER['PHP_SELF']);

		foreach ($pages as $page) {
			if ($page["lien"] === $currentPage && !empty($page["subpages"])) { ?>
				<nav class="sub-nav">
					<ul>
						<?php foreach ($page["subpages"] as $sub) { ?>
							<li>
								<a href="<?= $sub["lien"] ?>">
									<?= $sub["nom"] ?>
								</a>
							</li>
						<?php } ?>
					</ul>
				</nav>
				<?php
			}
		}
		?>
	</aside>
	<main>
		<h1>Page technique</h1>
		<p class="subtitle">Exemples d'utilisation d'API JSON en PHP</p>

		<section id="ghibli">
			<h2>API Ghibli</h2>
			<h3>
				<?= $film['title'] ?>
			</h3>
			<h3 lang="jp">
				<?= $film['original_title'] ?>
			</h3>
			<p>
				<?= $film['release_date'] ?>
			</p>
			<p lang="en">
				<?= $film['description'] ?>
			</p>
			<img src="<?= $film['image'] ?>" width="200" alt="" />
			<img src="<?= $film['movie_banner'] ?>" width="400" alt="" />
		</section>

		<section id="geo">
			<h2>Votre position</h2>
			<?php if ($geo !== false) { ?>
				<p><strong>Adresse IP :</strong> <?= $ip ?></p>
				<p><strong>Ville :</strong> <?= (string) $geo->server_data->city ?></p>
				<p><strong>Région :</strong> <?= (string) $geo->server_data->region ?></p>
				<p><strong>Pays :</strong> <?= (string) $geo->server_data->country ?></p>
			<?php } else { ?>
				<p>Impossible de récupérer la position.</p>
			<?php } ?>
		</section>

	</main>
</div>
<?php
require_once "./include/footer.inc.php";
?>
