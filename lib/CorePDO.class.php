<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\Utiles;
use APP\Modules\Base\Lib\Champ\Champ;

class CorePDO extends \PDO
{
    public $sMessagePDO;
    private static $bExceptionSurContrainte;
    public $cTypeRequeteBuilder = '\APP\Modules\Base\Lib\RequeteBuilder\MySQL\RequeteBuilder';

    public function __construct($szBase = '', $szLogin = '', $szMotDePasse = '')
    {
        $this->sMessagePDO = '';
        parent::__construct($szBase, $szLogin, $szMotDePasse);
        if (isset(self::$bExceptionSurContrainte) === false) {
            self::$bExceptionSurContrainte = $GLOBALS['aModules']['base']['conf']['bExceptionSurContrainte'];
        }

    }

    public function query($szRequete = '')
    {
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';
        $mResultat = null;
        try {
            $mResultat = parent::query($szRequete);
        }
        catch (\PDOException $e) {
            $oUtiles = new Utiles;
            if (method_exists($oUtiles, 'vLogRequete')) {
                $oUtiles->vLogRequete($szRequete, true);
            }

            /**
             * Contient les infos sur l'erreur SQL :
             * [0] => Code d'erreur SQLSTATE (défini par rapport au standard ANSI SQL)
             * [1] => Code d'erreur du driver spécifique
             * [2] => Message d'erreur spécifique au driver
             * @var array $infosErreur
             */
            $sCodeErreur = $e->getCode();
            $this->sMessagePDO = $this->sGetMessagePDO($e);
            
        } finally {
            if (isset($e) === true) {
                if (self::$bExceptionSurContrainte === true) {
                    throw $e;
                } else {
                    switch ($sCodeErreur) {
                        // Rejouter ici des codes erreur SQLSTATE pour ne pas throw d'exceptions dans ces cas précis
                        case '23000': // Violation de contrainte d'intégrité
                            break;
                        default:
                            throw $e;
                            break;
                    }
                }
            }
            return $mResultat;
        }
    }

    /**
     * Exécution d'une requête préparée
     * 
     * @param string[] $aChampsPrepares Les champs préalablement préparé (ex :  [':nom'] = 'Michel')
     * 
     * @return boolean Vrai si la préparation à fonctionner, faux sinon
     */
    public function execute($aChampsPrepares = array())
    {
        try
        {
            return parent::execute($aChampsPrepares);
        }
        catch(\PDOException $e)
        {
            $oUtiles = new Utiles;
            if (method_exists($oUtiles, 'vLogRequete')) {
                $oUtiles->vLogRequete($szRequete, true);
            }

            $this->sMessagePDO = $this->sGetMessagePDO($e);
            return false;
        }
    }

    /**
     * Préparation de la requête SQL
     * 
     * @param string Requête à préparer
     * 
     * @return \PDOStatement Objet requête préparé PDO
     */
    public function prepare($szRequete = '', $aOptionsDriver = NULL)
    {
        try
        {
            return parent::prepare($szRequete);
        }
        catch(\PDOException $e)
        {
            $oUtiles = new Utiles;
            if (method_exists($oUtiles, 'vLogRequete')) {
                $oUtiles->vLogRequete($szRequete, true);
            }

            $this->sMessagePDO = $this->sGetMessagePDO($e);
            return false;
        }
    }


//    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
//    {
//        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';
//
//        return parent::aSelectBDD($szRequete);
//    }

    /**
     * Permet d'obtenir un message PDO correspondant à l'erreur SQL traduit si possible/gérer
     * 
     * @param \PDOException $oException L'exception PDO généré
     * 
     * @return string Le message PDO
     */
    protected function sGetMessagePDO($oException = null)
    {
        /**
         * Contient les infos sur l'erreur SQL :
         * [0] => Code d'erreur SQLSTATE (défini par rapport au standard ANSI SQL)
         * [1] => Code d'erreur du driver spécifique
         * [2] => Message d'erreur spécifique au driver
         * @var array $infosErreur
         */
        $infosErreur = parent::errorInfo();

        $sMessagePDO = "Erreur BDD inconnue";
        switch ($infosErreur[0]) {
            case "23000": {
                switch ($infosErreur[1]) {
                    //Contrainte d'unicité
                    case 1062 : {
                        $champFautif = str_replace('Duplicate entry ', '', $infosErreur[2]);
                        $champFautif = str_replace(' for key', '', $champFautif);

                        $sMessagePDO = "Impossible d'insérer ou de mettre à jour cet élément à cause d'un champ qui doit être unique. Champ posant problème : " . $champFautif;
                        break;
                    }
                    //Erreur de suppression à cause d'un RESTRICT sur une clé étrangère
                    case 1451 : {
                        $sMessagePDO = "Attention : cet élément est lié à un ou plusieurs autre(s). Il ne peut être supprimé.";
                        break;
                    }
                }
                break;
            }
            
            default: {
                if($oException !== null)
                {
                    $sMessagePDO = $oException->getMessage();
                }
            }
        }
        return $sMessagePDO;
    }



    /*============ Récup de BDD  Partie lecture==========================================*/


//    /**
//     * Ajoute le LIMIT et l'OFFSET à la requête
//     * @param int $nNbElements
//     * @param int $nStart
//     * @param string $szRequete
//     * @return string
//     */
//    public function sPaginerRequete($nNbElements, $nStart, $szRequete)
//    {
//        if ($nNbElements > 0) {
//            $szRequete .= ' LIMIT ' . $nStart . ', ' . $nNbElements;
//        }
//        return $szRequete;
//    }

    /**
     * Méthode qui sélectionne les éléments dans la base de données et retourne un tableau d'objets prêt à être utilisé
     *
     * @param  string   $szRequete      La requête SQL.
     * @param  array    $aMappingChamps Un tableau associatif avec comme clé les champs SQL et comme valeur les attributs à retourner.
     * @param  boolean  $bNoCache       Désactiver le cache pour certaines requêtes.
     *
     * @return array    $aResultat      Les résultats.
     */
    public function aSelectBDD($szRequete, $aMappingChamps = array(), $bNoCache = false)
    {
        $aResultat = array();

        // $bNoCache = false;
        if ($bNoCache === false) {
            if ((isset($GLOBALS['aParamsAppli']['cache']['base']) === false ||
                    $GLOBALS['aParamsAppli']['cache']['base'] == 'non') ||
                isset($_REQUEST['bNoCache']) === true ||
                isset($_REQUEST['szIdBloc']) === true && $GLOBALS['aModules'][$_REQUEST['szModule']]['blocs'][$_REQUEST['szIdBloc']]['cache']['html'] == 'non') {
                $bNoCache = true;
            }
        }

        if ($bNoCache === false) {

            // $objMemCache = new \Memcache;
            // $objMemCache->connect($GLOBALS['aParamsAppli']['memcache']['serveur'], $GLOBALS['aParamsAppli']['memcache']['port']) or die ('Could not connect');

            $objMemCache = $this->oGetMemcache();

            $szCle = md5($szRequete);

            $aRetour = $objMemCache->get($szCle);
            // echo '$szCle : <pre>'.print_r($aRetour, true).'</pre>';
            // echo 'cache BDD'.PHP_EOL;
            if ($aRetour != '') {
                return $aRetour;
            }
        }

        // echo '<pre>'.$szRequete.'</pre>';
        $rLien = $this->query($szRequete);

        if ($rLien) {
            // echo '<pre>'.print_r($aResult, true).'</pre>';

            while($objRow = $rLien->fetch(\PDO::FETCH_OBJ)) {
                $aResultat[] = $this->oGenererObjet($aMappingChamps, $objRow, $aResultat);

                // echo '<pre>'.print_r($objRow, true).'</pre>';
            }

            // echo '<pre>'.print_r($aResultat, true).'</pre>';
        }
// echo $szCle.' : mise en cache'.PHP_EOL;
        if ($bNoCache === false) {
            $objMemCache->set($szCle, $aResultat, MEMCACHE_COMPRESSED, 1200);
        }

        return $aResultat;
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

//			SELECT ".$aChamps[0].' id, '.$aChamps[1].' text
			FROM '.$sTable."
            WHERE 1=1 AND (replace(".$aChamps[1].",'-', ' ') LIKE " . $this->quote('%'. $aRecherche['sTerm'] . '%') . " OR replace(".$aChamps[1].",' ', '-') LIKE " . $this->quote('%' . $aRecherche['sTerm'] . '%') . ") ".$sRestriction."
			ORDER BY ".$sOrderBy." ASC
        ";
        // echo $szRequete."\r\n";

        $aResultats = $this->aSelectBDD($szRequete, $aMappingChamps);

        return $aResultats;
    }

    /*========== Partie écriture ========== */

    /**
     * @param $nIdElement par reference
     * @param array $aChamps
     * @param array $aChampsNull
     * @return bool
     * @throws \Exception
     */
    public function bInsert(Mapping $oMapping, $aChamps = [], $aChampsNull = [])
    {
        $aPreparationRequete = $this->aPreparerChampsPlaceHolderRequete($oMapping, $aChamps, $aChampsNull);

        $sRequete = "INSERT INTO {$oMapping->sNomTable()} SET " . $aPreparationRequete['sRequete'];

        $oRequetePrepare = $this->oPreparerRequete($sRequete);
        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aPreparationRequete['aChampsPrepare']);

        return $bRetour;

//        if ($bRetour === false) {
//            $this->vLog('critical', $sRequete);
//            $this->vLog('critical', '<pre>' . print_r($aPreparationRequete['aChampsPrepare'], true) . '</pre>');
//            $this->vLog('critical', 'sMessagePDO ----> ' . $this->sMessagePDO);
//            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
//        } else {
//
//            $nIdElement = $this->nGetLastInsertId();
//            if ($this->bRessourceLogsPresente() && $this->sNomTable != 'logs') {
//                $this->bSetLog("insert_{$this->sNomTable}", $nIdElement);
//            }
//        }
    }

    /**
     * Modification d'un élément dans la bdd
     *
     * @param string[] $aChamps Liste des champs non nuls
     * @param string[] $aChampsNull Liste des champs nuls
     *
     * @return bool Vrai si la requête a fonctionné, faux sinon
     */
    public function bUpdate(Mapping $oMapping, $nIdElement, $aChamps = array(), $aChampsNull = array())
    {

        $aPreparationRequete = $this->aPreparerChampsPlaceHolderRequete($oMapping, $aChamps, $aChampsNull);
        $sRequete = "UPDATE {$oMapping->sNomTable()} SET " . $aPreparationRequete['sRequete'] . "
        WHERE $oMapping->sNomCle = :$oMapping->sNomCle";
        $oRequetePrepare = $this->oPreparerRequete($sRequete);
        //On rajoute l'id élement dans les valeurs préparé pour qu'elle remplace le placeholder

        $aPreparationRequete['aChampsPrepare'][':' . $oMapping->sNomCle] =  $nIdElement;

        /**
         * Génération requète pour test
         */
//        error_log(str_replace(
//            array_keys($aPreparationRequete['aChampsPrepare']),
//            array_map(function ($mChamp) { return $this->quote($mChamp);}, $aPreparationRequete['aChampsPrepare']),
//            $sRequete
//        ));

        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aPreparationRequete['aChampsPrepare']);

        return $bRetour;
    }

    /**
     * @TODO test
     * Suppression d'un élément par son id
     *
     * Pour utiliser cette fonction la proprité
     * sNomChampId doit être défini en plus
     * du nom de la table
     *
     * @return bool Vrai en cas de succès, faux sinon
     */
    public function bDelete($nIdElement, $sNomTable, $sNomCle)
    {
        $sRequete = "DELETE FROM {$sNomTable} WHERE {$sNomCle} = :nIdElement";
        $oRequetePrepare = $this->oPreparerRequete($sRequete);
        $aChampsPrepare = [':nIdElement' => $nIdElement];
        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aChampsPrepare);

        return $bRetour;
    }



    /**
     * Formate les champs de une requête d'insert/update non préparée
     *
     * afin d'en retourner une chaine pour le SET.
     *
     * @param  array  $aChamps Champs concernés par l'édition.
     *
     * @return string          Fragment de requête formaté.
     */
    protected function sFormateChampsRequeteEdition($aChamps = array(), $aChampsNull = array())
    {
        $sRequete = '';

        $aLignes = array();
        foreach ($aChamps as $sUnChamp => $sUneValeur) {
            $aLignes[] = " ".$sUnChamp." = ". $this->quote($sUneValeur);
        }
        if ($aChampsNull) {
            foreach ($aChampsNull as $sUnChamp) {
                $aLignes[] = " ".$sUnChamp." = NULL";
            }
        }

        $sRequete .= implode(', ', $aLignes);

        return $sRequete;
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
    protected function aPreparerChampsPlaceHolderRequete($oMapping, $aChamps = array(), $aChampsNull= array())
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
            $aLignes[] = " {$sUnChamp} = :{$sUnChamp}";
            $aRetour['aChampsPrepare'][":{$sUnChamp}"] = $sUneValeur;
        }

        if ($aChampsNull) {
            foreach ($aChampsNull as $sUnChamp) {
                $aLignes[] = "{$sUnChamp} = NULL";
            }
        }

        $aRetour['sRequete'] = implode(', ', $aLignes);

        return $aRetour;
    }

    /**
     * Crée la partie de la requête contenant les placeholders (:nomchamp)
     *
     * @param string[] $aChamps
     *
     * @return string
     */
    protected function sPreparerRequetePlaceHolder($oMapping, $aChamps = array(), $aChampsNull= array())
    {
        $sRequete = '';
        $aLignes = array();

        foreach ($aChamps as $sUnChamp => $sUneValeur) {
            $aLignes[] = " {$sUnChamp} = :{$sUnChamp}";
        }

        if (is_array($aChampsNull)) {
            foreach ($aChampsNull as $sUnChamp) {
                $aLignes[] = "{$sUnChamp} = NULL";
            }
        }

        return implode(', ', $aLignes);
    }

    /**
     * Prépare les champs pour la requête contenant les placeholders
     *
     * (Attention : il faut que les indexs dans le tableau $aChamps correspondent aux placeholders
     * dans la requête, veillez donc à utiliser le même tableau de champs dans les deux méthodes
     * si vous n'utilisez pas la fonction aPreparerChampsPlaceHolderRequete)
     *
     * @param string[] $aChamps Les champs à préparer : L'index utilisé dans le nouveau tableau sera égale à ":nomdudchamp"
     *
     * @return string[] Les champs préparés
     */
    protected function aPreparerChampsRequete($aChamps= array())
    {
        $sRequete = '';
        $aChampsPrepare = array();

        foreach ($aChamps as $sUnChamp => $sUneValeur) {
            $aChampsPrepare[":{$sUnChamp}"] = $sUneValeur;
        }

        return $aChampsPrepare;
    }


    /**
     * Prépare la requête avec les placeholders et renvoi un objet PDOStatement
     *
     * @param string $sRequete La première partie de la requête (ex : 'INSERT INTO table SET ')
     *
     * @return \PDOStatement L'objet de PDO pour les requêtes préparées
     */
    protected function oPreparerRequete($sRequete)
    {
        return $this->prepare($sRequete);
    }

    /**
     * Execute la requête par l'objet \PDOStatement
     *
     * C'est cette fonction qu'il faut utiliser si vous souhaitez
     * exécuter la même requête préparé avec des paramètres différents
     *
     * @param \PDOStatement $oRequetePrepare La requête préparé préalablement
     * @param string[] $aChampsPrepare Les champs préparé à utilisé dans la requête
     *
     * @return bool Vrai en cas de succès, faux sinon
     */
    protected function bExecuterRequetePrepare(\PDOStatement $oRequetePrepare, $aChampsPrepare = array())
    {
        $this->sDerniereRequete = $oRequetePrepare->queryString;
        $this->aChampPrepareDerniereRequete = $aChampsPrepare;

        try {
            $mResultat = $oRequetePrepare->execute($aChampsPrepare);
        } catch (\PDOException $e) {
            /**
             * Contient les infos sur l'erreur SQL :
             * [0] => Code d'erreur SQLSTATE (défini par rapport au standard ANSI SQL)
             * [1] => Code d'erreur du driver spécifique
             * [2] => Message d'erreur spécifique au driver
             * @var array $infosErreur
             */
            $nCodeErreur = $e->getCode();
            $this->vLogErreurRequete();
        } finally {
            $this->vGererErreurSurContrainte($e, $nCodeErreur);
        }

        return $mResultat;

    }

    public function bInsertionLigneOuMiseAJourSiExiste($sCleSynchro, $oUnElement)
    {
        $aLigneExiste = $this->bLigneExiste($sCleSynchro, $oUnElement);

        if (isset($aLigneExiste['aChamps']) === false || empty($aLigneExiste['aChamps']) === true) {
            return true;
        }

        if ($aLigneExiste['bExiste'] === false) {
            $sRequete = $this->sRequeteSqlInsertLigne($aLigneExiste);
            $this->sLog .= "- Insertion car absente\n";
            $this->sLog .= "----> $sRequete\n";
        } else {
            $sRequete = $this->sRequeteSqlUpdateLigne($aLigneExiste);
            $this->sLog .= "- Mise à jour car présente\n";
            $this->sLog .= "----> $sRequete\n";
        }
        if (isset($GLOBALS['aParamsAppli']['conf']['bLogRequeteInsertOuUpdate']) === true && $GLOBALS['aParamsAppli']['conf']['bLogRequeteInsertOuUpdate'] === true) {
            $this->vLog('notice', $sRequete);
        }
        $rLien = $this->rConnexion->query($sRequete);

        if (!$rLien) {
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
            $this->vLog('critical', $sRequete);
            return false;
        }

        return true;
    }

    protected function sRequeteSqlInsertLigne($aLigneExiste, $sNomTable)
    {
        return 'INSERT INTO ' . $sNomTable . ' (' . implode(', ', $aLigneExiste['aChamps']) . ') '
            . 'VALUES(\'' . implode('\', \'', $aLigneExiste['aValeur']) . '\''
            . ')';
    }

    protected function sRequeteSqlUpdateLigne($aLigneExiste, $sNomTable)
    {
        $sRequete = 'UPDATE ' . $sNomTable . ' SET ' . implode(', ', $aLigneExiste['aChamps']) . ' '
            . 'WHERE 1 ';
        foreach ($aLigneExiste['oLigne'] as $sChamp => $sValeur) {
            $sRequete .= 'AND ' . $sChamp . ' = ' . $this->quote($sValeur) . ' ';
        }
        return $sRequete;
    }

    public function bInsertionLigneSiAbsente($sCleSynchro, $oUnElement)
    {
        $aLigneExiste = $this->bLigneExiste($sCleSynchro, $oUnElement);

        if ($aLigneExiste['bExiste'] === false) {
            $sRequete = $this->sRequeteSqlInsertLigne($aLigneExiste);

            $this->sLog .= "- Insertion car absente\n";
            $this->sLog .= "----> $sRequete\n";

            $this->vLog('notice', $sRequete);

            $rLien = $this->rConnexion->query($sRequete);

            if (!$rLien) {
                $this->sMessagePDO = $this->rConnexion->sMessagePDO;
                $this->vLog('critical', $sRequete);
                return false;
            }
        } else {
            $this->sLog .= "- On ne fait rien car présente\n";
        }

        return true;
    }

    /**
     * @param $sCleSynchro
     * @param $oUnElement
     * @param $sTable
     * @param array|string $mClePrimaire
     * @param $aMappingChamps
     * @return array|false
     */
    public function bLigneExiste($sCleSynchro, $oUnElement, $sTable, $mClePrimaire, $aMappingChamps)
    {
        $aRetour = [
            'bExiste' => false,
            'aClePrimaire' => [],
            'aChamps' => [],
        ];

        // On regarde si la ligne existe déjà.
        $sRequete = 'SELECT ';
        if (is_array($mClePrimaire) === true) {
            $sRequete .= implode(', ', $mClePrimaire);
        } else {
            $sRequete .= $mClePrimaire;
        }
        $sRequete .= ' FROM ' . $sTable . ' '
            . 'WHERE 1 ';

        if (is_array($mClePrimaire)) {
            foreach ($mClePrimaire as $sUneClePrimaire) {
                $sAliasClePrimaire = $aMappingChamps[$sUneClePrimaire];
                $sRequete .= 'AND ' . $sUneClePrimaire . ' = ' . $this->quote($oUnElement->$sAliasClePrimaire) . ' ';
            }
        } else {
            $sAliasClePrimaire = $aMappingChamps[$mClePrimaire];
            $sRequete .= 'AND ' . $mClePrimaire . ' = ' . $this->quote($oUnElement->$sAliasClePrimaire) . ' ';
        }
        $this->sLog .= "==============================\n";
        $this->sLog .= "Recherche ligne\n";
        $this->sLog .= "==============================\n";
        $this->sLog .= "- Ligne existe ?\n";
        $this->sLog .= "----> $sRequete\n";
        // $this->vLog('notice', $sRequete);
        $aElement = $this->aSelectBDD($sRequete);

        $aRetour['bExiste'] = empty($aElement) === false;
        $aRetour['oLigne'] = $aElement[0] ?? new \StdClass();

        $aMappingChamps = array_flip($aMappingChamps);
        foreach ($oUnElement as $sAliasChamp => $sValeur) {
            if ($sAliasChamp == 'nIdElement') {
                // On ignore le nIdElement qui est un
                // reliquat de la classe model.
                continue;
            }
            if (isset($aMappingChamps[$sAliasChamp]) === false) {
                // Si le champ n'est pas présent dans le mappingchamp
                // c'est une erreur grave ! On s'arrête !
                $this->vLog('critical', 'Erreur : champ introuvable dans le mapping champ de ' . $sTable . ' : ' . $sAliasChamp);
                return false;
            }
            if (preg_match('/_formate$/', $aMappingChamps[$sAliasChamp])) {
                continue;
            }
            // echo '-> '.$aMappingChamps[$sAliasChamp]."\n";

            if ($aRetour['bExiste'] === false) {
                $aRetour['aChamps'][] = $aMappingChamps[$sAliasChamp];
                $aRetour['aValeur'][] = addslashes($sValeur);
            } else {
                $aRetour['aChamps'][] = $aMappingChamps[$sAliasChamp] . ' = ' . $this->quote($sValeur) . ' ';
            }
        }

        return $aRetour;
    }

    /* ========= Utilitaires ================== */

    /**
     * @TODO test
     * Vérifie qu'une valeur d'un champ devant rester unique n'a pas déjà été choisie.
     * @oracle
     * @param  string $szTable  Table concernée.
     * @param  string $szChamp  Champ à vérifier.
     * @param  string $szValeur Valeur à trouver.
     *
     * @return boolean          Existe ou non.
     */
    public function bChampUniqueDejaUtilise($szTable = '', $szChamp = '', $szValeur = '', $aMappingChamps = [])
    {
        $bRetour = true;

        $szRequete = '
            SELECT COUNT(*) AS nNbElements
            FROM '.$szTable.'
            WHERE '.$szChamp.' LIKE \''.$szValeur.'\'
        ';

        // echo "$szRequete";

        $aResultats = $this->aSelectBDD($szRequete, $aMappingChamps);

        if (isset($aResultats[0]) === true) {
            if ($aResultats[0]->nNbElements == 0) {
                $bRetour = false;
            }
        }

        return $bRetour;
    }



    /** Récup le bon type ?
     * @param $szType
     * @param false $bObjet
     * @return array|string|string[]
     */
    public function szGetBonType($szType, $bObjet=false)
    {
        $szType = str_replace($GLOBALS['aParamsAppli']['AppId'].'_', '', $szType);
        if (in_array($szType, array_flip($GLOBALS['aParamsAppli']['namespaces']['data']))) {
            if ($bObjet == true) {
                $szType = '\\'.$GLOBALS['aParamsAppli']['namespaces']['data'][$szType];
            } else {
                $szType;
            }

        } else {
            $szType;
        }

        return $szType;
    }

    public function bRessourceLogsPresente()
    {
        if (!isset(self::$bPresenceRessourceLogs)) {
            self::$bPresenceRessourceLogs = isset($GLOBALS['aParamsAppli']['modules']['logs']) || in_array('logs', $GLOBALS['aParamsAppli']['modules']);
        }
        return self::$bPresenceRessourceLogs;
    }

    /**
     * Récupère le dernier id inséré
     * @param $sNomSequence
     * @return false|string
     */
    public function nGetLastInsertId($sNomSequence = '')
    {
        return $this->lastInsertId();
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
        if (!preg_match('/\./', $sDate)) {
            $sDate = "'" . $sDate . "'";
        }

        return 'DATE_FORMAT(' . $sDate . ', \'' . $sFormat . '\')';
    }

    /**
     * Retourne la fonction de base de données
     * récupérant le datetime courant.
     *
     * @return string Fonction.
     */
    public function sDatetimeCourant()
    {
        return 'NOW()';
    }

    /**
     * Concatène des chaines pour inclure dans une
     * requête SQL via MySQL.
     * @param  array $aChaine Chaines à concaténer.
     * @return string         Chaine à inclure dans le SQL.
     */
    public function sConcat($aChaine)
    {
        $aChaine = array_map(function($sChaine)
        {
            if (preg_match('/DATE_FORMAT|\./', $sChaine)) {
                return $sChaine;
            } else {
                return "'" . $sChaine . "'";
            }
        }, $aChaine);
        return 'CONCAT(' . implode(", ", $aChaine) . ')';
    }

    /**
     * Démarrage du process
     * @return void
     */
    public function bDemarreProcess()
    {
        $this->beginTransaction();
    }

    /**
     * Annulation du process.
     * @return void
     */
    public function bAnnuleProcess()
    {
        $this->rollBack();
    }

    /**
     * Validation du process.
     * @return void
     */
    public function bValideProcess()
    {
        $this->commit();
    }

    /**
     * Permet de générer une clause case de BDD pour faire correspondre une valeur à un libellé.
     *
     * @param  string $sNomChamp Nom du champ contenant la valeur.
     * @param  array  $aLibelle  Tableau de correspondance valeur => libellé.
     *
     * @return string            Clause CASE.
     */
    public function sGetClauseCase($sNomChamp, $aLibelle)
    {
        $sRequete = '
            CASE
                '.$sNomChamp.'
        ';
        foreach ($aLibelle as $sValeur => $sLibelle) {
            $sRequete .= '
                WHEN
                    \''.$sValeur.'\'
                THEN
                     '.$this->quote($sLibelle).'
            ';
        }
        $sRequete .= '
                ELSE
                    '.$sNomChamp.'
            END
        ';
        return $sRequete;
    }

    public function aListeTables()
    {
        $aTables = array();

        $sRequete = "SHOW FULL tables FROM `".$GLOBALS['aParamsBdd']['base']."`  where Table_Type != 'VIEW'";

        $aResultats = $this->aSelectBDD($sRequete);

        foreach ($aResultats as $nIndex => $oTable) {

            $sCle = 'Tables_in_'.$GLOBALS['aParamsBdd']['base'];

            $aTables[$oTable->$sCle] = array();

            $sRequete = "SHOW columns FROM ".$oTable->$sCle;

            $aResultats = $this->aSelectBDD($sRequete);

            foreach ($aResultats as $nIndex => $oChamp) {

                $aType = explode('(', $oChamp->Type);
                $sType = array_shift($aType);
                $sMaxLength = array_shift($aType);

                $aNom = explode('_', $oChamp->Field);
                $aNom = array_map('ucfirst', $aNom);
                $sNom = implode('', $aNom);

                $oChamp->sType = $sType;

                switch ($sType) {
                    case 'int':
                    case 'tinyint':
                    case 'smallint':
                        $oChamp->sChamp = 'n'.$sNom;
                        if ($sMaxLength != '') {
                            $oChamp->nMaxLength = str_replace(')', '', $sMaxLength);
                        }
                        break;

                    case 'varchar':
                    case 'text':
                    case 'mediumtext':
                    case 'longtext':
                        $oChamp->sChamp = 's'.$sNom;
                        if ($sMaxLength != '') {
                            $oChamp->nMaxLength = str_replace(')', '', $sMaxLength);
                        }
                        break;

                    case 'enum':
                        $oChamp->sChamp = 's'.$sNom;
                        break;

                    case 'datetime':
                        $oChamp->sChamp = 'dt'.$sNom;
                        break;

                    case 'time':
                        $oChamp->sChamp = 't'.$sNom;
                        break;

                    case 'date':
                        $oChamp->sChamp = 'd'.$sNom;
                        break;

                    case 'decimal':
                    case 'float':
                    case 'double':
                        $oChamp->sChamp = 'f'.$sNom;
                        if ($sMaxLength != '') {
                            $oChamp->nMaxLength = str_replace(')', '', $sMaxLength);
                        }
                        break;

                    default:
                        # code...
                        break;
                }

                $aTables[$oTable->$sCle][$oChamp->Field] = $oChamp;
            }

        }

        // echo "<pre>".print_r($aTables, true)."</pre>";

        return $aTables;
    }


    /**
     * @param Iterable $mMapping
     * @param $objRow
     * @return object
     */
    protected function oGenererObjet(Iterable $mMapping, $objRow): object
    {
        if (is_iterable($mMapping) && count($mMapping)) {
            $objResultat = new \StdClass();

            foreach ($objRow as $szCleLigne => $mValeur) {
                $sNomChamp = $this->sRecupererNomChamp($mMapping, $szCleLigne);
                $mValeur = $this->mTraiterValeur($mMapping, $szCleLigne, $mValeur);
                $objResultat->$sNomChamp = $mValeur;
            }

            return $objResultat;

        } else {

            return $objRow;
        }
    }


    /**
     * Récupère les noms des champs correspondant aux colonnes si le mapping est un tableau (méthode classique)
     * @param $mMapping
     * @param $szCleLigne
     * @return string
     */
    protected function sRecupererNomChamp($mMapping, $szCleLigne): string
    {
        return is_array($mMapping) && isset($mMapping[$szCleLigne]) ? $mMapping[$szCleLigne] : $szCleLigne;
    }

    /**
     * Lance le traitement sur la valeur récupérée si les objets de type champ sont utilisés
     * @param $mMapping
     * @param $szCleLigne
     * @param $mValeur
     * @return mixed
     */
    protected function mTraiterValeur($mMapping, $szCleLigne, $mValeur)
    {
        if ($mMapping instanceof Mapping && isset($mMapping[$szCleLigne])) {
            $oChamp  = $mMapping[$szCleLigne];
            $mValeur = $oChamp->mGetValeur($mValeur);
        }
        return $mValeur;
    }

    /**
     *
     * @return string
     */
    public function sDebugRequetePreparee()
    {
        $sRequete = $this->sDerniereRequete;
        $aChamps = $this->aChampPrepareDerniereRequete;

        if (!empty($aChamps)) {
            foreach ($aChamps as $sChamp => $mValeur) {
                if (is_object($mValeur) === true) {
                    if ($mValeur instanceof \DateTime) {
                        $mValeur = $mValeur->format('Y-m-d H:i:s');
                    } else {
                        continue;
                    }
                } elseif (is_string($mValeur) === true) {
                    $mValeur = "'" . addslashes($mValeur) . "'";
                } elseif ($mValeur === null) {
                    $mValeur = 'NULL';
                } elseif (is_array($mValeur) === true) {
                    $mValeur = implode(',', $mValeur);
                }
                $sRequete = str_replace(':'. $sChamp, $mValeur, $sRequete);

                //$sRequete = preg_replace('/:(\b' . str_replace(':', '', $sChamp) . '\b)/', $mValeur, $sRequete);
            }
        }
        return $sRequete;
    }

    /**
     * @return string
     */
    protected function vLogErreurRequete(): string
    {
        $GLOBALS['oUtiles']->vLog('critical', '<pre>' . $this->sDerniereRequete . '</pre>');
        $GLOBALS['oUtiles']->vLog('critical', '<pre>' . print_r($this->aChampPrepareDerniereRequete, true) . '</pre>');
        $GLOBALS['oUtiles']->vLog('critical', '<pre>' .  'ERREUR SQL GRAVE : ' . $this->sDebugRequetePreparee(). '</pre>');

        $this->vLog('critical', 'sMessagePDO ----> ' . $this->sMessagePDO);

        return $this->sMessagePDO;
    }


    /**
     * @param $e
     * @param $sCodeErreur
     */
    protected function vGererErreurSurContrainte($e, $sCodeErreur): void
    {
        if (isset($e)) {
            $bExceptionSurContrainte = $GLOBALS['aModules']['base']['conf']['bExceptionSurContrainte'];
            if ($bExceptionSurContrainte === true) {
                throw $e;
            } else {
                switch ($sCodeErreur) {
                    // Rejouter ici des codes erreur SQLSTATE pour ne pas throw d'exceptions dans ces cas précis
                    case '23000': // Violation de contrainte d'intégrité
                        break;
                    default:
                        throw $e;
                        break;
                }
            }
        }
    }


}
