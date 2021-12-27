<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Booleen extends Nombre
{
    public function bActifDansRecherche($mValeur)
    {
        return $mValeur !== 'nc';
    }
}