<?php

namespace APP\Modules\Base\Lib;
use APP\Modules\Base\Lib\CorePDO as CorePDO;

class BddGeneration
{
    /**
     * Connexion à la base
     * @var object
     */
    public $objConnexion;

    /**
     * Nom de la table reformatée.
     * @var string
     */
    protected $szTable;

    /**
     * Nom de la table originale
     * @var string
     */
    protected $szTableOriginale;


    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        $this->aChamps = array();

        try
        {
            $this->objConnexion = new CorePDO('mysql:host='.$GLOBALS['aParamsBdd']['hote'].';dbname='.$GLOBALS['aParamsBdd']['base'], $GLOBALS['aParamsBdd']['utilisateur'], $GLOBALS['aParamsBdd']['mot_de_passe']);

            // paramètrage de l'encodage en UTF-8
            if (isset($GLOBALS['aParamsAppli']['encodage']) === false) {
                $GLOBALS['aParamsAppli']['encodage'] = 'UTF-8';
            }
            $this->objConnexion->query('SET NAMES '.$GLOBALS['aParamsAppli']['encodage'].';');
        }
        catch( PDOException $e )
        {
            echo 'Unable to connect to the database : ' . $e->getMessage();
            exit;
        }
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
     * Formate le nom d'un champ.
     *
     * @param  string $szChamp  Nom du champ.
     * @return string           Nom du champ formaté.
     */
    private function szFormatageNomChamp( $szChamp )
    {
        $aChamps = explode('_', $szChamp);
        $szChamp = '';
        foreach( $aChamps as $aChamp )
        {
            $szChamp .= ucfirst( $aChamp );
        }

        return $szChamp;
    }

    /**
     * Récupère les détails d'un champ.
     *
     * @param  string $szType   Type du champ.
     * @return array            Infos du champ.
     */
    private function aGetDetailsType( $szType )
    {
        $szValeurs = '';
        $szPrefixe = '';
        $nLongueur = '';
        $aType = explode('(', $szType);

        if (count($aType) > 1) {
            $szType = $aType[0];
            $nLongueur = str_replace(')', '', $aType[1]);
        }

        if ($szType == 'int' ) {
            $szPrefixe = 'n';
        } else
        if ($szType == 'tinyint' ) {
            $szPrefixe = 'b';
        } else
        if ($szType == 'datetime' || $szType == 'date' || $szType == 'decimal' ) {
            $szPrefixe = 'sz';
        } else
        if ($szType == 'varchar' || $szType == 'text' || $szType == 'enum' ) {
            $szPrefixe = 'sz';
            $szValeurs = str_replace(')', '', $szType);
        }

        return array( $szPrefixe, $szType, $nLongueur, $szValeurs );
    }


    /**
     * Récupère la ou les clés primaires de la table.
     *
     * @return array Le ou les clés primaires.
     */
    protected function aGetClesPrimaires()
    {
        $szQuery = 'SHOW COLUMNS FROM '.$this->szTableOriginale;
        $rResult = $this->objConnexion->query( $szQuery );
        $aCles = array();

        foreach( $rResult as $szCle => $aInfosChamp )
        {
            if ($aInfosChamp['Key'] == 'PRI' )
            {
                $aCles[$szCle]['table'] = $aInfosChamp['Field'];
                $aCles[$szCle]['clean'] = $this->szFormatageNomChamp( $aInfosChamp['Field'] );
                $aDetailsType = $this->aGetDetailsType( $aInfosChamp['Type'] );
                $aCles[$szCle]['prefixe'] = $aDetailsType[0];
                $aCles[$szCle]['type'] = $aDetailsType[1];
            }
        }

        return $aCles;
    }



    /**
     * Récupère les champs d'une table.
     *
     * @return array Champs de la table.
     */
    protected function aGetChamps()
    {
        $szQuery = 'SHOW FULL COLUMNS FROM '.$this->szTableOriginale;
        // echo '<pre>'.$szQuery.'</pre>';
        $rResult = $this->objConnexion->query( $szQuery );
        $aChamps = array();

        foreach( $rResult as $szCle => $aChamp )
        {
            $aChamps[$szCle]['field_clean'] = $this->szFormatageNomChamp( $aChamp['Field'] );
            $aChamps[$szCle]['field'] = $aChamp['Field'];
            $aDetailsType = $this->aGetDetailsType( $aChamp['Type'] );

            $aChamps[$szCle]['prefixe'] = $aDetailsType[0];

            $szCommentaire = $aChamp['Comment'];
            $szType = $aDetailsType[1];
            if (strpos( $aChamp['Comment'], '#' ) )
            {
                $aCommentaire = explode( '#', $aChamp['Comment'] );
                $szCommentaire = $aCommentaire[0];
                $szType = $aCommentaire[1];
            }
            $aChamps[$szCle]['commentaire'] = utf8_encode( $szCommentaire );
            $aChamps[$szCle]['type'] = $szType;

            if (substr( $aChamp['Type'], 0, 4 ) == 'enum' )
            {
                $aChamps[$szCle]['valeurs'] = $aDetailsType[3];
            }

            if (!preg_match('#[^0-9]#', $aDetailsType[2]) )
            {
                $aChamps[$szCle]['longueur'] = $aDetailsType[2];
            }
            else
            {
                $aChamps[$szCle]['choix'] = 'array( '.str_replace(',', ', ', $aDetailsType[2]).' )';
            }

            // on regarde si le champ est requis ou non
            if (isset($aChamps[$szCle]['Null']) && $aChamps[$szCle]['Null'] == 'YES' )
            {
                $aChamps[$szCle]['requis'] = 0;
            }
            else
            {
                $aChamps[$szCle]['requis'] = 1;
            }

            if ($szType == 'password' )
            {
                $aChamps['mot_de_passe_conf']['field'] = 'mot_de_passe_conf';
                $aChamps['mot_de_passe_conf']['prefixe'] = 'sz';
                $aChamps['mot_de_passe_conf']['field_clean'] = 'MotDePasseConf';
                $aChamps['mot_de_passe_conf']['commentaire'] = 'Confirmation';
                $aChamps['mot_de_passe_conf']['type'] = 'password';
                $aChamps['mot_de_passe_conf']['longueur'] = $aDetailsType[2];
                $aChamps['mot_de_passe_conf']['requis'] = true;
            }
        }

        return $aChamps;
    }


    public function bDetecteSiAliasUtiliseDansChaine($szAlias = '', $szChaine = '')
    {
        $bRetour = false;

        if (preg_match('/(\(| )'.$szAlias.'\./', $szChaine)) {
            $bRetour = true;
        }

        return $bRetour;
    }
}

?>