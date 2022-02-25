<?php

namespace APP\Ressources\Base\Lib\Recherche\Critere;

use APP\Modules\Base\Lib\Champ\Champ;

class Critere implements CritereInterface
{
    protected $sCle;
    protected $mValeur;
    protected $sOperateur;
    protected $sOperateurLogique;

    protected $nErreur = 0;

    public function __construct(Champ $oChamp, $mValeur, $sOperateur = '=', $sOperateurLogique = 'AND')
    {
        $this->sCle = static::sGenererCle($oChamp);
        $this->mValeur = $mValeur;
        $this->sOperateur = $sOperateur;
        $this->sOperateurLogique = $sOperateurLogique;
    }

    /**
     * Génère la clé du critère, c'est à dire la colonne sur laquelle la recherche est filtrée, mais on peut
     * éventuellement aussi personnaliser avec des procédures comme LOWER UPPER etc pour le pb des LIKE sous Oracle .
     * @param Champ $oChamp
     * @return string
     */
    protected static function sGenererCle(Champ $oChamp)
    {
        return $oChamp->sGetColonnePrefixee();
    }

    /**
     * @return string
     */
    public function sGetOperateurLogique()
    {
        return $this->sOperateurLogique;
    }

    /**
     * @param string $sOperateur
     */
    public function vSetOperateurLogique(string $sOperateur)
    {
        $this->sOperateurLogique = $sOperateur;
    }

    public function __toString()
    {
        return $this->sGetTexte();
    }

    public function sGetTexte() : string
    {
        return "$this->sOperateurLogique $this->sCle $this->sOperateur $this->mValeur";
    }

    public function sCle()
    {
        return $this->sCle;
    }

    public function bDoitEtreAjoute() : bool
    {
        return isset($this->mValeur);
    }

    /**
     * @return int
     */
    public function nGetErreur(): int
    {
        return $this->nErreur;
    }

}