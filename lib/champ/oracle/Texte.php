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
        return "TO_CLOB(:$this->sColonne)";
    }
    public function mGetValeur($mValeur)
    {
        return stream_get_contents($mValeur);
    }
}