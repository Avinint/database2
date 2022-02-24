<?php

namespace APP\Modules\Base\Lib\RequeteBuilder;

interface RequeteBuilderInterface
{
    function oSelect(array $aChamps = []);
    function oSelectCount();

    /**
     * Ajoute "manuellement" un select plus complexe
     * @param string $sSelect
     * @return void
     */
    function oAjouterSelectTexte(string $sSelect);
    function oAjouterJointure(string $sJointure);



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
    function oLeftJoin($sTable, $sNomChamp, $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface;
    function oRightJoin($sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface;
    function oInnerJoin($sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface;
    function oJoin($sType, $sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '') : RequeteBuilderInterface;

    function oGroupBy($mGroupBy = '') : RequeteBuilderInterface;
    function oLimit($nStart, $nNbElements) : RequeteBuilderInterface;
    function oOrderBy($sOrderBy = '') : RequeteBuilderInterface;
    function __toString();
    function sGetAlias();

    function oDistinct() : RequeteBuilderInterface;
}