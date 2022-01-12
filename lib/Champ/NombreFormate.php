<?php

namespace APP\Modules\Base\Lib\Champ;

class NombreFormatte extends Nombre
{
    public function sTraiterValeur($sValeur) : string
    {
        return (int) preg_replace('/[^\d\.\-]+/', '', $sValeur);
    }

}