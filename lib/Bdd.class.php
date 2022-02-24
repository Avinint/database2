<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\UndeadBrain;
use APP\Core\Lib\Interne\PHP\Utiles;
use APP\Modules\Base\Lib\Champ\Champ;
use APP\Modules\Base\Lib\Recherche\Recherche;
use APP\Modules\Base\Lib\RequeteBuilder\RequeteBuilderInterface;
use APP\Ressources\Base\Lib\SelectMapping;

class Bdd extends UndeadBrain
{
    /**
     * Ressource de la connexion
     * @var CorePDO
     */
    public $rConnexion;
    public $szVersion;

    protected Recherche $oRecherche;

    private static $aConnexionStatic = array();

    /**
     * Tableau de correspondance des champs et de leurs alias.
     * @var array|Mapping
     */
    protected $aMappingChamps;

    public $sMessagePDO;
    public $sRequeteErreur;

    public $aTitreLibelle;

    /**
     * Nom de la table (Utilisé dans bInsert, bUpdate et bDelete)
     * @var string
     */
    public $sNomTable;

    public $sLog;

    private static $bPresenceRessourceLogs;

    public $bSansAnnulationProcess = false;

    private $bIsOracle;

    /**
     * Constructeur de la classe
     *
     * @return void
     */
    public function __construct($nIdElement = 0)
    {
        $this->sMessagePDO = '';

        $this->bIsOracle = $GLOBALS['aParamsBdd']['oracle'] ?? false;
        // echo ' bdd ';
        // echo '-------- avant <pre>'.print_r($this->rConnexion, true).'</pre>';
        if (isset($this->rConnexion) === false) {
            $this->vConnexionBdd();
        }

        if (empty($this->sAlias)) {
            $this->sAlias = substr($this->sNomTable,  0, 3);
        }

        $this->sLog = '';

        if (empty($this->oRecherche)) {
            $this->oRecherche = new Recherche($this->aMappingChamps);
        }

        $this->vInitialiseProprietes($nIdElement);
    }

    /**
     * @param $nIdElement
     */
    private function vInitialiseProprietes($nIdElement)
    {
        if ($nIdElement > 0) {
            $aRecherche = array($this->sNomChampId() => $nIdElement);
            $aElements = $this->aGetElements($aRecherche);
            if (isset($aElements[0]) === true) {
                foreach ($aElements[0] as $szCle => $szValeur) {
                    $this->$szCle = $szValeur;
                }
            }
        }
    }

    public function bRessourceLogsPresente()
    {
        if (!isset(self::$bPresenceRessourceLogs)) {
            self::$bPresenceRessourceLogs = isset($GLOBALS['aParamsAppli']['modules']['logs']);
        }
        return self::$bPresenceRessourceLogs;
    }

    /**
     * Méthode permettant de se connecter à la base de données..
     *
     * @param  string $sHote           Hôte hébbergeant la base de données.
     * @param  string $sNomBase        Nom de la base de données.
     * @param  string $sUtilisateur    Nom d'utilisateur pouvant se connecter.
     * @param  string $sMotDePasse     Mot de passe de connexion.
     * @param  string $sEncodage       Encodage de la base de données.
     * @param  string $sAliasConnexion Nom de la variable globale contenant l'objet de connexion.
     *
     * @return void
     */
    public function vConnexionBdd($sHote = '', $sNomBase = '', $sUtilisateur = '', $sMotDePasse = '', $sEncodage = '', $sAliasConnexion = 'rConnexion')
    {
        if (isset(self::$aConnexionStatic[$sAliasConnexion]) === false) {
            if (isset($GLOBALS['aParamsBdd']['sqlite']) === false) {
                $bSqlite = false;
                if (isset($GLOBALS['aParamsAppli']['encodage']) === false) {
                    $GLOBALS['aParamsAppli']['encodage'] = 'UTF-8';
                }

                if ($sHote == '' && isset($GLOBALS['aParamsBdd']['hote']) === true) {
                    $sHote = $GLOBALS['aParamsBdd']['hote'];
                }
                if ($sNomBase == '' && isset($GLOBALS['aParamsBdd']['base']) === true) {
                    $sNomBase = $GLOBALS['aParamsBdd']['base'];
                }
                if ($sUtilisateur == '' && isset($GLOBALS['aParamsBdd']['utilisateur']) === true) {
                    $sUtilisateur = $GLOBALS['aParamsBdd']['utilisateur'];
                }
                if ($sMotDePasse == '' && isset($GLOBALS['aParamsBdd']['mot_de_passe']) === true) {
                    $sMotDePasse = $GLOBALS['aParamsBdd']['mot_de_passe'];
                }
                if ($sEncodage == '' && isset($GLOBALS['aParamsBdd']['encodage']) === true) {
                    $sEncodage = $GLOBALS['aParamsAppli']['encodage'];
                }

            } else {
                $bSqlite = true;
                if ($sHote == '') {
                    $sHote = $GLOBALS['aParamsBdd']['chemin_fichier'];
                }
            }

            // echo '<pre>'.print_r($GLOBALS['aParamsBdd'], true).'</pre>';
            try
            {
                if ($bSqlite === true)
                {
                    self::$aConnexionStatic[$sAliasConnexion] = new CorePDOSqlite('sqlite:'.$sHote);
                }
//                elseif ($GLOBALS['aParamsBdd']['oracle'])
//                {
//                    self::$aConnexionStatic[$sAliasConnexion] = oci_connect($sUtilisateur, $sMotDePasse, $sHote, $sEncodage);
//
//                    if (!self::$aConnexionStatic[$sAliasConnexion]) {
//                        $e = oci_error();
//                        $sErreur = 'Impossible de se connecter à la base de données : '. htmlentities($e['message'], ENT_QUOTES);
//                        throw new \PDOException($sErreur,E_USER_ERROR);
//                        trigger_error($sErreur, E_USER_ERROR);
//                    }
//                }
                elseif ($this->bIsOracle) {
                    $szBase = 'oci:dbname=//' . $sHote . '/' . $sNomBase . ';charset=al32utf8';
                    self::$aConnexionStatic[$sAliasConnexion] = new CorePDOOracle($szBase, $sUtilisateur, $sMotDePasse);
                } else {
                    $szBase = 'mysql:host=' . $sHote . ';dbname=' . $sNomBase . ';charset=utf8';
                    self::$aConnexionStatic[$sAliasConnexion] = new CorePDO($szBase, $sUtilisateur, $sMotDePasse);
//                    self::$aConnexionStatic[$sAliasConnexion]->query('SET NAMES \'' . str_replace('-', '', $sEncodage) . '\';');

                }


                /* avant pdo_oci */
                //  self::$aConnexionStatic[$sAliasConnexion] = new \APP\Modules\Base\Lib\CorePDO('mysql:host='.$sHote.';dbname='.$sNomBase, $sUtilisateur, $sMotDePasse);

                // echo 'mysql:host='.$sHote.';dbname='.$sNomBase, $sUtilisateur, $sMotDePasse."<br/>\n";
                // echo 'mysql:host='.$GLOBALS['aParamsBdd']['hote'].';dbname='.$GLOBALS['aParamsBdd']['base'], $GLOBALS['aParamsBdd']['utilisateur'], $GLOBALS['aParamsBdd']['mot_de_passe'];
                // paramètrage de l'encodage en UTF-8

                self::$aConnexionStatic[$sAliasConnexion]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                // echo '-------- après <pre>'.print_r($this->rConnexion, true).'</pre>';
            }
            catch( PDOException $e )
            {
                echo 'Unable to connect to the database : ' . $e->getMessage();
                exit;
            }
        }
        $this->$sAliasConnexion = self::$aConnexionStatic[$sAliasConnexion];

        Champ::$rConnexion = $this->$sAliasConnexion;

        $GLOBALS[$sAliasConnexion.'BDD'] = $this->$sAliasConnexion;
    }



    /**
     * Méthode qui sélectionne les éléments dans la base de données et retourne un tableau d'objets prêt à être utilisé
     *
     * @param  string   $szRequete      La requête SQL.
     * @param  array    $aMappingChamps Un tableau associatif avec comme clé les champs SQL et comme valeur les attributs à retourner.
     * @param  boolean  $bNoCache       Désactiver le cache pour certaines requêtes.
     *
     * @return array    $aResultat      Les résultats.
     */
    public function aSelectBDD($szRequete, $aMappingChamps = array(), $bNoCache = false, $sAliasConnexion = 'rConnexion')
    {
        return $this->$sAliasConnexion->aSelectBDD($szRequete, $aMappingChamps, $bNoCache);
    }

    /**
     * Convertit les critères d'un champ sous forme de tableau en chaine.
     *
     * @param  array  $aRegles Règles du champ.
     * @return string          Règle du champ.
     */
    public function szGetCriteresValidation($aRegles = array())
    {
        $szRegle = '';

        foreach ($aRegles as $szCle => $szValeur) {

            if ($szRegle != '') {
                $szRegle .= ', ';
            }

            if ($szValeur != '') {
                $szRegle .= '\''.$szCle.'\' : '.$szValeur;
            }

        }

        return $szRegle;
    }

    /**
     * Initialise un RequeteBuilder avec des paramètres de requète basiques (
     * @param bool|string $mModeCount si string alias de colonne count
     * @param $sGroupBy
     * @param $szOrderBy
     * @return RequeteBuilderInterface
     */
    public function oConstruireRequete($mModeCount = false, $sGroupBy = '', $szOrderBy = '') : RequeteBuilderInterface
    {
        $oRequete = $this->oGetRequeteBuilder();

        if ($mModeCount) {
            $oRequete
                ->oSelectCount($mModeCount);
        } else {
            $oRequete
                ->oGroupBy($sGroupBy)
                ->oInitOrderBy($szOrderBy);
        }

        return $oRequete->oGroupBy($sGroupBy);
    }

    public function oGetSelection($oMapping, $bDistinct = false) : RequeteBuilderInterface
    {
        if (is_array($oMapping)) {
            $oMapping = new SelectMapping(...$oMapping);
        }

        $oRequete = $this->oGetRequeteBuilder($oMapping);

            $oRequete
                ->oSelect([$oMapping->sNomChampId, $oMapping->sNomChampLibelle])
                ->oInitOrderBy($oMapping->sGetOrderBy());

            if ($bDistinct) {
                $oRequete->oDistinct();
            }
        return $oRequete;
    }

    /** Initialise un générateur de requêtes avec les paramètres du modèles et du type de connexion BDD
     * @return RequeteBuilderInterface
     */
    protected function oGetRequeteBuilder($oMapping = null) : RequeteBuilderInterface
    {
        $cRequeteBuilder = $this->rConnexion->cTypeRequeteBuilder;

        return new $cRequeteBuilder( $oMapping ?? $this->aMappingChamps);
    }

    /**
     * @throws \Exception
     */
    protected function szGetCriteresRecherche($aRecherche = [])
    {
        $this->oRecherche->vAjouterCriteresRecherche($aRecherche);


        return $this->oRecherche->sGetTexte();
    }

    /**
     * Récupère les éléments en fonction des critères de recherche
     *
     * @param  array   $aRecherche  Critères de recherche
     * @param  integer $nStart      Numéro de début.
     * @param  integer $nNbElements Nombre de résultats.
     * @param  string  $szOrderBy   Ordre de tri.
     * @param  string  $szGroupBy   Groupé par tel champ.
     *
     * @return array                Liste des éléments
     */
    public function aGetElements($aRecherche = array(), $nStart = 0, $nNbElements = 0, $szOrderBy = '', $szGroupBy = '', $szContexte = '')
    {
        $szRequete = $this->szGetSelect($aRecherche, $szOrderBy, false, $nStart, $nNbElements, $szGroupBy, $szContexte);
//        $szRequete = $this->sPaginerRequete($nNbElements, $nStart, $szRequete);
        // echo '<pre>'.$szRequete.'</pre>';

        $aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);

        if ($this->rConnexion->sMessagePDO != '') {
            // echo '--------->'.$this->rConnexion->sMessagePDO;
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
            $this->sRequeteErreur = $this->rConnexion->sRequeteErreur;
        }

        if ($this->aTitreLibelle) {
            foreach ($aResultats as $sCle => $oUneLigne) {
                $sTitreLibelle = "";
                foreach ($this->aTitreLibelle as $sNomChamp) {
                    if (isset($oUneLigne->$sNomChamp) === true) {
                        $sTitreLibelle .= $oUneLigne->$sNomChamp." ";
                    }
                }
                if ($sTitreLibelle != '') {
                    $aResultats[$sCle]->sTitreLibelle = trim($sTitreLibelle);
                }
            }
        }

        return  $aResultats;
    }

    /**
     * Connaître le nombre d'éléments.
     * @param array $aRecherche Critères de recherche
     * @param  string  $szGroupBy   Groupé par tel champ.
     * @param  string  $szContexte   Contexte d'exécution de la requête.
     * @return string           Retourne la requête
     */
    public function nGetNbElements($aRecherche, $szGroupBy = '', $szContexte = '')
    {
        $nRetour = 0;


        $szRequete = $this->szGetSelect($aRecherche, '', true, 0, 0, $szGroupBy, $szContexte);



        // echo '<pre>'.$szRequete.'</pre>';

        $aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);



        if (isset($aResultats[0]->nNbElements) === true) {
            $nRetour = $aResultats[0]->nNbElements;
        }

        return $nRetour;
    }

    /**
     * Limite les lignes qui sont affichées
     * @param $nNbElements
     * @param $nStart
     * @param $szRequete
     * @return string
     */
    protected function sPaginerRequete($nNbElements, $nStart, $szRequete)
    {
        return $this->rConnexion->sPaginerRequete($nNbElements, $nStart, $szRequete);
    }

    public function aListeTables()
    {
        $aTables = array();

        if (isset($this->rConnexion) === false) {
            $this->vConnexionBdd();
        }

        return $this->rConnexion->aListeTables();
    }


    public function vAnalyseBdd($szModule = '')
    {
        //--------------------------------------------------------------------------------------------
        // Analyse du fichier de conf.
        //--------------------------------------------------------------------------------------------

        $szFichierRessource = $_SERVER['DOCUMENT_ROOT'].'/ressources/'.$szModule.'/config/bdd.yml';
        $szFichierModule = $_SERVER['DOCUMENT_ROOT'].'/modules/'.$szModule.'/config/bdd.yml';

        $aTables = array();

        if (file_exists($szFichierRessource) === true) {

            $aTablesTemp = \Spyc::YAMLLoad($szFichierRessource);
            $aTables = array_merge($aTables, $aTablesTemp);

        }

        if (file_exists($szFichierModule) === true) {

            $aTablesTemp = \Spyc::YAMLLoad($szFichierModule);
            $aTables = array_merge($aTables, $aTablesTemp);
        }

        // echo '<pre>'.print_r($aTables, true).'</pre>';


        //--------------------------------------------------------------------------------------------
        // Récupération des infos de chaque table.
        //--------------------------------------------------------------------------------------------

        foreach ($aTables as $szTable => $aParams) {

            $szRequete = '
                SELECT column_name AS nom_champ, column_default AS not_null, is_nullable, data_type AS type,
                character_maximum_length, character_set_name, column_key, extra
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE table_schema = \'easy2watch\'
                AND table_name = \''.$szTable.'\'
                ORDER BY ordinal_position ASC
            ';

            $aResultats = $this->aSelectBDD($szRequete);

            // echo '- '.$szTable.'<br/>';

            foreach ($aResultats as $nIndex => $oTable) {

                // echo '<pre>'.print_r($oTable, true).'</pre>';

                foreach ($aTables[$szTable]['champs'] as $szChamp => $aParamsChamps) {
                    $aParamsChamps['nom_champ'] = $szChamp;
                    // echo '<pre>'.print_r($aParamsChamps, true).'</pre>';
                }
                // echo '<pre>'.print_r($oTable, true).'</pre>';
            }

        }
    }


    /**
     * Vérifie qu'une valeur d'un champ devant rester unique n'a pas déjà été choisie.
     * @oracle
     * @param  string $szTable  Table concernée.
     * @param  string $szChamp  champ à vérifier.
     * @param  string $szValeur Valeur à trouver.
     *
     * @return boolean          Existe ou non.
     */
    public function bChampUniqueDejaUtilise($szTable = '', $szChamp = '', $szValeur = '')
    {
        return $this->rConnexion->bChampUniqueDejaUtilise($szTable, $szChamp, $szValeur, $this->aMappingChamps);
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
    public function aGetSelect2JSON($aRecherche = array(), $aChamps = array(), $sTable = '', $sOrderBy = '', $sRestriction = '')
    {
        return $this->rConnexion->aGetSelect2JSON($aRecherche, $aChamps, $sTable, $sOrderBy, $sRestriction);
    }


    /**
     * Formate les champs de la requête d'insert/update
     * afin d'en retourner une chaine pour le SET.
     *
     * @param  array  $aChamps Champs concernés par l'édition.
     *
     * @return string          Fragment de requête formaté.
     */
//    protected function sFormateChampsRequeteEdition($aChamps = array(), $aChampsNull = array())
//    {
//        $sRequete = '';
//
//        $aLignes = array();
//        foreach ($aChamps as $sUnChamp => $sUneValeur) {
//            $aLignes[] = " ".$sUnChamp." = '". $this->sEchappe($sUneValeur) ."'";
//        }
//        if ($aChampsNull) {
//            foreach ($aChampsNull as $sUnChamp) {
//                $aLignes[] = " ".$sUnChamp." = NULL";
//            }
//        }
//
//        $sRequete .= implode(', ', $aLignes);
//
//        return $sRequete;
//    }


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
//    protected function aPreparerChampsPlaceHolderRequete($aChamps = array(), $aChampsNull= array())
//    {
//        $aRetour = array(
//            'sRequete' => '',
//            'aChampsPrepare' => array()
//        );
//
//        $aLignes = array();
//        /**
//         * Dans le parcours des champs on prépare
//         * les lignes dans la requête SQL contenant
//         * les placeholders (:nomchamp) et on prépare en
//         * même temps les valeurs de ces placeholders
//         * qui seront attribué dans le aPreparerRequete
//         */
//        foreach ($aChamps as $sUnChamp => $sUneValeur) {
//            $aLignes[] = " {$sUnChamp} = :{$sUnChamp}";
//            $aRetour['aChampsPrepare'][":{$sUnChamp}"] = $sUneValeur;
//        }
//
//
//        if ($aChampsNull) {
//            foreach ($aChampsNull as $sUnChamp) {
//                $aLignes[] = "{$sUnChamp} = NULL";
//            }
//        }
//
//        $aRetour['sRequete'] = "INSERT INTO {$this->sNomTable} SET " .  implode(', ', $aLignes);
//
//        return $aRetour;
//    }

    /**
     * Crée la partie de la requête contenant les placeholders (:nomchamp)
     *
     * @param string[] $aChamps
     *
     * @return string
     */
//    protected function sPreparerRequetePlaceHolder($aChamps = array(), $aChampsNull= array())
//    {
//        $sRequete = '';
//        $aLignes = array();
//
//        foreach ($aChamps as $sUnChamp => $sUneValeur) {
//            $aLignes[] = " {$sUnChamp} = :{$sUnChamp}";
//        }
//
//        if (is_array($aChampsNull)) {
//            foreach ($aChampsNull as $sUnChamp) {
//                $aLignes[] = "{$sUnChamp} = NULL";
//            }
//        }
//
//        return implode(', ', $aLignes);
//    }

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
//    protected function aPreparerChampsRequete($aChamps= array())
//    {
//        $sRequete = '';
//        $aChampsPrepare = array();
//
//        foreach ($aChamps as $sUnChamp => $sUneValeur) {
//            $aChampsPrepare[":{$sUnChamp}"] = $sUneValeur;
//        }
//
//        return $aChampsPrepare;
//    }

    /**
     * Prépare la requête avec les placeholders et renvoi un objet PDOStatement
     *
     * @param string $sRequete La première partie de la requête (ex : 'INSERT INTO table SET ')
     *
     * @return \PDOStatement L'objet de PDO pour les requêtes préparées
     */
//    protected function oPreparerRequete($sRequete)
//    {
//        return $this->rConnexion->prepare($sRequete);
//    }

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
//    protected function bExecuterRequetePrepare($oRequetePrepare, $aChampsPrepare = array())
//    {
//        $mResultat = false;
//        try
//        {
//            $mResultat = $oRequetePrepare->execute($aChampsPrepare);
//        }
//        catch (\PDOException $e) {
//            $this->vLog('critical', '<pre>'.print_r($oRequetePrepare, true).'</pre>');
//            $this->vLog('critical', '<pre>'.print_r($aChampsPrepare, true).'</pre>');
//            // $oUtiles = new Utiles;
//            // if (method_exists($oUtiles, 'vLogRequete')) {
//            //     $oUtiles->vLogRequete($szRequete, true);
//            // }
//            /**
//             * Contient les infos sur l'erreur SQL :
//             * [0] => Code d'erreur SQLSTATE (défini par rapport au standard ANSI SQL)
//             * [1] => Code d'erreur du driver spécifique
//             * [2] => Message d'erreur spécifique au driver
//             * @var array $infosErreur
//             */
//            $sCodeErreur = $e->getCode();
//            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
//        } finally {
//            if (isset($e) === true) {
//                $bExceptionSurContrainte = $GLOBALS['aModules']['base']['conf']['bExceptionSurContrainte'];
//                if ($bExceptionSurContrainte === true) {
//                    throw $e;
//                } else {
//                    switch ($sCodeErreur) {
//                        // Rejouter ici des codes erreur SQLSTATE pour ne pas throw d'exceptions dans ces cas précis
//                        case '23000': // Violation de contrainte d'intégrité
//                            break;
//                        default:
//                            throw $e;
//                            break;
//                    }
//                }
//            }
//            return $mResultat;
//        }
//
//    }

    /**
     * Pour utiliser les méthodes qui vont suivre
     * vous devez définir deux propriétés dans le modèle correspondant
     *
     * sNomTable : Nom de la table
     * sNomChampIdBdd : Nom du champ en base de données désigné en tant que clé primaire
     *
     * Vous devez également indiqué son mapping dans le tableau aMappingChamps (Normalement cela est fait automatiquement par le générateur)
     */

    /**
     * Insertion d'un élément dans la bdd
     *
     * @param string[] $aChamps Liste des champs non nuls
     * @param string[] $aChampsNull Liste des champs nuls
     *
     * @return bool Vrai si la requête a fonctionné, faux sinon
     */
    public function bInsert($aChamps = array(), $aChampsNull = array())
    {
        $sNomTable = $this->sNomTable();
        $sNomChampId = $this->sNomChampId();

        $bRetour = $this->rConnexion->bInsert($this->aMappingChamps, $aChamps, $aChampsNull);

        $this->$sNomChampId = $this->nGetLastInsertId();

        if ($bRetour && $this->bRessourceLogsPresente() && $sNomTable != 'logs') {
            $this->bSetLog("insert_{$sNomTable}", $this->$sNomChampId);
        }

        return $bRetour;
    }

    /**
     * Modification d'un élément dans la bdd
     *
     * @param string[] $aChamps Liste des champs non nuls
     * @param string[] $aChampsNull Liste des champs nuls
     *
     * @return bool Vrai si la requête a fonctionné, faux sinon
     */
    public function bUpdate($aChamps = array(), $aChampsNull = array())
    {
        $sNomTable = $this->sNomTable();
        $sNomChampId = $this->sNomChampId();

        $bRetour = $this->rConnexion->bUpdate($this->aMappingChamps, $this->$sNomChampId, $aChamps, $aChampsNull);

        if ($bRetour && $this->bRessourceLogsPresente() && $sNomTable != 'logs') {
            $this->bSetLog("update_{$sNomTable}", $this->$sNomChampId);
        }

        return $bRetour;
    }

    /**
     * Suppression d'un élément par son id
     *
     * Pour utiliser cette fonction la proprité
     * sNomChampId doit être défini en plus
     * du nom de la table
     *
     * @return bool Vrai en cas de succès, faux sinon
     */
    public function bDelete()
    {
        $sNomTable = $this->sNomTable();
        $sNomChampId = $this->sNomChampId();
        $sNomCle = $this->sNomCle();

        $bRetour = $this->rConnexion->bDelete($this->$sNomChampId, $sNomTable, $sNomCle);

        if($bRetour && $this->bRessourceLogsPresente() && $sNomTable != 'logs') {
            $this->bSetLog("delete_{$sNomTable}", $this->$sNomChampId);
        }

        return $bRetour;

//
//        //Ici pas besoin de générer les champs préparé, on possède uniquement le champ nIdElement
//        //On récupère le nom du champ contenant la clé primaire par le mapping champs
//        $sNomChampId = $this->sGetNomChampId();
//
//        $aChampsPrepare = array(
//            ':nIdElement' => $this->$sNomChampId
//        );
//
//        $sRequete = "DELETE FROM {$this->sNomTable} WHERE {$this->sNomCle} = :nIdElement";
//
//        $oRequetePrepare = $this->oPreparerRequete($sRequete);
//
//        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aChampsPrepare);
//
//        if($bRetour === false)
//        {
//            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
//        }
//        else
//        {
//            if ($this->bRessourceLogsPresente() && $this->sNomTable != 'logs') {
//                $this->bSetLog("delete_{$this->sNomTable}", $this->$sNomChampId);
//            }
//        }
//
//        return $bRetour;
    }

    /**
     * Permet de générer une clause case de BDD pour faire correspondre une valeur à un libellé.
     *
     * @param  string $sNomChamp Nom du champ contenant la valeur.
     * @param  array  $aLibelle  Tableau de correspondance valeur => libellé.
     *
     * @return string            Clause CASE.
     */
    protected function sGetClauseCase($sNomChamp, $aLibelle)
    {
        return $this->rConnexion->sGetClauseCase($sNomChamp, $aLibelle);
    }

    /**
     * Renvoie le champ mappé sur le champ
     * @param  string $sNomChamp nom du champ
     *
     * @return string Nom du champ mappé (Ex : nIdTrucMuche)
     */
    public function sGetNomChamp($sNomChamp = '')
    {
        $sRetour = '';
        if(isset($this->aMappingChamps[$sNomChamp])) {
            $sRetour = $this->aMappingChamps[$sNomChamp];
        }
        return $sRetour;
    }

    /**
     *  Renvoye le nom de la table
     * @return string
     */
    protected function sNomTable() : string
    {
        $sNomTable = $this->sNomTable ?? $this->aMappingChamps->sNomTable() ?? '';

        if (empty($sNomTable)) {
            throw new \Exception("Le nom de la table n'a pas été défini pour le modèle " . get_called_class());
        }

        return $sNomTable;
    }

    /**
     * Renvoie le champ correspondant à la clé primaire
     *
     * @return string Nom du champ mappé (Ex : nIdTrucMuche)
     */
    protected function sNomChampId() : string
    {
        $sNomChampId = $this->sNomChampId ?? $this->aMappingChamps->sNomChampId ?? '';

        if (empty($sNomChampId)) {
            throw new \Exception("Le champ id - correspondant à la clé primaire -  non défini pour ce modèle");
        }

        return $sNomChampId;
    }

    /**
     * Renvoie le nom de la colonne clé primaire
     * @return void
     * @throws \Exception
     */
    protected function sNomCle() : string
    {
        $sNomCle = $this->sNomCle ?? $this->aMappingChamps->sNomCle ?? '';

        if (empty($sNomCle)) {
            throw new \Exception("Le nom de la clé primaire n'a pas été définie pour ce modèle");
        }

        return $sNomCle;
    }

    /**
     * Renvoie le nom de la séquence, seulement utile et rensoigné dans les bases oracle, ne pas utiliser avec les autres sgbdr
     * @return string
     */
    protected function sNomSequence() : string
    {
        $sNomSequence = $this->aMappingChamps->sNomSequence() ?? '';

        if (empty($sNomSequence)) {
            throw new \Exception("Les séquences ne doivent pas être utilisées en dehors de Oracle Db");
        }

        return $sNomSequence;
    }

    /**
     * Récupération des données pour impression de PDF
     * @param  array  $aRecherche
     * @param  string $sOrderBy
     * @param  string $sGroupBy
     * @param  string $sContexte
     * @return array
     */
    public function aRecupereDonneesPdf($aRecherche = array(), $sOrderBy = '', $sGroupBy = '', $sContexte = '')
    {
        return $this->rConnexion->aGetElements($aRecherche, 0, '', $sOrderBy, $sGroupBy, $sContexte);
    }


    public function bInsertionLigneOuMiseAJourSiExiste($sCleSynchro, $oUnElement)
    {
        return $this->rConnexion->bInsertionLigneOuMiseAJourSiExiste($sCleSynchro, $oUnElement);
    }

//    protected function sRequeteSqlInsertLigne($aLigneExiste)
//    {
//        return $this->rConnexion->sRequeteSqlInsertLigne($aLigneExiste, $this->sNomTable);
//        return 'INSERT INTO ' . $this->sNomTable . ' (' . implode(', ', $aLigneExiste['aChamps']) . ') '
//                    . 'VALUES(\'' . implode('\', \'', $aLigneExiste['aValeur']) . '\''
//                    . ')';
//    }

//    protected function sRequeteSqlUpdateLigne($aLigneExiste)
//    {
//        return $this->rConnexion->sRequeteSqlUpdateLigne($aLigneExiste, $this->sNomTable);
//        $sRequete = 'UPDATE ' . $this->sNomTable . ' SET ' . implode(', ', $aLigneExiste['aChamps']) . ' '
//                    . 'WHERE 1 ';
//        foreach ($aLigneExiste['oLigne'] as $sChamp => $sValeur) {
//            $sRequete .= 'AND ' . $sChamp . ' = \'' . $this->sEchappe($sValeur) . '\' ';
//        }
//        return $sRequete;
//    }



    public function bInsertionLigneSiAbsente($sCleSynchro, $oUnElement)
    {
        return $this->rConnexion->bInsertionLigneSiAbsente($sCleSynchro, $oUnElement);
    }

    public function bLigneExiste($sCleSynchro, $oUnElement)
    {
        $mClePrimaire = $this->aClePrimaire ?? $this->sNomCle;

        return $this->rConnexion->bLigneExiste($sCleSynchro, $oUnElement, $this->sNomTable, $mClePrimaire, $this->aMappingChamps);
    }

    /**
     * Conversion de date pour inclure dans une
     * requête SQL via MySQL ou SQLite.
     * @param  string $sDate   Date à utiliser.
     * @param  string $sFormat Format de la date au final.
     * @return string          Chaine à inclure dans le SQL.
     */
    protected function sDateFormat($sDate, $sFormat)
    {
        return $this->rConnexion->sDateFormat($sDate, $sFormat);
    }

    /**
     * Retourne la fonction de base de données
     * récupérant le datetime courant.
     *
     * @return string Fonction.
     */
    protected function sDatetimeCourant()
    {
        return $this->rConnexion->sDatetimeCourant();
    }

    /**
     * Concatène des chaines pour inclure dans une
     * requête SQL via MySQL ou SQLite.
     * @param  array $aChaine Chaines à concaténer.
     * @return string         Chaine à inclure dans le SQL.
     */
    protected function sConcat($aChaine)
    {
        return $this->rConnexion->sConcat($aChaine);
    }

    /**
     * Récupération du tableau de correspondance
     * des champs avec leurs alias.
     * @return array Tableau de correspondance.
     */
    public function aGetMappingChamps()
    {
        return $this->aMappingChamps;
    }

    /**
     * Démarrage du process
     * @return void
     */
    public function bDemarreProcess($rConnexion = null)
    {
        $rConnexion = $rConnexion ?? $this->rConnexion;

        if ($this->bSansAnnulationProcess === false) {
            $rConnexion->bDemarreProcess();
        }
    }

    /**
     * Annulation du process.
     * @return void
     */
    public function bAnnuleProcess($rConnexion = null)
    {
        $rConnexion = $rConnexion ?? $this->rConnexion;

        if ($this->bSansAnnulationProcess === false) {
            $rConnexion->bAnnuleProcess();
        }
    }

    /**
     * Validation du process.
     * @return void
     */
    public function bValideProcess($rConnexion = null)
    {
        $rConnexion = $rConnexion ?? $this->rConnexion;

        if ($this->bSansAnnulationProcess === false) {
            $rConnexion->bValideProcess();
        }
    }

    /**
     * @param $sValeur
     * @return string
     */
    public function quote($sValeur): string
    {
        return $this->rConnexion->quote($sValeur);
    }

    protected function nGetLastInsertId()
    {
        if ($this->bIsOracle) {
            $sNomSequence = $this->sNomSequence();
            return $this->rConnexion->nGetLastInsertId($sNomSequence);
        }

        return $this->rConnexion->lastInsertId();
    }

    public function sGetNomChampId()
    {
        if(empty($this->aMappingChamps[$this->sNomCle]) === false)
        {
            return $this->aMappingChamps[$this->sNomCle];
        }

        throw new \Exception("Mapping de la clé primaire {$this->sNomCle} non défini dans le tableau aMappingChamps");

    }

//    public function oPagination($aRecherche = array(), $nNbElementsParPage = 0, $sContexte = '')
//    {
//        $nNbElements = $this->nGetNbElements($aRecherche, '', $sContexte);
//        $nPage = $_REQUEST['nPage'] ?? 1;
//        $oPagination = new Pagination($nNbElements, $nNbElementsParPage, $nPage);
//
//        return $oPagination;
//    }

}