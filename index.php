<?php
$titre = "Projet Web - Accueil";
$description = "Page d'accueil du projet web.";

if (isset($_GET["style"]) && $_GET["style"] === "alternatif") {
    $style = "alternatif";
    $feuille = "style-alt.css";
    $logo = "image/logoblanc.svg";
    $styleLabel = "Mode standard";
    $styleSuivant = "standard";
} else {
    $style = "standard";
    $feuille = "style.css";
    $logo = "image/logonoir.svg";
    $styleLabel = "Mode alternatif";
    $styleSuivant = "alternatif";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titre ?></title>
    <meta name="author" content="Enzo Phung">
    <meta name="description" content="<?= $description ?>">
    <link rel="stylesheet" href="<?= $feuille ?>">
    <link rel="icon" href="image/favicon.ico" type="image/x-icon">
</head>
<body>
    <div id="haut-page"></div>

    <header>
        <a href="index.php">
            <img class="logo" src="<?= $logo ?>" alt="Logo du projet" width="500" height="200">
        </a>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php?style=<?= $styleSuivant ?>"><?= $styleLabel ?></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Projet Web</h1>
        <p class="subtitle">Point d'avancement 1 : prise en main des API JSON</p>

        <section>
            <h2>Présentation</h2>
            <p>
                Cette première étape du projet sert à montrer qu'on sait utiliser
                une API web qui renvoie du JSON, puis afficher les données obtenues
                dans une page PHP.
            </p>
        </section>

        <section>
            <h2>Contenu du site</h2>
            <p>
                Le site contient une page d'accueil simple et une page technique.
                La page technique illustre deux appels d'API différents afin de
                montrer la récupération et l'exploitation de données JSON.
            </p>
            <ul class="liste-simple">
                <li>API Ghibli pour afficher un film aléatoire</li>
                <li>API de géolocalisation IP pour afficher une position approximative</li>
                <li>Affichage des données avec PHP et HTML</li>
            </ul>
        </section>

        <section>
            <h2>Objectif pédagogique</h2>
            <p>
                L'objectif est de comprendre le fonctionnement de
                <code>file_get_contents()</code>, de <code>json_decode()</code>,
                puis de la manipulation de tableaux PHP issus d'une réponse JSON.
            </p>
        </section>
    </main>

    <footer>
        <span style="text-align: right;">
            <a href="#haut-page" class="back-top">
                <img src="image/back_top.png" alt="Retour en haut">
            </a>
        </span>
        <div class="footer-info">
            <span><em>Enzo Phung</em></span>
            <span><em>CY Cergy Paris Université - ©2025-2026</em></span>
            <span><em>Projet Web - Avancement 1</em></span>
        </div>
        <div class="footer-links">
            <span class="link-btn">
                <a href="tech.php?style=<?= $style ?>">Accéder à la page technique</a>
            </span>
        </div>
    </footer>
</body>
</html>
