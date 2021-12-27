<?php

namespace APP\Modules\Base\Lib;

use APP\Modules\Base\Lib\Champ\Champ;

abstract class Mapping extends \ArrayObject
{
    public $sNomChampId;
    public $sNomCle;

    public function sGetColonneAliasee($sCle)
    {
        return ($this[$sCle]->sGetAlias() ?? $this->sGetAlias()) . '.' . ($this[$sCle]->sGetColonne() ?? $sCle);
    }

    public function __construct($array = [])
    {
        parent::__construct($this->aAjouter($array));
    }

    /**
     *  On ajoute l'alias de la table si un alias spécial n'a pas été spécifié et on transmet son nom au champ (clé en camelcase dans mapping)
     * @param array $aChamp
     * @return Champ[]
     */
    protected function aAjouter(array $aChamp) : array
    {
        $aRetour = [];
        foreach ($aChamp as $sCle => $oChamp) {
            /** @var Champ $oChamp */
            $aRetour[$sCle] = $oChamp
                ->oSetNom($sCle)
                ->oSetAliasParDefaut($this->sAlias);
        }

        return  $aRetour;
    }


    public function sGetAlias()
    {
        return $this->sAlias;
    }

    public function sNomTable()
    {
        return $this->sNomTable;
    }

    public function sNomSequence()
    {
        return $this->sNomSequence;
    }
}