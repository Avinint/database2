<?php

namespace APP\Modules\Base\Lib\Champ;

use APP\Modules\Base\Lib\Mapping;
use PDO;

abstract class Champ
{
    protected $sNom;
    protected $sColonne;
    protected $sAlias;
    protected $bEstNullable = true;
    public static $rConnexion;

    public function __construct($sColonne, $sAlias = null)
    {
        $this->sColonne = $sColonne;
        $this->sAlias   = $sAlias;
    }

    public function bEstDate()
    {
        return false;
    }

    public function sGetNom()
    {
        return $this->sNom;
    }

    public function sGetColonne()
    {
        return $this->sColonne;
    }

    public function sGetAlias()
    {
        return $this->sAlias;
    }

    public function sGetColonnePrefixee()
    {
        return $this->sGetAlias() . '.' . $this->sGetColonne();
    }

    /**
     * @param string $sAlias
     * @return Champ
     */
    public function oSetAlias(string $sAlias): Champ
    {
        $this->sAlias = $sAlias;

        return $this;
    }

    /**
     * Permet d'assigner l'alias de la table au champ si un autre alias n'a pas été spécifié
     * @param $sAlias
     * @return $this
     */
    public function oSetAliasParDefaut($sAlias) : Champ
    {
        if (!$this->sAlias) {
            $this->sAlias = $sAlias;
        }

        return $this;
    }

    /**
     * @param string $sNom
     * @return $this
     */
    public function oSetNom(string $sNom): Champ
    {
        $this->sNom = $sNom;

        return $this;
    }

    /**
     * Utilisé pour convertir une valeur à un format sql correct, par exemple un nombre décimal avec un point pour les critère de recherche ou l'écriture
     * @param $mValeur
     * @return string
     */
    public function sGetValeurEnregistree($mValeur) : string
    {
        return self::$rConnexion->quote($mValeur);
    }

    /**
     * Transforme un tableau en chaine de caractère composée des éléments séparés par des virgules, utile pour clause IN
     * @param array $aValeur
     * @return string
     */
    public function sConvertirTableauValeurEnTexte(array $aValeur): string
    {
        return implode(', ', array_map(function ($mValeur) {
            $this->sGetValeurEnregistree($mValeur);
        }, $aValeur));
    }

    /**
     * Ajoute ce champ à une clause select de façon appropriée pour son type
     * @param $sAliasColonne
     * @return string
     */
    abstract public function sGetSelect($sAliasColonne = '');

    public function __toString()
    {
        return $this->sNom;
    }

    /**
     * Marque ce champ comme devant pas être ignoré comme critère de recherche. En général on ignore les valeurs nulles ou vides
     * @param $mValeur
     * @return bool
     */
    public function bActifDansRecherche($mValeur)
    {
        return !empty($mValeur);
    }

    /**
     * Permet de changer un champ comme n'acceptant pas les valeurs nulles / non nullable
     * @return $this
     */
    public function oSetNotNull() : Champ
    {
        $this->bEstNullable = false;

        return $this;
    }

    /** Permet un traitement avant l'enregistrement des données côté SQL (ex: REPLACE() ou TO_DATE()
     * @return string
     */
    public function sGenererPlaceholderChampPrepare()
    {
        return ":{$this->sColonne}";
    }

    /**
     * Permet un traitement avant l'enregistrement des données côté PHP (ex: str_replace)
     * @param $mValeur
     * @return mixed
     */
    public function sFormatterValeurSQL($mValeur)
    {
        return $mValeur;
    }

    /**
     * Permet un traitement après récupération des données, au moment ou on génère l'objet
     * @param $mValeur
     * @return mixed
     */
    public function mGetValeur($mValeur)
    {
        return $mValeur;
    }

    /**
     * Peut potentiellement servir à améliorer la qualité de l'affichage
     * @return string
     */
    public function sGetFormatAffichage()
    {
        return $this->sAlias . '.' . $this->sColonne;
    }

}