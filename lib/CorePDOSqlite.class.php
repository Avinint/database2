<?php

namespace APP\Modules\Base\Lib;

class CorePDOSqlite extends \PDO
{
    public $sMessagePDO = '';

    public function __construct($szBase = '')
    {
        parent::__construct($szBase);
    }

    public function query($szRequete = '')
    {
        // if (preg_match('/(SELECT[\s])/', $szRequete)/* && preg_match('/easynot/', $szRequete)*/) {
        //     $objUtiles = new Utiles();
        //     $szRequete = $objUtiles->szGetRequeteOptimisee($szRequete);
        // // echo '<pre>'.$szRequete.'</pre>';
        // }

        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';
        $aRequetes = explode(';', $szRequete);
        foreach ($aRequetes as $szUneRequete) {
            $szUneRequeteSansEspace = preg_replace('/\s/', '', $szUneRequete);
            if ($szUneRequeteSansEspace != '') {
                $mResult = parent::query($szUneRequete.';');
                if ($mResult === false) {
                    continue;
                }
            }
        }
        
        if ($mResult === false) {
            $this->sMessagePDO = parent::errorInfo();
            error_log(print_r($this->sMessagePDO, true));
        }
        return $mResult;
    }

    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
    {
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';
        
        $mResult = parent::aSelectBDD($szRequete);
        
        if ($mResult === false) {
            $this->sMessagePDO = parent::errorInfo();
            error_log(print_r($this->sMessagePDO, true));
        }

        return $mResult;
    // {
    //     $objMemCache = new \Memcache;
    //     $objMemCache->connect($GLOBALS['aParamsAppli']['memcache']['serveur'], $GLOBALS['aParamsAppli']['memcache']['port']) or die ('Could not connect');

    //     $szCle = md5($szRequete);

    //     $aRetour = $objMemCache->get($szCle);
    //     if ($szContenuCache != '') {
    //         echo '<pre>'.print_r($aRetour, true).'</pre>';
    //         return $aRetour;
    //     } else {
    //         $aRetour = parent::__construct($szRequete, $aMappingChamps, $szAlias);
    //         $objMemCache->set($szCle, $aRetour, MEMCACHE_COMPRESSED, 1200);
    //         return $aRetour;
    //     }

    }
}
