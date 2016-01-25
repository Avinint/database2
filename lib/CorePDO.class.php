<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\PHP\Utiles as Utiles;

class CorePDO extends \PDO
{
    public function __construct($szBase = '', $szLogin = '', $szMotDePasse = '')
    {
        parent::__construct($szBase, $szLogin, $szMotDePasse);
    }

    public function query($szRequete = '')
    {
        // if (preg_match('/(SELECT[\s])/', $szRequete)/* && preg_match('/easynot/', $szRequete)*/) {
        //     $objUtiles = new Utiles();
        //     $szRequete = $objUtiles->szGetRequeteOptimisee($szRequete);
        // // echo "<pre>$szRequete</pre>";
        // }

        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';

        return parent::query($szRequete);
    }

    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
    {
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';

        return parent::aSelectBDD($szRequete);
    // {
    //     $objMemCache = new \Memcache;
    //     $objMemCache->connect($GLOBALS['aParamsAppli']['memcache']['serveur'], $GLOBALS['aParamsAppli']['memcache']['port']) or die ("Could not connect");

    //     $szCle = md5($szRequete);

    //     $aRetour = $objMemCache->get($szCle);
    //     if ($szContenuCache != '') {
    //         echo "<pre>".print_r($aRetour, true)."</pre>";
    //         return $aRetour;
    //     } else {
    //         $aRetour = parent::__construct($szRequete, $aMappingChamps, $szAlias);
    //         $objMemCache->set($szCle, $aRetour, MEMCACHE_COMPRESSED, 1200);
    //         return $aRetour;
    //     }

    }
}
