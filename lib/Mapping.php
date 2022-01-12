<?php

namespace APP\Modules\Base\Lib;

use APP\Modules\Base\Lib\Champ\Champ;
use APP\Modules\Base\Lib\Champ\Enum;

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

            if ($oChamp instanceof Enum) {
                $oChamp->oSetLibelles($GLOBALS['aModules'][$_REQUEST['szModule']]['conf']['aListe-' . $this->sGetModel() . '-' . $oChamp->getNom()]);
            }
        }

        return  $aRetour;
    }

    public function sGetModel()
    {
        return $this->sModel ?? str_replace('Mapping', '', (new ReflectionClass(get_called_class()))->getShortName());;
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