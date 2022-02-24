<?php

namespace APP\Modules\Base\Lib\Champ;

class DateTime extends Date
{
    protected $sFormatLibelle = 'd/m/Y H:i:s';
    protected $sFormatSQL = 'Y-m-d H:i:s';
}