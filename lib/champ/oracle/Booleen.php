<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Booleen extends Nombre
{
    public function bEstRenseigne($mValeur)
    {
        return $mValeur !== 'nc';
    }
}