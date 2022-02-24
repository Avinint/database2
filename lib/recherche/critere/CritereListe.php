<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereListe extends Critere
{
    public function __construct(Champ $oChamp, array $mValeur, $sOperateur = 'IN')
    {
        if (empty($mValeur)) {
            $mValeur = null;
        } else {
            $mValeur = "({$oChamp->sConvertirTableauValeurEnTexte($mValeur)})";
        }
        parent::__construct($oChamp, $mValeur,$sOperateur);
    }

}