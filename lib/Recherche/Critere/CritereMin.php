<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereMin extends Critere
{
    public function __construct(Champ $oChamp, $mValeur)
    {
        parent::__construct($oChamp, $mValeur,'>=');
    }
}

