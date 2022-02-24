<?php

namespace APP\Ressources\Base\Lib\Exception;

class ChampInexistantException extends \Exception
{
    public function __construct($sNomChamp, $cNomMapping)
    {
        $cNomModele = str_replace('Mapping', '', $cNomMapping);

        $sMessage = "Utilisation du champ \"$sNomChamp\" inexistant dans $cNomMapping comme parametre de m&eacute;thode dans $cNomModele";

        parent::__construct($sMessage);
    }
}