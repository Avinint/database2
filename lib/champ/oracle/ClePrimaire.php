<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class ClePrimaire extends Nombre
{
    /**
     * Ajoute ce champ à une clause select de façon appropriée pour son type
     * @param $sAliasColonne
     * @return string
     */
    public function sGetSelect($sAliasColonne = '')
    {
        return $this->sAlias . '.' . $this->sColonne . ' "' .  ($sAliasColonne ?: $this->sNom) . '",'
            . PHP_EOL . $this->sAlias . '.' . $this->sColonne . ' "nIdElement"';
    }
}