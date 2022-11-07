<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Texte extends Char
{
    public function sGetSelect($sAliasColonne = '')
    {
        return $this->sAlias . '.' . $this->sColonne  . ' "' .  ($sAliasColonne ?:  $this->sNom) . '"';
    }

    public function sGenererPlaceholderChampPrepare()
    {
        return ":$this->sColonne";
    }
    public function mGetValeur($mValeur)
    {
        if (is_resource($mValeur)) {
            $sRetour = stream_get_contents($mValeur);
        } else {
            $sRetour = $mValeur;
        }
        return $sRetour;
    }
}