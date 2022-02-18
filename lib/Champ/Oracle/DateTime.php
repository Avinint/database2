<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class DateTime extends Date
{
    protected $sFormatLibelle = 'DD/MM/YYYY HH24:MI:SS';
    protected $sFormatSQL = 'YYYY-MM-DD HH24:MI:SS';

    public function sGenererPlaceholderChampPrepare()
    {
        return "TO_DATETIME(:$this->sColonne, '$this->sFormatLibelle')";
    }
}