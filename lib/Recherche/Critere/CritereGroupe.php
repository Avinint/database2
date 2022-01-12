<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

class CritereGroupe extends Critere
{
    protected $sOperateurLogique = 'AND';

    public function __construct(array $aCritere, $bOpLogique = true, $bParentheses = true)
    {
        $this->aCritere = $aCritere;
        $this->bParentheses = $bParentheses;
        $this->bOpLogique = $bOpLogique;
    }

//    public static function xValidation($sValeur) : void
//    {
//        if (!is_array($sValeur) || empty($sValeur)) {
//            throw new \LogicException("Valeur de Critère groupé invalide, doit être un tableau d'au moins 1 élément");
//        }
//    }

    public function sGetTexte(): string
    {
        $sTexteCriteres = implode(' ', $this->aCritere);

        $sTexte = $this->bParentheses ? "($sTexteCriteres".PHP_EOL.")" : "$sTexteCriteres".PHP_EOL;

        return ($this->bOpLogique ? $this->sOperateurLogique . ' ' : '') . $sTexte;
    }

    public function bDoitEtreAjoute() : bool
    {
        return !empty($this->aCritere);
    }
}