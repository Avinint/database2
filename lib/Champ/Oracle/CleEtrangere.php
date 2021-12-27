<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class CleEtrangere extends Nombre
{
    private $sTableCible;

    public function __construct($sColonne, $sTableCible = null, $sAliasTableCible = null, $sAlias = null)
    {
        $this->sColonne = $sColonne;
        $this->sAlias   = $sAlias;
        $this->sTableCible = $sTableCible;
        $this->sAliasTableCible = $sAliasTableCible;
    }


}