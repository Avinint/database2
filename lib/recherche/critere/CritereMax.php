<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereMax extends Critere
{
    public function __construct(Champ $oChamp, $mValeur)
    {
        parent::__construct($oChamp, $oChamp->sTraiterValeur($mValeur) ,'<=');
    }
}

