<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereDifference extends Critere
{
    public function __construct($oChamp, $mValeur)
    {
        $mValeur = $oChamp->bEstRenseigne($mValeur) ?  "%$mValeur%" : null;
        parent::__construct($oChamp, $mValeur, 'NOT LIKE');
    }

}