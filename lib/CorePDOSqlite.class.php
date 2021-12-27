<?php

namespace APP\Modules\Base\Lib;

class CorePDOSqlite extends \PDO
{
    public $sMessagePDO = '';
    public $sRequeteErreur = '';

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

        $this->sRequeteErreur = '';
        $this->sMessagePDO = '';
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
            $this->sRequeteErreur = $szRequete;
            if (method_exists('\Log', 'vLog')) {
                \Log::vSaveLog('critical', $this->sRequeteErreur);
                \Log::vSaveLog('critical', print_r($this->sMessagePDO, true));
            } else {
                error_log($this->sRequeteErreur);
                error_log(print_r($this->sMessagePDO, true));
            }
        }
        return $mResult;
    }

    public function aSelectBDD($szRequete = '', $aMappingChamps = array(), $szAlias = '')
    {
        $this->sRequeteErreur = '';
        $this->sMessagePDO = '';
        $GLOBALS['szLogs'] .= '<pre>'.$szRequete.'</pre>';
        
        $mResult = parent::aSelectBDD($szRequete);
        
        if ($mResult === false) {
            $this->sMessagePDO = parent::errorInfo();
            $this->sRequeteErreur = $szRequete;
            if (method_exists('\Log', 'vLog')) {
                \Log::vSaveLog('critical', $this->sRequeteErreur);
                \Log::vSaveLog('critical', print_r($this->sMessagePDO, true));
            } else {
                error_log($this->sRequeteErreur);
                error_log(print_r($this->sMessagePDO, true));
            }
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

    /**
     * Conversion de date pour inclure dans une
     * requête SQL via MySQL ou SQLite.
     * @param  string $sDate   Date à utiliser.
     * @param  string $sFormat Format de la date au final.
     * @return string          Chaine à inclure dans le SQL.
     */
    public function sDateFormat($sDate, $sFormat)
    {
        if (!preg_match('/\./', $sDate)) {
            $sDate = "'" . $sDate . "'";
        }

        $sFormat = str_replace(['%i', '\h'], ['%M', 'h'], $sFormat);

        return 'strftime("' . $sFormat . '", ' . $sDate . ')';
    }

    /**
     * Retourne la fonction de base de données
     * récupérant le datetime courant.
     *
     * @return string Fonction.
     */
    public function sDatetimeCourant()
    {
        return 'datetime(\'now\')';
    }

    /**
     * Concatène des chaines pour inclure dans une
     * requête SQL via SQLite.
     * @param  array $aChaine Chaines à concaténer.
     * @return string         Chaine à inclure dans le SQL.
     */
    public function sConcat($aChaine)
    {
        $aChaine = array_map(function($sChaine)
        {
            if (preg_match('/strftime|\./', $sChaine)) {
                return $sChaine;
            } else {
                return "'" . $sChaine . "'";
            }
        }, $aChaine);

        return implode(" || ", $aChaine);

    }

    /**
     * Démarrage du process
     * @return void
     */
    public function bDemarreProcess()
    {
        $this->query('BEGIN TRANSACTION;');
    }

    /**
     * Annulation du process.
     * @return void
     */
    public function bAnnuleProcess()
    {
        $this->query('ROLLBACK;');
    }

    /**
     * Validation du process.
     * @return void
     */
    public function bValideProcess()
    {
        $this->query('COMMIT;');
    }
}
