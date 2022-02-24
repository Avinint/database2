<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CriterePartiel extends Critere
{
    public function __construct($oChamp, $mValeur, $sOperateur = 'LIKE', $sOperateurLogique = 'AND')
    {

        $mValeur = $oChamp->bEstRenseigne($mValeur) ?  "%$mValeur%" : null;

        parent::__construct($oChamp, $mValeur, $sOperateur, $sOperateurLogique);
    }

}