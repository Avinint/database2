<?php

namespace APP\Modules\Base\Lib\Champ;

class Nombre extends Char
{
    public function sTraiterValeur($mValeur) : string
    {
        return (int)($mValeur);
    }

    public function sFormatterValeurSQL($mValeur)
    {
        return $this->sTraiterValeur($mValeur);
    }

}