<?php

namespace APP\Modules\Base\Controllers;

class BaseBrain
{
    /**
     * Constructeur de la classe.
     * Initialisation du module.
     *
     * @param object $objUndeadBrain Objet représentant le noyau.
     *
     * @return  void
     */
    public function __construct($objUndeadBrain = '')
    {
        $szFichier = $_SERVER['DOCUMENT_ROOT'].'/ressources/base/config/conf.yml';
        $data = \Spyc::YAMLLoad($szFichier);

        foreach ($data as $sCle => $sValeur) {
            if (isset($GLOBALS['aParamsBdd'][$sCle]) === false) {
                $GLOBALS['aParamsBdd'][$sCle] = $sValeur;
            }
        }

    }
}
