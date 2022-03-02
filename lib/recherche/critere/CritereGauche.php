<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereGauche extends Critere
{
    public function __construct($oChamp, $mValeur, $sOperateur = 'LIKE', $sOperateurLogique = 'AND')
    {
        $mValeur = $oChamp->bEstRenseigne($mValeur) ? $oChamp->sTraiterValeur("$mValeur%") : null;
        parent::__construct($oChamp, $mValeur, $sOperateur, $sOperateurLogique);
    }
}