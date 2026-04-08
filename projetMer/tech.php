<?php
/**
 * @file tech.php
 * @brief Page technique du projet Station : démonstration API Ghibli (JSON)
 *        + Géolocalisation IP (JSON) + cookie mode jour/nuit.
 *
 * @author Meriem SIMYA, Ines LAIFAOUI
 * @version 1.0
 * @date 2026
 */

// COOKIE MODE JOUR / NUIT

/**
 * @brief Lit, valide et met à jour le cookie de charte graphique.
 *
 * Priorité : paramètre GET > cookie existant > valeur par défaut "standard".
 * Si le cookie contient une valeur invalide, il est supprimé.
 *
 * @return string "standard" ou "alternatif"
 */
function gerer_cookie_style(): string {
    $valeurs_valides = ["standard", "alternatif"];
    $style = "standard";

    if (isset($_GET["style"]) && in_array($_GET["style"], $valeurs_valides, true)) {
        $style = $_GET["style"];
        setcookie("style", $style, [
            "expires"  => time() + 30 * 24 * 3600,
            "path"     => "/projet/",
            "secure"   => false,
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    } elseif (isset($_COOKIE["style"])) {
        if (in_array($_COOKIE["style"], $valeurs_valides, true)) {
            $style = $_COOKIE["style"];
        } else {
            setcookie("style", "", ["expires" => time() - 3600, "path" => "/projet/"]);
        }
    }
    return $style;
}

$styleActif  = gerer_cookie_style();
$styleOppose = ($styleActif === "alternatif") ? "standard" : "alternatif";
$fichierCss  = ($styleActif === "alternatif") ? "style_nuit.css" : "style.css";
$imgStyle    = ($styleActif === "alternatif") ? "images/soleil.jpg" : "images/lune.jpg";
$altStyle    = ($styleActif === "alternatif") ? "Mode jour" : "Mode nuit";


// API GHIBLI — Film aléatoire (flux JSON)

/**
 * @brief Appelle une URL et retourne le JSON décodé en tableau PHP.
 *
 * @param string $url URL de l'API.
 * @return array|null Tableau PHP ou null en cas d'échec.
 */
function fetch_json(string $url): ?array {
    $ctx = stream_context_create(["http" => [
        "timeout" => 6,
        "header"  => "User-Agent: Mozilla/5.0\r\n"
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    return json_decode($raw, true);
}

/**
 * @brief Récupère un film Studio Ghibli tiré aléatoirement via l'API publique.
 *
 * @return array|null Données du film ou null en cas d'erreur.
 */
function get_film_aleatoire(): ?array {
    $films = fetch_json("https://ghibliapi.vercel.app/films");
    if (!$films || count($films) === 0) return null;
    return $films[array_rand($films)];
}

$film = get_film_aleatoire();

// GÉOLOCALISATION IP (flux JSON)

/**
 * @brief Retourne l'adresse IP du visiteur en gérant les proxies.
 *
 * @return string Adresse IP du client.
 */
function get_ip_visiteur(): string {
    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        return trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
    if (!empty($_SERVER["HTTP_CLIENT_IP"]))
        return trim($_SERVER["HTTP_CLIENT_IP"]);
    return $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
}

/**
 * @brief Géolocalise une adresse IP via ipinfo.io (JSON).
 *
 * Utilise une IP de démonstration si la requête vient de localhost.
 *
 * @param string $ip Adresse IP à géolocaliser.
 * @return array|null Tableau (city, region, country, loc…) ou null.
 */
function get_geolocalisation(string $ip): ?array {
    if ($ip === "127.0.0.1" || $ip === "::1") $ip = "193.54.115.192";
    return fetch_json("https://ipinfo.io/{$ip}/geo");
}

$ip  = get_ip_visiteur();
$geo = get_geolocalisation($ip);

$title = "Page du développeur";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> — Station</title>
    <meta name="author" content="SIMYA Meriem – LAIFAOUI Ines">
    <meta name="description" content="Page du développeur – projet Station – démonstration API JSON">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../<?= $fichierCss ?>">
    <link rel="icon" href="../favicon.ico">
    <style>
        /* Styles spécifiques page tech */

        .ghibli-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .ghibli-images {
            display: grid;
            grid-template-rows: 1fr 1fr;
            border-right: 1px solid var(--nav-border);
        }
        .ghibli-images figure {
            margin: 0; overflow: hidden; position: relative;
        }
        .ghibli-images figure:first-child {
            border-bottom: 1px solid var(--nav-border);
        }
        .ghibli-images img {
            width: 100%; height: 175px;
            object-fit: cover; display: block;
            transition: transform 0.35s ease;
        }
        .ghibli-images figure:hover img { transform: scale(1.04); }
        .ghibli-images figcaption {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 5px 10px; font-size: 12px; font-style: italic;
            color: #fff;
            background: linear-gradient(transparent, rgba(0,0,0,0.6));
        }
        .ghibli-info {
            padding: 20px 22px;
            display: flex; flex-direction: column;
            justify-content: center; gap: 10px;
        }
        .film-titre-jp {
            font-size: 1.2rem;
            font-family: 'Playfair Display', serif;
            color: var(--accent); line-height: 1.3;
        }
        .film-titre-en {
            font-size: 1.05rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 700; color: var(--blue); margin: 0;
        }
        .film-meta { display: flex; flex-wrap: wrap; gap: 6px; }
        .film-tag {
            background: var(--bg-card2); border: 1px solid var(--nav-border);
            color: var(--text-muted); border-radius: 20px;
            padding: 3px 11px; font-size: 12px; font-weight: 600;
        }
        .film-tag.annee {
            background: var(--accent-light);
            color: var(--accent-dark); border-color: var(--accent-light);
        }
        .film-desc {
            font-size: 13px; color: var(--text-muted); line-height: 1.65;
            display: -webkit-box; -webkit-line-clamp: 6;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .card-footer {
            border-top: 1px solid var(--nav-border);
            padding: 8px 18px; font-size: 12px;
            color: var(--text-muted); font-style: italic;
            background: var(--bg-card2);
        }
        .card-bordered {
            border: 1px solid var(--nav-border);
            border-radius: var(--radius);
            overflow: hidden; background: var(--bg-card);
        }

        .geo-grid {
            display: grid; grid-template-columns: auto 1fr;
            gap: 22px; padding: 20px 22px; align-items: start;
        }
        .geo-ip-bloc { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }
        .geo-ip-label {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: .08em; color: var(--text-muted); font-weight: 700;
        }
        .geo-ip-val {
            font-family: 'Courier New', monospace;
            font-size: 1.15rem; font-weight: 700; color: var(--accent);
        }
        .geo-ip-note { font-size: 11px; color: var(--text-muted); font-style: italic; }
        .geo-champs { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .geo-champ {
            background: var(--bg-card2); border: 1px solid var(--nav-border);
            border-radius: 8px; padding: 8px 12px;
        }
        .geo-champ-label {
            font-size: 10px; text-transform: uppercase;
            letter-spacing: .07em; color: var(--text-muted);
            font-weight: 700; margin-bottom: 2px;
        }
        .geo-champ-val { font-size: 14px; font-weight: 700; color: var(--text); }
        .geo-coords {
            grid-column: 1 / -1; background: var(--bg-card2);
            border: 1px solid var(--nav-border); border-radius: 8px; padding: 8px 12px;
        }
        .geo-coords-val {
            font-family: 'Courier New', monospace;
            font-size: 13px; color: var(--blue-mid); font-weight: 700;
        }

        .cookie-info {
            background: var(--bg-card2); border: 1px solid var(--nav-border);
            border-left: 4px solid var(--accent);
            border-radius: 8px; padding: 12px 16px;
            font-size: 13.5px; margin-top: 12px;
        }
        .api-source {
            font-size: 12px; color: var(--text-muted);
            margin-top: 7px; padding-left: 2px;
        }
        .api-source a { color: var(--blue-mid); text-decoration: none; font-weight: 600; }
        .api-source a:hover { text-decoration: underline; }
        .erreur-api {
            text-align: center; padding: 28px;
            color: var(--text-muted); font-style: italic;
        }

        @media (max-width: 640px) {
            .ghibli-grid { grid-template-columns: 1fr; }
            .ghibli-images { border-right: none; border-bottom: 1px solid var(--nav-border); grid-template-columns: 1fr 1fr; grid-template-rows: auto; }
            .ghibli-images figure:first-child { border-bottom: none; border-right: 1px solid var(--nav-border); }
            .geo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
    <a href="index.php" class="site-header-logo-link">
        <h1 class="site-title">Station</h1>
    </a>
</header>

<!-- NAV  -->
<nav class="menu">
    <a href="index.php">Accueil</a>

    <div class="nav-right">
        <a href="tech.php?style=<?= urlencode($styleOppose) ?>"
           class="lien-style" title="<?= htmlspecialchars($altStyle) ?>">
            <img src="../<?= $imgStyle ?>" alt="<?= htmlspecialchars($altStyle) ?>" class="img-style">
        </a>
    </div>
</nav>

<main>
    <h2 class="page-title"><?= htmlspecialchars($title) ?></h2>

    <!-- SECTION 1 : COOKIE -->
    <section>
        <h2>🍪 Cookie — Mémorisation de la charte graphique</h2>
        <p>
            Cliquez sur l'icône <strong>☀️ / 🌙</strong> dans la navigation pour basculer
            entre le mode jour et le mode nuit. Votre choix est mémorisé dans un cookie
            valable <strong>30 jours</strong>, limité au dossier <code>/projet/</code>.
            Lors d'une visite ultérieure, la charte est automatiquement restaurée.
        </p>
        <div class="cookie-info">
            <?php $cookie_actuel = $_COOKIE["style"] ?? null; ?>
            <?php if ($cookie_actuel): ?>
                🟢 Cookie actif — valeur&nbsp;: <strong><?= htmlspecialchars($cookie_actuel) ?></strong><br>
                Mode appliqué&nbsp;: <strong><?= $styleActif === "alternatif" ? "🌙 Nuit" : "☀️ Jour" ?></strong>
            <?php else: ?>
                ⚪ Aucun cookie trouvé — mode par défaut (☀️ Jour) appliqué.<br>
                Cliquez sur 🌙 pour créer le cookie.
            <?php endif; ?>
        </div>
    </section>

    <!-- SECTION 2 : GHIBLI -->
    <section>
        <h2>🎬 Film Studio Ghibli — tirage aléatoire</h2>
        <p>
            Un film est sélectionné aléatoirement à chaque chargement de la page
            depuis l'API publique Ghibli (format <strong>JSON</strong>).
            Rafraîchissez pour en découvrir un autre.
        </p>

        <?php if ($film): ?>
        <div class="card-bordered">
            <div class="ghibli-grid">
                <div class="ghibli-images">
                    <figure>
                        <img src="<?= htmlspecialchars($film["image"] ?? "") ?>"
                             alt="Affiche — <?= htmlspecialchars($film["title"] ?? "") ?>">
                        <figcaption>Affiche principale</figcaption>
                    </figure>
                    <figure>
                        <img src="<?= htmlspecialchars($film["movie_banner"] ?? $film["image"] ?? "") ?>"
                             alt="Bannière — <?= htmlspecialchars($film["title"] ?? "") ?>">
                        <figcaption>Bannière du film</figcaption>
                    </figure>
                </div>
                <div class="ghibli-info">
                    <div lang="ja" class="film-titre-jp">
                        <?= htmlspecialchars($film["original_title"] ?? "—") ?>
                    </div>
                    <h3 class="film-titre-en">
                        <?= htmlspecialchars($film["title"] ?? "—") ?>
                    </h3>
                    <div class="film-meta">
                        <span class="film-tag annee"><?= htmlspecialchars($film["release_date"] ?? "—") ?></span>
                        <span class="film-tag"><?= htmlspecialchars($film["running_time"] ?? "—") ?> min</span>
                        <span class="film-tag">Réal. <?= htmlspecialchars($film["director"] ?? "—") ?></span>
                    </div>
                    <p class="film-desc"><?= htmlspecialchars($film["description"] ?? "") ?></p>
                </div>
            </div>
            <div class="card-footer">↻ Rafraîchissez la page pour afficher un autre film aléatoirement</div>
        </div>
        <?php else: ?>
            <p class="erreur-api">⚠️ Impossible de contacter l'API Ghibli.</p>
        <?php endif; ?>

        <p class="api-source">
            Source&nbsp;: <a href="https://ghibliapi.vercel.app/films" target="_blank" rel="noopener">ghibliapi.vercel.app/films</a> — format JSON
        </p>
    </section>

    <!-- SECTION 3 : GÉOLOCALISATION -->
    <section>
        <h2>📍 Géolocalisation par adresse IP</h2>
        <p>
            La position géographique approximative du visiteur est estimée
            depuis son adresse IP via l'API ipinfo.io (format <strong>JSON</strong>).
        </p>

        <?php if ($geo): ?>
        <div class="card-bordered">
            <div class="geo-grid">
                <div class="geo-ip-bloc">
                    <span class="geo-ip-label">Adresse IP détectée</span>
                    <span class="geo-ip-val"><?= htmlspecialchars($geo["ip"] ?? $ip) ?></span>
                    <?php if ($ip === "127.0.0.1" || $ip === "::1"): ?>
                        <span class="geo-ip-note">(localhost — IP de démonstration)</span>
                    <?php endif; ?>
                </div>
                <div class="geo-champs">
                    <div class="geo-champ">
                        <div class="geo-champ-label">Ville</div>
                        <div class="geo-champ-val"><?= htmlspecialchars($geo["city"] ?? "—") ?></div>
                    </div>
                    <div class="geo-champ">
                        <div class="geo-champ-label">Région</div>
                        <div class="geo-champ-val"><?= htmlspecialchars($geo["region"] ?? "—") ?></div>
                    </div>
                    <div class="geo-champ">
                        <div class="geo-champ-label">Pays</div>
                        <div class="geo-champ-val"><?= htmlspecialchars($geo["country"] ?? "—") ?></div>
                    </div>
                    <div class="geo-champ">
                        <div class="geo-champ-label">Fuseau horaire</div>
                        <div class="geo-champ-val"><?= htmlspecialchars($geo["timezone"] ?? "—") ?></div>
                    </div>
                    <div class="geo-coords">
                        <div class="geo-champ-label">Coordonnées GPS estimées</div>
                        <div class="geo-coords-val"><?= htmlspecialchars($geo["loc"] ?? "—") ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
            <p class="erreur-api">⚠️ Impossible de contacter l'API de géolocalisation.</p>
        <?php endif; ?>

        <p class="api-source">
            Source&nbsp;: <a href="https://ipinfo.io" target="_blank" rel="noopener">ipinfo.io</a> — format JSON
        </p>
    </section>

</main>

<!-- FOOTER -->
<footer>
    <p>L2 Informatique – UE Développement Web © 2026 — Ines LAIFAOUI &amp; Meriem SIMYA</p>
    <p><a href="index.php">← Retour à l'accueil</a></p>
</footer>

<a href="#top" class="back-to-top" title="Retour en haut">↑</a>

</body>
</html>
