<?php

namespace APP\Modules\Base\Lib\Champ\Oracle;

class Date extends Char
{
//    protected $sFormatLibelle = 'DD/MM/YYYY';
//    protected $sFormatSQL = 'YYYY-MM-DD';

    protected $sFormatLibelle = 'DD/MM/YYYY';
    protected $sFormatSQL = 'YYYY-MM-DD';

    public function sTraiterValeur($sValeur) : string
    {
        $dDate = self::$rConnexion->quote($sValeur);


        return "TO_DATE($dDate, '$this->sFormatLibelle')";
    }



//    public function __construct($sNom, $sColonne, $sAlias = null, $sFormat = null)
//    {
//        $this->sColonne = $sColonne;
//        $this->sNom     = $sNom;
//        $this->sAlias   = $sAlias;
//
//        if (isset($sFormat)) {
//            $this->sFormatLecture = $sFormat;
//        }
//    }



    public function bEstDate()
    {
         return true;
    }

    public function sGetSelect($sAliasColonne = '')
    {
        $sRequete = "TO_CHAR($this->sAlias.$this->sColonne, '$this->sFormatLibelle') " ;
        $sAlias =  ' "' . ($sAliasColonne ?:  $this->sNom) . '"';


        if ($this->bEstNullable) {
            return "CASE WHEN $this->sAlias.$this->sColonne IS NULL THEN '' ELSE $sRequete END $sAlias";
        }

        return $sRequete . $sAlias;
    }

    public function sGenererPlaceholderChampPrepare()
    {
        return "TO_DATE(:$this->sColonne, '$this->sFormatLibelle')";
    }

    /**
     * Permet un traitement après récupération des données, au moment ou on génère l'objet
     * @param $mValeur
     * @return mixed
     */
    public function mGetValeur($mValeur)
    {
        return $mValeur ?? '';
    }
}