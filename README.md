# Version courante : 1.1.1

* **v1.1.1 : Correction de notices dans Bdd.class.php**

* **v1.1.0 : Utilisation de la méthode vLog en lieu et place des error_log. Attention, nécessite Core en 2.14**

* **v1.0.53 : Ajout d'une méthode dans Bdd permettant de récupérer la bonne méthode SQL pour le datetime courant.**

* **v1.0.52 : Possibilité de désactiver les transactions au besoin.**

* **v1.0.46 : Correction d'une notice dans Bdd.**

* **v1.0.45 : Ajout d'infos de debug pour les erreurs SQL.**

* **v1.0.44 : Ajout d'infos de debug supplémentaires dans les logs lors de l'échec des requêtes.**

* **v1.0.43 : Ajout d'infos de debug dans les logs lors de l'échec des requêtes.**

* **v1.0.42 : Renommage d'une fonction et ajout de fonctions de compatibilité SQL avec SQLite.**

* **v1.0.41 : Ajout d'error_log lors de l'échec d'un insert.**

* **v1.0.40 : Ajout d'une méthode permettant l'insertion d'une ligne si elle n'existe pas déjà.**

* **v1.0.39 : Exclusion des vues de la liste des tables.**

* **v1.0.38 : Ajout de méthodes génériques dans Bdd afin de gérer les débuts et fins de transaction depuis les classes métier.**

* **v1.0.37 : Ajout d'un test pour éviter de créer des notices si on affiche toutes les options dans un select ajax sans attendre que l'utilisateur aie commencé à saisir du texte (absence de $_REQUEST['sResearch']).**

* **v1.0.36 : Ajout d'un test pour éviter de créer des notices si aucune restriction n'est définie lors du chargement des select ajax.** 

* **v1.0.35 : Ajout d'un test pour vérifier si la ressource logs est présente avant d'essayer de logguer** 

* **v1.0.34 : Ajout d'une méthode générique aRecupereDonneesPdf utilisée pour la ressource PDF** 

* **v1.0.33 : debug de sGetClauseCase pour les cas où il y a une apostrophe dans les libellés** 

* **v1.0.32 : Création d'une méthode sGetNomChamp pour obtenir le nom du champ mappé à partir d'un nom de champ** 

* **v1.0.31 : Mise a jour du commentaire de la méthode bDelete.** 

* **v1.0.30 : Suppression de log de requête qui ne sert plus à rien avec les requêtes préparées** 

* **v1.0.29 : suppression de lignes de code inutiles (avec exécution de 2 requêtes)** 

* **v1.0.28 : Rajout du finally dans la fonction bExecuterRequetePrepare pour gérer les erreurs mysql** 

* **v1.0.27 : Ajout de isset pour tester des GLOBALS**

* **v1.0.26 : Modification des appels à bSetLog dans les cruds**

* **v1.0.25 : Correctif de sMessagePDO en cas d'erreur SQL sur les requêtes préparées**

* **v1.0.24 : Ajout d'un paramètre bExceptionSurContrainte (false par défaut) permettant de lancer une exception en cas de violation de contrainte (si true) ou non (si false).**

* **v1.0.23 : Ajout d'une méthode pour générer des CASE permettant de retourner le libellé à la place de la valeur.**

* **v1.0.22 : correctif sur try/catch de query afin de ne pas générer l'erreur PHP**

* **v1.0.21 : suppression d'un warning sur aSelectBDD**


* **v1.0.20 : Ajout paramètre aOptionsDriver pour prepare dans CorePDO pour correspondre avec la méthode parente et suppression paramètre inutilisé dans bDelete de Bdd**

* **v1.0.19 : Ajout d'un throw de l'exception PDO après qu'elle a été attrapée (et que la requête a été logguée) pour pouvoir l'attraper dans la fonction appelante.**

* **v1.0.18 : Correction visibilité méthodes bInsert, bUpdate et bDelete et rajout de bSetLog si réussite requêtes**

* **v1.0.17 : Surcharge de prepare() dans CodePDO.class.php pour gérer correctement les erreurs et les placer dans sMessagePDO**

* **v1.0.16 : Utilisation de la propriété sNomCle déjà existante pour obtenir le nom du champ clé primaire au lieu de sNomChampIdBdd**

* **v1.0.15 : Gestion des requêtes préparées et centralisation de bInsert, bUpdate et bDelete dans Bdd.class.php**
- Intérêts des requêtes préparées : Gain de sécurité en se prémunissant des injections SQL et gain de temps d'exécution en ne préparant pas n fois la requête à chaque exécution.
- Raison de la centralisation des méthodes précisé dans le titre : Ces méthodes sont automatiquement créées par le générateur mais celle-ci sont la plupart du temps similaires
et la seule partie qui change dans celle-ci est le nom de la table. Les placer directement dans Bdd.class.php permet de gagner en propreté de code et par extension d'utiliser 
les requêtes préparées citées au dessus systématiquement. Il est toujours possible de les surcharger.

Pour utiliser ces méthodes il est cependant nécessaire de définir deux propriétés de classe dans le constructeur de vos modèles : sNomTable et sNomChampIdBdd

* **v1.0.14 : aGetSelect2JSON correctif pour faire des recherches avec apostrophe mon cher Bernard Pivot** 

* **v1.0.13 : Ajout du paramètre dans l'appel au vLogRequete afin de loguer dans le fichier de log d'Apache** 

* **v1.0.12 : PIW-156 : Prise en compte des valeurs NULL dans sFormateChampsRequeteEdition.**

* **v1.0.11 : EADO-373 : Utilisation d'un singleton pour la connexion BDD.**

* **v1.0.10 : Gestion du paramètre sRestriction dans Bdd->aGetSelect2JSON**
Utilisé pour la génération de formulaire.

* **v1.0.9 : Prise en charge du code d'erreur de driver spécifique pour les contraintes d'unicité en plus du SQLSTATE**
- Ajout d'un message lors d'une erreur de contrainte d'unicité et récupération du champ posant problème
- Utilisation de errorInfo() au lieu de errorCode() pour avoir plus de détails sur l'erreur

* **v1.0.8 : Ajout de la prise en compte de nouveaux champs mysql pour la génération**
- Ajout des champs double et longtext
- Mutualisation du switch qui génère les nom de champs mappés

* **v1.0.7 : Exécution du vLogRequete uniquement si la méthode existe**

* **v1.0.6 : Log automatique des requêtes SQL ayant échouées**
Nécessite un coeur en v2.4.4 minimum.

* **v1.0.5 : Ajout du tri (ORDER BY) dans les paramètres de la méthode qui effectue une requête pour dynamiser le select2**
Ajout d'un paramètre sOrderBy à la méthode 'aGetSelect2JSON', pour effectuer un tri personnaliser dans la requête qui
récupére les datas en vue de la dynamisation d'un select2. Si sOrderBy n'est pas défini, c'est la valeur 'aChamps[1]' qui est 
utilisé à la place

* **v1.0.4 : Ajout d'un paramètre bMore dans la méthode aGetSelect2JSONResearch permettant de n'afficher que les n premiers éléments**

* **v1.0.3 : Prise en compte du contexte d'exécution de la requête lors du aGetElements**
Prise en compte du paramètre szContexte lors de l'appel de la méthode szGetSelect dans aGetElements

* **v1.0.2 : Prise en compte du contexte d'exécution de la requête lors du nGetNbElements** 
Ajout d'un troisième paramètre sContexte dans la méthode nGetNbElements.

* **v1.0.1 : Ajout d'une méthode de formatage de fragments SQL pour les SET des UPDATE**
Cette méthode "sFormateChampsRequeteEdition" est utilisée par les nouvelles versions des classes de data générées 
par le générateur de la release-1.1. On lui passe un tableau champs/valeurs et elle nous retourne un fragment de 
SQL prêt à être utilisé dans le SET des INSERT ou UPDATE.
