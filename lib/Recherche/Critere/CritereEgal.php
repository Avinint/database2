<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereEgal extends Critere
{
    public function __construct(Champ $oChamp, $mValeur)
    {
        $mValeur = $oChamp->bActifDansRecherche($mValeur) ? $oChamp->sGetValeurEnregistree($mValeur) : null;

        parent::__construct($oChamp, $mValeur);
    }

}