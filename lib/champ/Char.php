<?php

namespace APP\Modules\Base\Lib\Champ;

class Char extends Champ
{
    /**
     * Ajoute ce champ à une clause select de façon appropriée pour son type
     * @param $sAliasColonne
     * @return string
     */
    public function sGetSelect($sAliasColonne = '')
    {
        return $this->sAlias . '.' . $this->sColonne . ' ' .  ($sAliasColonne ?: $this->sNom);
    }

    public function sGetFormatAffichage()
    {
        return $this->sAlias . '.' . $this->sColonne . ' ';
    }
}