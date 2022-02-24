<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

interface CritereInterface
{
    public function sGetTexte();

    public function sGetOperateurLogique();

    public function vSetOperateurLogique(string $sOperateur);

    public function bDoitEtreAjoute() : bool;
}