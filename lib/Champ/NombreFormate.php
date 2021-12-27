<?php

namespace APP\Modules\Base\Lib\Champ;

class NombreFormate extends Nombre
{
    public function sGetValeurEnregistree($sValeur) : string
    {
        return (int) preg_replace('/[^\d\.\-]+/', '', $sValeur);
    }

}