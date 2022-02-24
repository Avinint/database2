<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class CritereInexistant extends Critere
{
    public function __construct($cCritere)
    {
        error_log("Le critère de recherche $cCritere n'existe pas");
    }
}