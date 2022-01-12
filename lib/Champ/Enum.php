<?php

namespace APP\Modules\Base\Lib\Champ;

class Enum extends Char
{
    protected array $aLibelle;

    public function oSetLibelles(array $aLibelle)
    {
        $this->aLibelle;
    }

    /**
     * Ajoute ce champ à une clause select de façon appropriée pour son type
     * @param $sAliasColonne
     * @return string
     */
    public function sGetSelect($sAliasColonne = '')
    {
        return Champ::$rConnexion->sGetClauseCase($this->sAlias . '.' . $this->sColonne , $this->aLibelle). ' ' .  ($sAliasColonne ?: $this->sNom);
    }

    public function sGetFormatAffichage()
    {
        return $this->sAlias . '.' . $this->sColonne . ' ';
    }

}