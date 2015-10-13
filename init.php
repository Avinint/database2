<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/modules/Base/config/bdd.yml') === true) {
    $szFichier = $_SERVER['DOCUMENT_ROOT'].'/modules/Base/config/bdd.yml';
} else {
    $szFichier = str_replace('/'.$GLOBALS['szNomDossierProjet'].'/', '/core/', $_SERVER['DOCUMENT_ROOT']).'/modules/Base/config/bdd.yml';
}
$data = \Spyc::YAMLLoad($szFichier);

$GLOBALS['aParamsBdd'] = $data;

// $objBdd = new APP\Modules\Base\Lib\Bdd();
// $bSucces = $objBdd->vConnexion();

// if ($bSucces === false) {
//     echo "Erreur lors de la connexion à la base de données.";
// }
