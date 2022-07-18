<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereNonNull extends Critere
{
    public function __construct($sCle, $mValeur)
    {
        parent::__construct($sCle, 'NULL', 'IS NOT');
    }
}