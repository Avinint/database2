<?php

namespace APP\Modules\Base\Lib\RequeteBuilder\Oracle;

use APP\Modules\Base\Lib\RequeteBuilder\MySQL\RequeteBuilder AS BaseRequeteBuilder;
use APP\Modules\Base\Lib\RequeteBuilder\RequeteBuilderInterface;

class RequeteBuilder extends BaseRequeteBuilder
{
    protected $aSelect  = [];
    protected $sFrom = '';
    protected $aJoins = [];
    protected $sWhere = '';
    protected $sGroupBy = '';
    protected $sOrderBy = '';
    protected $sHaving = '';

    public function __construct($oMapping)
    {
        $this->oMapping = $oMapping;
        $this->sFrom = "{$oMapping->sNomTable()} {$this->oMapping->sGetAlias()}";
        $this->sIndentation = str_repeat("\x20", 8);
    }


    public function __toString()
    {
        $sRequete = $this->sGetSelect()
            . $this->sGetFrom()
            . $this->sGetJoins()
            . $this->sGetWhere()
            . $this->sGetGroupBy()
            . $this->sGetOrderBy()
            . $this->sHaving;

        return $this->sPaginerRequete($sRequete);
    }

    /**
     * Ajoute le LIMIT et l'OFFSET à la requête
     * @param int $nNbElements
     * @param int $nStart
     * @param string $szRequete
     * @return string
     */
    public function sPaginerRequete($szRequete)
    {
        if ($this->nNbElements > 0) {

            $szRequete = '
SELECT *
FROM (
    SELECT tmp.*, rownum
    FROM
    (
        ' . $szRequete . '
    ) tmp
    WHERE rownum <= ' . $this->nNbElements . '
) WHERE rownum > ' . $this->nStart . '
    ';
        }

        return $szRequete;
    }

    public function oConcat(array $aElementConcat, $sAlias, $sDelimiteur = '') : RequeteBuilderInterface
    {
        $aChamp = [];
        foreach ($aElementConcat as $sUnElement) {
            $oChamp = $this->oGetChamp($sUnElement) ?? null;
            $aChamp[] = isset($oChamp) ? "{$oChamp->sGetFormatAffichage()}" : "'$sUnElement'";
        }

        if (empty($sDelimiteur)) {
            $this->aSelect[] = implode(' || ', $aChamp) . ' "' . $sAlias . '"';
        } else {
            $this->aSelect[] = implode(' || \'' . $sDelimiteur . '\' || ', $aChamp) . ' "' . $sAlias . '"';
        }

        return $this;
    }

    public function sGetWhere()
    {
        return $this->sWhere ? PHP_EOL . $this->sIndentation . $this->sWhere : '';
    }

}