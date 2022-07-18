<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereNull extends Critere
{
    public function __construct($sCle, $mValeur)
    {
        parent::__construct($sCle, 'NULL', 'IS');
    }
}