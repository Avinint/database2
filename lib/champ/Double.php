<?php

namespace APP\Modules\Base\Lib\Champ;

class Double extends Nombre
{
    public function sTraiterValeur($sValeur) : string
    {
       return (float)(str_replace(',', '.', $sValeur));
    }

    public function sGetSelect($sAliasColonne = '')
    {
        return $this->sGetFormatAffichage(). ($sAliasColonne ? : $this->sNom);
    }

    public function sGetFormatAffichage()
    {
        return 'REPlACE(' . $this->sAlias . '.' . $this->sColonne . ", '.', ',') ";
    }

    public function sFormatterValeurSQL($mValeur)
    {
        return $this->sTraiterValeur($mValeur);
    }
}