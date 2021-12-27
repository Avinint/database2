<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CriterePartiel extends Critere
{
    public function __construct($oChamp, $mValeur)
    {
        $mValeur = $oChamp->bActifDansRecherche($mValeur) ?  $oChamp->sGetValeurEnregistree("%$mValeur%") : null;

        parent::__construct($oChamp, $mValeur, 'LIKE');
    }

}