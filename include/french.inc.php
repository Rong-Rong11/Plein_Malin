<?php
/**
 * @file french.inc.php
 * @brief Contenu d'accueil affiche en francais sur la page d'index.
 * @author BAARIR Fatma-Zahra
 * @author PHUNG Enzo
 *
 * @details Ce fragment est inclus dynamiquement dans `index.php` lorsque
 * la langue francaise est selectionnee. Il contient une presentation
 * pedagogique du site et des liens directs vers les differents TD.
 */
?>
<?php
/**
 * Titre principal et presentation generale de la version francaise de l'accueil.
 *
 * Cette introduction pose le contexte universitaire du projet et rappelle
 * que le site sert de support aux exercices de developpement web.
 */
?>
<h1>Bienvenue sur notre site de TD</h1>
<p>
   Ce site regroupe les exercices réalisés dans le cadre du module
   de développement web (L2 Informatique).
</p>
<section>
   <?php
   /**
    * Section de presentation generale du projet et de son contexte pedagogique.
    *
    * Elle explique la finalite du site : rassembler plusieurs exercices
    * pratiques autour de PHP et de la generation dynamique de HTML.
    */
   ?>
   <h2>Présentation</h2>
   <p>
      Ce site a été réalisé dans le cadre du module de développement web
      en L2 Informatique. Il regroupe les différents travaux pratiques
      réalisés pendant le semestre.
   </p>
   <p>
      Chaque TD présente des exercices permettant de pratiquer le PHP,
      la création de fonctions, la manipulation de tableaux et la
      génération de contenu HTML dynamique.
   </p>
</section>
<section>
   <?php
   /**
    * Section listant les objectifs d'apprentissage du site.
    *
    * Les objectifs resumeent les competences principales travaillees
    * au fil des TD.
    */
   ?>
   <h2>Objectifs</h2>
   <ul>
      <li>Apprendre à utiliser PHP pour générer du HTML</li>
      <li>Créer et utiliser des fonctions</li>
      <li>Manipuler des tableaux PHP</li>
      <li>Structurer un site avec des fichiers réutilisables (header, footer)</li>
   </ul>
</section>
<section>
   <?php
   /**
    * Section expliquant comment naviguer dans les differents TD.
    *
    * Elle sert de mini guide d'utilisation pour un visiteur qui decouvre
    * le site sans connaitre son organisation interne.
    */
   ?>
   <h2>Navigation</h2>
   <p>
      Utilisez le menu de navigation pour accéder aux différents TD.
      Chaque page contient les exercices et les résultats générés par
      les scripts PHP.
      Retrouvez le plan du site en bas de page.
   </p>
   <p>
      Le TD10 est encore en cours de construction.
   </p>
</section>
<section class="practice-links">
   <?php
   /**
    * Liste des liens directs vers chaque travail dirige.
    *
    * Ces liens completenent la navigation principale en proposant un acces
    * immediat aux exercices depuis la page d'accueil.
    */
   ?>
   <h2>Travaux pratiques</h2>
   <ul>
      <li>
         <a href="td5.php<?= "?style=" . $style ?>" class="link-btn">TD5 - Fonctions et tables HTML</a>
      </li>
      <li>
         <a href="td6.php<?= "?style=" . $style ?>" class="link-btn">TD6 - Fonctions, constantes et factorisation</a>
      </li>
      <li>
         <a href="td7.php<?= "?style=" . $style ?>" class="link-btn">TD7 - Tableaux PHP et fonctions</a>
      </li>
      <li>
         <a href="td8.php<?= "?style=" . $style ?>" class="link-btn">TD8 - PHP - tableaux, fonctions et liens
            paramétrés</a>
      </li>
      <li>
         <a href="td9.php<?= "?style=" . $style ?>" class="link-btn">TD9 - Formulaires HTML et traitement PHP</a>
      </li>
      <li>
         <a href="td10.php<?= "?style=" . $style ?>" class="link-btn">TD10 - En construction</a>
      </li>
   </ul>
</section>
