<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CriterePartiel extends Critere
{
    public function __construct($oChamp, $mValeur)
    {
        $mValeur = $oChamp->bEstRenseigne($mValeur) ?  $this->sTraiterValeur($oChamp,"%$mValeur%") : null;

        parent::__construct($oChamp, $mValeur, 'LIKE');
    }

}