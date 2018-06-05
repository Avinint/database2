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
            switch (parent::errorCode()) {
                case '23000':
                    $this->sMessagePDO = "Attention : cet élément est lié à un ou plusieurs autre(s). Il ne peut être supprimé.";
                    break;
                
                default:
                    $this->sMessagePDO = $e->getMessage();
                    break;
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
