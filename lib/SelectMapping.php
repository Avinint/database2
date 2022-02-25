<?php

namespace APP\Ressources\Base\Lib;

use APP\Modules\Base\Lib\Champ\Oracle\Char;

class SelectMapping extends Mapping
{
    protected $sNomTable;
    protected $sAlias = 'SEL';

    public $sNomChampId;
    public $sNomCle;
    public $sNomChampLibelle;

    public function __construct($sNomTable, $sColonneValeur, $mColonneLibelle, $aNomChamp = ['valeur' => 'id', 'libelle' => 'text'], $sTriePar = '')
    {
        $this->sNomTable = $sNomTable;

        $this->sNomChampId = $aNomChamp['valeur'];
        $this->sNomChampLibelle = $aNomChamp['libelle'];
        $this->sNomCle = $sColonneValeur;

        $aMapping  = [
            $this->sNomChampId => new Char($sColonneValeur),
            $this->sNomChampLibelle => new Char($mColonneLibelle),
        ];

        $this->sOrderBy = $sTriePar ?: $aNomChamp['libelle'] . ' ASC';

        parent::__construct($aMapping);
    }
}