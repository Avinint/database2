# Version courante : 1.0.23

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
