<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\UndeadBrain as UndeadBrain;
use APP\Modules\Base\Lib\CorePDO as CorePDO;
use APP\Modules\Base\Lib\CorePDOSqlite;

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

                if ($sHote == '') {
                    $sHote = $GLOBALS['aParamsBdd']['hote'];
                }
                if ($sNomBase == '') {
                    $sNomBase = $GLOBALS['aParamsBdd']['base'];
                }
                if ($sUtilisateur == '') {
                    $sUtilisateur = $GLOBALS['aParamsBdd']['utilisateur'];
                }
                if ($sMotDePasse == '') {
                    $sMotDePasse = $GLOBALS['aParamsBdd']['mot_de_passe'];
                }
                if ($sEncodage == '') {

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
                if ($bSqlite === true) {
                    self::$aConnexionStatic[$sAliasConnexion] = new \APP\Modules\Base\Lib\CorePDOSqlite('sqlite:'.$sHote);
                } else {
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
                if (count($aMappingChamps)) {
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
        if (($nNbElements == 0 || $nNbElements == '') && isset($_REQUEST['nNbElementsParPage']) === true) {
            // $nNbElements = $_REQUEST['nNbElementsParPage'];
        }

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
            WHERE 1=1 AND (replace(".$aChamps[1].",'-', ' ') LIKE '%" . $aRecherche['sTerm'] . "%' OR replace(".$aChamps[1].",' ', '-') LIKE '%" . $aRecherche['sTerm'] . "%') ".$sRestriction."
			ORDER BY ".$sOrderBy." ASC
        ";
        
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


}