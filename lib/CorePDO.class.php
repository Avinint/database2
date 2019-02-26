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

        try {
            return parent::query($szRequete);
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
            $infosErreur = parent::errorInfo();
            
            switch ($infosErreur[0]) {
                case "23000": {
                    switch ($infosErreur[1]) {
                        //Contrainte d'unicité
                        case 1062 : {
                            $champFautif = str_replace('Duplicate entry ', '', $infosErreur[2]);
                            $champFautif = str_replace(' for key', '', $champFautif);

                            $this->sMessagePDO = "Impossible d'insérer ou de mettre à jour cet élément à cause d'un champ qui doit être unique. Champ posant problème : " . $champFautif;
                            break;
                        }
                        //Erreur de suppression à cause d'un RESTRICT sur une clé étrangère
                        case 1451 : {
                            $this->sMessagePDO = "Attention : cet élément est lié à un ou plusieurs autre(s). Il ne peut être supprimé.";
                            break;
                        }
                    }
                    break;
                }
                
                default: {
                    $this->sMessagePDO = $e->getMessage();
                    break;
                }
            }
            return false;
        }
    }


    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
    {
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';

        return parent::aSelectBDD($szRequete);
    }
}
