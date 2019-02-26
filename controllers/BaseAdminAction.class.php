<?php

namespace APP\Modules\Base\Controllers;
// use APP\Core\Lib\Interne\PHP\UndeadBrain as UndeadBrain;
use APP\Modules\Base\Lib\Bdd as Bdd;

class BaseAdminAction extends Bdd
{
    /**
     * Constructeur de la classe.
     *
     * @param  string $szAction Action à effectuer.
     *
     * @return  void
     */
    public function __construct($szAction = '')
    {
        // echo "<pre>".print_r($_REQUEST, true)."</pre>";
        // On regarde si du contenu est disponible en cache.
        $szContenuEnCache = $this->szGetContenuEnCache();

        if ($szContenuEnCache != '') {

            // Si du contenu est disponible en cache, on le renvoie.
            echo $szContenuEnCache;

        } else {

            // Si aucun contenu n'est en cache, on traite l'action demandée.

            if ($szAction == 'details_base') {
                $aRetour = $this->szDetailsBaseDonnees();
                $szRetour = json_encode($aRetour);
            } else if ($szAction == 'load_select') {

                $aRetour = $this->aLoadSelect2JSON();
                $szRetour = json_encode($aRetour);
            }

            echo $szRetour;

            // Sauvegarde du contenu dans le cache.
            $this->vSauvegardeContenuEnCache($szRetour);

        }

    }


    /**
     * Détails de la base de données.
     *
     * @return array                Paramètres de retour pour JSON.
     */
    protected function szDetailsBaseDonnees()
    {
        $aRetour = array();

        // $oBdd = $this->oNew('Bdd');
        $aRetour['aElements'] = $this->aListeTables();

        return $aRetour;
    }

    /**
     * Effectue une recherche et retourne le résultat pour une select2
     *
     * @return array $aRetour   Retour JSON
     */
    protected function aLoadSelect2JSON()
    {
        $aRetour = array(

            "aSelect2" => array()
        );

        $aRecherche = array();
        $aChamps = $_POST['aChamps'];
        $sTable = $_POST['sTable'];
        $sOrderBy = $_POST['sOrderBy'];
        $sRestriction = $_POST['sRestriction'];

        $aRecherche['sTerm'] = $_POST['sResearch'];

        $oBdd = $this->oNew('Bdd');
        
        $aElements = $oBdd->aGetSelect2JSON($aRecherche, $aChamps, $sTable);

        if (isset($_REQUEST['bAvecMore']) === true && $_REQUEST['bAvecMore'] == true) {
            $aRetour['aSelect2'] = array(
                'results' => $aElements,
                'more' => true,
            );
        } else {
            $aRetour['aSelect2'] = $aElements;
        }

        $aRetour['aSelect2'] = $oBdd->aGetSelect2JSON($aRecherche, $aChamps, $sTable, $sOrderBy, $sRestriction);
      
        return $aRetour;
    }
}
