<?php
define("HELLO", 20);
define("MAX", 10);
define("HEXA", 15);
define("TAB", 17);

/**
 * Fichiers de fonctions de notre site
 * @author BAARIR Fatma-Zahra
 * @author PHUNG Enzo 
 *
 * @details Ce fichier regroupe des fonctions reutilisees dans plusieurs TD.
 * On y trouve a la fois des fonctions purement pedagogiques
 * pour illustrer certains concepts PHP et des fonctions utilitaires
 * directement utilisees par le site.
 */

/**
 * Lit puis incremente le compteur global de hits du site.
 * La valeur est conservee dans un fichier texte afin d'etre partagee
 * entre toutes les pages. Un cache statique evite plusieurs increments
 * lors d'une meme requete.
 *
 * @return int Nombre de hits a afficher apres incrementation.
 *
 * @note La persistance repose sur `hits.txt` a la racine du projet.
 * La fonction reste volontairement simple afin de montrer un exemple
 * de stockage fichier sans base de donnees.
 */
function incrementer_compteur_hits(): int
{
	static $hitCount = null;

	if ($hitCount !== null) {
		return $hitCount;
	}

	$hitsFile = dirname(__DIR__) . "/hits.txt";
	$currentHits = 0;

	if (is_file($hitsFile) && is_readable($hitsFile)) {
		$fileContent = trim((string) file_get_contents($hitsFile));
		if ($fileContent !== "" && ctype_digit($fileContent)) {
			$currentHits = (int) $fileContent;
		}
	}

	$hitCount = $currentHits + 1;
	file_put_contents($hitsFile, (string) $hitCount, LOCK_EX);

	return $hitCount;
}

/**
 * Fonctions TD5
 */

/**
 * Construit une liste HTML contenant plusieurs lignes "hello numero X".
 * La fonction n'affiche rien directement : elle prepare la chaine HTML
 * qui pourra ensuite etre affichee avec `echo`.
 *
 * @return string Code HTML d'une liste `<ul>` contenant `HELLO` elements.
 *                Chaque element correspond a un message "hello numero 1",
 *                "hello numero 2", etc. jusqu'a la valeur de la constante `HELLO`.
 */
function afficherHello()
{
	$liste = "\t\t\t\t<ul>\n";
	for ($i = 1; $i <= HELLO; $i++) {
		$liste .= "\t\t\t\t\t<li>hello numéro $i</li>\n";
	}
	$liste .= "\t\t\t\t</ul>\n";
	return $liste;
}
?>
<?php
/**
 * Construit une liste HTML affichant les nombres de 0 a `HEXA`
 * convertis en base 16.
 * La fonction renvoie uniquement le code HTML ; elle ne l'affiche pas.
 *
 * @return string Code HTML d'une liste `<ul>` horizontale.
 *                Chaque element de la liste contient la representation
 *                hexadecimale d'un entier compris entre 0 et `HEXA`.
 */
function chiffreHexa()
{
	$liste = "\t\t\t\t<ul style=\"list-style-type: none; display: flex;\">\n";
	for ($i = 0; $i <= HEXA; $i++) {
		$liste .= "\t\t\t\t\t<li style=\"margin: 0 10px; padding: 5px 10px; background-color: #03FFFF; border-radius: 4px; font-weight: bold; font-family: monospace;\">"
			.
			dechex($i)
			.
			"\t\t\t\t</li>\n";
	}
	$liste .= "\t\t\t\t</ul>\n";
	return $liste;
}
?>

<?php
/**
 * Construit un tableau HTML de conversion des entiers de 0 a `$n`
 * dans les bases 2, 8, 10 et 16.
 *
 * @param int $n Valeur maximale incluse dans le tableau.
 *               Si aucun argument n'est fourni, la fonction utilise la
 *               constante `TAB`.
 *
 * @return string Code HTML d'un tableau `<table>`.
 *                Chaque ligne du resultat correspond a un entier et contient
 *                sa representation en binaire, en octal, en decimal
 *                et en hexadecimal.
 */
function tableau_bases(int $n = TAB)
{
	$table = "\t\t\t\t<table>\n";
	$table .= "\t\t\t\t\t<caption>Conversions bases 2, 8, 10 et 16</caption>\n";
	$table .= "\t\t\t\t\t<thead>\n";
	$table .= "\t\t\t\t\t\t<tr>\n";
	$table .= "\t\t\t\t\t\t\t<th>binaire</th>\n";
	$table .= "\t\t\t\t\t\t\t<th>octal</th>\n";
	$table .= "\t\t\t\t\t\t\t<th>décimal</th>\n";
	$table .= "\t\t\t\t\t\t\t<th>hexadécimal</th>\n";
	$table .= "\t\t\t\t\t\t</tr>\n";
	$table .= "\t\t\t\t\t</thead>\n";
	$table .= "\t\t\t\t\t<tbody>\n";

	for ($i = 0; $i <= $n; $i++) {
		$binaire = sprintf("%08b", $i);
		$octal = sprintf("%02o", $i);
		$decimal = sprintf("%02d", $i);
		$hexa = sprintf("%02X", $i);

		$table .= "\t\t\t\t\t\t<tr>\n";
		$table .= "\t\t\t\t\t\t\t<td>$binaire</td>\n";
		$table .= "\t\t\t\t\t\t\t<td>$octal</td>\n";
		$table .= "\t\t\t\t\t\t\t<td>$decimal</td>\n";
		$table .= "\t\t\t\t\t\t\t<td>$hexa</td>\n";
		$table .= "\t\t\t\t\t\t</tr>\n";
	}

	$table .= "\t\t\t\t\t</tbody>\n";
	$table .= "\t\t\t\t</table>";

	return $table;
}




/**
 * Fonctions TD6
 */
/**
 * Construit un tableau HTML de multiplication de 1 a `$n`.
 * La valeur recue est bornee pour eviter des tailles de tableau incoherentes :
 * elle est ramenee a 1 si elle est trop petite, et a `MAX * 2` si elle est trop grande.
 *
 * @param int $n Taille de la table de multiplication.
 *               Par exemple, `5` produit les multiplications de 1 a 5.
 *               La valeur par defaut est la constante `MAX`.
 *
 * @return string Code HTML d'un tableau `<table>`.
 *                La premiere ligne et la premiere colonne contiennent les facteurs,
 *                et chaque cellule interne contient le produit ligne x colonne.
 *
 * @note La taille demandee est bornee pour eviter la generation
 * de tableaux trop volumineux dans la page.
 */
function multiplicationTable(int $n = MAX): string
{
	if ($n < 1) {
		$n = 1;
	}
	if ($n > MAX * 2) {
		$n = MAX * 2;
	}

	$str = "\t\t\t\t<table>\n";
	$str .= "\t\t\t\t\t<caption>Table de multiplication</caption>\n";

	$str .= "\t\t\t\t\t<thead>\n";
	$str .= "\t\t\t\t\t\t<tr>\n";
	$str .= "\t\t\t\t\t\t\t<th>X</th>\n";

	// Colonnes
	for ($c = 1; $c <= $n; $c++) {
		$str .= "\t\t\t\t\t\t\t<th scope=\"col\">$c</th>\n";
	}

	$str .= "\t\t\t\t\t\t</tr>\n";
	$str .= "\t\t\t\t\t</thead>\n";
	$str .= "\t\t\t\t\t<tbody>\n";

	//lignes
	for ($l = 1; $l <= $n; $l++) {

		$str .= "\t\t\t\t\t\t<tr>\n";
		$str .= "\t\t\t\t\t\t\t<th scope=\"row\">$l</th>\n";

		for ($c = 1; $c <= $n; $c++) {
			$str .= "\t\t\t\t\t\t\t<td>" . ($l * $c) . "</td>\n";
		}

		$str .= "\t\t\t\t\t\t</tr>\n";
	}

	$str .= "\t\t\t\t\t</tbody>\n";
	$str .= "\t\t\t\t</table>\n";

	return $str;
}





/**
 * Convertit une couleur exprimee en composantes RGB vers son ecriture hexadecimale.
 * Chaque composante est transformee sur 2 caracteres hexadecimaux,
 * puis les trois parties sont assemblees sous la forme `#RRGGBB`.
 *
 * @param int $r Composante rouge de la couleur.
 *               La valeur attendue correspond en general a un entier de 0 a 255.
 * @param int $g Composante verte de la couleur.
 *               La valeur attendue correspond en general a un entier de 0 a 255.
 * @param int $b Composante bleue de la couleur.
 *               La valeur attendue correspond en general a un entier de 0 a 255.
 *
 * @return string Chaine representant la couleur au format hexadecimal CSS.
 *                Exemple : `#FF0000` pour du rouge.
 */
function conversion1RGB(int $r = 0, int $g = 0, int $b = 0): string
{
	$rHexa = strtoupper(dechex($r));
	if (strlen($rHexa) == 1) {
		$rHexa = "0" . $rHexa;
	}

	$gHexa = strtoupper(dechex($g));
	if (strlen($gHexa) == 1) {
		$gHexa = "0" . $gHexa;
	}

	$bHexa = strtoupper(dechex($b));
	if (strlen($bHexa) == 1) {
		$bHexa = "0" . $bHexa;
	}
	$s = "#" . $rHexa . $gHexa . $bHexa;
	return $s;
}

/**
 * Convertit une couleur hexadecimale en ses trois composantes RGB.
 * La fonction verifie d'abord que la chaine fournie est valide, puis remplit
 * les variables passees par reference avec les valeurs rouge, verte et bleue.
 *
 * @param string $val Couleur a analyser au format hexadecimal.
 *                    La chaine peut commencer par `#`, par exemple `#12AF7C`
 *                    ou `12AF7C`.
 * @param int $r Variable passee par reference qui recevra la composante rouge
 *               si la conversion reussit.
 * @param int $g Variable passee par reference qui recevra la composante verte
 *               si la conversion reussit.
 * @param int $b Variable passee par reference qui recevra la composante bleue
 *               si la conversion reussit.
 *
 * @return bool `true` si la chaine est valide et que `$r`, `$g` et `$b`
 *              ont bien ete remplis ; `false` si le format fourni n'est pas correct.
 */
function conversion2RGB(string $val, int &$r, int &$g, int &$b): bool
{
	$val = ltrim($val, '#');

	if (strlen($val) != 6) {
		return false;
	}
	for ($i = 0; $i < strlen($val); $i++) {
		$c = $val[$i];
		if (!(($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'F') || ($c >= 'a' && $c <= 'f'))) {
			return false;
		}
	}
	if (strlen($val) == 3) {
		$r = hexdec($val[0] . $val[0]);
		$g = hexdec($val[1] . $val[1]);
		$b = hexdec($val[2] . $val[2]);
		return true;
	} else if (strlen($val) == 6) {
		$r = hexdec(substr($val, 0, 2));
		$g = hexdec(substr($val, 2, 2));
		$b = hexdec(substr($val, 4, 2));
		return true;
	}
	return false;
}




/**
 * Convertit un nombre ecrit en chiffres romains en entier decimal.
 * La lecture se fait de droite a gauche afin d'appliquer la regle
 * de soustraction des chiffres romains (`IV`, `IX`, etc.).
 *
 * @param string $romain Chaine contenant le nombre romain a convertir,
 *                       par exemple `XIV` ou `MCMLXXXIV`.
 *
 * @return int Valeur decimale calculee a partir de la chaine fournie.
 *             Si un symbole inconnu est rencontre, il est traite comme 0.
 */
function romainDecimal(string $romain): int
{
	$val = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
	$res = 0;
	$valPrecedente = 0;
	for ($i = strlen($romain) - 1; $i >= 0; $i--) {
		$v = $val[$romain[$i]] ?? 0;
		if ($v < $valPrecedente)
			$res -= $v;
		else {
			$res += $v;
			$valPrecedente = $v;
		}
	}
	return $res;
}


/**
 * Construit une table ASCII partielle sous forme de tableau HTML.
 * La fonction parcourt les codes hexadecimaux de `0x20` a `0x7F`
 * et associe certaines cellules a des classes CSS selon leur type
 * (chiffres, majuscules, minuscules).
 *
 * @return string Code HTML d'un tableau `<table>` representant les caracteres ASCII.
 *                Les lignes correspondent au premier chiffre hexadecimal
 *                et les colonnes au second. Le contenu de chaque cellule
 *                est le caractere associe au code.
 */
function tableASCII(): string
{
	$str = "\t\t\t\t<table>\n";
	$str .= "\t\t\t\t\t<caption>Table ascii</caption>\n";
	$str .= "\t\t\t\t\t<thead>\n";
	$str .= "\t\t\t\t\t\t<tr>\n";
	$str .= "\t\t\t\t\t\t\t<th></th>\n";
	for ($i = 0; $i <= 15; $i++) {
		$str .= "\t\t\t\t\t\t\t<th>" . strtoupper(dechex($i)) . "</th>\n";
	}
	$str .= "\t\t\t\t\t\t</tr>\n";
	$str .= "\t\t\t\t\t</thead>\n";
	$str .= "\t\t\t\t\t<tbody>\n";

	for ($l = 2; $l <= 7; $l++) {
		$str .= "\t\t\t\t\t\t<tr>\n";
		$str .= "\t\t\t\t\t\t\t<th>" . strtoupper(dechex($l)) . "</th>\n";
		for ($c = 0; $c <= 15; $c++) {
			$class = "";
			if ($l == 3 && $c <= 9) {
				$class = "chiffre";
			} else if ($l == 4 && $c > 0) {
				$class = "majuscule";
			} else if ($l == 5 && $c <= 10) {
				$class = "majuscule";
			} else if ($l == 6 && $c > 0) {
				$class = "minuscule";
			} else if ($l == 7 && $c <= 10) {
				$class = "minuscule";
			}

			$hex = dechex($l) . dechex($c);
			$dec = hexdec($hex);
			if ($dec == 127) {
				$car = "&#x00A0;";
			} else {
				//changement pour validation xml
				$car = chr($dec);
				if ($car == "<") {
					$car = "&lt;";
				}
				if ($car == ">") {
					$car = "&gt;";
				}
				if ($car == "&") {
					$car = "&amp;";
				}
			}
			$str .= "\t\t\t\t\t\t\t<td class=\"" . $class . "\">" . $car . "</td>\n";
		}
		$str .= "\t\t\t\t\t\t</tr>\n";
	}
	$str .= "\t\t\t\t\t</tbody>\n";
	$str .= "\t\t\t\t</table>\n";
	return $str;
}

/**
 * Fonctions TD7
 */

/**
 * Construit une liste HTML a partir d'un tableau de noms de regions.
 * Selon la valeur de `$numerote`, la liste produite sera ordonnee (`<ol>`)
 * ou non ordonnee (`<ul>`).
 *
 * @param array $regions Tableau contenant les noms des regions a afficher.
 *                       Chaque valeur du tableau devient un element `<li>`.
 * @param bool $numerote Indique le type de liste a produire :
 *                       `true` pour une liste numerotee,
 *                       `false` pour une liste a puces.
 *
 * @return string Code HTML de la liste generee.
 *                Le resultat contient un element `<li>` pour chaque region.
 */
function regionList(array $regions, bool $numerote = false): string
{
	if ($numerote) {
		$tag = "ol";
	} else {
		$tag = "ul";
	}

	$str = "\t\t\t\t<$tag>\n";

	foreach ($regions as $region) {
		$str .= "\t\t\t\t\t<li>$region</li>\n";
	}

	$str .= "\t\t\t\t</$tag>\n";

	return $str;
}

/**
 * Produit une phrase expliquant l'origine etymologique du jour
 * de la semaine actuel et du mois actuel.
 * Les correspondances sont definies dans deux tableaux associatifs internes.
 *
 * @return string Phrase descriptive basee sur la date courante du serveur.
 *                Le resultat indique le nom du jour actuel, son origine,
 *                puis le numero du mois courant et son origine etymologique.
 */
function etymologiDate(): string
{
	$jours = [
		"lundi" => "Lune",
		"mardi" => "Mars",
		"mercredi" => "Mercure",
		"jeudi" => "Jupiter",
		"vendredi" => "Vénus",
		"samedi" => "Saturne",
		"dimanche" => "Soleil"
	];

	$mois = [
		1 => "Janus",
		2 => "Februa",
		3 => "Mars",
		4 => "Aphrodite",
		5 => "Maia",
		6 => "Junon",
		7 => "Jules César",
		8 => "Auguste",
		9 => "Sept",
		10 => "Octo",
		11 => "Novem",
		12 => "Decem"
	];

	$joursNom = ["lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche"];

	$jourActuel = $joursNom[date("N") - 1];
	$moisActuel = date("n");

	return $jourActuel . " vient de " . $jours[$jourActuel] .
		" et " . $moisActuel . " vient de " . $mois[$moisActuel];
}

/**
 * Fonctions TD8
 */
/**
 * Analyse une URL et extrait quelques informations principales sur son adresse.
 * La fonction decoupe l'hote pour recuperer le sous-domaine, le nom de domaine
 * et le TLD, puis traduit certains TLD en libelles plus parlants.
 *
 * @param string $url URL complete a analyser, par exemple `https://www.example.com`.
 *
 * @return array{domain: string, host: string, protocol: mixed|string, tld: string}
 *         Tableau associatif contenant :
 *         - `protocol` : le protocole utilise par l'URL, par exemple `http` ou `https`
 *         - `host` : la premiere partie du nom d'hote, par exemple `www`
 *         - `domain` : le nom de domaine principal, par exemple `example`
 *         - `tld` : le domaine de premier niveau, eventuellement remplace
 *           par un libelle comme `Commercial` ou `France`
 *
 * @warning Cette fonction suppose une URL simple de type
 * `sous-domaine.domaine.tld`. Les cas plus complexes comme les sous-domaines
 * multiples ou certains TLD composes ne sont pas entierement geres.
 */
function url(string $url): array
{
	$parse = parse_url($url);
	$parseDomain = explode(".", $parse["host"]);

	$protocol = $parse["scheme"];
	$host = $parseDomain[0];
	$domain = $parseDomain[1];
	$tld = rtrim($parseDomain[2], "/");

	$tlds = array(
		"com" => "Commercial",
		"org" => "Organisation",
		"net" => "Network",
		"fr" => "France"
	);
	$tld = $tlds[$tld] ?? $tld;

	$tab = array(
		"protocol" => $protocol,
		"host" => $host,
		"domain" => $domain,
		"tld" => $tld,
	);
	return $tab;
}

/**
 * Convertit une permission Unix ecrite sur 3 chiffres octaux
 * vers sa representation textuelle de type `rwx`.
 *
 * @param int $n Valeur numerique representant les permissions,
 *               par exemple `755` ou `644`.
 *               Chaque chiffre correspond respectivement au proprietaire,
 *               au groupe et aux autres utilisateurs.
 *
 * @return string Representation textuelle des permissions.
 *                Le resultat contient trois blocs separes par des espaces :
 *                proprietaire, groupe et autres.
 *                Exemple : `755` devient `rwx r-x r-x`.
 */

function convertirPermissions(int $n): string
{
	$nombre = strval($n);
	$permissions = array(
		"---", // pour 0
		"--x", // pour 1
		"-w-", // pour 2
		"-wx", // pour 3
		"r--", // pour 4
		"r-x", // pour 5
		"rw-", // pour 6
		"rwx"  // pour 7
	);

	$proprietaire = $nombre[0];
	$groupe = $nombre[1];
	$autres = $nombre[2];

	$resultat = $permissions[$proprietaire] . " " . $permissions[$groupe] . " " . $permissions[$autres];

	return $resultat;
}

/**
 * Lit les fichiers CSV des regions et des departements pour construire
 * une structure associative de la forme :
 * `["Nom de region" => [["numero" => "75", "nom" => "Paris"], ...]]`.
 *
 * @param string|null $regionsFile Chemin optionnel vers le fichier des regions.
 * @param string|null $departementsFile Chemin optionnel vers le fichier des departements.
 *
 * @return array<string, array<int, array{numero: string, nom: string}>>
 *         Tableau associatif dont chaque clef est un nom de region et
 *         chaque valeur est la liste des departements de cette region.
 *
 * @details La fonction effectue deux lectures successives :
 * elle charge d'abord les regions pour associer chaque code INSEE
 * a son libelle, puis elle rattache chaque departement a la region
 * correspondante grace au code regional present dans le second CSV.
 *
 * @note Le resultat conserve l'ordre du fichier `regions.csv`, ce qui
 * permet un affichage stable dans la page du TD10 et dans la documentation.
 */
function regionsAvecDepartements(?string $regionsFile = null, ?string $departementsFile = null): array
{
	$regionsFile = $regionsFile ?? dirname(__DIR__) . "/regions.csv";
	$departementsFile = $departementsFile ?? dirname(__DIR__) . "/departements.csv";

	if (!is_readable($regionsFile) || !is_readable($departementsFile)) {
		return [];
	}

	$regionsParCode = [];
	$resultat = [];

	if (($handle = fopen($regionsFile, "r")) !== false) {
		$entetes = fgetcsv($handle);

		if ($entetes !== false) {
			$indexReg = array_flip($entetes);

			while (($ligne = fgetcsv($handle)) !== false) {
				$codeRegion = $ligne[$indexReg["REG"]] ?? "";
				$nomRegion = $ligne[$indexReg["LIBELLE"]] ?? "";

				if ($codeRegion === "" || $nomRegion === "") {
					continue;
				}

				$regionsParCode[$codeRegion] = $nomRegion;
				$resultat[$nomRegion] = [];
			}
		}

		fclose($handle);
	}

	if (($handle = fopen($departementsFile, "r")) !== false) {
		$entetes = fgetcsv($handle);

		if ($entetes !== false) {
			$indexDep = array_flip($entetes);

			while (($ligne = fgetcsv($handle)) !== false) {
				$codeRegion = $ligne[$indexDep["REG"]] ?? "";
				$numeroDepartement = $ligne[$indexDep["DEP"]] ?? "";
				$nomDepartement = $ligne[$indexDep["LIBELLE"]] ?? "";

				if ($codeRegion === "" || $numeroDepartement === "" || $nomDepartement === "") {
					continue;
				}

				if (!isset($regionsParCode[$codeRegion])) {
					continue;
				}

				$nomRegion = $regionsParCode[$codeRegion];
				$resultat[$nomRegion][] = [
					"numero" => $numeroDepartement,
					"nom" => $nomDepartement,
				];
			}
		}

		fclose($handle);
	}

	return $resultat;
}

/**
 * Genere une liste de definition HTML affichant chaque region et
 * la liste de ses departements.
 *
 * @param array<string, array<int, array{numero: string, nom: string}>> $regionsDepartements
 *        Structure associative retournee par `regionsAvecDepartements()`.
 *
 * @return string Code HTML d'une liste de definition.
 *
 * @details Le choix de la balise `<dl>` est pertinent ici car chaque region
 * joue le role d'un terme et la liste de ses departements celui d'une
 * description associee.
 */
function afficherRegionsAvecDepartements(array $regionsDepartements): string
{
	if ($regionsDepartements === []) {
		return "<p>Aucune donnee regionale disponible.</p>\n";
	}

	$html = "<dl class=\"regions-departements\">\n";

	foreach ($regionsDepartements as $nomRegion => $departements) {
		$html .= "\t<dt>" . htmlspecialchars($nomRegion, ENT_QUOTES, "UTF-8") . "</dt>\n";
		$html .= "\t<dd>\n";
		$html .= "\t\t<ul>\n";

		foreach ($departements as $departement) {
			$numero = htmlspecialchars($departement["numero"], ENT_QUOTES, "UTF-8");
			$nom = htmlspecialchars($departement["nom"], ENT_QUOTES, "UTF-8");
			$html .= "\t\t\t<li><strong>$numero</strong> - $nom</li>\n";
		}

		$html .= "\t\t</ul>\n";
		$html .= "\t</dd>\n";
	}

	$html .= "</dl>\n";

	return $html;
}
?>
