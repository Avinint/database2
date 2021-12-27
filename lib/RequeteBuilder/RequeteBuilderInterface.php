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
    function oLeftJoin($sTable, $sNomChamp, $sAliasJointure = '', $sAlias = '', $sNomClePrimaire = '', $sRestriction = '');
    function oRightJoin($sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '');
    function oInnerJoin($sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '');
    function oJoin($sType, $sTable, $sNomChamp, $sAliasJointure, $sAlias = '', $sNomClePrimaire = '', $sRestriction = '');

    function oGroupBy($mGroupBy = '');
    function oLimit($nStart, $nNbElements);
    function oOrderBy($sOrderBy = '');
    function __toString();
    function sGetAlias();
}