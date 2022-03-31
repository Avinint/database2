<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class CleEtrangere extends Nombre
{
    private $sTableCible;
    private $sAliasTableCible;
    private $sNomClePrimaire;

    public function __construct($sColonne, $sTableCible, $sAliasTableCible, $sAlias = null, $sNomClePrimaire = '')
    {
        $this->sColonne = $sColonne;
        $this->sAlias   = $sAlias;
        $this->sTableCible = $sTableCible;
        $this->sAliasTableCible = $sAliasTableCible;
        $this->sNomClePrimaire = $sNomClePrimaire;
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

    public function sGetClePrimaire()
    {
        return $this->sNomClePrimaire;
    }

}