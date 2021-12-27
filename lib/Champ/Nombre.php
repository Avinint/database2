<?php

namespace APP\Modules\Base\Lib\Champ;

class Nombre extends Char
{
    public function sGetValeurEnregistree($mValeur) : string
    {
        return (int)($mValeur);
    }

    public function sFormatterValeurSQL($mValeur)
    {
        return $this->sGetValeurEnregistree($mValeur);
    }

}