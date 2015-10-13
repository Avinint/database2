<?php

namespace APP\Modules\Base\Lib;
use APP\Core\Lib\Interne\Utiles as Utiles;

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

        return parent::query($szRequete);
    }
}
