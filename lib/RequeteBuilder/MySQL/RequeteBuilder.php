<?php

namespace APP\Modules\Base\Lib\RequeteBuilder\MySQL;

use APP\Modules\Base\Lib\Champ\Champ;
use APP\Modules\Base\Lib\Champ\Oracle\CleEtrangere;
use APP\Modules\Base\Lib\RequeteBuilder\RequeteBuilderInterface;

class RequeteBuilder implements RequeteBuilderInterface
{
    protected $aSelect  = [];
    protected $sFrom = '';
    protected $oMapping;
    protected $aJoins = [];
    protected $sWhere = '';
    protected $sGroupBy = '';
    protected $sOrderBy = '';
    protected $sHaving = '';
    protected $nStart = 1;
    protected $nNbElements = 0;
    protected $sIndentation = '';

    public function __construct($oMapping)
    {
        $this->oMapping = $oMapping;
        $this->sFrom = "{$this->oMapping->NomTable()} {$this->oMapping->sGetAlias()}";
    }


    public function oSelect(array $aChamps = []) : RequeteBuilderInterface
    {
        if (!empty($aChamps)) {
            foreach ($aChamps as $sUnChamp) {
                $this->aSelect[] = $this->sAjouterSelect($sUnChamp);
            }
        }

        return $this;
    }

    public function oSelectCount() : RequeteBuilderInterface
    {
        $this->aSelect[] =  'COUNT(*) AS "nNbElements"';

        return $this;
    }

    public function oConcat(array $aElementConcat, $sAlias, $sDelimiteur = '') : RequeteBuilderInterface
    {
        $aChamp = [];
        foreach ($aElementConcat as $sUnElement) {
            $oChamp = $this->oGetChamp($sUnElement) ?? null;
            $aChamp[] = isset($oChamp) ? "{$oChamp->sGetFormatAffichage()}" : "'$sUnElement'";
        }

        $this->aSelect[] =  "CONCAT_WS('$sDelimiteur', " . implode(', ', $aChamp) . ') ' . $sAlias;

        return $this;
    }

    /**
     * @param $sUnChamp
     * @param $sAliasChamp
     * @param array $aSelect
     * @return string
     */
    private function sAjouterSelect($sUnChamp, $sAliasChamp = '') : string
    {
        if (is_array($sUnChamp)) {
            [$sUnChamp, $sAliasChamp] = $sUnChamp;
        }

        $oChamp = $this->oGetChamp($sUnChamp);
        if (!$oChamp instanceof Champ) {
            $sNomMapping = $this->sGetClasseMapping();
            $sNomModele = str_replace('Mapping', '', $sNomMapping);
            throw new \Exception("Utilisation du Champ \"$sUnChamp\" inexistant dans $sNomMapping comme parametre de oSelect dans $sNomModele");
            return '';
        }

        return $this->oGetChamp($sUnChamp)->sGetSelect($sAliasChamp);

    }

    protected function sGetClasseMapping()
    {
        $aNamespace = explode('\\', get_class($this->oMapping));
        return end($aNamespace);
    }

    /**
     * Ajoute manuellement un select sous forme de texte pour les cas plus complexe
     * @param string $sSelect
     * @return void
     */
    public function oAjouterSelectTexte(string $sSelect) : RequeteBuilderInterface
    {
        $this->aSelect[] = $sSelect;

        return $this;
    }

    /**
     *  Permet d'ajouter manuellement une jointure plus complexe
     * @param string $sJointure
     * @return void
     */
    public function oAjouterJointure(string $sJointure) : RequeteBuilderInterface
    {
        $this->aJoins[] = $sJointure;

        return $this;
    }


    /** Génération de LEFT JOIN
     * @param $sTable
     * @param $sNomChamp
     * @param $sAlias
     * @param $sAliasJointure
     * @param $sClePrimaire
     * @param $sNomClePrimaire
     * @param $sRestriction
     * @return $this
     */
    public function oLeftJoin($sNomChamp, $sTable = '', $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface
    {
        return $this->oJoin('LEFT', $sNomChamp, $sTable, $sAliasJointure, $sAlias, $sNomClePrimaire, $sRestriction);
    }

    public function oRightJoin($sNomChamp, $sTable = '', $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface
    {
        return $this->oJoin('RIGHT', $sNomChamp, $sTable, $sAliasJointure, $sAlias, $sNomClePrimaire, $sRestriction);
    }

    public function oInnerJoin($sNomChamp, $sTable = '', $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface
    {
        return $this->oJoin('INNER', $sNomChamp, $sTable, $sAliasJointure, $sAlias, $sNomClePrimaire, $sRestriction);
    }

    /** Génération de jointures
     * @param $sTable
     * @param $sNomChamp
     * @param $sAlias
     * @param $sAliasJointure
     * @param $sClePrimaire
     * @param $sNomClePrimaire
     * @param $sRestriction
     * @return $this
     */
    public function oJoin($sType, $sNomChamp, $sTable = '', $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface
    {
        $oChamp = $this->oGetChamp($sNomChamp);
        if ($oChamp instanceof CleEtrangere && empty($sTable)) {
            $sTable = $oChamp->sGetTableCible();
            $sAliasJointure = $oChamp->sGetAliasTablecible();
        }
        $sNomColonne = $oChamp->sGetColonne();
        $sNomClePrimaire = $sNomClePrimaire ? $this->oGetChamp($sNomClePrimaire)->sGetColonne() : $sNomColonne;
        $sAlias = $sAlias ?: $this->oMapping->sGetAlias();

        if ($sRestriction) {
            $sRestriction = ' '. $sRestriction;
        }

        $this->aJoins[] = "$sType JOIN $sTable $sAliasJointure ". PHP_EOL . $this->sIndentation  ."ON {$sAlias}.$sNomColonne = {$sAliasJointure}.{$sNomClePrimaire}{$sRestriction}";

        return $this;
    }

    public function oGroupBy($mGroupBy = '') : RequeteBuilderInterface
    {
        if (!empty($mGroupBy)) {
           $this->sGroupBy =  PHP_EOL . $this->sIndentation .  'GROUP BY ' . is_array($mGroupBy) ? implode(', ', $mGroupBy) : $mGroupBy;
        }

        return $this;
    }

    public function oLimit($nStart, $nNbElements) : RequeteBuilderInterface
    {
        $this->nStart      = $nStart;
        $this->nNbElements = $nNbElements;

        return $this;
    }

    public function oOrderBy($sOrderBy = '') : RequeteBuilderInterface
    {
        if (!empty($sOrderBy)) {
            $sOrderBy = is_array($sOrderBy) ? implode(', ', $sOrderBy) : $sOrderBy;
        } else {
            $sOrderBy = $this->oMapping->sNomCle . ' DESC';
        }

        $this->sOrderBy = PHP_EOL  . $this->sIndentation . 'ORDER BY ' . $sOrderBy;

        return $this;
    }

    protected function sGetSelect()
    {
        return 'SELECT ' . (empty($this->aSelect) ? ' * ' : implode(',' . PHP_EOL . $this->sIndentation, $this->aSelect));
    }

    protected function sGetFrom()
    {
        return  PHP_EOL . $this->sIndentation . 'FROM ' . $this->sFrom;
    }

    protected function sGetJoins()
    {
        return PHP_EOL . $this->sIndentation .  implode(PHP_EOL. $this->sIndentation, $this->aJoins);
    }

    public function __toString()
    {
        return $this->sGetSelect()
            . $this->sGetFrom()
            . $this->sGetJoins()
            . $this->sWhere
            . $this->sGroupBy
            . $this->sOrderBy
            . $this->sHaving
            . $this->sPagination();
    }

    /**
     * Ajoute le LIMIT et l'OFFSET à la requête
     * @param int $nNbElements
     * @param int $nStart
     * @param string $szRequete
     * @return string
     */
    private function sPagination()
    {
        if ($this->nNbElements > 0) {
            return PHP_EOL. $this->sIndentation . ' LIMIT ' . $this->nStart . ', ' . $this->nNbElements;
        }
        return '';
    }

    /**
     * @param array $sUnChamp
     * @param $sAliasChamp
     * @return array
     */
//    private function aAjouterChampAvecAlias(array $sUnChamp): array
//    {
//        [$sUnChamp, $sAliasChamp] = $sUnChamp;
//
//        return [$sUnChamp, '"' . $sAliasChamp. '"'];
//    }

    public function oWhere($sCriteres)
    {
        $this->sWhere .= PHP_EOL . $this->sIndentation .  $sCriteres;

        return $this;
    }

    public function sGetAlias()
    {
        return $this->sAlias;
    }

    /**
     * @param $sChamp
     * @return Champ|null
     */
    public function oGetChamp($sChamp) : ?Champ
    {

        return $this->oMapping[$sChamp] ?? null;
    }
}