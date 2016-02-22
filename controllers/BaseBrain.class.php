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
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/modules/base/config/conf.yml') === true) {
            $szFichier = $_SERVER['DOCUMENT_ROOT'].'/modules/base/config/conf.yml';
        } else if (file_exists($_SERVER['DOCUMENT_ROOT'].'/modules/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml') === true) {
            $szFichier = $_SERVER['DOCUMENT_ROOT'].'/modules/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml';
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/ressources/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml') === true) {
            $szFichier = $_SERVER['DOCUMENT_ROOT'].'/ressources/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml';
        }
        $data = \Spyc::YAMLLoad($szFichier);

        $GLOBALS['aParamsBdd'] = $data;
    }
}
