<?php
/**
 * @file index.php
 * @brief Page d'accueil du projet Station — Prix des carburants en France.
 *
 * @author Meriem SIMYA, Ines LAIFAOUI
 * @version 1.0
 * @date 07/04/2026
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

$title = "Accueil — Station";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="author" content="SIMYA Meriem – LAIFAOUI Ines">
    <meta name="description" content="Station — Trouvez le carburant le moins cher près de chez vous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../<?= $fichierCss ?>">
    <link rel="icon" href="../favicon.ico">
    <style>
        /* Hero */
        .hero {
            text-align: center;
            padding: 48px 20px 36px;
        }
        .hero-titre {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 6vw, 3.4rem);
            font-weight: 800;
            color: var(--blue);
            margin: 0 0 10px;
            line-height: 1.15;
        }
        .hero-titre span { color: var(--accent); }
        .hero-sous-titre {
            font-size: 1rem;
            color: var(--text-muted);
            margin: 0 0 32px;
        }
        .hero-btn {
            display: inline-block;
            background: var(--accent);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            box-shadow: 0 4px 14px rgba(224,80,138,0.28);
        }
        .hero-btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }

        /* Grille de fonctionnalités */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-top: 8px;
        }
        .feature-card {
            background: var(--bg-card2);
            border: 1px solid var(--nav-border);
            border-radius: var(--radius);
            padding: 22px 20px;
            text-align: center;
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        .feature-titre {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: var(--blue);
            margin: 0 0 6px;
        }
        .feature-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.55;
            margin: 0;
        }

        /* En construction */
        .wip-badge {
            display: inline-block;
            background: var(--accent-light);
            color: var(--accent-dark);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 8px;
            border: 1px solid var(--nav-border);
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

<!-- NAV -->
<nav class="menu">
    <a href="index.php">Accueil</a>

    <div class="nav-right">
        <a href="index.php?style=<?= urlencode($styleOppose) ?>"
           class="lien-style" title="<?= htmlspecialchars($altStyle) ?>">
            <img src="../<?= $imgStyle ?>" alt="<?= htmlspecialchars($altStyle) ?>" class="img-style">
        </a>
    </div>
</nav>

<main>

    <!-- Hero -->
    <div class="hero">
        <h2 class="hero-titre">Trouvez le carburant<br><span>le moins cher</span> près de chez vous</h2>
        <p class="hero-sous-titre">Consultez en temps réel les prix des stations-service en France métropolitaine</p>
        <a href="#recherche" class="hero-btn">Rechercher une station</a>
    </div>

    <!-- Fonctionnalités -->
    <section id="recherche">
        <h2>Fonctionnalités</h2>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🗺️</span>
                <p class="feature-titre">Sélection par région</p>
                <p class="feature-desc">Naviguez sur la carte interactive pour choisir votre région.</p>
                <span class="wip-badge">À venir</span>
            </div>
            <div class="feature-card">
                <span class="feature-icon">⛽</span>
                <p class="feature-titre">Prix en temps réel</p>
                <p class="feature-desc">Données officielles mises à jour en continu depuis data.economie.gouv.fr.</p>
                <span class="wip-badge">À venir</span>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📍</span>
                <p class="feature-titre">Stations à proximité</p>
                <p class="feature-desc">Trouvez les stations les plus proches de votre position géographique.</p>
                <span class="wip-badge">À venir</span>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <p class="feature-titre">Statistiques</p>
                <p class="feature-desc">Visualisez les tendances des prix et les villes les plus consultées.</p>
                <span class="wip-badge">À venir</span>
            </div>
        </div>
    </section>

</main>

<!-- FOOTER -->
<footer>
    <p>L2 Informatique – UE Développement Web © 2026 — Ines LAIFAOUI &amp; Meriem SIMYA</p>
    <p><a href="tech.php">Page développeur</a></p>
</footer>

<a href="#top" class="back-to-top" title="Retour en haut">↑</a>

</body>
</html>
