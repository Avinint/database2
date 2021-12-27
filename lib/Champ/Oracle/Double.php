<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Double extends Nombre
{
    public function sGetValeurEnregistree($sValeur) : string
    {
       return (float)(str_replace(',', '.', $sValeur));
    }

    public function sGetSelect($sAliasColonne = '')
    {
        return $this->sGetFormatAffichage(). ' "' .  ($sAliasColonne ? : $this->sNom) . '"';
    }

    public function sFormatterValeurSQL($mValeur)
    {
        return $this->sGetValeurEnregistree($mValeur);
    }



    public function sGetFormatAffichage()
    {
        return 'REPlACE(' . $this->sAlias . '.' . $this->sColonne . ", '.', ',') ";
    }
}