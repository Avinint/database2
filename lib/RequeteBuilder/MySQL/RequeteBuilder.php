<?php

namespace APP\Modules\Base\Lib\RequeteBuilder\MySQL;

use APP\Modules\Base\Lib\Champ\Champ;
use APP\Modules\Base\Lib\Champ\Oracle\CleEtrangere;
use APP\Modules\Base\Lib\Mapping;
use APP\Modules\Base\Lib\RequeteBuilder\RequeteBuilderInterface;
use APP\Ressources\Base\Lib\Exception\ChampInexistantException;

class RequeteBuilder implements RequeteBuilderInterface
{
    protected $aSelect  = [];
    protected $bDistinct = false;
    protected $sFrom = '';
    protected Mapping $oMapping;
    protected $aJoins = [];
    protected $sWhere = '';
    protected $aGroupBy = [];
    protected $aOrderBy = [];
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

    public function oDistinct(bool $distinct = true) : RequeteBuilderInterface
    {
        $this->bDistinct = $distinct;

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
    protected function sAjouterSelect($sNomChamp, $sAliasChamp = '') : string
    {
        if (is_array($sNomChamp)) {
            [$sNomChamp, $sAliasChamp] = $sNomChamp;
        }

        $oChamp = $this->oGetChamp($sNomChamp) ?? null;

        if ($oChamp instanceof Champ) {
            return $oChamp->sGetSelect($sAliasChamp);
        }

        return $sNomChamp;
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

        if ($oChamp instanceof Champ) {
            $sNomColonne = $oChamp->sGetColonne();
            $sNomClePrimaire = $sNomClePrimaire ? $this->oGetChamp($sNomClePrimaire)->sGetColonne() : $sNomColonne;
        } else {
            throw new ChampInexistantException($sNomChamp, $this->sGetClasseMapping());
        }

        $sAlias = $sAlias ?: $this->oMapping->sGetAlias();

        if ($sRestriction) {
            $sRestriction = ' '. $sRestriction;
        }

        $this->aJoins[] = "$sType JOIN $sTable $sAliasJointure ". PHP_EOL . $this->sIndentation  ."ON {$sAlias}.$sNomColonne = {$sAliasJointure}.{$sNomClePrimaire}{$sRestriction}";

        return $this;
    }

    /**
     * Ajoute un ou plusieurs champs à une clause GROUP BY
     * @param $mGroupBy
     * @return RequeteBuilderInterface
     */
    public function oGroupBy($mGroupBy = '') : RequeteBuilderInterface
    {
        if (!empty($mGroupBy)) {
            if (is_array($mGroupBy)) {
                $this->aGroupBy = array_flip(array_flip(array_merge(
                    $this->aGroupBy,
                    array_map(function($sGroupBy) {return $this->sParseGroupBy($sGroupBy);}, $mGroupBy)
                )));
            } else {
                $this->aGroupBy[] = $this->sParseGroupBy($mGroupBy);
            }
        }

        return $this;
    }

    /**
     * Remplace un champ ajouté au GROUP BY par la colonne préfixée de l'alias de table
     * @param $sGroupBy
     * @return mixed|string
     */
    public function sParseGroupBy($sGroupBy)
    {
        if ($oChamp = $this->oGetChamp($sGroupBy)) {
            $sGroupBy = $oChamp->sGetColonnePrefixee();
        }

        return $sGroupBy;
    }

    public function oLimit($nStart, $nNbElements) : RequeteBuilderInterface
    {
        $this->nStart      = $nStart;
        $this->nNbElements = $nNbElements;

        return $this;
    }

    /** Initialise la liste des ORDER BY. Si des ORDER BY ont déja été ajoutés ils sont effacés
     * @param $sOrderBy
     * @return RequeteBuilderInterface
     */
    public function oInitOrderBy($sOrderBy = '') : RequeteBuilderInterface
    {
        if (empty($sOrderBy)) {
            $sOrderBy = $this->oMapping->sGetOrderBy();
        }

        if (is_array($sOrderBy)) {
            $this->aOrderBy = array_map(function ($sUnOrderBy) { return $this->sParseOrderBy($sUnOrderBy); }, $sOrderBy);
        } else {
            $this->aOrderBy = [$this->sParseOrderBy($sOrderBy)];
        }

        return $this;
    }

    /** Permet d'ajouter des ORDER BY à la liste
     * @param $sOrderBy
     * @return $this
     */
    public function oOrderBy($sOrderBy = '')
    {
        if (is_array($sOrderBy)) {
            $this->aOrderBy = array_flip(array_flip(array_merge($this->aOrderBy, array_map(function ($sUnOrderBy) { return $this->sParseOrderBy($sUnOrderBy); }, $sOrderBy))));
        } else {
            $this->aOrderBy[] = $this->sParseOrderBy($sOrderBy);
        }

        return $this;
    }

    /**
     * Remplace les champs ajoutés à la clause ORDER BY par des colonnes préfixées des alias de table
     *
     * @param $sOrderBy
     * @return string
     */
    public function sParseOrderBy($sOrderBy)
    {
        [$sColonne, $sOrdre] = explode(" ", $sOrderBy) + ["", "DESC"];

        if ($oChamp = $this->oGetChamp($sColonne)) {
            $sColonne = $oChamp->sGetColonnePrefixee();
        }

        return $sColonne . ' ' . $sOrdre;
    }

    protected function sGetSelect()
    {
        return 'SELECT '. ($this->bDistinct ?  'DISTINCT ' : '') . (empty($this->aSelect) ? ' * ' : implode(',' . PHP_EOL . $this->sIndentation, $this->aSelect));
    }

    protected function sGetFrom()
    {
        return  PHP_EOL . $this->sIndentation . 'FROM ' . $this->sFrom;
    }

    protected function sGetJoins()
    {
        return PHP_EOL . $this->sIndentation .  implode(PHP_EOL. $this->sIndentation, $this->aJoins);
    }

    protected function sGetOrderBy()
    {
        if (empty($this->aOrderBy)) {
            return '';
        }

        return PHP_EOL .  $this->sIndentation  .  'ORDER BY ' . implode(',' . PHP_EOL. $this->sIndentation, $this->aOrderBy);
    }

    protected function sGetGroupBy()
    {
        if (empty($this->aGroupBy)) {
            return '';
        }

        return PHP_EOL . $this->sIndentation .  'GROUP BY ' . implode(', ', $this->aGroupBy);
    }

    public function __toString()
    {
        return $this->sGetSelect()
            . $this->sGetFrom()
            . $this->sGetJoins()
            . $this->sGetWhere()
            . $this->sGetGroupBy()
            . $this->sGetOrderBy()
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

    public function oWhere($sCriteres)
    {
        $this->sWhere .= $sCriteres ? PHP_EOL . $this->sIndentation .  $sCriteres : '';

        return $this;
    }

    public function sGetWhere()
    {
        return $this->sWhere;
    }

    public function sGetAlias()
    {
        return $this->sAlias;
    }

    /**
     * @param string $sChamp
     * @return Champ|null
     */
    public function oGetChamp(string $sChamp) : ?Champ
    {
        return $this->oMapping[$sChamp] ?? null;
    }


}