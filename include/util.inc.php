<?php
/**
 * @file util.inc.php
 * @brief Fonctions utilitaires generales du site.
 * @author BAARIR Fatma-Zahra
 * @author PHUNG Enzo
 */

/**
 * Recupere la chaine d'identification du navigateur transmise par le client.
 *
 * @return string Valeur de `$_SERVER['HTTP_USER_AGENT']`.
 */
function get_navigateur(): string
{
   return $_SERVER['HTTP_USER_AGENT'];
}
?>
