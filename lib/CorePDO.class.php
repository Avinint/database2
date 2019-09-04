<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\Utiles as Utiles;

class CorePDO extends \PDO
{
    public $sMessagePDO;

    public function __construct($szBase = '', $szLogin = '', $szMotDePasse = '')
    {
        $this->sMessagePDO = '';
        parent::__construct($szBase, $szLogin, $szMotDePasse);
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
            $this->sMessagePDO = $this->sGetMessagePDO($e);
            throw $e;
        } finally {
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


    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
    {
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';

        return parent::aSelectBDD($szRequete);
    }

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
}
