<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere\Oracle;

use APP\Modules\Base\Lib\Champ\Champ;
use APP\Ressources\Base\Lib\Recherche\Critere\Critere;

class CriterePartiel extends Critere
{
    public function __construct($oChamp, $mValeur, $sOperateur = 'LIKE', $sOperateurLogique = 'AND')
    {
        $mValeur = $oChamp->bEstRenseigne($mValeur) ?  $this->sTraiterValeur($oChamp,"%$mValeur%") : null;
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

    /**
     * Transformation de la clé avant ajout dans le critère
     * @param Champ $oChamp
     * @return string
     */
    protected static function sGenererCle(Champ $oChamp)
    {
        return 'LOWER(' . $oChamp->sGetColonnePrefixee().')';
    }

}