<?php
/**
 * @file pages.inc.php
 * @brief Definition de l'arborescence de navigation du site.
 *
 * @details Ce fichier joue le role de source centrale de navigation.
 * Les menus principaux, les sous-menus lateraux et une partie de la
 * documentation s'appuient sur cette meme structure de donnees.
 */

/* Liste des pages principales et de leurs ancres internes. */
$pages = array(
   array(
      "nom" => "TD5",
      "lien" => "td5.php",
      "subpages" => array(
         array("nom" => "Exo1", "lien" => "#exo1"),
         array("nom" => "Exo2", "lien" => "#exo2"),
         array("nom" => "Exo3", "lien" => "#exo3"),
         array("nom" => "Exo4", "lien" => "#exo4"),
         array("nom" => "Exo5", "lien" => "#exo5"),
      )
   ),
   array(
      "nom" => "TD6",
      "lien" => "td6.php",
      "subpages" => array(
         array("nom" => "Exo1", "lien" => "#exo1"),
         array("nom" => "Exo2", "lien" => "#exo2"),
         array("nom" => "Exo3", "lien" => "#exo3"),
         array("nom" => "Exo4", "lien" => "#exo4"),

      )
   ),
   array(
      "nom" => "TD7",
      "lien" => "td7.php",
      "subpages" => array(
         array("nom" => "Exo3", "lien" => "#exo3"),
         array("nom" => "Exo4", "lien" => "#exo4")
      )
   ),
   array(
      "nom" => "TD8",
      "lien" => "td8.php",
      "subpages" => array(
         array("nom" => "Exo1", "lien" => "#exo1"),
         array("nom" => "Exo2", "lien" => "#exo2"),
         array("nom" => "Exo3", "lien" => "#exo3")
      )
   ),
   array(
      "nom" => "TD9",
      "lien" => "td9.php",
      "subpages" => array(
         array("nom" => "Exo1", "lien" => "#exo1"),
         array("nom" => "Exo2", "lien" => "#exo2"),
         array("nom" => "Exo Sup", "lien" => "#exosup"),
         array("nom" => "Exo3", "lien" => "#exo3"),
         array("nom" => "Exo4", "lien" => "#exo4"),
         array("nom" => "Exo5", "lien" => "#exo5"),
         array("nom" => "Exo6", "lien" => "#exo6"),
         array("nom" => "Exo7", "lien" => "#exo7"),
         array("nom" => "Exo8", "lien" => "#exo8")
      )
   ),
   array(
      "nom" => "TD10",
      "lien" => "td10.php",
      "subpages" => array(
         array("nom" => "Exo3", "lien" => "#exo3"),
         array("nom" => "Exo5", "lien" => "#exo5"),
         array("nom" => "Exo6", "lien" => "#exo6")
      )
   ),
   array(
      "nom" => "Test Tech",
      "lien" => "tech.php",
      "subpages" => array(

      )
   )
);
?>