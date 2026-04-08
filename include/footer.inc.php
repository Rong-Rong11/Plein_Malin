<?php
/**
 * @file footer.inc.php
 * @brief Gabarit commun du pied de page du site.
 *
 * @details Le footer termine toutes les pages du site avec des informations
 * communes : auteurs, etablissement, navigateur detecte, compteur global
 * de visites et lien vers le plan du site.
 */
?>
<footer>
   <?php $hitCount = incrementer_compteur_hits(); ?>
   <?php
   /**
    * Lien flottant de retour en haut de page.
    *
    * Ce lien ameliore le confort de navigation sur les pages longues
    * comme les TD contenant plusieurs exercices ou de gros tableaux.
    */
   ?>
   <span style="text-align: right;">
      <a href="#" class="back-top">
         <img src="./image/back_top.png" alt="Retour en haut" />
      </a>
   </span>
   <div class="footer-info">
      <?php
      /**
       * Bloc informatif contenant les auteurs, l'etablissement et le navigateur detecte.
       *
       * Le compteur de hits affiche ici repose sur une fonction commune
       * afin de rester coherent sur l'ensemble du site.
       */
      ?>
      <span>
         <em>BAARIR Fatma-Zahra HADDAR Karim PHUNG Enzo</em>
      </span>
      <span>
         <em>CY Cergy Paris Université - ©2025-2026</em>
      </span>
      <span>
         <em>Navigateur utilisé : <?php echo get_navigateur(); ?>
         </em>
      </span>
      <span>
         <em>Nombre de hits : <?php echo $hitCount; ?></em>
      </span>
   </div>
   <div class="footer-links">
      <?php
      /**
       * Lien secondaire menant au plan du site.
       *
       * Le plan du site constitue un point d'entree utile pour verifier
       * rapidement la structure globale du projet.
       */
      ?>
      <span class="link-btn">
         <a href="plan-du-site.php<?= "?style=" . $style ?>">Retrouvez ici le plan du site</a>
      </span>
   </div>

</footer>
</body>

</html>
