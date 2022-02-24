<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Core\Lib\Interne\PHP\Utiles;

/**
 * Pour récupérer un type de critère de recherche en fonction de son nom
 * si jamais il n'existe pas dans la liste des critères de recherche spécifiques au modèle
 */
class CritereParser extends Utiles
{
    const SUFFIXES = ['Min', 'Max', 'Different', 'Partiel', 'Gauche', 'Droite', 'Difference', 'Liste', 'HorsListe'];
    // METTRE UN TABLEAU EN VALEUR ? Pour les LIKE et les IN ?

    public static function oDeduireCritere($sCle, $mValeur, $oMapping) : CritereInterface
    {
        $aListeCritere = $GLOBALS['aModules']['base']['conf']['aCritere'];

        $cCritere = '';

        foreach ($aListeCritere as $sSuffixe => $sChemin) {
            if (self::bTerminePar($sCle, $sSuffixe)) {
                $sCle = str_replace($sSuffixe, '', $sCle);
                $cCritere = $sChemin;
                break;
            }
        }

        if (empty($cCritere)) {
            foreach (self::SUFFIXES as $sSuffixe ) {
                if (self::bTerminePar($sCle, $sSuffixe)) {
                    $sCle = str_replace($sSuffixe, '', $sCle);
                    $cCritere = __NAMESPACE__ . '\\Critere' . $sSuffixe;
                    break;
                }
            }
        }



//        $nLimite  = max($aPosition);
//        $sSuffixe = substr($sCle, $nLimite);
//        $sCle     = substr($sCle, 0, $nLimite);

        $oChamp = $oMapping[$sCle] ?? null; // Récupération du champ

        if (isset($oChamp) && class_exists($cCritere)) {

            return new $cCritere($oChamp, $mValeur);

        }

        return new CritereInexistant('Critere' . $sSuffixe);
    }

    /**
     * Détermine si la chaine commence par la sous-chaine
     * @param $haystack
     * @param $needle
     * @return bool

     */
    public static function bCommencePar($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Détermine si la chaine se termine par la sous-chaine ou par le chouchen
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function bTerminePar($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}