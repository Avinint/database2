<?php
// echo $_SERVER['DOCUMENT_ROOT'].'/modules/base/config/bdd.yml<br/>';

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/modules/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml') === true) {
    $szFichier = $_SERVER['DOCUMENT_ROOT'].'/modules/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml';
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/ressources/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml') === true) {
    $szFichier = $_SERVER['DOCUMENT_ROOT'].'/ressources/base/config/bdd_'.$GLOBALS['aParamsAppli']['environnement'].'.yml';
}
$data = \Spyc::YAMLLoad($szFichier);

$GLOBALS['aParamsBdd'] = $data;

// $objBdd = new APP\Modules\Base\Lib\Bdd();
// $bSucces = $objBdd->vConnexion();

// if ($bSucces === false) {
//     echo "Erreur lors de la connexion à la base de données.";
// }
