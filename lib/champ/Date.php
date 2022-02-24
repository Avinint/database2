<?php

namespace APP\Modules\Base\Lib\Champ;

class Date extends Char
{
    protected $sFormatLibelle = 'd/m/Y';
    protected $sFormatSQL = 'Y-m-d';

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
        $sRequete = "DATE_FORMAT($this->sAlias.$this->sColonne, '$this->sFormatLibelle') " ;
        $sAlias =  ' "' . ($sAliasColonne ?:  $this->sNom) . '"';


        if ($this->bEstNullable) {
            return "IF(ISNULL($this->sAlias.$this->sColonne, '', $sRequete) $sAlias";
        }

        return $sRequete . $sAlias;
    }

    public function sGenererPlaceholderChampPrepare()
    {
        return "DATE_FORMAT(:$this->sColonne, '$this->sFormatSQL')";
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