<?php

namespace APP\Modules\Base\Lib\MySQL;

class Pagination
{
    public $nStart = 0;
    public $nPage;
    public $nNbElements;
    public $nParPage = 20;

    public $nNbPages = 1;
    public $nMinRange = 1;
    public $nMaxRange = 1;

    public function __construct($nNbElements, $nPage = 1, $nNbElementsParPage = 20)
    {
        $this->nPage = $nPage;
        $this->nStart = ($this->nPage * $nNbElementsParPage) - $nNbElementsParPage;
        $this->nNbElements = $nNbElements;
        $this->nParPage = $nNbElementsParPage;

        $this->setNombrePages();

        $this->setNombreLiensAffiches();
    }

    protected function setNombrePages()
    {
        if ($this->nParPage > 0) {
            $this->nNbPages = ceil($this->nNbElements / $this->nParPage);
        }
    }

    protected function setNombreLiensAffiches()
    {
        $this->nMaxRange = $this->nNbPages;

        if ($this->nPage - 4 > 1) {
            $this->nMinRange = $this->nPage - 4;
        }
        if ($this->nPage + 4 < $this->nNbPages) {
            $this->nMaxRange = $this->nPage + 4;
        }
    }

}