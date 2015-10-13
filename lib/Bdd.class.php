<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\Utiles as Utiles;
use APP\Modules\Base\Lib\CorePDO as CorePDO;

class Bdd extends Utiles
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
        $rLien = $this->rConnexion->query($szRequete);

        if ( $rLien )
        {
            $aResult = $rLien->fetchAll();
            // echo "toto";
// echo "<pre>".print_r($aResult, true)."</pre>";
// exit;
            foreach( $aResult as $objRow )
            {
                if (count($aMappingChamps)) {
                    if ($szTypeDeDonnee) {
                        $szTypeDeDonnee = $this->szGetBonType($szTypeDeDonnee, true);
                        $objResultat = new $szTypeDeDonnee();
                    } else {
                        $objResultat = new \StdClass();
                    }

                    foreach ($objRow as $szCle => $szValeur) {
                        if (isset($aMappingChamps[$szCle])) {
                            $szCleObjet = $aMappingChamps[$szCle];
                        } else {
                            $szCleObjet = $szCle;
                        }
                        $objResultat->$szCleObjet = $szValeur;
                    }

                    $aResultat[] = $objResultat;
                } else {
                    $aResultat[] = $objRow;
                }
            }
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