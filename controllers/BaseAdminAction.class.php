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
}