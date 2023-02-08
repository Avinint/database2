<?php

namespace APP\Modules\Base\Lib;

use APP\Modules\Base\Lib\Champ\Champ;

class CorePDOOracle extends CorePDO
{
    public $cTypeRequeteBuilder = '\APP\Modules\Base\Lib\RequeteBuilder\Oracle\RequeteBuilder';


    /**
     * @param array $aChamps
     * @param array $aChampsNull
     * @return bool
     * @throws \Exception
     */
    public function bInsert(Mapping $oMapping, $aChamps = [], $aChampsNull = [])
    {
        ['sRequete' => $sRequete, 'aChampsPrepare' => $aChampPrepare] = $this->aPreparerChampsPlaceHolderRequeteInsert($oMapping, $aChamps, $aChampsNull);
        $sRequete = "INSERT INTO {$oMapping->sNomTable()} (" .$sRequete . ')';
        $oRequetePrepare = $this->oPreparerRequete($sRequete);



//        var_dump($sRequete);
//        var_dump($aChampPrepare);
//        die();
        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aChampPrepare);

        return $bRetour;
    }

    /**
     * Ajoute le LIMIT et l'OFFSET à la requête
     * @param int $nNbElements
     * @param int $nStart
     * @param string $szRequete
     * @return string
     */
    public function sPaginerRequete($nNbElements, $nStart, $szRequete)
    {
        if ($nNbElements > 0) {

            $szRequete = '
    SELECT *
    FROM (
        SELECT tmp.*, rownum
        FROM
        (' .
            $szRequete . '
        ) tmp
        WHERE rownum <= ' . $nNbElements . '
    ) WHERE rownum > ' . $nStart . '
    ';
        }

        return $szRequete;
    }

//    public function bInsert($aChamps, $aChampsNull, $sNomChampId)
//    {
//        $bRetour = false;
//
//
//        $aPreparationRequete = $this->aPreparerChampsPlaceHolderRequete($aChamps, $aChampsNull);
//
//        $sRequete =  $aPreparationRequete['sRequete'];
//
//        $oRequetePrepare = $this->oPreparerRequete($sRequete);
//
//        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aPreparationRequete['aChampsPrepare']);
//
//        if ($bRetour === false) {
//            $this->vLog('critical', $sRequete);
//            $this->vLog('critical', '<pre>' . print_r($aPreparationRequete['aChampsPrepare'], true) . '</pre>');
//            $this->vLog('critical', 'sMessagePDO ----> ' . $this->sMessagePDO);
//            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
//        } else {
//            $sNomChampId = $this->sGetNomChampId();
//            $this->$sNomChampId = $this->getLastInsertId();
//            if ($this->bRessourceLogsPresente() && $this->sNomTable != 'logs') {
//                $this->bSetLog("insert_{$this->sNomTable}", $this->$sNomChampId);
//            }
//        }
//
//        return $bRetour;
//    }

    protected function aPreparerChampsPlaceHolderRequeteInsert($oMapping, $aChamps = [], $aChampsNull = [])
    {
        $aRetour = array(
            'sRequete' => '',
            'aChampsPrepare' => array()
        );

        $aColonne = [];
        $aValeur = [];

        foreach ($aChamps as $sUnChamp => $sUneValeur) {
            $oChamp = $oMapping[$sUnChamp];
            //var_dump($oChamp);
            $aColonne[] = $oChamp->sGetColonne();
            //$sClob = '';
            //if (is_string($sUneValeur) && strlen($sUneValeur) > 4000){
            $aValeur[] = $oChamp->sGenererPlaceholderChampPrepare();
            if (is_a($oChamp, '\APP\Modules\Base\Lib\Champ\Oracle\Texte')) {
                // for($i = 0; $i < strlen($sUneValeur)/4000; $i++ ){
                //     $sClob .= 'TO_CLOB(\''.substr($sUneValeur,$i*4000 + ($i * 1),($i+1)*4000).'\') || ';
                // }
                // $sClob = substr($sClob,0,strlen($sClob) - 4);
                // $aValeur[] = "$sClob";
                $sValeurFormatee = $oChamp->sFormatterValeurSQL($sUneValeur);
                $aRetour['aChampsPrepare'][":{$oChamp->sGetColonne()}"] = array(
                    'sValeur' => $sValeurFormatee
                    ,'nMaxSize' => strlen($sValeurFormatee)
                );
            } else {
                $aRetour['aChampsPrepare'][":{$oChamp->sGetColonne()}"] = $oChamp->sFormatterValeurSQL($sUneValeur);
            }
        }

        if ($aChampsNull) {

            foreach ($aChampsNull as $sUnChamp) {
                $oChamp = $oMapping[$sUnChamp];
                $aColonne[] = $oChamp->sGetColonne();
                $aValeur[] = "NULL";
            }
        }

        $aRetour['sRequete'] = implode(', ', $aColonne) . ') VALUES(' . implode(', ', $aValeur);
        return $aRetour;
    }

    /**
     * Prépare les champs et la requête placeholder pour une requête
     * préparé
     *
     * @param array $aChamps Champs avec des valeurs non nulles
     * @param array $aChampsNull Champs avec des valeurs nulles
     *
     * @return array[]
     * @return array['aChampsPrepares'] string[] Les champs préparés
     * @return array['sRequete'] string La partie de la requête avec les placeholders (SET machin = :valeur_machin)
     */
    protected function aPreparerChampsPlaceHolderRequete($oMapping, $aChamps = array(), $aChampsNull = array())
    {
        $aRetour = array(
            'sRequete' => '',
            'aChampsPrepare' => array()
        );

        $aLignes = array();
        /**
         * Dans le parcours des champs on prépare
         * les lignes dans la requête SQL contenant
         * les placeholders (:nomchamp) et on prépare en
         * même temps les valeurs de ces placeholders
         * qui seront attribué dans le aPreparerRequete
         */
        foreach ($aChamps as $sUnChamp => $sUneValeur) {
            /** @var Champ $oChamp */
            $oChamp = $oMapping[$sUnChamp];

            if (is_string($sUneValeur) && strpos($sUneValeur, ':') === 0) {
                $aLignes[] = "{$oChamp->sGetColonne()} = " . str_replace(':', '', $sUneValeur);
            } else {
                $sClob = '';
                $aColonne[] = $oChamp->sGetColonne();
                $aLignes[] = "{$oChamp->sGetColonne()} = {$oChamp->sGenererPlaceholderChampPrepare()}";
                if (is_a($oChamp, '\APP\Modules\Base\Lib\Champ\Oracle\Texte')) {
                    // for($i = 0; $i < strlen($sUneValeur)/4000; $i++ ){
                    //     $sClob .= 'TO_CLOB(\''.substr($sUneValeur,$i*4000 + ($i * 1),($i+1)*4000).'\') || ';
                    // }
                    // $sClob = substr($sClob,0,strlen($sClob) - 4);
                    // $aValeur[] = "$sClob";
                    $sValeurFormatee = $oChamp->sFormatterValeurSQL($sUneValeur);
                    $aRetour['aChampsPrepare'][":{$oChamp->sGetColonne()}"] = array(
                        'sValeur' => $sValeurFormatee
                        ,'nMaxSize' => strlen($sValeurFormatee)
                    );
                // if (is_string($sUneValeur) && strlen($sUneValeur) > 4000){
                //     for($i = 0; $i < strlen($sUneValeur)/4000; $i++ ){
                //         $sClob .= 'TO_CLOB(\''.substr($sUneValeur,$i*4000 + ($i * 1),($i+1)*4000).'\') || ';
                //     }
                //     $sClob = substr($sClob,0,strlen($sClob) - 4);
                //     $aLignes[] = "{$oChamp->sGetColonne()} = $sClob";
                }else{
                    
                    $aRetour['aChampsPrepare'][":{$oChamp->sGetColonne()}"] = $oChamp->sFormatterValeurSQL($sUneValeur);
                }
            }

        }

        if ($aChampsNull) {
            foreach ($aChampsNull as $sUnChamp) {
                $oChamp = $oMapping[$sUnChamp];
                $aLignes[] = "{$oChamp->sGetColonne()} = NULL";
            }
        }

        $aRetour['sRequete'] = implode(', ', $aLignes);
        return $aRetour;
    }

    /**
     * Récupère le dernier id inséré
     * @param $sNomSequence
     * @return false|mixed|string
     */
    public function nGetLastInsertId($sNomSequence = '')
    {
        $sRequete = 'SELECT '.$sNomSequence . '.CURRVAL AS "nIdElement" FROM dual';
        $aElement = $this->aSelectBDD($sRequete);

        $aUneData = $aElement[0] ?? null;

        return $aUneData->nIdElement;
    }

    /**
     * Conversion de date pour inclure dans une
     * requête SQL.
     * @param  string $sDate   Date à utiliser.
     * @param  string $sFormat Format de la date au final.
     * @return string          Chaine à inclure dans le SQL.
     */
    public function sDateFormat($sDate, $sFormat)
    {

        return 'TO_CHAR(' . $this->quote($sDate) . ', \'' . $sFormat . '\')';
    }

    /**
     * Retourne la fonction de base de données
     * récupérant le datetime courant.
     *
     * @return string Fonction.
     */
    public function sDatetimeCourant()
    {
        return 'SYSDATE';
    }

    /**
     * Concatène des chaines pour inclure dans une
     * requête SQL via Oracle.
     * @param  array $aChaine Chaines à concaténer.
     * @return string         Chaine à inclure dans le SQL.
     */
    public function sConcat($aChaine)
    {
        $aChaine = array_map(function($sChaine)
        {
            if (preg_match('/TO_CHAR|\./', $sChaine)) {
                return $sChaine;
            } else {
                return "'" . $sChaine . "'";
            }
        }, $aChaine);

        return implode(" || ", $aChaine);
    }



    public function aListeTables()
    {
        $aTables = array();

        $sRequete = "SELECT TABLE_NAME sNom FROM SYS.ALL_TABLES WHERE OWNER = '" . $GLOBALS['aParamsBdd']['utilisateur'] . "' ORDER BY TABLE_NAME";

        $aResultats = $this->aSelectBDD($sRequete);

        foreach ($aResultats as $oTable) {

            $aTables[$oTable->sNom] = array();

            $sRequete = "
                SELECT COL.COLUMN_ID,
                       COL.COLUMN_NAME sNom,
                       COL.DATA_TYPE sType,
                       COL.DATA_LENGTH sMaxLength,
                       COL.DATA_PRECISION nPrecision,
                       COL.DATA_SCALE nScale,
                       COL.NULLABLE bNullable
                FROM SYS.ALL_TAB_COLUMNS COL INNER JOIN SYS.ALL_TABLES T ON COL.OWNER = T.OWNER AND COL.TABLE_NAME = T.TABLE_NAME
                WHERE COL.OWNER = '" . $GLOBALS['aParamsBdd']['base'] . "'
                  AND COL.TABLE_NAME = '".$oTable->sNom ."'
                ORDER BY COL.COLUMN_ID";

            $aResultats = $this->aSelectBDD($sRequete);

            foreach ($aResultats as $oChamp) {
                $aNom = explode('_', $oChamp->sNom);
                $aNom = array_map('ucfirst', $aNom);
                $sNom = implode('', $aNom);

                switch ($oChamp->sType) {
                    case 'NUMBER':
                        if ($oChamp->nSCale > 0) {
                            $oChamp->sChamp = 'f' . $sNom;
                        } elseif ($oChamp->nPrecision == 1) {
                            $oChamp->sChamp = 'b' . $sNom;
                        } else {
                            $oChamp->sChamp = 'n' . $sNom;
                        }

                        $oChamp->sMaxLength = $oChamp->nPrecision + $oChamp->nScale + 1;
                        break;
                    case 'VARCHAR2':
                    //TEXT
                    case 'CLOB':
                    case 'NCLOB':
                        $oChamp->sChamp = 's'.$sNom;
                        break;
                    case 'TIMESTAMP(6)':
                    case 'DATE':
                        $oChamp->sChamp = 'd'.$sNom;
                        break;
                    case 'BINARY_DOUBLE':
                        $oChamp->sChamp = 'f'.$sNom;
                        $oChamp->sMaxLength = $oChamp->nPrecision + $oChamp->nScale + 1;
                        break;

                    default:
                        break;
                }

                $aTables[$oTable->sNom][$oChamp->sNom] = $oChamp;
            }

        }

        // echo "<pre>".print_r($aTables, true)."</pre>";

        return $aTables;

    }

    /**
     * Requête de selection pour dynamiser un select2
     * @oracle
     *
     * @param array $aRecherche     Critères de recherche
     * @param array $aChamps        Champs sur lesquelles effectuer la recherche
     * @param string $sTable        Table sur laquelle effectuer la recherche
     * @param string $sRestriction  Restriction venant compléter les critères de recherche
     *
     * @return array $aResultats    Tableau de résultats
     */
    public function aGetSelect2JSON($aRecherche = array(), $aChamps = array(), $sTable = '', $sOrderBy = '', $sRestriction = '', $aMappingChamps = [])
    {
        if ($sOrderBy == '') {
            $sOrderBy = $aChamps[1];
        }

        $szRequete = "
			SELECT ".$aChamps[0].' AS "id", '.$aChamps[1].' AS "text"
			FROM '.$sTable."
            WHERE 1=1 AND INSTR(LOWER(REPLACE(".$aChamps[1].",'-', ' ')), LOWER(" . $this->quote( $aRecherche['sTerm'] ) . ")) > 0 
            OR INSTR( LOWER(REPLACE(".$aChamps[1].",' ', '-')), LOWER(" . $this->quote( $aRecherche['sTerm']) . ")) > 0".$sRestriction."
			ORDER BY ".$sOrderBy." ASC
        ";
        // echo $szRequete."\r\n";

        $aResultats = $this->aSelectBDD($szRequete, $aMappingChamps);

        return $aResultats;
    }

}