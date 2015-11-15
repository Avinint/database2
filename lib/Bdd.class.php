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
        // echo "-------- avant <pre>".print_r($this->rConnexion, true)."</pre>";
        if (isset($GLOBALS['rConnexionBDD']) === false || is_object($GLOBALS['rConnexionBDD']) === false) {
            $this->vConnexionBdd();
        }
    }


    /**
     * Méthode magique permettant d'intercepter tous les appels à des méthodes
     * qui n'existent pas.
     * Nous l'utilisons afin de surcharger éventuellement certaines méthodes.
     *
     * @param  string   $szMethode      Méthode à appeler.
     * @param  array    $aParametres    Paramètres de la méthode.
     *
     * @return boolean                  Retourne le résultat de la méthode.
     */
    public function __call($szMethode = '', $aParametres = array())
    {
        $aRetour = array(
            'bSurcharge' => false,
            'bSucces' => false
        );

        // Si le premier caractère de la méthode est un underscore, on le supprime.
        if (substr($szMethode, 0, 1) == '_') {
            $szMethode = substr($szMethode, 1, strlen($szMethode));
        }

        // On récupère le namespace de la classe courante.
        $szNameSpaceClasse = get_class($this);

        // On récupère le nom de la classe courante.
        $aClasse = explode('\\', $szNameSpaceClasse);
        $szNomClasse = array_pop($aClasse);

        // On prépare le chemin de la classe de surcharge à chercher.
        $szClasseCible = str_replace($szNomClasse, $szNomClasse.'Surcharge', $szNameSpaceClasse);
        $szCheminClasseCible = $_SERVER['DOCUMENT_ROOT'].'/modules/'.strtolower($aClasse[2]).'/models/'.$szNomClasse.'Surcharge.class.php';

        // Si la classe de surcharge est présente, on l'instancie et on l'appelle.
        if (file_exists($szCheminClasseCible)) {
            $obj = new $szClasseCible();
            if (method_exists($obj, $szMethode)) {
                return eval('return $obj->$szMethode(\''.implode("', '", $aParametres).'\', $this);');
            }
        }

        // S'il n'y a pas eu de surcharge, on appelle la méthode notmale.
        return eval('return $this->$szMethode(\''.implode("', '", $aParametres).'\');');
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
     * @param  string $szRequete      La requête SQL
     * @param  array  $aMappingChamps Un tableau associatif avec comme clé les champs SQL et comme valeur les attributs à retourner
     * @param  string $szTypeDeDonnee Type de données
     * @return array  $aResultat Les résultats
     */
    public function aSelectBDD($szRequete, $aMappingChamps = array(), $szTypeDeDonnee = "")
    {
        $aResultat = array();

        $bNoCache = false;
        if ((isset($GLOBALS['aParamsAppli']['cache']['base']) === false ||
                $GLOBALS['aParamsAppli']['cache']['base'] == 'non') ||
                isset($_REQUEST['bNoCache']) === true ||
                isset($_REQUEST['szIdBloc']) === true && $GLOBALS['aModules'][$_REQUEST['szModule']]['blocs'][$_REQUEST['szIdBloc']]['cache']['html'] == 'non') {
            $bNoCache = true;
        }

        if ($bNoCache === false) {
            $objMemCache = new \Memcache;
            $objMemCache->connect('localhost', 11211) or die ("Could not connect");

            $szCle = md5($szRequete);

            $aRetour = $objMemCache->get($szCle);
            // echo "$szCle : <pre>".print_r($aRetour, true)."</pre>";
            // echo "cache BDD\n";
            if ($aRetour != '') {
                return $aRetour;
            }
        }

        $rLien = $this->rConnexion->query($szRequete);

        if ( $rLien )
        {
            $aResult = $rLien->fetchAll();

            foreach( $aResult as $objRow )
            {
                if (count($aMappingChamps)) {
                    if ($szTypeDeDonnee) {
                        $szTypeDeDonnee = $this->szGetBonType($szTypeDeDonnee, true);
                        $objResultat = new $szTypeDeDonnee();
                    } else {
                        $objResultat = new \StdClass();
                    }

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
            }
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
}