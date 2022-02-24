<?php

namespace APP\Modules\Base\Lib\Champ;

class CleEtrangere extends Nombre
{
    private $sTableCible;
    private $sAliasTableCible;

    public function __construct($sColonne, $sTableCible, $sAliasTableCible, $sAlias = null)
    {
        $this->sColonne = $sColonne;
        $this->sAlias   = $sAlias;
        $this->sTableCible = $sTableCible;
        $this->sAliasTableCible = $sAliasTableCible;
    }

    public function sGetTableCible()
    {
        return $this->sTableCible;
    }

    public function sGetAliasTablecible()
    {
        return $this->sAliasTableCible;
    }

    public function oSetAliasTableCible($sAliasTableCible)
    {
        $this->sAliasTableCible = $sAliasTableCible;

        return $this;
    }
}