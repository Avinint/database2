<?php

namespace APP\Modules\Base\Lib\Recherche;

use APP\Modules\Base\Lib\Mapping;
use APP\Ressources\Base\Lib\Recherche\Critere\CritereParser;
use APP\Ressources\Base\Lib\Recherche\Critere\CritereEgal;
use APP\Ressources\Base\Lib\Recherche\Critere\CritereInexistant;
use APP\Ressources\Base\Lib\Recherche\Critere\CritereInterface;

/**
 *  Génère une recherche SQL (clause WHERE)
 *  à partir de critères de recherche brut (type clé valeur)
 */
class Recherche implements RechercheInterface
{
    /* Permet de connaitre la totalité des critères de la recherche avant la fin du traitement */
    private $aRechercheBrut = [];
    private $aCriteres = [];
    private $sTexte  = '';
    protected $oMapping;
    protected $aListeCritereSpecifique = [];

    public function __construct($oMapping)
    {
        $this->oMapping = $oMapping;
    }

    /**
     * @var array
     * a liste des critères liés aux recherches sur le modèle en cours
     * (Recherches avec champs avec names permettant de savoir qu'on va utiliser des opérateurs
     * comme LIKE, NOT, >, <, etc.
     */

    /** Génération et mise en cache de la recherche si absente du cache
     * @param $aRecherche
     * @return $this;
     */
    public function vAjouterCriteresRecherche($aRecherche)
    {
        $this->vGenererRecherche($aRecherche);

        return $this;
    }

    /**
     * Génération de la recherche
     * @param $aRecherche
     */
    public function vGenererRecherche($aRecherche)
    {
        $this->aRechercheBrut = $aRecherche;
        $this->aCriteres = [];
        $aRechercheCritereSpecifique = array_intersect_key($aRecherche, $this->aListeCritereSpecifique);

        foreach ($aRechercheCritereSpecifique as $sCle => $sValeur) {
            $oCritere = $this->oGenererCritereSpecifique($sCle);
            unset ($aRecherche[$sCle]);

            if ($oCritere->bDoitEtreAjoute()) {
                $this->vAjouterCritere($oCritere);
            }
        }

        foreach ($aRecherche as $sCle => $sValeur) {
            if (isset($sValeur)) {
                $oCritere = $this->oGenererCritere($sCle, $sValeur);
                if (!$oCritere instanceof CritereInexistant || $oCritere->bDoitEtreAjoute()) {
                    $this->vAjouterCritere($oCritere);
                }
            }
        }
    }

    /**
     *  Les criteres personnalisés sont des critères qui ne rentrent pas dans les catégories habituelles
     *  On crée une classe critère dédiée pour ces cas et on leur donne accès à la totalité des critères
     * @param $sCle
     * @return mixed
     */
    public function oGenererCritereSpecifique($sCle)
    {
        [$sCle, $cCritere] = $this->aGetListeCritereSpecifique($sCle);

        $oChamp = $this->oGetChamp($sCle) ?? null;

        if ($oChamp) {
            $oCritere = new $cCritere($oChamp, $this->aGetRechercheBrut());

            return $oCritere;
        }

        return new CritereInexistant($cCritere);
    }

    /**
     * Factory qui génère un critère de recherche à partir des champs du modèle
     * et des suffixes permettant de changer les opérateurs
     * @param $sCle
     * @param $sValeur
     * @param string $sOperateurLogique
     * @param string $sOperateur
     * @return CritereInterface
     */
    public function oGenererCritere($sCle, $sValeur)
    {
        if ($oChamp = $this->oGetChamp($sCle)) {
            return new CritereEgal($oChamp, $sValeur);
        } else {
            return CritereParser::oDeduireCritere(
                $sCle,
                $sValeur,
                $this->oGetMapping()
            );
        }
    }

    /**
     * @param CritereInterface $oCritere
     */
    public function vAjouterCritere(CritereInterface $oCritere)
    {
        if ($oCritere->bDoitEtreAjoute()) {
            if (empty($this->aCriteres)) {
                $oCritere->vSetOperateurLogique('');
            }

            if ($oCritere->nGetErreur()) {
                $this->aErreur[] = $oCritere->nGetErreur();
            }

            $this->aCriteres[] = $oCritere;
        }
    }

    /**
     * @return array
     */
    public function aGetCriteres(): array
    {
        return $this->aCriteres;
    }

    public function vAjouterCriteresSpecifiques($aCritere = [])
    {
        $this->aListeCritereSpecifique = $aCritere;
    }

    public function bCritereSpecifiqueExiste($sCle)
    {
        return isset($this->aListeCritereSpecifique[$sCle]);
    }

    public function aGetListeCritereSpecifique($sCle = null)
    {
        return empty($sCle) ?
            $this->aListeCritereSpecifique :
            ($this->aListeCritereSpecifique[$sCle] ?? null);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->sGetTexte();
    }

    /**
     * @return Mapping
     */
    public function oGetMapping()
    {
        if (is_null($this->oMapping)) {
            throw new \Exception('Le Mapping des champs doit être transmis à la recherche');
        }
        return $this->oMapping;
    }

    public function vSetMapping(Mapping $oMapping)
    {
        $this->oMapping = $oMapping;
    }

    /**
     * @return string
     */
    public function sGetTexte(): string
    {
        return trim(implode(PHP_EOL . "    ",  $this->aCriteres));
    }

    /**
     * @param string $sTexte
     * @return bool
     */
    public function bSetTexte(string $sTexte) : bool
    {
        $this->sTexte = $sTexte;

        return true;
    }

    public function oGetChamp($sCle)
    {
        return $this->oGetMapping()[$sCle] ?? null;
    }

    /**
     * @return array
     */
    public function aGetRechercheBrut(): array
    {
        return $this->aRechercheBrut;
    }


}