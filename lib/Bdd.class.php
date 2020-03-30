<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\UndeadBrain as UndeadBrain;
use APP\Modules\Base\Lib\CorePDO as CorePDO;
use APP\Modules\Base\Lib\CorePDOSqlite;
use APP\Core\Lib\Interne\PHP\Utiles as Utiles;

class Bdd  extends UndeadBrain
{
    /**
     * Ressource de la connexion
     * @var object
     */
    public $rConnexion;
    public $szVersion;

    private static $aConnexionStatic = array();

    /**
     * Tableau de correspondance des champs et de leurs alias.
     * @var array
     */
    protected $aMappingChamps;

    public $sMessagePDO;
    
    public $aTitreLibelle;

    /** 
     * Nom de la table (Utilisé dans bInsert, bUpdate et bDelete)
     * @var string
     */
    public $sNomTable;

    /**
     * Constructeur de la classe
     *
     * @return void
     */
    public function __construct()
    {
        $this->sMessagePDO = '';

        // echo ' bdd ';
        // echo '-------- avant <pre>'.print_r($this->rConnexion, true).'</pre>';
        if (isset($this->rConnexion) === false) {
            $this->vConnexionBdd();
        }

        $this->sNomTable = "";
        $this->sNomChampIdBdd = "";
    }


    /**
     * Méthode permettant de se connecter à la base de données.
     *
     *
     *
     * @return void
     */

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
                    self::$aConnexionStatic[$sAliasConnexion] = new \APP\Modules\Base\Lib\CorePDOSqlite('sqlite:'.$sHote);
                } 
                else
                {
                    self::$aConnexionStatic[$sAliasConnexion] = new \APP\Modules\Base\Lib\CorePDO('mysql:host='.$sHote.';dbname='.$sNomBase, $sUtilisateur, $sMotDePasse);
                    // echo 'mysql:host='.$sHote.';dbname='.$sNomBase, $sUtilisateur, $sMotDePasse."<br/>\n";
                    // echo 'mysql:host='.$GLOBALS['aParamsBdd']['hote'].';dbname='.$GLOBALS['aParamsBdd']['base'], $GLOBALS['aParamsBdd']['utilisateur'], $GLOBALS['aParamsBdd']['mot_de_passe'];
                    // paramètrage de l'encodage en UTF-8

                    self::$aConnexionStatic[$sAliasConnexion]->query('SET NAMES \''.str_replace('-', '', $sEncodage).'\';');
                    self::$aConnexionStatic[$sAliasConnexion]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }

                // echo '-------- après <pre>'.print_r($this->rConnexion, true).'</pre>';
            }
            catch( PDOException $e )
            {
                echo 'Unable to connect to the database : ' . $e->getMessage();
                exit;
            }
        }
        $this->$sAliasConnexion = self::$aConnexionStatic[$sAliasConnexion];
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
        $rLien = $this->$sAliasConnexion->query($szRequete);

        if ($rLien) {
            $aResult = $rLien->fetchAll(\PDO::FETCH_OBJ);

            // echo '<pre>'.print_r($aResult, true).'</pre>';

            foreach( $aResult as $objRow )
            {
                if (is_array($aMappingChamps) && count($aMappingChamps)) {
                    $objResultat = new \StdClass();

                    foreach ($objRow as $szCleLigne => $szValeur) {
                        if (isset($aMappingChamps[$szCleLigne])) {
                            $szCleObjet = $aMappingChamps[$szCleLigne];
                        } else {
                            $szCleObjet = $szCleLigne;
                        }
                        $objResultat->$szCleObjet = $szValeur;
                    }
                    if ($this->aTitreLibelle) {
                        $sTitreLibelle = "";
                        foreach ($this->aTitreLibelle as $sNomChamp) {
                            if (isset($objRow->$sNomChamp) === true) {
                                $sTitreLibelle .= $objRow->$sNomChamp." ";
                            }
                        }
                        if ($sTitreLibelle != '') {
                            $objResultat->sTitreLibelle = trim($sTitreLibelle);
                        }
                    }

                    $aResultat[] = $objResultat;
                } else {
                    $aResultat[] = $objRow;
                }
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


    /**
     * Convertie les critères d'un champ sous forme de tableau en chaine.
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
    public function aGetElements($aRecherche = array(), $nStart = 0, $nNbElements = '', $szOrderBy = '', $szGroupBy = '', $szContexte = '')
    {

        if ($nStart == '') {
            $nStart = 0;
        }

        $szRequete = $this->szGetSelect($aRecherche, $szOrderBy, false, $nStart, $nNbElements, $szGroupBy, $szContexte);

        if ($nNbElements && $nNbElements != 0) {
            $szRequete .= ' LIMIT '.$nStart.', '.$nNbElements;
        }

        // echo '<pre>'.$szRequete.'</pre>';

        $aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);

        // echo '<pre>'.print_r($aResultats, true).'</pre>';

        return $aResultats;
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

        $szRequete = $this->szGetSelect($aRecherche, '', true, '', '', $szGroupBy, $szContexte);

        // echo '<pre>'.$szRequete.'</pre>';

        $aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);

        if (isset($aResultats[0]->nNbElements) === true) {
            $nRetour = $aResultats[0]->nNbElements;
        }

        return $nRetour;
    }

    public function aListeTables()
    {
        $aTables = array();

        if (isset($this->rConnexion) === false) {
            $this->vConnexionBdd();
        }

        $sRequete = "SHOW tables FROM `".$GLOBALS['aParamsBdd']['base']."`";

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
     *
     * @param  string $szTable  Table concernée.
     * @param  string $szChamp  Champ à vérifier.
     * @param  string $szValeur Valeur à trouver.
     *
     * @return boolean          Existe ou non.
     */
    public function bChampUniqueDejaUtilise($szTable = '', $szChamp = '', $szValeur = '')
    {
        $bRetour = true;

        $szRequete = '
            SELECT COUNT(*) AS nNbElements
            FROM '.$szTable.'
            WHERE '.$szChamp.' LIKE \''.$szValeur.'\'
        ';

        // echo "$szRequete";

        $aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);

        if (isset($aResultats[0]) === true) {
            if ($aResultats[0]->nNbElements == 0) {
                $bRetour = false;
            }
        }

        return $bRetour;
    }

    /**
     * Requête de selection pour dynamiser un select2
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
        if ($sOrderBy == '') {
            $sOrderBy = $aChamps[1];
        }
        $szRequete = "

			SELECT ".$aChamps[0]." AS id, ".$aChamps[1]." AS text
			FROM ".$sTable."
            WHERE 1=1 AND (replace(".$aChamps[1].",'-', ' ') LIKE '%" . addslashes($aRecherche['sTerm']) . "%' OR replace(".$aChamps[1].",' ', '-') LIKE '%" . addslashes($aRecherche['sTerm']) . "%') ".$sRestriction."
			ORDER BY ".$sOrderBy." ASC
        ";
        // echo $szRequete."\r\n";
		$aResultats = $this->aSelectBDD($szRequete, $this->aMappingChamps);

        return $aResultats;
	}


    /**
     * Formate les champs de la requête d'insert/update
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
            $aLignes[] = " ".$sUnChamp." = '".addslashes($sUneValeur)."'";
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
    protected function aPreparerChampsPlaceHolderRequete($aChamps = array(), $aChampsNull= array())
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

        $aRetour['sRequete'] .= implode(', ', $aLignes);

        return $aRetour;
    }

    /**
     * Crée la partie de la requête contenant les placeholders (:nomchamp)
     * 
     * @param string[] $aChamps 
     * 
     * @return string
     */
    protected function sPreparerRequetePlaceHolder($aChamps = array(), $aChampsNull= array())
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
        return $this->rConnexion->prepare($sRequete);
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
    protected function bExecuterRequetePrepare($oRequetePrepare, $aChampsPrepare = array())
    {
        $mResultat = false;
        try
        {
            $mResultat = $oRequetePrepare->execute($aChampsPrepare);
        }
        catch (\PDOException $e) {
            // $oUtiles = new Utiles;
            // if (method_exists($oUtiles, 'vLogRequete')) {
            //     $oUtiles->vLogRequete($szRequete, true);
            // }
            /**
             * Contient les infos sur l'erreur SQL :
             * [0] => Code d'erreur SQLSTATE (défini par rapport au standard ANSI SQL)
             * [1] => Code d'erreur du driver spécifique
             * [2] => Message d'erreur spécifique au driver
             * @var array $infosErreur
             */
            $sCodeErreur = $e->getCode();
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
        } finally {
            if (isset($e) === true) {
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
            return $mResultat;
        }
        
    }

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
        $bRetour = false;

        if(empty($this->sNomTable) === true)
        {
            throw new \Exception("sNomTable n'a pas été défini pour ce modèle");
        }

        $aPreparationRequete = $this->aPreparerChampsPlaceHolderRequete($aChamps, $aChampsNull);

        $sRequete = "INSERT INTO {$this->sNomTable} SET " . $aPreparationRequete['sRequete'];
        
        $oRequetePrepare = $this->oPreparerRequete($sRequete);

        
        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aPreparationRequete['aChampsPrepare']);

        if($bRetour === false)
        {
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
        }
        else
        {
            $sNomChampId = $this->sGetNomChampId();
            $this->$sNomChampId = $this->rConnexion->lastInsertId();
            if ($this->sNomTable != 'logs') {
                $this->bSetLog("insert_{$this->sNomTable}", $this->$sNomChampId);
            }
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
        if(empty($this->sNomTable) === true)
        {
            throw new \Exception("sNomTable n'a pas été défini pour le modèle " . get_called_class());
        }

        if(empty($this->sNomCle) === true)
        {
            throw new \Exception("sNomCle n'a pas été défini pour le modèle " . get_called_class());
        }

        $aPreparationRequete = $this->aPreparerChampsPlaceHolderRequete($aChamps, $aChampsNull);


        $sRequete = "UPDATE {$this->sNomTable} SET " . $aPreparationRequete['sRequete']
                  . " WHERE {$this->sNomCle} = :nIdElement";
        
        
        $oRequetePrepare = $this->oPreparerRequete($sRequete);

        //On récupère le nom du champ contenant la clé primaire par le mapping champs
        $sNomChampId = $this->sGetNomChampId();

        //On rajoute l'id élement dans les valeurs préparé pour qu'elle remplace le placeholder
        $aPreparationRequete['aChampsPrepare'][':nIdElement'] = $this->$sNomChampId;

        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aPreparationRequete['aChampsPrepare']);

        if($bRetour === false)
        {
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
        }
        else
        {
            if ($this->sNomTable != 'logs') {
                $this->bSetLog("update_{$this->sNomTable}", $this->$sNomChampId);
            }
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
        if(empty($this->sNomTable) === true)
        {
            throw new \Exception("sNomTable n'a pas été défini pour le modèle " . get_called_class());
        }

        if(empty($this->sNomCle) === true)
        {
            throw new \Exception("sNomCle n'a pas été défini pour le modèle " . get_called_class());
        }

        //Ici pas besoin de générer les champs préparé, on possède uniquement le champ nIdElement
        //On récupère le nom du champ contenant la clé primaire par le mapping champs
        $sNomChampId = $this->sGetNomChampId();

        $aChampsPrepare = array(
            ':nIdElement' => $this->$sNomChampId
        );

        $sRequete = "DELETE FROM {$this->sNomTable} WHERE {$this->sNomCle} = :nIdElement";

        $oRequetePrepare = $this->oPreparerRequete($sRequete);

        $bRetour = $this->bExecuterRequetePrepare($oRequetePrepare, $aChampsPrepare);

        if($bRetour === false)
        {
            $this->sMessagePDO = $this->rConnexion->sMessagePDO;
        }
        else
        {
            if ($this->sNomTable != 'logs') {
                $this->bSetLog("delete_{$this->sNomTable}", $this->$sNomChampId);
            }
        }

        return $bRetour;
    }

    /**
     * Renvoie le champ mappé sur sNomCle
     * 
     * @return string Nom du champ mappé (Ex : nIdTrucMuche)
     */
    private function sGetNomChampId(){
        if(empty($this->aMappingChamps[$this->sNomCle]) === false)
        {
            return $this->aMappingChamps[$this->sNomCle];
        }

        throw new \Exception("Mapping de la clé primaire {$this->sNomCle} non défini dans le tableau aMappingChamps");
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
        $sRequete = '
            CASE
                '.$sNomChamp.'
        ';
        foreach ($aLibelle as $sValeur => $sLibelle) {
            $sRequete .= '
                WHEN
                    \''.$sValeur.'\'
                THEN
                    \''.addslashes($sLibelle).'\'
            ';
        }
        $sRequete .= '
                ELSE
                    '.$sNomChamp.'
            END
        ';
        return $sRequete;
    }

    /**
     * Renvoie le champ mappé sur le champ
     * @param  string $sNomChamp nom du champ
     * 
     * @return string Nom du champ mappé (Ex : nIdTrucMuche)
     */
    public function sGetNomChamp($sNomChamp = '') {
        $sRetour = '';
        if(isset($this->aMappingChamps[$sNomChamp])) {
            $sRetour = $this->aMappingChamps[$sNomChamp];
        }
        return $sRetour;
    }

    /**
     * Récupération des données pour impression de PDF
     * @param  array  $aRecherche
     * @param  string $sOrderBy
     * @param  string $sGroupBy
     * @param  string $sContexte
     * @return array
     */
    public function aRecupereDonneesPdf($aRecherche = array(), $sOrderBy = '', $sGroupBy = '', $sContexte = '') {
        $aRetour = $this->aGetElements($aRecherche, 0, '', $sOrderBy, $sGroupBy, $sContexte);
        return $aRetour;
    }
}