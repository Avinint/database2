<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere\Oracle;

use APP\Ressources\Base\Lib\Recherche\Critere\Critere;

class CritereGauche extends Critere
{
    public function __construct($oChamp, $mValeur, $sOperateur = 'LIKE', $sOperateurLogique = 'AND')
    {
        $mValeur = $oChamp->bEstRenseigne($mValeur) ?  $this->sTraiterValeur($oChamp,"$mValeur%") : null;
        parent::__construct($oChamp, $mValeur, $sOperateur, $sOperateurLogique);

    }

    /**
     * Transformation de la valeur avant de l'ajout au critère
     * @param $oChamp
     * @param $sValeur
     * @return string
     */
    protected function sTraiterValeur($oChamp, $sValeur)
    {
        return 'LOWER(' . $oChamp->sTraiterValeur($sValeur) . ')';
    }
}