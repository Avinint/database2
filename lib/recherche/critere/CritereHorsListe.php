<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereHorsListe extends CritereListe
{
    public function __construct(Champ $oChamp, array $mValeur, $sOperateur = 'NOT IN')
    {
        parent::__construct($oChamp, $mValeur, $sOperateur);
    }

}