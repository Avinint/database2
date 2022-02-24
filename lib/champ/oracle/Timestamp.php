<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Timestamp extends DateTime
{
    public function sGenererPlaceholderChampPrepare()
    {
        return "TO_TIMESTAMP(:$this->sColonne, '$this->sFormatLibelle')";
    }
}