<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereDroite extends Critere
{
    public function __construct($sCle, $mValeur)
    {
        parent::__construct($sCle, "%$mValeur", 'LIKE');
    }
}