<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\UndeadBrain as UndeadBrain;
use APP\Modules\Base\Lib\CorePDO as CorePDO;

class Bdd  extends UndeadBrain
{
    /**
     * Ressource de la connexion
     * @var object
     */
    public $rConnexion;
    public $szVersion;

    /**
     * Constructeur de la classe
     *
     * @return void
     */
    public function __construct()
    {
        // echo " bdd ";
        // echo "-------- avant <pre>".print_r($this->rConnexion, true)."</pre>";
        if (isset($this->rConnexion) === false) {
            $this->vConnexionBdd();
        }
    }


    /**
     * Méthode permettant de se connecter à la base de données
     * @param string $szVersion gregor4 ou gregor3
     * @return void
     */
    public function vConnexionBdd()
    {
        // echo "<pre>".print_r($GLOBALS['aParamsBdd'], true)."</pre>";
        try
        {
            $this->rConnexion = new CorePDO("mysql:host=".$GLOBALS['aParamsBdd']['hote'].";dbname=".$GLOBALS['aParamsBdd']['base'], $GLOBALS['aParamsBdd']['utilisateur'], $GLOBALS['aParamsBdd']['mot_de_passe']);
// echo "mysql:host=".$GLOBALS['aParamsBdd']['hote'].";dbname=".$GLOBALS['aParamsBdd']['base'], $GLOBALS['aParamsBdd']['utilisateur'], $GLOBALS['aParamsBdd']['mot_de_passe'];
            // paramètrage de l'encodage en UTF-8
            $this->rConnexion->query("SET NAMES utf8;");

            $GLOBALS['rConnexionBDD'] = $this->rConnexion;
            // echo "-------- après <pre>".print_r($this->rConnexion, true)."</pre>";
        }
        catch( PDOException $e )
        {
            echo 'Unable to connect to the database : ' . $e->getMessage();
            exit;
        }
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

            $objMemCache = new \Memcache;
            $objMemCache->connect($GLOBALS['aParamsAppli']['memcache']['serveur'], $GLOBALS['aParamsAppli']['memcache']['port']) or die ("Could not connect");

            $szCle = md5($szRequete);

            $aRetour = $objMemCache->get($szCle);
            // echo "$szCle : <pre>".print_r($aRetour, true)."</pre>";
            // echo "cache BDD\n";
            if ($aRetour != '') {
                return $aRetour;
            }
        }

        // echo "<pre>$szRequete</pre>";
        $rLien = $this->rConnexion->query($szRequete);

        if ($rLien) {
            $aResult = $rLien->fetchAll(\PDO::FETCH_OBJ);

            // echo "<pre>".print_r($aResult, true)."</pre>";

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

                    $aResultat[] = $objResultat;
                } else {
                    $aResultat[] = $objRow;
                }
            // echo "<pre>".print_r($objRow, true)."</pre>";
            }
            // echo "<pre>".print_r($aResultat, true)."</pre>";
        }
// echo "$szCle : mise en cache\n";
        if ($bNoCache === false) {
            $objMemCache->set($szCle, $aResultat, MEMCACHE_COMPRESSED, 1200);
        }

        return $aResultat;
    }

    public function szGetBonType($szType, $bObjet=false)
    {
        $szType = str_replace($GLOBALS['aParamsAppli']["AppId"].'_', '', $szType);
        if (in_array($szType, array_flip($GLOBALS['aParamsAppli']['namespaces']["data"]))) {
            if ($bObjet == true) {
                $szType = "\\".$GLOBALS['aParamsAppli']['namespaces']["data"][$szType];
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
                $szRegle .= "'".$szCle."' : ".$szValeur;
            }

        }

        return $szRegle;
    }
}